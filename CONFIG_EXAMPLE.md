# Configuración de Raion Payment Gateways Library

Esta librería soporta múltiples métodos de configuración para adaptarse a diferentes necesidades.

> **📚 Documentación completa**: Para una guía detallada sobre el uso de ConfigKeys, consulta [CONFIG_KEYS.md](CONFIG_KEYS.md)

## Métodos de Configuración

### 1. Configuración mediante Array con ConfigKeys (Recomendado)

La forma más flexible y recomendada es usar el método `setConfig()` con las constantes de `ConfigKeys`:

```php
<?php

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

// Configurar todos los gateways al inicio de tu aplicación
GatewayConfig::setConfig([
    // Configuración para Flow
    ConfigKeys::FLOW_API_KEY => 'tu-flow-api-key',
    ConfigKeys::FLOW_SECRET_KEY => 'tu-flow-secret-key',
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',  // o https://www.flow.cl/api para producción
    
    // URLs de callback para Flow (opcional, usa placeholder {id})
    ConfigKeys::FLOW_CONFIRMATION_URL => 'https://tu-sitio.com/pago/confirmar/flow/{id}',
    ConfigKeys::FLOW_RETURN_URL => 'https://tu-sitio.com/pago/resultado/flow/{id}',
    
    // Configuración para Transbank/Webpay
    ConfigKeys::TRANSBANK_API_KEY => 'tu-transbank-api-key',
    ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
    ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',  // o 'PRODUCTION'
    
    // URL de callback para Webpay (opcional, usa placeholder {id})
    ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://tu-sitio.com/pago/confirmar/webpay/{id}',
    
    // Configuración para MercadoPago
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-tu-access-token',
    
    // URLs de callback para MercadoPago (opcional, usa placeholder {id})
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://tu-sitio.com/pago/confirmar/mercadopago/{id}',
    ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://tu-sitio.com/pago/error/mercadopago/{id}',
    ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://tu-sitio.com/pago/pendiente/mercadopago/{id}',
    
    // Configuración común
    ConfigKeys::BASE_URL => 'https://tu-sitio.com'
]);

// Usar los gateways normalmente
$gateway = Selector::GetGatewayInstance(Gateways::Flow);
$response = $gateway->createTransaction('ORDER-123', 50000, 'CLP', 'Compra de productos', 'cliente@email.com');
```

**Beneficios de usar ConfigKeys:**
- 🎯 Autocompletado en tu IDE
- 🔒 Type-safe: previene errores de tipeo
- 📝 Prefijos claros que identifican a qué gateway pertenece cada clave

### 2. Configuración mediante Variables de Entorno

También puedes usar variables de entorno (útil para contenedores Docker, CI/CD, etc.):

```bash
# .env o variables de entorno del sistema
FLOW_API_KEY=tu-flow-api-key
FLOW_SECRET_KEY=tu-flow-secret-key
FLOW_API_URL=https://sandbox.flow.cl/api
WEB_BASE_URL=https://tu-sitio.com
TRANSBANK_API_KEY=tu-transbank-api-key
TRANSBANK_COMMERCE_CODE=597055555532
TRANSBANK_ENVIRONMENT=INTEGRATION
MERCADOPAGO_ACCESS_TOKEN=APP_USR-tu-access-token

# URLs de callback (opcional)
FLOW_CONFIRMATION_URL=https://tu-sitio.com/webhooks/flow/confirm/{id}
FLOW_RETURN_URL=https://tu-sitio.com/pago/resultado/flow/{id}
WEBPAY_CONFIRMATION_URL=https://tu-sitio.com/webhooks/webpay/confirm/{id}
MERCADOPAGO_SUCCESS_URL=https://tu-sitio.com/webhooks/mp/success/{id}
MERCADOPAGO_FAILURE_URL=https://tu-sitio.com/webhooks/mp/failure/{id}
MERCADOPAGO_PENDING_URL=https://tu-sitio.com/webhooks/mp/pending/{id}
```

```php
<?php
// No necesitas llamar a setConfig(), la librería buscará en las variables de entorno automáticamente
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

$gateway = Selector::GetGatewayInstance(Gateways::Webpay);
```

### 3. Configuración Híbrida

Puedes combinar ambos métodos. La librería buscará en el siguiente orden:
1. Configuración establecida con `setConfig()`
2. Variables de entorno
3. Valores por defecto (si se proporcionan)

```php
<?php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

// Establecer solo algunas configuraciones
GatewayConfig::setConfig([
    ConfigKeys::FLOW_API_KEY => 'tu-flow-api-key',
    ConfigKeys::FLOW_SECRET_KEY => 'tu-flow-secret-key',
]);

// BASE_URL, TRANSBANK_*, etc. se tomarán de las variables de entorno
```

## Configuración por Gateway

### Flow

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    ConfigKeys::FLOW_API_KEY => 'tu-api-key',              // Requerido
    ConfigKeys::FLOW_SECRET_KEY => 'tu-secret-key',        // Requerido
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api', // Requerido
    ConfigKeys::BASE_URL => 'https://tu-sitio.com',   // Requerido
    
    // URLs de callback (opcional)
    ConfigKeys::FLOW_CONFIRMATION_URL => 'https://tu-sitio.com/webhooks/flow/confirm/{id}',
    ConfigKeys::FLOW_RETURN_URL => 'https://tu-sitio.com/pago/resultado/flow/{id}',
]);
```

### Transbank/Webpay

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    ConfigKeys::TRANSBANK_API_KEY => 'tu-api-key',
    ConfigKeys::TRANSBANK_COMMERCE_CODE => 'tu-commerce-code',
    ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION', // o 'PRODUCTION'
    ConfigKeys::BASE_URL => 'https://tu-sitio.com',
    
    // URL de callback (opcional)
    ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://tu-sitio.com/webhooks/webpay/confirm/{id}',
]);
```

### MercadoPago

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-tu-access-token',
    ConfigKeys::BASE_URL => 'https://tu-sitio.com',
    
    // URLs de callback (opcional)
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://tu-sitio.com/webhooks/mercadopago/success/{id}',
    ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://tu-sitio.com/webhooks/mercadopago/failure/{id}',
    ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://tu-sitio.com/webhooks/mercadopago/pending/{id}',
]);
```

## URLs de Callback (Webhooks)

### Placeholder {id}

Las URLs de callback soportan el placeholder `{id}` que será reemplazado automáticamente con el ID de la orden. Esto te permite crear URLs dinámicas sin necesidad de concatenación manual.

**Ejemplo:**
```php
// Configuración
'FLOW_CONFIRMATION_URL' => 'https://tu-sitio.com/webhooks/flow/{id}'

// Cuando creas una transacción con id='ORDER-123', la URL se convierte en:
// https://tu-sitio.com/webhooks/flow/ORDER-123
```

### URLs por Defecto

Si no configuras las URLs de callback, la librería usará valores por defecto basados en `BASEURL`:

#### Flow
- **Confirmación**: `{BASEURL}/pago/confirmar/flow/{id}`
- **Retorno**: `{BASEURL}/pago/resultado/flow/{id}`

#### Webpay
- **Confirmación**: `{BASEURL}/pago/confirmar/webpay/{id}`

#### MercadoPago
- **Éxito**: `{BASEURL}/pago/confirmar/mercadopago/{id}`
- **Fallo**: `{BASEURL}/pago/confirmar/mercadopago/{id}`
- **Pendiente**: `{BASEURL}/pago/confirmar/mercadopago/{id}`

### Configuración Personalizada

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    ConfigKeys::BASE_URL => 'https://mi-tienda.com',
    
    // Personalizar solo las URLs que necesites
    ConfigKeys::FLOW_CONFIRMATION_URL => 'https://mi-tienda.com/api/v1/payments/flow/confirm/{id}',
    ConfigKeys::FLOW_RETURN_URL => 'https://mi-tienda.com/gracias',  // Sin {id} si no lo necesitas
    
    // Webpay puede usar un dominio diferente
    ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://webhooks.mi-tienda.com/webpay/{id}',
    
    // MercadoPago con rutas diferenciadas
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://mi-tienda.com/pago/exito/{id}',
    ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://mi-tienda.com/pago/error/{id}',
    ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://mi-tienda.com/pago/pendiente/{id}',
]);
```

## Métodos Útiles

### Verificar si existe una configuración

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

if (GatewayConfig::has(ConfigKeys::FLOW_API_KEY)) {
    // La configuración existe
}
```

### Obtener todas las configuraciones

```php
$allConfig = GatewayConfig::getAll();
print_r($allConfig);
```

### Limpiar configuración

```php
// Útil en tests o cuando necesitas reconfigurar
GatewayConfig::clear();
```

### Valores por defecto

```php
// Si 'CUSTOM_KEY' no existe, usará 'valor-por-defecto'
$value = GatewayConfig::get(ConfigKeys::FLOW_API_URL, 'https://www.flow.cl/api');
```

## Ejemplos de Uso Completo

### Ejemplo 1: Aplicación Web

```php
<?php
// config/payment-gateways.php

use Raion\Gateways\Config\GatewayConfig;

// Cargar configuración desde tu sistema (base de datos, archivo, etc.)
$config = require __DIR__ . '/payment-config.php';

GatewayConfig::setConfig($config);
```

```php
<?php
// payment-config.php
return [
    'APIKEY' => env('FLOW_APIKEY'),
    'SECRETKEY' => env('FLOW_SECRETKEY'),
    'APIURL' => env('FLOW_API_URL', 'https://sandbox.flow.cl/api'),
    'BASEURL' => env('APP_URL'),
    'TRANSBANK_API_KEY' => env('TRANSBANK_API_KEY'),
    'TRANSBANK_COMMERCE_CODE' => env('TRANSBANK_COMMERCE_CODE'),
    'TRANSBANK_ENVIRONMENT' => env('TRANSBANK_ENVIRONMENT', 'INTEGRATION'),
    'MERCADOPAGO_ACCESS_TOKEN' => env('MERCADOPAGO_ACCESS_TOKEN'),
];
```

### Ejemplo 2: Testing

```php
<?php
use PHPUnit\Framework\TestCase;
use Raion\Gateways\Config\GatewayConfig;

class PaymentTest extends TestCase
{
    protected function setUp(): void
    {
        // Configuración de prueba
        GatewayConfig::setConfig([
            'APIKEY' => 'test-api-key',
            'SECRETKEY' => 'test-secret-key',
            'APIURL' => 'https://sandbox.flow.cl/api',
            'BASEURL' => 'http://localhost:8000'
        ]);
    }

    protected function tearDown(): void
    {
        // Limpiar configuración después de cada test
        GatewayConfig::clear();
    }

    public function testCrearTransaccion()
    {
        // Tu test aquí
    }
}
```

### Ejemplo 3: Configuración Dinámica por Tenant (Multi-tenant)

```php
<?php
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

function procesarPago($tenantId, $orderId, $monto)
{
    // Obtener configuración del tenant desde la base de datos
    $tenantConfig = obtenerConfiguracionTenant($tenantId);
    
    // Establecer configuración específica para este tenant
    GatewayConfig::setConfig([
        'APIKEY' => $tenantConfig['flow_api_key'],
        'SECRETKEY' => $tenantConfig['flow_secret_key'],
        'APIURL' => $tenantConfig['flow_api_url'],
        'BASEURL' => $tenantConfig['base_url']
    ]);
    
    // Procesar el pago
    $gateway = Selector::GetGatewayInstance(Gateways::Flow);
    return $gateway->createTransaction($orderId, $monto, 'CLP', 'Compra', 'cliente@email.com');
}
```

## Mapeo de Variables de Entorno

La librería mapea automáticamente las siguientes variables de entorno:

| Nombre de Configuración | Variable de Entorno |
|-------------------------|---------------------|
| APIKEY | FLOW_APIKEY |
| SECRETKEY | FLOW_SECRETKEY |
| APIURL | FLOW_API_URL |
| BASEURL | WEB_BASE_URL |
| TRANSBANK_API_KEY | TRANSBANK_API_KEY |
| TRANSBANK_COMMERCE_CODE | TRANSBANK_COMMERCE_CODE |
| TRANSBANK_ENVIRONMENT | TRANSBANK_ENVIRONMENT |
| MERCADOPAGO_ACCESS_TOKEN | MERCADOPAGO_ACCESS_TOKEN |
| FLOW_CONFIRMATION_URL | FLOW_CONFIRMATION_URL |
| FLOW_RETURN_URL | FLOW_RETURN_URL |
| WEBPAY_CONFIRMATION_URL | WEBPAY_CONFIRMATION_URL |
| MERCADOPAGO_SUCCESS_URL | MERCADOPAGO_SUCCESS_URL |
| MERCADOPAGO_FAILURE_URL | MERCADOPAGO_FAILURE_URL |
| MERCADOPAGO_PENDING_URL | MERCADOPAGO_PENDING_URL |

## Seguridad

⚠️ **Importante**: Nunca incluyas tus claves API directamente en el código fuente. Usa variables de entorno o sistemas de gestión de secretos (como AWS Secrets Manager, HashiCorp Vault, etc.).

```php
// ❌ NO hagas esto
GatewayConfig::setConfig([
    'APIKEY' => 'mi-clave-secreta-literal'
]);

// ✅ Haz esto
GatewayConfig::setConfig([
    'APIKEY' => getenv('FLOW_APIKEY') // o tu sistema de secretos
]);
```
