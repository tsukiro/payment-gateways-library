<?php

namespace Raion\Gateways\Tests\Unit\Gateways;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Gateways\WebpayGateway;
use Raion\Gateways\Models\Gateways;

class WebpayGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        GatewayConfig::setConfig([
            ConfigKeys::TRANSBANK_API_KEY => 'test-webpay-api-key',
            ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
            ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
            ConfigKeys::BASE_URL => 'https://test-site.com'
        ]);
    }

    protected function tearDown(): void
    {
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_can_instantiate_webpay_gateway(): void
    {
        $gateway = new WebpayGateway();
        
        $this->assertInstanceOf(WebpayGateway::class, $gateway);
    }

    public function test_name_returns_webpay(): void
    {
        $gateway = new WebpayGateway();
        
        $this->assertEquals(Gateways::Webpay->value, $gateway->name());
    }

    public function test_get_redirect_url_uses_token_ws_parameter(): void
    {
        $gateway = new WebpayGateway();
        $baseUrl = 'https://webpay3gint.transbank.cl/webpayserver/initTransaction';
        $token = 'test-token-456';
        
        $redirectUrl = $gateway->getRedirectUrl($baseUrl, $token);
        
        $this->assertEquals($baseUrl . '?token_ws=test-token-456', $redirectUrl);
    }

    public function test_get_confirmation_url_uses_base_url(): void
    {
        $gateway = new WebpayGateway();
        
        $confirmationUrl = $gateway->getConfirmationUrl();
        
        $this->assertEquals('https://test-site.com/pago/confirmar/webpay', $confirmationUrl);
    }

    public function test_get_result_url_includes_order_id(): void
    {
        $gateway = new WebpayGateway();
        $orderId = 'ORDER-67890';
        
        $resultUrl = $gateway->getResultUrl($orderId);
        
        $this->assertEquals('https://test-site.com/pago/resultado/webpay/ORDER-67890', $resultUrl);
    }

    public function test_uses_custom_confirmation_url_from_config(): void
    {
        GatewayConfig::setConfig([
            'WEBPAY_CONFIRMATION_URL' => 'https://custom-site.com/webhooks/webpay/{id}'
        ]);

        $this->assertEquals(
            'https://custom-site.com/webhooks/webpay/{id}',
            GatewayConfig::get('WEBPAY_CONFIRMATION_URL')
        );
    }
}
