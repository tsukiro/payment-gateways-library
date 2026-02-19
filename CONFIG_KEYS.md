# ConfigKeys - Guía de Uso

## Introducción

La clase `ConfigKeys` proporciona constantes type-safe para todas las claves de configuración de los gateways de pago. Esta clase mejora la experiencia del desarrollador al proporcionar:

- **Autocompletado en el IDE**: Las constantes son detectadas automáticamente por tu IDE
- **Prevención de errores de tipeo**: Los errores se detectan en tiempo de compilación
- **Identificación clara del gateway**: Cada clave tiene un prefijo que indica a qué gateway pertenece

## Uso Básico

### Antes (usando strings)

```php
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    'APIKEY' => 'tu-api-key',
    'SECRETKEY' => 'tu-secret-key',
    'APIURL' => 'https://sandbox.flow.cl/api',
    'BASEURL' => 'https://tu-sitio.com'
]);
```

### Ahora (usando ConfigKeys)

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    ConfigKeys::FLOW_API_KEY => 'tu-api-key',
    ConfigKeys::FLOW_SECRET_KEY => 'tu-secret-key',
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
    ConfigKeys::BASE_URL => 'https://tu-sitio.com'
]);
```

## Claves Disponibles

### Flow Gateway

- `ConfigKeys::FLOW_API_KEY` - Clave de API de Flow
- `ConfigKeys::FLOW_SECRET_KEY` - Clave secreta de Flow
- `ConfigKeys::FLOW_API_URL` - URL de la API de Flow
- `ConfigKeys::FLOW_CONFIRMATION_URL` - URL de confirmación (webhook) de Flow
- `ConfigKeys::FLOW_RETURN_URL` - URL de retorno para Flow

### Transbank Webpay

- `ConfigKeys::TRANSBANK_API_KEY` - API Key de Transbank
- `ConfigKeys::TRANSBANK_COMMERCE_CODE` - Código de comercio de Transbank
- `ConfigKeys::TRANSBANK_ENVIRONMENT` - Ambiente (PRODUCTION o INTEGRATION)
- `ConfigKeys::WEBPAY_CONFIRMATION_URL` - URL de confirmación (webhook) de Webpay

### MercadoPago

- `ConfigKeys::MERCADOPAGO_ACCESS_TOKEN` - Access Token de MercadoPago
- `ConfigKeys::MERCADOPAGO_SUCCESS_URL` - URL de pago exitoso
- `ConfigKeys::MERCADOPAGO_FAILURE_URL` - URL de pago fallido
- `ConfigKeys::MERCADOPAGO_PENDING_URL` - URL de pago pendiente

### Configuración General

- `ConfigKeys::BASE_URL` - URL base de tu aplicación

## Ejemplo Completo: Configuración de Flow

```php
<?php

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

// Configurar Flow
GatewayConfig::setConfig([
    ConfigKeys::FLOW_API_KEY => 'tu-flow-api-key',
    ConfigKeys::FLOW_SECRET_KEY => 'tu-flow-secret-key',
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
    ConfigKeys::BASE_URL => 'https://mi-tienda.com',
    ConfigKeys::FLOW_CONFIRMATION_URL => 'https://mi-tienda.com/webhooks/flow/{id}',
    ConfigKeys::FLOW_RETURN_URL => 'https://mi-tienda.com/pago/resultado/{id}'
]);

// Usar el gateway
$gateway = Selector::GetGatewayInstance(Gateways::Flow);
```

## Ejemplo Completo: Configuración de Webpay

```php
<?php

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

// Configurar Webpay
GatewayConfig::setConfig([
    ConfigKeys::TRANSBANK_API_KEY => '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C',
    ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
    ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION', // o 'PRODUCTION'
    ConfigKeys::BASE_URL => 'https://mi-tienda.com',
    ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://mi-tienda.com/webhooks/webpay/{id}'
]);

// Usar el gateway
$gateway = Selector::GetGatewayInstance(Gateways::Webpay);
```

## Ejemplo Completo: Configuración de MercadoPago

```php
<?php

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;
use Raion\Gateways\Selector;
use Raion\Gateways\Models\Gateways;

// Configurar MercadoPago
GatewayConfig::setConfig([
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-1234567890abcdef-123456-abc123',
    ConfigKeys::BASE_URL => 'https://mi-tienda.com',
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://mi-tienda.com/pago/exitoso/{id}',
    ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://mi-tienda.com/pago/fallido/{id}',
    ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://mi-tienda.com/pago/pendiente/{id}'
]);

// Usar el gateway
$gateway = Selector::GetGatewayInstance(Gateways::MercadoPago);
```

## Configuración de Múltiples Gateways

Puedes configurar todos los gateways a la vez:

```php
<?php

use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

GatewayConfig::setConfig([
    // Flow
    ConfigKeys::FLOW_API_KEY => 'tu-flow-api-key',
    ConfigKeys::FLOW_SECRET_KEY => 'tu-flow-secret-key',
    ConfigKeys::FLOW_API_URL => 'https://sandbox.flow.cl/api',
    ConfigKeys::FLOW_CONFIRMATION_URL => 'https://mi-tienda.com/webhooks/flow/{id}',
    ConfigKeys::FLOW_RETURN_URL => 'https://mi-tienda.com/pago/resultado/{id}',
    
    // Transbank Webpay
    ConfigKeys::TRANSBANK_API_KEY => 'tu-transbank-api-key',
    ConfigKeys::TRANSBANK_COMMERCE_CODE => '597055555532',
    ConfigKeys::TRANSBANK_ENVIRONMENT => 'INTEGRATION',
    ConfigKeys::WEBPAY_CONFIRMATION_URL => 'https://mi-tienda.com/webhooks/webpay/{id}',
    
    // MercadoPago
    ConfigKeys::MERCADOPAGO_ACCESS_TOKEN => 'APP_USR-1234567890abcdef-123456-abc123',
    ConfigKeys::MERCADOPAGO_SUCCESS_URL => 'https://mi-tienda.com/pago/exitoso/{id}',
    ConfigKeys::MERCADOPAGO_FAILURE_URL => 'https://mi-tienda.com/pago/fallido/{id}',
    ConfigKeys::MERCADOPAGO_PENDING_URL => 'https://mi-tienda.com/pago/pendiente/{id}',
    
    // General
    ConfigKeys::BASE_URL => 'https://mi-tienda.com'
]);
```

## Variables de Entorno

Las claves de configuración también pueden cargarse desde variables de entorno. El mapeo es:

| ConfigKey | Variable de Entorno |
|-----------|-------------------|
| `FLOW_API_KEY` | `FLOW_API_KEY` |
| `FLOW_SECRET_KEY` | `FLOW_SECRET_KEY` |
| `FLOW_API_URL` | `FLOW_API_URL` |
| `FLOW_CONFIRMATION_URL` | `FLOW_CONFIRMATION_URL` |
| `FLOW_RETURN_URL` | `FLOW_RETURN_URL` |
| `TRANSBANK_API_KEY` | `TRANSBANK_API_KEY` |
| `TRANSBANK_COMMERCE_CODE` | `TRANSBANK_COMMERCE_CODE` |
| `TRANSBANK_ENVIRONMENT` | `TRANSBANK_ENVIRONMENT` |
| `WEBPAY_CONFIRMATION_URL` | `WEBPAY_CONFIRMATION_URL` |
| `MERCADOPAGO_ACCESS_TOKEN` | `MERCADOPAGO_ACCESS_TOKEN` |
| `MERCADOPAGO_SUCCESS_URL` | `MERCADOPAGO_SUCCESS_URL` |
| `MERCADOPAGO_FAILURE_URL` | `MERCADOPAGO_FAILURE_URL` |
| `MERCADOPAGO_PENDING_URL` | `MERCADOPAGO_PENDING_URL` |
| `BASE_URL` | `WEB_BASE_URL` (por compatibilidad) |

### Ejemplo con .env

```env
# Flow
FLOW_API_KEY=tu-flow-api-key
FLOW_SECRET_KEY=tu-flow-secret-key
FLOW_API_URL=https://sandbox.flow.cl/api
FLOW_CONFIRMATION_URL=https://mi-tienda.com/webhooks/flow/{id}
FLOW_RETURN_URL=https://mi-tienda.com/pago/resultado/{id}

# Transbank Webpay
TRANSBANK_API_KEY=tu-transbank-api-key
TRANSBANK_COMMERCE_CODE=597055555532
TRANSBANK_ENVIRONMENT=INTEGRATION
WEBPAY_CONFIRMATION_URL=https://mi-tienda.com/webhooks/webpay/{id}

# MercadoPago
MERCADOPAGO_ACCESS_TOKEN=APP_USR-1234567890abcdef-123456-abc123
MERCADOPAGO_SUCCESS_URL=https://mi-tienda.com/pago/exitoso/{id}
MERCADOPAGO_FAILURE_URL=https://mi-tienda.com/pago/fallido/{id}
MERCADOPAGO_PENDING_URL=https://mi-tienda.com/pago/pendiente/{id}

# General
WEB_BASE_URL=https://mi-tienda.com
```

## Obtener Valores de Configuración

Para obtener una clave de configuración:

```php
use Raion\Gateways\Config\ConfigKeys;
use Raion\Gateways\Config\GatewayConfig;

// Obtener un valor
$apiKey = GatewayConfig::get(ConfigKeys::FLOW_API_KEY);

// Obtener con valor por defecto
$apiUrl = GatewayConfig::get(ConfigKeys::FLOW_API_URL, 'https://www.flow.cl/api');

// Verificar si existe una clave
if (GatewayConfig::has(ConfigKeys::FLOW_API_KEY)) {
    // La clave existe
}
```

## Migración desde Claves Antiguas

Si estás actualizando desde una versión anterior, aquí está la tabla de migración:

| Clave Antigua | Nueva Clave (ConfigKeys) |
|---------------|-------------------------|
| `'APIKEY'` | `ConfigKeys::FLOW_API_KEY` |
| `'SECRETKEY'` | `ConfigKeys::FLOW_SECRET_KEY` |
| `'APIURL'` | `ConfigKeys::FLOW_API_URL` |
| `'BASEURL'` | `ConfigKeys::BASE_URL` |
| `'TRANSBANK_API_KEY'` | `ConfigKeys::TRANSBANK_API_KEY` |
| `'TRANSBANK_COMMERCE_CODE'` | `ConfigKeys::TRANSBANK_COMMERCE_CODE` |
| `'TRANSBANK_ENVIRONMENT'` | `ConfigKeys::TRANSBANK_ENVIRONMENT` |
| `'MERCADOPAGO_ACCESS_TOKEN'` | `ConfigKeys::MERCADOPAGO_ACCESS_TOKEN` |

## Beneficios

1. **Type Safety**: Las constantes previenen errores de tipeo
2. **Autocompletado**: Tu IDE sugiere las claves disponibles
3. **Refactoring**: Cambiar el nombre de una clave es más fácil y seguro
4. **Documentación**: Cada constante tiene documentación inline
5. **Identificación clara**: Los prefijos indican claramente a qué gateway pertenece cada clave
