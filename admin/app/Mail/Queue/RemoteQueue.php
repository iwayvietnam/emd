<?php declare(strict_types=1);

namespace App\Mail\Queue;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;

/**
 * Remote mail queue class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class RemoteQueue implements QueueInterface
{
    private readonly SSH2 $ssh;

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
        if (!empty($privateKey)) {
            $this->ssh = new SSH2($this->remoteHost, $this->remotePort);
            if (
                !$this->ssh->login(
                    $this->remoteUser,
                    PublicKeyLoader::load($privateKey)
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
     * {@inheritdoc}
     */
    public function listQueue(): array
    {
        return json_decode($this->execCommand(self::POSTQUEUE_CMD . " -j"));
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(): bool
    {
        return !empty($this->execCommand(self::POSTQUEUE_CMD . " -f"));
    }

    /**
     * {@inheritdoc}
     */
    public function reQueue(string $queueId): bool
    {
        return !empty(
            $this->execCommand(implode([self::POSTSUPER_CMD, " -r ", $queueId]))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function holdQueue(string $queueId): bool
    {
        return !empty(
            $this->execCommand(implode([self::POSTSUPER_CMD, " -h ", $queueId]))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unholdQueue(string $queueId): bool
    {
        return !empty(
            $this->execCommand(implode([self::POSTSUPER_CMD, " -H ", $queueId]))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue(string $queueId): bool
    {
        return !empty(
            $this->execCommand(implode([self::POSTSUPER_CMD, " -d ", $queueId]))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function queueDetails(string $queueId): array
    {
        $output = $this->execCommand(
            implode([self::POSTCAT_CMD, " -q ", $queueId])
        );

        $details = [];
        $pattern = implode([
            "/",
            self::START_OF_QUEUE_REGEX,
            "(.*)",
            self::START_OF_MAIL_REGEX,
            "(.*)",
            self::END_OF_MAIL_REGEX,
            "/ms",
        ]);
        if (preg_match($pattern, $output, $matches)) {
            if (!empty($matches[1])) {
                $details["info"] = trim($matches[1]);
            }
            if (!empty($matches[2])) {
                $details["message"] = trim($matches[2]);
            }
        }

        return $details;
    }

    /**
     * Execute command.
     *
     * @param string $command.
     * @return string
     */
    private function execCommand(string $command): string
    {
        $output = "";
        try {
            $this->ssh->enableQuietMode();
            $output = $this->ssh->exec($command);
            $this->ssh->disableQuietMode();

            if (!empty($output)) {
                logger()->notice(
                    "Result of running command {command} with server {remoteHost}: {output}",
                    [
                        "command" => $command,
                        "remoteHost" => $this->remoteHost,
                        "output" => $output,
                    ]
                );
            }

            if (!empty($this->ssh->getStdError())) {
                throw new \RuntimeException(
                    strtr(
                        "Error running command {command} with host {remoteHost}: {message}",
                        [
                            "{command}" => $command,
                            "{remoteHost}" => $this->remoteHost,
                            "{message}" => $this->ssh->getStdError(),
                        ]
                    )
                );
            }
        } catch (\Throwable $th) {
            throw new \RuntimeException(
                strtr(
                    "Error running command {command} with host {remoteHost}: {message}",
                    [
                        "{command}" => $command,
                        "{remoteHost}" => $this->remoteHost,
                        "{message}" => $th->getMessage(),
                    ]
                )
            );
        }
        return $output;
    }
}
