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
    const POSTCAT_CMD = "/usr/sbin/postcat";
    const POSTQUEUE_CMD = "/usr/sbin/postqueue";
    const POSTSUPER_CMD = "/usr/sbin/postsuper";

    const ECHO_CMD = "echo '%s'";
    const SUDO_CMD = "sudo -S";

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
     * Flush queue
     *
     * @param  string $queueId
     * @return bool
     */
    function flushQueue(?string $queueId = null): bool;

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
     * @param  array $queueIds
     * @return void
     */
    function deleteQueue(array $queueIds = []): void;

    /**
     * Get message details by id in queue
     *
     * @param  string $queueId
     * @return array
     */
    function queueDetails(string $queueId): array;
}
