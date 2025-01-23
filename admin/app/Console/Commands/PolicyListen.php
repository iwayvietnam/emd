<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\Policy\Service;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Policy listen command class
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "policy:listen")]
class PolicyListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "policy:listen {argument=start}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Start listening policy service as a daemon";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Service::listen();
        return Command::SUCCESS;
    }
}
