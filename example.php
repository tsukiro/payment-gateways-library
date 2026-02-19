<?php
/**
 * Ejemplo básico de uso de la librería Raion Payment Gateways
 * 
 * Este archivo muestra cómo configurar y usar los diferentes gateways de pago
 */

require_once __DIR__ . '/vendor/autoload.php';

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

// ====================================
// CARGAR VARIABLES DE ENTORNO
// ====================================

/**
 * Función helper para cargar variables desde archivo .env
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new \Exception("El archivo .env no existe en: {$path}");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Establecer variable de entorno
            if (!empty($name)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Cargar variables desde .env
loadEnv(__DIR__ . '/.env');

// ====================================
// 1. CONFIGURACIÓN
// ====================================

// Opción A: Configuración mediante array con ConfigKeys cargando desde variables de entorno
GatewayConfig::setConfig([
    // Flow
    ConfigKeys::FLOW_API_KEY => getenv('FLOW_API_KEY'),
    ConfigKeys::FLOW_SECRET_KEY => getenv('FLOW_SECRET_KEY'),
    ConfigKeys::FLOW_API_URL => getenv('FLOW_API_URL'),
    
    // Transbank/Webpay
    ConfigKeys::TRANSBANK_API_KEY => getenv('TRANSBANK_API_KEY'),
    ConfigKeys::TRANSBANK_COMMERCE_CODE => getenv('TRANSBANK_COMMERCE_CODE'),
    ConfigKeys::TRANSBANK_ENVIRONMENT => getenv('TRANSBANK_ENVIRONMENT'),
    
    // MercadoPago
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => getenv('MERCADOPAGO_ACCESS_TOKEN'),
    ConfigKeys::MERCADOPAGO_PUBLIC_KEY => getenv('MERCADOPAGO_PUBLIC_KEY'),
    
    // Común para todos
    ConfigKeys::BASE_URL => getenv('WEB_BASE_URL'),
    
    // URLs de callback (opcional - personaliza según tus necesidades)
    // Usa {id} como placeholder para el ID de la orden
    ConfigKeys::FLOW_CONFIRMATION_URL => getenv('FLOW_CONFIRMATION_URL'),
    ConfigKeys::FLOW_RETURN_URL => getenv('FLOW_RETURN_URL'),
    ConfigKeys::WEBPAY_CONFIRMATION_URL => getenv('WEBPAY_CONFIRMATION_URL'),
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => getenv('MERCADOPAGO_SUCCESS_URL'),
    ConfigKeys::MERCADOPAGO_FAILURE_URL => getenv('MERCADOPAGO_FAILURE_URL'),
    ConfigKeys::MERCADOPAGO_PENDING_URL => getenv('MERCADOPAGO_PENDING_URL'),
]);

// Opción B: Si ya tienes las variables de entorno cargadas en tu servidor,
// no necesitas setConfig() - la librería las buscará automáticamente

// ====================================
// 2. CREAR TRANSACCIÓN CON FLOW
// ====================================

try {
    echo "=== Ejemplo Flow ===\n";
    
    $flowGateway = Selector::GetGatewayInstance(Gateways::Flow);
    
    $response = $flowGateway->createTransaction(
        id: 'ORDER-12345',
        amount: 50000,
        currency: 'CLP',
        description: 'Compra de productos en línea',
        email: 'bastian@raion.cl'
    );
    
    echo "Token: {$response->getToken()}\n";
    echo "URL de pago: {$response->getUrl()}\n";
    echo "URL de redirección: " . $flowGateway->getRedirectUrl($response->getUrl(), $response->getToken()) . "\n\n";
    
} catch (Exception $e) {
    echo "Error con Flow: {$e->getMessage()}\n\n";
}

// ====================================
// 3. CREAR TRANSACCIÓN CON WEBPAY
// ====================================

try {
    echo "=== Ejemplo Webpay ===\n";
    
    $webpayGateway = Selector::GetGatewayInstance(Gateways::Webpay);
    
    $response = $webpayGateway->createTransaction(
        id: 'ORDER-12346',
        amount: 75000,
        currency: 'CLP',
        description: 'Compra en tienda online',
        email: 'cliente@example.com'
    );
    
    echo "Token: {$response->getToken()}\n";
    echo "URL de pago: {$response->getUrl()}\n";
    echo "URL de redirección: " . $webpayGateway->getRedirectUrl($response->getUrl(), $response->getToken()) . "\n\n";
    
} catch (Exception $e) {
    echo "Error con Webpay: {$e->getMessage()}\n\n";
}

// ====================================
// 4. CREAR TRANSACCIÓN CON MERCADOPAGO
// ====================================

try {
    echo "=== Ejemplo MercadoPago ===\n";
    
    $mercadopagoGateway = Selector::GetGatewayInstance(Gateways::MercadoPago);
    
    $response = $mercadopagoGateway->createTransaction(
        id: 'ORDER123471',
        amount: 100000,
        currency: 'CLP',
        description: 'Suscripción mensual',
        email: 'bastian@raion.cl'
    );
    
    echo "Token: {$response->getToken()}\n";
    echo "URL de pago: {$response->getUrl()}\n\n";
    
    // ====================================
    // 5. VERIFICAR ESTADO DE TRANSACCIÓN
    // ====================================

    echo "=== Verificar Estado de Transacción ===\n";
    
    $mercadopagoGateway = Selector::GetGatewayInstance(Gateways::MercadoPago);
    $token = $response->getToken(); // Token obtenido al crear la transacción
    
    $estado = $mercadopagoGateway->getTransactionInProcess($token);
    
    echo "Estado de la transacción:\n";
    print_r($estado->id);
    echo "\n";
} catch (Exception $e) {
    echo "Error con MercadoPago: {$e->getMessage()}\n\n";
    //echo $e->getTraceAsString() . "\n";
    echo $e->getPrevious()?->getTraceAsString() . "\n";
    echo $e->getPrevious()?->getTraceAsString() . "\n";
    echo $e->getFile() ."\n";
}

// ====================================
// 6. MÉTODOS ÚTILES DE CONFIGURACIÓN
// ====================================

echo "=== Métodos Útiles ===\n";

// Verificar si una configuración existe
if (GatewayConfig::has(ConfigKeys::FLOW_API_KEY)) {
    echo "✓ FLOW_API_KEY está configurada\n";
}

// Obtener con valor por defecto
$customValue = GatewayConfig::get('CUSTOM_KEY', 'valor-por-defecto');
echo "Valor personalizado: {$customValue}\n";

// Ver toda la configuración (sin mostrar secretos en producción)
// $allConfig = GatewayConfig::getAll();
// print_r($allConfig);

echo "\n=== Fin de ejemplos ===\n";
