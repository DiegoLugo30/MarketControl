<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Vista del punto de venta (POS)
     */
    public function pos()
    {
        return view('sales.pos');
    }

    /**
     * Procesar y completar una venta
     * Soporta productos por unidad y por peso
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.weight' => 'nullable|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Verificar stock de productos no pesables
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Solo verificar stock para productos no pesables
                if (!$product->is_weighted) {
                    $quantity = $item['quantity'] ?? 1;
                    if (!$product->hasStock($quantity)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stock insuficiente para {$product->name}. Disponible: {$product->stock}",
                        ], 422);
                    }
                }
            }

            // Crear la venta
            $sale = Sale::create([
                'total' => $validated['total'],
                'created_at' => now(),
            ]);

            // Crear items y actualizar stock
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $saleItemData = [
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'price' => $item['price'],
                ];

                // Determinar si es producto pesable o por unidad
                if ($product->is_weighted && isset($item['weight'])) {
                    // Producto pesable
                    $saleItemData['weight'] = $item['weight'];
                    $saleItemData['quantity'] = 1; // Para mantener compatibilidad
                } else {
                    // Producto por unidad
                    $saleItemData['quantity'] = $item['quantity'] ?? 1;
                    $saleItemData['weight'] = null;

                    // Decrementar stock solo para productos no pesables
                    $product->decrementStock($saleItemData['quantity']);
                }

                SaleItem::create($saleItemData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'message' => 'Venta registrada exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ver recibo de venta
     */
    public function receipt($id)
    {
        $sale = Sale::with('items.product')->findOrFail($id);
        return view('sales.receipt', compact('sale'));
    }

    /**
     * Listar ventas
     */
    public function index()
    {
        $sales = Sale::orderBy('created_at', 'desc')->paginate(20);
        return view('sales.index', compact('sales'));
    }
}
