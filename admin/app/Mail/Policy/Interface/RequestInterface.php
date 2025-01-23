<?php declare(strict_types=1);

namespace App\Mail\Policy\Interface;

/**
 * Policy request interface
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
interface RequestInterface
{
    /**
     * Get request attribute
     *
     * @return string
     */
    function getRequest(): string;

    /**
     * Get protocol state attribute
     *
     * @return string
     */
    function getProtocolState(): string;

    /**
     * Get protocol name attribute
     *
     * @return string
     */
    function getProtocolName(): string;

    /**
     * Get helo name attribute
     *
     * @return string
     */
    function getHeloName(): string;

    /**
     * Get queue id attribute
     *
     * @return string
     */
    function getQueueId(): string;

    /**
     * Get sender attribute
     *
     * @return string
     */
    function getSender(): string;

    /**
     * Get recipient attribute
     *
     * @return string
     */
    function getRecipient(): string;

    /**
     * Get recipient count attribute
     *
     * @return int
     */
    function getRecipientCount(): int;

    /**
     * Get client address attribute
     *
     * @return string
     */
    function getClientAddress(): string;

    /**
     * Get client name attribute
     *
     * @return string
     */
    function getClientName(): string;

    /**
     * Get reverse client name attribute
     *
     * @return string
     */
    function getReverseClientName(): string;

    /**
     * Get instance attribute
     *
     * @return string
     */
    function getInstance(): string;

    /**
     * Get message size attribute
     *
     * @return int
     */
    function getSize(): int;

    /**
     * Get server address attribute
     *
     * @return string
     */
    function getServerAddress(): string;

    /**
     * Get server port attribute
     *
     * @return int
     */
    function getServerPort(): int;

    /**
     * Get mail version attribute
     *
     * @return string
     */
    function getMailVersion(): string;
}
