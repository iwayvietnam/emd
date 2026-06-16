<?php declare(strict_types=1);

namespace App\Workers;

use Spiral\RoadRunnerLaravel\WorkerInterface;
use Spiral\RoadRunnerLaravel\WorkerOptionsInterface;

class PolicyWorker implements WorkerInterface
{
	public function start(WorkerOptionsInterface $options): void
	{
	}
}