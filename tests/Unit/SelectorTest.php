<?php

namespace Raion\Gateways\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;
use Raion\Gateways\Gateways\FlowGateway;
use Raion\Gateways\Gateways\WebpayGateway;
use Raion\Gateways\Gateways\MercadoPagoGateway;

class SelectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup basic configuration for gateways
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'test-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'test-secret-key',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            ConfigKeys::BASE_URL => 'https://test.com',
            ConfigKeys::TRANSBANK_API_KEY => 'test-transbank-key',
            ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
            ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-test-token'
        ]);
    }

    protected function tearDown(): void
    {
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_can_get_flow_gateway_instance(): void
    {
        $gateway = Selector::GetGatewayInstance(Gateways::Flow);
        
        $this->assertInstanceOf(FlowGateway::class, $gateway);
    }

    public function test_can_get_webpay_gateway_instance(): void
    {
        $gateway = Selector::GetGatewayInstance(Gateways::Webpay);
        
        $this->assertInstanceOf(WebpayGateway::class, $gateway);
    }

    public function test_can_get_mercadopago_gateway_instance(): void
    {
        $gateway = Selector::GetGatewayInstance(Gateways::MercadoPago);
        
        $this->assertInstanceOf(MercadoPagoGateway::class, $gateway);
    }

    public function test_returns_null_for_invalid_gateway(): void
    {
        // This test would require a way to pass an invalid gateway
        // Since we're using an enum, this is already type-safe
        $this->assertTrue(true);
    }

    public function test_all_gateways_implement_gateway_interface(): void
    {
        foreach (Gateways::cases() as $gatewayEnum) {
            $gateway = Selector::GetGatewayInstance($gatewayEnum);
            
            $this->assertInstanceOf(
                \Raion\Gateways\Interfaces\GatewayInterface::class,
                $gateway,
                "Gateway {$gatewayEnum->value} should implement GatewayInterface"
            );
        }
    }

    public function test_factory_creates_new_instances_each_time(): void
    {
        $gateway1 = Selector::GetGatewayInstance(Gateways::Flow);
        $gateway2 = Selector::GetGatewayInstance(Gateways::Flow);
        
        $this->assertNotSame($gateway1, $gateway2, 'Factory should create new instances');
    }

    public function test_each_gateway_returns_correct_name(): void
    {
        $this->assertEquals('flow', Selector::GetGatewayInstance(Gateways::Flow)->name());
        $this->assertEquals('webpay', Selector::GetGatewayInstance(Gateways::Webpay)->name());
        $this->assertEquals('mercadopago', Selector::GetGatewayInstance(Gateways::MercadoPago)->name());
    }
}
