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
     * @param PolicyInterface $policy
     * @return self
     */
    public function __construct(PolicyInterface $policy)
    {
        parent::__construct($policy);

        $this->worker = new Worker(
            implode([
                "tcp://",
                config("emd.policy.listen_host", self::LISTEN_HOST),
                ":",
                config("emd.policy.listen_port", self::LISTEN_PORT),
            ])
        );

        $this->worker->name = config(
            "emd.policy.server_name",
            self::POLICY_NAME
        );
        $this->worker->count = (int) config(
            "emd.policy.server_worker",
            self::POLICY_WORKER
        );
        Worker::$logFile = storage_path("logs") . "/workerman.log";
        Worker::$pidFile = storage_path() . "/workerman.pid";
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $this->worker->onConnect = fn(
            Connection $connection
        ) => $this->onConnect(
            $connection->getRemoteAddress(),
            $connection->getRemotePort()
        );

        $this->worker->onMessage = fn(
            Connection $connection,
            string $data
        ) => $connection->close($this->response($data) . PHP_EOL . PHP_EOL);

        $this->worker->onClose = fn(Connection $connection) => $this->onClose(
            $connection->getRemoteAddress(),
            $connection->getRemotePort()
        );

        Worker::runAll();
    }
}
