<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\Policy\Policy;
use App\Mail\Policy\PolicyRequest;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Policy delegate command class
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "policy:delegate")]
class PolicyDelegate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "policy:delegate";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to handle policy delegation";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $policy = new Policy()
        echo $policy->check(PolicyRequest::fromData(self::readInput()))
            ->getAction() .
            PHP_EOL .
            PHP_EOL;
        return Command::SUCCESS;
    }

    private static function readInput(): string
    {
        $lines = [];
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            if ($line === false || trim($line) === "") {
                break;
            }
            $lines[] = trim($line);
        }
        return implode(PHP_EOL, $lines);
    }
}
