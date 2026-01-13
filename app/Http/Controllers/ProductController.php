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
        $isWeighted = $request->input('is_weighted', false);

        $rules = [
            'internal_code' => 'required|string|unique:products,internal_code',
            'barcode' => 'nullable|string|unique:products,barcode',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_weighted' => 'boolean',
        ];

        $messages = [
            'internal_code.required' => 'El código interno es obligatorio',
            'internal_code.unique' => 'Este código interno ya existe',
            'barcode.unique' => 'Este código de barras ya existe',
            'name.required' => 'El nombre es obligatorio',
        ];

        // Validaciones condicionales según tipo de producto
        if ($isWeighted) {
            $rules['price_per_kg'] = 'required|numeric|min:0';
            $messages['price_per_kg.required'] = 'El precio por kg es obligatorio';
            $messages['price_per_kg.min'] = 'El precio por kg debe ser mayor o igual a 0';
        } else {
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
            $messages['price.required'] = 'El precio es obligatorio';
            $messages['price.min'] = 'El precio debe ser mayor o igual a 0';
            $messages['stock.required'] = 'El stock es obligatorio';
            $messages['stock.min'] = 'El stock debe ser mayor o igual a 0';
        }

        $validated = $request->validate($rules, $messages);

        // Establecer valores por defecto según tipo
        if ($isWeighted) {
            $validated['stock'] = 0;
            $validated['price'] = 0;
        } else {
            $validated['price_per_kg'] = null;
        }

        $product = Product::create($validated);

        return redirect()->back()->with('success', 'Producto creado correctamente.');
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
        $isWeighted = $request->input('is_weighted', $product->is_weighted);

        $rules = [
            'internal_code' => [
                'required',
                'string',
                Rule::unique('products', 'internal_code')->ignore($product->id),
            ],
            'barcode' => [
                'nullable',
                'string',
                Rule::unique('products', 'barcode')->ignore($product->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_weighted' => 'boolean',
        ];

        $messages = [
            'internal_code.required' => 'El código interno es obligatorio',
            'internal_code.unique' => 'Este código interno ya existe',
            'barcode.unique' => 'Este código de barras ya existe',
            'name.required' => 'El nombre es obligatorio',
        ];

        // Validaciones condicionales según tipo de producto
        if ($isWeighted) {
            $rules['price_per_kg'] = 'required|numeric|min:0';
            $messages['price_per_kg.required'] = 'El precio por kg es obligatorio';
            $messages['price_per_kg.min'] = 'El precio por kg debe ser mayor o igual a 0';
        } else {
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
            $messages['price.required'] = 'El precio es obligatorio';
            $messages['price.min'] = 'El precio debe ser mayor o igual a 0';
            $messages['stock.required'] = 'El stock es obligatorio';
            $messages['stock.min'] = 'El stock debe ser mayor o igual a 0';
        }

        $validated = $request->validate($rules, $messages);

        // Establecer valores por defecto según tipo
        if ($isWeighted) {
            $validated['stock'] = 0;
            $validated['price'] = 0;
        } else {
            $validated['price_per_kg'] = null;
        }

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
