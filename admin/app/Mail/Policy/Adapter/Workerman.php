<?php declare(strict_types=1);

namespace App\Mail\Policy\Adapter;

use App\Mail\Policy\Interface\PolicyInterface;
use Workerman\Worker;
use Workerman\Connection\ConnectionInterface as Connection;

/**
 * Workerman adapter class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Workerman extends Base
{
    /**
     * Server worker
     *
     * @var Worker
     */
    private readonly Worker $worker;

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->worker = new Worker(
            implode([
                "tcp://",
                config("policy.listen_host", self::LISTEN_HOST),
                ":",
                config("policy.listen_port", self::LISTEN_PORT),
            ])
        );

        $this->worker->name = config("policy.server_name", self::POLICY_NAME);
        $this->worker->count = config(
            "policy.server_worker",
            self::POLICY_WORKER
        );
        Worker::$daemonize = config("policy.daemonize", self::POLICY_DAEMONIZE);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(PolicyInterface $policy): void
    {
        $this->worker->onConnect = function (Connection $connection) {
            $this->onConnect(
                $connection->getRemoteAddress(),
                $connection->getRemotePort()
            );
        };

        $this->worker->onMessage = function (
            Connection $connection,
            string $data
        ) use ($policy) {
            $connection->send($this->response($policy, $data));
        };

        $this->worker->onClose = function (Connection $connection) {
            $this->onClose(
                $connection->getRemoteAddress(),
                $connection->getRemotePort()
            );
        };

        Worker::runAll();
    }
}
