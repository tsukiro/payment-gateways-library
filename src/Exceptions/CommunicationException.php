<?php

namespace Raion\Gateways\Exceptions;

use Exception;

/**
 * Exception thrown when there is a communication error with the gateway API
 * 
 * Se lanza cuando hay problemas de red, timeouts, o errores HTTP
 * al comunicarse con las APIs de los gateways.
 */
class CommunicationException extends GatewayException
{
    /**
     * Creates a new exception for HTTP errors
     * 
     * @param int $httpCode The HTTP status code
     * @param string $message Additional error message
     * @param Exception|null $previous The previous exception
     * @return self
     */
    public static function httpError(int $httpCode, string $message = "", ?Exception $previous = null): self
    {
        $defaultMessage = "HTTP error {$httpCode}";
        $fullMessage = $message ? "{$defaultMessage}: {$message}" : $defaultMessage;
        
        return new self(
            $fullMessage,
            3000 + $httpCode,
            $previous
        );
    }

    /**
     * Creates a new exception for network errors
     * 
     * @param string $message The error message
     * @param Exception|null $previous The previous exception
     * @return self
     */
    public static function networkError(string $message, ?Exception $previous = null): self
    {
        return new self(
            "Network communication error: {$message}",
            3001,
            $previous
        );
    }

    /**
     * Creates a new exception for timeout errors
     * 
     * @param string $gateway The gateway name
     * @param Exception|null $previous The previous exception
     * @return self
     */
    public static function timeout(string $gateway, ?Exception $previous = null): self
    {
        return new self(
            "Communication timeout with {$gateway} API",
            3002,
            $previous
        );
    }
}
