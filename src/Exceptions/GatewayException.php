<?php

namespace Raion\Gateways\Exceptions;

use Exception;

/**
 * Base exception class for all gateway-related exceptions
 * 
 * Esta es la excepción base de la cual heredan todas las demás excepciones
 * específicas de la librería de gateways de pago.
 */
class GatewayException extends Exception
{
    /**
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception for chaining
     */
    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
