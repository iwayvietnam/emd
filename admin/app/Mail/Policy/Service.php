<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Mail\Policy\Adapter\Workerman;

/**
 * Policy service class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
final class Service
{
    const DEFAULT_ADAPTER = Workerman::class;

    public static function handle(): void
    {
        $adapter = config("emd.policy.adapter", self::DEFAULT_ADAPTER);
        (new $adapter(new Policy()))->handle();
    }
}
