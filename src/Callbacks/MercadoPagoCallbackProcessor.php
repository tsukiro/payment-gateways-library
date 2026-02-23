<?php

namespace Raion\Gateways\Callbacks;

use Raion\Gateways\Interfaces\GatewayInterface;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Config\ConfigKeys;

/**
 * Callback processor for MercadoPago gateway
 * 
 * MercadoPago sends callbacks via GET with 'collection_id', 'collection_status', 
 * and 'payment_id' parameters.
 * The response should be a redirect to the result page.
 */
class MercadoPagoCallbackProcessor implements CallbackProcessorInterface
{
    /**
     * Extract token from GET data
     * 
     * For MercadoPago, we use the preference_id or collection_id as the token.
     * 
     * @param array $request The request data
     * @return string|null The payment/collection ID
     */
    public function extractToken(array $request): ?string
    {
        // MercadoPago can send different parameters
        return $request['preference_id'] 
            ?? $request['payment_id'] 
            ?? $request['collection_id'] 
            ?? null;
    }

    /**
     * Process confirmation with MercadoPago
     * 
     * @param GatewayInterface $gateway The MercadoPago gateway instance
     * @param string $token The payment/preference ID
     * @return array Confirmation data from MercadoPago
     */
    public function processConfirmation(GatewayInterface $gateway, string $token): array
    {
        return $gateway->confirmTransaction($token);
    }

    /**
     * Build redirect response for MercadoPago
     * 
     * @param array $confirmationData The confirmation data
     * @param bool $success Whether confirmation was successful
     * @return array Response with type 'redirect'
     */
    public function buildResponse(array $confirmationData, bool $success): array
    {
        $orderId = $confirmationData['external_reference'] ?? 'unknown';
        $baseUrl = GatewayConfig::get(ConfigKeys::BASE_URL, '');
        
        return [
            'type' => 'redirect',
            'url' => $baseUrl . '/pago/resultado/mercadopago/' . $orderId,
            'status' => $success ? 302 : 400,
        ];
    }

    /**
     * Check if the confirmation was successful
     * 
     * @param array $confirmationData The confirmation data
     * @return bool True if collection_status is 'approved'
     */
    public static function isSuccessful(array $confirmationData): bool
    {
        return isset($confirmationData['status']) 
            && $confirmationData['status'] === 'approved';
    }

    /**
     * Get collection status from request
     * 
     * @param array $request The request data
     * @return string|null The collection status
     */
    public static function getCollectionStatus(array $request): ?string
    {
        return $request['collection_status'] ?? $request['status'] ?? null;
    }

    /**
     * Get external reference (order ID) from request
     * 
     * @param array $request The request data
     * @return string|null The external reference
     */
    public static function getExternalReference(array $request): ?string
    {
        return $request['external_reference'] ?? null;
    }
}
