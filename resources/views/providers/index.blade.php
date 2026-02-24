@extends('layouts.app')

@section('title', 'Gestión de Proveedores')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-truck-moving"></i> Gestión de Proveedores
            </h1>
            <a href="{{ route('providers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </a>
        </div>

        <!-- Tabla de sucursales -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">ID</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Nombre</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Descripcion</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Teléfono</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-700">Mail</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-700">Dia de entrega</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <span class="font-semibold">{{ $provider->id }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <span class="font-semibold">{{ $provider->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $provider->description ?? 'No especificada' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $provider->phone ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $provider->mail ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $provider->delivery_day ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ url('/providers/' . $provider->id . '/edit') }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ url('/providers/' . $provider->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-building text-4xl mb-2 opacity-50"></i>
                                <p>No hay proveedores registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
