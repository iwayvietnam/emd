<?php declare(strict_types=1);

namespace App\Enum;

/**
 * Policy listen enum
 *
 * @package  App
 * @category Enum
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum PolicyListen: string
{
    case START = "start";
    case STOP = "stop";
    case RELOAD = "reload";
    case STATUS = "status";
}
