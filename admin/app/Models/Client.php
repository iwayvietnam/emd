<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Client model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Client extends Model
{
    const RATE_LIMIT_SUFFIX = "rate-limit-counter";
    const QUOTA_LIMIT_SUFFIX = "quota-limit-counter";

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
                $model->id
            )->delete()
        );
    }

    public function resetRateCounter(): self
    {
        RateLimiter::resetAttempts(
            $this->limitCounterKey(self::RATE_LIMIT_SUFFIX)
        );
        return $this;
    }

    public function resetQuotaCounter(): self
    {
        RateLimiter::resetAttempts(
            $this->limitCounterKey(self::QUOTA_LIMIT_SUFFIX)
        );
        return $this;
    }

    private function limitCounterKey(string $suffix): string
    {
        return sha1($this->sender_address . "|" . $suffix);
    }
}
