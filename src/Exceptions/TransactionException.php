<?php

namespace Raion\Gateways\Exceptions;

use Exception;

/**
 * Exception thrown when there is an error creating or processing a transaction
 * 
 * Se lanza cuando ocurre un error al crear una transacción, procesarla,
 * o al obtener su estado.
 */
class TransactionException extends GatewayException
{
    /**
     * Creates a new exception for transaction creation errors
     * 
     * @param string $gateway The gateway name
     * @param string $message The error message
     * @param Exception|null $previous The previous exception
     * @return self
     */
    public static function creationFailed(string $gateway, string $message, ?Exception $previous = null): self
    {
        return new self(
            "Error creating transaction in {$gateway}: {$message}",
            2001,
            $previous
        );
    }

    /**
     * Creates a new exception for transaction processing errors
     * 
     * @param string $gateway The gateway name
     * @param string $message The error message
     * @param Exception|null $previous The previous exception
     * @return self
     */
    public static function processingFailed(string $gateway, string $message, ?Exception $previous = null): self
    {
        return new self(
            "Error processing transaction in {$gateway}: {$message}",
            2002,
            $previous
        );
    }

    /**
     * Creates a new exception for transaction status retrieval errors
     * 
     * @param string $gateway The gateway name
     * @param string $token The transaction token
     * @param Exception|null $previous The previous exception
     * @return self
     */
    public static function statusRetrievalFailed(string $gateway, string $token, ?Exception $previous = null): self
    {
        return new self(
            "Error retrieving transaction status in {$gateway} for token: {$token}",
            2003,
            $previous
        );
    }
}
