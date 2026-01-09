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

// Rutas de ventas
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::post('/sales/complete', [SaleController::class, 'complete'])->name('sales.complete');
Route::get('/sales/{id}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
