@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-receipt"></i> Historial de Ventas
            </h1>
            <a href="{{ env('APP_URL') }}/" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-plus"></i> Nueva Venta
            </a>
        </div>

        <!-- Filtros de Fecha y Botones de Exportación -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <form method="GET" action="{{ env('APP_URL') }}/sales/" class="space-y-4">
                <!-- Filtros de Fecha -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar"></i> Fecha Desde
                        </label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar"></i> Fecha Hasta
                        </label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                    <div class="flex items-end">
                        <a href="{{ env('APP_URL') }}/sales/" class="w-full bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition text-center">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>

                <!-- Botones de Exportación -->
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-file-excel"></i> Descargar Reportes
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="exportReport('daily')"
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-download"></i> Reporte Diario
                        </button>
                        <button type="button" onclick="exportReport('monthly')"
                                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-download"></i> Reporte Mensual
                        </button>
                        <button type="button" onclick="exportReport('custom')"
                                class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
                            <i class="fas fa-download"></i> Reporte Personalizado
                        </button>
                    </div>
                </div>
            </form>
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
                                    <a href="{{ env('APP_URL') }}/sales/{{ $sale->id }}/receipt/" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
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
                <a href="{{ env('APP_URL') }}" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Realizar primera venta
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function exportReport(type) {
    const dateFrom = document.querySelector('input[name="date_from"]').value;
    const dateTo = document.querySelector('input[name="date_to"]').value;

    let url = '{{ env('APP_URL') }}/sales/export?type=' + type;

    if (type === 'custom') {
        if (dateFrom) url += '&date_from=' + dateFrom;
        if (dateTo) url += '&date_to=' + dateTo;
    } else if (type === 'daily') {
        const today = new Date().toISOString().split('T')[0];
        url += '&date_from=' + today + '&date_to=' + today;
    } else if (type === 'monthly') {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        url += '&date_from=' + firstDay + '&date_to=' + lastDay;
    }

    window.location.href = url;
}
</script>
@endsection
