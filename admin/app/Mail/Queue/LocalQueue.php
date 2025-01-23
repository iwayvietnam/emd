<?php declare(strict_types=1);

namespace App\Mail\Queue;

use Symfony\Component\Process\Process;

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
        $process = new Process([self::POSTQUEUE_CMD, "-j"]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });

        return json_decode($process->getOutput());
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(): bool
    {
        $process = new Process([self::POSTQUEUE_CMD, "-f"]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });
        return $process->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function reQueue(string $queueId): bool
    {
        $process = new Process([self::POSTSUPER_CMD, "-r", $queueId]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });
        return $process->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function holdQueue(string $queueId): bool
    {
        $process = new Process([self::POSTSUPER_CMD, "-h", $queueId]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });
        return $process->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function unholdQueue(string $queueId): bool
    {
        $process = new Process([self::POSTSUPER_CMD, "-H", $queueId]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });
        return $process->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQueue(string $queueId): bool
    {
        $process = new Process([self::POSTSUPER_CMD, "-d", $queueId]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });
        return $process->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
    public function queueDetails(string $queueId): array
    {
        $process = new Process([self::POSTCAT_CMD, "-q", $queueId]);
        $process->run(static function ($type, $output) {
            if ($type === Process::ERR) {
                logger()->error($output);
            }
        });

        $details = [];
        if ($process->isSuccessful()) {
            $pattern = implode([
                "/",
                self::START_OF_QUEUE_REGEX,
                "(.*)",
                self::START_OF_MAIL_REGEX,
                "(.*)",
                self::END_OF_MAIL_REGEX,
                "/ms",
            ]);
            if (preg_match($pattern, $process->getOutput(), $matches)) {
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
