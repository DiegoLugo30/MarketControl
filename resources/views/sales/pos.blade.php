@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-cash-register"></i> Punto de Venta
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel de escaneo -->
            <div class="lg:col-span-2">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-barcode"></i> Escanear Código de Barras
                    </label>
                    <input
                        type="text"
                        id="pos-barcode-input"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                        placeholder="Escanea o ingresa el código..."
                        autofocus
                    >
                    <p class="text-sm text-gray-600 mt-2">
                        Presiona ENTER después de escanear o ingresar el código
                    </p>
                </div>

                <!-- Lista de productos en el carrito -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h2 class="text-xl font-bold mb-4 text-gray-700">
                        <i class="fas fa-shopping-cart"></i> Productos en Carrito
                    </h2>

                    <div id="cart-items" class="space-y-2">
                        <div class="text-center text-gray-500 py-8" id="empty-cart">
                            <i class="fas fa-shopping-cart text-5xl mb-3 opacity-50"></i>
                            <p>El carrito está vacío</p>
                            <p class="text-sm">Escanea productos para comenzar</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de totales y acciones -->
            <div class="lg:col-span-1">
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-4 text-gray-700">Resumen</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Items:</span>
                            <span class="font-semibold" id="total-items">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Productos:</span>
                            <span class="font-semibold" id="total-quantity">0</span>
                        </div>
                        <hr>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-700">TOTAL:</span>
                            <span class="text-3xl font-bold text-green-600" id="total-amount">$0.00</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <button id="btn-complete-sale" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i class="fas fa-check-circle"></i> Finalizar Compra
                        </button>
                        <button id="btn-clear-cart" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-trash"></i> Limpiar Carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de venta -->
<div id="sale-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="text-green-600 text-6xl mb-4">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">Venta Completada</h2>
            <p class="text-gray-600 mb-6">La venta se ha registrado exitosamente</p>
            <div class="space-y-3">
                <a id="btn-view-receipt" href="#" class="block w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-receipt"></i> Ver Recibo
                </a>
                <button id="btn-new-sale" class="w-full bg-gray-600 text-white py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-plus"></i> Nueva Venta
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cart = [];

    // Auto-focus en el input de código de barras
    function focusBarcodeInput() {
        $('#pos-barcode-input').focus();
    }

    // Detectar ENTER en input de código de barras
    $('#pos-barcode-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                searchAndAddProduct(barcode);
            }
        }
    });

    // Buscar producto y agregarlo al carrito
    function searchAndAddProduct(barcode) {
        $.post('{{ route("barcode.search") }}', { barcode: barcode })
            .done(function(response) {
                if (response.found_locally) {
                    addToCart(response.product);
                    $('#pos-barcode-input').val('');
                } else {
                    alert('Producto no encontrado. Por favor regístralo primero.');
                    $('#pos-barcode-input').val('');
                }
            })
            .fail(function() {
                alert('Error al buscar el producto');
                $('#pos-barcode-input').val('');
            });
    }

    // Agregar producto al carrito
    function addToCart(product) {
        if (product.stock < 1) {
            alert('Producto sin stock disponible');
            return;
        }

        const existingItem = cart.find(item => item.product_id === product.id);

        if (existingItem) {
            if (existingItem.quantity < product.stock) {
                existingItem.quantity++;
            } else {
                alert('No hay más stock disponible de este producto');
                return;
            }
        } else {
            cart.push({
                product_id: product.id,
                barcode: product.barcode,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock
            });
        }

        renderCart();
        focusBarcodeInput();
    }

    // Renderizar carrito
    function renderCart() {
        if (cart.length === 0) {
            $('#empty-cart').removeClass('hidden');
            $('#cart-items .cart-item').remove();
            $('#btn-complete-sale').prop('disabled', true);
        } else {
            $('#empty-cart').addClass('hidden');
            $('#cart-items .cart-item').remove();

            cart.forEach((item, index) => {
                const subtotal = item.quantity * item.price;
                const itemHtml = `
                    <div class="cart-item bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800">${item.name}</h3>
                                <p class="text-sm text-gray-500">Código: ${item.barcode}</p>
                                <p class="text-sm text-green-600 font-semibold">$${item.price.toFixed(2)} c/u</p>
                            </div>
                            <button class="text-red-500 hover:text-red-700" onclick="removeFromCart(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <button class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded" onclick="decrementQuantity(${index})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="font-semibold text-lg px-3">${item.quantity}</span>
                                <button class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded" onclick="incrementQuantity(${index})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Subtotal</p>
                                <p class="font-bold text-green-600 text-xl">$${subtotal.toFixed(2)}</p>
                            </div>
                        </div>
                    </div>
                `;
                $('#cart-items').append(itemHtml);
            });

            $('#btn-complete-sale').prop('disabled', false);
        }

        updateTotals();
    }

    // Incrementar cantidad
    window.incrementQuantity = function(index) {
        const item = cart[index];
        if (item.quantity < item.stock) {
            item.quantity++;
            renderCart();
        } else {
            alert('No hay más stock disponible');
        }
    };

    // Decrementar cantidad
    window.decrementQuantity = function(index) {
        const item = cart[index];
        if (item.quantity > 1) {
            item.quantity--;
            renderCart();
        } else {
            removeFromCart(index);
        }
    };

    // Eliminar del carrito
    window.removeFromCart = function(index) {
        cart.splice(index, 1);
        renderCart();
    };

    // Actualizar totales
    function updateTotals() {
        const totalItems = cart.length;
        const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
        const totalAmount = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);

        $('#total-items').text(totalItems);
        $('#total-quantity').text(totalQuantity);
        $('#total-amount').text('$' + totalAmount.toFixed(2));
    }

    // Limpiar carrito
    $('#btn-clear-cart').click(function() {
        if (cart.length > 0 && confirm('¿Estás seguro de limpiar el carrito?')) {
            cart = [];
            renderCart();
            focusBarcodeInput();
        }
    });

    // Completar venta
    $('#btn-complete-sale').click(function() {
        if (cart.length === 0) return;

        const totalAmount = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);

        const saleData = {
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            })),
            total: totalAmount.toFixed(2)
        };

        $.post('{{ route("sales.complete") }}', saleData)
            .done(function(response) {
                if (response.success) {
                    $('#btn-view-receipt').attr('href', '/sales/' + response.sale_id + '/receipt');
                    $('#sale-modal').removeClass('hidden');
                } else {
                    alert(response.message || 'Error al procesar la venta');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                alert(response?.message || 'Error al procesar la venta');
            });
    });

    // Nueva venta
    $('#btn-new-sale').click(function() {
        $('#sale-modal').addClass('hidden');
        cart = [];
        renderCart();
        focusBarcodeInput();
    });

    // Mantener foco en el input
    $(document).on('click', function() {
        focusBarcodeInput();
    });

    focusBarcodeInput();
});
</script>
@endpush
