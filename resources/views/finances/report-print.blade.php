<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Financiero - {{ $monthName }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            @page {
                margin: 0.7cm;
                size: A4;
            }

            /* Ajustar para impresión - punto medio */
            .chart-container {
                height: 180px !important;
                margin: 8px 0 !important;
            }

            h1 {
                font-size: 22px !important;
                margin: 6px 0 !important;
            }

            h2 {
                font-size: 18px !important;
                margin: 4px 0 !important;
            }

            h3, h4 {
                font-size: 13px !important;
                margin: 6px 0 !important;
            }

            p {
                margin: 3px 0 !important;
            }

            .summary-cards {
                margin-bottom: 10px !important;
            }

            .summary-card {
                padding: 10px !important;
            }

            table {
                font-size: 10px !important;
            }

            th, td {
                padding: 4px !important;
                line-height: 1.3 !important;
            }

            /* Evitar que elementos se corten entre páginas */
            .summary-cards,
            table {
                page-break-inside: avoid;
            }

            /* Espaciado moderado */
            .mb-4 {
                margin-bottom: 12px !important;
            }

            body {
                font-size: 11px !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .chart-container {
            position: relative;
            height: 220px;
            margin: 12px 0;
        }
    </style>
</head>
<body class="bg-white p-6">
    <!-- Botones de acción (no se imprimen) -->
    <div class="no-print fixed top-4 right-4 flex gap-3 z-50">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 shadow-lg">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
        <a href="{{ env('APP_URL') }}/finances?month={{ $month }}&year={{ $year }}"
           class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 shadow-lg">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Font Awesome (solo para los botones) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" class="no-print">

    <!-- Encabezado del Reporte -->
    <div class="bg-blue-900 text-white p-5 rounded-lg mb-4">
        <h1 class="text-3xl font-bold text-center mb-1">📊 REPORTE FINANCIERO</h1>
        <h2 class="text-xl font-semibold text-center mb-1">{{ strtoupper($monthName) }}</h2>
        <p class="text-center text-blue-200 text-sm">Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Resumen Ejecutivo -->
    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-4 summary-cards">
        <h3 class="text-lg font-bold text-blue-900 mb-3">Resumen Ejecutivo</h3>
        <div class="grid grid-cols-4 gap-3">
            <div class="bg-white p-3 rounded border border-blue-200 summary-card">
                <p class="text-xs text-gray-600 mb-1">Total de Ventas</p>
                <p class="text-xl font-bold text-blue-900">{{ $salesCount }}</p>
            </div>
            <div class="bg-white p-3 rounded border border-green-200 summary-card">
                <p class="text-xs text-gray-600 mb-1">Ingresos</p>
                <p class="text-xl font-bold text-green-600">${{ number_format($income, 2) }}</p>
            </div>
            <div class="bg-white p-3 rounded border border-red-200 summary-card">
                <p class="text-xs text-gray-600 mb-1">Gastos</p>
                <p class="text-xl font-bold text-red-600">${{ number_format($totalExpenses, 2) }}</p>
            </div>
            <div class="bg-white p-3 rounded border border-{{ $result >= 0 ? 'purple' : 'orange' }}-200 summary-card">
                <p class="text-xs text-gray-600 mb-1">Resultado</p>
                <p class="text-xl font-bold text-{{ $result >= 0 ? 'purple' : 'orange' }}-600">
                    ${{ number_format($result, 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="mb-4">
        <h3 class="text-lg font-bold text-gray-900 mb-3 border-b-2 border-blue-600 pb-1">
            📈 Gráficos Financieros
        </h3>

        <div class="grid grid-cols-2 gap-4">
            <!-- Gráfico de Barras: Ingresos vs Gastos -->
            <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-3">
                <h4 class="text-sm font-semibold text-gray-800 mb-2 text-center">
                    Ingresos vs Gastos
                </h4>
                <div class="chart-container">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Torta: Gastos por Categoría -->
            <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-3">
                <h4 class="text-sm font-semibold text-gray-800 mb-2 text-center">
                    Gastos por Categoría
                </h4>
                @if($expensesByCategory->count() > 0)
                    <div class="chart-container">
                        <canvas id="expensesCategoryChart"></canvas>
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 text-gray-400">
                        <p class="text-sm">No hay gastos</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detalle de Gastos -->
    <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 border-b-2 border-blue-600 pb-1">
            📋 Detalle de Gastos
        </h3>

        @if($expensesList->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-xs">
                    <thead>
                        <tr class="bg-blue-900 text-white">
                            <th class="border border-gray-300 px-3 py-2 text-left">Fecha</th>
                            <th class="border border-gray-300 px-3 py-2 text-left">Categoría</th>
                            <th class="border border-gray-300 px-3 py-2 text-left">Descripción</th>
                            <th class="border border-gray-300 px-3 py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expensesList as $index => $expense)
                            <tr class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="border border-gray-300 px-3 py-2">
                                    {{ $expense->date->format('d/m/Y') }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                        {{ $expense->category }}
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-gray-700">
                                    {{ $expense->description ?? '-' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right font-bold text-red-600">
                                    ${{ number_format($expense->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-yellow-100 font-bold">
                            <td colspan="3" class="border border-gray-300 px-3 py-2 text-right">
                                TOTAL GASTOS:
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-red-600">
                                ${{ number_format($totalExpenses, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <p class="text-sm">No hay gastos registrados en este mes</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="mt-6 pt-3 border-t-2 border-blue-900 text-center text-gray-600 text-sm">
        <p class="font-semibold">Sistema de Gestión MarketControl</p>
    </div>

    <!-- Scripts para los gráficos -->
    <script>
        // Esperar a que cargue todo
        window.addEventListener('load', function() {
            // Gráfico de Ingresos vs Gastos
            const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
            if (incomeExpenseCtx) {
                new Chart(incomeExpenseCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Ingresos', 'Gastos'],
                        datasets: [{
                            label: 'Monto ($)',
                            data: [{{ $income }}, {{ $totalExpenses }}],
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
                                bodyFont: {
                                    size: 11
                                },
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.parsed.y.toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de Gastos por Categoría
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
                                'rgba(14, 165, 233, 0.8)',
                                'rgba(249, 115, 22, 0.8)',
                            ],
                            borderColor: [
                                'rgba(59, 130, 246, 1)',
                                'rgba(239, 68, 68, 1)',
                                'rgba(34, 197, 94, 1)',
                                'rgba(251, 191, 36, 1)',
                                'rgba(168, 85, 247, 1)',
                                'rgba(236, 72, 153, 1)',
                                'rgba(14, 165, 233, 1)',
                                'rgba(249, 115, 22, 1)',
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
                                    boxWidth: 10,
                                    padding: 8,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            tooltip: {
                                bodyFont: {
                                    size: 11
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': $' + context.parsed.toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        }
                    }
                });
            }
            @endif
        });
    </script>
</body>
</html>
