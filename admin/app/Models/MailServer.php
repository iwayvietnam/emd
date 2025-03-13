<?php declare(strict_types=1);

namespace App\Models;

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
    const POSTMAP_COMMAND = "sudo -S postmap lmdb:%s";
    const COPY_COMMAND = "sudo -S cp -f %s %s";
    const ECHO_COMMAND = "echo '%s'";

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
                    sprintf(self::ECHO_COMMAND, $this->sudo_password),
                    " | ",
                    sprintf(self::COPY_COMMAND, $tempFile, $transportFile),
                ])
            );
            $remoteServer->runCommand(
                implode([
                    sprintf(self::ECHO_COMMAND, $this->sudo_password),
                    " | ",
                    sprintf(self::POSTMAP_COMMAND, $transportFile),
                ])
            );
        }
    }
}
