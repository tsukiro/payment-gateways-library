<?php

namespace Raion\Gateways\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Models\Gateways;

class GatewaysTest extends TestCase
{
    public function test_flow_gateway_enum_value(): void
    {
        $this->assertEquals('flow', Gateways::Flow->value);
    }

    public function test_webpay_gateway_enum_value(): void
    {
        $this->assertEquals('webpay', Gateways::Webpay->value);
    }

    public function test_mercadopago_gateway_enum_value(): void
    {
        $this->assertEquals('mercadopago', Gateways::MercadoPago->value);
    }

    public function test_can_get_all_gateway_cases(): void
    {
        $cases = Gateways::cases();
        
        $this->assertCount(3, $cases);
        $this->assertContains(Gateways::Flow, $cases);
        $this->assertContains(Gateways::Webpay, $cases);
        $this->assertContains(Gateways::MercadoPago, $cases);
    }
}
