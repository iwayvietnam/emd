<?php declare(strict_types=1);

namespace App\Support;

/**
 * Helper class
 *
 * @package  App
 * @category Support
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Helper
{
    public static function explodeRecipients(string $recipients): array
    {
        $addresses = [];
        $lines = array_map(
            static fn($line) => strtolower(trim($line)),
            explode(PHP_EOL, trim($recipients))
        );
        foreach ($lines as $line) {
            if (filter_var($line, FILTER_VALIDATE_EMAIL)) {
                $addresses[] = $line;
            } else {
                $parts = array_map(
                    static fn($part) => trim($part),
                    explode(",", $line)
                );
                foreach ($parts as $part) {
                    if (filter_var($part, FILTER_VALIDATE_EMAIL)) {
                        $addresses[] = $part;
                    }
                }
            }
        }
        return $addresses;
    }
}
