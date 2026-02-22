<?php

namespace Raion\Gateways\Callbacks;

use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Config\ConfigKeys;

/**
 * Callback processor for Webpay (Transbank) gateway
 * 
 * Webpay sends callbacks via GET with a 'token_ws' parameter.
 * The response should be a redirect to the result page.
 */
class WebpayCallbackProcessor implements CallbackProcessorInterface
{
    /**
     * Extract token from GET data
     * 
     * @param array $request The request data
     * @return string|null The token from GET['token_ws']
     */
    public function extractToken(array $request): ?string
    {
        return $request['token_ws'] ?? null;
    }

    /**
     * Process confirmation with Webpay
     * 
     * @param GatewayInterface $gateway The Webpay gateway instance
     * @param string $token The transaction token
     * @return array Confirmation data from Webpay
     */
    public function processConfirmation(GatewayInterface $gateway, string $token): array
    {
        return $gateway->confirmTransaction($token);
    }

    /**
     * Build redirect response for Webpay
     * 
     * Webpay expects a redirect to the result page after confirmation.
     * 
     * @param array $confirmationData The confirmation data
     * @param bool $success Whether confirmation was successful
     * @return array Response with type 'redirect'
     */
    public function buildResponse(array $confirmationData, bool $success): array
    {
        $orderId = $confirmationData['buyOrder'] ?? 'unknown';
        $baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL, '');
        
        return [
            'type' => 'redirect',
            'url' => $baseUrl . '/pago/resultado/webpay/' . $orderId,
            'status' => $success ? 302 : 400,
        ];
    }

    /**
     * Check if the confirmation was successful
     * 
     * @param array $confirmationData The confirmation data from Webpay
     * @return bool True if responseCode is 0 and status is AUTHORIZED
     */
    public static function isSuccessful(array $confirmationData): bool
    {
        return isset($confirmationData['responseCode']) && $confirmationData['responseCode'] == 0
            && isset($confirmationData['status']) && $confirmationData['status'] === 'AUTHORIZED';
    }

    /**
     * Validate amount matches expected
     * 
     * @param array $confirmationData The confirmation data
     * @param float $expectedAmount The expected transaction amount
     * @return bool True if amounts match
     */
    public static function validateAmount(array $confirmationData, float $expectedAmount): bool
    {
        return isset($confirmationData['amount']) 
            && (float) $confirmationData['amount'] === $expectedAmount;
    }
}
