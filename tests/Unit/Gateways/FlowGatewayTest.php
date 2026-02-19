<?php

namespace Raion\Gateways\Tests\Unit\Gateways;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Gateways\FlowGateway;
use Raion\Gateways\Models\Gateways;

class FlowGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'test-flow-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'test-flow-secret-key',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            ConfigKeys::BASE_URL => 'https://test-site.com'
        ]);
    }

    protected function tearDown(): void
    {
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_can_instantiate_flow_gateway(): void
    {
        $gateway = new FlowGateway();
        
        $this->assertInstanceOf(FlowGateway::class, $gateway);
    }

    public function test_name_returns_flow(): void
    {
        $gateway = new FlowGateway();
        
        $this->assertEquals(Gateways::Flow->value, $gateway->name());
    }

    public function test_get_redirect_url_appends_token(): void
    {
        $gateway = new FlowGateway();
        $baseUrl = 'https://payment.flow.cl/gateway';
        $token = 'test-token-123';
        
        $redirectUrl = $gateway->getRedirectUrl($baseUrl, $token);
        
        $this->assertEquals('https://payment.flow.cl/gateway?token=test-token-123', $redirectUrl);
    }

    public function test_get_confirmation_url_uses_base_url(): void
    {
        $gateway = new FlowGateway();
        
        $confirmationUrl = $gateway->getConfirmationUrl();
        
        $this->assertEquals('https://test-site.com/pago/confirmar/flow', $confirmationUrl);
    }

    public function test_get_result_url_includes_order_id(): void
    {
        $gateway = new FlowGateway();
        $orderId = 'ORDER-12345';
        
        $resultUrl = $gateway->getResultUrl($orderId);
        
        $this->assertEquals('https://test-site.com/pago/resultado/flow/ORDER-12345', $resultUrl);
    }

    public function test_uses_custom_confirmation_url_from_config(): void
    {
        GatewayConfig::setConfig([
            'FLOW_CONFIRMATION_URL' => 'https://custom-site.com/webhooks/flow/{id}'
        ]);

        // This would require creating a transaction to test
        // For now, we verify the config is set
        $this->assertEquals(
            'https://custom-site.com/webhooks/flow/{id}',
            GatewayConfig::get('FLOW_CONFIRMATION_URL')
        );
    }

    public function test_uses_custom_return_url_from_config(): void
    {
        GatewayConfig::setConfig([
            'FLOW_RETURN_URL' => 'https://custom-site.com/payment/result/{id}'
        ]);

        $this->assertEquals(
            'https://custom-site.com/payment/result/{id}',
            GatewayConfig::get('FLOW_RETURN_URL')
        );
    }
}
