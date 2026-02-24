@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-edit"></i> Editar Proveedor
            </h1>
        </div>

        <form action="{{ route('providers.update', $provider) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Nombre -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-tag"></i> Nombre *
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $provider->name) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripcion -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-map-marker-alt"></i> Descripcion
                </label>
                <textarea
                    name="description"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                >{{ old('description', $provider->description) }}</textarea>
                @error('description')
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
                    value="{{ old('phone', $provider->phone) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
                >
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input
                        type="text"
                        name="email"
                        value="{{ old('mail', $provider->mail) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                >
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Entrega -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-calendar-alt"></i> Dia de entrega
                </label>
                <input
                        type="text"
                        name="delivery_day"
                        value="{{ old('delivery_day') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('delivery_day') border-red-500 @enderror"
                        placeholder="Ej: lunes, martes, etc..."
                >
                @error('delivery_day')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('providers.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save"></i> Actualizar proveedor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
