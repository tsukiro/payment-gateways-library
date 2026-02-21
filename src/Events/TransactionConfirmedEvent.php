<?php

namespace Raion\Gateways\Events;

/**
 * Event dispatched when a transaction is confirmed successfully
 * 
 * This event is triggered after the gateway confirms the payment was successful.
 * Listeners can use this to:
 * - Update order status in the database
 * - Process the order (ship products, activate services, etc.)
 * - Send confirmation emails
 * - Generate invoices
 * - Update inventory
 */
class TransactionConfirmedEvent extends PaymentEvent
{
    private float $amount;
    private string $externalId;
    private string $status;
    private ?string $authorizationCode;

    /**
     * @param string $orderId The order/transaction identifier
     * @param string $gatewayName The gateway name
     * @param float $amount The confirmed transaction amount
     * @param string $externalId The external transaction ID from the gateway
     * @param string $status The status from the gateway
     * @param string|null $authorizationCode The authorization code if available
     * @param array $transactionData Complete transaction data from gateway
     */
    public function __construct(
        string $orderId,
        string $gatewayName,
        float $amount,
        string $externalId,
        string $status,
        ?string $authorizationCode = null,
        array $transactionData = []
    ) {
        parent::__construct($orderId, $gatewayName, $transactionData);
        $this->amount = $amount;
        $this->externalId = $externalId;
        $this->status = $status;
        $this->authorizationCode = $authorizationCode;
    }

    /**
     * Get the confirmed amount
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get the external transaction ID
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * Get the transaction status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get the authorization code
     */
    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'amount' => $this->amount,
            'externalId' => $this->externalId,
            'status' => $this->status,
            'authorizationCode' => $this->authorizationCode,
        ]);
    }
}
