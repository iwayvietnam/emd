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
                        "SSH login error with server: {remoteUser}@{remoteHost}:{remotePort}",
                        [
                            "{remoteUser}" => $this->remoteUser,
                            "{remoteHost}" => $this->remoteHost,
                            "{remotePort}" => $this->remotePort,
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
     * @return self
     */
    public function runCommand(string $command): self
    {
        try {
            $this->ssh->enableQuietMode();
            $output = $this->ssh->exec($command);
            $this->ssh->disableQuietMode();

            if (!empty($output)) {
                logger()->debug(
                    "Result of running command {command} on server {host}: {output}",
                    [
                        "command" => $command,
                        "remoteHost" => $this->remoteHost,
                        "output" => $output,
                    ]
                );
            }

            $errorStr = str($this->ssh->getStdError())->trim();
            if ($errorStr->isNotEmpty()) {
                logger()->error($errorStr);
                $throwError = true;
                if ($errorStr->contains(self::SUDO_ASK_PASSWORD)) {
                    $throwError = $errorStr->contains([
                        self::SUDO_NO_PASSWORD,
                        self::SUDO_INCORRECT_PASSWORD,
                    ]);
                }
                if ($throwError) {
                    throw new \RuntimeException(
                        strtr(
                            "Error running command {command} on server {remoteHost}: {message}",
                            [
                                "{command}" => $command,
                                "{remoteHost}" => $this->remoteHost,
                                "{message}" => $errorStr,
                            ]
                        )
                    );
                }
            }
        } catch (\Throwable $th) {
            throw new \RuntimeException(
                strtr(
                    "Error running command {command} on server {remoteHost}: {message}",
                    [
                        "{command}" => $command,
                        "{remoteHost}" => $this->remoteHost,
                        "{message}" => $th->getMessage(),
                    ]
                )
            );
        }
        return $this;
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
                    "There was an error uploading content to {remoteFile} on server {remoteHost}: {message}",
                    [
                        "{remoteFile}" => $remoteFile,
                        "{remoteHost}" => $this->remoteHost,
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
                    "There was an error deleting {remoteFile} on server {remoteHost}: {message}",
                    [
                        "{remoteFile}" => $remoteFile,
                        "{remoteHost}" => $this->remoteHost,
                        "{message}" => $th->getMessage(),
                    ]
                )
            );
        }
        return $this;
    }
}
