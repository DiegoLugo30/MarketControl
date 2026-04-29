@php
    $totalStock   = $product->is_weighted ? null : $product->getTotalStockAttribute();
    $isOutOfStock = !$product->is_weighted && $totalStock !== null && $totalStock <= 0;
    $isLowStock   = !$product->is_weighted && $totalStock !== null && $totalStock > 0 && $totalStock <= 5;

    $imageUrl = $product->image_path ? asset('storage/' . $product->image_path) : null;

    // Data passed to Alpine addItem (unit products)
    $cartData = json_encode([
        'id'        => $product->id,
        'name'      => $product->name,
        'price'     => (float) $product->price,
        'stock'     => $totalStock,
        'unit_type' => 'unit',
        'image'     => $imageUrl,
    ]);

    // Data passed to Alpine addWeightItem (weight products — no stock limit)
    $weightData = json_encode([
        'id'        => $product->id,
        'name'      => $product->name,
        'price'     => (float) ($product->price_per_kg ?? 0),
        'stock'     => null,
        'unit_type' => 'weight',
        'image'     => $imageUrl,
    ]);
@endphp

<article class="bg-white rounded-2xl shadow-card hover:shadow-card-hover transition-shadow duration-300 overflow-hidden flex flex-col group">

    {{-- Product image area --}}
    <a href="{{ route('store.product', $product->id) }}" class="block">
        <div class="relative h-44 overflow-hidden
                    @if(!$imageUrl) bg-gradient-to-br
                        @if($product->is_weighted) from-blue-50 to-indigo-100
                        @elseif($isLowStock) from-amber-50 to-yellow-100
                        @else from-brand-50 to-emerald-100 @endif
                    @endif
                    flex items-center justify-center">

            @if($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
            @else
                <i class="fas fa-{{ $product->is_weighted ? 'weight-hanging' : 'box' }}
                          text-5xl
                          @if($product->is_weighted) text-indigo-200
                          @elseif($isLowStock) text-amber-200
                          @else text-brand-200 @endif
                          group-hover:scale-110 transition-transform duration-300"></i>
            @endif

            {{-- Type / stock badge --}}
            @if($product->is_weighted)
                <span class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                             text-xs font-semibold bg-blue-100/90 text-blue-700 shadow-sm backdrop-blur-sm">
                    <i class="fas fa-weight text-[10px]"></i> A granel
                </span>
            @elseif($isOutOfStock)
                <span class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                             text-xs font-semibold bg-red-100/90 text-red-700 shadow-sm backdrop-blur-sm">
                    <i class="fas fa-times-circle text-[10px]"></i> Sin stock
                </span>
            @elseif($isLowStock)
                <span class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                             text-xs font-semibold bg-amber-100/90 text-amber-700 shadow-sm backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Últimas {{ $totalStock }}
                </span>
            @else
                <span class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                             text-xs font-semibold bg-brand-100/90 text-brand-700 shadow-sm backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                    Disponible
                </span>
            @endif
        </div>
    </a>

    {{-- Card body --}}
    <div class="flex flex-col flex-1 p-4 gap-3">

        {{-- Name + description --}}
        <div class="flex-1">
            <a href="{{ route('store.product', $product->id) }}">
                <h3 class="font-semibold text-gray-900 leading-snug line-clamp-2 hover:text-brand-600 transition-colors">
                    {{ $product->name }}
                </h3>
            </a>
            @if($product->description)
                <p class="text-xs text-gray-400 mt-1 line-clamp-2 leading-relaxed">
                    {{ $product->description }}
                </p>
            @endif
        </div>

        {{-- Price --}}
        <div>
            @if($product->is_weighted)
                <p class="text-2xl font-extrabold text-gray-900">
                    ${{ number_format($product->price_per_kg, 2) }}
                    <span class="text-sm font-normal text-gray-400">/kg</span>
                </p>
            @else
                <p class="text-2xl font-extrabold text-gray-900">
                    ${{ number_format($product->price, 2) }}
                </p>
            @endif
        </div>

        {{-- CTA --}}
        @if($product->is_weighted)
            {{-- Weight product: unit selector (g/kg) + input + add to cart --}}
            <div x-data="{ qty: '', unit: 'g', added: false, product: {{ $weightData }} }">

                {{-- Unit selector + input row --}}
                <div class="flex gap-2 mb-2 items-center">
                    <select
                        x-model="unit"
                        @change="qty = ''"
                        class="shrink-0 border-2 border-gray-200 rounded-xl px-2 py-2
                               text-xs font-semibold text-gray-600 bg-white
                               focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100
                               transition-all cursor-pointer"
                    >
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                    </select>

                    <div class="relative flex-1">
                        <input
                            type="number"
                            x-model="qty"
                            :placeholder="unit === 'g' ? '100' : '0.5'"
                            :step="unit === 'g' ? '1' : '0.001'"
                            :min="unit === 'g' ? '1' : '0.001'"
                            :class="qty && parseFloat(qty) > 0
                                ? 'border-indigo-400 ring-2 ring-indigo-100'
                                : 'border-gray-200'"
                            class="w-full pl-3 pr-9 py-2 border-2 rounded-xl text-sm font-semibold
                                   focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100
                                   transition-all text-center"
                            @keydown.enter.prevent="
                                const value = parseFloat(qty);
                                const kg = unit === 'g' ? value / 1000 : value;
                                if (kg > 0) {
                                    $store.cart.addWeightItem({...product, display_unit: unit}, kg);
                                    added = true; qty = '';
                                    setTimeout(() => added = false, 1800)
                                }
                            "
                        >
                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none"
                              x-text="unit"></span>
                    </div>

                    {{-- Live subtotal badge --}}
                    <div
                        x-show="parseFloat(qty) > 0"
                        x-cloak
                        class="flex items-center bg-indigo-50 rounded-xl px-2.5 shrink-0"
                    >
                        <span class="text-xs font-bold text-indigo-700"
                              x-text="$store.cart.fmt(product.price * (unit === 'g' ? parseFloat(qty) / 1000 : parseFloat(qty)) || 0)"></span>
                    </div>
                </div>

                {{-- Validation hints --}}
                <p
                    x-show="qty !== '' && parseFloat(qty) <= 0"
                    x-cloak
                    class="text-xs text-red-500 mb-2"
                >Ingresá una cantidad mayor a 0</p>
                <p
                    x-show="unit === 'g' && qty !== '' && parseFloat(qty) > 0 && qty % 1 !== 0"
                    x-cloak
                    class="text-xs text-amber-500 mb-2"
                >Para gramos usá números enteros</p>

                {{-- Add to cart button --}}
                <button
                    @click="
                        const value = parseFloat(qty);
                        const kg = unit === 'g' ? value / 1000 : value;
                        if (!kg || kg <= 0) return;
                        $store.cart.addWeightItem({...product, display_unit: unit}, kg);
                        added = true;
                        qty = '';
                        setTimeout(() => added = false, 1800)
                    "
                    :disabled="!qty || parseFloat(qty) <= 0 || isNaN(parseFloat(qty)) || added"
                    :class="added
                        ? 'bg-indigo-700 cursor-default'
                        : (!qty || parseFloat(qty) <= 0
                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            : 'bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700 text-white')"
                    class="w-full flex items-center justify-center gap-2
                           py-2.5 rounded-xl font-semibold text-sm
                           transition-all duration-200 select-none"
                >
                    <template x-if="!added">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-cart-plus text-sm"></i>
                            Agregar al carrito
                        </span>
                    </template>
                    <template x-if="added">
                        <span class="flex items-center gap-2 text-white">
                            <i class="fas fa-check text-sm"></i>
                            ¡Agregado!
                        </span>
                    </template>
                </button>
            </div>

        @else
            {{-- Unit product --}}
            @if($totalStock !== null && $totalStock <= 0)
                {{-- Out of stock: request restock via WhatsApp --}}
                <button
                    onclick="requestRestock({{ json_encode(['name' => $product->name]) }})"
                    class="w-full flex items-center justify-center gap-2
                           bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white
                           py-2.5 rounded-xl font-semibold text-sm
                           transition-all duration-200"
                >
                    <i class="fab fa-whatsapp text-sm"></i>
                    Solicitar reposición
                </button>
            @else
                <div x-data="{ added: false }">
                    <button
                        @click="$store.cart.addItem({{ $cartData }}); added = true; setTimeout(() => added = false, 1800)"
                        :class="added
                            ? 'bg-brand-700 cursor-default'
                            : 'bg-brand-500 hover:bg-brand-600 active:bg-brand-700'"
                        class="w-full flex items-center justify-center gap-2
                               text-white py-2.5 rounded-xl font-semibold text-sm
                               transition-all duration-200 select-none"
                        :disabled="added"
                    >
                        <template x-if="!added">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-cart-plus text-sm"></i>
                                Agregar al carrito
                            </span>
                        </template>
                        <template x-if="added">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-check text-sm"></i>
                                ¡Agregado!
                            </span>
                        </template>
                    </button>
                </div>
            @endif
        @endif
    </div>
</article>
