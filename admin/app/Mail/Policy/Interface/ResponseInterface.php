<?php declare(strict_types=1);

namespace App\Mail\Policy\Interface;

/**
 * Policy response interface
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
interface ResponseInterface
{
    /**
     * Get response action
     *
     * @return string
     */
    function getAction(): string;
}
