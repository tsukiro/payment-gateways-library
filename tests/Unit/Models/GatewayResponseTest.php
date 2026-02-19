<?php

namespace Raion\Gateways\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Raion\Gateways\Models\GatewayResponse;

class GatewayResponseTest extends TestCase
{
    public function test_can_create_gateway_response(): void
    {
        $token = 'test-token-123';
        $url = 'https://payment.gateway.com/pay';
        
        $response = new GatewayResponse($token, $url);
        
        $this->assertInstanceOf(GatewayResponse::class, $response);
        $this->assertEquals($token, $response->token);
        $this->assertEquals($url, $response->url);
    }

    public function test_can_get_token(): void
    {
        $token = 'test-token-456';
        $response = new GatewayResponse($token, 'https://test.com');
        
        $this->assertEquals($token, $response->getToken());
    }

    public function test_can_get_url(): void
    {
        $url = 'https://payment.test.com/checkout';
        $response = new GatewayResponse('token', $url);
        
        $this->assertEquals($url, $response->getUrl());
    }

    public function test_properties_are_public(): void
    {
        $response = new GatewayResponse('token123', 'https://test.com');
        
        // Test that properties can be accessed directly
        $this->assertEquals('token123', $response->token);
        $this->assertEquals('https://test.com', $response->url);
    }
}
