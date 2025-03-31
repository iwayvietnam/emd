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
    protected static function boot(): void
    {
        parent::boot();

        static::creating(
            static fn(self $model) => $model->encrypted_secret = $model->secret
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            ...$this->casts,
            "encrypted_secret" => "encrypted",
        ];
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
