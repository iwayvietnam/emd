<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Enums\AccessVerdict;
use App\Enums\ProtocolState;
use App\Mail\Policy\Interface\PolicyInterface;
use App\Mail\Policy\Interface\RequestInterface;
use App\Mail\Policy\Interface\ResponseInterface;
use App\Models\ClientAccess;
use App\Models\RestrictedRecipient;
use Illuminate\Support\Facades\Log;
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
    /**
     * {@inheritdoc}
     */
    public function check(RequestInterface $request): ResponseInterface
    {
        $clientAccesses = ClientAccess::cachedAccesses();
        $state = ProtocolState::tryFrom($request->getProtocolState());
        switch ($state) {
            case ProtocolState::Rcpt:
                if (self::isRejected($request, $clientAccesses)) {
                    Log::error("Client {sender}:{address} is rejected.", [
                        "sender" => $request->getSender(),
                        "address" => $request->getClientAddress(),
                    ]);
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Client access is not allowed!"
                    );
                }
                if (self::rateIsExceeded($request, $clientAccesses)) {
                    Log::error(
                        "Rate limit of client {sender}:{address} is exceeded",
                        [
                            "sender" => $request->getSender(),
                            "address" => $request->getClientAddress(),
                        ]
                    );
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Rate limit is exceeded. Retry later!"
                    );
                }
                if (self::recipientIsRestricted($request)) {
                    Log::error(
                        "Recipient {recipient} of client {sender}:{address} is restricted.",
                        [
                            "recipient" => $request->getRecipient(),
                            "sender" => $request->getSender(),
                            "address" => $request->getClientAddress(),
                        ]
                    );
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Recipient address is restricted!"
                    );
                }
                return new PolicyResponse(AccessVerdict::Ok);
            case ProtocolState::EndOfMessage:
                if (self::quotaIsExceeded($request, $clientAccesses)) {
                    Log::error(
                        "Quota limit of client {sender}:{address} is exceeded.",
                        [
                            "sender" => $request->getSender(),
                            "address" => $request->getClientAddress(),
                        ]
                    );
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        "Quota limit is exceeded. Retry later!"
                    );
                }
                return new PolicyResponse(AccessVerdict::Ok);
            default:
                Log::error("Protocol state {state} is invalid.", [
                    "state" => $state,
                ]);
                return new PolicyResponse(
                    AccessVerdict::Reject,
                    "Invalid protocol state!"
                );
        }
    }

    private static function isRejected(
        RequestInterface $request,
        array $clientAccesses = []
    ): bool {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($clientAccesses[$sender][$address]["verdict"])) {
            $verdict = $clientAccesses[$sender][$address]["verdict"];
            return AccessVerdict::tryFrom($verdict) === AccessVerdict::Reject;
        } else {
            return true;
        }
    }

    private static function rateIsExceeded(
        RequestInterface $request,
        array $clientAccesses = []
    ): bool {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($clientAccesses[$sender][$address]["policy"])) {
            $policy = $clientAccesses[$sender][$address]["policy"];
            if (!empty($policy) && !empty($policy["rate_limit"])) {
                $counterKey = self::limitCounterKey(
                    $policy["name"],
                    $sender,
                    ClientAccess::RATE_LIMIT_SUFFIX
                );
                if (
                    RateLimiter::tooManyAttempts(
                        $counterKey,
                        $policy["rate_limit"]
                    )
                ) {
                    return true;
                }
                RateLimiter::hit($counterKey, $policy["rate_period"]);
            }
        }
        return false;
    }

    private static function quotaIsExceeded(
        RequestInterface $request,
        array $clientAccesses = []
    ): bool {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($clientAccesses[$sender][$address]["policy"])) {
            $policy = $clientAccesses[$sender][$address]["policy"];
            if (!empty($policy) && !empty($policy["quota_limit"])) {
                $counterKey = self::limitCounterKey(
                    $policy["name"],
                    $sender,
                    ClientAccess::QUOTA_LIMIT_SUFFIX
                );
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
                    $policy["quota_period"],
                    $request->getSize()
                );
            }
        }

        return false;
    }

    private static function recipientIsRestricted(
        RequestInterface $request
    ): bool {
        $restrictedRecipients = RestrictedRecipient::cachedRecipients();
        return AccessVerdict::tryFrom(
            $restrictedRecipients[$request->getRecipient()] ?? ""
        ) === AccessVerdict::Reject;
    }

    private static function limitCounterKey(
        string $policy,
        string $sender,
        string $suffix
    ) {
        return sha1(implode([$policy, $sender, $suffix]));
    }
}
