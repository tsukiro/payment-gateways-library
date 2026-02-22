<?php

namespace Raion\Gateways\Models;

use DateTimeImmutable;
use JsonSerializable;

/**
 * Transaction model representing payment transaction data
 * 
 * This model stores all relevant information about a payment transaction
 * and can be serialized for cache storage.
 */
class Transaction implements JsonSerializable
{
    private string $id;
    private string $orderId;
    private string $gateway;
    private ?string $token;
    private ?string $externalId;
    private float $amount;
    private string $currency;
    private TransactionStatus $status;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $confirmedAt;
    private array $metadata;

    /**
     * @param string $id Unique transaction identifier
     * @param string $orderId Order/commerce order identifier
     * @param string $gateway Gateway name (flow, webpay, mercadopago)
     * @param float $amount Transaction amount
     * @param string $currency Transaction currency
     * @param string|null $token Gateway token (if available)
     * @param string|null $externalId External transaction ID from gateway
     * @param TransactionStatus $status Transaction status
     * @param DateTimeImmutable|null $createdAt Creation timestamp
     * @param DateTimeImmutable|null $confirmedAt Confirmation timestamp
     * @param array $metadata Additional metadata
     */
    public function __construct(
        string $id,
        string $orderId,
        string $gateway,
        float $amount,
        string $currency,
        ?string $token = null,
        ?string $externalId = null,
        TransactionStatus $status = TransactionStatus::Pending,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $confirmedAt = null,
        array $metadata = []
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->gateway = $gateway;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->token = $token;
        $this->externalId = $externalId;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->confirmedAt = $confirmedAt;
        $this->metadata = $metadata;
    }

    // Getters

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getConfirmedAt(): ?DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    // Setters

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function setStatus(TransactionStatus $status): void
    {
        $this->status = $status;
        
        // Automatically set confirmedAt when status changes to confirmed
        if ($status === TransactionStatus::Confirmed && $this->confirmedAt === null) {
            $this->confirmedAt = new DateTimeImmutable();
        }
    }

    public function setConfirmedAt(DateTimeImmutable $confirmedAt): void
    {
        $this->confirmedAt = $confirmedAt;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    // Helper methods

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if transaction is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status->isConfirmed();
    }

    /**
     * Check if transaction has failed
     */
    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    /**
     * Convert transaction to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'orderId' => $this->orderId,
            'gateway' => $this->gateway,
            'token' => $this->token,
            'externalId' => $this->externalId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'confirmedAt' => $this->confirmedAt?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Create transaction from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            orderId: $data['orderId'],
            gateway: $data['gateway'],
            amount: (float) $data['amount'],
            currency: $data['currency'],
            token: $data['token'] ?? null,
            externalId: $data['externalId'] ?? null,
            status: TransactionStatus::from($data['status']),
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            confirmedAt: isset($data['confirmedAt']) ? new DateTimeImmutable($data['confirmedAt']) : null,
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * JSON serialize implementation
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert to JSON string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Create transaction from JSON string
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }
        return self::fromArray($data);
    }
}
