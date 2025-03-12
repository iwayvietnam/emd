<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ClientAccess;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * ResetLimitCounter command
 * Reset client's quota & rate limit counter.
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "reset:limit")]
class ResetLimitCounter extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "reset:limit";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Reset client's quota & rate limit counter.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->resetLimitCounter();
        $this->info("Reset limit counter command was successful!");
        return Command::SUCCESS;
    }

    private function resetLimitCounter(): void
    {
        foreach (ClientAccess::all() as $record) {
            RateLimiter::resetAttempts(
                $record->limitCounterKey(ClientAccess::RATE_LIMIT_SUFFIX)
            );
            RateLimiter::resetAttempts(
                $record->limitCounterKey(ClientAccess::QUOTA_LIMIT_SUFFIX)
            );
        }
    }
}
