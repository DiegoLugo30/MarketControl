@extends('layouts.app')

@section('title', 'Pedido ' . $order->code)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- Back link --}}
    <a href="{{ route('admin.orders.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-5 transition">
        <i class="fas fa-arrow-left text-xs"></i> Volver a pedidos
    </a>

    @php
        $statusConfig = [
            'pending'   => ['label' => 'Pendiente',  'bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'dot' => 'bg-amber-500 animate-pulse'],
            'completed' => ['label' => 'Completado', 'bg' => 'bg-green-100', 'text' => 'text-green-800', 'dot' => 'bg-green-500'],
            'cancelled' => ['label' => 'Cancelado',  'bg' => 'bg-red-100',   'text' => 'text-red-700',   'dot' => 'bg-red-500'],
        ];
        $sc = $statusConfig[$order->status] ?? $statusConfig['pending'];

        $orderTotal = $order->items->sum(fn($i) =>
            $i->unit_type === 'weight'
                ? (float)$i->price * (float)$i->quantity
                : (float)$i->price * (float)$i->quantity
        );
    @endphp

    {{-- Flash --}}
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Order header --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $order->code }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                            {{ $sc['label'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-400">
                        <i class="fas fa-clock text-xs mr-1"></i>{{ $order->created_at->format('d/m/Y H:i:s') }}
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    @if($order->status === 'pending')
                        <a href="{{ route('admin.pos') }}?order={{ $order->code }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                                  bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold transition shadow-sm">
                            <i class="fas fa-cash-register"></i> Procesar en POS
                        </a>
                        <form method="POST" action="{{ route('admin.orders.cancel', $order->code) }}"
                              onsubmit="return confirm('¿Cancelar este pedido?')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                                           bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold transition">
                                <i class="fas fa-times-circle"></i> Cancelar pedido
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">

            {{-- Customer info --}}
            <div class="px-6 py-5">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Cliente</h2>
                <p class="font-semibold text-gray-800">{{ $order->user?->name ?? 'Invitado' }}</p>
                @if($order->user?->email)
                    <p class="text-sm text-gray-500 mt-0.5">
                        <i class="fas fa-envelope text-xs mr-1 text-gray-400"></i>{{ $order->user->email }}
                    </p>
                @endif
                @if(!$order->user)
                    <p class="text-xs text-gray-400 mt-1">No registrado</p>
                @endif
            </div>

            {{-- Comment --}}
            <div class="px-6 py-5">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Comentario</h2>
                @if($order->comment)
                    <p class="text-sm text-gray-700 italic leading-relaxed">{{ $order->comment }}</p>
                @else
                    <p class="text-sm text-gray-400">Sin comentario</p>
                @endif
            </div>

            {{-- Summary --}}
            <div class="px-6 py-5">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Resumen</h2>
                <p class="text-sm text-gray-600 mb-1">
                    <span class="font-semibold">{{ $order->items->count() }}</span>
                    producto{{ $order->items->count() !== 1 ? 's' : '' }}
                </p>
                <p class="text-2xl font-extrabold text-gray-900">
                    ${{ number_format($orderTotal, 2) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Total estimado</p>
            </div>
        </div>

        {{-- Items table --}}
        <div class="border-t border-gray-100">
            <div class="px-6 py-4 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-600">
                    <i class="fas fa-list text-xs mr-1"></i> Productos del pedido
                </h2>
            </div>
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-6 py-3 text-left">Producto</th>
                        <th class="px-6 py-3 text-center">Cantidad</th>
                        <th class="px-6 py-3 text-right">Precio unitario</th>
                        <th class="px-6 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($order->items as $item)
                        @php
                            $isWeight = $item->unit_type === 'weight';
                            $subtotal = (float)$item->price * (float)$item->quantity;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800">{{ $item->product_name }}</p>
                                @if($item->product)
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">
                                        {{ $item->product->internal_code }}
                                    </p>
                                @endif
                                @if($isWeight)
                                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-700">
                                        <i class="fas fa-weight-hanging text-[9px]"></i> A granel
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold">
                                @if($isWeight)
                                    <span class="text-indigo-600">
                                        {{ number_format((float)$item->quantity, 3) }} kg
                                    </span>
                                @else
                                    {{ (int)$item->quantity }} ud.
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                ${{ number_format((float)$item->price, 2) }}
                                @if($isWeight)<span class="text-xs text-gray-400">/kg</span>@endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                ${{ number_format($subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-700">Total estimado</td>
                        <td class="px-6 py-4 text-right text-xl font-extrabold text-gray-900">
                            ${{ number_format($orderTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
