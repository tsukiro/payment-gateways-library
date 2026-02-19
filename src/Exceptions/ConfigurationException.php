<?php

namespace Raion\Gateways\Exceptions;

/**
 * Exception thrown when there is a configuration error
 * 
 * Se lanza cuando falta una configuración requerida o cuando
 * un valor de configuración es inválido.
 */
class ConfigurationException extends GatewayException
{
    /**
     * Creates a new exception for a missing configuration key
     * 
     * @param string $key The missing configuration key
     * @return self
     */
    public static function missingKey(string $key): self
    {
        return new self(
            "The configuration element '{$key}' does not exist and no default value was provided",
            1001
        );
    }

    /**
     * Creates a new exception for an invalid configuration value
     * 
     * @param string $key The configuration key
     * @param string $reason The reason why the value is invalid
     * @return self
     */
    public static function invalidValue(string $key, string $reason): self
    {
        return new self(
            "The configuration value for '{$key}' is invalid: {$reason}",
            1002
        );
    }
}
