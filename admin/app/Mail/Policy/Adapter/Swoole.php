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
    /**
     * Swoole server
     *
     * @var Server
     */
    private readonly Server $server;

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->server = new Server(
            config("policy.listen_host", self::LISTEN_HOST),
            (int) config("policy.listen_port", self::LISTEN_PORT)
        );
        $this->server->set([
            "worker_num" => (int) config("policy.server_worker", self::POLICY_WORKER),
            "daemonize" => (bool) config("policy.daemonize", self::POLICY_DAEMONIZE),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(PolicyInterface $policy): void
    {
        $this->server->on("connect", function (Server $server, int $fd) {
            $info = $server->getClientInfo($fd);
            $this->onConnect($info["remote_ip"], $info["remote_port"]);
        });

        $this->server->on("receive", fn (
            Server $server,
            int $fd,
            int $reactorId,
            string $data
        ) => $server->send($fd, $this->response($policy, $data) . PHP_EOL . PHP_EOL));

        $this->server->on("close", function (Server $server, int $fd) {
            $info = $server->getClientInfo($fd);
            $this->onClose($info["remote_ip"], $info["remote_port"]);
        });

        $this->server->start();
    }
}
