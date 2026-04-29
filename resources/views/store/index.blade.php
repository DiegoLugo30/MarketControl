@extends('store.layout')

@section('title', request('q') ? 'Buscar: ' . request('q') : 'Catálogo')
@section('meta_description', 'Explorá nuestro catálogo y hacé tu pedido por WhatsApp.')

@section('content')

{{-- ── Hero banner ──────────────────────────────────────────────────────────── --}}
@unless(request('q'))
<div class="bg-gradient-to-br from-brand-500 via-brand-600 to-emerald-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="max-w-xl">
            <span class="inline-block bg-white/20 backdrop-blur-sm text-white text-xs font-semibold
                         px-3 py-1 rounded-full mb-4 tracking-wide uppercase">
                Tienda online
            </span>
            <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-3">
                Hacé tu pedido<br>de forma fácil y rápida
            </h1>
            <p class="text-brand-100 text-base leading-relaxed">
                Elegí los productos, completá tu carrito y envianos tu pedido
                directamente por WhatsApp. ¡Sin registros, sin complicaciones!
            </p>
            <div class="flex items-center gap-3 mt-6">
                <div class="flex items-center gap-2 bg-white/15 rounded-full px-4 py-2 text-sm font-medium">
                    <i class="fab fa-whatsapp text-lg"></i>
                    Pedido por WhatsApp
                </div>
                <div class="flex items-center gap-2 bg-white/15 rounded-full px-4 py-2 text-sm font-medium">
                    <i class="fas fa-bolt text-yellow-300"></i>
                    Sin registro
                </div>
            </div>
        </div>
    </div>
</div>
@endunless


{{-- ── Main content ─────────────────────────────────────────────────────────── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Results bar --}}
    <div class="flex items-center justify-between mb-6 gap-4">
        <div>
            @if(request('q'))
                <h2 class="text-xl font-bold text-gray-900">
                    Resultados para
                    <span class="text-brand-600">"{{ request('q') }}"</span>
                </h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ $products->total() }} producto{{ $products->total() !== 1 ? 's' : '' }} encontrado{{ $products->total() !== 1 ? 's' : '' }}
                </p>
            @else
                <h2 class="text-xl font-bold text-gray-900">Todos los productos</h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ $products->total() }} producto{{ $products->total() !== 1 ? 's' : '' }} disponible{{ $products->total() !== 1 ? 's' : '' }}
                </p>
            @endif
        </div>

        @if(request('q'))
            <a href="{{ route('store.index') }}"
               class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 font-medium transition-colors shrink-0">
                <i class="fas fa-times text-xs"></i>
                Limpiar
            </a>
        @endif
    </div>

    {{-- ── Product grid ──────────────────────────────────────────────────────── --}}
    @if($products->isEmpty())

        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-box-open text-gray-300 text-4xl"></i>
            </div>
            <h3 class="text-gray-700 font-bold text-xl mb-2">
                @if(request('q'))
                    No encontramos productos
                @else
                    No hay productos disponibles
                @endif
            </h3>
            <p class="text-gray-400 text-sm max-w-xs leading-relaxed">
                @if(request('q'))
                    Probá con otras palabras o
                    <a href="{{ route('store.index') }}" class="text-brand-600 hover:underline font-medium">
                        ver todos los productos
                    </a>.
                @else
                    Estamos actualizando nuestro catálogo. ¡Volvé pronto!
                @endif
            </p>
            @if(request('q'))
                <a href="{{ route('store.index') }}"
                   class="mt-6 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-full text-sm font-semibold transition-colors">
                    Ver catálogo completo
                </a>
            @endif
        </div>

    @else

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($products as $product)
                @include('store.components.product-card', ['product' => $product])
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-10 flex justify-center">
                {{-- Custom minimal pagination --}}
                <nav class="flex items-center gap-1" aria-label="Paginación">
                    {{-- Previous --}}
                    @if($products->onFirstPage())
                        <span class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-300 cursor-not-allowed">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}"
                           class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </a>
                    @endif

                    {{-- Page numbers --}}
                    @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if($page == $products->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-brand-500 text-white text-sm font-semibold">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 text-sm font-medium transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}"
                           class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </a>
                    @else
                        <span class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-300 cursor-not-allowed">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </span>
                    @endif
                </nav>
            </div>
        @endif

    @endif
</div>

@endsection
