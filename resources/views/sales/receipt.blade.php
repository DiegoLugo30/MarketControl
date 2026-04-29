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
            @php
                $paymentIcons = [
                    'efectivo'      => '💵',
                    'debito'        => '💳',
                    'transferencia' => '🏦',
                    'cuenta_dni'    => '📱',
                    'rappi'         => '🛵',
                ];
                $paymentColors = [
                    'efectivo'      => 'bg-green-100 text-green-800',
                    'debito'        => 'bg-blue-100 text-blue-800',
                    'transferencia' => 'bg-purple-100 text-purple-800',
                    'cuenta_dni'    => 'bg-green-100 text-green-800',
                    'rappi'         => 'bg-red-100 text-red-800',
                ];
                $paymentMethod = $sale->payment_method ?? 'efectivo';
            @endphp
            <p class="mt-2">
                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $paymentColors[$paymentMethod] }}">
                    {{ $paymentIcons[$paymentMethod] }} Método de pago: {{ ucfirst($paymentMethod) }}
                </span>
            </p>
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
                        <th class="px-4 py-2 text-right">Descuento</th>
                        <th class="px-4 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($sale->items as $item)
                        <tr>
                            {{-- Product name: always from snapshot; badges for weighted / manual --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold">{{ $item->getDisplayName() }}</p>

                                    @if($item->isWeighted())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="fas fa-weight mr-1"></i> Pesable
                                        </span>
                                    @endif

                                    @if($item->is_custom)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-800">
                                            <i class="fas fa-pencil-alt mr-1"></i> Manual
                                        </span>
                                    @endif
                                </div>

                                {{-- Show catalogue codes only when a product record exists --}}
                                @if(!$item->is_custom && $item->product)
                                    <p class="text-sm text-gray-500 font-mono">
                                        {{ $item->product->internal_code }}
                                        @if($item->product->barcode)
                                            <span class="text-gray-400">| {{ $item->product->barcode }}</span>
                                        @endif
                                    </p>
                                @endif
                            </td>

                            {{-- Quantity or weight --}}
                            <td class="px-4 py-3 text-center font-semibold">
                                @if($item->isWeighted())
                                    <span class="text-blue-600">{{ number_format($item->weight, 3) }} kg</span>
                                @else
                                    {{ $item->quantity }} ud.
                                @endif
                            </td>

                            {{-- Unit / per-kg price --}}
                            <td class="px-4 py-3 text-right">
                                @if($item->isWeighted())
                                    @php
                                        $pricePerKg = $item->product
                                            ? $item->product->price_per_kg
                                            : (float)$item->price;
                                    @endphp
                                    ${{ number_format($pricePerKg, 2) }}<span class="text-xs text-gray-500">/kg</span>
                                @else
                                    ${{ number_format($item->price, 2) }}
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right font-semibold">
                                ${{ number_format($item->subtotal, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-red-600">
                                @if($item->item_discount > 0)
                                    -${{ number_format($item->item_discount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">
                                ${{ number_format($item->total_with_discount, 2) }}
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

            <hr class="my-3">

            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-semibold">${{ number_format($sale->calculateSubtotal(), 2) }}</span>
            </div>

            @if($sale->calculateItemDiscounts() > 0)
                <div class="flex justify-between items-center mb-2 text-red-600">
                    <span>Descuentos en Items:</span>
                    <span class="font-semibold">-${{ number_format($sale->calculateItemDiscounts(), 2) }}</span>
                </div>
            @endif

            @if($sale->discount_amount > 0)
                <div class="flex justify-between items-center mb-2 text-red-600">
                    <span>Descuento General:</span>
                    <span class="font-semibold">-${{ number_format($sale->discount_amount, 2) }}</span>
                </div>
                @if($sale->discount_description)
                    <p class="text-sm text-gray-500 italic mb-2">{{ $sale->discount_description }}</p>
                @endif
            @endif

            <div class="flex justify-between items-center text-2xl font-bold text-green-600 mt-4">
                <span>TOTAL:</span>
                <span>${{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        <!-- Info del pedido online (si aplica) -->
        @if($sale->order_code)
        <div class="border-t border-gray-200 pt-4 mb-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">
                <i class="fas fa-shopping-bag mr-1"></i> Pedido Online
            </h2>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-1 text-sm">
                <p><span class="font-semibold text-gray-600">Código:</span>
                   <span class="font-mono font-bold text-amber-800">{{ $sale->order_code }}</span></p>
                <p><span class="font-semibold text-gray-600">Cliente:</span>
                   {{ $sale->customer_name }}</p>
                @if($sale->customer_email)
                    <p><span class="font-semibold text-gray-600">Email:</span>
                       {{ $sale->customer_email }}</p>
                @endif
                @if($sale->order_comment)
                    <p><span class="font-semibold text-gray-600">Comentario:</span>
                       <span class="italic text-gray-700">{{ $sale->order_comment }}</span></p>
                @endif
            </div>
        </div>
        @endif

        <!-- Pie del recibo -->
        <div class="text-center text-gray-500 text-sm border-t border-gray-300 pt-6">
            <p class="mb-2">Gracias por su compra</p>
            <p>Sistema POS Barcode - Laravel {{ app()->version() }}</p>
        </div>

        <!-- Acciones -->
        <div class="flex flex-wrap justify-center gap-3 mt-8">
            <a href="{{ route('admin.sales.receipt.print', $sale->id) }}" target="_blank"
               class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                <i class="fas fa-ticket-alt"></i> Imprimir ticket cliente
            </a>
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-print"></i> Imprimir vista admin
            </button>
            <a href="{{ route('admin.home') }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                <i class="fas fa-cash-register"></i> Nueva Venta
            </a>
            <a href="{{ route('admin.sales.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
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
