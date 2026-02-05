<?php

/**
 * Script de Verificación de Sucursales
 *
 * Ejecutar en producción via: php artisan tinker
 * Luego copiar y pegar este script completo
 */

echo "\n====================================\n";
echo "VERIFICACIÓN DE SUCURSALES\n";
echo "====================================\n\n";

// Verificar que existe la tabla branches
try {
    $branchesExist = \Schema::hasTable('branches');
    echo "✓ Tabla 'branches': " . ($branchesExist ? "EXISTE" : "NO EXISTE") . "\n";

    if (!$branchesExist) {
        echo "\n❌ ERROR: La tabla 'branches' no existe.\n";
        echo "   Ejecuta: php artisan migrate\n\n";
        return;
    }
} catch (\Exception $e) {
    echo "❌ Error al verificar tabla branches: " . $e->getMessage() . "\n\n";
    return;
}

// Verificar que existe la tabla product_stocks
try {
    $stocksExist = \Schema::hasTable('product_stocks');
    echo "✓ Tabla 'product_stocks': " . ($stocksExist ? "EXISTE" : "NO EXISTE") . "\n";

    if (!$stocksExist) {
        echo "\n❌ ERROR: La tabla 'product_stocks' no existe.\n";
        echo "   Ejecuta: php artisan migrate\n\n";
        return;
    }
} catch (\Exception $e) {
    echo "❌ Error al verificar tabla product_stocks: " . $e->getMessage() . "\n\n";
    return;
}

echo "\n";

// Contar sucursales
$totalBranches = \App\Models\Branch::count();
echo "📊 Total de sucursales: {$totalBranches}\n";

if ($totalBranches === 0) {
    echo "\n⚠️  NO HAY SUCURSALES CREADAS\n";
    echo "   Creando sucursal principal...\n\n";

    try {
        $mainBranch = \App\Models\Branch::create([
            'code' => 'MAIN',
            'name' => 'Sucursal Principal',
            'address' => 'Dirección Principal',
            'phone' => '',
            'is_main' => true,
            'is_active' => true,
        ]);

        echo "✅ Sucursal principal creada exitosamente\n";
        echo "   ID: {$mainBranch->id}\n";
        echo "   Nombre: {$mainBranch->name}\n";
        echo "   Código: {$mainBranch->code}\n";
    } catch (\Exception $e) {
        echo "❌ Error al crear sucursal principal: " . $e->getMessage() . "\n";
        return;
    }
} else {
    echo "\n📋 Sucursales registradas:\n";
    $branches = \App\Models\Branch::all();
    foreach ($branches as $branch) {
        $mainStar = $branch->is_main ? ' ⭐' : '';
        $activeStatus = $branch->is_active ? '🟢' : '🔴';
        echo "   {$activeStatus} [{$branch->code}] {$branch->name}{$mainStar}\n";
    }
}

echo "\n";

// Verificar sucursal principal
$mainBranch = \App\Models\Branch::main();
if ($mainBranch) {
    echo "✅ Sucursal principal encontrada: {$mainBranch->name} (ID: {$mainBranch->id})\n";
} else {
    echo "⚠️  No hay sucursal marcada como principal\n";
    echo "   Puedes marcar una existente o crear una nueva\n";
}

echo "\n";

// Verificar productos
$totalProducts = \App\Models\Product::withoutBranchScope()->count();
echo "📦 Total de productos: {$totalProducts}\n";

// Verificar product_stocks
$totalStocks = \App\Models\ProductStock::count();
echo "📊 Total de registros de stock: {$totalStocks}\n";

if ($totalProducts > 0 && $totalStocks === 0) {
    echo "\n⚠️  ADVERTENCIA: Hay productos pero sin stock en product_stocks\n";
    echo "   Ejecuta el script de migración de stock: migrate_stock_to_branches.php\n";
}

echo "\n====================================\n";
echo "VERIFICACIÓN COMPLETADA\n";
echo "====================================\n\n";

echo "📝 Próximos pasos:\n";
if ($totalBranches === 0) {
    echo "1. ✅ Sucursal principal creada automáticamente\n";
    echo "2. Ejecuta el script de migración de stock si tienes productos\n";
} else {
    echo "1. ✅ Sistema multi-sucursal configurado correctamente\n";
}
echo "3. Recarga la aplicación y verifica que funcione correctamente\n";
echo "\n";
