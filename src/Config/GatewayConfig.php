<?php

namespace Raion\Gateways\Config;

use Raion\Gateways\Exceptions\ConfigurationException;

/**
 * Clase de configuración para los diferentes Gateways de pago
 * 
 * Permite configurar los parámetros de forma flexible mediante un array
 * o usar variables de entorno como fallback.
 * 
 * Ejemplo de uso:
 * ```php
 * use Raion\Gateways\Config\ConfigKeys;
 * use Raion\Gateways\Config\GatewayConfig;
 * 
 * GatewayConfig::setConfig([
 *     ConfigKeys::FLOW_API_KEY => 'tu-api-key',
 *     ConfigKeys::FLOW_SECRET_KEY => 'tu-secret-key',
 *     ConfigKeys::FLOW_API_URL => 'https://api.flow.cl',
 *     ConfigKeys::BASE_URL => 'https://tu-sitio.com',
 *     ConfigKeys::TRANSBANK_API_KEY => 'tu-transbank-key',
 *     ConfigKeys::TRANSBANK_COMMERCE_CODE => 'tu-commerce-code',
 *     ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
 *     ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'tu-access-token'
 * ]);
 * ```
 */
class GatewayConfig
{
    /**
     * Almacena la configuración personalizada
     */
    private static array $config = [];

    /**
     * Mapeo de variables de entorno
     * @var array<string, string>|null
     */
    private static ?array $envMapping = null;

    /**
     * Establece la configuración para los gateways
     * 
     * @param array $config Array asociativo con las configuraciones
     * @return void
     */
    public static function setConfig(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Obtiene un valor de configuración
     * 
     * Busca en el siguiente orden:
     * 1. Configuración establecida mediante setConfig()
     * 2. Variables de entorno
     * 3. Valor por defecto proporcionado
     * 
     * @param string $name Nombre del parámetro de configuración
     * @param mixed $default Valor por defecto si no se encuentra (opcional)
     * @return mixed El valor de configuración
     * @throws ConfigurationException Si no se encuentra el parámetro y no hay valor por defecto
     */
    public static function get(string $name, mixed $default = null): mixed
    {
        // Initialize env mapping on first use
        if (self::$envMapping === null) {
            self::$envMapping = ConfigKeys::getEnvMapping();
        }

        // 1. Buscar en configuración personalizada
        if (isset(self::$config[$name]) && self::$config[$name] !== null && self::$config[$name] !== '') {
            return self::$config[$name];
        }

        // 2. Buscar en variables de entorno
        $envName = self::$envMapping[$name] ?? $name;
        $envValue = getenv($envName);
        if ($envValue !== false && $envValue !== '') {
            return $envValue;
        }

        // 3. Usar valor por defecto si fue proporcionado
        if ($default !== null) {
            return $default;
        }

        // 4. Lanzar excepción si no se encontró nada
        throw ConfigurationException::missingKey($name);
    }

    /**
     * Obtiene toda la configuración actual
     * 
     * @return array
     */
    public static function getAll(): array
    {
        return self::$config;
    }

    /**
     * Limpia toda la configuración
     * 
     * @return void
     */
    public static function clear(): void
    {
        self::$config = [];
    }

    /**
     * Verifica si existe un valor de configuración
     * 
     * @param string $name Nombre del parámetro
     * @return bool
     */
    public static function has(string $name): bool
    {
        // Initialize env mapping on first use
        if (self::$envMapping === null) {
            self::$envMapping = ConfigKeys::getEnvMapping();
        }

        if (isset(self::$config[$name]) && self::$config[$name] !== null && self::$config[$name] !== '') {
            return true;
        }

        $envName = self::$envMapping[$name] ?? $name;
        $envValue = getenv($envName);
        return $envValue !== false && $envValue !== '';
    }
}
