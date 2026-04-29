@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-edit"></i> Editar Producto
        </h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            @method('PUT')

            <!-- Tipo de Producto -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label class="block text-gray-700 font-semibold mb-3">
                    Tipo de Producto *
                </label>
                <div class="flex space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_weighted" value="0" {{ old('is_weighted', $product->is_weighted) == 0 ? 'checked' : '' }} class="mr-2" id="type-unit">
                        <span class="text-lg">
                            <i class="fas fa-box"></i> Por Unidad
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_weighted" value="1" {{ old('is_weighted', $product->is_weighted) == 1 ? 'checked' : '' }} class="mr-2" id="type-weight">
                        <span class="text-lg">
                            <i class="fas fa-weight"></i> Por Peso (kg)
                        </span>
                    </label>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    <i class="fas fa-info-circle"></i> Cambiar el tipo puede afectar ventas futuras
                </p>
            </div>

            <!-- Código Interno -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Código Interno *
                    <span class="text-sm font-normal text-gray-500">(ej: A001, FRU12, SEM001)</span>
                </label>
                <input
                    type="text"
                    name="internal_code"
                    value="{{ old('internal_code', $product->internal_code) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('internal_code') border-red-500 @enderror"
                    required
                    placeholder="Ejemplo: A001"
                >
                @error('internal_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Código de Barras -->
            <div class="mb-4" id="barcode-field">
                <label class="block text-gray-700 font-semibold mb-2">
                    Código de Barras (EAN)
                    <span class="text-sm font-normal text-gray-500">(opcional para productos pesables)</span>
                </label>
                <input
                    type="text"
                    name="barcode"
                    value="{{ old('barcode', $product->barcode) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('barcode') border-red-500 @enderror"
                    placeholder="Escanea o ingresa el código de barras"
                >
                @error('barcode')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nombre -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Nombre del Producto *
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $product->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Descripción
                </label>
                <textarea
                    name="description"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                >{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Imagen del Producto -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    Imagen del Producto
                </label>
                @if($product->image_path)
                    <div class="mb-3 flex items-center gap-4">
                        <img
                            src="{{ asset('storage/' . $product->image_path) }}"
                            alt="{{ $product->name }}"
                            class="w-20 h-20 object-cover rounded-lg border border-gray-200 shadow-sm"
                        >
                        <p class="text-sm text-gray-500">Imagen actual. Subí una nueva para reemplazarla.</p>
                    </div>
                @endif
                <input
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600
                           file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0
                           file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                           hover:file:bg-blue-100 cursor-pointer
                           @error('image') border-red-500 @enderror"
                >
                @error('image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1">JPG, PNG o WebP. Máximo 2 MB.</p>
            </div>

            <!-- Campos para Producto por Unidad -->
            <div id="unit-fields">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Precio Unitario *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input
                                type="number"
                                name="price"
                                value="{{ old('price', $product->price) }}"
                                step="0.01"
                                min="0"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror"
                                id="price-unit"
                            >
                        </div>
                        @error('price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Stock en {{ $activeBranch ? $activeBranch->name : 'Sucursal' }} *
                        </label>
                        <input
                            type="number"
                            name="stock"
                            value="{{ old('stock', $activeBranch ? $product->getStockInBranch($activeBranch->id) : 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('stock') border-red-500 @enderror"
                            id="stock-unit"
                        >
                        @error('stock')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-600 mt-1">
                            <i class="fas fa-info-circle"></i> Este stock es específico para la sucursal activa
                        </p>
                    </div>
                </div>
            </div>

            <!-- Campos para Producto Pesable -->
            <div id="weight-fields" class="hidden">
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Precio por Kilogramo *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input
                            type="number"
                            name="price_per_kg"
                            value="{{ old('price_per_kg', $product->price_per_kg) }}"
                            step="0.01"
                            min="0"
                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_per_kg') border-red-500 @enderror"
                            id="price-kg"
                            disabled
                        >
                        <span class="absolute right-3 top-2 text-gray-500">/kg</span>
                    </div>
                    @error('price_per_kg')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-blue-600 mt-2">
                        <i class="fas fa-info-circle"></i> Los productos pesables no requieren stock
                    </p>
                </div>
            </div>

            <!-- Visibilidad en Tienda -->
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <input type="hidden" name="visible_in_store" value="0">
                    <input
                        type="checkbox"
                        id="visible_in_store"
                        name="visible_in_store"
                        value="1"
                        {{ old('visible_in_store', $product->visible_in_store) ? 'checked' : '' }}
                        class="mt-1 w-4 h-4 accent-green-600"
                    >
                    <label for="visible_in_store" class="cursor-pointer">
                        <span class="block font-semibold text-gray-700">
                            <i class="fas fa-store text-green-600 mr-1"></i>
                            Visible en la Tienda
                        </span>
                        <span class="block text-sm text-gray-500 mt-0.5">
                            Mostrá este producto en el catálogo público en <code>/tienda</code>
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex justify-between">
                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-save"></i> Actualizar Producto
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>

                <button type="button" onclick="if(confirm('¿Estás seguro de eliminar este producto?')) document.getElementById('delete-form').submit();" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </form>

        <form id="delete-form" action="{{ route('admin.products.destroy', $product) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const typeUnit = $('#type-unit');
    const typeWeight = $('#type-weight');
    const unitFields = $('#unit-fields');
    const weightFields = $('#weight-fields');
    const priceUnit = $('#price-unit');
    const stockUnit = $('#stock-unit');
    const priceKg = $('#price-kg');

    function toggleFields() {
        if (typeWeight.is(':checked')) {
            // Modo pesable
            unitFields.addClass('hidden');
            weightFields.removeClass('hidden');
            priceUnit.prop('required', false).prop('disabled', true);
            stockUnit.prop('required', false).prop('disabled', true);
            priceKg.prop('required', true).prop('disabled', false);
        } else {
            // Modo por unidad
            unitFields.removeClass('hidden');
            weightFields.addClass('hidden');
            priceUnit.prop('required', true).prop('disabled', false);
            stockUnit.prop('required', true).prop('disabled', false);
            priceKg.prop('required', false).prop('disabled', true);
        }
    }

    $('input[name="is_weighted"]').on('change', toggleFields);
    toggleFields(); // Inicializar estado al cargar la página
});
</script>
@endpush
