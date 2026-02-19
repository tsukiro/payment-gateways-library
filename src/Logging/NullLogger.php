<?php

namespace Raion\Gateways\Logging;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Logger nulo que no hace nada
 * Implementación por defecto cuando no se configura un logger
 */
class NullLogger extends AbstractLogger
{
    /**
     * @param mixed $level
     * @param string|Stringable $message
     * @param array<mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        // No hace nada - logger silencioso
    }
}
