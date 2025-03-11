<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Enums\AccessVerdict;
use App\Mail\Policy\Interface\ResponseInterface;

/**
 * Policy response class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PolicyResponse implements ResponseInterface
{
    /**
     * Constructor
     *
     * @param AccessVerdict $verdict
     * @param string $message
     * @return self
     */
    public function __construct(
        private readonly AccessVerdict $verdict,
        private readonly string $message = ""
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAction(): string
    {
        return trim(implode([
            "action=",
            $this->verdict->value,
            " ",
            $this->message,
        ]));
    }
}
