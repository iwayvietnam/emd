<?php declare(strict_types=1);

namespace App\Mail\Queue;

/**
 * Mail queue interface
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
interface QueueInterface
{
    const POSTCAT_CMD = "sudo postcat";
    const POSTQUEUE_CMD = "sudo postqueue";
    const POSTSUPER_CMD = "sudo postsuper";

    const QUEUE_REGEX = "/^([^\s\*\!]+)[\*\!]?\s*(\d+)\s+(\S+\s+\S+\s+\d+\s+\d+:\d+:\d+)\s+(.*)/";
    const START_OF_QUEUE_REGEX = "(?:\*\*\*\s+ENVELOPE\s+RECORDS.*\*\*\*)";
    const START_OF_MAIL_REGEX = "(?:\*\*\*\s+MESSAGE\s+CONTENTS.*\*\*\*)";
    const END_OF_MAIL_REGEX = "(?:\*\*\*\s+HEADER\s+EXTRACTED.*\*\*\*)";

    /**
     * List all messages in queues
     *
     * @return array
     */
    function listQueue(): array;

    /**
     * Flush all queues
     *
     * @return bool
     */
    function flushQueue(): bool;

    /**
     * Re queue a message by id
     *
     * @param  string $queueId
     * @return bool
     */
    function reQueue(string $queueId): bool;

    /**
     * Hold a message by id in queue
     *
     * @param  string $queueId
     * @return bool
     */
    function holdQueue(string $queueId): bool;

    /**
     * Unhold a message by id in queue
     *
     * @param  string $queueId
     * @return bool
     */
    function unholdQueue(string $queueId): bool;

    /**
     * Delete a message by id in queue
     *
     * @param  string $queueId
     * @return bool
     */
    function deleteQueue(string $queueId): bool;

    /**
     * Get message details by id in queue
     *
     * @param  string $queueId
     * @return array
     */
    function queueDetails(string $queueId): array;
}
