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
        $url = self::API_URL . $barcode . '.json';

        Log::info('🔍 Consultando OpenFoodFacts API', [
            'barcode' => $barcode,
            'url' => $url,
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'MarketControl/1.0 (Laravel POS System)',
                ])
                ->get($url);

            $statusCode = $response->status();

            Log::info('📡 Respuesta de OpenFoodFacts API', [
                'barcode' => $barcode,
                'status_code' => $statusCode,
                'successful' => $response->successful(),
                'body_length' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                Log::warning('⚠️ API retornó código no exitoso', [
                    'barcode' => $barcode,
                    'status' => $statusCode,
                    'body' => substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $data = $response->json();

            if (!isset($data['status']) || $data['status'] !== 1) {
                Log::info('ℹ️ Producto no encontrado en OpenFoodFacts', [
                    'barcode' => $barcode,
                    'api_status' => $data['status'] ?? 'undefined',
                ]);
                return null;
            }

            $product = $data['product'] ?? [];

            Log::info('✅ Producto encontrado en OpenFoodFacts', [
                'barcode' => $barcode,
                'name' => $this->extractName($product),
            ]);

            return [
                'found' => true,
                'name' => $this->extractName($product),
                'description' => $this->extractDescription($product),
                'barcode' => $barcode,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('❌ Error de conexión a OpenFoodFacts API', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
                'type' => 'ConnectionException',
            ]);
            return null;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('❌ Error en request a OpenFoodFacts API', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
                'type' => 'RequestException',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('❌ Error inesperado consultando OpenFoodFacts API', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
