<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Domain extends Model
{
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
        "description",
        "dns_mx_record",
        "dns_dmarc_record",
        "dns_ptr_record",
        "dns_spf_record",
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, "domain_id", "id");
    }
}
