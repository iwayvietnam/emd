<?php declare(strict_types=1);

namespace App\Workers;

use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Tcp\TcpWorker;
use Spiral\RoadRunner\Tcp\TcpResponse;
use Spiral\RoadRunner\Tcp\TcpEvent;
use Spiral\RoadRunnerLaravel\WorkerInterface;
use Spiral\RoadRunnerLaravel\WorkerOptionsInterface;

class PolicyWorker implements WorkerInterface
{
    public function start(WorkerOptionsInterface $options): void
    {
        $worker = Worker::create();
        $tcpWorker = new TcpWorker($worker);
        while ($request = $tcpWorker->waitRequest()) {
            try {
                if ($request->event === TcpEvent::Connected) {
                }

                if ($request->event === TcpEvent::Data) {
                }

                if ($request->event === TcpEvent::Close) {
                }
            } catch (\Throwable $e) {
                $tcpWorker->respond("Something went wrong\r\n", TcpResponse::RespondClose);
                $worker->error((string)$e);
            }
        }
    }
}
