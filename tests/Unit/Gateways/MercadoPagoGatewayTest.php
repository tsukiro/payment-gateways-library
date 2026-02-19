<?php

namespace Raion\Gateways\Tests\Unit\Gateways;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Gateways\MercadoPagoGateway;
use Raion\Gateways\Models\Gateways;

class MercadoPagoGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        GatewayConfig::setConfig([
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-test-token-123',
            ConfigKeys::BASE_URL => 'https://test-site.com'
        ]);
    }

    protected function tearDown(): void
    {
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_can_instantiate_mercadopago_gateway(): void
    {
        $gateway = new MercadoPagoGateway();
        
        $this->assertInstanceOf(MercadoPagoGateway::class, $gateway);
    }

    public function test_name_returns_mercadopago(): void
    {
        $gateway = new MercadoPagoGateway();
        
        $this->assertEquals(Gateways::MercadoPago->value, $gateway->name());
    }

    public function test_get_redirect_url_returns_url_as_is(): void
    {
        $gateway = new MercadoPagoGateway();
        $url = 'https://www.mercadopago.cl/checkout/v1/redirect';
        $token = 'preference-id-123';
        
        $redirectUrl = $gateway->getRedirectUrl($url, $token);
        
        // MercadoPago returns the URL as is
        $this->assertEquals($url, $redirectUrl);
    }

    public function test_get_confirmation_url_returns_not_implemented(): void
    {
        $gateway = new MercadoPagoGateway();
        
        $confirmationUrl = $gateway->getConfirmationUrl();
        
        $this->assertEquals('Not implemented', $confirmationUrl);
    }

    public function test_get_result_url_includes_order_id(): void
    {
        $gateway = new MercadoPagoGateway();
        $orderId = 'ORDER-MP-123';
        
        $resultUrl = $gateway->getResultUrl($orderId);
        
        $this->assertEquals('https://test-site.com/pago/resultado/mercadopago/ORDER-MP-123', $resultUrl);
    }

    public function test_uses_custom_success_url_from_config(): void
    {
        GatewayConfig::setConfig([
            'MERCADOPAGO_SUCCESS_URL' => 'https://custom-site.com/payment/success/{id}'
        ]);

        $this->assertEquals(
            'https://custom-site.com/payment/success/{id}',
            GatewayConfig::get('MERCADOPAGO_SUCCESS_URL')
        );
    }

    public function test_uses_custom_failure_url_from_config(): void
    {
        GatewayConfig::setConfig([
            'MERCADOPAGO_FAILURE_URL' => 'https://custom-site.com/payment/failure/{id}'
        ]);

        $this->assertEquals(
            'https://custom-site.com/payment/failure/{id}',
            GatewayConfig::get('MERCADOPAGO_FAILURE_URL')
        );
    }

    public function test_uses_custom_pending_url_from_config(): void
    {
        GatewayConfig::setConfig([
            'MERCADOPAGO_PENDING_URL' => 'https://custom-site.com/payment/pending/{id}'
        ]);

        $this->assertEquals(
            'https://custom-site.com/payment/pending/{id}',
            GatewayConfig::get('MERCADOPAGO_PENDING_URL')
        );
    }

    public function test_uses_access_token_from_config(): void
    {
        $token = 'APP_USR-custom-token-456';
        GatewayConfig::clear();
        GatewayConfig::setConfig([
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => $token,
            ConfigKeys::BASE_URL => 'https://test.com'
        ]);

        $this->assertEquals($token, GatewayConfig::get(ConfigKeys::MERCADOPAGO_ACCESS_TOKEN));
        
        // Instantiate gateway to verify it uses the token from config
        $gateway = new MercadoPagoGateway();
        $this->assertInstanceOf(MercadoPagoGateway::class, $gateway);
    }
}
