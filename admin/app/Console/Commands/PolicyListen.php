<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\Policy\Service;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Policy listen command class
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "policy:listen")]
class PolicyListen extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "policy:listen {listen=start}";

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
        Service::handle();
        return Command::SUCCESS;
    }
}
