# Exception Handling Guide

## Jerarquía de Excepciones

Esta librería utiliza un sistema de excepciones personalizadas para proporcionar mensajes de error claros y específicos. Todas las excepciones heredan de la clase base `GatewayException`.

```
Exception (PHP)
└── GatewayException
    ├── ConfigurationException
    ├── TransactionException
    ├── CommunicationException
    └── InvalidResponseException
```

## Tipos de Excepciones

### 1. GatewayException

**Namespace:** `Raion\Gateways\Exceptions\GatewayException`

Excepción base de la cual heredan todas las demás excepciones de la librería. Puede usarse para capturar cualquier error relacionado con los gateways de pago.

**Ejemplo de uso:**
```php
try {
    $gateway = Selector::GetGatewayInstance(Gateways::Flow);
    $response = $gateway->createTransaction(...);
} catch (GatewayException $e) {
    // Captura cualquier error de gateway
    log_error($e->getMessage());
}
```

---

### 2. ConfigurationException

**Namespace:** `Raion\Gateways\Exceptions\ConfigurationException`

**Código de error:** 1001-1999

Se lanza cuando hay problemas con la configuración de los gateways.

**Métodos estáticos:**

- `missingKey(string $key)` - Se lanza cuando falta una clave de configuración requerida
- `invalidValue(string $key, string $reason)` - Se lanza cuando un valor de configuración es inválido

**Ejemplos:**
```php
try {
    $apiKey = GatewayConfig::get('APIKEY'); // Sin valor por defecto
} catch (ConfigurationException $e) {
    // Error: The configuration element 'APIKEY' does not exist and no default value was provided
    echo $e->getMessage();
    echo $e->getCode(); // 1001
}
```

---

### 3. TransactionException

**Namespace:** `Raion\Gateways\Exceptions\TransactionException`

**Código de error:** 2001-2999

Se lanza cuando hay errores al crear, procesar o consultar transacciones.

**Métodos estáticos:**

- `creationFailed(string $gateway, string $message, ?Exception $previous)` - Error al crear una transacción
- `processingFailed(string $gateway, string $message, ?Exception $previous)` - Error al procesar una transacción
- `statusRetrievalFailed(string $gateway, string $token, ?Exception $previous)` - Error al obtener el estado de una transacción

**Ejemplos:**
```php
try {
    $flow = new FlowGateway();
    $response = $flow->createTransaction('ORDER123', 10000, 'CLP', 'Test', 'user@example.com');
} catch (TransactionException $e) {
    // Error creating transaction in Flow: API key invalid
    echo $e->getMessage();
    echo $e->getCode(); // 2001
    
    // Obtener la excepción original si existe
    if ($e->getPrevious()) {
        echo $e->getPrevious()->getMessage();
    }
}
```

---

### 4. CommunicationException

**Namespace:** `Raion\Gateways\Exceptions\CommunicationException`

**Código de error:** 3001-3999

Se lanza cuando hay problemas de comunicación con las APIs de los gateways (errores de red, HTTP, timeouts).

**Métodos estáticos:**

- `httpError(int $httpCode, string $message, ?Exception $previous)` - Error HTTP (400, 500, etc.)
- `networkError(string $message, ?Exception $previous)` - Error de red general
- `timeout(string $gateway, ?Exception $previous)` - Timeout de comunicación

**Ejemplos:**
```php
try {
    $flow = new FlowGateway();
    $transaction = $flow->getTransactionInProcess($token);
} catch (CommunicationException $e) {
    if ($e->getCode() >= 3400 && $e->getCode() < 3500) {
        // Error 4xx (cliente)
        echo "Error en la petición: " . $e->getMessage();
    } elseif ($e->getCode() >= 3500 && $e->getCode() < 3600) {
        // Error 5xx (servidor)
        echo "Error del servidor: " . $e->getMessage();
    } else {
        // Otros errores de comunicación
        echo "Error de red: " . $e->getMessage();
    }
}
```

---

### 5. InvalidResponseException

**Namespace:** `Raion\Gateways\Exceptions\InvalidResponseException`

**Código de error:** 4001-4999

Se lanza cuando la API del gateway devuelve una respuesta inválida o incompleta.

**Métodos estáticos:**

- `incompleteResponse(string $gateway, string $missingFields, string $responseData)` - Respuesta incompleta
- `invalidJson(string $gateway, string $jsonError)` - Error al parsear JSON
- `unexpectedFormat(string $gateway, string $expected, string $actual)` - Formato inesperado

**Ejemplos:**
```php
try {
    $flow = new FlowGateway();
    $response = $flow->createTransaction(...);
} catch (InvalidResponseException $e) {
    // Incomplete response from Flow. Missing fields: token, url
    echo $e->getMessage();
    echo $e->getCode(); // 4001, 4002, 4003
}
```

---

## Buenas Prácticas

### 1. Captura Específica antes que General

```php
try {
    $gateway = Selector::GetGatewayInstance(Gateways::Flow);
    $response = $gateway->createTransaction(...);
} catch (ConfigurationException $e) {
    // Manejar error de configuración
    return "Por favor configure las credenciales";
} catch (TransactionException $e) {
    // Manejar error de transacción
    return "No se pudo crear la transacción";
} catch (CommunicationException $e) {
    // Manejar error de comunicación
    return "Servicio temporalmente no disponible";
} catch (GatewayException $e) {
    // Capturar cualquier otro error de gateway
    return "Error inesperado";
}
```

### 2. Logging de Errores

```php
try {
    $response = $gateway->createTransaction(...);
} catch (GatewayException $e) {
    // Log completo para debugging
    error_log(sprintf(
        "[%s] %s (Code: %d)",
        get_class($e),
        $e->getMessage(),
        $e->getCode()
    ));
    
    // Log del stack trace si es necesario
    if ($e->getPrevious()) {
        error_log("Previous: " . $e->getPrevious()->getMessage());
    }
    
    throw $e; // Re-lanzar si es necesario
}
```

### 3. Manejo por Tipo de Error

```php
try {
    $response = $gateway->createTransaction(...);
} catch (ConfigurationException $e) {
    // Errores de configuración son fatales - notificar al admin
    notifyAdmin($e);
    throw $e;
} catch (CommunicationException $e) {
    // Errores de comunicación pueden ser temporales - reintentar
    sleep(2);
    return retryTransaction();
} catch (InvalidResponseException $e) {
    // Respuestas inválidas pueden indicar cambios en la API
    logApiChange($e);
    throw $e;
}
```

## Códigos de Error

| Rango | Tipo | Descripción |
|-------|------|-------------|
| 1001-1999 | Configuration | Errores de configuración |
| 2001-2999 | Transaction | Errores en transacciones |
| 3001-3999 | Communication | Errores de comunicación |
| 4001-4999 | Invalid Response | Respuestas inválidas |

### Códigos Específicos

- `1001` - Clave de configuración faltante
- `1002` - Valor de configuración inválido
- `2001` - Error al crear transacción
- `2002` - Error al procesar transacción
- `2003` - Error al obtener estado de transacción
- `3001` - Error de red
- `3002` - Timeout
- `3xxx` - Error HTTP (3000 + código HTTP)
- `4001` - Respuesta incompleta
- `4002` - JSON inválido
- `4003` - Formato inesperado
