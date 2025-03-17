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
    const SUDO_COMMAND = 'sudo ';
    const SUDO_NO_PASSWORD = 'no password was provided';
    const SUDO_INCORRECT_PASSWORD = 'incorrect password attempt';

    private readonly SFTP $ssh;

    public function __construct(
        private readonly string $remoteHost = "0.0.0.0",
        private readonly int $remotePort = 22,
        private readonly string $remoteUser = "root",
        private readonly string $privateKey = ""
    ) {
        if (!empty($this->privateKey)) {
            $this->ssh = new SFTP(
                $this->remoteHost, $this->remotePort, self::SSH_CONNECT_TIMEOUT
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

    public function runCommand(string $command)
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

            $error = $this->ssh->getStdError();
            if (!empty($error)) {
                logger()->error($error);
                $throwError = false;
                if (strpos($command, self::SUDO_COMMAND) !== false) {
                    if (
                        (strpos($error, self::SUDO_NO_PASSWORD) !== false) ||
                        strpos($error, self::SUDO_INCORRECT_PASSWORD) !== false
                    ) {
                        $throwError = true;
                    }
                }
                elsee {
                    $throwError = true;
                }
                if ($throwError) {
                    throw new \RuntimeException(
                        strtr(
                            "Error running command {command} on server {remoteHost}: {message}",
                            [
                                "{command}" => $command,
                                "{remoteHost}" => $this->remoteHost,
                                "{message}" => $error,
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
    }

    public function uploadFile(string $remoteFile, string $localFile)
    {
        return $this->uploadContent($remoteFile, file_get_contents($localFile));
    }

    public function uploadContent(string $remoteFile, string $content)
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
    }

    public function deleteFile(string $remoteFile)
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
    }
}
