<?php

namespace Raion\Gateways\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

class ConfigurationIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_can_configure_and_instantiate_all_gateways(): void
    {
        // Setup complete configuration
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'flow-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'flow-secret-key',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            ConfigKeys::TRANSBANK_API_KEY => 'webpay-api-key',
            ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
            ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-mercadopago-token',
            ConfigKeys::BASE_URL => 'https://mystore.com'
        ]);

        // Test each gateway can be instantiated
        $flowGateway = Selector::GetGatewayInstance(Gateways::Flow);
        $webpayGateway = Selector::GetGatewayInstance(Gateways::Webpay);
        $mercadopagoGateway = Selector::GetGatewayInstance(Gateways::MercadoPago);

        $this->assertNotNull($flowGateway);
        $this->assertNotNull($webpayGateway);
        $this->assertNotNull($mercadopagoGateway);

        $this->assertEquals('flow', $flowGateway->name());
        $this->assertEquals('webpay', $webpayGateway->name());
        $this->assertEquals('mercadopago', $mercadopagoGateway->name());
    }

    public function test_callback_urls_are_configurable_with_placeholder(): void
    {
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_CONFIRMATION_URL => 'https://mystore.com/webhooks/flow/{id}',
            ConfigKeys::FLOW_RETURN_URL => 'https://mystore.com/payment/flow/result/{id}',
            ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://mystore.com/webhooks/webpay/{id}',
            ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://mystore.com/payment/mercadopago/success/{id}',
            ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://mystore.com/payment/mercadopago/failure/{id}',
            ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://mystore.com/payment/mercadopago/pending/{id}',
            ConfigKeys::BASE_URL => 'https://mystore.com'
        ]);

        // Verify all URLs are properly configured
        $this->assertTrue(GatewayConfig::has(ConfigKeys::FLOW_CONFIRMATION_URL));
        $this->assertTrue(GatewayConfig::has(ConfigKeys::FLOW_RETURN_URL));
        $this->assertTrue(GatewayConfig::has(ConfigKeys::WEBPAY_CONFIRMATION_URL));
        $this->assertTrue(GatewayConfig::has(ConfigKeys::MERCADOPAGO_SUCCESS_URL));
        $this->assertTrue(GatewayConfig::has(ConfigKeys::MERCADOPAGO_FAILURE_URL));
        $this->assertTrue(GatewayConfig::has(ConfigKeys::MERCADOPAGO_PENDING_URL));

        // Verify placeholder is present in URLs
        $flowConfirmationUrl = GatewayConfig::get(ConfigKeys::FLOW_CONFIRMATION_URL);
        $this->assertStringContainsString('{id}', $flowConfirmationUrl);
    }

    public function test_configuration_can_be_updated_at_runtime(): void
    {
        // Initial configuration
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'initial-key',
            ConfigKeys::BASE_URL => 'https://initial.com'
        ]);

        $this->assertEquals('initial-key', GatewayConfig::get(ConfigKeys::FLOW_API_KEY));
        $this->assertEquals('https://initial.com', GatewayConfig::get(ConfigKeys::BASE_URL));

        // Update configuration
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'updated-key',
            ConfigKeys::BASE_URL => 'https://updated.com'
        ]);

        $this->assertEquals('updated-key', GatewayConfig::get(ConfigKeys::FLOW_API_KEY));
        $this->assertEquals('https://updated.com', GatewayConfig::get(ConfigKeys::BASE_URL));
    }

    public function test_gateway_specific_configs_are_isolated(): void
    {
        GatewayConfig::setConfig([
            // Flow configs
            ConfigKeys::FLOW_API_KEY => 'flow-key',
            ConfigKeys::FLOW_SECRET_KEY => 'flow-secret',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            // Webpay configs
            ConfigKeys::TRANSBANK_API_KEY => 'webpay-key',
            ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
            ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
            // MercadoPago configs
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-mp-token',
            // Shared configs
            ConfigKeys::BASE_URL => 'https://mystore.com'
        ]);

        // Each gateway should only access its own configs
        $this->assertEquals('flow-key', GatewayConfig::get(ConfigKeys::FLOW_API_KEY));
        $this->assertEquals('webpay-key', GatewayConfig::get(ConfigKeys::TRANSBANK_API_KEY));
        $this->assertEquals('APP_USR-mp-token', GatewayConfig::get(ConfigKeys::MERCADOPAGO_ACCESS_TOKEN));
        
        // All can access shared config
        $this->assertEquals('https://mystore.com', GatewayConfig::get(ConfigKeys::BASE_URL));
    }

    public function test_environment_variable_fallback_works(): void
    {
        // Clear config to test env variable fallback
        GatewayConfig::clear();
        
        // Set an environment variable (using the mapped env var name)
        putenv('WEB_BASE_URL=https://env-based-url.com');
        
        $baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL);
        
        $this->assertEquals('https://env-based-url.com', $baseUrl);
        
        // Clean up
        putenv('WEB_BASE_URL');
    }

    public function test_configuration_priority_manual_over_environment(): void
    {
        // Set environment variable
        putenv('FLOW_API_KEY=env-api-key');
        
        // Set manual configuration
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'manual-api-key'
        ]);
        
        // Manual config should take priority
        $this->assertEquals('manual-api-key', GatewayConfig::get(ConfigKeys::FLOW_API_KEY));
        
        // Clean up
        putenv('FLOW_API_KEY');
    }
}
