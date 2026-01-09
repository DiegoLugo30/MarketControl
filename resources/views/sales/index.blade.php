@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-receipt"></i> Historial de Ventas
            </h1>
            <a href="{{ route('home') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus"></i> Nueva Venta
            </a>
        </div>

        @if($sales->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Fecha y Hora</th>
                            <th class="px-4 py-3 text-center">Items</th>
                            <th class="px-4 py-3 text-center">Productos</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-semibold">
                                    #{{ $sale->id }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $sale->created_at->format('d/m/Y') }}</p>
                                    <p class="text-sm text-gray-500">{{ $sale->created_at->format('H:i:s') }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                        {{ $sale->items->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-semibold">
                                        {{ $sale->items->sum('quantity') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-green-600 text-lg">
                                    ${{ number_format($sale->total, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('sales.receipt', $sale->id) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                                        <i class="fas fa-eye"></i> Ver Recibo
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right">TOTAL VENTAS:</td>
                            <td class="px-4 py-3 text-right text-green-600 text-xl">
                                ${{ number_format($sales->sum('total'), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6">
                {{ $sales->links() }}
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-receipt text-6xl mb-4 opacity-50"></i>
                <p class="text-xl">No hay ventas registradas</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Realizar primera venta
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
