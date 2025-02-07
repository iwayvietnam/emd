<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Enum\AccessVerdict;
use App\Enum\ProtocolState;
use App\Mail\Policy\Interface\PolicyInterface;
use App\Mail\Policy\Interface\RequestInterface;
use App\Mail\Policy\Interface\ResponseInterface;
use App\Models\ClientAccess;
use App\Models\RestrictedRecipient;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Policy class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class Policy implements PolicyInterface
{
    private array $clientAccesses = [];

    /**
     * {@inheritdoc}
     */
    public function check(RequestInterface $request): ResponseInterface
    {
        $this->clientAccesses = ClientAccess::cachedAccesses();
        $state = ProtocolState::tryFrom($request->getProtocolState());
        switch ($state) {
            case ProtocolState::Rcpt:
                if ($this->isRejected($request)) {
                    logger()->error("Client {sender}:{address} is rejected.", [
                        "sender" => $request->getSender(),
                        "address" => $request->getClientAddress(),
                    ]);
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Client access is not allowed!"
                    );
                }
                if ($this->rateIsExceeded($request)) {
                    logger()->error("Rate limit of client {sender}:{address} is exceeded", [
                        "sender" => $request->getSender(),
                        "address" => $request->getClientAddress(),
                    ]);
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Rate limit is exceeded. Retry later!"
                    );
                }
                if ($this->recipientIsRestricted($request)) {
                    logger()->error("Recipient {recipient} is restricted.", [
                        "recipient" => $request->getRecipient(),
                    ]);
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Recipient address is restricted!"
                    );
                }
                return new PolicyResponse(AccessVerdict::Ok);
            case ProtocolState::EndOfMessage:
                if ($this->quotaIsExceeded($request)) {
                    logger()->error("Quota limit of client {sender}:{address} is exceeded.", [
                        "sender" => $request->getSender(),
                        "address" => $request->getClientAddress(),
                    ]);
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Quota limit is exceeded. Retry later!"
                    );
                }
                return new PolicyResponse(AccessVerdict::Ok);
            default:
                logger()->error("Protocol state {state} is invalid.", [
                    "state" => $state,
                ]);
                return new PolicyResponse(
                    AccessVerdict::Reject,
                    "Invalid protocol state!"
                );
        }
    }

    private function isRejected(RequestInterface $request): bool
    {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($this->clientAccesses[$sender][$address]["verdict"])) {
            $verdict = $this->clientAccesses[$sender][$address]["verdict"];
            return AccessVerdict::tryFrom($verdict) === AccessVerdict::Reject;
        } else {
            return true;
        }
    }

    private function rateIsExceeded(RequestInterface $request): bool
    {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($this->clientAccesses[$sender][$address]["policy"])) {
            $counterKey = self::counterKey(
                $request,
                ClientAccess::RATE_LIMIT_SUFFIX
            );
            $policy = $this->clientAccesses[$sender][$address]["policy"];
            if (!empty($policy) && !empty($policy["rate_limit"])) {
                if (
                    RateLimiter::tooManyAttempts(
                        $counterKey,
                        $policy["rate_limit"]
                    )
                ) {
                    return true;
                }
                RateLimiter::hit($counterKey, (int) $policy["rate_period"]);
            }
        }
        return false;
    }

    private function quotaIsExceeded(RequestInterface $request): bool
    {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($this->clientAccesses[$sender][$address]["policy"])) {
            $counterKey = self::counterKey(
                $request,
                ClientAccess::QUOTA_LIMIT_SUFFIX
            );
            $policy = $this->clientAccesses[$sender][$address]["policy"];
            if (!empty($policy) && !empty($policy["quota_limit"])) {
                if (
                    RateLimiter::tooManyAttempts(
                        $counterKey,
                        $policy["quota_limit"]
                    )
                ) {
                    return true;
                }
                RateLimiter::increment(
                    $counterKey,
                    (int) $policy["quota_period"],
                    $request->getSize()
                );
            }
        }

        return false;
    }

    private function recipientIsRestricted(RequestInterface $request): bool
    {
        $restrictedRecipients = RestrictedRecipient::cachedRecipients();
        return AccessVerdict::tryFrom(
            $restrictedRecipients[$request->getRecipient()] ?? ""
        ) === AccessVerdict::Reject;
    }

    private static function counterKey(
        RequestInterface $request,
        string $suffix
    ): string {
        return sha1(
            $request->getSender() .
                "|" .
                $request->getClientAddress() .
                "|" .
                $suffix
        );
    }
}
