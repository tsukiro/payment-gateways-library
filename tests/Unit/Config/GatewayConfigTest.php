<?php

namespace Raion\Gateways\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Exceptions\ConfigurationException;

class GatewayConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear config before each test
        GatewayConfig::clear();
    }

    protected function tearDown(): void
    {
        // Clear config after each test
        GatewayConfig::clear();
        parent::tearDown();
    }

    public function test_can_set_and_get_configuration(): void
    {
        $config = [
            ConfigKeys::FLOW_API_KEY => 'test-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'test-secret-key',
            ConfigKeys::BASE_URL => 'https://test.com'
        ];

        GatewayConfig::setConfig($config);

        $this->assertEquals('test-api-key', GatewayConfig::get(ConfigKeys::FLOW_API_KEY));
        $this->assertEquals('test-secret-key', GatewayConfig::get(ConfigKeys::FLOW_SECRET_KEY));
        $this->assertEquals('https://test.com', GatewayConfig::get(ConfigKeys::BASE_URL));
    }

    public function test_get_returns_default_value_when_key_not_found(): void
    {
        $result = GatewayConfig::get('NON_EXISTENT_KEY', 'default-value');
        
        $this->assertEquals('default-value', $result);
    }

    public function test_get_throws_exception_when_key_not_found_and_no_default(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("The configuration element 'NON_EXISTENT_KEY' does not exist");
        
        GatewayConfig::get('NON_EXISTENT_KEY');
    }

    public function test_has_returns_true_for_existing_config(): void
    {
        GatewayConfig::setConfig([ConfigKeys::FLOW_API_KEY => 'test-key']);
        
        $this->assertTrue(GatewayConfig::has(ConfigKeys::FLOW_API_KEY));
    }

    public function test_has_returns_false_for_non_existent_config(): void
    {
        $this->assertFalse(GatewayConfig::has('NON_EXISTENT_KEY'));
    }

    public function test_get_all_returns_all_configuration(): void
    {
        $config = [
            'KEY1' => 'value1',
            'KEY2' => 'value2',
            'KEY3' => 'value3'
        ];

        GatewayConfig::setConfig($config);
        
        $this->assertEquals($config, GatewayConfig::getAll());
    }

    public function test_clear_removes_all_configuration(): void
    {
        GatewayConfig::setConfig(['KEY' => 'value']);
        
        $this->assertTrue(GatewayConfig::has('KEY'));
        
        GatewayConfig::clear();
        
        $this->assertFalse(GatewayConfig::has('KEY'));
    }

    public function test_config_can_be_merged(): void
    {
        GatewayConfig::setConfig(['KEY1' => 'value1']);
        GatewayConfig::setConfig(['KEY2' => 'value2']);
        
        $this->assertEquals('value1', GatewayConfig::get('KEY1'));
        $this->assertEquals('value2', GatewayConfig::get('KEY2'));
    }

    public function test_can_override_existing_config(): void
    {
        GatewayConfig::setConfig(['KEY' => 'old-value']);
        GatewayConfig::setConfig(['KEY' => 'new-value']);
        
        $this->assertEquals('new-value', GatewayConfig::get('KEY'));
    }

    public function test_flow_configuration(): void
    {
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'flow-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'flow-secret-key',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            ConfigKeys::BASE_URL => 'https://test.com'
        ]);

        $this->assertEquals('flow-api-key', GatewayConfig::get(ConfigKeys::FLOW_API_KEY));
        $this->assertEquals('flow-secret-key', GatewayConfig::get(ConfigKeys::FLOW_SECRET_KEY));
        $this->assertEquals('https://sandbox.flow.cl/api', GatewayConfig::get(ConfigKeys::FLOW_API_URL));
        $this->assertEquals('https://test.com', GatewayConfig::get(ConfigKeys::BASE_URL));
    }

    public function test_webpay_configuration(): void
    {
        GatewayConfig::setConfig([
            ConfigKeys::TRANSBANK_API_KEY => 'webpay-api-key',
            ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
            ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
            ConfigKeys::BASE_URL => 'https://test.com'
        ]);

        $this->assertEquals('webpay-api-key', GatewayConfig::get(ConfigKeys::TRANSBANK_API_KEY));
        $this->assertEquals('597055555532', GatewayConfig::get(ConfigKeys::TRANSBANK_COMMERCE_CODE));
        $this->assertEquals('INTEGRATION', GatewayConfig::get(ConfigKeys::TRANSBANK_ENVIRONMENT));
    }

    public function test_mercadopago_configuration(): void
    {
        GatewayConfig::setConfig([
            ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-test-token',
            ConfigKeys::BASE_URL => 'https://test.com'
        ]);

        $this->assertEquals('APP_USR-test-token', GatewayConfig::get(ConfigKeys::MERCADOPAGO_ACCESS_TOKEN));
    }

    public function test_callback_urls_configuration(): void
    {
        GatewayConfig::setConfig([
            'FLOW_CONFIRMATION_URL' => 'https://test.com/flow/confirm/{id}',
            'FLOW_RETURN_URL' => 'https://test.com/flow/return/{id}',
            'WEBPAY_CONFIRMATION_URL' => 'https://test.com/webpay/confirm/{id}',
            'MERCADOPAGO_SUCCESS_URL' => 'https://test.com/mp/success/{id}',
            'MERCADOPAGO_FAILURE_URL' => 'https://test.com/mp/failure/{id}',
            'MERCADOPAGO_PENDING_URL' => 'https://test.com/mp/pending/{id}',
        ]);

        $this->assertEquals('https://test.com/flow/confirm/{id}', GatewayConfig::get('FLOW_CONFIRMATION_URL'));
        $this->assertEquals('https://test.com/flow/return/{id}', GatewayConfig::get('FLOW_RETURN_URL'));
        $this->assertEquals('https://test.com/webpay/confirm/{id}', GatewayConfig::get('WEBPAY_CONFIRMATION_URL'));
        $this->assertEquals('https://test.com/mp/success/{id}', GatewayConfig::get('MERCADOPAGO_SUCCESS_URL'));
        $this->assertEquals('https://test.com/mp/failure/{id}', GatewayConfig::get('MERCADOPAGO_FAILURE_URL'));
        $this->assertEquals('https://test.com/mp/pending/{id}', GatewayConfig::get('MERCADOPAGO_PENDING_URL'));
    }

    public function test_empty_string_values_are_treated_as_not_set(): void
    {
        GatewayConfig::setConfig(['KEY' => '']);
        
        $this->assertFalse(GatewayConfig::has('KEY'));
        $this->assertEquals('default', GatewayConfig::get('KEY', 'default'));
    }

    public function test_null_values_are_treated_as_not_set(): void
    {
        GatewayConfig::setConfig(['KEY' => null]);
        
        $this->assertFalse(GatewayConfig::has('KEY'));
        $this->assertEquals('default', GatewayConfig::get('KEY', 'default'));
    }
}
