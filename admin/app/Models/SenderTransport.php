<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function belongTransport(): BelongsTo
    {
        return $this->belongsTo(Transport::class, "transport_id");
    }

    public static function transports(): array
    {
        return static::all()->map(
            static fn ($item) =>  $item->sender . " " . $item->transport
        )->toArray();
    }
}
