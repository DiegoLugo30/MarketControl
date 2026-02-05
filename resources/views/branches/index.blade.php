@extends('layouts.app')

@section('title', 'Gestión de Sucursales')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-building"></i> Gestión de Sucursales
            </h1>
            <a href="{{ route('branches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus"></i> Nueva Sucursal
            </a>
        </div>

        <!-- Mensajes de éxito/error -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Tabla de sucursales -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Código</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Nombre</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Dirección</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Teléfono</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-700">Principal</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-700">Estado</th>
                        <th class="px-4 py-2 text-center font-semibold text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                    {{ $branch->code }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    @if($branch->is_main)
                                        <i class="fas fa-star text-yellow-500 mr-2" title="Principal"></i>
                                    @endif
                                    <span class="font-semibold">{{ $branch->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $branch->address ?? 'No especificada' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $branch->phone ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($branch->is_main)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                        <i class="fas fa-star"></i> Principal
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($branch->is_active)
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                        <i class="fas fa-check"></i> Activa
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                        <i class="fas fa-times"></i> Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('branches.edit', $branch) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$branch->is_main)
                                        <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta sucursal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-300" title="No se puede eliminar la sucursal principal">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-building text-4xl mb-2 opacity-50"></i>
                                <p>No hay sucursales registradas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Información adicional -->
        <div class="mt-6 text-sm text-gray-600">
            <p><i class="fas fa-info-circle"></i> <strong>Nota:</strong> La sucursal principal no puede ser eliminada. Para eliminar una sucursal, primero asegúrate de que no tenga ventas, gastos o stock asociado.</p>
        </div>
    </div>
</div>
@endsection
