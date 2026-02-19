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
