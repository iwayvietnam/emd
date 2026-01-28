<?php declare(strict_types=1);

namespace App\Mail\Queue;

use Illuminate\Support\Facades\Process;

/**
 * Local mail queue class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class LocalQueue implements QueueInterface
{
    /**
     * Constructor
     *
     * @param string $configDir
     * @return self
     */
    public function __construct(private readonly string $configDir) {}

    /**
     * {@inheritdoc}
     */
    public function listQueue(): array
    {
        $result = Process::run(
            implode([self::POSTQUEUE_CMD, " -c ", $this->configDir, " -j"]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }

        return json_decode($result->output());
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(?string $queueId = null): bool
    {
        if (empty($queueId)) {
            $result = Process::run(
                implode([self::POSTQUEUE_CMD, " -c ", $this->configDir, " -f"]),
            );
        } else {
            $result = Process::run(
                implode([
                    self::POSTQUEUE_CMD,
                    " -c ",
                    $this->configDir,
                    " -i ",
                    $queueId,
                ]),
            );
        }
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->successful();
    }

    /**
     * {@inheritdoc}
     */
    public function reQueue(string $queueId): bool
    {
        $result = Process::run(
            implode([
                self::POSTQUEUE_CMD,
                " -c ",
                $this->configDir,
                " -r ",
                $queueId,
            ]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->successful();
    }

    /**
     * {@inheritdoc}
     */
    public function holdQueue(string $queueId): bool
    {
        $result = Process::run(
            implode([
                self::POSTSUPER_CMD,
                " -c ",
                $this->configDir,
                " -h ",
                $queueId,
            ]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->successful();
    }

    /**
     * {@inheritdoc}
     */
    public function unholdQueue(string $queueId): bool
    {
        $result = Process::run(
            implode([
                self::POSTSUPER_CMD,
                " -c ",
                $this->configDir,
                " -H ",
                $queueId,
            ]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->successful();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue(array $queueIds = []): void
    {
        $result = Process::run(
            implode([
                self::POSTSUPER_CMD,
                " -c ",
                $this->configDir,
                " -d ",
                implode(" -d ", $queueIds),
            ]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function queueDetails(string $queueId): array
    {
        $result = Process::run(
            implode([
                self::POSTCAT_CMD,
                " -c ",
                $this->configDir,
                " -q ",
                $queueId,
            ]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }

        $details = [];
        if ($result->successful()) {
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
            if (preg_match($pattern, $result->output(), $matches)) {
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
        }

        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function queueContent(string $queueId): string
    {
        $result = Process::run(
            implode([
                self::POSTCAT_CMD,
                " -c ",
                $this->configDir,
                " -qb ",
                $queueId,
            ]),
        );
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->output();
    }
}
