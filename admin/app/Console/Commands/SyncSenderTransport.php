<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MailServer;
use App\Models\SenderTransport;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Sync sender transport command
 * Synchronize sender transports to mail server.
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "sync:tranport")]
class SyncSenderTransport extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sync:tranport {server}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Synchronize sender transports to mail server.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->syncTransport($this->argument("server"));
        return Command::SUCCESS;
    }

    private function syncTransport(string $server): void
    {
        if (filter_var($server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $server = MailServer::firstWhere("ip_address", $server);
        } else {
            $server = MailServer::firstWhere("name", $server);
        }
        if ($server->id) {
            $transports = SenderTransport::transports();
            if (!empty($transports)) {
                $server->syncSenderTransports($transports);
                $this->info(
                    "Sender transports were synchronized to mail server successfully!",
                );
            } else {
                $this->info(
                    "There are no sender transports to sync to mail server!",
                );
            }
        } else {
            $this->info("Mail server does not exists!");
        }
    }
}
