<?php

namespace Raion\Gateways;

use Psr\SimpleCache\CacheInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Events\TransactionCreatedEvent;
use Raion\Gateways\Events\TransactionConfirmedEvent;
use Raion\Gateways\Events\TransactionFailedEvent;
use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Models\Transaction;
use Raion\Gateways\Models\TransactionStatus;
use Raion\Gateways\Models\GatewayResponse;
use Raion\Gateways\Exceptions\TransactionException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * TransactionManager handles the lifecycle of payment transactions
 * 
 * This manager orchestrates:
 * - Transaction creation with gateways
 * - Caching of transaction data (PSR-16)
 * - Event dispatching (PSR-14) for application hooks
 * - Transaction confirmation and status updates
 * 
 * Example usage:
 * ```php
 * $manager = new TransactionManager($cache, $eventDispatcher);
 * 
 * // Create transaction
 * $transaction = new Transaction(...);
 * $response = $manager->createTransaction($gateway, $transaction);
 * 
 * // Later, in callback
 * $transaction = $manager->getTransaction('flow', 'ORDER-123');
 * $manager->confirmTransaction($gateway, $transaction, $callbackData);
 * ```
 */
class TransactionManager
{
    private CacheInterface $cache;
    private EventDispatcherInterface $eventDispatcher;
    private int $cacheTtl;
    private string $cachePrefix;

    /**
     * @param CacheInterface $cache PSR-16 cache implementation
     * @param EventDispatcherInterface $eventDispatcher PSR-14 event dispatcher
     * @param int|null $cacheTtl Time to live for cached transactions (seconds)
     * @param string|null $cachePrefix Prefix for cache keys
     */
    public function __construct(
        CacheInterface $cache,
        EventDispatcherInterface $eventDispatcher,
        ?int $cacheTtl = null,
        ?string $cachePrefix = null
    ) {
        $this->cache = $cache;
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheTtl = $cacheTtl ?? (int) GatewayConfig::get(ConfigKeys::CACHE_TTL, 3600);
        $this->cachePrefix = $cachePrefix ?? GatewayConfig::get(ConfigKeys::CACHE_PREFIX, 'raion_payment_');
    }

    /**
     * Create a new transaction with the gateway
     * 
     * @param GatewayInterface $gateway The gateway instance
     * @param Transaction $transaction The transaction to create
     * @return GatewayResponse The response from the gateway
     * @throws TransactionException
     * @throws InvalidArgumentException
     */
    public function createTransaction(GatewayInterface $gateway, Transaction $transaction): GatewayResponse
    {
        try {
            // Create transaction in the gateway
            $response = $gateway->createTransaction(
                id: $transaction->getOrderId(),
                amount: (int) $transaction->getAmount(),
                currency: $transaction->getCurrency(),
                description: $transaction->getMetadata()['description'] ?? 'Payment',
                email: $transaction->getMetadata()['email'] ?? ''
            );

            // Update transaction with gateway response
            $transaction->setToken($response->getToken());
            $transaction->setExternalId($response->getToken()); // Initial external ID
            
            // Store in cache
            $cacheKey = $this->buildCacheKey($transaction->getGateway(), $transaction->getOrderId());
            $this->cache->set($cacheKey, $transaction->toJson(), $this->cacheTtl);

            // Also store with token as key for quick lookup during callbacks
            $tokenCacheKey = $this->buildTokenCacheKey($transaction->getGateway(), $response->getToken());
            $this->cache->set($tokenCacheKey, $transaction->toJson(), $this->cacheTtl);

            // Dispatch event
            $event = new TransactionCreatedEvent(
                orderId: $transaction->getOrderId(),
                gatewayName: $transaction->getGateway(),
                token: $response->getToken(),
                redirectUrl: $response->getUrl(),
                amount: $transaction->getAmount(),
                currency: $transaction->getCurrency(),
                transactionData: $transaction->toArray()
            );
            $this->eventDispatcher->dispatch($event);

            return $response;
        } catch (\Exception $e) {
            // Dispatch failure event
            $failureEvent = new TransactionFailedEvent(
                orderId: $transaction->getOrderId(),
                gatewayName: $transaction->getGateway(),
                errorMessage: $e->getMessage(),
                errorCode: $e->getCode() ? (string) $e->getCode() : null,
                failureReason: 'creation_failed',
                failureStage: 'creation',
                transactionData: $transaction->toArray()
            );
            $this->eventDispatcher->dispatch($failureEvent);

            throw $e;
        }
    }

    /**
     * Get a transaction from cache by order ID
     * 
     * @param string $gatewayName The gateway name
     * @param string $orderId The order ID
     * @return Transaction|null The transaction or null if not found
     * @throws InvalidArgumentException
     */
    public function getTransaction(string $gatewayName, string $orderId): ?Transaction
    {
        $cacheKey = $this->buildCacheKey($gatewayName, $orderId);
        $cached = $this->cache->get($cacheKey);

        if ($cached === null) {
            return null;
        }

        return Transaction::fromJson($cached);
    }

    /**
     * Get a transaction from cache by token
     * 
     * @param string $gatewayName The gateway name
     * @param string $token The transaction token
     * @return Transaction|null The transaction or null if not found
     * @throws InvalidArgumentException
     */
    public function getTransactionByToken(string $gatewayName, string $token): ?Transaction
    {
        $cacheKey = $this->buildTokenCacheKey($gatewayName, $token);
        $cached = $this->cache->get($cacheKey);

        if ($cached === null) {
            return null;
        }

        return Transaction::fromJson($cached);
    }

    /**
     * Confirm a transaction after gateway callback
     * 
     * @param GatewayInterface $gateway The gateway instance
     * @param Transaction $transaction The transaction to confirm
     * @param array $callbackData The callback data from the gateway
     * @return Transaction The updated transaction
     * @throws TransactionException
     * @throws InvalidArgumentException
     */
    public function confirmTransaction(
        GatewayInterface $gateway,
        Transaction $transaction,
        array $callbackData
    ): Transaction {
        try {
            // Get confirmation from gateway
            $confirmationData = $gateway->confirmTransaction($transaction->getToken());

            // Determine if successful based on gateway response
            $isSuccessful = $this->isConfirmationSuccessful($gateway->name(), $confirmationData);

            if ($isSuccessful) {
                // Update transaction status
                $transaction->setStatus(TransactionStatus::Confirmed);
                
                // Update external ID if available
                $externalId = $this->extractExternalId($gateway->name(), $confirmationData);
                if ($externalId) {
                    $transaction->setExternalId($externalId);
                }

                // Add confirmation data to metadata
                $transaction->addMetadata('confirmation', $confirmationData);

                // Update cache
                $this->updateTransactionInCache($transaction);

                // Dispatch success event
                $event = new TransactionConfirmedEvent(
                    orderId: $transaction->getOrderId(),
                    gatewayName: $transaction->getGateway(),
                    amount: $transaction->getAmount(),
                    externalId: $transaction->getExternalId() ?? '',
                    status: 'confirmed',
                    authorizationCode: $confirmationData['authorizationCode'] ?? null,
                    transactionData: $confirmationData
                );
                $this->eventDispatcher->dispatch($event);
            } else {
                // Mark as failed
                $transaction->setStatus(TransactionStatus::Failed);
                $transaction->addMetadata('failure', $confirmationData);

                // Update cache
                $this->updateTransactionInCache($transaction);

                // Dispatch failure event
                $failureEvent = new TransactionFailedEvent(
                    orderId: $transaction->getOrderId(),
                    gatewayName: $transaction->getGateway(),
                    errorMessage: $this->extractErrorMessage($gateway->name(), $confirmationData),
                    errorCode: $confirmationData['responseCode'] ?? $confirmationData['status'] ?? null,
                    failureReason: 'payment_rejected',
                    failureStage: 'confirmation',
                    transactionData: $confirmationData
                );
                $this->eventDispatcher->dispatch($failureEvent);
            }

            return $transaction;
        } catch (\Exception $e) {
            // Mark as failed
            $transaction->setStatus(TransactionStatus::Failed);
            $transaction->addMetadata('error', $e->getMessage());
            
            // Update cache
            $this->updateTransactionInCache($transaction);

            // Dispatch failure event
            $failureEvent = new TransactionFailedEvent(
                orderId: $transaction->getOrderId(),
                gatewayName: $transaction->getGateway(),
                errorMessage: $e->getMessage(),
                errorCode: $e->getCode() ? (string) $e->getCode() : null,
                failureReason: 'confirmation_error',
                failureStage: 'confirmation',
                transactionData: $transaction->toArray()
            );
            $this->eventDispatcher->dispatch($failureEvent);

            throw $e;
        }
    }

    /**
     * Clear a transaction from cache
     * 
     * @param string $gatewayName The gateway name
     * @param string $orderId The order ID
     * @return bool True if deleted, false otherwise
     * @throws InvalidArgumentException
     */
    public function clearTransaction(string $gatewayName, string $orderId): bool
    {
        $cacheKey = $this->buildCacheKey($gatewayName, $orderId);
        return $this->cache->delete($cacheKey);
    }

    /**
     * Update a transaction in cache
     * 
     * @param Transaction $transaction The transaction to update
     * @throws InvalidArgumentException
     */
    private function updateTransactionInCache(Transaction $transaction): void
    {
        $cacheKey = $this->buildCacheKey($transaction->getGateway(), $transaction->getOrderId());
        $this->cache->set($cacheKey, $transaction->toJson(), $this->cacheTtl);

        // Also update token cache if available
        if ($transaction->getToken()) {
            $tokenCacheKey = $this->buildTokenCacheKey($transaction->getGateway(), $transaction->getToken());
            $this->cache->set($tokenCacheKey, $transaction->toJson(), $this->cacheTtl);
        }
    }

    /**
     * Build cache key for order ID
     */
    private function buildCacheKey(string $gatewayName, string $orderId): string
    {
        return $this->cachePrefix . 'tx_' . $gatewayName . '_' . $orderId;
    }

    /**
     * Build cache key for token
     */
    private function buildTokenCacheKey(string $gatewayName, string $token): string
    {
        return $this->cachePrefix . 'token_' . $gatewayName . '_' . md5($token);
    }

    /**
     * Check if confirmation was successful based on gateway response
     */
    private function isConfirmationSuccessful(string $gatewayName, array $confirmationData): bool
    {
        return match (strtolower($gatewayName)) {
            'flow' => isset($confirmationData['status']) && $confirmationData['status'] == 2,
            'webpay' => isset($confirmationData['responseCode']) && $confirmationData['responseCode'] == 0
                && isset($confirmationData['status']) && $confirmationData['status'] === 'AUTHORIZED',
            'mercadopago' => isset($confirmationData['status']) && $confirmationData['status'] === 'approved',
            default => false,
        };
    }

    /**
     * Extract external transaction ID from confirmation data
     */
    private function extractExternalId(string $gatewayName, array $confirmationData): ?string
    {
        return match (strtolower($gatewayName)) {
            'flow' => $confirmationData['flowOrder'] ?? null,
            'webpay' => $confirmationData['buyOrder'] ?? null,
            'mercadopago' => $confirmationData['id'] ?? null,
            default => null,
        };
    }

    /**
     * Extract error message from confirmation data
     */
    private function extractErrorMessage(string $gatewayName, array $confirmationData): string
    {
        return match (strtolower($gatewayName)) {
            'flow' => $confirmationData['message'] ?? 'Transaction failed',
            'webpay' => 'Response code: ' . ($confirmationData['responseCode'] ?? 'unknown'),
            'mercadopago' => $confirmationData['status_detail'] ?? 'Transaction failed',
            default => 'Transaction failed',
        };
    }
}
