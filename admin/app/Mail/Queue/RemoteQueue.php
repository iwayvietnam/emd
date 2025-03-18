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
     * @return self
     */
    public function __construct(
        private readonly RemoteServer $remoteServer,
        private readonly string $sudoPassword
    ) {}

    /**
     * {@inheritdoc}
     */
    public function listQueue(): array
    {
        return json_decode($this->runCommand(implode([
            sprintf(self::ECHO_CMD, $this->sudoPassword),
            " | ",
            self::SUDO_CMD,
            " ",
            self::POSTQUEUE_CMD,
            " -j",
        ])));
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(string? $queueId = null): bool
    {
        if (empty($queueId)) {
            return !empty($this->runCommand(implode([
                sprintf(self::ECHO_CMD, $this->sudoPassword),
                " | ",
                self::SUDO_CMD,
                " ",
                self::POSTQUEUE_CMD,
                " -f",
            ])));
        }
        else {
            return !empty($this->runCommand(implode([
                sprintf(self::ECHO_CMD, $this->sudoPassword),
                " | ",
                self::SUDO_CMD,
                " ",
                self::POSTQUEUE_CMD,
                " -i ",
                $queueId,
            ])));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reQueue(string $queueId): bool
    {
        return !empty($this->runCommand(implode([
            sprintf(self::ECHO_CMD, $this->sudoPassword),
            " | ",
            self::SUDO_CMD,
            " ",
            self::POSTSUPER_CMD,
            " -r ",
            $queueId,
        ])));
    }

    /**
     * {@inheritdoc}
     */
    public function holdQueue(string $queueId): bool
    {
        return !empty($this->runCommand(implode([
            sprintf(self::ECHO_CMD, $this->sudoPassword),
            " | ",
            self::SUDO_CMD,
            " ",
            self::POSTSUPER_CMD,
            " -h ",
            $queueId,
        ])));
    }

    /**
     * {@inheritdoc}
     */
    public function unholdQueue(string $queueId): bool
    {
        return !empty($this->runCommand(implode([
            sprintf(self::ECHO_CMD, $this->sudoPassword),
            " | ",
            self::SUDO_CMD,
            " ",
            self::POSTSUPER_CMD,
            " -H ",
            $queueId,
        ])));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue(string $queueId): bool
    {
        return !empty($this->runCommand(implode([
            sprintf(self::ECHO_CMD, $this->sudoPassword),
            " | ",
            self::SUDO_CMD,
            " ",
            self::POSTSUPER_CMD,
            " -d ",
            $queueId,
        ])));
    }

    /**
     * {@inheritdoc}
     */
    public function queueDetails(string $queueId): array
    {
        $output = $this->runCommand(implode([
            sprintf(self::ECHO_CMD, $this->sudoPassword),
            " | ",
            self::SUDO_CMD,
            " ",
            self::POSTCAT_CMD,
            " -q ",
            $queueId,
        ]));

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
    private function runCommand(string $command): string
    {
        return $this->remoteServer->runCommand($command);
    }
}
