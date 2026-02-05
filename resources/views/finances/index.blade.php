@extends('layouts.app')

@section('title', 'Finanzas - Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-chart-line"></i> Dashboard Financiero
                </h1>
                @if($showingAllBranches)
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-globe"></i> Mostrando datos consolidados de todas las sucursales
                    </p>
                @elseif($viewingBranch && $activeBranch && $viewingBranch->id !== $activeBranch->id)
                    <p class="text-sm text-orange-600 mt-1">
                        <i class="fas fa-building"></i> Visualizando: {{ $viewingBranch->name }}
                    </p>
                @else
                    <p class="text-sm text-gray-600 mt-1">
                        <i class="fas fa-building"></i> Sucursal: {{ $activeBranch ? $activeBranch->name : 'N/A' }}
                    </p>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ env('APP_URL') }}/finances/expenses" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-money-bill-wave"></i> Gestionar Gastos
                </a>
                <button onclick="downloadReport()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-file-pdf"></i> Generar Reporte PDF
                </button>
            </div>
        </div>

        <!-- Filtro de Mes/Año/Sucursal -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <form method="GET" action="{{ env('APP_URL') }}/finances" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar"></i> Mes
                    </label>
                    <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt"></i> Año
                    </label>
                    <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                @if($activeBranch && $activeBranch->is_main)
                    <!-- Filtro de Sucursal (solo visible desde sucursal principal) -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building"></i> Sucursal
                            <span class="text-xs text-gray-500">(Solo desde Principal)</span>
                        </label>
                        <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-blue-50">
                            <option value="">{{ $activeBranch->name }} (Activa)</option>
                            <option value="all" {{ $filterBranchId === 'all' ? 'selected' : '' }} class="font-bold">
                                🌐 Todas las Sucursales (Consolidado)
                            </option>
                            <optgroup label="────────────────"></optgroup>
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}" {{ $filterBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}{{ $branch->is_main ? ' ⭐' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </form>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Total Ventas -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Total Ventas</p>
                        <h3 class="text-3xl font-bold mt-1">{{ $salesCount }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-shopping-cart text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Ingresos -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Ingresos</p>
                        <h3 class="text-3xl font-bold mt-1">${{ number_format($income, 2) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-arrow-up text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Gastos -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">Gastos</p>
                        <h3 class="text-3xl font-bold mt-1">${{ number_format($expenses, 2) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-arrow-down text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Resultado -->
            <div class="bg-gradient-to-br from-{{ $result >= 0 ? 'purple' : 'orange' }}-500 to-{{ $result >= 0 ? 'purple' : 'orange' }}-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-opacity-90 text-sm">Ganancias</p>
                        <h3 class="text-3xl font-bold mt-1">${{ number_format($result, 2) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-{{ $result >= 0 ? 'check-circle' : 'exclamation-triangle' }} text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico de Barras: Ingresos vs Gastos -->
            <div class="bg-white border rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar text-blue-600"></i> Ingresos vs Gastos
                </h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Torta: Gastos por Categoría -->
            <div class="bg-white border rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie text-purple-600"></i> Gastos por Categoría
                </h3>
                @if($expensesByCategory->count() > 0)
                    <div style="position: relative; height: 300px;">
                        <canvas id="expensesCategoryChart"></canvas>
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 text-gray-400">
                        <div class="text-center">
                            <i class="fas fa-inbox text-5xl mb-3"></i>
                            <p>No hay gastos registrados</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top 5 Productos -->
        <div class="bg-white border rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-trophy text-yellow-500"></i> Top 5 Productos Más Vendidos
            </h3>
            @if($topProducts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Posición</th>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-center">Cantidad Vendida</th>
                                <th class="px-4 py-3 text-center">Progreso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @php $maxQuantity = $topProducts->first()->total_quantity; @endphp
                            @foreach($topProducts as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                            {{ $index === 0 ? 'bg-yellow-400 text-white' : ($index === 1 ? 'bg-gray-400 text-white' : ($index === 2 ? 'bg-orange-400 text-white' : 'bg-gray-200')) }}
                                            font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $item->product->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                            {{ number_format($item->total_quantity) }} unidades
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="bg-blue-600 h-4 rounded-full" style="width: {{ ($item->total_quantity / $maxQuantity) * 100 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-box-open text-6xl mb-4 opacity-50"></i>
                    <p class="text-xl">No hay productos vendidos en este mes</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Gráfico de Ingresos vs Gastos (Barras)
const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
if (incomeExpenseCtx) {
    new Chart(incomeExpenseCtx, {
        type: 'bar',
        data: {
            labels: ['Ingresos', 'Gastos'],
            datasets: [{
                label: 'Monto ($)',
                data: [{{ $chartData['income'] }}, {{ $chartData['expenses'] }}],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(239, 68, 68, 1)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            layout: {
                padding: 10
            }
        }
    });
}

// Gráfico de Gastos por Categoría (Torta)
const expensesCategoryCtx = document.getElementById('expensesCategoryChart');
@if($expensesByCategory->count() > 0)
if (expensesCategoryCtx) {
    new Chart(expensesCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($expensesByCategory->pluck('category')) !!},
            datasets: [{
                label: 'Gastos',
                data: {!! json_encode($expensesByCategory->pluck('total')) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(34, 197, 94, 1)',
                    'rgba(251, 191, 36, 1)',
                    'rgba(168, 85, 247, 1)',
                    'rgba(236, 72, 153, 1)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': $' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            layout: {
                padding: 10
            }
        }
    });
}
@endif

// Función para descargar reporte PDF
function downloadReport() {
    const month = {{ $month }};
    const year = {{ $year }};
    const branchId = '{{ $filterBranchId ?? '' }}';

    let url = '{{ env('APP_URL') }}/finances/export-report?month=' + month + '&year=' + year;
    if (branchId) {
        url += '&branch_id=' + branchId;
    }

    // Abrir en nueva pestaña para que el usuario pueda imprimir/guardar como PDF
    window.open(url, '_blank');
}
</script>
@endsection
