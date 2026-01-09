@extends('layouts.app')

@section('title', 'Recibo de Venta')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Encabezado del recibo -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-receipt"></i> RECIBO DE VENTA
            </h1>
            <p class="text-gray-600">POS Barcode System</p>
            <p class="text-sm text-gray-500">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
            <p class="text-sm text-gray-500 font-mono">Venta #{{ $sale->id }}</p>
        </div>

        <!-- Detalles de la venta -->
        <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-4">Productos</h2>
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Producto</th>
                        <th class="px-4 py-2 text-center">Cantidad</th>
                        <th class="px-4 py-2 text-right">Precio</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold">{{ $item->product->name }}</p>
                                    @if($item->isWeighted())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="fas fa-weight mr-1"></i> Pesable
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 font-mono">
                                    {{ $item->product->internal_code }}
                                    @if($item->product->barcode)
                                        <span class="text-gray-400">| {{ $item->product->barcode }}</span>
                                    @endif
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">
                                @if($item->isWeighted())
                                    <span class="text-blue-600">{{ number_format($item->weight, 3) }} kg</span>
                                @else
                                    {{ $item->quantity }} ud.
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($item->isWeighted())
                                    ${{ number_format($item->product->price_per_kg, 2) }}<span class="text-xs text-gray-500">/kg</span>
                                @else
                                    ${{ number_format($item->product->price, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                ${{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="border-t-2 border-gray-300 pt-4 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Total de Items:</span>
                <span class="font-semibold">{{ $sale->items->count() }}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Total de Productos:</span>
                <span class="font-semibold">{{ $sale->items->sum('quantity') }}</span>
            </div>
            <div class="flex justify-between items-center text-2xl font-bold text-green-600 mt-4">
                <span>TOTAL:</span>
                <span>${{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        <!-- Pie del recibo -->
        <div class="text-center text-gray-500 text-sm border-t border-gray-300 pt-6">
            <p class="mb-2">Gracias por su compra</p>
            <p>Sistema POS Barcode - Laravel {{ app()->version() }}</p>
        </div>

        <!-- Acciones -->
        <div class="flex justify-center space-x-4 mt-8">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="{{ route('home') }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                <i class="fas fa-cash-register"></i> Nueva Venta
            </a>
            <a href="{{ route('sales.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                <i class="fas fa-list"></i> Ver Ventas
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .bg-white, .bg-white * {
        visibility: visible;
    }
    .bg-white {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    nav, footer, .flex.space-x-4 {
        display: none !important;
    }
}
</style>
@endpush
@endsection
