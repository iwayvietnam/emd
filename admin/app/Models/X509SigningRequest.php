<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * X509 signing request model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class X509SigningRequest extends Model
{
    use HasFactory;

    protected $table = 'x509_signing_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_id',
        'private_key_id',
        'cn',
        'country',
        'state',
        'locality',
        'organization',
        'organization_unit',
        'csr_data',
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

    public function certificates(): HasMany
    {
        return $this->hasMany(X509Certificate::class, 'signing_request_id');
    }
}
