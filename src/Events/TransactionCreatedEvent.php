<?php

namespace Raion\Gateways\Events;

/**
 * Event dispatched when a transaction is created successfully in the gateway
 * 
 * This event is triggered after the gateway API returns a successful response
 * with a token and redirect URL. Listeners can use this to:
 * - Store the transaction in their database
 * - Log the transaction creation
 * - Send notifications
 * - Track analytics
 */
class TransactionCreatedEvent extends PaymentEvent
{
    private string $token;
    private string $redirectUrl;
    private float $amount;
    private string $currency;

    /**
     * @param string $orderId The order/transaction identifier
     * @param string $gatewayName The gateway name
     * @param string $token The transaction token from the gateway
     * @param string $redirectUrl The URL to redirect the user to complete payment
     * @param float $amount The transaction amount
     * @param string $currency The transaction currency
     * @param array $transactionData Additional transaction data
     */
    public function __construct(
        string $orderId,
        string $gatewayName,
        string $token,
        string $redirectUrl,
        float $amount,
        string $currency,
        array $transactionData = []
    ) {
        parent::__construct($orderId, $gatewayName, $transactionData);
        $this->token = $token;
        $this->redirectUrl = $redirectUrl;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Get the transaction token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get the redirect URL
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * Get the transaction amount
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get the transaction currency
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'token' => $this->token,
            'redirectUrl' => $this->redirectUrl,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ]);
    }
}
