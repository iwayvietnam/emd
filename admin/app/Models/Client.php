<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Client model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Client extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "clients";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "domain_id",
        "name",
        "sender_address",
        "bcc_address",
        "description",
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, "domain_id");
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(
            static fn(self $model) => ClientAccess::where(
                "client_id",
                $model->id,
            )->delete(),
        );
    }

    public static function senderBccMaps(): array
    {
        $addresses = [];
        $senders = static::all()->pluck('bcc_address', 'sender_address');
        foreach ($senders as $sender => $bcc) {
            if (!$bcc != null && !empty(trim($bcc))) {
                $addresses[$sender] = $sender . " " . $bcc;
            }
        }
        return $addresses;
    }
}
