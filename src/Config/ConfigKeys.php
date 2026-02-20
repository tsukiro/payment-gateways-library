<?php

namespace Raion\Gateways\Config;

/**
 * Constantes para las claves de configuración de los gateways
 * 
 * Esta clase proporciona constantes para acceder a la configuración
 * de manera type-safe y evitar errores de tipeo.
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
 *     ConfigKeys::BASE_URL => 'https://tu-sitio.com'
 * ]);
 * ```
 */
class ConfigKeys
{
    // ========================================
    // Flow Gateway Configuration Keys
    // ========================================
    
    /**
     * Flow API Key - Clave de API proporcionada por Flow
     */
    public const FLOW_API_KEY = 'FLOW_API_KEY';
    
    /**
     * Flow Secret Key - Clave secreta proporcionada por Flow
     */
    public const FLOW_SECRET_KEY = 'FLOW_SECRET_KEY';
    
    /**
     * Flow API URL - URL de la API de Flow
     * Default: https://www.flow.cl/api o https://sandbox.flow.cl/api
     */
    public const FLOW_API_URL = 'FLOW_API_URL';
    
    /**
     * Flow Confirmation URL - URL de confirmación para Flow
     * Admite placeholder {id} para el ID de la orden
     */
    public const FLOW_CONFIRMATION_URL = 'FLOW_CONFIRMATION_URL';
    
    /**
     * Flow Return URL - URL de retorno después del pago en Flow
     * Admite placeholder {id} para el ID de la orden
     */
    public const FLOW_RETURN_URL = 'FLOW_RETURN_URL';

    // ========================================
    // Webpay (Transbank) Gateway Configuration Keys
    // ========================================
    
    /**
     * Transbank API Key - Clave de API de Transbank
     */
    public const TRANSBANK_API_KEY = 'TRANSBANK_API_KEY';
    
    /**
     * Transbank Commerce Code - Código de comercio de Transbank
     */
    public const TRANSBANK_COMMERCE_CODE = 'TRANSBANK_COMMERCE_CODE';
    
    /**
     * Transbank Environment - Ambiente de Transbank (INTEGRATION o PRODUCTION)
     */
    public const TRANSBANK_ENVIRONMENT = 'TRANSBANK_ENVIRONMENT';
    
    /**
     * Webpay Confirmation URL - URL de confirmación para Webpay
     * Admite placeholder {id} para el ID de la orden
     */
    public const WEBPAY_CONFIRMATION_URL = 'WEBPAY_CONFIRMATION_URL';

    // ========================================
    // MercadoPago Gateway Configuration Keys
    // ========================================
    
    /**
     * MercadoPago Access Token - Token de acceso de MercadoPago
     */
    public const MERCADOPAGO_ACCESS_TOKEN = 'MERCADOPAGO_ACCESS_TOKEN';

    /**
     * MercadoPago Public Key - Clave pública de MercadoPago (opcional, para SDKs frontend)
     */
    public const MERCADOPAGO_PUBLIC_KEY = 'MERCADOPAGO_PUBLIC_KEY';
    
    /**
     * MercadoPago Success URL - URL de éxito después del pago
     * Admite placeholder {id} para el ID de la orden
     */
    public const MERCADOPAGO_SUCCESS_URL = 'MERCADOPAGO_SUCCESS_URL';
    
    /**
     * MercadoPago Failure URL - URL de error después del pago
     * Admite placeholder {id} para el ID de la orden
     */
    public const MERCADOPAGO_FAILURE_URL = 'MERCADOPAGO_FAILURE_URL';
    
    /**
     * MercadoPago Pending URL - URL de pago pendiente
     * Admite placeholder {id} para el ID de la orden
     */
    public const MERCADOPAGO_PENDING_URL = 'MERCADOPAGO_PENDING_URL';

    /**
     * MercadoPago Runtime Environment - Entorno de ejecución de MercadoPago (LOCAL o SERVER)
     */
    public const MERCADOPAGO_RUNTIME_ENVIRONMENT = 'MERCADOPAGO_RUNTIME_ENVIRONMENT';

        /**
        * MercadoPago Redirect URL - URL de redirección para MercadoPago (opcional, si no se usa success/failure)
        * Admite placeholder {id} para el ID de la orden
        */
    public const MERCADOPAGO_REDIRECT_URL = 'MERCADOPAGO_REDIRECT_URL';
    // ========================================
    // General Configuration Keys
    // ========================================
    
    /**
     * Base URL - URL base de tu aplicación web
     * Ejemplo: https://tu-sitio.com
     */
    public const BASE_URL = 'BASE_URL';

    // ========================================
    // Environment Variable Mapping
    // ========================================
    
    /**
     * Mapeo de claves de configuración a variables de entorno
     * 
     * @internal
     * @return array<string, string>
     */
    public static function getEnvMapping(): array
    {
        return [
            self::FLOW_API_KEY => 'FLOW_API_KEY',
            self::FLOW_SECRET_KEY => 'FLOW_SECRET_KEY',
            self::FLOW_API_URL => 'FLOW_API_URL',
            self::FLOW_CONFIRMATION_URL => 'FLOW_CONFIRMATION_URL',
            self::FLOW_RETURN_URL => 'FLOW_RETURN_URL',
            self::BASE_URL => 'WEB_BASE_URL', // Backward compatibility
            self::TRANSBANK_API_KEY => 'TRANSBANK_API_KEY',
            self::TRANSBANK_COMMERCE_CODE => 'TRANSBANK_COMMERCE_CODE',
            self::TRANSBANK_ENVIRONMENT => 'TRANSBANK_ENVIRONMENT',
            self::WEBPAY_CONFIRMATION_URL => 'WEBPAY_CONFIRMATION_URL',
            self::MERCADOPAGO_ACCESS_TOKEN => 'MERCADOPAGO_ACCESS_TOKEN',
            self::MERCADOPAGO_SUCCESS_URL => 'MERCADOPAGO_SUCCESS_URL',
            self::MERCADOPAGO_FAILURE_URL => 'MERCADOPAGO_FAILURE_URL',
            self::MERCADOPAGO_PENDING_URL => 'MERCADOPAGO_PENDING_URL',
            self::MERCADOPAGO_RUNTIME_ENVIRONMENT => 'MERCADOPAGO_RUNTIME_ENVIRONMENT',
            self::MERCADOPAGO_REDIRECT_URL => 'MERCADOPAGO_REDIRECT_URL',
        ];
    }
}
