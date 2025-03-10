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
     * {@inheritdoc}
     */
    public function listQueue(): array
    {
        $result = Process::run(self::POSTQUEUE_CMD . ' -j');
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }

        return json_decode($result->output());
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(): bool
    {
        $result = Process::run(self::POSTQUEUE_CMD . ' -f');
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
        $result = Process::run(self::POSTQUEUE_CMD . ' -r ' . $queueId);
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
        $result = Process::run(self::POSTSUPER_CMD . ' -h ' . $queueId);
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
        $result = Process::run(self::POSTSUPER_CMD . ' -H ' . $queueId);
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->successful();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue(string $queueId): bool
    {
        $result = Process::run(self::POSTSUPER_CMD . ' -d ' . $queueId);
        if ($result->failed()) {
            logger()->error($result->errorOutput());
        }
        return $result->successful();
    }

    /**
     * {@inheritdoc}
     */
    public function queueDetails(string $queueId): array
    {
        $result = Process::run(self::POSTCAT_CMD . ' -q ' . $queueId);
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
            }
        }

        return $details;
    }
}
