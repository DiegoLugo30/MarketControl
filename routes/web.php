<?php

use App\Http\Controllers\BarcodeController;
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
