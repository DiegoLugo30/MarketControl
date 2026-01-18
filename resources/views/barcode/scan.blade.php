@extends('layouts.app')

@section('title', 'Escanear Código de Barras')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-barcode"></i> Escanear Código de Barras
        </h1>

        <!-- Métodos de escaneo -->
        <div class="mb-6">
            <div class="flex space-x-4">
                <button id="btn-usb" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-keyboard"></i> Lector USB
                </button>
                <button id="btn-camera" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-camera"></i> Cámara
                </button>
            </div>
        </div>

        <!-- Input para lector USB -->
        <div id="usb-scanner" class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">
                Código de Barras (Presiona ENTER después de escanear)
            </label>
            <input
                type="text"
                id="barcode-input"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Escanea o ingresa el código de barras..."
                autofocus
            >
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-info-circle"></i> El input detecta automáticamente cuando presionas ENTER
            </p>
        </div>

        <!-- Contenedor para cámara -->
        <div id="camera-scanner" class="mb-6 hidden">
            <div class="bg-gray-900 rounded-lg overflow-hidden relative" style="height: 400px;">
                <div id="camera-viewport" class="w-full h-full"></div>
                <div class="absolute top-4 right-4">
                    <button id="btn-stop-camera" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        <i class="fas fa-stop"></i> Detener
                    </button>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-info-circle"></i> Coloca el código de barras frente a la cámara
            </p>
        </div>

        <!-- Spinner de carga -->
        <div id="loading" class="hidden text-center py-4">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600"></i>
            <p class="text-gray-600 mt-2">Buscando producto...</p>
        </div>

        <!-- Resultado de la búsqueda -->
        <div id="result" class="hidden">
            <!-- Producto encontrado localmente -->
            <div id="result-found" class="hidden bg-green-50 border border-green-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-green-800 mb-4">
                    <i class="fas fa-check-circle"></i> Producto Encontrado
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Código de Barras</p>
                        <p class="font-semibold" id="found-barcode"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Nombre</p>
                        <p class="font-semibold" id="found-name"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Precio</p>
                        <p class="font-semibold text-green-600 text-xl" id="found-price"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Stock</p>
                        <p class="font-semibold" id="found-stock"></p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-600">Descripción</p>
                    <p id="found-description" class="text-gray-700"></p>
                </div>
                <div class="mt-6 flex space-x-4">
                    <a id="btn-edit" href="#" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button id="btn-new-scan" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                        <i class="fas fa-redo"></i> Nuevo Escaneo
                    </button>
                </div>
            </div>

            <!-- Producto encontrado en API -->
            <div id="result-api" class="hidden bg-yellow-50 border border-yellow-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-yellow-800 mb-4">
                    <i class="fas fa-exclamation-triangle"></i> Producto No Registrado (Datos de API)
                </h2>
                <p class="text-gray-700 mb-4">
                    El producto se encontró en la base de datos externa. Completa los datos faltantes para registrarlo:
                </p>
                <form id="form-api-create" action="{{ env('APP_URL') }}/products" method="POST">
                    @csrf
                    <input type="hidden" name="barcode" id="api-barcode">
                    <input type="hidden" name="internal_code" id="api-internal-code">

                    <!-- Tipo de Producto -->
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Tipo de Producto *
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="is_weighted" value="0" checked class="mr-2 api-type-unit">
                                <span><i class="fas fa-box"></i> Por Unidad</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="is_weighted" value="1" class="mr-2 api-type-weight">
                                <span><i class="fas fa-weight"></i> Por Peso (kg)</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold mb-1">Nombre</label>
                            <input type="text" name="name" id="api-name" class="w-full px-3 py-2 border rounded" readonly>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold mb-1">Descripción</label>
                            <textarea name="description" id="api-description" class="w-full px-3 py-2 border rounded" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <!-- Campos para Producto por Unidad -->
                    <div id="api-unit-fields" class="mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Precio Unitario *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input type="number" name="price" step="0.01" min="0" class="w-full pl-8 pr-4 py-2 border rounded" id="api-price-unit">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Stock Inicial *</label>
                                <input type="number" name="stock" min="0" class="w-full px-3 py-2 border rounded" id="api-stock-unit">
                            </div>
                        </div>
                    </div>

                    <!-- Campos para Producto Pesable -->
                    <div id="api-weight-fields" class="mt-4 hidden">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Precio por Kilogramo *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" name="price_per_kg" step="0.01" min="0" class="w-full pl-8 pr-4 py-2 border rounded" id="api-price-kg" disabled>
                                <span class="absolute right-3 top-2 text-gray-500">/kg</span>
                            </div>
                            <p class="text-sm text-blue-600 mt-2">
                                <i class="fas fa-info-circle"></i> Los productos pesables no requieren stock
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                            <i class="fas fa-save"></i> Guardar Producto
                        </button>
                        <button type="button" id="btn-cancel-api" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Producto no encontrado -->
            <div id="result-not-found" class="hidden bg-red-50 border border-red-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-red-800 mb-4">
                    <i class="fas fa-times-circle"></i> Producto No Encontrado
                </h2>
                <p class="text-gray-700 mb-4">
                    El código <strong id="notfound-barcode"></strong> no existe en la base de datos ni en fuentes externas.
                </p>
                <form id="form-manual-create" action="{{ env('APP_URL') }}/products" method="POST">
                    @csrf
                    <input type="hidden" name="barcode" id="manual-barcode">
                    <input type="hidden" name="internal_code" id="manual-internal-code">

                    <!-- Tipo de Producto -->
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Tipo de Producto *
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="is_weighted" value="0" checked class="mr-2 manual-type-unit">
                                <span><i class="fas fa-box"></i> Por Unidad</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="is_weighted" value="1" class="mr-2 manual-type-weight">
                                <span><i class="fas fa-weight"></i> Por Peso (kg)</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold mb-1">Nombre *</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border rounded" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold mb-1">Descripción</label>
                            <textarea name="description" class="w-full px-3 py-2 border rounded" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Campos para Producto por Unidad -->
                    <div id="manual-unit-fields" class="mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Precio Unitario *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input type="number" name="price" step="0.01" min="0" class="w-full pl-8 pr-4 py-2 border rounded" id="manual-price-unit">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Stock Inicial *</label>
                                <input type="number" name="stock" min="0" class="w-full px-3 py-2 border rounded" id="manual-stock-unit">
                            </div>
                        </div>
                    </div>

                    <!-- Campos para Producto Pesable -->
                    <div id="manual-weight-fields" class="mt-4 hidden">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Precio por Kilogramo *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" name="price_per_kg" step="0.01" min="0" class="w-full pl-8 pr-4 py-2 border rounded" id="manual-price-kg" disabled>
                                <span class="absolute right-3 top-2 text-gray-500">/kg</span>
                            </div>
                            <p class="text-sm text-blue-600 mt-2">
                                <i class="fas fa-info-circle"></i> Los productos pesables no requieren stock
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                            <i class="fas fa-save"></i> Crear Producto
                        </button>
                        <button type="button" id="btn-cancel-manual" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QuaggaJS para lectura de códigos de barras con cámara -->
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.4/dist/quagga.min.js"></script>

<script>
$(document).ready(function() {
    let currentMode = 'usb';
    let cameraActive = false;
    let lastScannedCode = '';
    let lastScanTime = 0;

    // Cambiar a modo USB
    $('#btn-usb').click(function() {
        currentMode = 'usb';
        $('#usb-scanner').removeClass('hidden');
        $('#camera-scanner').addClass('hidden');
        stopCamera();
        $('#barcode-input').focus();
    });

    // Cambiar a modo cámara
    $('#btn-camera').click(function() {
        currentMode = 'camera';
        $('#usb-scanner').addClass('hidden');
        $('#camera-scanner').removeClass('hidden');
        startCamera();
    });

    // Detener cámara
    $('#btn-stop-camera').click(function() {
        stopCamera();
        $('#btn-usb').click();
    });

    // Detectar ENTER en input USB
    $('#barcode-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                searchBarcode(barcode);
            }
        }
    });

    // Buscar código de barras
    function searchBarcode(barcode) {
        const url = '{{ route("barcode.search") }}';

        console.log('🔍 Iniciando búsqueda de código:', barcode);
        console.log('📋 CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
        console.log('🌐 URL:', url);

        $('#loading').removeClass('hidden');
        $('#result').addClass('hidden');
        $('#result-found, #result-api, #result-not-found').addClass('hidden');

        $.ajax({
            url: url,
            type: 'POST',
            data: { barcode: barcode },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('✅ Respuesta recibida:', response);

                $('#loading').addClass('hidden');
                $('#result').removeClass('hidden');

                if (response.found_locally) {
                    showLocalProduct(response.product);
                } else if (response.found_api) {
                    showApiProduct(response.product);
                } else {
                    showNotFound(barcode);
                }

                $('#barcode-input').val('');
            },
            error: function(xhr, status, error) {
                console.error('❌ ERROR COMPLETO:', {
                    status: status,
                    error: error,
                    statusCode: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON,
                    headers: xhr.getAllResponseHeaders()
                });

                $('#loading').addClass('hidden');

                let errorMessage = '❌ ERROR AL BUSCAR PRODUCTO\n\n';
                errorMessage += 'Código HTTP: ' + xhr.status + ' ' + xhr.statusText + '\n';
                errorMessage += 'Código escaneado: ' + barcode + '\n\n';

                // Intentar parsear respuesta JSON
                if (xhr.responseJSON) {
                    errorMessage += 'Mensaje: ' + (xhr.responseJSON.message || 'Sin mensaje') + '\n';
                    if (xhr.responseJSON.error_type) {
                        errorMessage += 'Tipo: ' + xhr.responseJSON.error_type + '\n';
                    }
                } else if (xhr.responseText && xhr.responseText.length > 0) {
                    // Si es HTML (error 500, 419, etc)
                    if (xhr.responseText.includes('<html') || xhr.responseText.includes('<!DOCTYPE')) {
                        errorMessage += 'Error HTML (ver consola para detalles)\n';
                        console.error('📄 HTML de error:', xhr.responseText);

                        // Extraer título si existe
                        const titleMatch = xhr.responseText.match(/<title>(.*?)<\/title>/i);
                        if (titleMatch) {
                            errorMessage += 'Título: ' + titleMatch[1] + '\n';
                        }
                    } else {
                        errorMessage += 'Respuesta: ' + xhr.responseText.substring(0, 300) + '\n';
                    }
                } else {
                    errorMessage += 'Sin respuesta del servidor\n';
                }

                // Casos especiales
                if (xhr.status === 419) {
                    errorMessage += '\n⚠️ Error CSRF Token - La sesión expiró\n';
                    errorMessage += 'Solución: Recarga la página (F5)';
                } else if (xhr.status === 500) {
                    errorMessage += '\n⚠️ Error interno del servidor\n';
                    errorMessage += 'Ver consola del navegador para detalles';
                } else if (xhr.status === 0) {
                    errorMessage += '\n⚠️ No se pudo conectar al servidor\n';
                    errorMessage += 'Verifica tu conexión a internet';
                }

                alert(errorMessage);
                console.error('🔴 ABRE ESTA LÍNEA PARA VER EL ERROR COMPLETO:', xhr);
            }
        });
    }

    // Mostrar producto local
    function showLocalProduct(product) {
        $('#found-barcode').text(product.barcode);
        $('#found-name').text(product.name);
        $('#found-price').text('$' + parseFloat(product.price).toFixed(2));
        $('#found-stock').text(product.stock + ' unidades');
        $('#found-description').text(product.description || 'Sin descripción');
        $('#btn-edit').attr('href', '/products/' + product.id + '/edit');
        $('#result-found').removeClass('hidden');
    }

    // Mostrar producto de API
    function showApiProduct(product) {
        $('#api-barcode').val(product.barcode);
        $('#api-internal-code').val(product.barcode); // Usar barcode como internal_code por defecto
        $('#api-name').val(product.name || 'Sin nombre');
        $('#api-description').val(product.description || '');
        $('#result-api').removeClass('hidden');
    }

    // Mostrar producto no encontrado
    function showNotFound(barcode) {
        $('#notfound-barcode').text(barcode);
        $('#manual-barcode').val(barcode);
        $('#manual-internal-code').val(barcode); // Usar barcode como internal_code por defecto
        $('#result-not-found').removeClass('hidden');
    }

    // Nuevo escaneo
    $('#btn-new-scan').click(function() {
        $('#result').addClass('hidden');
        if (currentMode === 'camera') {
            startCamera();
        } else {
            $('#barcode-input').val('').focus();
        }
    });

    $('#btn-cancel-api, #btn-cancel-manual').click(function() {
        $('#result').addClass('hidden');
        if (currentMode === 'camera') {
            startCamera();
        } else {
            $('#barcode-input').val('').focus();
        }
    });

    // Iniciar cámara con QuaggaJS
    function startCamera() {
        if (cameraActive) return;

        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#camera-viewport'),
                constraints: {
                    width: 640,
                    height: 480,
                    facingMode: "environment"
                }
            },
            decoder: {
                readers: [
                    "ean_reader",
                    "ean_8_reader",
                    "code_128_reader",
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader"
                ]
            },
            locate: true,
            locator: {
                halfSample: true,
                patchSize: "medium"
            }
        }, function(err) {
            if (err) {
                console.error('Error al iniciar QuaggaJS:', err);
                alert('Error al acceder a la cámara. Verifica los permisos.');
                $('#btn-usb').click();
                return;
            }
            console.log('QuaggaJS iniciado correctamente');
            Quagga.start();
            cameraActive = true;
        });

        // Evento cuando se detecta un código de barras
        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            const now = Date.now();

            // Prevenir lecturas duplicadas (debounce de 2 segundos)
            if (code === lastScannedCode && (now - lastScanTime) < 2000) {
                return;
            }

            lastScannedCode = code;
            lastScanTime = now;

            console.log('Código detectado:', code);

            // Vibración en dispositivos móviles
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }

            // Detener cámara y buscar producto
            stopCamera();
            searchBarcode(code);
        });
    }

    // Detener cámara
    function stopCamera() {
        if (!cameraActive) return;

        Quagga.stop();
        cameraActive = false;
        lastScannedCode = '';
        lastScanTime = 0;

        // Limpiar el viewport
        $('#camera-viewport').empty();
    }

    // ============================================
    // Toggle de campos según tipo de producto
    // ============================================

    // Para formulario de API
    function toggleApiFields() {
        const isWeighted = $('.api-type-weight').is(':checked');
        const unitFields = $('#api-unit-fields');
        const weightFields = $('#api-weight-fields');
        const priceUnit = $('#api-price-unit');
        const stockUnit = $('#api-stock-unit');
        const priceKg = $('#api-price-kg');

        if (isWeighted) {
            // Modo pesable
            unitFields.addClass('hidden');
            weightFields.removeClass('hidden');
            priceUnit.prop('required', false).prop('disabled', true).val('');
            stockUnit.prop('required', false).prop('disabled', true).val('');
            priceKg.prop('required', true).prop('disabled', false);
        } else {
            // Modo por unidad
            unitFields.removeClass('hidden');
            weightFields.addClass('hidden');
            priceUnit.prop('required', true).prop('disabled', false);
            stockUnit.prop('required', true).prop('disabled', false);
            priceKg.prop('required', false).prop('disabled', true).val('');
        }
    }

    // Para formulario manual
    function toggleManualFields() {
        const isWeighted = $('.manual-type-weight').is(':checked');
        const unitFields = $('#manual-unit-fields');
        const weightFields = $('#manual-weight-fields');
        const priceUnit = $('#manual-price-unit');
        const stockUnit = $('#manual-stock-unit');
        const priceKg = $('#manual-price-kg');

        if (isWeighted) {
            // Modo pesable
            unitFields.addClass('hidden');
            weightFields.removeClass('hidden');
            priceUnit.prop('required', false).prop('disabled', true).val('');
            stockUnit.prop('required', false).prop('disabled', true).val('');
            priceKg.prop('required', true).prop('disabled', false);
        } else {
            // Modo por unidad
            unitFields.removeClass('hidden');
            weightFields.addClass('hidden');
            priceUnit.prop('required', true).prop('disabled', false);
            stockUnit.prop('required', true).prop('disabled', false);
            priceKg.prop('required', false).prop('disabled', true).val('');
        }
    }

    // Event listeners
    $('input[name="is_weighted"]').on('change', function() {
        // Detectar qué formulario se está usando
        if ($(this).hasClass('api-type-unit') || $(this).hasClass('api-type-weight')) {
            toggleApiFields();
        } else if ($(this).hasClass('manual-type-unit') || $(this).hasClass('manual-type-weight')) {
            toggleManualFields();
        }
    });

    // Inicializar el estado de los campos cuando se muestran los formularios
    $(document).on('DOMNodeInserted', function(e) {
        if ($(e.target).is('#result-api:not(.hidden)')) {
            toggleApiFields();
        }
        if ($(e.target).is('#result-not-found:not(.hidden)')) {
            toggleManualFields();
        }
    });
});
</script>
@endpush
