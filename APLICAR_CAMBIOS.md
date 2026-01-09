# 🚀 Cómo Aplicar los Cambios - Productos Pesables

## ⚡ Guía Rápida (5 minutos)

### Paso 1: Ejecutar Migraciones

```bash
docker-compose exec app php artisan migrate
```

**Salida esperada:**
```
Migrating: 2024_01_01_000004_add_weighted_products_support
Migrated:  2024_01_01_000004_add_weighted_products_support (XX ms)
Migrating: 2024_01_01_000005_add_weight_to_sale_items
Migrated:  2024_01_01_000005_add_weight_to_sale_items (XX ms)
```

### Paso 2: Limpiar Cache

```bash
docker-compose exec app php artisan optimize:clear
```

### Paso 3: Reiniciar Aplicación

```bash
docker-compose restart app
```

### Paso 4: Verificar

Abre: **http://localhost:8000/products/create**

Deberías ver el selector **"Tipo de Producto"** con opciones:
- ☐ Por Unidad
- ☐ Por Peso (kg)

---

## ✅ Verificación Completa

### 1. Verificar Migraciones

```bash
docker-compose exec app php artisan migrate:status
```

Busca estas dos líneas con ✅:
```
✅ 2024_01_01_000004_add_weighted_products_support
✅ 2024_01_01_000005_add_weight_to_sale_items
```

### 2. Verificar Base de Datos

```bash
docker-compose exec app php artisan tinker
```

Dentro de tinker:
```php
// Verificar que la tabla products tiene las nuevas columnas
DB::select("SELECT column_name FROM information_schema.columns WHERE table_name='products'");

// Deberías ver: internal_code, is_weighted, price_per_kg

exit
```

### 3. Crear Producto de Prueba

**Producto Pesable:**
1. Ir a: http://localhost:8000/products/create
2. Seleccionar: **Por Peso (kg)**
3. Llenar:
   - Código Interno: `TEST001`
   - Nombre: `Producto Prueba Pesable`
   - Precio por kg: `100.00`
4. Guardar

**Producto Normal:**
1. Seleccionar: **Por Unidad**
2. Llenar:
   - Código Interno: `UNIT001`
   - Código de Barras: `123456789`
   - Nombre: `Producto Prueba Unitario`
   - Precio: `50.00`
   - Stock: `10`
3. Guardar

---

## 🎯 Pruebas Funcionales

### Prueba 1: Búsqueda por Código Interno

```bash
# Abrir el navegador en modo consola (F12)
# En la consola JavaScript:

$.post('/barcode/search', { barcode: 'TEST001' }, function(data) {
    console.log(data);
});

// Deberías ver: found_locally: true, is_weighted: true
```

### Prueba 2: Crear Venta con Producto Pesable

1. Ir a: http://localhost:8000 (Punto de Venta)
2. Ingresar código: `TEST001`
3. **Importante**: Actualmente falta el modal de peso
   - Por ahora, productos pesables aparecerán pero sin solicitar peso
   - La implementación del modal está pendiente

---

## 📋 Archivos Modificados/Creados

### Migraciones ✅
- `database/migrations/2024_01_01_000004_add_weighted_products_support.php`
- `database/migrations/2024_01_01_000005_add_weight_to_sale_items.php`

### Modelos ✅
- `app/Models/Product.php` - Agregados métodos para productos pesables
- `app/Models/SaleItem.php` - Soporte para peso

### Controladores ✅
- `app/Http/Controllers/ProductController.php` - Validaciones dinámicas
- `app/Http/Controllers/BarcodeController.php` - Búsqueda por internal_code
- `app/Http/Controllers/SaleController.php` - Procesamiento de peso

### Vistas ✅
- `resources/views/products/create.blade.php` - Formulario con selector de tipo

### Documentación ✅
- `PRODUCTOS_PESABLES.md` - Documentación completa
- `APLICAR_CAMBIOS.md` - Esta guía

---

## 🐛 Solución de Problemas

### Error: "SQLSTATE[42S22]: Column not found"

**Causa**: Las migraciones no se ejecutaron

**Solución**:
```bash
docker-compose exec app php artisan migrate
```

### Error: "Class 'App\Http\Controllers\Controller' not found"

**Causa**: Archivos base de Laravel faltantes

**Solución**:
```bash
# Ya deberían estar creados, pero si falta:
docker-compose restart app
```

### La vista no muestra los nuevos campos

**Solución**:
```bash
# Limpiar cache de vistas
docker-compose exec app php artisan view:clear

# Reiniciar
docker-compose restart app
```

### Los cambios no se reflejan

**Solución**:
```bash
# Limpiar todo el cache
docker-compose exec app php artisan optimize:clear

# Reconstruir
docker-compose down
docker-compose up -d --build
```

---

## 🔄 Rollback (Deshacer Cambios)

Si algo sale mal y quieres volver atrás:

### Opción 1: Rollback de Migraciones

```bash
# Deshacer las últimas 2 migraciones
docker-compose exec app php artisan migrate:rollback --step=2
```

### Opción 2: Rollback Completo

```bash
# Advertencia: Esto borra TODA la base de datos
docker-compose exec app php artisan migrate:fresh
```

---

## 📊 Consultas Útiles

### Ver todos los productos

```bash
docker-compose exec app php artisan tinker
```

```php
// Productos pesables
Product::where('is_weighted', true)->get(['internal_code', 'name', 'price_per_kg']);

// Productos por unidad
Product::where('is_weighted', false)->get(['internal_code', 'name', 'price', 'stock']);

exit
```

### Consulta SQL directa

```bash
docker-compose exec db psql -U root -d barcode
```

```sql
-- Ver estructura de products
\d products;

-- Ver productos pesables
SELECT internal_code, name, price_per_kg FROM products WHERE is_weighted = true;

-- Salir
\q
```

---

## 🚧 Implementaciones Pendientes

### Alta Prioridad
1. **Modal de peso en POS** - Necesario para vender productos pesables
2. **Vista edit.blade.php** - Actualizada con selector de tipo
3. **Vista index.blade.php** - Mostrar tipo y precio correcto

### Media Prioridad
4. **Vista receipt.blade.php** - Mostrar peso en recibos
5. **Validación** - Evitar cambiar tipo con ventas existentes

### Baja Prioridad
6. Teclado numérico virtual
7. Integración con balanza
8. Reportes específicos

---

## 📞 Checklist Final

Marca cada item cuando lo completes:

- [ ] Migraciones ejecutadas exitosamente
- [ ] Cache limpiado
- [ ] Aplicación reiniciada
- [ ] Puedo ver el selector de tipo en create
- [ ] Puedo crear un producto pesable
- [ ] Puedo crear un producto por unidad
- [ ] Búsqueda por internal_code funciona
- [ ] Documentación leída

---

## ✨ ¡Listo!

Una vez completados todos los pasos, tu sistema estará listo para:

✅ Crear productos pesables
✅ Crear productos por unidad
✅ Buscar por código interno
✅ Gestionar precios por kg
✅ Mantener compatibilidad con sistema anterior

**Próximo paso**: Implementar el modal de peso en el POS para poder vender productos pesables.

---

**¿Necesitas ayuda?**

Revisa el archivo `PRODUCTOS_PESABLES.md` para documentación completa.
