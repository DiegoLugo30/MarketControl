@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-box"></i> Productos
            </h1>
            <a href="{{ route('products.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
        </div>

        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Código Interno</th>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-center">Tipo</th>
                            <th class="px-4 py-3 text-left">Descripción</th>
                            <th class="px-4 py-3 text-right">Precio</th>
                            <th class="px-4 py-3 text-right">Stock</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-mono font-semibold">{{ $product->internal_code }}</div>
                                    @if($product->barcode)
                                        <div class="text-xs text-gray-500 font-mono">{{ $product->barcode }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($product->is_weighted)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="fas fa-weight mr-1"></i> Pesable
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                            <i class="fas fa-box mr-1"></i> Unidad
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-sm">
                                    {{ Str::limit($product->description, 40) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-green-600">
                                    @if($product->is_weighted)
                                        ${{ number_format($product->price_per_kg, 2) }}<span class="text-xs text-gray-500">/kg</span>
                                    @else
                                        ${{ number_format($product->price, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($product->is_weighted)
                                        <span class="text-gray-400 text-sm">N/A</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-sm {{ $product->stock > 10 ? 'bg-green-100 text-green-800' : ($product->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $product->stock }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('products.edit', $product) }}" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="https://marketcontrol-production-3c1f.up.railway.app/products/{{ $product->id }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-box-open text-6xl mb-4"></i>
                <p class="text-xl">No hay productos registrados</p>
                <a href="{{ route('products.create') }}" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Crear primer producto
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
