<?php declare(strict_types=1);

namespace App\Support;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

/**
 * Remote server class
 *
 * @package  App
 * @category Support
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class RemoteServer
{
    const SSH_CONNECT_TIMEOUT = 1000;
    const SUDO_ASK_PASSWORD = "[sudo] password for";
    const SUDO_NO_PASSWORD = "no password was provided";
    const SUDO_INCORRECT_PASSWORD = "incorrect password attempt";

    private readonly SFTP $ssh;

    /**
     * Constructor
     *
     * @param string $remoteHost
     * @param int $remotePort
     * @param string $remoteUser
     * @param string $privateKey
     * @return self
     */
    public function __construct(
        private readonly string $remoteHost = "0.0.0.0",
        private readonly int $remotePort = 22,
        private readonly string $remoteUser = "root",
        private readonly string $privateKey = ""
    ) {
        if (!empty($this->privateKey)) {
            $this->ssh = new SFTP(
                $this->remoteHost,
                $this->remotePort,
                self::SSH_CONNECT_TIMEOUT
            );
            if (
                !$this->ssh->login(
                    $this->remoteUser,
                    PublicKeyLoader::load($this->privateKey)
                )
            ) {
                throw new \RuntimeException(
                    strtr(
                        "SSH login error with server: {user}@{host}",
                        [
                            "{user}" => $this->remoteUser,
                            "{host}" => $this->remoteHost,
                        ]
                    )
                );
            }
        } else {
            throw new \UnexpectedValueException(
                "The SSH private key is empty."
            );
        }
    }

    /**
     * Run remote command
     *
     * @param string $command
     * @return string
     */
    public function runCommand(string $command): string
    {
        $this->ssh->enableQuietMode();
        $output = $this->ssh->exec($command);
        $this->ssh->disableQuietMode();

        $errorStr = str($this->ssh->getStdError())->trim();
        if ($errorStr->isNotEmpty()) {
            $throwError = true;
            if ($errorStr->contains(self::SUDO_ASK_PASSWORD)) {
                $throwError = $errorStr->contains([
                    self::SUDO_NO_PASSWORD,
                    self::SUDO_INCORRECT_PASSWORD,
                ]);
                if (empty($output)) {
                    $output = $errorStr->toString();
                }
            }
            if ($throwError) {
                throw new \RuntimeException(
                    strtr(
                        "Error running command {command} on server {host}: {message}",
                        [
                            "{command}" => $command,
                            "{host}" => $this->remoteHost,
                            "{message}" => $errorStr,
                        ]
                    )
                );
            }
            else {
                logger()->error($errorStr);
            }
        }
        return $output;
    }

    /**
     * Upload file to remote server
     *
     * @param string $remoteFile
     * @param string $localFile
     * @return self
     */
    public function uploadFile(string $remoteFile, string $localFile): self
    {
        return $this->uploadContent($remoteFile, file_get_contents($localFile));
    }

    /**
     * Upload content to remote server
     *
     * @param string $remoteFile
     * @param string $content
     * @return self
     */
    public function uploadContent(string $remoteFile, string $content): self
    {
        try {
            $remoteDir = str_replace(basename($remoteFile), "", $remoteFile);
            if (!$this->ssh->file_exists($remoteDir)) {
                $this->ssh->mkdir($remoteDir, 0755, true);
            }
            $this->ssh->put($remoteFile, $content, SFTP::SOURCE_STRING);
        } catch (\Throwable $th) {
            throw new \RuntimeException(
                strtr(
                    "Error uploading content to {file} on server {host}: {message}",
                    [
                        "{file}" => $remoteFile,
                        "{host}" => $this->remoteHost,
                        "{message}" => $th->getMessage(),
                    ]
                )
            );
        }
        return $this;
    }

    /**
     * Delete file on  remote server
     *
     * @param string $remoteFile
     * @return self
     */
    public function deleteFile(string $remoteFile): self
    {
        try {
            if ($this->ssh->file_exists($remoteFile)) {
                $this->ssh->delete($remoteFile);
            }
        } catch (\Throwable $th) {
            throw new \RuntimeException(
                strtr(
                    "Error deleting {file} on server {host}: {message}",
                    [
                        "{file}" => $remoteFile,
                        "{host}" => $this->remoteHost,
                        "{message}" => $th->getMessage(),
                    ]
                )
            );
        }
        return $this;
    }
}
