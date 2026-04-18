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
     * Procesar y completar una venta.
     *
     * Soporta tres tipos de items:
     *   1. Producto por unidad  – product_id present, is_weighted = false
     *   2. Producto por peso    – product_id present, is_weighted = true
     *   3. Producto manual      – is_custom = true, product_id = null
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'nullable|exists:products,id',
            'items.*.name'             => 'nullable|string|max:255',
            'items.*.is_custom'        => 'nullable|boolean',
            'items.*.unit_type'        => 'nullable|in:unit,weight',
            'items.*.quantity'         => 'nullable|integer|min:1',
            'items.*.weight'           => 'nullable|numeric|min:0.001',
            'items.*.price'            => 'required|numeric|min:0',
            'items.*.item_discount'    => 'nullable|numeric|min:0',
            'total'                    => 'required|numeric|min:0',
            'discount_amount'          => 'nullable|numeric|min:0',
            'discount_description'     => 'nullable|string|max:255',
            'payment_method'           => 'required|in:efectivo,debito,transferencia,cuenta_dni,rappi',
        ]);

        // ── Per-item business-rule validation ────────────────────────────────
        foreach ($validated['items'] as $idx => $item) {
            $isCustom = !empty($item['is_custom']);
            $pos      = $idx + 1;

            if ($isCustom) {
                if (empty($item['name'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Item #{$pos}: el nombre es requerido para productos manuales.",
                    ], 422);
                }

                $unitType = $item['unit_type'] ?? null;

                if (!$unitType) {
                    return response()->json([
                        'success' => false,
                        'message' => "Item #{$pos}: el tipo de unidad es requerido para productos manuales.",
                    ], 422);
                }

                if ($unitType === 'weight') {
                    if (empty($item['weight']) || $item['weight'] <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "Item #{$pos}: el peso debe ser mayor a 0.",
                        ], 422);
                    }
                } else {
                    if (empty($item['quantity']) || $item['quantity'] < 1) {
                        return response()->json([
                            'success' => false,
                            'message' => "Item #{$pos}: la cantidad debe ser al menos 1.",
                        ], 422);
                    }
                }
            } else {
                if (empty($item['product_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Item #{$pos}: product_id es requerido para items no manuales.",
                    ], 422);
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        try {
            DB::beginTransaction();

            // Resolve active branch
            $branchId = session('active_branch_id');
            if (!$branchId) {
                $mainBranch = \App\Models\Branch::main();
                $branchId   = $mainBranch?->id;
            }

            if (!$branchId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error: No hay sucursal configurada en el sistema. Por favor contacte al administrador.',
                ], 500);
            }

            // ── Stock check (catalogue products only) ────────────────────────
            foreach ($validated['items'] as $item) {
                if (!empty($item['is_custom'])) {
                    continue; // Custom items have no stock to check
                }

                $product = Product::findOrFail($item['product_id']);

                if (!$product->is_weighted) {
                    $quantity       = $item['quantity'] ?? 1;
                    $availableStock = $product->getStockInBranch($branchId);

                    if (!$product->hasStockInBranch($branchId, $quantity)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stock insuficiente para {$product->name}. Disponible: {$availableStock}",
                        ], 422);
                    }
                }
            }

            // ── Create the sale record ────────────────────────────────────────
            $sale = Sale::create([
                'total'                => $validated['total'],
                'discount_amount'      => $validated['discount_amount'] ?? 0,
                'discount_description' => $validated['discount_description'] ?? null,
                'payment_method'       => $validated['payment_method'],
                'created_at'           => now(),
            ]);

            // ── Create sale items ─────────────────────────────────────────────
            foreach ($validated['items'] as $item) {
                $isCustom = !empty($item['is_custom']);

                $saleItemData = [
                    'sale_id'       => $sale->id,
                    'price'         => $item['price'],
                    'item_discount' => $item['item_discount'] ?? 0,
                ];

                if ($isCustom) {
                    // ── Manual / custom product ───────────────────────────────
                    $unitType = $item['unit_type'];

                    $saleItemData['product_id'] = null;
                    $saleItemData['name']       = $item['name'];
                    $saleItemData['is_custom']  = true;
                    $saleItemData['unit_type']  = $unitType;

                    if ($unitType === 'weight') {
                        $saleItemData['weight']   = $item['weight'];
                        $saleItemData['quantity'] = 1;
                    } else {
                        $saleItemData['quantity'] = $item['quantity'] ?? 1;
                        $saleItemData['weight']   = null;
                    }
                } else {
                    // ── Catalogue product ─────────────────────────────────────
                    $product = Product::findOrFail($item['product_id']);

                    $saleItemData['product_id'] = $product->id;
                    $saleItemData['name']       = $product->name; // snapshot
                    $saleItemData['is_custom']  = false;
                    $saleItemData['unit_type']  = $product->is_weighted ? 'weight' : 'unit';

                    if ($product->is_weighted && isset($item['weight'])) {
                        $saleItemData['weight']   = $item['weight'];
                        $saleItemData['quantity'] = 1;
                    } else {
                        $saleItemData['quantity'] = $item['quantity'] ?? 1;
                        $saleItemData['weight']   = null;

                        $product->decrementStockInBranch($branchId, $saleItemData['quantity']);
                    }
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
     * Listar ventas con filtros de fecha
     */
    public function index(Request $request)
    {
        $query = Sale::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);
        $sales->appends($request->only(['date_from', 'date_to']));

        return view('sales.index', compact('sales'));
    }

    /**
     * Exportar ventas a Excel (.xls) con estilos y colores
     */
    public function export(Request $request)
    {
        $query = Sale::with('items.product');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $type     = $request->get('type', 'custom');
        $fileName = 'ventas_';

        if ($type === 'daily') {
            $fileName .= 'diario_' . date('Y-m-d');
        } elseif ($type === 'monthly') {
            $fileName .= 'mensual_' . date('Y-m');
        } else {
            $fileName .= 'personalizado_' . date('Y-m-d_His');
        }

        $fileName .= '.xls';

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($sales) {
            echo '<!DOCTYPE html>';
            echo '<html>';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            echo '<style>';
            echo 'body { font-family: Arial, sans-serif; }';
            echo 'table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }';
            echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
            echo '.header { background-color: #2563eb; color: white; font-weight: bold; text-align: center; font-size: 18px; padding: 12px; }';
            echo '.info-label { background-color: #3b82f6; color: white; font-weight: bold; padding: 8px; }';
            echo '.info-value { background-color: #dbeafe; padding: 8px; }';
            echo '.table-header { background-color: #1e40af; color: white; font-weight: bold; text-align: center; }';
            echo '.table-header-green { background-color: #059669; color: white; font-weight: bold; text-align: center; }';
            echo '.total-row { background-color: #fef3c7; font-weight: bold; }';
            echo '.data-row:nth-child(even) { background-color: #f9fafb; }';
            echo '.data-row:nth-child(odd) { background-color: #ffffff; }';
            echo '.number { text-align: right; }';
            echo '.center { text-align: center; }';
            echo '.money { color: #059669; font-weight: bold; }';
            echo '.manual-badge { background-color: #fed7aa; color: #9a3412; padding: 1px 6px; border-radius: 4px; font-size: 11px; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';

            echo '<table>';
            echo '<tr><td colspan="8" class="header">📊 REPORTE DE VENTAS</td></tr>';
            echo '<tr><td class="info-label">Generado el:</td><td colspan="7" class="info-value">' . date('d/m/Y H:i:s') . '</td></tr>';
            echo '<tr><td class="info-label">Total de ventas:</td><td colspan="7" class="info-value">' . count($sales) . '</td></tr>';
            echo '<tr><td class="info-label">Total general:</td><td colspan="7" class="info-value money">$' . number_format($sales->sum('total'), 2) . '</td></tr>';
            echo '</table>';

            echo '<br/>';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th class="table-header">ID Venta</th>';
            echo '<th class="table-header">Fecha</th>';
            echo '<th class="table-header">Hora</th>';
            echo '<th class="table-header">Cantidad Items</th>';
            echo '<th class="table-header">Método Pago</th>';
            echo '<th class="table-header">Subtotal</th>';
            echo '<th class="table-header">Descuento</th>';
            echo '<th class="table-header">Total</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $paymentIcons = [
                'efectivo'      => '💵',
                'debito'        => '💳',
                'transferencia' => '🏦',
                'cuenta_dni'    => '📱',
                'rappi'         => '🛵',
            ];

            foreach ($sales as $sale) {
                $paymentMethod = $sale->payment_method ?? 'efectivo';
                $paymentIcon   = $paymentIcons[$paymentMethod] ?? '💵';

                echo '<tr class="data-row">';
                echo '<td class="center">' . $sale->id . '</td>';
                echo '<td>' . $sale->created_at->format('d/m/Y') . '</td>';
                echo '<td class="center">' . $sale->created_at->format('H:i:s') . '</td>';
                echo '<td class="center">' . $sale->items->count() . '</td>';
                echo '<td class="center">' . $paymentIcon . ' ' . ucfirst($paymentMethod) . '</td>';
                echo '<td class="number">$' . number_format($sale->calculateSubtotal(), 2) . '</td>';
                echo '<td class="number">$' . number_format($sale->discount_amount, 2) . '</td>';
                echo '<td class="number money">$' . number_format($sale->total, 2) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';

            echo '<br/><br/>';
            echo '<table>';
            echo '<tr><td colspan="9" class="header">📦 DETALLE DE PRODUCTOS VENDIDOS</td></tr>';
            echo '<thead>';
            echo '<tr>';
            echo '<th class="table-header-green">ID Venta</th>';
            echo '<th class="table-header-green">Fecha</th>';
            echo '<th class="table-header-green">Producto</th>';
            echo '<th class="table-header-green">Código Barras</th>';
            echo '<th class="table-header-green">Cantidad/Peso</th>';
            echo '<th class="table-header-green">Precio Unitario</th>';
            echo '<th class="table-header-green">Subtotal Item</th>';
            echo '<th class="table-header-green">Descuento Item</th>';
            echo '<th class="table-header-green">Total Item</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    // Use snapshot name (works for custom items & deleted products)
                    $productName = $item->getDisplayName();
                    $isCustom    = $item->is_custom;

                    // Barcode / code column
                    if ($isCustom) {
                        $productCode = '<span class="manual-badge">Manual</span>';
                    } elseif ($item->product) {
                        $productCode = htmlspecialchars(
                            $item->product->barcode ?? $item->product->internal_code ?? '-'
                        );
                    } else {
                        $productCode = '-';
                    }

                    // Quantity / weight display
                    $quantity = $item->isWeighted()
                        ? number_format($item->weight, 3) . ' kg'
                        : $item->quantity . ' ud.';

                    // Unit price (always derivable from snapshot data)
                    if ($item->isWeighted()) {
                        $weightKg      = (float) $item->weight;
                        $pricePerUnit  = $weightKg > 0
                            ? '$' . number_format((float) $item->price / $weightKg, 2) . '/kg'
                            : '$' . number_format((float) $item->price, 2);
                    } else {
                        $pricePerUnit = '$' . number_format((float) $item->price, 2);
                    }

                    $customLabel = $isCustom
                        ? ' <span class="manual-badge">Manual</span>'
                        : '';

                    echo '<tr class="data-row">';
                    echo '<td class="center">' . $sale->id . '</td>';
                    echo '<td>' . $sale->created_at->format('d/m/Y H:i:s') . '</td>';
                    echo '<td>' . htmlspecialchars($productName) . $customLabel . '</td>';
                    echo '<td class="center">' . $productCode . '</td>';
                    echo '<td class="center">' . $quantity . '</td>';
                    echo '<td class="number">' . $pricePerUnit . '</td>';
                    echo '<td class="number">$' . number_format($item->subtotal, 2) . '</td>';
                    echo '<td class="number">$' . number_format($item->item_discount, 2) . '</td>';
                    echo '<td class="number money">$' . number_format($item->total_with_discount, 2) . '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';

            echo '</body>';
            echo '</html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
