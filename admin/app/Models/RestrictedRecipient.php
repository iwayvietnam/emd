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
    const CACHE_KEY_SUFFIX = 'restricted-recipients';

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

    public static function cachedRecipients(): array
    {
        $cacheKey = self::cacheKey();
        $recipients = Cache::get($cacheKey, []);
        if (empty($recipients)) {
            $recipients = static::all()->pluck(
                "recipient", "verdict"
            )->all();
            Cache::put(
                $cacheKey, $recipients
            );
        }
        return $recipients;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::cacheKey());
    }

    private static function cacheKey(): string
    {
        return sha1(static::class . "|" . static::CACHE_KEY_SUFFIX);
    }
}
