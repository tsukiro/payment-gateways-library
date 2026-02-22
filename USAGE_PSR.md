# Guía de Uso: Caché y Eventos PSR

Esta guía explica cómo utilizar las nuevas funcionalidades de caché (PSR-16) y eventos (PSR-14) en la librería Raion Payment Gateways.

## 📋 Tabla de Contenidos

- [Introducción](#introducción)
- [Requisitos](#requisitos)
- [Arquitectura](#arquitectura)
- [Configuración](#configuración)
- [TransactionManager](#transactionmanager)
- [Sistema de Eventos](#sistema-de-eventos)
- [Procesadores de Callbacks](#procesadores-de-callbacks)
- [Ejemplo Completo](#ejemplo-completo)
- [Migración desde Arquitectura Anterior](#migración-desde-arquitectura-anterior)

## Introducción

La librería ahora soporta:

- **PSR-16 (Simple Cache)**: Almacenamiento temporal de transacciones en caché
- **PSR-14 (Event Dispatcher)**: Sistema de eventos para hooks en el ciclo de vida de pagos
- **TransactionManager**: Orquestador que simplifica el flujo de pagos

### ¿Por qué usar estas características?

**Antes** (ejemplo del controlador tradicional):
- El controlador maneja toda la lógica de creación, confirmación y almacenamiento
- Tokens y datos temporales se guardan manualmente en base de datos
- Código repetitivo para cada pasarela
- Difícil de testear y mantener

**Ahora** (con PSR):
- La librería gestiona el caché de tokens/IDs automáticamente
- Los eventos permiten reaccionar a acciones clave sin acoplar código
- Controladores más limpios y enfocados
- Fácil de testear con mocks de caché y eventos

## Requisitos

```json
{
  "require": {
    "psr/simple-cache": "^3.0",
    "psr/event-dispatcher": "^1.0"
  }
}
```

**Implementaciones recomendadas:**

- **Symfony Cache** (para Symfony/Laravel):
  ```bash
  composer require symfony/cache
  ```

- **Laravel Cache** (para Laravel):
  ```php
  // Ya incluido, usar Illuminate\Support\Facades\Cache
  ```

- **Simple Cache** (para proyectos simples):
  ```bash
  composer require cache/array-adapter
  composer require cache/filesystem-adapter
  ```

## Arquitectura

```
┌─────────────┐
│ Controller  │
└──────┬──────┘
       │
       ▼
┌──────────────────┐      ┌──────────────┐
│ TransactionMgr   │─────▶│   Gateway    │
└──────┬───────────┘      └──────────────┘
       │
       ├─────▶ Cache (PSR-16)
       │       - Tokens temporales
       │       - IDs de transacción
       │
       └─────▶ EventDispatcher (PSR-14)
               - TransactionCreatedEvent
               - TransactionConfirmedEvent
               - TransactionFailedEvent
```

## Configuración

### 1. Configurar Caché (PSR-16)

#### Opción A: Symfony Cache

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Crear adaptador
$adapter = new FilesystemAdapter(
    namespace: 'payment_cache',
    defaultLifetime: 3600,
    directory: '/path/to/cache'
);

// Convertir a PSR-16
$cache = new Psr16Cache($adapter);
```

#### Opción B: Laravel Cache

```php
use Illuminate\Support\Facades\Cache;

// Laravel ya implementa PSR-16
$cache = Cache::store('file'); // o 'redis', 'memcached', etc.
```

#### Opción C: Array Cache (solo para desarrollo)

```php
use Cache\Adapter\PHPArray\ArrayCachePool;

$cache = new ArrayCachePool();
```

### 2. Configurar Event Dispatcher (PSR-14)

#### Opción A: Symfony Event Dispatcher

```bash
composer require symfony/event-dispatcher
```

```php
use Symfony\Component\EventDispatcher\EventDispatcher;

$eventDispatcher = new EventDispatcher();
```

#### Opción B: Laravel Events

```php
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$eventDispatcher = new Dispatcher(new Container());
```

#### Opción C: Implementación Simple

```php
use Psr\EventDispatcher\EventDispatcherInterface;

class SimpleEventDispatcher implements EventDispatcherInterface
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
```

### 3. Configurar Claves en GatewayConfig

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    // ... otras configuraciones ...
    
    // Configuración de caché
    ConfigKeys::CACHE_TTL => 3600, // 1 hora
    ConfigKeys::CACHE_PREFIX => 'raion_payment_',
]);
```

## TransactionManager

El `TransactionManager` es el componente central que orquesta el flujo de pagos.

### Creación

```php
use Raion\Gateways\Selector;

$transactionManager = Selector::CreateTransactionManager(
    cache: $cache,
    eventDispatcher: $eventDispatcher,
    cacheTtl: 3600, // opcional
    cachePrefix: 'my_app_' // opcional
);
```

### Métodos Principales

#### createTransaction()

Crea una transacción en la pasarela y la almacena en caché:

```php
use Raion\Gateways\Models\Transaction;
use Raion\Gateways\Models\TransactionStatus;
use Raion\Gateways\Models\Gateways;
use Raion\Gateways\Selector;

// 1. Crear modelo de transacción
$transaction = new Transaction(
    id: uniqid('tx_'),
    orderId: 'ORDER-12345',
    gateway: Gateways::Flow->value,
    amount: 50000,
    currency: 'CLP',
    status: TransactionStatus::Pending,
    metadata: [
        'description' => 'Compra de productos',
        'email' => 'cliente@example.com'
    ]
);

// 2. Obtener gateway
$gateway = Selector::GetGatewayInstance(Gateways::Flow);

// 3. Crear transacción
$response = $transactionManager->createTransaction($gateway, $transaction);

// 4. Redirigir al usuario
header('Location: ' . $gateway->getRedirectUrl($response->getUrl(), $response->getToken()));
```

#### getTransaction()

Recupera una transacción desde caché:

```php
$transaction = $transactionManager->getTransaction('flow', 'ORDER-12345');

if ($transaction) {
    echo "Estado: " . $transaction->getStatus()->name;
}
```

#### getTransactionByToken()

Recupera una transacción usando el token:

```php
$transaction = $transactionManager->getTransactionByToken('flow', $token);
```

#### confirmTransaction()

Confirma una transacción después del callback:

```php
$transaction = $transactionManager->getTransactionByToken('flow', $token);

if ($transaction) {
    $updatedTransaction = $transactionManager->confirmTransaction(
        gateway: $gateway,
        transaction: $transaction,
        callbackData: $_POST
    );
    
    if ($updatedTransaction->isConfirmed()) {
        echo "¡Pago confirmado!";
    }
}
```

## Sistema de Eventos

### Eventos Disponibles

#### 1. TransactionCreatedEvent

Disparado después de crear una transacción exitosamente.

```php
use Raion\Gateways\Events\TransactionCreatedEvent;

$eventDispatcher->addListener(
    TransactionCreatedEvent::class,
    function (TransactionCreatedEvent $event) {
        // Guardar en base de datos
        DB::table('transactions')->insert([
            'order_id' => $event->getOrderId(),
            'gateway' => $event->getGatewayName(),
            'token' => $event->getToken(),
            'amount' => $event->getAmount(),
            'currency' => $event->getCurrency(),
            'status' => 'pending',
            'created_at' => $event->getTimestamp()->format('Y-m-d H:i:s'),
        ]);
        
        // Log
        error_log("Transaction created: {$event->getOrderId()}");
    }
);
```

#### 2. TransactionConfirmedEvent

Disparado cuando una transacción se confirma exitosamente.

```php
use Raion\Gateways\Events\TransactionConfirmedEvent;

$eventDispatcher->addListener(
    TransactionConfirmedEvent::class,
    function (TransactionConfirmedEvent $event) {
        // Actualizar orden en base de datos
        DB::table('orders')
            ->where('id', $event->getOrderId())
            ->update(['status' => 'paid']);
        
        // Enviar email de confirmación
        Mail::send('emails.payment-confirmed', [
            'orderId' => $event->getOrderId(),
            'amount' => $event->getAmount(),
        ], function ($message) {
            $message->to('cliente@example.com')
                   ->subject('Pago Confirmado');
        });
        
        // Procesar orden (envío, activación, etc.)
        OrderProcessor::process($event->getOrderId());
    }
);
```

#### 3. TransactionFailedEvent

Disparado cuando una transacción falla.

```php
use Raion\Gateways\Events\TransactionFailedEvent;

$eventDispatcher->addListener(
    TransactionFailedEvent::class,
    function (TransactionFailedEvent $event) {
        // Actualizar estado
        DB::table('orders')
            ->where('id', $event->getOrderId())
            ->update(['status' => 'failed']);
        
        // Notificar al cliente
        Mail::send('emails.payment-failed', [
            'orderId' => $event->getOrderId(),
            'error' => $event->getErrorMessage(),
        ], function ($message) {
            $message->to('cliente@example.com')
                   ->subject('Pago Fallido');
        });
        
        // Log para análisis
        error_log("Payment failed: {$event->getOrderId()} - {$event->getErrorMessage()}");
    }
);
```

### Propiedades de Eventos

Todos los eventos heredan de `PaymentEvent`:

```php
$event->getOrderId();        // ID de la orden
$event->getGatewayName();    // Nombre de la pasarela
$event->getTimestamp();      // Timestamp del evento
$event->getTransactionData(); // Datos adicionales
$event->toArray();           // Convertir a array
```

## Procesadores de Callbacks

Los procesadores de callbacks simplifican el manejo de respuestas de cada pasarela.

### FlowCallbackProcessor

```php
use Raion\Gateways\Callbacks\FlowCallbackProcessor;
use Raion\Gateways\Models\Gateways;
use Raion\Gateways\Selector;

$processor = new FlowCallbackProcessor();
$gateway = Selector::GetGatewayInstance(Gateways::Flow);

// Extraer token
$token = $processor->extractToken($_POST);

if ($token) {
    // Procesar confirmación
    $confirmationData = $processor->processConfirmation($gateway, $token);
    
    // Verificar éxito
    $success = FlowCallbackProcessor::isSuccessful($confirmationData);
    
    // Construir respuesta
    $response = $processor->buildResponse($confirmationData, $success);
    
    // Enviar respuesta
    header('Content-Type: application/json');
    http_response_code($response['status']);
    echo json_encode($response['data']);
}
```

### WebpayCallbackProcessor

```php
use Raion\Gateways\Callbacks\WebpayCallbackProcessor;

$processor = new WebpayCallbackProcessor();
$gateway = Selector::GetGatewayInstance(Gateways::Webpay);

$token = $processor->extractToken($_GET);

if ($token) {
    $confirmationData = $processor->processConfirmation($gateway, $token);
    $success = WebpayCallbackProcessor::isSuccessful($confirmationData);
    
    // Validar monto
    if ($success && !WebpayCallbackProcessor::validateAmount($confirmationData, 50000)) {
        // Monto no coincide
        $success = false;
    }
    
    $response = $processor->buildResponse($confirmationData, $success);
    
    // Redirigir
    header('Location: ' . $response['url']);
}
```

### MercadoPagoCallbackProcessor

```php
use Raion\Gateways\Callbacks\MercadoPagoCallbackProcessor;

$processor = new MercadoPagoCallbackProcessor();
$gateway = Selector::GetGatewayInstance(Gateways::MercadoPago);

$token = $processor->extractToken($_GET);
$collectionStatus = MercadoPagoCallbackProcessor::getCollectionStatus($_GET);

if ($token && $collectionStatus === 'approved') {
    $confirmationData = $processor->processConfirmation($gateway, $token);
    $success = MercadoPagoCallbackProcessor::isSuccessful($confirmationData);
    
    $response = $processor->buildResponse($confirmationData, $success);
    header('Location: ' . $response['url']);
}
```

## Ejemplo Completo

Ver [exampleWithPSR.php](exampleWithPSR.php) para un ejemplo completo funcional.

### Controlador Simplificado

```php
<?php

use Raion\Gateways\Selector;
use Raion\Gateways\Models\{Transaction, TransactionStatus, Gateways};
use Raion\Gateways\Callbacks\FlowCallbackProcessor;

class PaymentController
{
    private $transactionManager;
    
    public function __construct($cache, $eventDispatcher)
    {
        $this->transactionManager = Selector::CreateTransactionManager(
            $cache,
            $eventDispatcher
        );
    }
    
    // Crear pago
    public function create($orderId, $amount, $gateway)
    {
        $transaction = new Transaction(
            id: uniqid('tx_'),
            orderId: $orderId,
            gateway: $gateway,
            amount: $amount,
            currency: 'CLP',
            metadata: [
                'description' => "Orden $orderId",
                'email' => 'cliente@example.com'
            ]
        );
        
        $gatewayInstance = Selector::GetGatewayInstance(Gateways::from($gateway));
        $response = $this->transactionManager->createTransaction($gatewayInstance, $transaction);
        
        header('Location: ' . $gatewayInstance->getRedirectUrl(
            $response->getUrl(),
            $response->getToken()
        ));
    }
    
    // Confirmar pago (callback)
    public function confirm($gateway)
    {
        $processor = new FlowCallbackProcessor(); // o WebpayCallbackProcessor, etc.
        $gatewayInstance = Selector::GetGatewayInstance(Gateways::from($gateway));
        
        $token = $processor->extractToken($_POST);
        $transaction = $this->transactionManager->getTransactionByToken($gateway, $token);
        
        if ($transaction) {
            $this->transactionManager->confirmTransaction(
                $gatewayInstance,
                $transaction,
                $_POST
            );
        }
        
        $response = $processor->buildResponse([], true);
        // Enviar respuesta...
    }
}
```

## Migración desde Arquitectura Anterior

### Antes

```php
// Crear transacción
$response = $gateway->createTransaction(...);

// Guardar manualmente
$this->db->insert('pasarela', [
    'token' => $response->getToken(),
    'orden_id' => $orderId,
    // ...
]);

// Redirigir
redirect($gateway->getRedirectUrl(...));
```

### Después

```php
// Todo en uno: crear, cachear, disparar evento
$response = $transactionManager->createTransaction($gateway, $transaction);

// Los listeners del evento ya guardaron en BD
// Solo redirigir
redirect($gateway->getRedirectUrl(...));
```

### Ventajas

✅ **Menos código**: El manager maneja caché y eventos  
✅ **Más testeable**: Mock fácil de caché y eventos  
✅ **Desacoplado**: Lógica de negocio en listeners  
✅ **Retrocompatible**: La API anterior sigue funcionando  
✅ **Flexible**: Puedes usar solo caché, solo eventos, o ambos

### Compatibilidad

La arquitectura anterior **sigue funcionando**. Las nuevas características son **opt-in**:

```php
// Antiguo (aún funciona)
$gateway = Selector::GetGatewayInstance(Gateways::Flow);
$response = $gateway->createTransaction(...);

// Nuevo (opcional)
$manager = Selector::CreateTransactionManager($cache, $eventDispatcher);
$response = $manager->createTransaction($gateway, $transaction);
```

## Mejores Prácticas

1. **Usa listeners para lógica de negocio**: No mezcles confirmación de pago con procesamiento de orden
2. **Configura TTL apropiado**: 1 hora es suficiente para la mayoría de casos
3. **Limpia caché después de confirmar**: Usa `clearTransaction()` si es necesario
4. **Valida montos en callbacks**: Especialmente en Webpay
5. **Loggea eventos**: Útil para debugging y análisis
6. **Testea con mocks**: PSR facilita testing unitario

## Troubleshooting

### El caché no persiste datos

- Verifica que el adaptador esté configurado correctamente
- Revisa permisos de escritura (para FilesystemAdapter)
- Confirma que el TTL no sea muy corto

### Los eventos no se disparan

- Verifica que hayas registrado los listeners antes de usar el manager
- Confirma que el EventDispatcher implemente correctamente PSR-14

### Token no encontrado en callback

- Verifica que el token esté siendo enviado por la pasarela
- Revisa el método correcto (GET vs POST) según la pasarela
- Confirma que no haya expirado el caché (TTL)

## Soporte

Para más información, ver:
- [README.md](README.md)
- [CONFIG_KEYS.md](CONFIG_KEYS.md)
- [exampleWithPSR.php](exampleWithPSR.php)
