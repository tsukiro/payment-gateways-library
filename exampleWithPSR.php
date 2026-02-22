<?php

/**
 * Ejemplo de uso de Raion Payment Gateways con PSR-16 (Caché) y PSR-14 (Eventos)
 * 
 * Este ejemplo demuestra cómo usar el TransactionManager para simplificar
 * el flujo de pagos con caché y eventos.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Transaction;
use Raion\Gateways\Models\TransactionStatus;
use Raion\Gateways\Models\Gateways;
use Raion\Gateways\Events\TransactionCreatedEvent;
use Raion\Gateways\Events\TransactionConfirmedEvent;
use Raion\Gateways\Events\TransactionFailedEvent;
use Raion\Gateways\Callbacks\FlowCallbackProcessor;
use Raion\Gateways\Callbacks\WebpayCallbackProcessor;
use Raion\Gateways\Callbacks\MercadoPagoCallbackProcessor;

// ============================================================================
// PASO 1: Configurar pasarelas
// ============================================================================

GatewayConfig::setConfig([
    // Flow
    ConfigKeys::FLOW_API_KEY => getenv('FLOW_API_KEY') ?: 'your-flow-api-key',
    ConfigKeys::FLOW_SECRET_KEY => getenv('FLOW_SECRET_KEY') ?: 'your-flow-secret-key',
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
    
    // Transbank/Webpay
    ConfigKeys::TRANSBANK_API_KEY => getenv('TRANSBANK_API_KEY') ?: '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C',
    ConfigKeys::TRANSBANK_COMMERCE_CODE => getenv('TRANSBANK_COMMERCE_CODE') ?: '597055555532',
    ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
    
    // MercadoPago
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => getenv('MERCADOPAGO_ACCESS_TOKEN') ?: 'your-mp-access-token',
    
    // URLs base
    ConfigKeys::BASE_URL => 'http://localhost:8000',
    
    // Configuración de caché
    ConfigKeys::CACHE_TTL => 3600, // 1 hora
    ConfigKeys::CACHE_PREFIX => 'raion_payment_',
]);

// ============================================================================
// PASO 2: Configurar Caché (PSR-16)
// ============================================================================

// Opción A: Usar Symfony Cache (recomendado para producción)
/*
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

$adapter = new FilesystemAdapter('payment_cache', 3600, __DIR__ . '/cache');
$cache = new Psr16Cache($adapter);
*/

// Opción B: Array Cache (solo para desarrollo/ejemplo)
class SimpleArrayCache implements \Psr\SimpleCache\CacheInterface
{
    private array $data = [];
    
    public function get($key, $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
    
    public function set($key, $value, $ttl = null): bool
    {
        $this->data[$key] = $value;
        return true;
    }
    
    public function delete($key): bool
    {
        unset($this->data[$key]);
        return true;
    }
    
    public function clear(): bool
    {
        $this->data = [];
        return true;
    }
    
    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }
    
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }
    
    public function has($key): bool
    {
        return isset($this->data[$key]);
    }
}

$cache = new SimpleArrayCache();

// ============================================================================
// PASO 3: Configurar Event Dispatcher (PSR-14)
// ============================================================================

// Implementación simple de Event Dispatcher
class SimpleEventDispatcher implements \Psr\EventDispatcher\EventDispatcherInterface
{
    private array $listeners = [];
    
    public function dispatch(object $event): object
    {
        $eventClass = get_class($event);
        
        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                $listener($event);
            }
        }
        
        return $event;
    }
    
    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }
}

$eventDispatcher = new SimpleEventDispatcher();

// ============================================================================
// PASO 4: Registrar Event Listeners
// ============================================================================

// Listener para TransactionCreatedEvent
$eventDispatcher->addListener(
    TransactionCreatedEvent::class,
    function (TransactionCreatedEvent $event) {
        echo "\n🎉 EVENTO: Transacción creada\n";
        echo "   Order ID: {$event->getOrderId()}\n";
        echo "   Gateway: {$event->getGatewayName()}\n";
        echo "   Token: {$event->getToken()}\n";
        echo "   Amount: {$event->getAmount()} {$event->getCurrency()}\n";
        echo "   Redirect URL: {$event->getRedirectUrl()}\n";
        
        // Aquí normalmente guardarías en base de datos:
        // DB::table('transactions')->insert([...]);
    }
);

// Listener para TransactionConfirmedEvent
$eventDispatcher->addListener(
    TransactionConfirmedEvent::class,
    function (TransactionConfirmedEvent $event) {
        echo "\n✅ EVENTO: Transacción confirmada\n";
        echo "   Order ID: {$event->getOrderId()}\n";
        echo "   Gateway: {$event->getGatewayName()}\n";
        echo "   Amount: {$event->getAmount()}\n";
        echo "   External ID: {$event->getExternalId()}\n";
        echo "   Status: {$event->getStatus()}\n";
        
        // Aquí normalmente:
        // - Actualizarías el estado de la orden en BD
        // - Enviarías email de confirmación
        // - Procesarías el pedido (activar servicio, enviar producto, etc.)
        // OrderProcessor::process($event->getOrderId());
        // Mail::send(...);
    }
);

// Listener para TransactionFailedEvent
$eventDispatcher->addListener(
    TransactionFailedEvent::class,
    function (TransactionFailedEvent $event) {
        echo "\n❌ EVENTO: Transacción fallida\n";
        echo "   Order ID: {$event->getOrderId()}\n";
        echo "   Gateway: {$event->getGatewayName()}\n";
        echo "   Error: {$event->getErrorMessage()}\n";
        echo "   Error Code: {$event->getErrorCode()}\n";
        echo "   Stage: {$event->getFailureStage()}\n";
        
        // Aquí normalmente:
        // - Actualizarías el estado a fallido
        // - Notificarías al cliente
        // - Logearías para análisis
    }
);

// ============================================================================
// PASO 5: Crear TransactionManager
// ============================================================================

$transactionManager = Selector::CreateTransactionManager(
    cache: $cache,
    eventDispatcher: $eventDispatcher,
    cacheTtl: 3600
);

echo "\n" . str_repeat("=", 80) . "\n";
echo "  EJEMPLO: Raion Payment Gateways con PSR-16 y PSR-14\n";
echo str_repeat("=", 80) . "\n";

// ============================================================================
// EJEMPLO 1: Crear transacción con Flow
// ============================================================================

echo "\n\n--- EJEMPLO 1: Crear transacción con Flow ---\n";

try {
    // Crear modelo de transacción
    $transaction = new Transaction(
        id: uniqid('tx_'),
        orderId: 'ORDER-' . time(),
        gateway: Gateways::Flow->value,
        amount: 50000,
        currency: 'CLP',
        status: TransactionStatus::Pending,
        metadata: [
            'description' => 'Compra de productos en línea',
            'email' => 'cliente@example.com',
            'customer_name' => 'Juan Pérez'
        ]
    );
    
    echo "Transacción creada: {$transaction->getId()}\n";
    echo "Order ID: {$transaction->getOrderId()}\n";
    
    // Obtener gateway
    $flowGateway = Selector::GetGatewayInstance(Gateways::Flow);
    
    // Crear en la pasarela (esto dispara TransactionCreatedEvent)
    $response = $transactionManager->createTransaction($flowGateway, $transaction);
    
    echo "\nRespuesta de la pasarela:\n";
    echo "Token: {$response->getToken()}\n";
    echo "URL: {$response->getUrl()}\n";
    
    // URL de redirección completa
    $redirectUrl = $flowGateway->getRedirectUrl($response->getUrl(), $response->getToken());
    echo "Redirect URL: {$redirectUrl}\n";
    
    echo "\n💡 En producción, redirigirías al usuario a: {$redirectUrl}\n";
    
    // Simular que guardamos el token para usar después
    $flowToken = $response->getToken();
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// ============================================================================
// EJEMPLO 2: Recuperar transacción desde caché
// ============================================================================

echo "\n\n--- EJEMPLO 2: Recuperar transacción desde caché ---\n";

try {
    $orderId = $transaction->getOrderId();
    
    // Recuperar por Order ID
    $cachedTransaction = $transactionManager->getTransaction('flow', $orderId);
    
    if ($cachedTransaction) {
        echo "✓ Transacción recuperada desde caché\n";
        echo "Order ID: {$cachedTransaction->getOrderId()}\n";
        echo "Status: {$cachedTransaction->getStatus()->name}\n";
        echo "Amount: {$cachedTransaction->getAmount()}\n";
        echo "Token: {$cachedTransaction->getToken()}\n";
    }
    
    // También se puede recuperar por token
    $cachedByToken = $transactionManager->getTransactionByToken('flow', $flowToken);
    
    if ($cachedByToken) {
        echo "\n✓ También recuperada por token\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// ============================================================================
// EJEMPLO 3: Simular callback y confirmación (Flow)
// ============================================================================

echo "\n\n--- EJEMPLO 3: Simular callback de Flow ---\n";

try {
    // Simular datos del callback de Flow (normalmente viene en $_POST)
    $callbackData = [
        'token' => $flowToken,
    ];
    
    // Usar el procesador de callbacks
    $processor = new FlowCallbackProcessor();
    
    // Extraer token
    $token = $processor->extractToken($callbackData);
    echo "Token extraído del callback: {$token}\n";
    
    // Recuperar transacción
    $transaction = $transactionManager->getTransactionByToken('flow', $token);
    
    if ($transaction) {
        echo "Transacción encontrada: {$transaction->getOrderId()}\n";
        
        // IMPORTANTE: En producción, aquí llamarías al gateway real para confirmar:
        // $flowGateway = Selector::GetGatewayInstance(Gateways::Flow);
        // $updatedTransaction = $transactionManager->confirmTransaction(
        //     $flowGateway,
        //     $transaction,
        //     $callbackData
        // );
        
        echo "\n💡 En producción, aquí confirmarías con la pasarela real\n";
        echo "   Esto dispararía TransactionConfirmedEvent o TransactionFailedEvent\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// ============================================================================
// EJEMPLO 4: Crear transacción con Webpay
// ============================================================================

echo "\n\n--- EJEMPLO 4: Crear transacción con Webpay ---\n";

try {
    $transaction = new Transaction(
        id: uniqid('tx_'),
        orderId: 'ORDER-WP-' . time(),
        gateway: Gateways::Webpay->value,
        amount: 75000,
        currency: 'CLP',
        status: TransactionStatus::Pending,
        metadata: [
            'description' => 'Suscripción Premium - 1 mes',
            'email' => 'usuario@example.com'
        ]
    );
    
    $webpayGateway = Selector::GetGatewayInstance(Gateways::Webpay);
    $response = $transactionManager->createTransaction($webpayGateway, $transaction);
    
    echo "Transacción Webpay creada\n";
    echo "Token: {$response->getToken()}\n";
    
    // Guardar para simular callback
    $webpayToken = $response->getToken();
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// ============================================================================
// EJEMPLO 5: Procesador de callback para Webpay
// ============================================================================

echo "\n\n--- EJEMPLO 5: Callback de Webpay ---\n";

try {
    // Simular callback de Webpay (normalmente viene en $_GET)
    $callbackData = [
        'token_ws' => $webpayToken,
    ];
    
    $processor = new WebpayCallbackProcessor();
    $token = $processor->extractToken($callbackData);
    
    echo "Token de Webpay: {$token}\n";
    
    $transaction = $transactionManager->getTransactionByToken('webpay', $token);
    
    if ($transaction) {
        echo "Transacción recuperada: {$transaction->getOrderId()}\n";
        
        // Construir respuesta (en producción, después de confirmar)
        $response = $processor->buildResponse(
            ['buyOrder' => $transaction->getOrderId()], 
            true
        );
        
        echo "Tipo de respuesta: {$response['type']}\n";
        echo "URL de redirección: {$response['url']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// ============================================================================
// EJEMPLO 6: Resumen de transacciones en caché
// ============================================================================

echo "\n\n--- EJEMPLO 6: Resumen ---\n";

echo "\n📊 Transacciones en caché:\n";
echo "- Flow: ORDER-{$transaction->getOrderId()}\n";
echo "- Webpay: ORDER-WP-...\n";

echo "\n✨ Características demostradas:\n";
echo "  ✓ Creación de transacciones con TransactionManager\n";
echo "  ✓ Almacenamiento automático en caché (PSR-16)\n";
echo "  ✓ Dispatch automático de eventos (PSR-14)\n";
echo "  ✓ Recuperación de transacciones por Order ID y Token\n";
echo "  ✓ Procesadores de callbacks específicos por pasarela\n";
echo "  ✓ Event listeners para lógica de negocio desacoplada\n";

echo "\n📚 Para más información, ver:\n";
echo "  - USAGE_PSR.md: Documentación completa\n";
echo "  - README.md: Información general\n";
echo "  - exampleController.php: Ejemplo de controlador CodeIgniter\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// ============================================================================
// EJEMPLO 7: Comparación antes/después
// ============================================================================

echo "--- COMPARACIÓN: Antes vs Después ---\n\n";

echo "❌ ANTES (sin PSR):\n";
echo "   1. \$gateway->createTransaction(...)\n";
echo "   2. \$db->insert('pasarela', [...])  <- Manual\n";
echo "   3. redirect(...)\n";
echo "   4. En callback: consultar DB, validar, actualizar, enviar email <- Todo manual\n";

echo "\n✅ DESPUÉS (con PSR):\n";
echo "   1. \$transactionManager->createTransaction(...)\n";
echo "   2. <- Caché y eventos automáticos\n";
echo "   3. redirect(...)\n";
echo "   4. En callback: TransactionManager lo maneja, listeners procesan <- Desacoplado\n";

echo "\n💡 Beneficios:\n";
echo "   - Menos código boilerplate\n";
echo "   - Más testeable (mocks de caché/eventos)\n";
echo "   - Lógica de negocio desacoplada\n";
echo "   - Retrocompatible con API anterior\n";

echo "\n";
