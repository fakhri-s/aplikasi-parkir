{{--
    Catatan pembelajaran:
    View Blade ini menampilkan tampilan halaman aplikasi. Komponen utama seperti form, tabel, dan modal dipasang di sini, lalu diberi data dari controller melalui compact() atau session().
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

{{{--
    Sidebar navigasi.
    Di-include dari layouts/app.blade.php, yang menyediakan state Alpine
    "sidebarOpen" lewat x-data di elemen <body>.
--}}

@php
    $currentRole = Auth::check() ? Auth::user()->role : 'user';
    $isSuperAdmin = $currentRole === 'super_admin';
    $isAdmin = $isSuperAdmin || in_array($currentRole, ['admin', 'owner'], true);
    $isUser = $isSuperAdmin || $currentRole === 'user';
    $isOwner = $isSuperAdmin || $currentRole === 'owner';
@endphp

{{-- Overlay gelap di belakang sidebar, hanya muncul di mobile saat sidebar terbuka --}}
<div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
    class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" style="display:none;"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:shadow-none">

    {{-- Logo + tombol tutup (mobile) --}}
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="h-8 w-8 fill-current text-indigo-600" />
            <span class="font-bold tracking-tight text-slate-800">Parking System</span>
        </a>
        <button @click="if (window.innerWidth < 1024) { sidebarOpen = false; }" type="button"
            class="ms-auto rounded-md p-1 text-slate-400 hover:bg-slate-100 lg:hidden" aria-label="Tutup menu">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg> 
        </button>
    </div>

    {{-- Menu utama --}}
    <nav class="flex-1 space-y-2 overflow-y-auto px-3 py-4">
        <a href="{{ route('dashboard') }}" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            {{ __('Dashboard') }}
        </a>

        @if ($isAdmin)
            <div class="pt-1">
                <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400">
                    {{ __('Admin') }}
                </p>
                <div class="space-y-1">
                    <a href="{{route('logs.admin.index')}}"
                        @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5M8.25 17.25h7.5M4.5 6.75h.01M4.5 12h.01M4.5 17.25h.01" />
                        </svg>
                        {{ __('Log Aktivitas') }}
                    </a>

                    <a href="{{route('user-management.index')}}"
                        @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        {{ __('Kelola User') }}
                    </a>

                    <a href="{{route('management.tarif.index')}}"
                        @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m-3-3h6M4.5 4.5h15a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18V6a1.5 1.5 0 0 1 1.5-1.5Z" />
                        </svg>
                        {{ __('Tarif') }}
                    </a>

                    <a href="{{route('management.area.index')}}"
                        @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M3 6h18M3 18h18" />
                        </svg>
                        {{ __('Area') }}
                    </a>

                    <a href="{{route('management.vehicle.index')}}"
                        @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 7.5h9v9h-9zM4.5 4.5h15v15h-15z" />
                        </svg>
                        {{ __('Kendaraan') }}
                    </a>

                    <a href="{{route('management.setting.index')}}"
                        @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                        </svg>
                        {{ __('Setting') }}
                    </a>
                </div>
            </div>
        @endif

        @if ($isUser)
            <div class="pt-1">
                <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400">
                    {{ __('User') }}
                </p>
                <div class="space-y-1">
                    <a href="{{route('parkir.masuk')}}" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M12 4v16" />
                        </svg>
                        {{ __('Kendaraan Masuk') }}
                    </a>

                    <a href="{{route('parkir.keluar')}}" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4M12 20V4" />
                        </svg>
                        {{ __('Kendaraan Keluar') }}
                    </a>

                    <a href="{{route('parkir.terparkir')}}" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5h-16.5zM8.25 10.5h7.5M8.25 13.5h4.5" />
                        </svg>
                        {{ __('Data Kendaraan Terparkir') }}
                    </a>

                    <a href="{{route('logs.index')}}" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5M8.25 17.25h7.5M4.5 6.75h.01M4.5 12h.01M4.5 17.25h.01" />
                        </svg>
                        {{ __('Log Aktivitas Saya') }}
                    </a>
                </div>
            </div>
        @endif

        @if ($isOwner)
            <div class="pt-1">
                <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400">
                    {{ __('Owner') }}
                </p>
                <div class="space-y-1">
                    <a href="{{route('report.transaksi.index')}}" @click="if (window.innerWidth < 1024) { sidebarOpen = false; }"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 18h18M7 14V9m5 5V5m5 9v-7" />
                        </svg>
                        {{ __('Laporan') }}
                    </a>
                </div>
            </div>
        @endif
    </nav>

    {{-- Kartu user + dropdown profil/logout, nempel di bawah sidebar --}}
    <div class="border-t border-slate-200 p-3" x-data="{ open: false }" @click.outside="open = false">
        <button @click="open = !open" type="button"
            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-colors hover:bg-slate-100">
            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium text-slate-800">{{ Auth::user()->name }}</span>
                <span class="block truncate text-xs text-slate-500">{{ Auth::user()->email }}</span>
            </span>
            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="open && 'rotate-180'"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mt-1 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
            style="display:none;">
            <a href="{{route('profile.edit')}}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                {{ __('Profile') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</aside>