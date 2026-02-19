[![PHP Composer](https://github.com/tsukiro/payment-gateways-library/actions/workflows/php.yml/badge.svg)](https://github.com/tsukiro/payment-gateways-library/actions/workflows/php.yml)

# Raion Payment Gateways Library

Librería PHP para integrar múltiples pasarelas de pago en Chile: Flow, Transbank/Webpay y MercadoPago.

## 🚀 Características

- ✅ Soporte para múltiples gateways: **Flow**, **Transbank/Webpay** y **MercadoPago**
- ✅ Interfaz unificada para todos los gateways
- ✅ **Configuración flexible**: mediante array o variables de entorno
- ✅ Fácil de usar y extender
- ✅ Compatible con PHP 8.0+

## 📦 Instalación

```bash
composer require raion/payment-gateways-library
```

## 🔧 Configuración

> **📚 Documentación completa**: Para una guía detallada sobre el uso de ConfigKeys, consulta [CONFIG_KEYS.md](CONFIG_KEYS.md)

### Opción 1: Configuración mediante Array con ConfigKeys (Recomendado)

```php
<?php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    // Flow
    ConfigKeys::FLOW_API_KEY => 'tu-flow-api-key',
    ConfigKeys::FLOW_SECRET_KEY => 'tu-flow-secret-key',
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
    
    // Transbank/Webpay
    ConfigKeys::TRANSBANK_API_KEY => 'tu-transbank-api-key',
    ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
    ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
    
    // MercadoPago
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-tu-access-token',
    
    // URL base de tu aplicación
    ConfigKeys::BASE_URL => 'https://tu-sitio.com',
    
    // URLs de callback (opcional - usa placeholder {id})
    ConfigKeys::FLOW_CONFIRMATION_URL => 'https://tu-sitio.com/webhooks/flow/confirm/{id}',
    ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://tu-sitio.com/webhooks/webpay/confirm/{id}',
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://tu-sitio.com/webhooks/mp/success/{id}',
]);
```

**Beneficios de usar ConfigKeys:**
- 🎯 Autocompletado en tu IDE
- 🔒 Type-safe: previene errores de tipeo
- 📝 Prefijos claros que identifican a qué gateway pertenece cada clave
- 📖 Documentación inline en cada constante

### Opción 2: Variables de Entorno

Crea un archivo `.env` o configura variables de entorno:

```bash
FLOW_API_KEY=tu-flow-api-key
FLOW_SECRET_KEY=tu-flow-secret-key
FLOW_API_URL=https://sandbox.flow.cl/api
WEB_BASE_URL=https://tu-sitio.com
TRANSBANK_API_KEY=tu-transbank-api-key
TRANSBANK_COMMERCE_CODE=597055555532
TRANSBANK_ENVIRONMENT=INTEGRATION
MERCADOPAGO_ACCESS_TOKEN=APP_USR-tu-access-token
```

La librería buscará automáticamente en las variables de entorno si no usas `setConfig()`.

## 💻 Uso Básico

### Crear una transacción con Flow

```php
<?php
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

// Obtener instancia del gateway
$gateway = Selector::GetGatewayInstance(Gateways::Flow);

// Crear transacción
$response = $gateway->createTransaction(
    id: 'ORDER-12345',
    amount: 50000,
    currency: 'CLP',
    description: 'Compra de productos',
    email: 'cliente@example.com'
);

// Redirigir al usuario
$urlRedireccion = $gateway->getRedirectUrl($response->getUrl(), $response->getToken());
header("Location: $urlRedireccion");
```

### Crear una transacción con Webpay

```php
<?php
$gateway = Selector::GetGatewayInstance(Gateways::Webpay);

$response = $gateway->createTransaction(
    id: 'ORDER-12346',
    amount: 75000,
    currency: 'CLP',
    description: 'Compra en tienda',
    email: 'cliente@example.com'
);

$urlRedireccion = $gateway->getRedirectUrl($response->getUrl(), $response->getToken());
header("Location: $urlRedireccion");
```

### Crear una transacción con MercadoPago

```php
<?php
$gateway = Selector::GetGatewayInstance(Gateways::MercadoPago);

$response = $gateway->createTransaction(
    id: 'ORDER-12347',
    amount: 100000,
    currency: 'CLP',
    description: 'Suscripción mensual',
    email: 'cliente@example.com'
);

// MercadoPago devuelve la URL directamente
header("Location: {$response->getUrl()}");
```

### Verificar estado de transacción

```php
<?php
$gateway = Selector::GetGatewayInstance(Gateways::Flow);

// Cuando el usuario regresa después del pago
$token = $_GET['token'] ?? null;

if ($token) {
    $estado = $gateway->getTransactionInProcess($token);
    
    // Procesar según el estado
    if ($estado['status'] === 2) {
        // Pago exitoso
        echo "¡Pago completado!";
    }
}
```

## 📚 Documentación Completa

- [CONFIG_EXAMPLE.md](CONFIG_EXAMPLE.md) - Guía completa de configuración
- [example.php](example.php) - Ejemplos de uso para cada gateway

## 🔐 Seguridad

⚠️ **Importante**: 
- Nunca incluyas tus claves API directamente en el código fuente
- Usa variables de entorno o sistemas de gestión de secretos
- No versiones archivos con credenciales en Git

```php
// ❌ NO hagas esto
GatewayConfig::setConfig(['APIKEY' => 'mi-clave-literal']);

// ✅ Haz esto
GatewayConfig::setConfig(['APIKEY' => getenv('FLOW_APIKEY')]);
```

## 🛠️ Gateways Soportados

| Gateway | Estado | Métodos |
|---------|--------|---------|
| Flow | ✅ Completo | Crear transacción, verificar estado, confirmar |
| Transbank/Webpay | ✅ Completo | Crear transacción, verificar estado, confirmar |
| MercadoPago | ✅ Completo | Crear transacción, verificar estado |

## 📋 Requisitos

- PHP 8.0 o superior
- Composer
- Extensiones PHP: curl, json

## 🧪 Testing

```bash
vendor/bin/phpunit
```

## 📝 Sistema de Logging (PSR-3)

La librería implementa el estándar PSR-3 de logging para facilitar el debugging y auditoría de transacciones.

### Loggers Disponibles

#### NullLogger (por defecto)
No registra nada. Ideal para producción sin overhead de performance:

```php
use Raion\Gateways\Logging\NullLogger;

$logger = new NullLogger();
$gateway = Selector::GetGatewayInstance(Gateways::Flow, $logger);
```

#### FileLogger
Registra eventos en archivo con niveles configurables:

```php
use Raion\Gateways\Logging\FileLogger;
use Psr\Log\LogLevel;

// Registrar solo INFO y superiores
$logger = new FileLogger(
    logFile: __DIR__ . '/logs/payment.log',
    minLevel: LogLevel::INFO
);

$gateway = Selector::GetGatewayInstance(Gateways::Flow, $logger);
```

### Niveles de Log

- `DEBUG`: Información detallada de debugging
- `INFO`: Eventos informativos (transacción creada, completada)
- `NOTICE`: Eventos normales pero significativos
- `WARNING`: Advertencias
- `ERROR`: Errores en tiempo de ejecución
- `CRITICAL`: Condiciones críticas
- `ALERT`: Requiere acción inmediata
- `EMERGENCY`: Sistema inutilizable

### Ejemplo de Logs

```
[2024-02-19 18:00:00] INFO: Creating Flow transaction {"gateway":"flow","order_id":"ORDER-123","amount":10000}
[2024-02-19 18:00:01] INFO: Flow transaction created successfully {"gateway":"flow","token":"ABC123"}
[2024-02-19 18:00:05] ERROR: Flow transaction creation failed {"gateway":"flow","error":"Invalid API key"}
```

### Logger Personalizado

Puedes usar cualquier logger compatible con PSR-3:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('payments');
$logger->pushHandler(new StreamHandler(__DIR__ . '/payment.log'));

$gateway = Selector::GetGatewayInstance(Gateways::Flow, $logger);
```

## ✅ Validación Automática

La librería valida automáticamente todos los parámetros antes de enviarlos al gateway.

### Reglas de Validación

#### ID de Transacción
- Longitud: 1-255 caracteres
- Solo alfanuméricos, guiones (-) y guiones bajos (_)

```php
// ✅ Válido
$gateway->createTransaction('ORDER-123', ...);

// ❌ Inválido - caracteres especiales
$gateway->createTransaction('ORDER@123!', ...);
```

#### Monto

Montos mínimos por gateway:

| Gateway | Monto Mínimo |
|---------|--------------|
| Flow | 50 (CLP/UF) |
| Webpay | 50 (CLP) |
| MercadoPago | 1 (cualquier moneda) |

Monto máximo: 999,999,999 para todos

```php
// ✅ Válido para Flow
$gateway->createTransaction('ORDER-123', 10000, 'CLP', ...);

// ❌ Inválido - muy bajo para Flow
$gateway->createTransaction('ORDER-123', 40, 'CLP', ...);
```

#### Moneda

Monedas soportadas por gateway:

| Gateway | Monedas |
|---------|---------|
| Flow | CLP, UF |
| Webpay | CLP |
| MercadoPago | CLP, ARS, BRL, MXN, USD |

```php
// ✅ Válido
$gateway->createTransaction('ORDER-123', 10000, 'CLP', ...);

// ❌ Inválido - Flow no soporta USD
$flowGateway->createTransaction('ORDER-123', 10000, 'USD', ...);
```

#### Descripción
- Longitud: 3-500 caracteres

```php
// ✅ Válido
$gateway->createTransaction(..., 'Compra de producto');

// ❌ Inválido - muy corta
$gateway->createTransaction(..., 'OK');
```

#### Email
- Formato RFC-compliant
- Máximo 254 caracteres

```php
// ✅ Válido
$gateway->createTransaction(..., 'user@example.com');

// ❌ Inválido
$gateway->createTransaction(..., 'invalid-email');
```

### Validación Manual

```php
use Raion\Gateways\Validation\TransactionValidator;
use Raion\Gateways\Exceptions\ValidationException;

$validator = new TransactionValidator();

try {
    $validator->validateTransaction(
        gateway: 'flow',
        id: 'ORDER-123',
        amount: 10000,
        currency: 'CLP',
        description: 'Test product',
        email: 'user@example.com'
    );
} catch (ValidationException $e) {
    echo "Error: " . $e->getMessage();
}
```

## 🧪 Testing

```php
use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

class PaymentTest extends TestCase
{
    protected function setUp(): void
    {
        GatewayConfig::setConfig([
            ConfigKeys::FLOW_API_KEY => 'test-api-key',
            ConfigKeys::FLOW_SECRET_KEY => 'test-secret',
            ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
            ConfigKeys::BASE_URL => 'http://localhost'
        ]);
    }

    protected function tearDown(): void
    {
        GatewayConfig::clear();
    }
}
```

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-caracteristica`)
3. Commit tus cambios (`git commit -am 'Agrega nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Abre un Pull Request

## 📄 Licencia

[MIT License](LICENSE)

## 🔗 Enlaces Útiles

### Documentación de la Librería
- [Guía de ConfigKeys](CONFIG_KEYS.md) - Constantes type-safe para configuración
- [Guía de Excepciones](EXCEPTIONS.md) - Sistema de excepciones personalizadas

### Documentación de Gateways
- [Documentación Flow](https://www.flow.cl/docs/api.html)
- [Documentación Transbank](https://www.transbankdevelopers.cl/)
- [Documentación MercadoPago](https://www.mercadopago.cl/developers)

## 📞 Soporte

Para reportar bugs o solicitar features, por favor abre un [issue](../../issues) en GitHub.
