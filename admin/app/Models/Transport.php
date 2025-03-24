<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Transport model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Transport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "transports";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["name", "transport", "nexthop"];

    protected static function boot(): void
    {
        parent::boot();

        static::updating(static function (self $model) {
            if ($model->isDirty("transport") || $model->isDirty("nexthop")) {
                SenderTransport::where("transport_id", $model->id)->update([
                    "transport" => $model->transport . ":" . $model->nexthop,
                ]);
            }
        });

        static::deleting(
            static fn(self $model) => SenderTransport::where(
                "transport_id",
                $model->id
            )->delete()
        );
    }
}
