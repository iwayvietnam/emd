<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dkim key model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DkimKey extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dkim_keys";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "domain_id",
        "domain",
        "selector",
        "key_bits",
        "private_key",
        "dns_record",
    ];
}
