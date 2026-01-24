<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductApiService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __construct(
        private ProductApiService $productApiService
    ) {}

    /**
     * Vista principal de escaneo
     */
    public function scan()
    {
        return view('barcode.scan');
    }

    /**
     * Buscar producto por código de barras o código interno
     * Busca primero en BD local (barcode, luego internal_code), después en API externa
     */
    public function search(Request $request)
    {
        $code = $request->input('barcode'); // Puede ser barcode o internal_code

        \Log::info('🔎 Búsqueda de producto iniciada', [
            'code' => $code,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (empty($code)) {
            \Log::warning('⚠️ Búsqueda con código vacío');
            return response()->json([
                'success' => false,
                'message' => 'Código vacío',
            ]);
        }

        try {
            // Normalizar código a mayúsculas para búsqueda case-insensitive
            $normalizedCode = strtoupper($code);

            // Buscar en base de datos local - primero por barcode
            $product = Product::whereRaw('UPPER(barcode) = ?', [$normalizedCode])->first();

            // Si no se encuentra por barcode, buscar por internal_code
            if (!$product) {
                $product = Product::whereRaw('UPPER(internal_code) = ?', [$normalizedCode])->first();
            }

            if ($product) {
                \Log::info('✅ Producto encontrado localmente', [
                    'code' => $code,
                    'product_id' => $product->id,
                    'name' => $product->name,
                ]);

                return response()->json([
                    'success' => true,
                    'found_locally' => true,
                    'product' => [
                        'id' => $product->id,
                        'internal_code' => $product->internal_code,
                        'barcode' => $product->barcode,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'price_per_kg' => $product->price_per_kg,
                        'stock' => $product->stock,
                        'is_weighted' => $product->is_weighted,
                        'requires_weight' => $product->requiresWeight(),
                    ],
                ]);
            }

            // Si no existe localmente, consultar API externa (solo para códigos que parezcan EAN)
            if (strlen($code) >= 8 && is_numeric($code)) {
                \Log::info('🌐 Consultando API externa', [
                    'code' => $code,
                    'code_length' => strlen($code),
                ]);

                $apiResult = $this->productApiService->searchByBarcode($code);

                if ($apiResult && $apiResult['found']) {
                    \Log::info('✅ Producto encontrado en API externa', [
                        'code' => $code,
                        'name' => $apiResult['name'],
                    ]);

                    return response()->json([
                        'success' => true,
                        'found_locally' => false,
                        'found_api' => true,
                        'product' => [
                            'barcode' => $apiResult['barcode'],
                            'name' => $apiResult['name'],
                            'description' => $apiResult['description'],
                            'price' => null,
                            'stock' => null,
                            'is_weighted' => false,
                        ],
                    ]);
                } else {
                    \Log::info('ℹ️ Producto no encontrado en API externa', [
                        'code' => $code,
                    ]);
                }
            } else {
                \Log::info('⏭️ Código no válido para API externa (debe ser numérico y >= 8 dígitos)', [
                    'code' => $code,
                    'length' => strlen($code),
                    'is_numeric' => is_numeric($code),
                ]);
            }

            // No se encontró en ningún lado
            \Log::info('❌ Producto no encontrado', [
                'code' => $code,
            ]);

            return response()->json([
                'success' => true,
                'found_locally' => false,
                'found_api' => false,
                'code' => $code,
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error inesperado en búsqueda de producto', [
                'code' => $code,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar el producto: ' . $e->getMessage(),
                'error_type' => get_class($e),
            ], 500);
        }
    }
}
