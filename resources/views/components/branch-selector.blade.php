@php
    $activeBranch = $activeBranch ?? \App\Models\Branch::main();
    $allBranches = \App\Models\Branch::active()->orderBy('name')->get();
@endphp

<!-- Selector de Sucursal -->
<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Botón del selector -->
    <button
        @click="open = !open"
        class="flex items-center space-x-2 bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg transition"
        type="button"
    >
        <!-- Icono de sucursal -->
        <i class="fas fa-building text-white"></i>

        <!-- Nombre de sucursal activa -->
        <span class="text-white font-semibold" id="active-branch-name">
            {{ $activeBranch ? $activeBranch->name : 'Sin sucursal' }}
        </span>

        <!-- Icono de flecha -->
        <i class="fas fa-chevron-down text-white text-sm" :class="{ 'rotate-180': open }"></i>
    </button>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
        style="display: none;"
    >
        <!-- Header del dropdown -->
        <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Seleccionar Sucursal</p>
        </div>

        <!-- Lista de sucursales -->
        <div class="py-1 max-h-64 overflow-y-auto">
            @forelse($allBranches as $branch)
                <button
                    type="button"
                    onclick="changeBranch({{ $branch->id }}, '{{ $branch->name }}')"
                    class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center justify-between group {{ $activeBranch && $activeBranch->id === $branch->id ? 'bg-blue-50' : '' }}"
                >
                    <div class="flex items-center space-x-3">
                        @if($branch->is_main)
                            <i class="fas fa-star text-yellow-500"></i>
                        @else
                            <i class="fas fa-building text-gray-400 group-hover:text-gray-600"></i>
                        @endif
                        <span class="text-gray-800 group-hover:text-gray-900 {{ $activeBranch && $activeBranch->id === $branch->id ? 'font-semibold' : '' }}">
                            {{ $branch->name }}
                        </span>
                    </div>
                    @if($activeBranch && $activeBranch->id === $branch->id)
                        <i class="fas fa-check text-blue-600"></i>
                    @endif
                </button>
            @empty
                <div class="px-4 py-3 text-center text-gray-500 text-sm">
                    <i class="fas fa-exclamation-circle mb-2"></i>
                    <p>No hay sucursales disponibles</p>
                </div>
            @endforelse
        </div>

        <!-- Divisor -->
        <div class="border-t border-gray-200"></div>

        <!-- Opción de gestión -->
        <div class="py-1">
            <a
                href="{{ env('APP_URL') }}/branche"
                class="w-full text-left px-4 py-2 hover:bg-gray-100 transition flex items-center space-x-3 text-gray-700 hover:text-gray-900"
            >
                <i class="fas fa-cog"></i>
                <span>Gestionar Sucursales</span>
            </a>
        </div>
    </div>
</div>

<!-- Script para cambiar sucursal -->
<script>
function changeBranch(branchId, branchName) {
    // Mostrar indicador de carga
    const branchNameElement = document.getElementById('active-branch-name');
    const originalText = branchNameElement.textContent;
    branchNameElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';

    // Hacer petición AJAX
    fetch('{{ route("branches.set-active") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ branch_id: branchId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar nombre en UI
            branchNameElement.textContent = branchName;

            // Mostrar notificación de éxito
            showNotification('✓ ' + data.message, 'success');

            // Recargar página después de 500ms para reflejar cambios
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            branchNameElement.textContent = originalText;
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        branchNameElement.textContent = originalText;
        console.error('Error:', error);
        showNotification('Error al cambiar de sucursal', 'error');
    });
}

// Función para mostrar notificaciones
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    const container = document.getElementById('alert-container');
    if (container) {
        container.insertAdjacentHTML('beforeend', alertHTML);

        // Auto-remover después de 3 segundos
        setTimeout(() => {
            const alerts = container.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[0].remove();
            }
        }, 3000);
    }
}
</script>

<!-- Alpine.js para el dropdown (si no está ya incluido) -->
@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
