@extends('store.layout')

@section('title', 'Mis pedidos')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Page header --}}
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('store.index') }}"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mis pedidos</h1>
            <p class="text-sm text-gray-400 mt-0.5">Historial de tus pedidos realizados</p>
        </div>
    </div>

    @if($orders->isEmpty())

        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-clipboard-list text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-gray-700 font-bold text-lg mb-2">No tenés pedidos todavía</h3>
            <p class="text-gray-400 text-sm max-w-xs leading-relaxed mb-6">
                Cuando hagas tu primer pedido, lo vas a ver acá.
            </p>
            <a href="{{ route('store.index') }}"
               class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-full text-sm font-semibold transition-colors">
                Ver catálogo
            </a>
        </div>

    @else

        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $total = $order->items->sum(fn($i) => $i->price * $i->quantity);
                    [$badgeClass, $statusLabel] = match($order->status) {
                        'completed' => ['bg-green-100 text-green-700',   'Completado'],
                        'cancelled' => ['bg-red-100 text-red-700',       'Cancelado'],
                        default     => ['bg-yellow-100 text-yellow-700', 'Pendiente'],
                    };
                @endphp

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5">
                    <div class="flex items-start justify-between gap-4">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-mono text-sm font-bold text-gray-700">#{{ $order->code }}</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                {{ $order->created_at->format('d/m/Y') }}
                                <span class="text-gray-300 mx-1">·</span>
                                {{ $order->created_at->format('H:i') }}
                            </p>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'producto' : 'productos' }}
                                @if($order->comment)
                                    <span class="text-gray-300 mx-1">·</span>
                                    <span class="italic text-gray-400">"{{ \Illuminate\Support\Str::limit($order->comment, 40) }}"</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <span class="text-xl font-extrabold text-gray-900">
                                {{ config('store.currency_symbol') }}{{ number_format($total, 2, ',', '.') }}
                            </span>
                            <a href="{{ route('store.account.orders.show', $order->code) }}"
                               class="text-xs font-semibold text-brand-600 hover:text-brand-700 transition
                                      px-3 py-1.5 bg-brand-50 hover:bg-brand-100 rounded-lg">
                                Ver detalle
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-8 flex justify-center">
                @include('store.components.pagination', ['paginator' => $orders])
            </div>
        @endif

    @endif

</div>
@endsection
