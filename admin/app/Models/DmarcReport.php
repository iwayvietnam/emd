<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dmarc report model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DmarcReport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dmarc_reports";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "report_id",
        "org_name",
        "org_email",
        "extra_contact",
        "date_begin",
        "date_end",
        "domain",
        "adkim",
        "aspf",
        "policy",
        "subdomain_policy",
        "percentage",
        "is_forensic",
    ];

    protected $casts = [
        'is_forensic' => 'boolean',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(DmarcReportRecord::class, 'report_id', 'report_id');
    }
}
