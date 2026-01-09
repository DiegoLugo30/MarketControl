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
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Verificar stock de todos los productos
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->hasStock($item['quantity'])) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para {$product->name}. Disponible: {$product->stock}",
                    ], 422);
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

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                // Decrementar stock
                $product->decrementStock($item['quantity']);
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
