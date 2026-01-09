<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS Barcode')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navegación -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-2xl font-bold">
                        <i class="fas fa-cash-register"></i> POS Barcode
                    </a>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('home') }}" class="hover:bg-blue-700 px-4 py-2 rounded transition">
                        <i class="fas fa-shopping-cart"></i> Punto de Venta
                    </a>
                    <a href="{{ route('barcode.scan') }}" class="hover:bg-blue-700 px-4 py-2 rounded transition">
                        <i class="fas fa-barcode"></i> Escanear
                    </a>
                    <a href="{{ route('products.index') }}" class="hover:bg-blue-700 px-4 py-2 rounded transition">
                        <i class="fas fa-box"></i> Productos
                    </a>
                    <a href="{{ route('sales.index') }}" class="hover:bg-blue-700 px-4 py-2 rounded transition">
                        <i class="fas fa-receipt"></i> Ventas
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mensajes de alerta -->
    @if(session('success'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} POS Barcode - Sistema de Punto de Venta</p>
        </div>
    </footer>

    <!-- jQuery (necesario para algunos scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Scripts globales -->
    <script>
        // Configurar CSRF token para peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Función helper para mostrar alertas
        window.showAlert = function(message, type = 'success') {
            const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            const alert = `
                <div class="${alertClass} border px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">${message}</span>
                </div>
            `;
            $('main').prepend(alert);

            setTimeout(() => {
                $('main .border').first().fadeOut(() => {
                    $(this).remove();
                });
            }, 3000);
        };
    </script>

    @stack('scripts')
</body>
</html>
