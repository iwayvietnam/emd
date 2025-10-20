<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dmarc report record model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class DmarcReportRecord extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dmarc_report_records";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "report_id",
        "source_ip",
        "count",
        "header_from",
        "envelope_from",
        "envelope_to",
        "disposition",
        "dkim",
        "spf",
        "reason",
        "dkim_domain",
        "dkim_selector",
        "dkim_result",
        "spf_domain",
        "spf_result",
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DmarcReport::class, "report_id", "report_id");
    }
}
