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
    private readonly array $clientAccesses;
    private readonly array $restrictedRecipients;

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->clientAccesses = ClientAccess::cachedAccesses();
        $this->restrictedRecipients = RestrictedRecipient::cachedRecipients();
    }

    /**
     * {@inheritdoc}
     */
    public function check(RequestInterface $request): ResponseInterface
    {
        $state = ProtocolState::tryFrom($request->getProtocolState());
        switch ($state) {
            case ProtocolState::Rcpt:
                if ($this->isRejected($request)) {
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        sprintf(
                            "Client %s:%s is rejected!",
                            $request->getSender(),
                            $request->getClientAddress()
                        )
                    );
                }
                if ($this->rateIsExceeded($request)) {
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        sprintf(
                            "The rate of client %s:%s is exceeded!",
                            $request->getSender(),
                            $request->getClientAddress()
                        )
                    );
                }
                if ($this->recipientIsRestricted($request)) {
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        sprintf(
                            "Recipient %s is restricted!",
                            $request->getRecipient()
                        )
                    );
                }
                return new PolicyResponse(AccessVerdict::Ok);
            case ProtocolState::EndOfMessage:
                return new PolicyResponse(AccessVerdict::Ok);
                if ($this->quotaIsExceeded($request)) {
                    return new PolicyResponse(
                        AccessVerdict::Reject,
                        sprintf(
                            "The quota of client %s:%s is exceeded!",
                            $request->getSender(),
                            $request->getClientAddress()
                        )
                    );
                }
                $transport = $this->clientTransport($request);
                if (!empty($transport)) {
                    return new PolicyResponse(
                        AccessVerdict::Filter,
                        $transport
                    );
                }
                return new PolicyResponse(AccessVerdict::Ok);
            default:
                logger()->error("Invalid protocol state {state}.", [
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
        }
        else {
            return true;
        }
        
    }

    private function rateIsExceeded(RequestInterface $request): bool
    {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($this->clientAccesses[$sender][$address]["policy"])) {
            $counterKey = self::counterKey($request, ClientAccess::RATE_LIMIT_SUFFIX);
            $policy = $this->clientAccesses[$sender][$address]["policy"];
            if (!empty($policy) && !empty($policy['rate_limit'])) {
                if (RateLimiter::tooManyAttempts($counterKey, $policy['rate_limit'])) {
                    return true;
                }
                RateLimiter::hit($counterKey, (int) $policy['rate_period']);
            }
        }
        return false;
    }

    private function quotaIsExceeded(RequestInterface $request): bool
    {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($this->clientAccesses[$sender][$address]["policy"])) {
            $counterKey = self::counterKey($request, ClientAccess::QUOTA_LIMIT_SUFFIX);
            $policy = $this->clientAccesses[$sender][$address]["policy"];
            if (!empty($policy) && !empty($policy['quota_limit'])) {
                if (RateLimiter::tooManyAttempts($counterKey, $policy['quota_limit'])) {
                    return true;
                }
                RateLimiter::increment(
                    $counterKey,
                    (int) $policy['quota_period'],
                    $request->getSize()
                );
            }
        }

        return false;
    }

    private function recipientIsRestricted(RequestInterface $request): bool
    {
        $recipient = $request->getRecipient();
        $verdict = AccessVerdict::tryFrom(
            $this->restrictedRecipients[$recipient] ?? ""
        );
        return $verdict === AccessVerdict::Reject;
    }

    private function clientTransport(RequestInterface $request): string
    {
        $address = $request->getClientAddress();
        $sender = $request->getSender();
        if (isset($this->clientAccesses[$sender][$address]["transport"])) {
            return $this->clientAccesses[$sender][$address]["transport"];
        }
        return "";
    }

    private static function counterKey(
        RequestInterface $request, string $suffix
    ): string
    {
        return sha1(
            $request->getSender() . "|" . $request->getClientAddress() . "|" . $suffix
        );
    }
}
