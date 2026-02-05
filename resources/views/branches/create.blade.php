@extends('layouts.app')

@section('title', 'Nueva Sucursal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-building"></i> Nueva Sucursal
            </h1>
        </div>

        <form action="{{ route('branches.store') }}" method="POST">
            @csrf

            <!-- Código -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-barcode"></i> Código *
                </label>
                <input
                    type="text"
                    name="code"
                    value="{{ old('code') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror"
                    placeholder="Ej: SUC001, MATRIZ, etc."
                    required
                >
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-600 mt-1">Código único identificador de la sucursal</p>
            </div>

            <!-- Nombre -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-tag"></i> Nombre *
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Ej: Sucursal Centro, Sucursal Norte, etc."
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dirección -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-map-marker-alt"></i> Dirección
                </label>
                <textarea
                    name="address"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror"
                    placeholder="Dirección física de la sucursal"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-phone"></i> Teléfono
                </label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
                    placeholder="Ej: +1234567890"
                >
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Checkboxes -->
            <div class="mb-6 space-y-3">
                <!-- Sucursal Principal -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_main"
                        id="is_main"
                        value="1"
                        {{ old('is_main') ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label for="is_main" class="ml-2 text-gray-700">
                        <i class="fas fa-star text-yellow-500"></i> Marcar como sucursal principal
                    </label>
                </div>
                <p class="text-sm text-gray-600 ml-6">
                    Solo puede haber una sucursal principal. Si marcas esta, la anterior se desmarcará automáticamente.
                </p>

                <!-- Activa -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label for="is_active" class="ml-2 text-gray-700">
                        <i class="fas fa-check text-green-600"></i> Sucursal activa
                    </label>
                </div>
                <p class="text-sm text-gray-600 ml-6">
                    Solo las sucursales activas pueden ser seleccionadas para operar.
                </p>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('branches.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save"></i> Guardar Sucursal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
