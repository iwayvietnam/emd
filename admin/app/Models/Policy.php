<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Policy recipient model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Policy extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "policies";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "name",
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

        static::deleting(
            static fn(self $model) => ClientAccess::where(
                "policy_id",
                $model->id
            )->delete()
        );
    }
}
