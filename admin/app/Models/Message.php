<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Message model.
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Message extends Model
{
    const HREF_PATTERN = '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "messages";

    protected $casts = [
        "headers" => "array",
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "user_id",
        "hash",
        "from_name",
        "from_email",
        "reply_to",
        "recipient",
        "headers",
        "message_id",
        "subject",
        "content",
        "ip_address",
        "open_count",
        "click_count",
        "sent_at",
        "last_opened",
        "last_clicked",
    ];

    public array $uploads = [];

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

        static::created(static function (self $model) {
            foreach ($model->uploads as $upload) {
                Attachment::create([
                    "message_id" => $model->id,
                    "file_name" => basename($upload),
                    "file_path" => $upload,
                    "file_mime" => Storage::mimeType($upload),
                    "file_size" => Storage::size($upload),
                ]);
            }

            preg_match_all(self::HREF_PATTERN, $model->content, $matches);
            $urls = array_filter(
                $matches["href"] ?? [],
                static fn($href) => filter_var($href, FILTER_VALIDATE_URL),
            );
            foreach ($urls as $url) {
                MessageUrl::create([
                    "message_id" => $model->id,
                    "url" => $url,
                ]);
            }
        });

        static::deleting(static function (self $model) {
            $model->attachments()->delete();
            $model->devices()->delete();
            $model->failures()->delete();
            $model->urls()->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(MessageDevice::class);
    }

    public function failures(): HasMany
    {
        return $this->hasMany(MessageFailure::class)->orderBy("failed_at");
    }

    public function urls(): HasMany
    {
        return $this->hasMany(MessageUrl::class);
    }
}
