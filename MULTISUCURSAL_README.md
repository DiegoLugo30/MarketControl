# 🏢 Sistema Multi-Sucursal - MarketControl

## 📋 Descripción

Sistema de gestión de inventario multi-sucursal que permite mantener **stock independiente por sucursal** mientras los productos se definen una sola vez de manera centralizada.

---

## 🎯 Características

- ✅ **Stock independiente por sucursal**
- ✅ **Productos únicos** (sin duplicación)
- ✅ **Migración automática** de datos existentes
- ✅ **Reversible** (rollback seguro)
- ✅ **Compatible** con código legacy
- ✅ **Escalable** para múltiples sucursales

---

## 📊 Nuevo Modelo de Datos

```
┌─────────────────────────┐
│       PRODUCTS          │
│─────────────────────────│
│ id (PK)                 │
│ internal_code (UNIQUE)  │
│ barcode (UNIQUE)        │
│ name                    │
│ price / price_per_kg    │
│ is_weighted             │
└───────────┬─────────────┘
            │
            │ 1:N
            │
┌───────────▼─────────────┐         ┌─────────────────────────┐
│   PRODUCT_STOCKS        │    N:1  │       BRANCHES          │
│─────────────────────────│◄────────│─────────────────────────│
│ id (PK)                 │         │ id (PK)                 │
│ product_id (FK)         │         │ code (UNIQUE)           │
│ branch_id (FK)          │         │ name                    │
│ stock                   │         │ address                 │
│ UNIQUE(product_id,      │         │ is_main                 │
│        branch_id)       │         │ is_active               │
└─────────────────────────┘         └─────────────────────────┘
```

---

## 📂 Archivos Creados

### Migraciones
- `database/migrations/2024_01_01_000009_create_branches_table.php`
- `database/migrations/2024_01_01_000010_create_product_stocks_table.php`
- `database/migrations/2024_01_01_000011_migrate_existing_stock_to_branches.php`
- `database/migrations/2024_01_01_000012_remove_stock_from_products_table.php` (opcional)

### Modelos
- `app/Models/Branch.php`
- `app/Models/ProductStock.php`
- `app/Models/Product.php` (actualizado)

---

## 🚀 Instalación y Migración

### Paso 1: Backup (OBLIGATORIO)

```bash
# Backup de base de datos
mysqldump -u usuario -p database_name > backup_antes_migracion_$(date +%Y%m%d).sql
```

### Paso 2: Ejecutar Migraciones

```bash
# Ver estado actual
php artisan migrate:status

# Ejecutar migraciones
php artisan migrate

# Verificar ejecución
php artisan migrate:status
```

### Paso 3: Verificar Migración

```bash
# Opción 1: Script PHP
php verificar_migracion.php

# Opción 2: Artisan Tinker
php artisan tinker
>>> include 'verificar_migracion.php';

# Opción 3: SQL Manual
mysql -u usuario -p database_name < CONSULTAS_SQL_UTILES.sql
```

---

## 💻 Uso del Sistema

### Consultas Básicas

```php
use App\Models\Product;
use App\Models\Branch;
use App\Models\ProductStock;

// Obtener stock en una sucursal
$product = Product::find(1);
$branchId = 1;
$stock = $product->getStockInBranch($branchId);

// Verificar disponibilidad
$hasStock = $product->hasStockInBranch($branchId, quantity: 10);

// Obtener stock total
$totalStock = $product->total_stock;

// Stock por sucursal (array)
$stocksBySucursal = $product->getStockBySucursal();
// Resultado: [1 => 50, 2 => 30, 3 => 20]

// Sucursal principal
$mainBranch = Branch::main();

// Decrementar stock
$product->decrementStockInBranch($branchId, quantity: 5);

// Incrementar stock
$product->incrementStockInBranch($branchId, quantity: 10);
```

### Transferencias Entre Sucursales

```php
$productStock = ProductStock::where('product_id', 1)
    ->where('branch_id', 1)
    ->first();

$success = $productStock->transferTo(
    destinationBranchId: 2,
    quantity: 10
);
```

---

## 🔧 Adaptación del Código

### SaleController - Completar Venta

```php
public function complete(Request $request)
{
    // Obtener sucursal actual (desde sesión o configuración)
    $branchId = session('current_branch_id') ?? Branch::main()->id;

    foreach ($request->items as $item) {
        $product = Product::find($item['product_id']);

        // Solo validar stock para productos no pesables
        if (!$product->is_weighted) {
            // Verificar stock en la sucursal
            if (!$product->hasStockInBranch($branchId, $item['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para {$product->name}"
                ], 400);
            }

            // Decrementar stock en la sucursal
            $product->decrementStockInBranch($branchId, $item['quantity']);
        }

        // ... resto del código
    }
}
```

### ProductController - Crear Producto

```php
public function store(Request $request)
{
    // Crear producto
    $product = Product::create($request->except('stock'));

    // Crear stock inicial en sucursal principal (si aplica)
    if ($request->filled('stock') && !$product->is_weighted) {
        $mainBranch = Branch::main();

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $mainBranch->id,
            'stock' => $request->stock,
        ]);
    }

    return redirect()->route('products.index');
}
```

---

## 📚 Documentación Completa

Consulta estos archivos para información detallada:

| Archivo | Descripción |
|---------|-------------|
| `RESUMEN_EJECUTIVO_MULTISUCURSAL.md` | Resumen de 1 página |
| `SISTEMA_MULTISUCURSAL_IMPLEMENTACION.md` | Documentación completa (50+ páginas) |
| `CONSULTAS_SQL_UTILES.sql` | 50+ consultas SQL útiles |
| `verificar_migracion.php` | Script de verificación post-migración |

Ubicación: `[carpeta_temporal]/scratchpad/`

---

## 🧪 Testing

### Verificación Post-Migración

```bash
# Ejecutar script de verificación
php verificar_migracion.php
```

### Tests Manuales

1. **Crear producto con stock inicial**
   - Verificar que se crea registro en `product_stocks`
   - Confirmar stock en sucursal principal

2. **Realizar venta**
   - Verificar que se decrementa stock en sucursal correcta
   - Confirmar que productos pesables no afectan stock

3. **Transferir stock entre sucursales**
   - Usar método `transferTo()`
   - Verificar stock en ambas sucursales

---

## ⚠️ Consideraciones Importantes

### Compatibilidad Hacia Atrás

Los métodos legacy **siguen funcionando temporalmente**:

```php
// Estos métodos aún funcionan (usan sucursal principal)
$product->hasStock(10);
$product->decrementStock(5);
```

### Rollback Seguro

```bash
# Revertir las 3 últimas migraciones
php artisan migrate:rollback --step=3
```

Esto restaurará:
- ✅ Campo `stock` en tabla `products`
- ✅ Valores originales de stock
- ✅ Eliminará `product_stocks` y `branches`

### Migración 12 (Opcional)

La migración 12 elimina el campo `stock` de `products`.

**Solo ejecutarla cuando:**
- ✅ Sistema probado y estable
- ✅ Todo el código actualizado
- ✅ Tests pasando al 100%

---

## 📊 Consultas SQL Comunes

### Stock por Sucursal

```sql
SELECT
    b.name AS sucursal,
    COUNT(ps.id) AS productos,
    SUM(ps.stock) AS stock_total
FROM branches b
LEFT JOIN product_stocks ps ON b.id = ps.branch_id
GROUP BY b.id, b.name;
```

### Productos Sin Stock

```sql
SELECT p.name, p.internal_code
FROM products p
LEFT JOIN product_stocks ps ON p.id = ps.product_id
WHERE p.is_weighted = 0
GROUP BY p.id
HAVING COALESCE(SUM(ps.stock), 0) = 0;
```

### Valor del Inventario

```sql
SELECT
    b.name AS sucursal,
    SUM(ps.stock * p.price) AS valor_inventario
FROM branches b
LEFT JOIN product_stocks ps ON b.id = ps.branch_id
LEFT JOIN products p ON ps.product_id = p.id
WHERE p.is_weighted = 0
GROUP BY b.id, b.name;
```

---

## 🎯 Próximos Pasos Recomendados

### Funcionalidades Adicionales

1. **Selector de Sucursal Global**
   - Agregar dropdown en navbar
   - Guardar en sesión
   - Aplicar a todas las operaciones

2. **Transferencias entre Sucursales**
   - Interfaz de transferencias
   - Historial de movimientos
   - Validaciones y aprobaciones

3. **Reportes Avanzados**
   - Dashboard por sucursal
   - Comparativas
   - Alertas de stock bajo

4. **Historial de Movimientos**
   - Tabla `stock_movements`
   - Kardex por producto
   - Auditoría completa

---

## 🐛 Solución de Problemas

### Stock no coincide

```sql
-- Verificar discrepancias
SELECT
    p.id,
    p.name,
    p.stock AS stock_antiguo,
    SUM(ps.stock) AS stock_nuevo
FROM products p
LEFT JOIN product_stocks ps ON p.id = ps.product_id
GROUP BY p.id
HAVING p.stock != COALESCE(SUM(ps.stock), 0);
```

### Productos sin stock

```php
// Crear stock faltante en sucursal principal
$mainBranch = Branch::main();

Product::where('is_weighted', false)
    ->whereDoesntHave('productStocks')
    ->each(function($product) use ($mainBranch) {
        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $mainBranch->id,
            'stock' => $product->stock ?? 0,
        ]);
    });
```

---

## 📞 Soporte

Si encuentras problemas:

1. **Revisa logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Ejecuta verificación:**
   ```bash
   php verificar_migracion.php
   ```

3. **Consulta SQL:**
   Ver archivo `CONSULTAS_SQL_UTILES.sql`

4. **Rollback si necesario:**
   ```bash
   php artisan migrate:rollback --step=3
   ```

---

## 📝 Changelog

### Versión 1.0 (2026-02-04)

- ✅ Creación de tabla `branches`
- ✅ Creación de tabla `product_stocks`
- ✅ Migración automática de stock existente
- ✅ Modelos actualizados con nuevos métodos
- ✅ Compatibilidad hacia atrás
- ✅ Documentación completa

---

## 👥 Contribuciones

Este sistema fue diseñado e implementado por **Claude Code** siguiendo las mejores prácticas de:

- Normalización de bases de datos
- Migraciones reversibles
- Compatibilidad hacia atrás
- Documentación exhaustiva

---

## 📄 Licencia

Este código es parte del sistema MarketControl y sigue la misma licencia del proyecto principal.

---

**Fecha de Implementación:** 2026-02-04
**Versión:** 1.0
**Estado:** ✅ Listo para producción (después de testing)
