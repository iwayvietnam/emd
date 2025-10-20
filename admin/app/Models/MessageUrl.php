<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Message url model.
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MessageUrl extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "message_urls";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "message_id",
        "hash",
        "url",
        "click_count",
        "last_clicked",
    ];

    /**
     * The "boot" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(
            static fn(self $model) => ($model->hash =
                $model->hash ?: Str::uuid()->toString()),
        );
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, "message_id");
    }
}
