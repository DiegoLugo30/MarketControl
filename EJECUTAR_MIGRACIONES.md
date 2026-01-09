# 🔧 Solución: Ejecutar Migraciones con Productos Existentes

## Problema
La migración falló porque ya existen productos en la base de datos y se intentó agregar una columna `internal_code` NOT NULL.

## Solución Aplicada
Se modificó la migración para:
1. Agregar `internal_code` como nullable primero
2. Generar códigos internos automáticamente para productos existentes:
   - Si el producto tiene barcode, usa ese barcode como internal_code
   - Si no tiene barcode, genera código: `PROD0001`, `PROD0002`, etc.
3. Hacer la columna NOT NULL después

## 📝 Pasos para Ejecutar

### 1. Hacer Rollback de la Migración Fallida
```bash
docker-compose exec app php artisan migrate:rollback
```

**Salida esperada:**
```
INFO  Rolling back migrations.

  2024_01_01_000004_add_weighted_products_support .......................... DONE
```

### 2. Ejecutar las Migraciones Nuevamente
```bash
docker-compose exec app php artisan migrate
```

**Salida esperada:**
```
INFO  Running migrations.

  2024_01_01_000004_add_weighted_products_support .......................... DONE
  2024_01_01_000005_add_weight_to_sale_items ............................... DONE
```

### 3. Verificar que Funcionó
```bash
docker-compose exec app php artisan tinker
```

Dentro de tinker:
```php
// Ver productos con sus códigos internos
\App\Models\Product::all(['id', 'internal_code', 'barcode', 'name']);

// Salir
exit
```

## ✅ Resultado

Tus productos existentes ahora tendrán:
- `internal_code` = su barcode (si lo tenían)
- `internal_code` = "PROD0001", "PROD0002", etc. (si no tenían barcode)
- `is_weighted` = false (productos normales por defecto)
- `price_per_kg` = null

Puedes editar estos productos después para:
- Cambiar el internal_code si quieres (ej: "A001", "FRU12")
- Convertirlos a pesables si es necesario

## 🎯 Continuar

Una vez completadas las migraciones, continúa con los pasos del archivo `APLICAR_CAMBIOS.md`:
- Limpiar cache
- Reiniciar aplicación
- Crear productos de prueba

---

**Nota**: Esta modificación preserva todos tus productos existentes y sus ventas.
