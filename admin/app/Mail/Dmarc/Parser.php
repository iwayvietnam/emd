<?php declare(strict_types=1);

namespace App\Mail\Dmarc;

use Webklex\PHPIMAP\Message;

/**
 * Dmarc parser class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
final class Parser
{
    const EMPTY_NODE_XPATH = "//*[not(text())]";

    public static function parseMessage(Message $message): array
    {
        $reports = [];

        $attachments = $message->attachments();
        foreach ($attachments as $attachment) {
            $xml = false;
            switch ($attachment->content_type) {
                case "application/zip":
                case "application/x-zip-compressed":
                    $xml = self::unzip($attachment->content);
                    break;
                case "application/gzip":
                case "application/x-gzip":
                    $xml = self::gunzip($attachment->content);
                    break;
                case "application/xml":
                    $xml = $attachment->content;
                    break;
            }
            if (!empty($xml)) {
                $report = self::parseXml($xml);
                if (!empty($report)) {
                    $reports[] = $report;
                }
            }
        }

        return $reports;
    }

    private static function parseXml(string $content): mixed
    {
        $xml = @simplexml_load_string($content);
        if (empty($xml)) {
            return false;
        }
        foreach ($xml->xpath(self::EMPTY_NODE_XPATH) as $remove) {
            unset($remove[0]);
        }
        try {
            $report = json_decode(json_encode($xml));
            unset($xml);

            // Adjust to array unconditionally
            if (is_array($report->record)) {
                $report->records = $report->record;
            } else {
                $report->records = [$report->record];
            }
            unset($report->record);

            return $report;
        } catch (\Throwable $e) {
            logger()->error($e);
            return false;
        }
    }

    private static function unzip(string $data): string|bool
    {
        if (class_exists(\ZipArchive::class)) {
            $zipFile = tempnam(sys_get_temp_dir(), time() . "-report.zip");
            file_put_contents($zipFile, $data);
            $zip = new \ZipArchive();
            $zip->open($zipFile);
            $xml = $zip->getFromIndex(0);
            $zip->close();
            return $xml;
        } elseif (class_exists(\PhpZip\ZipFile::class)) {
            $zip = new \PhpZip\ZipFile();
            $zip->openFromString($data);
            foreach ($zip as $content) {
                $xml = $content;
                break;
            }
            $zip->close();
            return $xml ?? false;
        }
        return false;
    }

    private static function gunzip(string $data): string|bool
    {
        if (function_exists("gzdecode")) {
            return gzdecode($data);
        } else {
            return self::gzdecode($data);
        }
    }

    private static function gzdecode(
        string $data,
        string &$fileName = ""
    ): string|bool {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            logger()->error("gzdecode: not in gzip format.");
            return false; // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data, 2, 1)); // Compression method
        $flags = ord(substr($data, 3, 1)); // Flags
        if ($flags & (31 != $flags)) {
            logger()->error("gzdecode: reserved bits not allowed.");
            return false;
        }
        // NOTE: $mTime may be negative (PHP integer limitations)
        $mTime = unpack("V", substr($data, 4, 4));
        $mTime = $mTime[1];
        $xfl = substr($data, 8, 1);
        $os = substr($data, 8, 1);
        $headerLen = 10;
        $extraLen = 0;
        $extra = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerLen - 2 < 8) {
                return false; // invalid
            }
            $extraLen = unpack("v", substr($data, 8, 2));
            $extraLen = $extraLen[1];
            if ($len - $headerLen - 2 - $extraLen < 8) {
                return false; // invalid
            }
            $extra = substr($data, 10, $extraLen);
            $headerLen += 2 + $extraLen;
        }
        $fileNameLen = 0;
        $fileName = "";
        if ($flags & 8) {
            // C-style string
            if ($len - $headerLen - 1 < 8) {
                return false; // invalid
            }
            $fileNameLen = strpos(substr($data, $headerLen), chr(0));
            if (
                $fileNameLen === false ||
                $len - $headerLen - $fileNameLen - 1 < 8
            ) {
                return false; // invalid
            }
            $fileName = substr($data, $headerLen, $fileNameLen);
            $headerLen += $fileNameLen + 1;
        }
        $commentLen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerLen - 1 < 8) {
                return false; // invalid
            }
            $commentLen = strpos(substr($data, $headerLen), chr(0));
            if (
                $commentLen === false ||
                $len - $headerLen - $commentLen - 1 < 8
            ) {
                return false; // Invalid header format
            }
            $comment = substr($data, $headerLen, $commentLen);
            $headerLen += $commentLen + 1;
        }
        $headerCrc = "";
        if ($flags & 2) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerLen - 2 < 8) {
                return false; // invalid
            }
            $calcCrc = crc32(substr($data, 0, $headerLen)) & 0xffff;
            $headerCrc = unpack("v", substr($data, $headerLen, 2));
            $headerCrc = $headerCrc[1];
            if ($headerCrc != $calcCrc) {
                logger()->error("gzdecode: header checksum failed.");
                return false; // Bad header CRC
            }
            $headerLen += 2;
        }
        // GZIP FOOTER
        $dataCrc = unpack("V", substr($data, -8, 4));
        $dataCrc = sprintf("%u", $dataCrc[1] & 0xffffffff);
        $iSize = unpack("V", substr($data, -4));
        $iSize = $iSize[1];
        // decompression:
        $bodyLen = $len - $headerLen - 8;
        if ($bodyLen < 1) {
            // IMPLEMENTATION BUG!
            return null;
        }
        $body = substr($data, $headerLen, $bodyLen);
        $data = "";
        if ($bodyLen > 0) {
            switch ($method) {
                case 8:
                    // Currently the only supported compression method:
                    $data = gzinflate($body);
                    break;
                default:
                    logger()->error("gzdecode: unknown compression method.");
                    return false;
            }
        } // zero-byte body content is allowed
        // Verifiy CRC32
        $crc = sprintf("%u", crc32($data));
        $crcOk = $crc == $dataCrc;
        $lenOk = $iSize == strlen($data);
        if (!$lenOk || !$crcOk) {
            $error =
                ($lenOk ? "" : "length check failed. ") .
                ($crcOk ? "" : "checksum failed.");
            logger()->error("gzdecode: $error");
            return false;
        }
        return $data;
    }
}
