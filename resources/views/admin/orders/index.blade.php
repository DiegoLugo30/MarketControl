@extends('layouts.app')

@section('title', 'Pedidos de la Tienda')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-shopping-bag text-indigo-500 mr-2"></i>Pedidos de la Tienda
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Pedidos creados desde el catálogo online</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Código</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Estado</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Cliente</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Ítems</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Fecha</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- Code --}}
                        <td class="px-5 py-4">
                            <span class="font-mono font-bold text-gray-800 text-sm">{{ $order->code }}</span>
                        </td>

                        {{-- Status badge --}}
                        <td class="px-5 py-4">
                            @if($order->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pendiente
                                </span>
                            @elseif($order->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check text-[9px]"></i> Completado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    <i class="fas fa-times text-[9px]"></i> Cancelado
                                </span>
                            @endif
                        </td>

                        {{-- Customer --}}
                        <td class="px-5 py-4 text-gray-700">
                            {{ $order->user?->name ?? 'Invitado' }}
                            @if($order->user?->email)
                                <p class="text-xs text-gray-400">{{ $order->user->email }}</p>
                            @endif
                        </td>

                        {{-- Item count --}}
                        <td class="px-5 py-4 text-gray-600">
                            {{ $order->items_count }} producto{{ $order->items_count !== 1 ? 's' : '' }}
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 text-gray-500">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">

                                @if($order->status === 'pending')
                                    <a href="{{ route('admin.pos') }}?order={{ $order->code }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg
                                              bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold transition">
                                        <i class="fas fa-cash-register text-xs"></i> Procesar
                                    </a>
                                @endif

                                <a href="{{ route('admin.orders.show', $order->code) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg
                                          bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition">
                                    <i class="fas fa-eye text-xs"></i> Ver
                                </a>

                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('admin.orders.cancel', $order->code) }}"
                                          onsubmit="return confirm('¿Cancelar el pedido {{ $order->code }}?')">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg
                                                       bg-red-100 hover:bg-red-200 text-red-700 text-xs font-semibold transition">
                                            <i class="fas fa-times text-xs"></i> Cancelar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                            <i class="fas fa-shopping-bag text-4xl mb-3 opacity-30 block"></i>
                            No hay pedidos registrados todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
