<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MailServer;
use App\Models\RestrictedRecipient;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Sync recipient restriction command
 * Synchronize restricted recipients to mail server.
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "sync:restriction")]
class SyncRecipientRestriction extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sync:restriction {server}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Synchronize restricted recipients to mail server.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->syncRestriction($this->argument("server"));
        return Command::SUCCESS;
    }

    private function syncRestriction(string $server): void
    {
        if (filter_var($server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $server = MailServer::firstWhere("ip_address", $server);
        } else {
            $server = MailServer::firstWhere("name", $server);
        }
        if ($server->id) {
            $restrictions = RestrictedRecipient::recipientRestrictions();
            if (!empty($restrictions)) {
                $server->syncRecipientRestrictions($restrictions);
                $this->info(
                    "Restrict recipients were synchronized to mail server successfully!",
                );
            } else {
                $this->info(
                    "There are no restrict recipients to sync to mail server!",
                );
            }
        } else {
            $this->info("Mail server does not exists!");
        }
    }
}
