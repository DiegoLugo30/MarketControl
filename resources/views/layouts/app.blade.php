<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Arima Store')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos personalizados para alertas -->
    <style>
        /* Asegurar que las alertas de Bootstrap estén sobre todo */
        #alert-container {
            z-index: 9999 !important;
        }

        /* Animación de entrada para las alertas */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        #alert-container .alert {
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        /* Prevenir conflictos entre Bootstrap y Tailwind */
        .alert {
            position: relative;
            padding: 1rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navegación -->
    <nav class="bg-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ env('APP_URL') }}/" class="text-2xl font-bold">
                    <i class="fas fa-cash-register"></i> Arima Store
                    </a>
                </div>
                <div class="flex space-x-4 items-center">
                    <a href="{{ env('APP_URL') }}/" class="hover:bg-green-700 px-4 py-2 rounded transition">
                    <i class="fas fa-shopping-cart"></i> Punto de Venta
                    </a>
                    <a href="{{ env('APP_URL') }}/barcode/scan" class="hover:bg-green-700 px-4 py-2 rounded transition">
                        <i class="fas fa-barcode"></i> Escanear
                    </a>
                    <a href="{{ env('APP_URL') }}/products" class="hover:bg-green-700 px-4 py-2 rounded transition">
                    <i class="fas fa-box"></i> Productos
                    </a>
                    <a href="{{ env('APP_URL') }}/sales" class="hover:bg-green-700 px-4 py-2 rounded transition">
                    <i class="fas fa-receipt"></i> Ventas
                    </a>
                    <a href="{{ env('APP_URL') }}/finances" class="hover:bg-green-700 px-4 py-2 rounded transition">
                    <i class="fas fa-chart-line"></i> Finanzas
                    </a>

                    <!-- Divisor -->
                    <div class="border-l border-green-500 h-8"></div>

                    <!-- Selector de Sucursal -->
                    @include('components.branch-selector')
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenedor de alertas Bootstrap -->
    <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; max-width: 400px;">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Advertencia:</strong> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Información:</strong> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Arima Store - Sistema de Punto de Venta</p>
        </div>
    </footer>

    <!-- jQuery (necesario para algunos scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts globales -->
    <script>
        // Configurar CSRF token para peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Auto-cerrar alertas después de 5 segundos
        $(document).ready(function() {
            const alerts = $('#alert-container .alert');

            alerts.each(function() {
                const alert = $(this);

                // Auto-cerrar después de 5 segundos
                setTimeout(function() {
                    // Usar la API de Bootstrap para cerrar la alerta con animación
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert[0]);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Función helper para mostrar alertas dinámicamente
        window.showAlert = function(message, type = 'success') {
            const iconMap = {
                'success': 'fa-check-circle',
                'danger': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };

            const titleMap = {
                'success': '¡Éxito!',
                'danger': 'Error:',
                'warning': 'Advertencia:',
                'info': 'Información:'
            };

            const icon = iconMap[type] || 'fa-info-circle';
            const title = titleMap[type] || 'Mensaje:';

            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon} me-2"></i>
                    <strong>${title}</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            $('#alert-container').append(alertHtml);

            // Auto-cerrar después de 5 segundos
            const newAlert = $('#alert-container .alert').last();
            setTimeout(function() {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(newAlert[0]);
                bsAlert.close();
            }, 5000);
        };
    </script>

    @stack('scripts')
</body>
</html>
