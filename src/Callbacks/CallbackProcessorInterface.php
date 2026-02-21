<?php

namespace Raion\Gateways\Callbacks;

use Raion\Gateways\Interfaces\GatewayInterface;

/**
 * Interface for processing gateway callbacks
 * 
 * Each gateway has different callback mechanisms (POST/GET, different parameters).
 * This interface standardizes the callback processing flow.
 */
interface CallbackProcessorInterface
{
    /**
     * Extract the transaction token from the callback request
     * 
     * @param array $request The request data ($_GET, $_POST, or combined)
     * @return string|null The transaction token or null if not found
     */
    public function extractToken(array $request): ?string;

    /**
     * Process the confirmation with the gateway
     * 
     * @param GatewayInterface $gateway The gateway instance
     * @param string $token The transaction token
     * @return array The confirmation data from the gateway
     */
    public function processConfirmation(GatewayInterface $gateway, string $token): array;

    /**
     * Build the response to send back to the gateway/user
     * 
     * @param array $confirmationData The confirmation data
     * @param bool $success Whether the confirmation was successful
     * @return array Response array with 'type' (json|redirect) and 'data'
     */
    public function buildResponse(array $confirmationData, bool $success): array;
}
