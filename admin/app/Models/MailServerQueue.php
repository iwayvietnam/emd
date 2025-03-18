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
        $server = MailServer::find($formState['mail_server'] ?? 0);
        $queues = collect($server?->listQueue() ?? []);
        $sender = $formState['sender'] ?? '';
        $recipient = $formState['recipient'] ?? '';
        if (!empty($sender) || !empty($recipient)) {
            $queues = $queues->filter(function ($queue) use ($sender, $recipient) {
                return str($queue['sender'])->contains([$sender, $recipient]);
            });
        }
        return $queues;
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
