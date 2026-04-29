<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Public product catalogue.
     *
     * Shows only products where:
     *   - visible_in_store = true
     *   - AND (is_weighted = true  OR  has stock > 0 in at least one branch)
     */
    public function index(Request $request)
    {
        $query = Product::where('visible_in_store', true)
            ->with('productStocks'); // eager-load for stock badge; zero-stock products are still shown

        // Full-text search (case-insensitive)
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            });
        }

        $products = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('store.index', compact('products'));
    }

    /**
     * Single product detail page.
     */
    public function show(int $id)
    {
        $product = Product::where('visible_in_store', true)
            ->with('productStocks')
            ->findOrFail($id);

        return view('store.product', compact('product'));
    }
}
