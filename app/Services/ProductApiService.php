<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductApiService
{
    private const API_URL = 'https://world.openfoodfacts.org/api/v0/product/';

    /**
     * Buscar producto en OpenFoodFacts por código de barras
     *
     * @param string $barcode
     * @return array|null
     */
    public function searchByBarcode(string $barcode): ?array
    {
        try {
            $response = Http::timeout(5)
                ->get(self::API_URL . $barcode . '.json');

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (!isset($data['status']) || $data['status'] !== 1) {
                return null;
            }

            $product = $data['product'] ?? [];

            return [
                'found' => true,
                'name' => $this->extractName($product),
                'description' => $this->extractDescription($product),
                'barcode' => $barcode,
            ];
        } catch (\Exception $e) {
            // Log a Laravel log normal
            Log::error('Error consultando OpenFoodFacts API', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
            ]);

            // Además imprimilo a stdout para que aparezca en Deploy Logs
            echo "[DEBUG] Error consultando API: " . $e->getMessage() . " (barcode: $barcode)\n";

            return null;
        }

    }

    /**
     * Extraer nombre del producto
     */
    private function extractName(array $product): ?string
    {
        return $product['product_name']
            ?? $product['product_name_es']
            ?? $product['product_name_en']
            ?? null;
    }

    /**
     * Extraer descripción del producto
     */
    private function extractDescription(array $product): ?string
    {
        $parts = [];

        if (!empty($product['brands'])) {
            $parts[] = 'Marca: ' . $product['brands'];
        }

        if (!empty($product['quantity'])) {
            $parts[] = 'Cantidad: ' . $product['quantity'];
        }

        if (!empty($product['categories'])) {
            $parts[] = 'Categorías: ' . $product['categories'];
        }

        return !empty($parts) ? implode(' | ', $parts) : null;
    }
}
