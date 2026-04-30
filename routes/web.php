<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProvidersController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Store\AccountController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Support\Facades\Route;

// ── Autenticación ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [LoginController::class,    'create'])->name('login');
    Route::post('/login',    [LoginController::class,    'store']);
    Route::get('/register',  [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

// ── Raíz: redirige según rol o a login ──────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.home')
            : redirect()->route('store.index');
    }
    return redirect()->route('login');
});

// ── Panel de administración — requiere auth + rol admin ──────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Punto de venta
    Route::get('/', [SaleController::class, 'pos'])->name('home');
    Route::get('/pos', [SaleController::class, 'pos'])->name('pos');

    // Productos — la ruta personalizada va antes del resource para evitar conflictos
    Route::post('products/search-barcode', [ProductController::class, 'searchByBarcode'])
        ->name('products.search-barcode');
    Route::resource('products', ProductController::class);

    // Código de barras
    Route::get('barcode/scan',    [BarcodeController::class, 'scan'])->name('barcode.scan');
    Route::post('barcode/search', [BarcodeController::class, 'search'])->name('barcode.search');

    // Ventas — export antes del segmento dinámico {id}
    Route::get('sales/export',              [SaleController::class, 'export'])->name('sales.export');
    Route::get('sales',                     [SaleController::class, 'index'])->name('sales.index');
    Route::post('sales/complete',           [SaleController::class, 'complete'])->name('sales.complete');
    Route::get('sales/{id}/receipt',        [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::get('sales/{id}/receipt-print',  [SaleController::class, 'receiptPrint'])->name('sales.receipt.print');

    // Finanzas
    Route::get('finances',                    [FinanceController::class, 'index'])->name('finances.index');
    Route::get('finances/expenses',           [FinanceController::class, 'expenses'])->name('finances.expenses');
    Route::get('finances/expenses/create',    [FinanceController::class, 'createExpense'])->name('finances.expenses.create');
    Route::post('finances/expenses',          [FinanceController::class, 'storeExpense'])->name('finances.expenses.store');
    Route::get('finances/expenses/{id}/edit', [FinanceController::class, 'editExpense'])->name('finances.expenses.edit');
    Route::put('finances/expenses/{id}',      [FinanceController::class, 'updateExpense'])->name('finances.expenses.update');
    Route::delete('finances/expenses/{id}',   [FinanceController::class, 'destroyExpense'])->name('finances.expenses.destroy');
    Route::get('finances/export-report',      [FinanceController::class, 'exportReport'])->name('finances.export');

    // Sucursales — set-active antes del resource para que no capture "set-active" como {branch}
    Route::post('branches/set-active', [BranchController::class, 'setActive'])->name('branches.set-active');
    Route::resource('branches', BranchController::class);

    // Proveedores
    Route::resource('providers', ProvidersController::class);

    // Pedidos de la tienda
    Route::get('orders',                [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{code}',         [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{code}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// ── Diagnóstico (sin auth) ───────────────────────────────────────────────────
Route::get('/test', function () {
    $request = request();
    return response()->json([
        'status'              => 'OK',
        'timestamp'           => now()->toDateTimeString(),
        'php_version'         => phpversion(),
        'laravel_version'     => app()->version(),
        // HTTPS diagnostics — remove once confirmed working in production
        'is_secure'           => $request->isSecure(),
        'scheme'              => $request->getScheme(),
        'x_forwarded_proto'   => $request->header('X-Forwarded-Proto'),
        'x_forwarded_for'     => $request->header('X-Forwarded-For'),
        'app_url'             => config('app.url'),
        'app_env'             => config('app.env'),
        'generated_route_url' => route('login'),
    ]);
});

Route::post('/test-post', function (\Illuminate\Http\Request $request) {
    \Log::info('🧪 Test POST recibido', [
        'data'    => $request->all(),
        'headers' => $request->headers->all(),
    ]);

    return response()->json([
        'status'        => 'OK',
        'message'       => 'POST recibido correctamente',
        'data_received' => $request->all(),
        'csrf_ok'       => true,
    ]);
});

// ── Tienda pública — accesible sin autenticación ─────────────────────────────
Route::prefix('tienda')->name('store.')->group(function () {
    Route::get('/',              [StoreController::class, 'index'])->name('index');
    Route::get('/producto/{id}', [StoreController::class, 'show'])->name('product');
    Route::post('/orders',       [OrderController::class, 'store'])->name('orders.store');

    // ── Área de cuenta — requiere autenticación ──────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::get('/mi-cuenta/pedidos',        [AccountController::class, 'orders'])->name('account.orders');
        Route::get('/mi-cuenta/pedidos/{code}', [AccountController::class, 'show'])->name('account.orders.show');
    });
});
