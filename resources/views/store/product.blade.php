@extends('store.layout')

@section('title', $product->name)
@section('meta_description', $product->description ?: 'Ver detalles del producto ' . $product->name)

@section('content')

@php
    $totalStock   = $product->is_weighted ? null : $product->getTotalStockAttribute();
    $isOutOfStock = !$product->is_weighted && $totalStock !== null && $totalStock <= 0;
    $isLowStock   = !$product->is_weighted && $totalStock !== null && $totalStock > 0 && $totalStock <= 5;
    $imageUrl     = $product->image_path ? asset('storage/' . $product->image_path) : null;

    $cartData = json_encode([
        'id'        => $product->id,
        'name'      => $product->name,
        'price'     => (float) $product->price,
        'stock'     => $totalStock,
        'unit_type' => 'unit',
        'image'     => $imageUrl,
    ]);

    $weightData = json_encode([
        'id'        => $product->id,
        'name'      => $product->name,
        'price'     => (float) ($product->price_per_kg ?? 0),
        'stock'     => null,
        'unit_type' => 'weight',
        'image'     => $imageUrl,
    ]);
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('store.index') }}" class="hover:text-brand-600 transition-colors font-medium">
            <i class="fas fa-store mr-1 text-xs"></i>Tienda
        </a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-700 font-medium line-clamp-1">{{ $product->name }}</span>
    </nav>

    {{-- Product card --}}
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="grid md:grid-cols-5 gap-0">

            {{-- ── Image panel (2/5) ──────────────────────────────────────── --}}
            <div class="md:col-span-2 relative
                        @if(!$imageUrl)
                            @if($product->is_weighted) bg-gradient-to-br from-blue-50 to-indigo-100
                            @elseif($isLowStock) bg-gradient-to-br from-amber-50 to-yellow-100
                            @else bg-gradient-to-br from-brand-50 to-emerald-100 @endif
                        @endif
                        flex flex-col items-center justify-center
                        min-h-[280px] md:min-h-full overflow-hidden">

                @if($imageUrl)
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $product->name }}"
                        class="absolute inset-0 w-full h-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                    {{-- Badge over image --}}
                    <div class="absolute bottom-5 inset-x-0 flex justify-center">
                        @if($product->is_weighted)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                         text-sm font-semibold bg-blue-100/90 text-blue-700 shadow-sm backdrop-blur-sm">
                                <i class="fas fa-weight text-xs"></i> Producto a granel
                            </span>
                        @elseif($isOutOfStock)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                         text-sm font-semibold bg-red-100/90 text-red-700 shadow-sm backdrop-blur-sm">
                                <i class="fas fa-times-circle text-xs"></i> Sin stock
                            </span>
                        @elseif($isLowStock)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                         text-sm font-semibold bg-amber-100/90 text-amber-700 shadow-sm backdrop-blur-sm">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Últimas {{ $totalStock }} unidades
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                         text-sm font-semibold bg-brand-100/90 text-brand-700 shadow-sm backdrop-blur-sm">
                                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                En stock — {{ $totalStock }} unidades
                            </span>
                        @endif
                    </div>
                @else
                    <i class="fas fa-{{ $product->is_weighted ? 'weight-hanging' : 'box' }}
                              text-8xl md:text-9xl
                              @if($product->is_weighted) text-indigo-200
                              @elseif($isOutOfStock) text-red-200
                              @elseif($isLowStock) text-amber-200
                              @else text-brand-200 @endif
                              mb-4"></i>
                    {{-- Badge --}}
                    @if($product->is_weighted)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                     text-sm font-semibold bg-blue-100 text-blue-700 shadow-sm">
                            <i class="fas fa-weight text-xs"></i> Producto a granel
                        </span>
                    @elseif($isOutOfStock)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                     text-sm font-semibold bg-red-100 text-red-700 shadow-sm">
                            <i class="fas fa-times-circle text-xs"></i> Sin stock
                        </span>
                    @elseif($isLowStock)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                     text-sm font-semibold bg-amber-100 text-amber-700 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Últimas {{ $totalStock }} unidades
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                     text-sm font-semibold bg-brand-100 text-brand-700 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            En stock — {{ $totalStock }} unidades
                        </span>
                    @endif
                @endif
            </div>

            {{-- ── Info panel (3/5) ───────────────────────────────────────── --}}
            <div class="md:col-span-3 p-6 md:p-10 flex flex-col justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-3">
                        {{ $product->name }}
                    </h1>

                    @if($product->description)
                        <p class="text-gray-500 leading-relaxed text-base mb-6">
                            {{ $product->description }}
                        </p>
                    @endif

                    {{-- Price block --}}
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 inline-block">
                        @if($product->is_weighted)
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-extrabold text-gray-900">
                                    ${{ number_format($product->price_per_kg, 2) }}
                                </span>
                                <span class="text-lg text-gray-400 font-medium">/ kg</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                El precio final varía según el peso exacto
                            </p>
                        @else
                            <span class="text-4xl font-extrabold text-gray-900">
                                ${{ number_format($product->price, 2) }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">Precio por unidad</p>
                        @endif
                    </div>
                </div>

                {{-- CTA --}}
                <div class="space-y-3">

                    @if($product->is_weighted)
                        {{-- ── Weight product: unit selector (g/kg) + live price + add to cart ── --}}
                        <div x-data="{ qty: '', unit: 'g', added: false, product: {{ $weightData }} }">

                            {{-- Label + unit selector --}}
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    Cantidad
                                </label>
                                <select
                                    x-model="unit"
                                    @change="qty = ''"
                                    class="border-2 border-gray-200 rounded-lg px-2.5 py-1 text-sm font-semibold
                                           text-gray-600 bg-white focus:outline-none focus:border-indigo-400
                                           focus:ring-2 focus:ring-indigo-100 transition-all cursor-pointer"
                                >
                                    <option value="g">Gramos (g)</option>
                                    <option value="kg">Kilogramos (kg)</option>
                                </select>
                            </div>

                            <div class="relative mb-3">
                                <input
                                    type="number"
                                    x-model="qty"
                                    :placeholder="unit === 'g' ? 'Ej: 100, 250, 500' : 'Ej: 0.5, 1.25, 2'"
                                    :step="unit === 'g' ? '1' : '0.001'"
                                    :min="unit === 'g' ? '1' : '0.001'"
                                    :class="qty && parseFloat(qty) > 0
                                        ? 'border-indigo-400 ring-2 ring-indigo-100'
                                        : 'border-gray-200'"
                                    class="w-full pl-4 pr-16 py-3.5 border-2 rounded-xl text-lg font-semibold
                                           focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100
                                           transition-all"
                                    @keydown.enter.prevent="
                                        const value = parseFloat(qty);
                                        const kg = unit === 'g' ? value / 1000 : value;
                                        if (kg > 0) {
                                            $store.cart.addWeightItem({...product, display_unit: unit}, kg);
                                            added = true; qty = '';
                                            setTimeout(() => added = false, 2000)
                                        }
                                    "
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold pointer-events-none text-sm"
                                      x-text="unit"></span>
                            </div>

                            {{-- Validation hints --}}
                            <p
                                x-show="qty !== '' && (parseFloat(qty) <= 0 || isNaN(parseFloat(qty)))"
                                x-cloak
                                class="text-sm text-red-500 mb-3 flex items-center gap-1.5"
                            >
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                Ingresá una cantidad mayor a 0
                            </p>
                            <p
                                x-show="unit === 'g' && qty !== '' && parseFloat(qty) > 0 && qty % 1 !== 0"
                                x-cloak
                                class="text-sm text-amber-500 mb-3 flex items-center gap-1.5"
                            >
                                <i class="fas fa-info-circle text-xs"></i>
                                Para gramos usá números enteros
                            </p>

                            {{-- Live price display --}}
                            <div
                                x-show="parseFloat(qty) > 0"
                                x-cloak
                                class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-4"
                            >
                                <p class="text-sm text-indigo-600 font-medium mb-1">Total estimado</p>
                                <p class="text-3xl font-extrabold text-gray-900"
                                   x-text="$store.cart.fmt(product.price * (unit === 'g' ? parseFloat(qty) / 1000 : parseFloat(qty)) || 0)"></p>
                                <p class="text-xs text-gray-400 mt-1"
                                   x-text="(unit === 'g' ? qty + ' g' : qty + ' kg') + ' × ' + $store.cart.fmt(product.price) + '/kg'"></p>
                            </div>

                            {{-- Add to cart button --}}
                            <button
                                @click="
                                    const value = parseFloat(qty);
                                    const kg = unit === 'g' ? value / 1000 : value;
                                    if (!kg || kg <= 0 || isNaN(kg)) return;
                                    $store.cart.addWeightItem({...product, display_unit: unit}, kg);
                                    added = true;
                                    qty = '';
                                    setTimeout(() => added = false, 2000)
                                "
                                :disabled="!qty || parseFloat(qty) <= 0 || isNaN(parseFloat(qty)) || added"
                                :class="added
                                    ? 'bg-indigo-700 cursor-default shadow-md shadow-indigo-200'
                                    : (!qty || parseFloat(qty) <= 0 || isNaN(parseFloat(qty))
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700 text-white shadow-md shadow-indigo-200')"
                                class="w-full flex items-center justify-center gap-2.5
                                       py-4 rounded-xl font-bold text-base
                                       transition-all duration-200 select-none"
                            >
                                <template x-if="!added">
                                    <span class="flex items-center gap-2.5">
                                        <i class="fas fa-cart-plus text-xl"></i>
                                        Agregar al carrito
                                    </span>
                                </template>
                                <template x-if="added">
                                    <span class="flex items-center gap-2.5 text-white">
                                        <i class="fas fa-check text-xl"></i>
                                        ¡Agregado al carrito!
                                    </span>
                                </template>
                            </button>

                            {{-- View cart link --}}
                            <button
                                x-show="added"
                                x-cloak
                                @click="$store.cart.open = true"
                                class="w-full flex items-center justify-center gap-2 mt-2
                                       border-2 border-indigo-400 text-indigo-600 hover:bg-indigo-50
                                       py-3 rounded-xl font-semibold text-sm transition-colors"
                            >
                                <i class="fas fa-shopping-cart"></i>
                                Ver carrito y finalizar pedido
                            </button>
                        </div>

                    @else
                        {{-- ── Unit product: add to cart or restock ── --}}
                        @if($totalStock !== null && $totalStock <= 0)
                            {{-- Out of stock: request restock via WhatsApp --}}
                            <button
                                onclick="requestRestock({{ json_encode(['name' => $product->name]) }})"
                                class="w-full flex items-center justify-center gap-2.5
                                       bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white
                                       py-4 rounded-xl font-bold text-base
                                       shadow-md shadow-amber-200 transition-all duration-200"
                            >
                                <i class="fab fa-whatsapp text-xl"></i>
                                Solicitar reposición
                            </button>
                            <p class="text-sm text-center text-gray-400 mt-2 flex items-center justify-center gap-1.5">
                                <i class="fas fa-info-circle text-xs"></i>
                                Sin stock disponible · Te contactamos por WhatsApp
                            </p>
                        @else
                            <div x-data="{ added: false }">
                                <button
                                    @click="$store.cart.addItem({{ $cartData }}); added = true; setTimeout(() => added = false, 2000)"
                                    :class="added
                                        ? 'bg-brand-700 cursor-default'
                                        : 'bg-brand-500 hover:bg-brand-600 active:bg-brand-700'"
                                    class="w-full flex items-center justify-center gap-2.5
                                           text-white py-4 rounded-xl font-bold text-base
                                           shadow-md shadow-green-100 transition-all duration-200 select-none"
                                    :disabled="added"
                                >
                                    <template x-if="!added">
                                        <span class="flex items-center gap-2.5">
                                            <i class="fas fa-cart-plus text-xl"></i>
                                            Agregar al carrito
                                        </span>
                                    </template>
                                    <template x-if="added">
                                        <span class="flex items-center gap-2.5">
                                            <i class="fas fa-check text-xl"></i>
                                            ¡Producto agregado!
                                        </span>
                                    </template>
                                </button>
                            </div>
                            <button
                                x-data
                                @click="$store.cart.addItem({{ $cartData }}); $store.cart.open = true"
                                class="w-full flex items-center justify-center gap-2
                                       border-2 border-brand-500 text-brand-600 hover:bg-brand-50
                                       py-3 rounded-xl font-semibold text-sm transition-colors"
                            >
                                <i class="fas fa-shopping-cart"></i>
                                Ver carrito y finalizar pedido
                            </button>
                        @endif
                    @endif

                    {{-- Back to store --}}
                    <a
                        href="{{ route('store.index') }}"
                        class="flex items-center justify-center gap-1.5 text-sm text-gray-400
                               hover:text-brand-600 transition-colors py-1"
                    >
                        <i class="fas fa-arrow-left text-xs"></i>
                        Seguir comprando
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
