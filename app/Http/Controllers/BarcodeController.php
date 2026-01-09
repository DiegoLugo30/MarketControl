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
     * Buscar producto por código de barras
     * Busca primero en BD local, luego en API externa
     */
    public function search(Request $request)
    {
        $barcode = $request->input('barcode');

        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Código de barras vacío',
            ]);
        }

        // Buscar en base de datos local
        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'found_locally' => true,
                'product' => [
                    'id' => $product->id,
                    'barcode' => $product->barcode,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                ],
            ]);
        }

        // Si no existe localmente, consultar API externa
        $apiResult = $this->productApiService->searchByBarcode($barcode);

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
                ],
            ]);
        }

        // No se encontró en ningún lado
        return response()->json([
            'success' => true,
            'found_locally' => false,
            'found_api' => false,
            'barcode' => $barcode,
        ]);
    }
}
