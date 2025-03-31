<?php declare(strict_types=1);

namespace App\Mail\Policy\Interface;

/**
 * Policy server adapter interface
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
interface AdapterInterface
{
    const POLICY_WORKER = 4;
    const LISTEN_HOST = "127.0.0.1";
    const LISTEN_PORT = 1403;

    /**
     * Handle policy
     *
     * @return void
     */
    function handle(): void;
}
