<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Domain model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Domain extends Model
{
    const RATE_LIMIT_SUFFIX = "domain-rate-limit";
    const QUOTA_LIMIT_SUFFIX = "domain-quota-limit";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "domains";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "name",
        "email",
        "organization",
        "description",
        "quota_limit",
        "quota_period",
        "rate_limit",
        "rate_period",
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(static function (self $model) {
            if ($model->quota_limit == 0) {
                $model->quota_period = 0;
            }

            if ($model->rate_limit == 0) {
                $model->rate_period = 0;
            }
        });
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, "domain_id", "id");
    }

    public function viewRateCounter(): array
    {
        return $this->viewLimitCounter(
            $this->limitCounterKey(self::RATE_LIMIT_SUFFIX),
            $this->rate_limit,
        );
    }

    public function viewQuotaCounter(): array
    {
        return $this->viewLimitCounter(
            $this->limitCounterKey(self::QUOTA_LIMIT_SUFFIX),
            $this->quota_limit,
        );
    }

    public function clearRateCounter(): self
    {
        RateLimiter::clear($this->limitCounterKey(self::RATE_LIMIT_SUFFIX));
        return $this;
    }

    public function clearQuotaCounter(): self
    {
        RateLimiter::clear($this->limitCounterKey(self::QUOTA_LIMIT_SUFFIX));
        return $this;
    }

    private function viewLimitCounter(
        string $counterKey,
        int $maxAttempts = 0,
    ): array {
        return [
            "attempts" => RateLimiter::attempts($counterKey),
            "availableIn" => RateLimiter::availableIn($counterKey),
            "maxAttempts" => $maxAttempts,
            "remaining" => RateLimiter::remaining($counterKey, $maxAttempts),
        ];
    }

    private function limitCounterKey(string $suffix): string
    {
        return sha1(implode([$this->name, $suffix]));
    }
}
