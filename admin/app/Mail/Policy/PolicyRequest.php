<?php declare(strict_types=1);

namespace App\Mail\Policy;

use App\Mail\Policy\Interface\RequestInterface;
use Illuminate\Support\Str;

/**
 * Policy request class
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class PolicyRequest implements RequestInterface
{
    const EOL = "\n";

    /**
     * Constructor
     *
     * @param array $attributes
     * @return self
     */
    protected function __construct(private readonly array $attributes = []) {}

    /**
     * Process policy request data
     *
     * @param string $data
     * @return self
     */
    public static function fromData(string $data): self
    {
        $attributes = [];
        $lines = explode(self::EOL, $data);
        foreach ($lines as $line) {
            if (strpos($line, "=")) {
                [$key, $value] = explode("=", trim($line));
                $attributes[$key] = $value;
            }
        }
        return new self($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest(): string
    {
        return $this->attributes["request"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolState(): string
    {
        return $this->attributes["protocol_state"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolName(): string
    {
        return $this->attributes["protocol_name"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getHeloName(): string
    {
        return $this->attributes["helo_name"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueId(): string
    {
        return $this->attributes["queue_id"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getSender(): string
    {
        return $this->attributes["sender"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient(): string
    {
        return $this->attributes["recipient"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientCount(): int
    {
        return intval($this->attributes["recipient_count"] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getClientAddress(): string
    {
        return $this->attributes["client_address"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getClientName(): string
    {
        return $this->attributes["client_name"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getReverseClientName(): string
    {
        return $this->attributes["reverse_client_name"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(): string
    {
        return $this->attributes["instance"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return intval($this->attributes["size"] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerAddress(): string
    {
        return $this->attributes["server_address"] ?? "";
    }

    /**
     * {@inheritdoc}
     */
    public function getServerPort(): int
    {
        return intval($this->attributes["server_port"] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailVersion(): string
    {
        return $this->attributes["mail_version"] ?? "";
    }
}
