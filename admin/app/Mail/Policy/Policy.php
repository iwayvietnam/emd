<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Enums\AccessVerdict;
use App\Enums\ProtocolState;
use App\Mail\Policy\Interface\PolicyInterface;
use App\Mail\Policy\Interface\RequestInterface;
use App\Mail\Policy\Interface\ResponseInterface;
use App\Models\ClientAccess;
use App\Models\RestrictedRecipient;
use Illuminate\Support\Facades\Cache;
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
    const ACCESS_CACHE_EXPIRES = 600;

    const RECIPIENT_CACHE_EXPIRES = 3600;

    /**
     * {@inheritdoc}
     */
    public function check(RequestInterface $request): ResponseInterface
    {
        $clientAccesses = self::cachedClientAccesses();
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

    private static function cachedClientAccesses(): array
    {
        $cacheKey = ClientAccess::class;
        $accesses = Cache::store("array")->get($cacheKey, []);
        if (empty($accesses)) {
            foreach (ClientAccess::all() as $model) {
                $accesses[$model->sender][$model->client_ip] = [
                    "policy" => [
                        "name" => $model->policy->name,
                        "quota_limit" => $model->policy->quota_limit,
                        "quota_period" => $model->policy->quota_period,
                        "rate_limit" => $model->policy->rate_limit,
                        "rate_period" => $model->policy->rate_period,
                    ],
                    "client" => $model->client->name,
                    "verdict" => $model->verdict,
                ];
            }
            Cache::store("array")->put($cacheKey, $accesses, self::ACCESS_CACHE_EXPIRES);
        }
        return $accesses;
    }

    private static function recipientIsRestricted(
        RequestInterface $request
    ): bool {
        $restrictedRecipients = self::cachedRestrictedRecipients();
        return AccessVerdict::tryFrom(
            $restrictedRecipients[$request->getRecipient()] ?? ""
        ) === AccessVerdict::Reject;
    }

    private static function cachedRestrictedRecipients(): array
    {
        $cacheKey = RestrictedRecipient::class;
        $recipients = Cache::store("array")->get($cacheKey, []);
        if (empty($recipients)) {
            $recipients = RestrictedRecipient::all()->pluck("verdict", "recipient")->all();
            Cache::store("array")->put($cacheKey, $recipients, self::RECIPIENT_CACHE_EXPIRES);
        }
        return $recipients;
    }

    private static function limitCounterKey(
        string $policy,
        string $sender,
        string $suffix
    ) {
        return sha1(implode([$policy, $sender, $suffix]));
    }
}
