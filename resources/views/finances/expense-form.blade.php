@extends('layouts.app')

@section('title', isset($expense) ? 'Editar Gasto' : 'Nuevo Gasto')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-{{ isset($expense) ? 'edit' : 'plus-circle' }}"></i>
                {{ isset($expense) ? 'Editar Gasto' : 'Nuevo Gasto' }}
            </h1>
            <a href="{{ env('APP_URL') }}/finances/expenses" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- Errores de validación -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ isset($expense) ? env('APP_URL') . '/finances/expenses/' . $expense->id : env('APP_URL') . '/finances/expenses' }}">
            @csrf
            @if(isset($expense))
                @method('PUT')
            @endif

            <div class="space-y-6">
                <!-- Fecha -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar"></i> Fecha del Gasto *
                    </label>
                    <input type="date"
                           id="date"
                           name="date"
                           value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : date('Y-m-d')) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag"></i> Categoría *
                    </label>
                    <input type="text"
                           id="category"
                           name="category"
                           value="{{ old('category', isset($expense) ? $expense->category : '') }}"
                           list="categories"
                           required
                           placeholder="Ej: Servicios, Insumos, Alquiler, Sueldos..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <datalist id="categories">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                        <option value="Servicios">
                        <option value="Insumos">
                        <option value="Alquiler">
                        <option value="Sueldos">
                        <option value="Mantenimiento">
                        <option value="Marketing">
                        <option value="Transporte">
                        <option value="Otros">
                    </datalist>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monto -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign"></i> Monto *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500 text-lg">$</span>
                        <input type="number"
                               id="amount"
                               name="amount"
                               value="{{ old('amount', isset($expense) ? $expense->amount : '') }}"
                               step="0.01"
                               min="0.01"
                               required
                               placeholder="0.00"
                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left"></i> Descripción (Opcional)
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              placeholder="Detalles adicionales del gasto..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', isset($expense) ? $expense->description : '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 mt-8">
                <button type="submit" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                    <i class="fas fa-save"></i> {{ isset($expense) ? 'Actualizar Gasto' : 'Guardar Gasto' }}
                </button>
                <a href="{{ env('APP_URL') }}/finances/expenses" class="flex-1 bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
