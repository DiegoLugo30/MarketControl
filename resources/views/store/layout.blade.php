<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogo') — {{ config('store.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Comprá nuestros productos y hacé tu pedido por WhatsApp.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04)',
                        'card-hover': '0 10px 25px -5px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.04)',
                    }
                }
            }
        }
    </script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Alpine.js cart store — must be defined BEFORE Alpine loads --}}
    <script>
        const STORE_WHATSAPP = '{{ config('store.whatsapp_number') }}';
        const STORE_CURRENCY  = '{{ config('store.currency_symbol') }}';
        const STORE_LOCALE    = '{{ config('store.currency_locale') }}';

        window.requestRestock = function(product) {
            const user  = @json(auth()->user());
            const name  = user ? user.name  : 'Invitado';
            const email = user ? user.email : 'No registrado';
            const message = `Hola, quisiera que vuelvan a traer:\n\nProducto: ${product.name}\nUsuario: ${name}\nEmail: ${email}`;
            window.open(`https://wa.me/${STORE_WHATSAPP}?text=${encodeURIComponent(message)}`, '_blank');
        };

        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                open:    false,
                comment: '',
                items:   JSON.parse(localStorage.getItem('store_cart') || '[]'),

                // ── Computed ──────────────────────────────────────────────────
                get total() {
                    return this.items.reduce(
                        (sum, item) => sum + item.price * item.quantity, 0
                    );
                },
                // Weight items count as 1; unit items count by quantity
                get count() {
                    return this.items.reduce((sum, item) =>
                        sum + (item.unit_type === 'weight' ? 1 : (item.quantity || 0)), 0
                    );
                },

                // ── Actions ───────────────────────────────────────────────────
                toggle() { this.open = !this.open; },
                close()  { this.open = false; },

                // Unit products — increments quantity, respects available stock
                addItem(product) {
                    const existing = this.items.find(i => i.id === product.id);
                    if (existing) {
                        if (product.stock != null && existing.quantity >= product.stock) return false;
                        existing.quantity++;
                    } else {
                        if (product.stock != null && product.stock <= 0) return false;
                        this.items.push({ ...product, quantity: 1, unit_type: product.unit_type || 'unit' });
                    }
                    this.save();
                    this.open = true;
                    return true;
                },

                // Weight products — accumulates kg (rounds to 3 decimal places)
                addWeightItem(product, weightKg) {
                    const kg = parseFloat(weightKg);
                    if (!kg || kg <= 0 || isNaN(kg)) return false;

                    const existing = this.items.find(i => i.id === product.id);
                    if (existing) {
                        existing.quantity = Math.round((existing.quantity + kg) * 1000) / 1000;
                    } else {
                        this.items.push({ ...product, quantity: kg, unit_type: 'weight' });
                    }
                    this.save();
                    this.open = true;
                    return true;
                },

                removeItem(id) {
                    this.items = this.items.filter(i => i.id !== id);
                    this.save();
                },

                increment(id) {
                    const item = this.items.find(i => i.id === id);
                    if (!item || item.unit_type === 'weight') return;
                    if (item.stock != null && item.quantity >= item.stock) return;
                    item.quantity++;
                    this.save();
                },

                decrement(id) {
                    const item = this.items.find(i => i.id === id);
                    if (!item || item.unit_type === 'weight') return;
                    if (item.quantity > 1) { item.quantity--; this.save(); }
                    else                   { this.removeItem(id); }
                },

                clear() {
                    this.items = [];
                    localStorage.removeItem('store_cart');
                },

                save() {
                    localStorage.setItem('store_cart', JSON.stringify(this.items));
                },

                // ── Helpers ───────────────────────────────────────────────────
                fmt(amount) {
                    return STORE_CURRENCY + new Intl.NumberFormat(STORE_LOCALE, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(amount);
                },

                fmtQty(item) {
                    if (item.unit_type === 'weight') {
                        return item.display_unit === 'g'
                            ? `${(item.quantity * 1000).toFixed(0)} g`
                            : `${item.quantity.toFixed(3)} kg`;
                    }
                    return `x${item.quantity}`;
                },

                async checkout() {
                    if (this.items.length === 0) return;

                    // Persist order to backend and get a unique code
                    let orderCode = null;
                    try {
                        const payload = {
                            items: this.items.map(i => ({
                                id:        i.id,
                                name:      i.name,
                                price:     i.price,
                                quantity:  i.quantity,
                                unit_type: i.unit_type,
                            })),
                            comment: this.comment.trim() || null,
                        };

                        const res = await fetch('{{ route('store.orders.store') }}', {
                            method:  'POST',
                            headers: {
                                'Content-Type':  'application/json',
                                'Accept':        'application/json',
                                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (res.ok) {
                            const data = await res.json();
                            orderCode = data.code;
                        }
                    } catch (e) {
                        // If order creation fails, continue with WhatsApp without a code
                    }

                    const lines = this.items.map(i => {
                        let qty;
                        if (i.unit_type === 'weight') {
                            qty = i.display_unit === 'g'
                                ? `${(i.quantity * 1000).toFixed(0)} g`
                                : `${i.quantity.toFixed(3)} kg`;
                        } else {
                            qty = `x${i.quantity}`;
                        }
                        return `• ${i.name} ${qty} — ${this.fmt(i.price * i.quantity)}`;
                    }).join('\n');

                    const parts = [
                        'Hola! 👋 Quiero hacer el siguiente pedido:',
                        '',
                        lines,
                        '',
                        `💰 *Total: ${this.fmt(this.total)}*`,
                    ];

                    if (this.comment.trim()) {
                        parts.push('', `📝 *Comentario:*\n${this.comment.trim()}`);
                    }

                    if (orderCode) {
                        const posUrl = `${window.location.origin}/admin/pos?order=${orderCode}`;

                        parts.push(
                            '',
                            `Pedido #${orderCode}`,
                            posUrl
                        );
                    }

                    window.open(
                        `https://wa.me/${STORE_WHATSAPP}?text=${encodeURIComponent(parts.join('\n'))}`,
                        '_blank'
                    );
                    this.comment = '';
                    this.clear();
                },
            });
        });
    </script>

    <style>
        /* Hide elements before Alpine initialises (prevents flash) */
        [x-cloak] { display: none !important; }

        /* Smooth cart sidebar scroll without showing scrollbar */
        .cart-scroll {
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #e5e7eb transparent;
        }
        .cart-scroll::-webkit-scrollbar       { width: 4px; }
        .cart-scroll::-webkit-scrollbar-track { background: transparent; }
        .cart-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

        /* Smooth page transitions */
        * { scroll-behavior: smooth; }
    </style>

    @stack('head')
</head>

{{-- x-data="" makes the body an Alpine component so $store.cart is accessible everywhere --}}
<body class="bg-gray-50 text-gray-800 antialiased font-sans" x-data>

{{-- ═══════════════════════════════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════════════════════════════════ --}}
<header class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">

            {{-- Logo / store name --}}
            <a href="{{ route('store.index') }}" class="flex items-center gap-2.5 shrink-0">
                <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fas fa-store text-white text-sm"></i>
                </div>
                <span class="font-bold text-lg text-gray-900 tracking-tight">
                    {{ config('store.name') }}
                </span>
            </a>

            {{-- Search bar (desktop) --}}
            <form method="GET" action="{{ route('store.index') }}" class="hidden md:flex flex-1 max-w-lg">
                <div class="relative w-full">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Buscar productos…"
                        autocomplete="off"
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-100 border border-transparent rounded-full
                               focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                               transition-all placeholder-gray-400"
                    >
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    @if(request('q'))
                        <a href="{{ route('store.index') }}"
                           class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xs"></i>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Auth section (desktop) --}}
            <div class="hidden sm:flex items-center gap-2 shrink-0">
                @auth
                    <span class="text-sm text-gray-500 hidden lg:block truncate max-w-[120px]">
                        {{ auth()->user()->name }}
                    </span>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.home') }}"
                           class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Admin
                        </a>
                    @else
                        <a href="{{ route('store.account.orders') }}"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition flex items-center gap-1">
                            <i class="fas fa-clipboard-list text-[11px]"></i>
                            Mis pedidos
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                        Ingresar
                    </a>
                    <a href="{{ route('register') }}"
                       class="text-sm font-semibold bg-brand-500 hover:bg-brand-600 text-white px-3.5 py-1.5 rounded-full transition shadow-sm">
                        Registrarse
                    </a>
                @endauth
            </div>

            {{-- Cart button --}}
            <button
                @click="$store.cart.toggle()"
                class="relative flex items-center gap-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700
                       text-white px-4 py-2 rounded-full text-sm font-semibold shadow-sm transition-colors shrink-0"
            >
                <i class="fas fa-shopping-cart text-base"></i>
                <span class="hidden sm:inline">Carrito</span>
                {{-- Item count badge --}}
                <span
                    x-show="$store.cart.count > 0"
                    x-cloak
                    x-text="$store.cart.count"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold
                           min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center
                           ring-2 ring-white"
                ></span>
            </button>

        </div>
    </div>

    {{-- Search bar + auth (mobile) --}}
    <div class="md:hidden border-t border-gray-100 px-4 pb-3 pt-2 space-y-2">
        <form method="GET" action="{{ route('store.index') }}">
            <div class="relative">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar productos…"
                    autocomplete="off"
                    class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 border border-transparent rounded-full
                           focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                           transition-all placeholder-gray-400"
                >
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
            </div>
        </form>

        {{-- Mobile auth links --}}
        @guest
            <div class="flex items-center justify-end gap-2 pt-0.5">
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                    Ingresar
                </a>
                <a href="{{ route('register') }}"
                   class="text-sm font-semibold bg-brand-500 hover:bg-brand-600 text-white px-3.5 py-1.5 rounded-full transition shadow-sm">
                    Registrarse
                </a>
            </div>
        @endguest
        @auth
            <div class="flex items-center justify-between pt-0.5">
                <span class="text-sm text-gray-500 truncate max-w-[150px]">{{ auth()->user()->name }}</span>
                <div class="flex items-center gap-1">
                    @if(!auth()->user()->isAdmin())
                        <a href="{{ route('store.account.orders') }}"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition flex items-center gap-1">
                            <i class="fas fa-clipboard-list text-[11px]"></i>
                            Mis pedidos
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</header>


{{-- ═══════════════════════════════════════════════════════════════════════
     CART OVERLAY
═══════════════════════════════════════════════════════════════════════════ --}}
<div
    x-show="$store.cart.open"
    x-cloak
    @click="$store.cart.close()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50"
></div>


{{-- ═══════════════════════════════════════════════════════════════════════
     CART SIDEBAR
═══════════════════════════════════════════════════════════════════════════ --}}
<aside
    x-show="$store.cart.open"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    @click.outside="$store.cart.close()"
    class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 flex flex-col"
>
    {{-- Cart header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-cart text-brand-600 text-sm"></i>
            </div>
            <h2 class="font-bold text-gray-900">Mi Carrito</h2>
            <span
                x-show="$store.cart.count > 0"
                x-cloak
                x-text="$store.cart.count + ' item' + ($store.cart.count !== 1 ? 's' : '')"
                class="bg-brand-100 text-brand-700 text-xs font-semibold px-2 py-0.5 rounded-full"
            ></span>
        </div>
        <button
            @click="$store.cart.close()"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
        >
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Cart items --}}
    <div class="flex-1 cart-scroll px-5 py-4 space-y-3">

        {{-- Empty state --}}
        <div x-show="$store.cart.items.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-shopping-cart text-gray-300 text-3xl"></i>
            </div>
            <p class="font-semibold text-gray-600 mb-1">Tu carrito está vacío</p>
            <p class="text-sm text-gray-400 mb-5">Agregá productos para empezar</p>
            <button
                @click="$store.cart.close()"
                class="text-brand-600 hover:text-brand-700 font-medium text-sm underline underline-offset-2"
            >
                Ver catálogo
            </button>
        </div>

        {{-- Item list --}}
        <template x-for="item in $store.cart.items" :key="item.id">
            <div class="flex gap-3 bg-gray-50 rounded-xl p-3 group">

                {{-- Thumbnail or icon --}}
                <div class="w-11 h-11 rounded-lg overflow-hidden shadow-sm shrink-0 border border-gray-100 bg-white flex items-center justify-center">
                    <template x-if="item.image">
                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!item.image">
                        <i :class="item.unit_type === 'weight' ? 'fas fa-weight-hanging text-indigo-300' : 'fas fa-box text-brand-400'" class="text-sm"></i>
                    </template>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 leading-snug truncate" x-text="item.name"></p>
                    <p class="text-xs font-medium mt-0.5"
                       :class="item.unit_type === 'weight' ? 'text-indigo-500' : 'text-brand-600'"
                       x-text="$store.cart.fmt(item.price) + (item.unit_type === 'weight' ? ' /kg' : ' c/u')"></p>

                    {{-- Unit: +/- controls --}}
                    <div class="flex items-center gap-2 mt-2" x-show="item.unit_type !== 'weight'">
                        <button
                            @click="$store.cart.decrement(item.id)"
                            class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center
                                   text-gray-500 hover:bg-gray-200 transition text-[10px]"
                        ><i class="fas fa-minus"></i></button>

                        <span class="text-sm font-bold text-gray-800 w-5 text-center" x-text="item.quantity"></span>

                        <button
                            @click="$store.cart.increment(item.id)"
                            :disabled="item.stock != null && item.quantity >= item.stock"
                            :class="item.stock != null && item.quantity >= item.stock
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-brand-500 hover:bg-brand-600 text-white'"
                            class="w-6 h-6 rounded-full flex items-center justify-center transition text-[10px]"
                        ><i class="fas fa-plus"></i></button>

                        <span
                            x-show="item.stock != null && item.quantity >= item.stock"
                            x-cloak
                            class="text-[10px] text-red-500 font-semibold leading-none"
                        >Máx.</span>
                    </div>

                    {{-- Weight: show quantity with user-selected unit --}}
                    <div class="flex items-center gap-1.5 mt-2" x-show="item.unit_type === 'weight'">
                        <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full">
                            <i class="fas fa-weight-hanging text-[9px]"></i>
                            <span x-text="item.display_unit === 'g'
                                ? (item.quantity * 1000).toFixed(0) + ' g'
                                : item.quantity.toFixed(3) + ' kg'"></span>
                        </span>
                    </div>
                </div>

                {{-- Subtotal + delete --}}
                <div class="flex flex-col items-end justify-between py-0.5">
                    <p class="text-sm font-bold text-gray-900" x-text="$store.cart.fmt(item.price * item.quantity)"></p>
                    <button
                        @click="$store.cart.removeItem(item.id)"
                        class="text-gray-300 hover:text-red-500 transition p-1 opacity-0 group-hover:opacity-100"
                    >
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>

            </div>
        </template>
    </div>

    {{-- Cart footer --}}
    <div x-show="$store.cart.items.length > 0" x-cloak class="border-t border-gray-100 px-5 py-5 bg-white space-y-3">

        {{-- Total --}}
        <div class="flex items-center justify-between">
            <span class="text-gray-500 text-sm">Total estimado</span>
            <span class="text-2xl font-extrabold text-gray-900" x-text="$store.cart.fmt($store.cart.total)"></span>
        </div>

        {{-- Order comment --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                <i class="fas fa-comment-alt text-[10px] mr-1"></i> Comentario del pedido
            </label>
            <textarea
                x-model="$store.cart.comment"
                placeholder="Ej: sin sal, bien maduro, bolsa extra…"
                rows="2"
                class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2
                       focus:outline-none focus:ring-2 focus:ring-brand-100 focus:border-brand-400
                       resize-none placeholder-gray-400 transition-all"
            ></textarea>
        </div>

        {{-- WhatsApp CTA --}}
        <button
            @click="$store.cart.checkout()"
            class="w-full flex items-center justify-center gap-2
                   bg-[#25D366] hover:bg-[#1ebe5b] active:bg-[#18a84f]
                   text-white py-3.5 rounded-xl font-bold text-sm
                   shadow-md shadow-green-200 transition-all"
        >
            <i class="fab fa-whatsapp text-xl"></i>
            <span>Finalizar pedido por WhatsApp</span>
        </button>

        {{-- Clear cart --}}
        <button
            @click="$store.cart.clear()"
            class="w-full text-center text-xs text-gray-400 hover:text-gray-600 py-1 transition"
        >
            Vaciar carrito
        </button>

    </div>
</aside>


{{-- ═══════════════════════════════════════════════════════════════════════
     PAGE CONTENT
═══════════════════════════════════════════════════════════════════════════ --}}
<main>
    @yield('content')
</main>


{{-- ═══════════════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════════════════ --}}
<footer class="bg-white border-t border-gray-100 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 bg-brand-500 rounded-md flex items-center justify-center">
                    <i class="fas fa-store text-white text-xs"></i>
                </div>
                <span class="font-semibold text-gray-700">{{ config('store.name') }}</span>
            </div>
            <p class="text-sm text-gray-400">
                © {{ date('Y') }} {{ config('store.name') }}. Todos los derechos reservados.
            </p>
            <a
                href="https://wa.me/{{ config('store.whatsapp_number') }}"
                target="_blank"
                class="flex items-center gap-2 text-sm text-[#25D366] hover:text-[#1ebe5b] font-medium transition"
            >
                <i class="fab fa-whatsapp text-lg"></i>
                Contactar por WhatsApp
            </a>
        </div>
    </div>
</footer>

{{-- Alpine.js – must load AFTER the store definition script above --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@stack('scripts')
</body>
</html>
