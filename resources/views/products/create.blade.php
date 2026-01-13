@extends('layouts.app')

@section('title', 'Crear Producto')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Producto
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

        <form action="https://marketcontrol-production-3c1f.up.railway.app/products" method="POST" id="product-form">
        @csrf

            <!-- Tipo de Producto -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label class="block text-gray-700 font-semibold mb-3">
                    Tipo de Producto *
                </label>
                <div class="flex space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_weighted" value="0" checked class="mr-2" id="type-unit">
                        <span class="text-lg">
                            <i class="fas fa-box"></i> Por Unidad
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_weighted" value="1" class="mr-2" id="type-weight">
                        <span class="text-lg">
                            <i class="fas fa-weight"></i> Por Peso (kg)
                        </span>
                    </label>
                </div>
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
                    value="{{ old('internal_code') }}"
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
                    value="{{ old('barcode') }}"
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
                    value="{{ old('name') }}"
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
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
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
                                value="{{ old('price') }}"
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
                            Stock Inicial *
                        </label>
                        <input
                            type="number"
                            name="stock"
                            value="{{ old('stock', 0) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('stock') border-red-500 @enderror"
                            id="stock-unit"
                        >
                        @error('stock')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
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
                            value="{{ old('price_per_kg') }}"
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

            <div class="flex space-x-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
                <a href="{{ route('products.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
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
    toggleFields(); // Inicializar estado
});
</script>
@endpush
