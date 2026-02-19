<?php

namespace Raion\Gateways\Exceptions;

/**
 * Exception thrown when transaction validation fails
 */
class ValidationException extends GatewayException
{
    /**
     * Create a validation exception for invalid input
     *
     * @param string $field The field that failed validation
     * @param string $message The validation error message
     * @return self
     */
    public static function invalidInput(string $field, string $message): self
    {
        return new self("Validation failed for field '{$field}': {$message}");
    }

    /**
     * Create a validation exception for invalid value
     *
     * @param string $field The field with invalid value
     * @param string $message The validation error message
     * @return self
     */
    public static function invalidValue(string $field, string $message): self
    {
        return new self($message);
    }

    /**
     * Create a validation exception for unsupported values
     *
     * @param string $field The field with unsupported value
     * @param mixed $value The unsupported value
     * @param array $supported List of supported values
     * @return self
     */
    public static function unsupportedValue(string $field, $value, array $supported): self
    {
        $supportedList = implode(', ', $supported);
        return new self(
            "Unsupported value '{$value}' for field '{$field}'. Supported values: {$supportedList}"
        );
    }
}
