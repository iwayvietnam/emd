<?php declare(strict_types=1);

namespace App\Enums;

/**
 * Access verdict enum
 *
 * @package  App
 * @category Enums
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum AccessVerdict: string
{
    case Ok = "OK";
    case Reject = "REJECT";
    case Defer = "DEFER";
    case DeferIfReject = "DEFER_IF_REJECT";
    case DeferIfPermit = "DEFER_IF_PERMIT";
    case Bcc = "BCC";
    case Discard = "DISCARD";
    case Dunno = "DUNNO";
    case Filter = "FILTER";
    case Hold = "HOLD";
    case Prepend = "PREPEND";
    case Redirect = "REDIRECT";
    case Info = "INFO";
    case Warn = "WARN";
}
