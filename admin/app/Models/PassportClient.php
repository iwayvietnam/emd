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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            ...$this->casts,
            "secret" => "encrypted",
        ];
    }

    /**
     * Revoke the client instance.
     *
     * @return bool
     */
    public function revoke(): bool
    {
        return $this->forceFill(['revoked' => true])->save();
    }
}
