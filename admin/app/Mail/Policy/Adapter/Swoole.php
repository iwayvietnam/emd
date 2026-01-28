<?php declare(strict_types=1);

namespace App\Mail\Policy\Adapter;

use App\Mail\Policy\Interface\PolicyInterface;
use Swoole\Server;

/**
 * Swoole adapter class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Swoole extends Base
{
    const LOG_FILE = "swoole.log";
    const PID_FILE = "swoole.pid";

    /**
     * Swoole server
     *
     * @var Server
     */
    private readonly Server $server;

    /**
     * Constructor
     *
     * @param PolicyInterface $policy
     * @return self
     */
    public function __construct(PolicyInterface $policy)
    {
        parent::__construct($policy);

        $this->server = new Server(
            config("emd.policy.listen_host", self::LISTEN_HOST),
            (int) config("emd.policy.listen_port", self::LISTEN_PORT),
        );

        $this->server->set([
            "worker_num" => (int) config(
                "emd.policy.server_worker",
                self::POLICY_WORKER,
            ),
            "debug_mode" => (bool) config("app.debug"),
            "log_file" => storage_path("logs") . "/" . self::LOG_FILE,
            "log_level" => (bool) config("app.debug")
                ? SWOOLE_LOG_DEBUG
                : SWOOLE_LOG_INFO,
            "log_rotation" => SWOOLE_LOG_ROTATION_DAILY,
            "pid_file" => storage_path() . "/" . self::PID_FILE,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $this->server->on("connect", function (Server $server, int $fd) {
            $info = $server->getClientInfo($fd);
            $this->onConnect($info["remote_ip"], $info["remote_port"]);
        });

        $this->server->on("receive", function (
            Server $server,
            int $fd,
            int $reactorId,
            string $data,
        ) {
            $server->send($fd, $this->response($data) . PHP_EOL . PHP_EOL);
            $server->close($fd);
        });

        $this->server->on("close", function (Server $server, int $fd) {
            $info = $server->getClientInfo($fd);
            $this->onClose($info["remote_ip"], $info["remote_port"]);
        });

        $this->server->start();
    }
}
