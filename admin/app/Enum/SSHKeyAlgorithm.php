<?php declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

/**
 * SSH key algorithm enum
 *
 * @package  App
 * @category Enum
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum SSHKeyAlgorithm: int implements HasLabel
{
    case Rsa = 1;
    case NistP256 = 2;
    case NistP384 = 3;
    case NistP521 = 4;
    case Ed25519 = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Rsa => 'RSA',
            self::NistP256  => 'NIST Curve P-256',
            self::NistP384  => 'NIST Curve P-384',
            self::NistP521  => 'NIST Curve P-521',
            self::Ed25519  => 'Edwards Curve 25519',
        };
    }
}
