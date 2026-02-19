<?php

namespace Raion\Gateways\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

/**
 * Logger simple que escribe en archivo
 * Útil para desarrollo y debugging
 */
class FileLogger extends AbstractLogger
{
    private string $logFile;
    private string $minLevel;
    
    /**
     * Niveles de log en orden de severidad
     */
    private const LEVELS = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
    ];

    /**
     * @param string $logFile Ruta del archivo de log
     * @param string $minLevel Nivel mínimo a registrar (default: DEBUG)
     */
    public function __construct(string $logFile, string $minLevel = LogLevel::DEBUG)
    {
        $this->logFile = $logFile;
        $this->minLevel = $minLevel;
        
        // Crear directorio si no existe
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * @param mixed $level
     * @param string|Stringable $message
     * @param array<mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $levelStr = strtoupper($level);
        
        // Interpolar contexto en el mensaje
        $message = $this->interpolate((string)$message, $context);
        
        // Formatear contexto adicional
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logLine = "[{$timestamp}] {$levelStr}: {$message}{$contextStr}\n";
        
        file_put_contents($this->logFile, $logLine, FILE_APPEND);
    }

    /**
     * Verifica si se debe registrar el nivel
     */
    private function shouldLog(string $level): bool
    {
        $levelValue = self::LEVELS[$level] ?? 0;
        $minLevelValue = self::LEVELS[$this->minLevel] ?? 0;
        
        return $levelValue >= $minLevelValue;
    }

    /**
     * Interpola valores del contexto en el mensaje
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        
        return strtr($message, $replace);
    }
}
