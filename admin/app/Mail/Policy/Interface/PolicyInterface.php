<?php declare(strict_types=1);

namespace App\Mail\Policy\Interface;

/**
 * Policy interface
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
interface PolicyInterface
{
    /**
     * Check access policy
     *
     * @return RequestInterface $request
     * @return ResponseInterface
     */
    function check(RequestInterface $request): ResponseInterface;
}
