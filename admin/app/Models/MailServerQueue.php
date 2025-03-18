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
        return $server?->listQueue() ?? [];
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
