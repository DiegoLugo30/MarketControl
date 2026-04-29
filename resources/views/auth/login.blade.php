@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Bienvenido de vuelta</h1>
    <p class="text-sm text-gray-500 mb-8">Ingresá con tu cuenta para continuar.</p>

    {{-- Session status --}}
    @if (session('status'))
        <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

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
                autofocus
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
                autocomplete="current-password"
                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl bg-white
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition"
                placeholder="••••••••"
            >
            @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center">
            <input
                id="remember"
                type="checkbox"
                name="remember"
                class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500"
            >
            <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-semibold
                   py-2.5 rounded-xl transition-colors shadow-sm text-sm"
        >
            Iniciar sesión
        </button>

    </form>

@endsection

@section('footer_link')
    ¿No tenés cuenta?
    <a href="{{ route('register') }}" class="text-brand-600 hover:text-brand-700 font-semibold ml-1">
        Registrate gratis
    </a>
@endsection
