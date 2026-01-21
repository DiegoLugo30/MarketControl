# 🔍 Debugging en Railway - Logs Mejorados

## ✅ Cambios Implementados

He agregado **logging detallado** en toda la aplicación para que puedas diagnosticar el problema con la API de OpenFoodFacts. Ahora verás logs con emojis para identificar rápidamente:

### Logs del Backend (Laravel)

**En ProductApiService.php:**
- 🔍 `Consultando OpenFoodFacts API` - Cuando inicia la consulta
- 📡 `Respuesta de OpenFoodFacts API` - Status code y detalles de respuesta
- ✅ `Producto encontrado en OpenFoodFacts` - Cuando encuentra el producto
- ℹ️ `Producto no encontrado en OpenFoodFacts` - Cuando no existe
- ⚠️ `API retornó código no exitoso` - Errores HTTP (500, 403, etc.)
- ❌ `Error de conexión a OpenFoodFacts API` - Problemas de red/timeout
- ❌ `Error inesperado consultando OpenFoodFacts API` - Otros errores

**En BarcodeController.php:**
- 🔎 `Búsqueda de producto iniciada` - Incluye código, IP y user agent
- ✅ `Producto encontrado localmente` - Encontrado en tu BD
- 🌐 `Consultando API externa` - Va a consultar OpenFoodFacts
- ⏭️ `Código no válido para API externa` - Código muy corto o no numérico
- ❌ `Producto no encontrado` - No existe en ningún lado
- ❌ `Error inesperado en búsqueda` - Error con stacktrace completo

### Logs del Frontend (JavaScript)

**En consola del navegador (F12):**
- 🔍 `Iniciando búsqueda de código:` - Cuando envías la búsqueda
- ✅ `Respuesta recibida:` - Respuesta del servidor
- ❌ `Error en búsqueda:` - Con detalles completos del error

---

## 📋 Cómo Ver los Logs en Railway

### Paso 1: Acceder a los Logs

1. Ve a tu proyecto en Railway: https://railway.app
2. Selecciona tu servicio de aplicación (marketcontrol)
3. Haz clic en la pestaña **"Deployments"**
4. Selecciona el deployment activo (el que tiene el ✅ verde)
5. Haz clic en **"View Logs"** o la pestaña **"Logs"**

### Paso 2: Filtrar los Logs

Railway muestra logs en tiempo real. Para encontrar los errores:

**Busca por estos términos:**
- `❌` - Errores críticos
- `Error consultando` - Errores de API
- `Error en búsqueda` - Errores generales
- `ConnectionException` - Problemas de conexión
- El código de barras específico que probaste

### Paso 3: Reproducir el Error

1. En otra pestaña, abre tu aplicación en Railway
2. Ve a `/barcode/scan` o al POS
3. Escanea/ingresa un código de barras (ej: `7790001001689`)
4. Regresa a la pestaña de Logs de Railway
5. **Los logs aparecerán INMEDIATAMENTE**

---

## 🧪 Prueba Paso a Paso

### Opción A: Desde la Interfaz Web

1. Abre Railway Logs en una pestaña
2. Abre tu app: `https://marketcontrol-production-3c1f.up.railway.app/barcode/scan`
3. Escanea código: `7790001001689`
4. Observa los logs en Railway

**Deberías ver algo como:**
```
🔎 Búsqueda de producto iniciada {"code":"7790001001689","ip":"..."}
🌐 Consultando API externa {"code":"7790001001689","code_length":13}
🔍 Consultando OpenFoodFacts API {"barcode":"7790001001689","url":"https://world.openfoodfacts.org/api/v0/product/7790001001689.json"}
📡 Respuesta de OpenFoodFacts API {"barcode":"7790001001689","status_code":200,"successful":true}
```

### Opción B: Desde Consola del Navegador

1. Abre tu app en Railway
2. Presiona **F12** para abrir DevTools
3. Ve a la pestaña **Console**
4. Escanea un código de barras
5. Verás los logs del frontend:

```javascript
🔍 Iniciando búsqueda de código: 7790001001689
✅ Respuesta recibida: {success: true, found_locally: false, ...}
```

**Si hay error:**
```javascript
❌ Error en búsqueda: {status: "error", error: "...", responseText: "..."}
```

---

## 🐛 Posibles Causas del Error

Basándome en que dice "Error instantáneo", probablemente es uno de estos:

### 1. **Error de CSRF Token** (Más probable)
**Síntoma**: Falla instantáneamente sin importar el código
**Log esperado**: `419 Page Expired` o `CSRF token mismatch`
**Solución**:
```bash
# En Railway, configura en Variables:
SESSION_DRIVER=cookie
SESSION_DOMAIN=.railway.app
```

### 2. **Error de extensión cURL/HTTP Client**
**Síntoma**: Error "cURL error 6: Could not resolve host"
**Log esperado**: `ConnectionException` en logs
**Solución**: Verificar que Railway pueda hacer requests externos

### 3. **Timeout muy corto**
**Síntoma**: Falla después de 5-10 segundos
**Log esperado**: `cURL error 28: Operation timed out`
**Solución**: Ya lo aumenté a 10 segundos en el código

### 4. **Firewall/IP bloqueado**
**Síntoma**: Código 403 o 429 de OpenFoodFacts
**Log esperado**: `API retornó código no exitoso {status: 403}`
**Solución**: Agregué User-Agent personalizado

---

## 📊 Qué Buscar en los Logs

Copia y pégame **TODA** la salida de logs cuando escanees un código. Específicamente busca:

1. **Línea de inicio**: `🔎 Búsqueda de producto iniciada`
2. **Si consulta API**: `🌐 Consultando API externa`
3. **Request a OpenFoodFacts**: `🔍 Consultando OpenFoodFacts API`
4. **Respuesta HTTP**: `📡 Respuesta de OpenFoodFacts API` (status_code)
5. **Errores**: Cualquier línea con `❌` o `Error`

**Ejemplo de salida completa:**
```
[2026-01-13 18:30:15] production.INFO: 🔎 Búsqueda de producto iniciada {"code":"7790001001689","ip":"192.168.1.1"}
[2026-01-13 18:30:15] production.INFO: 🌐 Consultando API externa {"code":"7790001001689","code_length":13}
[2026-01-13 18:30:15] production.INFO: 🔍 Consultando OpenFoodFacts API {"barcode":"7790001001689"}
[2026-01-13 18:30:16] production.ERROR: ❌ Error de conexión {"error":"cURL error 6: Could not resolve host"}
```

---

## 🚀 Próximos Pasos

1. **Despliega estos cambios** en Railway:
   ```bash
   git add .
   git commit -m "Add detailed logging for API debugging"
   git push
   ```

2. **Espera a que se complete el deploy** (~2-3 minutos)

3. **Reproduce el error** y **captura los logs**

4. **Envíame los logs** completos y te diré exactamente qué está pasando

---

## 📞 Información Adicional para Debugging

También puedes compartir:

- **Variables de entorno** en Railway (oculta los valores sensibles)
- **Versión de PHP**: Debe ser 8.2+
- **Región de Railway**: US/EU
- **Captura de pantalla** del error en el navegador (con DevTools abierto)

---

## ✨ Mejoras Adicionales Implementadas

Además del logging, hice estos cambios:

1. ✅ **Timeout aumentado**: De 5s a 10s para evitar timeouts prematuros
2. ✅ **User-Agent personalizado**: `MarketControl/1.0` para evitar bloqueos
3. ✅ **Frontend mejorado**: Muestra errores detallados al usuario
4. ✅ **Try-catch completo**: Captura todos los tipos de excepciones
5. ✅ **Logging estructurado**: JSON con contexto completo

---

**Con estos logs, podré ver exactamente qué está fallando.** 🎯
