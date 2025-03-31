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
    const LOG_FILE = "workerman.log";
    const PID_FILE = "workerman.pid";
    const WORKER_NAME = "Access Policy Delegation";

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

        $this->worker->name = self::WORKER_NAME;
        $this->worker->count = (int) config(
            "emd.policy.server_worker",
            self::POLICY_WORKER
        );
        Worker::$logFile = storage_path("logs") . "/" . self::LOG_FILE;
        Worker::$pidFile = storage_path() . "/" . self::PID_FILE;
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
