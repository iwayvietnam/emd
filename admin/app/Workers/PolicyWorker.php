<?php declare(strict_types=1);

namespace App\Workers;

use App\Mail\Policy\Policy;
use App\Mail\Policy\PolicyRequest;
use Illuminate\Support\Facades\Log;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Tcp\TcpWorker;
use Spiral\RoadRunner\Tcp\TcpResponse;
use Spiral\RoadRunner\Tcp\TcpEvent;
use Spiral\RoadRunnerLaravel\WorkerInterface;
use Spiral\RoadRunnerLaravel\WorkerOptionsInterface;

/**
 * Policy worker
 *
 * @package  App
 * @category Workers
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PolicyWorker implements WorkerInterface
{
    const WORKER_NAME = "policy";

    public function start(WorkerOptionsInterface $options): void
    {
        $tcpWorker = new TcpWorker(Worker::create());
        while ($request = $tcpWorker->waitRequest()) {
            try {
                if ($request->event === TcpEvent::Connected) {
                    Log::debug(
                        "Access policy {remote_ip} connect at {time}.",
                        [
                            "remote_ip" => $request->getRemoteAddress(),
                            "time" => microtime(true),
                        ],
                    );
                }

                if ($request->event === TcpEvent::Data) {
                    $start = hrtime(true);
                    var $policy = new Policy();
                    var $action = $policy->check(
                        PolicyRequest::fromData($request->getBody())
                    )->getAction();
                    Log::debug(
                        "Policy service checked client access in {elapsed_time} ms.",
                        [
                            "elapsed_time" => (hrtime(true) - $start) / 1_000_000,
                        ],
                    );
                    $tcpWorker->respond($action . PHP_EOL . PHP_EOL, TcpResponse::RespondClose);
                }

                if ($request->event === TcpEvent::Close) {
                    Log::debug(
                        "Access policy {remote_ip} closed at {time}.",
                        [
                            "remote_ip" => $request->getRemoteAddress(),
                            "time" => microtime(true),
                        ],
                    );
                }

                if ($request->event === TcpEvent::Unknown) {
                    $tcpWorker->respond("", TcpResponse::RespondClose);
                }
            } catch (\Throwable $e) {
                $tcpWorker->respond("Something went wrong\r\n", TcpResponse::RespondClose);
                Log::error((string)$e);
            }
        }
    }
}
