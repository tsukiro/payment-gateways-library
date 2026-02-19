<?php

namespace Raion\Gateways\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

class GatewayWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup configuration for all gateways
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'test-flow-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'test-flow-secret-key',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            ConfigKeys::TRANSBANK_API_KEY => 'test-webpay-api-key',
            ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
            ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-test-token',
            ConfigKeys::BASE_URL => 'https://mystore.com',
            ConfigKeys::FLOW_CONFIRMATION_URL => 'https://mystore.com/webhooks/flow/{id}',
            ConfigKeys::FLOW_RETURN_URL => 'https://mystore.com/payment/result/{id}',
            ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://mystore.com/webhooks/webpay/{id}',
            ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://mystore.com/payment/success/{id}',
            ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://mystore.com/payment/failure/{id}',
            ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://mystore.com/payment/pending/{id}'
        ]);
    }

    protected function tearDown(): void
    {
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_flow_gateway_url_generation_workflow(): void
    {
        $gateway = Selector::GetGatewayInstance(Gateways::Flow);
        
        // Test redirect URL generation
        $baseUrl = 'https://payment.flow.cl/gateway';
        $token = 'test-token-123';
        $redirectUrl = $gateway->getRedirectUrl($baseUrl, $token);
        
        $this->assertStringContainsString($token, $redirectUrl);
        $this->assertStringContainsString($baseUrl, $redirectUrl);
        
        // Test confirmation URL
        $confirmationUrl = $gateway->getConfirmationUrl();
        $this->assertStringContainsString('mystore.com', $confirmationUrl);
        
        // Test result URL
        $orderId = 'ORDER-123';
        $resultUrl = $gateway->getResultUrl($orderId);
        $this->assertStringContainsString($orderId, $resultUrl);
    }

    public function test_webpay_gateway_url_generation_workflow(): void
    {
        $gateway = Selector::GetGatewayInstance(Gateways::Webpay);
        
        // Test redirect URL generation with token_ws parameter
        $baseUrl = 'https://webpay3gint.transbank.cl/webpayserver/initTransaction';
        $token = 'test-token-456';
        $redirectUrl = $gateway->getRedirectUrl($baseUrl, $token);
        
        $this->assertStringContainsString('token_ws=' . $token, $redirectUrl);
        
        // Test confirmation URL
        $confirmationUrl = $gateway->getConfirmationUrl();
        $this->assertStringContainsString('mystore.com', $confirmationUrl);
        
        // Test result URL
        $orderId = 'ORDER-456';
        $resultUrl = $gateway->getResultUrl($orderId);
        $this->assertStringContainsString($orderId, $resultUrl);
    }

    public function test_mercadopago_gateway_url_generation_workflow(): void
    {
        $gateway = Selector::GetGatewayInstance(Gateways::MercadoPago);
        
        // Test redirect URL (MercadoPago returns URL as-is)
        $url = 'https://www.mercadopago.cl/checkout/v1/redirect';
        $token = 'preference-id-789';
        $redirectUrl = $gateway->getRedirectUrl($url, $token);
        
        $this->assertEquals($url, $redirectUrl);
        
        // Test result URL
        $orderId = 'ORDER-789';
        $resultUrl = $gateway->getResultUrl($orderId);
        $this->assertStringContainsString($orderId, $resultUrl);
        $this->assertStringContainsString('mercadopago', $resultUrl);
    }

    public function test_multiple_gateways_can_coexist(): void
    {
        // Instantiate all gateways
        $flowGateway = Selector::GetGatewayInstance(Gateways::Flow);
        $webpayGateway = Selector::GetGatewayInstance(Gateways::Webpay);
        $mercadopagoGateway = Selector::GetGatewayInstance(Gateways::MercadoPago);
        
        // All should be properly instantiated
        $this->assertNotNull($flowGateway);
        $this->assertNotNull($webpayGateway);
        $this->assertNotNull($mercadopagoGateway);
        
        // All should have correct names
        $this->assertEquals('flow', $flowGateway->name());
        $this->assertEquals('webpay', $webpayGateway->name());
        $this->assertEquals('mercadopago', $mercadopagoGateway->name());
        
        // All should generate URLs independently
        $orderId = 'ORDER-MULTI-123';
        
        $this->assertStringContainsString('flow', $flowGateway->getResultUrl($orderId));
        $this->assertStringContainsString('webpay', $webpayGateway->getResultUrl($orderId));
        $this->assertStringContainsString('mercadopago', $mercadopagoGateway->getResultUrl($orderId));
    }

    public function test_gateway_selection_by_enum(): void
    {
        // Test that we can select gateways dynamically
        $gatewayTypes = [Gateways::Flow, Gateways::Webpay, Gateways::MercadoPago];
        $expectedNames = ['flow', 'webpay', 'mercadopago'];
        
        foreach ($gatewayTypes as $index => $gatewayType) {
            $gateway = Selector::GetGatewayInstance($gatewayType);
            
            $this->assertEquals(
                $expectedNames[$index],
                $gateway->name(),
                "Gateway type {$gatewayType->value} should return correct name"
            );
        }
    }
}
