<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sender transport model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class SenderTransport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "sender_transports";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "client_id",
        "transport_id",
        "sender",
        "transport",
    ];
}
