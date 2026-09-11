<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Parkir System') }}</title>

    @fonts

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>

<body
    class="min-h-screen flex items-center justify-center p-6 lg:p-8 bg-gradient-to-br from-blue-50 via-sky-100 to-blue-200 dark:from-slate-900 dark:via-blue-950 dark:to-slate-900 text-slate-800 dark:text-slate-100 antialiased">

    <div class="w-full max-w-2xl text-center">

        {{-- Icon --}}
        <div
            class="w-20 h-20 mx-auto mb-8 rounded-3xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center shadow-xl shadow-blue-500/30">
            <img src="{{ asset('images/area-parkir.png') }}" alt="Logo Parkir System"
                class="w-full h-full object-contain drop-shadow-xl">
        </div>

        {{-- Badge --}}
        <span
            class="inline-flex items-center gap-2 px-4 py-1.5 mb-6 rounded-full bg-blue-100/80 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-300 text-xs font-semibold tracking-wide">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            SYSTEM PARKIR
        </span>

        {{-- Heading --}}
        <h1 class="text-4xl lg:text-6xl font-extrabold leading-tight tracking-tight mb-4">
            Selamat Datang di
            <span class="block bg-gradient-to-r from-blue-600 via-sky-500 to-cyan-400 bg-clip-text text-transparent">
                Parkir System
            </span>
        </h1>

        {{-- Subtitle --}}
        <p class="text-base lg:text-lg text-slate-600 dark:text-slate-300 max-w-xl mx-auto leading-relaxed mb-8">
            Kelola area parkir, pantau kendaraan masuk & keluar, dan atur tarif dengan mudah dalam satu platform
            terintegrasi yang cepat, aman, dan efisien.
        </p>

        {{-- CTA --}}
        @if (Route::has('login'))
            <div class="flex flex-wrap justify-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="px-8 py-3 rounded-full text-white font-semibold bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-700 hover:to-sky-600 shadow-lg shadow-blue-500/40 transition-all duration-300 hover:scale-105">
                        Masuk Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="px-8 py-3 rounded-full text-white font-semibold bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-700 hover:to-sky-600 shadow-lg shadow-blue-500/40 transition-all duration-300 hover:scale-105">
                        Masuk
                    </a>

                
                @endauth
            </div>
        @endif


    </div>

</body>

</html>
