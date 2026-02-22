<?php

namespace Raion\Gateways;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Gateways\FlowGateway;
use Raion\Gateways\Gateways\WebpayGateway;
use Raion\Gateways\Gateways\MercadoPagoGateway;
use Raion\Gateways\Models\Gateways;
use Raion\Gateways\Validation\TransactionValidator;

class Selector
{
    /**
     * Get gateway instance by enum
     * 
     * @param Gateways $gateway The gateway enum to instantiate
     * @param LoggerInterface|null $logger Optional PSR-3 logger
     * @param TransactionValidator|null $validator Optional transaction validator
     * @return GatewayInterface|null Gateway instance or null if not found
     */
    public static function GetGatewayInstance(
        Gateways $gateway,
        ?LoggerInterface $logger = null,
        ?TransactionValidator $validator = null
    ): ?GatewayInterface {
        return match ($gateway) {
            Gateways::Flow => new FlowGateway($logger, $validator),
            Gateways::Webpay => new WebpayGateway($logger, $validator),
            Gateways::MercadoPago => new MercadoPagoGateway($logger, $validator),
            default => null,
        };
    }

    /**
     * Create a TransactionManager instance
     * 
     * This factory method creates a TransactionManager with the provided
     * cache and event dispatcher implementations.
     * 
     * @param CacheInterface $cache PSR-16 cache implementation
     * @param EventDispatcherInterface $eventDispatcher PSR-14 event dispatcher
     * @param int|null $cacheTtl Optional cache TTL in seconds (default: 3600)
     * @param string|null $cachePrefix Optional cache key prefix
     * @return TransactionManager
     */
    public static function CreateTransactionManager(
        CacheInterface $cache,
        EventDispatcherInterface $eventDispatcher,
        ?int $cacheTtl = null,
        ?string $cachePrefix = null
    ): TransactionManager {
        return new TransactionManager($cache, $eventDispatcher, $cacheTtl, $cachePrefix);
    }
}