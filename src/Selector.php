<?php

namespace Raion\Gateways;

use Psr\Log\LoggerInterface;
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
}