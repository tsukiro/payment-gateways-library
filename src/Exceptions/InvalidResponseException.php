<?php

namespace Raion\Gateways\Exceptions;

/**
 * Exception thrown when the gateway API returns an invalid or incomplete response
 * 
 * Se lanza cuando la respuesta de la API no contiene los datos esperados,
 * tiene un formato inválido, o no puede ser procesada correctamente.
 */
class InvalidResponseException extends GatewayException
{
    /**
     * Creates a new exception for incomplete responses
     * 
     * @param string $gateway The gateway name
     * @param string $missingFields Comma-separated list of missing fields
     * @param string $responseData The actual response data (for debugging)
     * @return self
     */
    public static function incompleteResponse(string $gateway, string $missingFields, string $responseData = ""): self
    {
        $message = "Incomplete response from {$gateway}. Missing fields: {$missingFields}";
        if ($responseData) {
            $message .= ". Response: {$responseData}";
        }
        
        return new self($message, 4001);
    }

    /**
     * Creates a new exception for JSON parsing errors
     * 
     * @param string $gateway The gateway name
     * @param string $jsonError The JSON error message
     * @return self
     */
    public static function invalidJson(string $gateway, string $jsonError): self
    {
        return new self(
            "Invalid JSON response from {$gateway}: {$jsonError}",
            4002
        );
    }

    /**
     * Creates a new exception for unexpected response format
     * 
     * @param string $gateway The gateway name
     * @param string $expected The expected format description
     * @param string $actual The actual format/data received
     * @return self
     */
    public static function unexpectedFormat(string $gateway, string $expected, string $actual): self
    {
        return new self(
            "Unexpected response format from {$gateway}. Expected: {$expected}, Got: {$actual}",
            4003
        );
    }
}
