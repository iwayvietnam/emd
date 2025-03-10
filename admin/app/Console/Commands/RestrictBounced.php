<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RestrictedRecipient;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Restrict bounced command
 * Restrict recipients which can never be delivered.
 *
 * @package  App
 * @category Console
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
#[AsCommand(name: "restrict:bounced")]
class RestrictBounced extends Command implements Isolatable
{
    const BOUNCED_STATUS_REGEX = "/to=<([^>]*)>, (.*), dsn=([^,]+), status=bounced (.*)/";
    const MAIL_LOG_FILE = "/var/log/maillog";
    const RESTRICT_ACCESS = "REJECT";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "restrict:bounced {--log-file=}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Restrict recipients which can never be delivered.";

    private static $restrictCodes = ["5.1.1", "5.2.1", "5.4.1", "5.4.4"];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->processMailLog();
        $this->info("Restrict bounced command was successful!");
        return Command::SUCCESS;
    }

    private function processMailLog(): void
    {
        $logFile =
            $this->option("log-file") ?:
            env("MAIL_LOG_FILE", self::MAIL_LOG_FILE);
        if (file_exists($logFile) && is_readable($logFile)) {
            $recipients = collect(
                array_map(
                    static fn($record) => $record['recipient'],
                    RestrictedRecipient::all()->toArray()
                )
            );
            $codes = collect(self::$restrictCodes);

            $fh = fopen($logFile, "r");
            while (!feof($fh)) {
                $line = fgets($fh);
                if (
                    preg_match(
                        self::BOUNCED_STATUS_REGEX,
                        $line ?: "",
                        $matches
                    )
                ) {
                    $recipient = $matches[1];
                    $dsn = $matches[3];

                    if (
                        !$recipients->contains($recipient) &&
                        $codes->contains($dsn)
                    ) {
                        RestrictedRecipient::create([
                            "recipient" => $recipient,
                            "verdict" => self::RESTRICT_ACCESS,
                        ]);
                        $recipients->push($recipient);
                        $this->info(
                            $recipient . " was added to restrict recipients!"
                        );
                    }
                }
            }
            fclose($fh);
        }
    }
}
