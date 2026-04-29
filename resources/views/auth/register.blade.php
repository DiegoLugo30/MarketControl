@extends('layouts.auth')

@section('title', 'Crear cuenta')

@section('content')

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Creá tu cuenta</h1>
    <p class="text-sm text-gray-500 mb-8">Completá el formulario para empezar a comprar.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                Nombre completo
            </label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                       @error('name') border-red-400 bg-red-50 @else border-gray-300 bg-white @enderror
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                placeholder="Juan Pérez"
            >
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                Correo electrónico
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                       @error('email') border-red-400 bg-red-50 @else border-gray-300 bg-white @enderror
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                placeholder="tu@email.com"
            >
            @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- DNI --}}
        <div>
            <label for="dni" class="block text-sm font-medium text-gray-700 mb-1.5">
                DNI
                <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input
                id="dni"
                type="text"
                name="dni"
                value="{{ old('dni') }}"
                autocomplete="off"
                class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                       @error('dni') border-red-400 bg-red-50 @else border-gray-300 bg-white @enderror
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                placeholder="12345678"
            >
            @error('dni')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                Contraseña
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl bg-white
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition"
                placeholder="Mínimo 8 caracteres"
            >
            @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                Confirmar contraseña
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl bg-white
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition"
                placeholder="Repetí tu contraseña"
            >
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-semibold
                   py-2.5 rounded-xl transition-colors shadow-sm text-sm"
        >
            Crear cuenta
        </button>

    </form>

@endsection

@section('footer_link')
    ¿Ya tenés cuenta?
    <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-semibold ml-1">
        Iniciá sesión
    </a>
@endsection
