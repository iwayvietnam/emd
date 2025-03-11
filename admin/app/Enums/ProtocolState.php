<?php declare(strict_types=1);

namespace App\Enums;

/**
 * Policy protocol state enum
 *
 * @package  App
 * @category Enums
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
enum ProtocolState: string
{
    case Connect = "CONNECT";
    case Ehlo = "EHLO";
    case Helo = "HELO";
    case Mail = "MAIL";
    case Rcpt = "RCPT";
    case Data = "DATA";
    case EndOfMessage = "END-OF-MESSAGE";
    case Vrfy = "VRFY";
    case Etrn = "ETRN";
}
