<?php

namespace Raion\Gateways\Events;

/**
 * Event dispatched when a transaction fails
 * 
 * This event is triggered when a payment fails at any stage (creation, confirmation, etc.).
 * Listeners can use this to:
 * - Update order status to failed
 * - Send notification to customer
 * - Log the failure for analysis
 * - Trigger retry mechanisms
 * - Flag for manual review
 */
class TransactionFailedEvent extends PaymentEvent
{
    private string $errorMessage;
    private ?string $errorCode;
    private string $failureReason;
    private string $failureStage;

    /**
     * @param string $orderId The order/transaction identifier
     * @param string $gatewayName The gateway name
     * @param string $errorMessage Human-readable error message
     * @param string|null $errorCode Error code from the gateway (if available)
     * @param string $failureReason The reason for failure
     * @param string $failureStage The stage where failure occurred (creation, confirmation, etc.)
     * @param array $transactionData Additional transaction data
     */
    public function __construct(
        string $orderId,
        string $gatewayName,
        string $errorMessage,
        ?string $errorCode = null,
        string $failureReason = 'unknown',
        string $failureStage = 'unknown',
        array $transactionData = []
    ) {
        parent::__construct($orderId, $gatewayName, $transactionData);
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        $this->failureReason = $failureReason;
        $this->failureStage = $failureStage;
    }

    /**
     * Get the error message
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get the failure reason
     */
    public function getFailureReason(): string
    {
        return $this->failureReason;
    }

    /**
     * Get the failure stage
     */
    public function getFailureStage(): string
    {
        return $this->failureStage;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'errorMessage' => $this->errorMessage,
            'errorCode' => $this->errorCode,
            'failureReason' => $this->failureReason,
            'failureStage' => $this->failureStage,
        ]);
    }
}
