# 🏢 Sistema de Sucursal Activa - MarketControl

## 📋 Descripción

Sistema completo de **sucursal activa** que permite a los usuarios trabajar sobre una sucursal específica, con filtrado automático de todos los datos (ventas, gastos, stock) por la sucursal seleccionada.

---

## ✅ Características Principales

- 🎯 **Sucursal activa persistente** en sesión
- 🔄 **Filtrado automático** de ventas y gastos
- 🎨 **Selector elegante** en el navbar
- ⚙️ **CRUD completo** de sucursales
- 🚀 **Sin cambios en código existente** (trait con global scope)
- 📱 **Responsive** y fácil de usar

---

## 🚀 Instalación (3 Pasos)

### Paso 1: Ejecutar Migración

```bash
php artisan migrate
```

Esto ejecutará la migración 13 que agrega `branch_id` a `sales` y `expenses`.

### Paso 2: Limpiar Caché

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Paso 3: Verificar

Accede a tu aplicación y verás:
- ✅ Selector de sucursal en el navbar
- ✅ Sucursal activa mostrada
- ✅ Opción "Gestionar Sucursales"

---

## 💻 Uso Básico

### Obtener Sucursal Activa

```php
// Helper global
$branch = active_branch();
$branchId = active_branch_id();

// En vistas Blade
{{ $activeBranch->name }}
```

### Consultas Filtradas Automáticamente

```php
// ✅ Estos modelos SE FILTRAN automáticamente por sucursal activa
$sales = Sale::all();
$expenses = Expense::all();

// ❌ Consultar SIN filtro
$allSales = Sale::withoutBranchScope()->get();

// 🔍 Consultar sucursal específica
$salesBranch2 = Sale::forBranch(2)->get();
```

### Cambiar Sucursal

El selector en el navbar permite cambiar de sucursal con un clic. También puedes hacerlo programáticamente:

```php
set_active_branch(2);
```

---

## 📂 Archivos Principales

| Tipo | Archivo | Descripción |
|------|---------|-------------|
| **Middleware** | `app/Http/Middleware/SetActiveBranch.php` | Establece sucursal activa |
| **Trait** | `app/Traits/BelongsToBranch.php` | Filtrado automático |
| **Controlador** | `app/Http/Controllers/BranchController.php` | CRUD sucursales |
| **Helper** | `app/Helpers/BranchHelper.php` | Funciones globales |
| **Componente** | `resources/views/components/branch-selector.blade.php` | Selector UI |
| **Vistas** | `resources/views/branches/*.blade.php` | CRUD sucursales |

---

## 🎨 UI del Selector

**Ubicación:** Navbar (esquina superior derecha)

```
┌────────────────────────────────────┐
│  🏢 Sucursal Principal    ▼       │
└────────────────────────────────────┘
         │
         │ Click
         ▼
┌────────────────────────────────────┐
│  SELECCIONAR SUCURSAL              │
├────────────────────────────────────┤
│  ⭐ Sucursal Principal         ✓   │
│  🏢 Sucursal Norte                 │
│  🏢 Sucursal Sur                   │
├────────────────────────────────────┤
│  ⚙️  Gestionar Sucursales          │
└────────────────────────────────────┘
```

---

## 🔧 Modelos que Usan el Trait

Los siguientes modelos se filtran automáticamente por sucursal activa:

- ✅ `Sale` (Ventas)
- ✅ `Expense` (Gastos)

Para agregar más modelos, simplemente usa el trait:

```php
use App\Traits\BelongsToBranch;

class MiModelo extends Model
{
    use BelongsToBranch;

    protected $fillable = ['branch_id', ...];
}
```

---

## 📊 Ejemplos de Consultas

### Ventas

```php
// Ventas de hoy (sucursal activa)
$ventasHoy = Sale::whereDate('created_at', today())->get();

// Ventas totales (sucursal activa)
$totalVentas = Sale::sum('total');

// Ventas de TODAS las sucursales
$totalGlobal = Sale::withoutBranchScope()->sum('total');

// Ventas por sucursal
$ventasPorSucursal = Sale::withoutBranchScope()
    ->join('branches', 'sales.branch_id', '=', 'branches.id')
    ->selectRaw('branches.name, SUM(sales.total) as total')
    ->groupBy('branches.id', 'branches.name')
    ->get();
```

### Gastos

```php
// Gastos del mes (sucursal activa)
$gastosDelMes = Expense::whereMonth('date', now()->month)->sum('amount');

// Gastos por categoría (sucursal activa)
$gastosPorCategoria = Expense::selectRaw('category, SUM(amount) as total')
    ->groupBy('category')
    ->get();
```

---

## ⚙️ Gestión de Sucursales

### Crear Sucursal

**Vía UI:** Navega a "Gestionar Sucursales" → "Nueva Sucursal"

**Vía código:**
```php
Branch::create([
    'code' => 'SUC002',
    'name' => 'Sucursal Norte',
    'address' => 'Av. Principal #123',
    'phone' => '+1234567890',
    'is_main' => false,
    'is_active' => true,
]);
```

### Rutas Disponibles

```
GET    /branches              # Listar sucursales
GET    /branches/create       # Formulario crear
POST   /branches              # Guardar sucursal
GET    /branches/{id}/edit    # Formulario editar
PUT    /branches/{id}         # Actualizar sucursal
DELETE /branches/{id}         # Eliminar sucursal

POST   /branches/set-active   # Cambiar sucursal activa (AJAX)
```

---

## 🔒 Validaciones

### Sucursal Principal
- Solo puede haber **una** sucursal principal
- Al marcar una como principal, las demás se desmarcan
- **No se puede eliminar**

### Eliminación de Sucursal
Una sucursal NO se puede eliminar si:
- ❌ Es la sucursal principal
- ❌ Tiene ventas asociadas
- ❌ Tiene gastos asociados
- ❌ Tiene stock > 0

---

## 🧪 Testing

### Pruebas Manuales

1. **Cambiar de sucursal**
   - Usar selector en navbar
   - Verificar que página recarga
   - Verificar que datos cambian

2. **Crear venta**
   - Crear venta en Sucursal A
   - Cambiar a Sucursal B
   - Verificar que venta NO aparece en B

3. **Filtrado automático**
   - Ver ventas del día
   - Cambiar de sucursal
   - Verificar que ventas son diferentes

---

## 📞 Solución de Problemas

### No aparece selector de sucursal

```bash
php artisan config:clear
php artisan view:clear
```

Verificar que Alpine.js está cargado (incluido en `branch-selector.blade.php`)

### Datos no se filtran

1. Verificar que el modelo usa `BelongsToBranch`
2. Verificar que la tabla tiene `branch_id`
3. Ejecutar migración 13

### Error al cambiar sucursal

- Verificar token CSRF
- Revisar logs: `storage/logs/laravel.log`
- Verificar que la ruta existe: `php artisan route:list | grep branches`

---

## 📚 Documentación Completa

Para documentación detallada, consulta:

**📁 Ubicación:** `[carpeta_temporal]/scratchpad/SISTEMA_SUCURSAL_ACTIVA_IMPLEMENTACION.md`

Incluye:
- Arquitectura completa
- Ejemplos de código
- Casos de uso avanzados
- Extensiones recomendadas

---

## 🎯 Próximos Pasos

### Extensiones Recomendadas

1. **Permisos por Sucursal**
   - Asignar usuarios a sucursales
   - Restringir acceso

2. **Reportes Comparativos**
   - Dashboard multi-sucursal
   - Gráficos comparativos

3. **Transferencias de Stock**
   - Mover stock entre sucursales
   - Historial de transferencias

4. **Notificaciones**
   - Alertas por sucursal
   - Stock bajo por sucursal

---

## ✅ Checklist de Verificación

```
[✅] Ejecutar php artisan migrate
[✅] Limpiar caché
[✅] Verificar selector en navbar
[✅] Crear 2+ sucursales de prueba
[✅] Cambiar entre sucursales
[✅] Crear venta en sucursal A
[✅] Cambiar a sucursal B
[✅] Verificar que venta no aparece en B
[✅] Revisar reportes financieros
```

---

## 🎉 ¡Todo Listo!

El sistema de sucursal activa está completamente funcional. Los usuarios ahora pueden:
- ✅ Seleccionar su sucursal de trabajo
- ✅ Ver solo datos de su sucursal
- ✅ Gestionar múltiples sucursales
- ✅ Cambiar de sucursal en cualquier momento

**¡Sin necesidad de modificar código existente!**

---

**Implementado:** 2026-02-04
**Versión:** 1.0
**Autor:** Claude Code
