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
                        <i class="fas fa-barcode"></i> Código de Barras / Código Interno
                    </label>
                    <input
                        type="text"
                        id="pos-barcode-input"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg"
                        placeholder="Escanea o ingresa el código..."
                        autofocus
                    >
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="fas fa-info-circle"></i> Presiona ENTER después de escanear
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
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold" id="subtotal-amount">$0.00</span>
                        </div>

                        <!-- Descuento Total -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="fas fa-tag"></i> Descuento Total (%)
                            </label>
                            <div class="flex items-center">
                                <input
                                    type="number"
                                    id="total-discount-input"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                    placeholder="0"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value="0"
                                >
                                <span class="ml-2 text-lg font-semibold text-gray-600">%</span>
                            </div>
                        </div>

                        <hr class="border-gray-300">
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-gray-700">TOTAL:</span>
                            <span class="text-3xl font-bold text-green-600" id="total-amount">$0.00</span>
                        </div>

                        <!-- Método de Pago -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-credit-card"></i> Método de Pago
                            </label>
                            <select id="payment-method" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="efectivo" selected>💵 Efectivo</option>
                                <option value="debito">💳 Débito</option>
                                <option value="transferencia">🏦 Transferencia</option>
                            </select>
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

<!-- Modal de Peso para Productos Pesables -->
<div id="weight-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <div class="text-center mb-4">
            <div class="text-blue-600 text-5xl mb-2">
                <i class="fas fa-weight"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2" id="weight-product-name">Producto Pesable</h2>
            <p class="text-gray-600 text-sm" id="weight-product-price"></p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2 text-center">
                Ingrese el Peso
            </label>

            <!-- Input de peso con selector de unidad -->
            <div class="flex gap-2 mb-3">
                <input
                    type="number"
                    id="weight-input"
                    class="flex-1 px-4 py-3 border-2 border-blue-300 rounded-lg text-center text-2xl font-bold focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="0"
                    step="1"
                    min="1"
                    autofocus
                >
                <select id="weight-unit" class="px-3 py-2 border-2 border-blue-300 rounded-lg font-semibold">
                    <option value="g" selected>g</option>
                    <option value="kg">kg</option>
                </select>
            </div>

            <!-- Teclado numérico virtual -->
            <div class="grid grid-cols-3 gap-2 mb-3">
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="1">1</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="2">2</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="3">3</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="4">4</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="5">5</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="6">6</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="7">7</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="8">8</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="9">9</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num=".">.</button>
                <button class="num-btn bg-gray-100 hover:bg-gray-200 py-3 rounded text-xl font-semibold" data-num="0">0</button>
                <button id="btn-clear-weight" class="bg-red-100 hover:bg-red-200 py-3 rounded text-xl font-semibold">
                    <i class="fas fa-backspace"></i>
                </button>
            </div>

            <!-- Precio calculado -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                <p class="text-sm text-gray-600 mb-1">Precio Total</p>
                <p class="text-3xl font-bold text-green-600" id="calculated-price">$0.00</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button id="btn-add-weighted" class="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-check"></i> Agregar
            </button>
            <button id="btn-cancel-weight" class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold">
                <i class="fas fa-times"></i> Cancelar
            </button>
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
    let pendingWeightedProduct = null; // Producto esperando peso

    // Auto-focus en el input de código de barras
    function focusBarcodeInput() {
        $('#pos-barcode-input').focus();
    }

    // Detectar ENTER en input de código de barras
    $('#pos-barcode-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const code = $(this).val().trim();
            if (code) {
                searchAndAddProduct(code);
            }
        }
    });

    // Buscar producto y agregarlo al carrito o pedir peso
    function searchAndAddProduct(code) {
        // Forzar HTTPS en la URL
        let url = '{{ env('APP_URL') }}/barcode/search';

        console.log('🔍 [POS] Buscando código:', code);
        console.log('📋 [POS] CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
        console.log('🔒 [POS] URL:', url);

        $.ajax({
            url: url,
            type: 'POST',
            data: { barcode: code },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('✅ [POS] Respuesta:', response);

                if (response.found_locally) {
                    const product = response.product;

                    // Verificar si es producto pesable
                    if (product.requires_weight) {
                        // Mostrar modal de peso
                        showWeightModal(product);
                    } else {
                        // Producto normal - agregar directamente
                        addToCart(product);
                    }

                    $('#pos-barcode-input').val('');
                } else {
                    alert('Producto no encontrado. Por favor regístralo primero.');
                    $('#pos-barcode-input').val('');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ [POS] ERROR:', {
                    status: status,
                    error: error,
                    statusCode: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });

                let errorMessage = '❌ ERROR EN POS\n\n';
                errorMessage += 'HTTP: ' + xhr.status + ' ' + xhr.statusText + '\n';
                errorMessage += 'Código: ' + code + '\n\n';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMessage += '⚠️ Sesión expirada\nRecarga la página (F5)';
                } else if (xhr.status === 500) {
                    errorMessage += '⚠️ Error del servidor\nVer consola (F12)';
                } else if (xhr.status === 0) {
                    errorMessage += '⚠️ Sin conexión al servidor';
                } else {
                    errorMessage += 'Ver consola del navegador (F12) para detalles';
                }

                alert(errorMessage);
                console.error('🔴 [POS] ERROR COMPLETO:', xhr);

                $('#pos-barcode-input').val('');
            }
        });
    }

    // Mostrar modal de peso
    function showWeightModal(product) {
        pendingWeightedProduct = product;
        $('#weight-product-name').text(product.name);
        $('#weight-product-price').text('$' + parseFloat(product.price_per_kg).toFixed(2) + ' por kg');
        $('#weight-input').val('');
        $('#weight-unit').val('g'); // Resetear a gramos
        $('#weight-input').attr('step', '1').attr('min', '1').attr('placeholder', '0'); // Ajustar para gramos
        $('#calculated-price').text('$0.00');
        $('#btn-add-weighted').prop('disabled', true);
        $('#weight-modal').removeClass('hidden');
        $('#weight-input').focus();
    }

    // Cerrar modal de peso
    function closeWeightModal() {
        $('#weight-modal').addClass('hidden');
        pendingWeightedProduct = null;
        focusBarcodeInput();
    }

    // Teclado numérico virtual
    $('.num-btn').on('click', function() {
        const num = $(this).data('num');
        const currentVal = $('#weight-input').val();
        $('#weight-input').val(currentVal + num);
        calculateWeightPrice();
    });

    // Botón borrar
    $('#btn-clear-weight').on('click', function() {
        const currentVal = $('#weight-input').val();
        $('#weight-input').val(currentVal.slice(0, -1));
        calculateWeightPrice();
    });

    // Calcular precio al escribir
    $('#weight-input, #weight-unit').on('input change', calculateWeightPrice);

    // Ajustar step y placeholder según unidad seleccionada
    $('#weight-unit').on('change', function() {
        const unit = $(this).val();
        const input = $('#weight-input');

        if (unit === 'g') {
            input.attr('step', '1');
            input.attr('min', '1');
            input.attr('placeholder', '0');
        } else {
            input.attr('step', '0.001');
            input.attr('min', '0.001');
            input.attr('placeholder', '0.000');
        }
    });

    function calculateWeightPrice() {
        if (!pendingWeightedProduct) return;

        let weight = parseFloat($('#weight-input').val());
        const unit = $('#weight-unit').val();

        if (isNaN(weight) || weight <= 0) {
            $('#calculated-price').text('$0.00');
            $('#btn-add-weighted').prop('disabled', true);
            return;
        }

        // Convertir gramos a kg si es necesario
        if (unit === 'g') {
            weight = weight / 1000;
        }

        const pricePerKg = parseFloat(pendingWeightedProduct.price_per_kg);
        const totalPrice = weight * pricePerKg;

        $('#calculated-price').text('$' + totalPrice.toFixed(2));
        $('#btn-add-weighted').prop('disabled', false);
    }

    // Agregar producto pesable
    $('#btn-add-weighted').on('click', function() {
        if (!pendingWeightedProduct) return;

        let weight = parseFloat($('#weight-input').val());
        const unit = $('#weight-unit').val();

        if (isNaN(weight) || weight <= 0) {
            alert('Por favor ingrese un peso válido');
            return;
        }

        // Convertir a kg
        if (unit === 'g') {
            weight = weight / 1000;
        }

        const pricePerKg = parseFloat(pendingWeightedProduct.price_per_kg);
        const totalPrice = weight * pricePerKg;

        // Agregar al carrito con peso
        addWeightedToCart(pendingWeightedProduct, weight, totalPrice);
        closeWeightModal();
    });

    // Cancelar modal de peso
    $('#btn-cancel-weight').on('click', closeWeightModal);

    // ENTER en input de peso
    $('#weight-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-add-weighted').click();
        }
    });

    // Agregar producto normal al carrito
    function addToCart(product) {
        if (product.stock < 1) {
            alert('Producto sin stock disponible');
            return;
        }

        const existingItem = cart.find(item =>
            item.product_id === product.id && !item.is_weighted
        );

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
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock,
                is_weighted: false,
                weight: null,
                item_discount: 0
            });
        }

        renderCart();
        focusBarcodeInput();
    }

    // Agregar producto pesable al carrito
    function addWeightedToCart(product, weight, totalPrice) {
        // Los productos pesables NO se acumulan - cada peso es un item separado
        cart.push({
            product_id: product.id,
            name: product.name,
            price: totalPrice,
            quantity: 1,
            stock: 0,
            is_weighted: true,
            weight: weight,
            item_discount: 0
        });

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
                const subtotal = item.is_weighted ? item.price : (item.quantity * item.price);
                const itemDiscountPercent = item.item_discount || 0;
                const itemDiscountAmount = (subtotal * itemDiscountPercent / 100);
                const totalWithDiscount = subtotal - itemDiscountAmount;
                const quantityText = item.is_weighted
                    ? `<span class="text-blue-600">${item.weight.toFixed(3)} kg</span>`
                    : `${item.quantity} ud.`;

                const priceText = item.is_weighted
                    ? ''
                    : `<p class="text-sm text-green-600 font-semibold">$${item.price.toFixed(2)} c/u</p>`;

                const itemHtml = `
                    <div class="cart-item bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800">${item.name}</h3>
                                ${priceText}
                                ${item.is_weighted ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Pesable</span>' : ''}
                            </div>
                            <button class="text-red-500 hover:text-red-700" onclick="removeFromCart(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center space-x-2">
                                ${!item.is_weighted ? `
                                    <button class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded" onclick="decrementQuantity(${index})">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="font-semibold text-lg px-3">${item.quantity}</span>
                                    <button class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded" onclick="incrementQuantity(${index})">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                ` : `
                                    <span class="font-semibold text-lg px-3">${quantityText}</span>
                                `}
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Subtotal</p>
                                <p class="font-bold text-gray-700 text-lg">$${subtotal.toFixed(2)}</p>
                            </div>
                        </div>

                        <!-- Descuento del Item -->
                        <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded p-2">
                            <label class="text-xs text-gray-600 whitespace-nowrap">
                                <i class="fas fa-tag"></i> Desc.
                            </label>
                            <div class="flex items-center">
                                <input
                                    type="number"
                                    class="w-16 px-2 py-1 border border-gray-300 rounded text-sm text-center item-discount-input"
                                    data-index="${index}"
                                    placeholder="0"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value="${itemDiscountPercent}"
                                >
                                <span class="ml-1 text-sm font-semibold text-gray-600">%</span>
                            </div>
                            <div class="flex-1 text-right">
                                <p class="text-xs text-gray-500">Total</p>
                                <p class="font-bold text-green-600 text-lg">$${totalWithDiscount.toFixed(2)}</p>
                            </div>
                        </div>
                    </div>
                `;
                $('#cart-items').append(itemHtml);
            });

            $('#btn-complete-sale').prop('disabled', false);
        }

        updateTotals();

        // Event listener para descuentos de items
        $('.item-discount-input').off('input').on('input', function() {
            const index = parseInt($(this).data('index'));
            let discountPercent = parseFloat($(this).val()) || 0;

            // Validar que el porcentaje no sea mayor a 100
            if (discountPercent > 100) {
                discountPercent = 100;
                $(this).val(100);
            }

            cart[index].item_discount = discountPercent;

            // Actualizar solo el total del item específico sin re-renderizar todo
            const item = cart[index];
            const itemSubtotal = item.is_weighted ? item.price : (item.quantity * item.price);
            const itemDiscountAmount = (itemSubtotal * discountPercent / 100);
            const totalWithDiscount = itemSubtotal - itemDiscountAmount;

            // Actualizar solo el display del total del item
            $(this).closest('.cart-item').find('.font-bold.text-green-600.text-lg').text('$' + totalWithDiscount.toFixed(2));

            // Actualizar los totales generales
            updateTotals();
        });
    }

    // Incrementar cantidad (solo productos normales)
    window.incrementQuantity = function(index) {
        const item = cart[index];
        if (!item.is_weighted && item.quantity < item.stock) {
            item.quantity++;
            renderCart();
        } else if (!item.is_weighted) {
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
        const totalQuantity = cart.reduce((sum, item) => {
            return item.is_weighted ? sum + 1 : sum + item.quantity;
        }, 0);

        // Calcular subtotal (después de descuentos individuales)
        const subtotal = cart.reduce((sum, item) => {
            const itemSubtotal = item.is_weighted ? item.price : (item.quantity * item.price);
            const itemDiscountPercent = item.item_discount || 0;
            const itemDiscountAmount = (itemSubtotal * itemDiscountPercent / 100);
            return sum + itemSubtotal - itemDiscountAmount;
        }, 0);

        // Obtener porcentaje de descuento total
        const totalDiscountPercent = parseFloat($('#total-discount-input').val()) || 0;
        const totalDiscountAmount = (subtotal * totalDiscountPercent / 100);

        // Calcular total final
        const totalAmount = Math.max(0, subtotal - totalDiscountAmount);

        $('#total-items').text(totalItems);
        $('#total-quantity').text(totalQuantity);
        $('#subtotal-amount').text('$' + subtotal.toFixed(2));
        $('#total-amount').text('$' + totalAmount.toFixed(2));
    }

    // Event listener para descuento total
    $('#total-discount-input').on('input', function() {
        updateTotals();
    });

    // Limpiar carrito
    $('#btn-clear-cart').click(function() {
        if (cart.length > 0 && confirm('¿Estás seguro de limpiar el carrito?')) {
            cart = [];
            $('#total-discount-input').val(0);
            renderCart();
            focusBarcodeInput();
        }
    });

    // Completar venta
    $('#btn-complete-sale').click(function() {
        if (cart.length === 0) return;

        // Calcular subtotal con descuentos de items (convertir porcentajes a montos)
        const subtotal = cart.reduce((sum, item) => {
            const itemSubtotal = item.is_weighted ? item.price : (item.quantity * item.price);
            const itemDiscountPercent = item.item_discount || 0;
            const itemDiscountAmount = (itemSubtotal * itemDiscountPercent / 100);
            return sum + itemSubtotal - itemDiscountAmount;
        }, 0);

        // Calcular descuento total en pesos
        const totalDiscountPercent = parseFloat($('#total-discount-input').val()) || 0;
        const totalDiscountAmount = (subtotal * totalDiscountPercent / 100);
        const totalAmount = Math.max(0, subtotal - totalDiscountAmount);

        const saleData = {
            items: cart.map(item => {
                const itemSubtotal = item.is_weighted ? item.price : (item.quantity * item.price);
                const itemDiscountPercent = item.item_discount || 0;
                const itemDiscountAmount = (itemSubtotal * itemDiscountPercent / 100);

                return {
                    product_id: item.product_id,
                    quantity: item.is_weighted ? 1 : item.quantity,
                    weight: item.is_weighted ? item.weight : null,
                    price: item.price,  // Enviar precio unitario, no el total
                    item_discount: itemDiscountAmount
                };
            }),
            total: totalAmount.toFixed(2),
            discount_amount: totalDiscountAmount.toFixed(2),
            discount_description: totalDiscountPercent > 0 ? `Descuento ${totalDiscountPercent}% aplicado en punto de venta` : null,
            payment_method: $('#payment-method').val()
        };

        const url = '{{ env('APP_URL') }}/sales/complete';

        $.post(url, saleData)
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
    let isSelecting = false;
    let isUsingPaymentMethod = false;

    $(document).on('mousedown', function(e) {
        // Detectar si se está iniciando una selección en un input
        if ($(e.target).is('input')) {
            isSelecting = true;
        }
    });

    $(document).on('mouseup', function(e) {
        // Esperar un momento para que la selección se complete
        setTimeout(function() {
            isSelecting = false;
        }, 100);
    });

    $(document).on('click', function(e) {
        // No enfocar si estamos en el modal de peso
        if (!$('#weight-modal').hasClass('hidden')) {
            return;
        }

        // No enfocar si hay texto seleccionado
        if (window.getSelection().toString().length > 0) {
            return;
        }

        // No enfocar si se está seleccionando texto
        if (isSelecting) {
            return;
        }

        // No enfocar si estamos usando el selector de método de pago
        if (isUsingPaymentMethod) {
            return;
        }

        // No enfocar si estamos escribiendo en inputs de descuento o selector de método de pago
        if ($(e.target).hasClass('item-discount-input') ||
            $(e.target).is('#total-discount-input') ||
            $(e.target).is('#payment-method') ||
            $(e.target).is('input[type="number"]') ||
            $(e.target).is('select')) {
            return;
        }

        focusBarcodeInput();
    });

    // Prevenir auto-focus al interactuar con el selector de método de pago
    $('#payment-method').on('mousedown focus click', function(e) {
        e.stopPropagation();
        isUsingPaymentMethod = true;
    });

    $('#payment-method').on('blur change', function() {
        setTimeout(function() {
            isUsingPaymentMethod = false;
        }, 200);
    });

    // Modificar la función de auto-focus para considerar el selector de pago
    const originalFocusBarcodeInput = focusBarcodeInput;
    focusBarcodeInput = function() {
        if (!isUsingPaymentMethod) {
            originalFocusBarcodeInput();
        }
    };

    focusBarcodeInput();
});
</script>
@endpush
