<?php declare(strict_types=1);

namespace App\Models;

use Laravel\Passport\Client;

/**
 * Passport client model class
 *
 * @package  App
 * @category Models
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PassportClient extends Client
{
    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(static function (self $model) {
            $model->encrypted_secret = $model->plainSecret;
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [...$this->casts, "encrypted_secret" => "encrypted"];
    }

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization()
    {
        return $this->firstParty();
    }

    /**
     * Revoke the client instance.
     *
     * @return bool
     */
    public function revoke(): bool
    {
        return $this->forceFill(["revoked" => true])->save();
    }
}
