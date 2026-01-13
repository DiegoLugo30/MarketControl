# 🔧 Solución: Error 0 - Petición Bloqueada

## ❌ El Problema

El error que tienes:
```
statusCode: 0
responseText: undefined
```

Significa que **la petición AJAX está siendo bloqueada ANTES de llegar al servidor Laravel**.

Esto pasa por **uno de estos motivos**:
1. ⚠️ **CSRF Token inválido o expirado** (más probable)
2. ⚠️ **Sesiones no configuradas correctamente en Railway**
3. ⚠️ **Middleware bloqueando las peticiones POST**

---

## ✅ SOLUCIÓN 1: Configurar Variables de Entorno en Railway

### Paso 1: Ve a Railway
1. Abre tu proyecto en Railway: https://railway.app
2. Selecciona tu servicio `marketcontrol`
3. Ve a la pestaña **"Variables"**

### Paso 2: Agrega/Modifica Estas Variables

**Copia y pega exactamente estas variables:**

```env
probe
```

**Si ya existen, reemplázalas. Si no existen, agrégalas.**

### Paso 3: Redeploy
Después de agregar las variables, Railway hará redeploy automáticamente (2-3 min).

---

## ✅ SOLUCIÓN 2: Excluir Rutas del CSRF (Alternativa)

Si la Solución 1 no funciona, haz esto:

### Abrir el Middleware CSRF

Edita el archivo: `app/Http/Middleware/VerifyCsrfToken.php`

Agrega estas líneas:

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'barcode/search',  // ← AGREGAR ESTA LÍNEA
        'sales/complete',  // ← AGREGAR ESTA LÍNEA
    ];
}
```

**Guarda, commitea y haz push:**
```bash
git add app/Http/Middleware/VerifyCsrfToken.php
git commit -m "Exclude barcode/search from CSRF verification"
git push
```

---

## ✅ SOLUCIÓN 3: Verificar el CSRF Token (Debugging)

Antes de hacer cambios, verifica si el CSRF token está presente:

### Abre la consola del navegador (F12) y ejecuta:

```javascript
console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]'));
console.log('CSRF Content:', document.querySelector('meta[name="csrf-token"]')?.content);
```

**Resultado esperado:**
```
CSRF Token: <meta name="csrf-token" content="ABC123...">
CSRF Content: "ABC123DEF456..."
```

**Si el resultado es `null` o `undefined`**, entonces el layout no tiene el meta tag.

---

## ✅ SOLUCIÓN 4: Verificar el Layout

Abre: `resources/views/layouts/app.blade.php`

**Asegúrate de que tenga esto en el `<head>`:**

```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">  <!-- ← ESTA LÍNEA -->
    <title>@yield('title', 'POS System')</title>
    ...
</head>
```

Si falta, agrégala.

---

## 🧪 Prueba Rápida

Después de aplicar las soluciones, prueba esto:

### Opción A - Desde la Consola del Navegador

Abre `/barcode/scan`, presiona F12 → Console, y ejecuta:

```javascript
$.ajax({
    url: '/barcode/search',
    type: 'POST',
    data: { barcode: '123456789' },
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(data) {
        console.log('✅ FUNCIONA:', data);
    },
    error: function(xhr) {
        console.error('❌ ERROR:', xhr.status, xhr.statusText);
    }
});
```

**Si funciona:** Verás `✅ FUNCIONA: {...}`
**Si falla:** Verás el código de error real (419, 500, etc.)

---

## 🎯 Diagnóstico por Código de Error

### statusCode: 0
**Causa:** Petición bloqueada por navegador (CORS/CSRF)
**Solución:** Solución 1 (Variables de entorno)

### statusCode: 419
**Causa:** CSRF Token inválido
**Solución:** Solución 2 (Excluir rutas) o verificar que el meta tag existe

### statusCode: 500
**Causa:** Error interno de Laravel
**Solución:** Ver logs de Railway

### statusCode: 404
**Causa:** Ruta no existe
**Solución:** Verificar que la ruta `/barcode/search` existe en `routes/web.php`

---

## 📋 Checklist de Solución

Haz esto en orden:

- [ ] **1. Verificar CSRF token** (consola: `$('meta[name="csrf-token"]').attr('content')`)
- [ ] **2. Agregar variables de entorno** en Railway (SESSION_DRIVER, etc.)
- [ ] **3. Esperar redeploy** (2-3 min)
- [ ] **4. Probar** en `/barcode/scan`
- [ ] **5. Si sigue fallando**: Excluir rutas del CSRF (Solución 2)
- [ ] **6. Si sigue fallando**: Verificar layout (Solución 4)

---

## 🚀 Solución Rápida (Más Probable)

**El problema casi seguro es las variables de entorno de Railway.**

**HAZ ESTO:**

1. Ve a Railway → Variables
2. Agrega:
   ```
   SESSION_DRIVER=cookie
   SESSION_SECURE_COOKIE=true
   SESSION_SAME_SITE=lax
   ```
3. Espera el redeploy
4. Recarga la página (F5)
5. Prueba de nuevo

**Esto debería solucionarlo en el 90% de los casos.**

---

## 📞 Si Nada Funciona

Envíame:
1. Screenshot de las **Variables de Railway**
2. Resultado de ejecutar en la consola:
   ```javascript
   console.log('CSRF:', $('meta[name="csrf-token"]').attr('content'));
   ```
3. Resultado de abrir: `https://tu-app.railway.app/test`

---

## 🎯 TL;DR - Solución en 30 Segundos

1. Railway → Tu proyecto → Variables
2. Agregar: `SESSION_DRIVER=cookie`
3. Agregar: `SESSION_SECURE_COOKIE=true`
4. Esperar redeploy
5. F5 en el navegador
6. Probar

**Debería funcionar.** 🎉
