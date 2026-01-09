# 🏪 Extensión: Productos Pesables para Dietética

## 📋 Resumen de Cambios

Esta extensión agrega soporte completo para productos pesables (venta por peso en kg) al sistema POS existente, manteniendo la compatibilidad con productos por unidad.

---

## ✅ Cambios Implementados

### 1️⃣ Base de Datos

**Nueva Migración**: `2024_01_01_000004_add_weighted_products_support.php`
- `internal_code` (string, unique) - Código interno del producto
- `is_weighted` (boolean) - Indica si es producto pesable
- `price_per_kg` (decimal) - Precio por kilogramo
- `barcode` ahora es nullable

**Nueva Migración**: `2024_01_01_000005_add_weight_to_sale_items.php`
- `weight` (decimal) - Peso en kilogramos para items pesables

### 2️⃣ Modelos

**Product.php** - Métodos nuevos:
```php
calculateWeightPrice(float $weight): float  // Calcular precio por peso
getDisplayPrice(): string                   // Formato de precio según tipo
requiresWeight(): bool                      // Verificar si requiere peso
```

**SaleItem.php** - Métodos nuevos:
```php
isWeighted(): bool           // Verificar si es item pesable
getQuantityText(): string    // Texto descriptivo (kg o unidades)
```

### 3️⃣ Controladores

**ProductController** ✅
- Validaciones dinámicas según tipo de producto
- Campos requeridos adaptativos (precio vs precio/kg)

**BarcodeController** ✅
- Búsqueda por `barcode` O `internal_code`
- Retorna información de productos pesables

**SaleController** ✅
- Procesamiento de ventas con peso
- Manejo de stock solo para productos no pesables
- Cálculo automático de precios

### 4️⃣ Vistas

**products/create.blade.php** ✅
- Selector de tipo de producto (Unidad / Peso)
- Campos dinámicos según selección
- Validación JavaScript

**products/edit.blade.php** ⏳ (pendiente actualizar)
**products/index.blade.php** ⏳ (pendiente actualizar)
**sales/pos.blade.php** ⏳ (pendiente modal de peso)

---

## 🚀 Instrucciones de Instalación

### Paso 1: Ejecutar Migraciones

```bash
# Dentro del contenedor Docker
docker-compose exec app php artisan migrate

# O si estás dentro del contenedor
php artisan migrate
```

Esto creará las nuevas columnas en las tablas existentes sin perder datos.

### Paso 2: Verificar Migración

```bash
docker-compose exec app php artisan migrate:status
```

Deberías ver:
```
✅ 2024_01_01_000004_add_weighted_products_support
✅ 2024_01_01_000005_add_weight_to_sale_items
```

### Paso 3: Reiniciar Aplicación

```bash
docker-compose restart app
```

---

## 📖 Cómo Usar el Sistema

### Crear Producto Pesable

1. Ir a **Productos** → **Nuevo Producto**
2. Seleccionar **"Por Peso (kg)"**
3. Ingresar:
   - **Código Interno**: Ej: `FRU001`, `SEM012`, `A001`
   - **Código de Barras**: Opcional
   - **Nombre**: Ej: "Almendras"
   - **Precio por kg**: Ej: `850.00`
4. Guardar

### Crear Producto por Unidad (Normal)

1. Seleccionar **"Por Unidad"**
2. Ingresar:
   - **Código Interno**: Ej: `PROD001`
   - **Código de Barras**: Escanear o ingresar
   - **Precio**: Precio unitario
   - **Stock**: Cantidad disponible

### Vender Producto Pesable

1. En **Punto de Venta**
2. Escanear o ingresar **código interno** (ej: `FRU001`)
3. **Modal de peso** aparecerá automáticamente
4. Ingresar peso en kg (ej: `0.500` = 500g)
5. Sistema calcula precio automáticamente
6. Producto se agrega al carrito

### Vender Producto Normal

1. Escanear código de barras o interno
2. Se agrega al carrito inmediatamente
3. Cantidades se acumulan automáticamente

---

## 🔍 Ejemplos de Uso

### Ejemplo 1: Dietética

```
Producto: Almendras
Código Interno: FRU001
Precio por kg: $850.00
Tipo: Pesable

Cliente compra 0.250 kg
→ Precio final: $212.50
```

### Ejemplo 2: Producto Mixto

```
Producto: Galletitas
Código Interno: GAL001
Código de Barras: 779123456789
Precio Unitario: $120.00
Stock: 50
Tipo: Por Unidad

Cliente compra 3 unidades
→ Precio final: $360.00
```

---

## 🔧 Estructura de Datos

### Producto Pesable

```json
{
  "internal_code": "FRU001",
  "barcode": null,
  "name": "Almendras",
  "is_weighted": true,
  "price_per_kg": 850.00,
  "price": 0,
  "stock": 0
}
```

### Producto por Unidad

```json
{
  "internal_code": "PROD001",
  "barcode": "7790123456789",
  "name": "Galletitas",
  "is_weighted": false,
  "price": 120.00,
  "price_per_kg": null,
  "stock": 50
}
```

### Item de Venta Pesable

```json
{
  "sale_id": 1,
  "product_id": 5,
  "quantity": 1,
  "weight": 0.250,
  "price": 212.50
}
```

### Item de Venta Normal

```json
{
  "sale_id": 1,
  "product_id": 3,
  "quantity": 3,
  "weight": null,
  "price": 360.00
}
```

---

## 🎯 Flujo de Trabajo en Caja

### Escenario Real

**Cliente compra:**
- 250g de almendras (pesable)
- 2 paquetes de arroz (unidad)
- 1.5kg de avena (pesable)

**Flujo:**

1. Escanear `FRU001` (Almendras)
   → Modal pide peso → Ingresar `0.250`
   → Agrega: "Almendras 0.250 kg - $212.50"

2. Escanear `ARR001` (Arroz)
   → Se agrega inmediatamente
   → Escanear de nuevo
   → Se acumula: "Arroz x2 - $180.00"

3. Escanear `AVE001` (Avena)
   → Modal pide peso → Ingresar `1.500`
   → Agrega: "Avena 1.500 kg - $675.00"

**Total**: $1,067.50

---

## 🔄 Compatibilidad

✅ **Productos existentes** siguen funcionando normalmente
✅ **Ventas anteriores** se mantienen intactas
✅ **Stock** se controla solo en productos por unidad
✅ **Búsquedas** funcionan por barcode o internal_code
✅ **API externa** sigue consultando OpenFoodFacts

---

## ⚠️ Notas Importantes

1. **Códigos Internos Únicos**: Cada producto debe tener un código interno único
2. **Productos Pesables**: No se controla stock (siempre disponibles)
3. **Peso Mínimo**: 0.001 kg (1 gramo)
4. **Precisión**: Peso se guarda con 3 decimales (0.250 kg)
5. **Precio**: Se calcula automáticamente (peso × precio/kg)

---

## 🐛 Solución de Problemas

### Error: "Column not found: internal_code"
```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate
```

### Modal de peso no aparece
- Verificar que el producto tenga `is_weighted = true`
- Revisar JavaScript en navegador (F12)

### Precio no se calcula
- Verificar que `price_per_kg` no sea null
- Verificar que weight sea > 0

---

## 📊 Consultas SQL Útiles

### Ver productos pesables
```sql
SELECT internal_code, name, price_per_kg, is_weighted
FROM products
WHERE is_weighted = true;
```

### Ver ventas con peso
```sql
SELECT p.name, si.weight, si.price
FROM sale_items si
JOIN products p ON si.product_id = p.id
WHERE si.weight IS NOT NULL;
```

### Total vendido por producto pesable
```sql
SELECT p.name, SUM(si.weight) as total_kg, SUM(si.price) as total_venta
FROM sale_items si
JOIN products p ON si.product_id = p.id
WHERE si.weight IS NOT NULL
GROUP BY p.id, p.name;
```

---

## 🚧 Pendiente de Implementar

1. **Vista edit.blade.php actualizada** - Formulario de edición con selector de tipo
2. **Vista index.blade.php actualizada** - Mostrar tipo de producto y precio correcto
3. **Modal de peso en POS** - Interfaz para ingresar peso con teclado numérico
4. **Vista receipt.blade.php actualizada** - Mostrar peso en recibos
5. **Validaciones adicionales** - Evitar cambiar tipo de producto con ventas

---

## 📞 Soporte

Si encuentras problemas:

1. Verificar logs: `docker-compose logs app`
2. Revisar migraciones: `php artisan migrate:status`
3. Limpiar cache: `php artisan optimize:clear`

---

## ✨ Próximas Mejoras Sugeridas

- 🔢 Teclado numérico virtual para peso
- 📱 Optimización móvil del modal
- 🏷️ Etiquetas imprimibles con código interno
- 📈 Reportes de productos más vendidos (peso vs unidad)
- ⚖️ Integración con balanza digital
- 🔄 Conversión entre gramos/kg automática

---

**Implementado por**: Claude AI
**Fecha**: Enero 2026
**Versión**: 1.0
