<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\LimitPeriod;
use App\Models\ClientAccess;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
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
        $day = date("D");
        $date = (int) date("j");

        foreach (ClientAccess::all() as $record) {
            $period = LimitPeriod::tryFrom($record->policy->quota_period);
            switch ($period) {
                case LimitPeriod::PerWeek:
                    if ($day === "Mon") {
                        $record->resetQuotaCounter();
                    }
                    break;
                case LimitPeriod::PerMonth:
                    if ($date === 1) {
                        $record->resetQuotaCounter();
                    }
                    break;
                default:
                    $record->resetQuotaCounter();
                    break;
            }

            $period = LimitPeriod::tryFrom($record->policy->rate_period);
            switch ($period) {
                case LimitPeriod::PerWeek:
                    if ($day === "Mon") {
                        $record->resetRateCounter();
                    }
                    break;
                case LimitPeriod::PerMonth:
                    if ($date === 1) {
                        $record->resetRateCounter();
                    }
                    break;
                default:
                    $record->resetRateCounter();
                    break;
            }
        }
    }
}
