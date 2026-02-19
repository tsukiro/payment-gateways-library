<?php

namespace Raion\Gateways;

use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Gateways\FlowGateway;
use Raion\Gateways\Gateways\WebpayGateway;
use Raion\Gateways\Gateways\MercadoPagoGateway;
use Raion\Gateways\Models\Gateways;
class Selector
{
    public static function GetGatewayInstance(Gateways $gateway): ?GatewayInterface
    {
        return match ($gateway) {
            Gateways::Flow => new FlowGateway(),
            Gateways::Webpay => new WebpayGateway(),
            Gateways::MercadoPago => new MercadoPagoGateway(),
            default => null,
        };
    } 
}