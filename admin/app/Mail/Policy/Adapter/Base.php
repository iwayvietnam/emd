<?php declare(strict_types=1);

namespace App\Mail\Policy\Adapter;

use App\Mail\Policy\Interface\AdapterInterface;
use App\Mail\Policy\Interface\PolicyInterface;
use App\Mail\Policy\PolicyRequest;
use Illuminate\Support\Facades\Log;

/**
 * Base adapter class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
abstract class Base implements AdapterInterface
{
    /**
     * Constructor
     *
     * @param PolicyInterface $policy
     * @return self
     */
    protected function __construct(private readonly PolicyInterface $policy) {}

    protected function response(string $data): string
    {
        return $this->policy
            ->check(PolicyRequest::fromData($data))
            ->getAction();
    }

    protected function onConnect(string $remoteIp, int $remotePort): void
    {
        Log::debug("Access policy {remote_ip}:{remote_port} connect.", [
            "remote_ip" => $remoteIp,
            "remote_port" => $remotePort,
        ]);
    }

    protected function onClose(string $remoteIp, int $remotePort): void
    {
        Log::debug("Access policy {remote_ip}:{remote_port} closed.", [
            "remote_ip" => $remoteIp,
            "remote_port" => $remotePort,
        ]);
    }
}
