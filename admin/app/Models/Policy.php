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

        static::deleting(static function (self $model) {
            ClientAccess::where("policy_id", $model->id)->delete();
        });
    }
}
