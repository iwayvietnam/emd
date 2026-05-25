<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * X509 private key model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class X509PrivateKey extends Model
{
    use HasFactory;

    protected $table = 'x509_private_key';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_id',
        'fingerprint',
        'key_algorithm',
        'key_strength',
        'with_password',
        'encrypted_password',
        'key_data',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'with_password' => 'boolean',
        "encrypted_password" => "encrypted",
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id')->withDefault();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(X509Certificate::class, 'private_key_id');
    }
}
