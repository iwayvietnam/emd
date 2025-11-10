<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Restricted recipient model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class RestrictedRecipient extends Model
{
    const CACHE_KEY_SUFFIX = "restricted-recipients";
    const CACHE_EXPIRES = 3600;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "restricted_recipients";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["recipient", "verdict"];

    public static function cachedRecipients(string $cacheStore): array
    {
        $cacheKey = self::cacheKey();
        $recipients = Cache::store($cacheStore)->get($cacheKey, []);
        if (empty($recipients)) {
            $recipients = static::all()->pluck("verdict", "recipient")->all();
            Cache::store($cacheStore)->put($cacheKey, $recipients, self::CACHE_EXPIRES);
        }
        return $recipients;
    }

    public static function clearCache(string $cacheStore): void
    {
        Cache::store($cacheStore)->forget(self::cacheKey());
    }

    protected static function boot(): void
    {
        parent::boot();
        static::created(static fn() => static::clearCache());
    }

    private static function cacheKey(): string
    {
        return sha1(implode([self::class, self::CACHE_KEY_SUFFIX]));
    }

    public static function recipientRestrictions(): array
    {
        $accesses = [];
        foreach (static::all() as $item) {
            $accesses[$item->recipient] =
                $item->recipient . " " . $item->verdict;
        }
        return $accesses;
    }
}
