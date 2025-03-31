<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Mail\Policy\Adapter\OpenSwoole;
use App\Mail\Policy\Adapter\Swoole;
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
    public static function handle(): void
    {
        $adapter = match (true) {
            class_exists(\OpenSwoole\Server::class) => new OpenSwoole(
                new Policy()
            ),
            class_exists(\Swoole\Server::class) => new Swoole(new Policy()),
            default => new Workerman(new Policy()),
        };
        $adapter->handle();
    }
}
