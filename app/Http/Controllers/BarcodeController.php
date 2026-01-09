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

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Código vacío',
            ]);
        }

        // Buscar en base de datos local - primero por barcode
        $product = Product::where('barcode', $code)->first();

        // Si no se encuentra por barcode, buscar por internal_code
        if (!$product) {
            $product = Product::where('internal_code', $code)->first();
        }

        if ($product) {
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
            $apiResult = $this->productApiService->searchByBarcode($code);

            if ($apiResult && $apiResult['found']) {
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
            }
        }

        // No se encontró en ningún lado
        return response()->json([
            'success' => true,
            'found_locally' => false,
            'found_api' => false,
            'code' => $code,
        ]);
    }
}
