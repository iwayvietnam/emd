<?php declare(strict_types=1);

namespace App\Models;

use App\Mail\Queue\RemoteQueue;
use App\Support\RemoteServer;
use Illuminate\Database\Eloquent\Model;

/**
 * Mail server model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailServer extends Model
{
    const POSTMAP_CMD = "sudo -S postmap lmdb:%s";
    const COPY_CMD = "sudo -S cp -f %s %s";
    const ECHO_CMD = "echo '%s'";
    const CONFIG_DIR = "/etc/postfix";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "mail_servers";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "name",
        "ip_address",
        "ssh_user",
        "ssh_port",
        "ssh_private_key",
        "ssh_public_key",
        "sudo_password",
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "ssh_private_key" => "encrypted",
            "sudo_password" => "encrypted",
        ];
    }

    public function listQueue(string $configDir = self::CONFIG_DIR): array
    {
        $remoteQueue = new RemoteQueue(
            new RemoteServer(
                $this->ip_address,
                $this->ssh_port,
                $this->ssh_user,
                $this->ssh_private_key
            ),
            $this->sudo_password,
            $configDir
        );
        return collect($remoteQueue->listQueue())
            ->map(function ($queue) {
                $queue["mail_server"] = $this->id;
                return $queue;
            })
            ->toArray();
    }

    public function queueContent(
        string $queueId,
        string $configDir = self::CONFIG_DIR
    ): string {
        $remoteQueue = new RemoteQueue(
            new RemoteServer(
                $this->ip_address,
                $this->ssh_port,
                $this->ssh_user,
                $this->ssh_private_key
            ),
            $this->sudo_password,
            $configDir
        );
        return $remoteQueue->queueContent($queueId);
    }

    public function flushQueue(
        array $queueIds = [],
        string $configDir = self::CONFIG_DIR
    ): void {
        if (!empty($queueIds)) {
            $remoteQueue = new RemoteQueue(
                new RemoteServer(
                    $this->ip_address,
                    $this->ssh_port,
                    $this->ssh_user,
                    $this->ssh_private_key
                ),
                $this->sudo_password,
                $configDir
            );
            foreach ($queueIds as $queueId) {
                $remoteQueue->flushQueue($queueId);
            }
        }
    }

    public function deleteQueue(
        array $queueIds = [],
        string $configDir = self::CONFIG_DIR
    ) {
        if (!empty($queueIds)) {
            $remoteQueue = new RemoteQueue(
                new RemoteServer(
                    $this->ip_address,
                    $this->ssh_port,
                    $this->ssh_user,
                    $this->ssh_private_key
                ),
                $this->sudo_password,
                $configDir
            );
            $remoteQueue->deleteQueue($queueIds);
        }
    }

    public function syncSenderTransports(array $transports): void
    {
        if (!empty($transports)) {
            $transportFile = config("emd.sender_transport");
            $tempFile = tempnam(sys_get_temp_dir(), "emd");
            $remoteServer = new RemoteServer(
                $this->ip_address,
                $this->ssh_port,
                $this->ssh_user,
                $this->ssh_private_key
            );
            $remoteServer->uploadContent(
                $tempFile,
                implode(PHP_EOL, $transports)
            );
            $remoteServer->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudo_password),
                    " | ",
                    sprintf(self::COPY_CMD, $tempFile, $transportFile),
                ])
            );
            $remoteServer->runCommand(
                implode([
                    sprintf(self::ECHO_CMD, $this->sudo_password),
                    " | ",
                    sprintf(self::POSTMAP_CMD, $transportFile),
                ])
            );
        }
    }
}
