<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * X509 certificate model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class X509Certificate extends Model
{
    use HasFactory;

    protected $table = 'x509_certificates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_id',
        'private_key_id',
        'signing_request_id',
        'serial_number',
        'subject_dn',
        'issuer_dn',
        'not_before',
        'not_after',
        'certificate_data',
        'created_by',
        'updated_by',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id')->withDefault();
    }

    public function privateKey(): BelongsTo
    {
        return $this->belongsTo(X509PrivateKey::class, 'private_key_id')->withDefault();
    }

    public function csr(): BelongsTo
    {
        return $this->belongsTo(X509SigningRequest::class, 'signing_request_id')->withDefault();
    }
}
