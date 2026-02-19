# ⚠️ IMPORTANTE: Configuración de Credenciales

## 🔒 Seguridad

**NUNCA** subas al repositorio:
- Tu archivo `.env` con credenciales reales
- API Keys, Secret Keys o Tokens de producción
- Códigos de comercio reales (excepto los de integración pública)

El archivo `.env` está en `.gitignore` y no se subirá al repositorio.

## 📝 Cómo Configurar

### 1. Copia el archivo de ejemplo

```bash
cp .env.example .env
```

### 2. Edita `.env` con tus credenciales reales

```bash
nano .env  # o usa tu editor favorito
```

### 3. Completa los valores

```env
# Flow Gateway Configuration - REEMPLAZA CON TUS CREDENCIALES REALES
FLOW_API_KEY=tu-flow-api-key-real
FLOW_SECRET_KEY=tu-flow-secret-key-real
FLOW_API_URL=https://sandbox.flow.cl/api  # o https://www.flow.cl/api para producción

# Transbank Webpay Configuration - REEMPLAZA CON TUS CREDENCIALES REALES
TRANSBANK_API_KEY=tu-transbank-api-key-real
TRANSBANK_COMMERCE_CODE=tu-commerce-code-real
TRANSBANK_ENVIRONMENT=INTEGRATION  # o PRODUCTION

# MercadoPago Configuration - REEMPLAZA CON TUS CREDENCIALES REALES
MERCADOPAGO_ACCESS_TOKEN=APP_USR-tu-access-token-real
MERCADOPAGO_PUBLIC_KEY=APP_USR-tu-public-key-real

# URLs de tu aplicación
WEB_BASE_URL=https://tu-sitio-real.com
```

## 🔑 Dónde Obtener las Credenciales

### Flow
1. Regístrate en [Flow](https://www.flow.cl/)
2. Ve a "Configuración" → "API Keys"
3. Copia tu API Key y Secret Key
4. Para sandbox: `https://sandbox.flow.cl/api`
5. Para producción: `https://www.flow.cl/api`

### Transbank Webpay
1. Regístrate en [Transbank Developers](https://www.transbankdevelopers.cl/)
2. Solicita tus credenciales de integración/producción
3. **Credenciales de Integración (públicas):**
   - API Key: `579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C`
   - Commerce Code: `597055555532`
   - Environment: `INTEGRATION`
4. Para producción necesitas solicitarlas directamente

### MercadoPago
1. Regístrate en [MercadoPago Developers](https://www.mercadopago.cl/developers)
2. Ve a "Tus integraciones" → "Credenciales"
3. Copia tu Access Token y Public Key
4. Puedes usar credenciales de Test o Producción

## ⚙️ Ambientes

### Desarrollo Local
- Usa `.env` con credenciales de sandbox/integración
- Nunca uses credenciales de producción en desarrollo

### Staging/Testing
- Configura variables de entorno en tu servidor
- Usa credenciales de sandbox/integración

### Producción
- **SIEMPRE** usa variables de entorno del servidor
- **NUNCA** hardcodees credenciales en el código
- Usa credenciales de producción solo en producción
- Configura monitoreo y logs

## 🛡️ Mejores Prácticas

1. **Rotación de Credenciales**: Cambia tus API Keys periódicamente
2. **Separa Ambientes**: Usa credenciales diferentes para dev, staging y producción
3. **Revoca Credenciales Comprometidas**: Si crees que una credencial fue expuesta, revócala inmediatamente
4. **No Compartas**: Nunca compartas tus credenciales por email, Slack, etc.
5. **Usa .env**: Siempre carga credenciales desde variables de entorno

## 📚 Más Información

- [Configuración General](CONFIG_EXAMPLE.md)
- [Constantes ConfigKeys](CONFIG_KEYS.md)
- [Documentación README](README.md)
