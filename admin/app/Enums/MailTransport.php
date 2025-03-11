<?php declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Mail transport enum
 *
 * @package  App
 * @category Enums
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum MailTransport: string implements HasLabel
{
    case Local = "local";
    case Virtual = "virtual";
    case Lmtp = "lmtp";
    case Relay = "relay";
    case Smtp = "smtp";

    public function getLabel(): string
    {
        return match ($this) {
            self::Local => __("Local"),
            self::Virtual => __("Virtual"),
            self::Lmtp => __("Lmtp"),
            self::Relay => __("Relay"),
            self::Smtp => __("Smtp"),
        };
    }
}
