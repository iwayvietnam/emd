<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * Message failure model.
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MessageFailure extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "message_failures";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "message_id",
        "severity",
        "description",
        "failed_at",
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, "message_id");
    }
}
