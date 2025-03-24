<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Attachment model.
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Attachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "attachments";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "message_id",
        "file_name",
        "file_path",
        "file_mime",
        "file_size",
    ];

    /**
     * The "boot" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(
            static fn(self $model) => Storage::delete($model->file_path)
        );
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, "message_id");
    }
}
