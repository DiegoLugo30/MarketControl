<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Listar todos los productos
     */
    public function index()
    {
        $products = Product::orderBy('name')->paginate(20);
        return view('products.index', compact('products'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|unique:products,barcode',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ], [
            'barcode.required' => 'El código de barras es obligatorio',
            'barcode.unique' => 'Este código de barras ya existe',
            'name.required' => 'El nombre es obligatorio',
            'price.required' => 'El precio es obligatorio',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'stock.required' => 'El stock es obligatorio',
            'stock.min' => 'El stock debe ser mayor o igual a 0',
        ]);

        $product = Product::create($validated);

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Producto creado exitosamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'barcode' => [
                'required',
                'string',
                Rule::unique('products', 'barcode')->ignore($product->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ], [
            'barcode.required' => 'El código de barras es obligatorio',
            'barcode.unique' => 'Este código de barras ya existe',
            'name.required' => 'El nombre es obligatorio',
            'price.required' => 'El precio es obligatorio',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'stock.required' => 'El stock es obligatorio',
            'stock.min' => 'El stock debe ser mayor o igual a 0',
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Producto actualizado exitosamente');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()
                ->route('products.index')
                ->with('success', 'Producto eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()
                ->route('products.index')
                ->with('error', 'No se puede eliminar el producto porque tiene ventas asociadas');
        }
    }

    /**
     * Buscar producto por código de barras (AJAX)
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        if (empty($barcode)) {
            return response()->json(['found' => false]);
        }

        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            return response()->json([
                'found' => true,
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

        return response()->json(['found' => false]);
    }
}
