<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-md w-full text-center">
        <div class="text-red-500 mb-4">
            <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h1 class="text-5xl font-extrabold text-gray-800 mb-2">403</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Acceso denegado</h2>
        <p class="text-gray-500 mb-8">No tenés permiso para acceder a esta sección. Si creés que es un error, contactá al administrador.</p>
        <a href="{{ route('store.index') }}"
           class="inline-block bg-green-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-green-700 transition">
            ← Volver a la tienda
        </a>
    </div>
</body>
</html>
