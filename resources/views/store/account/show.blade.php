@extends('store.layout')

@section('title', 'Pedido #' . $order->code)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('store.account.orders') }}"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Pedido <span class="font-mono">#{{ $order->code }}</span>
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @php
            [$badgeClass, $statusLabel] = match($order->status) {
                'completed' => ['bg-green-100 text-green-700',   'Completado'],
                'cancelled' => ['bg-red-100 text-red-700',       'Cancelado'],
                default     => ['bg-yellow-100 text-yellow-700', 'Pendiente'],
            };
        @endphp
        <span class="text-sm font-semibold px-3 py-1.5 rounded-full {{ $badgeClass }} shrink-0 mt-1">
            {{ $statusLabel }}
        </span>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Productos</h2>
        </div>

        <div class="divide-y divide-gray-50">
            @foreach($order->items as $item)
                <div class="flex items-center gap-4 px-5 py-4">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                        <i class="{{ $item->unit_type === 'weight' ? 'fas fa-weight-hanging text-indigo-300' : 'fas fa-box text-brand-400' }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $item->product_name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if($item->unit_type === 'weight')
                                {{ number_format($item->quantity, 3) }} kg
                                <span class="mx-1">·</span>
                                {{ config('store.currency_symbol') }}{{ number_format($item->price, 2, ',', '.') }}/kg
                            @else
                                x{{ $item->quantity }}
                                <span class="mx-1">·</span>
                                {{ config('store.currency_symbol') }}{{ number_format($item->price, 2, ',', '.') }} c/u
                            @endif
                        </p>
                    </div>
                    <p class="text-sm font-bold text-gray-900 shrink-0">
                        {{ config('store.currency_symbol') }}{{ number_format($item->price * $item->quantity, 2, ',', '.') }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-b-xl">
            <span class="text-sm font-semibold text-gray-600">Total</span>
            <span class="text-xl font-extrabold text-gray-900">
                {{ config('store.currency_symbol') }}{{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 2, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Comment --}}
    @if($order->comment)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4">
            <h2 class="font-semibold text-gray-800 mb-2">
                <i class="fas fa-comment-alt text-gray-400 text-sm mr-1.5"></i>
                Comentario del pedido
            </h2>
            <p class="text-sm text-gray-500 italic">"{{ $order->comment }}"</p>
        </div>
    @endif

</div>
@endsection
