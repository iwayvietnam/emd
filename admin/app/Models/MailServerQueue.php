<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

/**
 * Mail server queue model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MailServerQueue extends Model
{
    use Sushi;

    public function getRows(): array
    {
        $formState = session()->get(MailServerQueue::class);
        $server = MailServer::find($formState["mail_server"] ?? 0);
        $queues = collect(
            $server?->listQueue(
                $formState["config_dir"] ?? MailServer::CONFIG_DIR
            ) ?? []
        );

        $sender = $formState["sender"] ?? "";
        if (!empty($sender)) {
            $queues = $queues->filter(
                fn($queue) => str($queue["sender"])->contains($sender)
            );
        }

        $recipient = $formState["recipient"] ?? "";
        if (!empty($recipient)) {
            $queues = $queues->filter(
                fn($queue) => str($queue["recipients"])->contains($recipient)
            );
        }

        return array_values($queues->toArray());
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
