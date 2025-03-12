<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Client access model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class ClientAccess extends Model
{
    const RATE_LIMIT_SUFFIX = 'rate-limit-counter';
    const QUOTA_LIMIT_SUFFIX = 'quota-limit-counter';
    const CACHE_KEY_SUFFIX = 'client-accesses';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "client_accesses";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "client_id",
        "policy_id",
        "sender",
        "client_ip",
        "verdict",
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, "policy_id");
    }

    public function resetRateCounter(): self
    {
        RateLimiter::resetAttempts(
            $this->limitCounterKey(ClientAccess::RATE_LIMIT_SUFFIX)
        );
        return $this;
    }

    public function resetQuotaCounter(): self
    {
        RateLimiter::resetAttempts(
            $record->limitCounterKey(ClientAccess::QUOTA_LIMIT_SUFFIX)
        );
        return $this;
    }

    public static function cachedAccesses(): array
    {
        $cacheKey = self::cacheKey();
        $accesses = Cache::get($cacheKey, []);
        if (empty($accesses)) {
            foreach (static::all() as $model) {
                $accesses[$model->sender][$model->client_ip] = [
                    "policy" => [
                        "name" => $model->policy->name,
                        "quota_limit" => $model->policy->quota_limit,
                        "quota_period" => $model->policy->quota_period,
                        "rate_limit" => $model->policy->rate_limit,
                        "rate_period" => $model->policy->rate_period,
                    ],
                    "client" => $model->client->name,
                    "verdict" => $model->verdict,
                ];
            }
            Cache::put($cacheKey, $accesses);
        }
        return $accesses;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::cacheKey());
    }

    private function limitCounterKey(string $suffix): string
    {
        return sha1($this->sender . "|" . $this->client_ip . "|" . $suffix);
    }

    private static function cacheKey(): string
    {
        return sha1(static::class . "|" . static::CACHE_KEY_SUFFIX);
    }
}
