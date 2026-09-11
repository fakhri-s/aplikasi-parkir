<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        @include('components.sidebar')

        <div class="lg:pl-64">
            @isset($header)
                <header class="bg-gradient-to-r from-sky-500 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                    <div class="mx-auto flex items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                        <button @click="sidebarOpen = !sidebarOpen" type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 lg:hidden"
                            aria-label="Buka atau tutup menu sidebar">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>

                        <div class="min-w-0 flex-1">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <main class="min-w-0">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>