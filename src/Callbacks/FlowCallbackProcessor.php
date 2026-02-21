<?php

namespace Raion\Gateways\Callbacks;

use Raion\Gateways\Interfaces\GatewayInterface;

/**
 * Callback processor for Flow gateway
 * 
 * Flow sends callbacks via POST with a 'token' parameter.
 * The response should be JSON.
 */
class FlowCallbackProcessor implements CallbackProcessorInterface
{
    /**
     * Extract token from POST data
     * 
     * @param array $request The request data
     * @return string|null The token from POST['token']
     */
    public function extractToken(array $request): ?string
    {
        return $request['token'] ?? null;
    }

    /**
     * Process confirmation with Flow
     * 
     * @param GatewayInterface $gateway The Flow gateway instance
     * @param string $token The transaction token
     * @return array Confirmation data from Flow
     */
    public function processConfirmation(GatewayInterface $gateway, string $token): array
    {
        return $gateway->confirmTransaction($token);
    }

    /**
     * Build JSON response for Flow
     * 
     * Flow expects a JSON response acknowledging receipt of the callback.
     * 
     * @param array $confirmationData The confirmation data
     * @param bool $success Whether confirmation was successful
     * @return array Response with type 'json'
     */
    public function buildResponse(array $confirmationData, bool $success): array
    {
        return [
            'type' => 'json',
            'data' => $confirmationData,
            'status' => $success ? 200 : 400,
        ];
    }

    /**
     * Check if the confirmation was successful
     * 
     * @param array $confirmationData The confirmation data from Flow
     * @return bool True if status is 2 (success)
     */
    public static function isSuccessful(array $confirmationData): bool
    {
        return isset($confirmationData['status']) && $confirmationData['status'] == 2;
    }
}
