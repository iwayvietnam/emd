<?php declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Limit period enum
 *
 * @package  App
 * @category Enums
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum LimitPeriod: int implements HasLabel
{
    case PerMinute = 60;
    case PerHour = 3600;
    case PerDay = 86400;
    case PerWeek = 604800;

    public function getLabel(): string
    {
        return match ($this) {
            self::PerMinute => __("Per Minute"),
            self::PerHour => __("Per Hour"),
            self::PerDay => __("Per Day"),
            self::PerWeek => __("Per Week"),
        };
    }
}
