<?php

use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

// Ruta principal - Punto de venta
Route::get('/', [SaleController::class, 'pos'])->name('home');

// Rutas de productos
Route::resource('products', ProductController::class);
Route::post('/products/search-barcode', [ProductController::class, 'searchByBarcode'])
    ->name('products.search-barcode');

// Rutas de escaneo de código de barras
Route::get('/barcode/scan', [BarcodeController::class, 'scan'])->name('barcode.scan');
Route::post('/barcode/search', [BarcodeController::class, 'search'])->name('barcode.search');

// Endpoint de prueba para verificar que el servidor funciona
Route::get('/test', function() {
    return response()->json([
        'status' => 'OK',
        'message' => 'El servidor está funcionando correctamente',
        'timestamp' => now()->toDateTimeString(),
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
    ]);
});

Route::post('/test-post', function(\Illuminate\Http\Request $request) {
    \Log::info('🧪 Test POST recibido', [
        'data' => $request->all(),
        'headers' => $request->headers->all(),
    ]);

    return response()->json([
        'status' => 'OK',
        'message' => 'POST recibido correctamente',
        'data_received' => $request->all(),
        'csrf_ok' => true,
    ]);
});

// Rutas de ventas
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::post('/sales/complete', [SaleController::class, 'complete'])->name('sales.complete');
Route::get('/sales/{id}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
Route::get('/sales/export', [SaleController::class, 'export'])->name('sales.export');

// Rutas de finanzas
Route::get('/finances', [\App\Http\Controllers\FinanceController::class, 'index'])->name('finances.index');
Route::get('/finances/expenses', [\App\Http\Controllers\FinanceController::class, 'expenses'])->name('finances.expenses');
Route::get('/finances/expenses/create', [\App\Http\Controllers\FinanceController::class, 'createExpense'])->name('finances.expenses.create');
Route::post('/finances/expenses', [\App\Http\Controllers\FinanceController::class, 'storeExpense'])->name('finances.expenses.store');
Route::get('/finances/expenses/{id}/edit', [\App\Http\Controllers\FinanceController::class, 'editExpense'])->name('finances.expenses.edit');
Route::put('/finances/expenses/{id}', [\App\Http\Controllers\FinanceController::class, 'updateExpense'])->name('finances.expenses.update');
Route::delete('/finances/expenses/{id}', [\App\Http\Controllers\FinanceController::class, 'destroyExpense'])->name('finances.expenses.destroy');
Route::get('/finances/export-report', [\App\Http\Controllers\FinanceController::class, 'exportReport'])->name('finances.export');

// Rutas de sucursales (branches)
Route::resource('branches', BranchController::class);
Route::post('/branches/set-active', [BranchController::class, 'setActive'])->name('branches.set-active');
