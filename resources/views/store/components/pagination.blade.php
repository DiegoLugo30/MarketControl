@php
    $window      = 2;
    $currentPage = $paginator->currentPage();
    $lastPage    = $paginator->lastPage();
    $winStart    = max(1, $currentPage - $window);
    $winEnd      = min($lastPage, $currentPage + $window);
@endphp

<nav class="flex items-center gap-1" aria-label="Paginación">

    {{-- Previous --}}
    @if($paginator->onFirstPage())
        <span class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-300 cursor-not-allowed">
            <i class="fas fa-chevron-left text-sm"></i>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition">
            <i class="fas fa-chevron-left text-sm"></i>
        </a>
    @endif

    {{-- First page + ellipsis --}}
    @if($winStart > 1)
        <a href="{{ $paginator->url(1) }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 text-sm font-medium transition">
            1
        </a>
        @if($winStart > 2)
            <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm select-none">…</span>
        @endif
    @endif

    {{-- Window pages --}}
    @for($i = $winStart; $i <= $winEnd; $i++)
        @if($i === $currentPage)
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-brand-500 text-white text-sm font-semibold">
                {{ $i }}
            </span>
        @else
            <a href="{{ $paginator->url($i) }}"
               class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 text-sm font-medium transition">
                {{ $i }}
            </a>
        @endif
    @endfor

    {{-- Ellipsis + last page --}}
    @if($winEnd < $lastPage)
        @if($winEnd < $lastPage - 1)
            <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm select-none">…</span>
        @endif
        <a href="{{ $paginator->url($lastPage) }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 text-sm font-medium transition">
            {{ $lastPage }}
        </a>
    @endif

    {{-- Next --}}
    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition">
            <i class="fas fa-chevron-right text-sm"></i>
        </a>
    @else
        <span class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-300 cursor-not-allowed">
            <i class="fas fa-chevron-right text-sm"></i>
        </span>
    @endif

</nav>
