<?php

namespace Raion\Gateways\Events;

use DateTimeImmutable;

/**
 * Base class for all payment-related events
 * 
 * All payment events share common properties like order ID, gateway name,
 * timestamp, and transaction data.
 */
abstract class PaymentEvent
{
    protected string $orderId;
    protected string $gatewayName;
    protected DateTimeImmutable $timestamp;
    protected array $transactionData;

    /**
     * @param string $orderId The order/transaction identifier
     * @param string $gatewayName The gateway name (flow, webpay, mercadopago)
     * @param array $transactionData Additional transaction data
     */
    public function __construct(string $orderId, string $gatewayName, array $transactionData = [])
    {
        $this->orderId = $orderId;
        $this->gatewayName = $gatewayName;
        $this->timestamp = new DateTimeImmutable();
        $this->transactionData = $transactionData;
    }

    /**
     * Get the order ID
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Get the gateway name
     */
    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    /**
     * Get the event timestamp
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Get the transaction data
     */
    public function getTransactionData(): array
    {
        return $this->transactionData;
    }

    /**
     * Get event as array for logging/debugging
     */
    public function toArray(): array
    {
        return [
            'event' => static::class,
            'orderId' => $this->orderId,
            'gatewayName' => $this->gatewayName,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'transactionData' => $this->transactionData,
        ];
    }
}
