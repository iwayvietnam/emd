<?php declare(strict_types=1);

namespace App\Mail\Queue;

use App\Support\RemoteServer;

/**
 * Remote mail queue class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class RemoteQueue implements QueueInterface
{
    /**
     * Constructor
     *
     * @param RemoteServer $remoteServer
     * @param string $sudoPassword
     * @param string $configDir
     * @return self
     */
    public function __construct(
        private readonly RemoteServer $remoteServer,
        private readonly string $sudoPassword,
        private readonly string $configDir,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function listQueue(): array
    {
        $result = [];
        $lines = explode(
            PHP_EOL,
            $this->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudoPassword),
                    " | ",
                    self::SUDO_CMD,
                    " ",
                    self::POSTQUEUE_CMD,
                    " -c ",
                    $this->configDir,
                    " -j",
                ]),
            ),
        );
        foreach ($lines as $line) {
            $queue = json_decode($line, true);
            if ($queue) {
                $recipients = [];
                foreach ($queue["recipients"] as $recipient) {
                    $recipients[] = $recipient["address"];
                }
                $queue["recipients"] = implode(", ", $recipients);
                $result[] = $queue;
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(?string $queueId = null): bool
    {
        if (empty($queueId)) {
            return !empty(
                $this->runCommand(
                    implode([
                        sprintf(self::ECHO_CMD, $this->sudoPassword),
                        " | ",
                        self::SUDO_CMD,
                        " ",
                        self::POSTQUEUE_CMD,
                        " -c ",
                        $this->configDir,
                        " -f",
                    ]),
                )
            );
        } else {
            return !empty(
                $this->runCommand(
                    implode([
                        sprintf(self::ECHO_CMD, $this->sudoPassword),
                        " | ",
                        self::SUDO_CMD,
                        " ",
                        self::POSTQUEUE_CMD,
                        " -c ",
                        $this->configDir,
                        " -i ",
                        $queueId,
                    ]),
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reQueue(string $queueId): bool
    {
        return !empty(
            $this->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudoPassword),
                    " | ",
                    self::SUDO_CMD,
                    " ",
                    self::POSTSUPER_CMD,
                    " -c ",
                    $this->configDir,
                    " -r ",
                    $queueId,
                ]),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function holdQueue(string $queueId): bool
    {
        return !empty(
            $this->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudoPassword),
                    " | ",
                    self::SUDO_CMD,
                    " ",
                    self::POSTSUPER_CMD,
                    " -c ",
                    $this->configDir,
                    " -h ",
                    $queueId,
                ]),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unholdQueue(string $queueId): bool
    {
        return !empty(
            $this->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudoPassword),
                    " | ",
                    self::SUDO_CMD,
                    " ",
                    self::POSTSUPER_CMD,
                    " -c ",
                    $this->configDir,
                    " -H ",
                    $queueId,
                ]),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue(array $queueIds = []): void
    {
        if (!empty($queueIds)) {
            $this->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudoPassword),
                    " | ",
                    self::SUDO_CMD,
                    " ",
                    self::POSTSUPER_CMD,
                    " -c ",
                    $this->configDir,
                    " -d ",
                    implode(" -d ", $queueIds),
                ]),
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function queueDetails(string $queueId): array
    {
        $output = $this->runCommand(
            implode([
                sprintf(self::ECHO_CMD, $this->sudoPassword),
                " | ",
                self::SUDO_CMD,
                " ",
                self::POSTCAT_CMD,
                " -c ",
                $this->configDir,
                " -q ",
                $queueId,
            ]),
        );

        $details = [];
        $pattern = implode([
            "/",
            self::START_OF_QUEUE_REGEX,
            "(.*)",
            self::START_OF_MAIL_REGEX,
            "(.*)",
            self::HEADER_OF_MAIL_REGEX,
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
            if (!empty($matches[3])) {
                $details["header"] = trim($matches[3]);
            }
        }

        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function queueContent(string $queueId): string
    {
        return $this->runCommand(
            implode([
                sprintf(self::ECHO_CMD, $this->sudoPassword),
                " | ",
                self::SUDO_CMD,
                " ",
                self::POSTCAT_CMD,
                " -c ",
                $this->configDir,
                " -qb ",
                $queueId,
            ]),
        );
    }

    /**
     * Execute command.
     *
     * @param string $command.
     * @return string
     */
    private function runCommand(string $command): string
    {
        return $this->remoteServer->runCommand($command);
    }
}
