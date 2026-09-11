{{--
    Catatan pembelajaran:
    Dashboard menampilkan ringkasan data parkir hari ini: jumlah transaksi, kendaraan masuk, pendapatan, dan area parkir yang sedang terisi.
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{--
                Kartu-kartu ini menampilkan ringkasan cepat sistem parkir:
                - Transaksi Hari Ini: total kendaraan yang tercatat hari ini.
                - Sedang Terparkir: kendaraan yang belum keluar dari area parkir.
                - Pendapatan Hari Ini: total uang yang masuk dari transaksi keluar.
                - Area Parkir: jumlah area yang aktif dan siap dipakai.
            --}}
            <div class="mb-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Transaksi Hari Ini</p>
                        <span class="rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">Today</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-800">{{ $transactionsToday }}</p>
                    <p class="mt-2 text-sm text-slate-500">Total kendaraan yang tercatat hari ini</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Sedang Terparkir</p>
                        <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Live</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-800">{{ $currentlyParked }}</p>
                    <p class="mt-2 text-sm text-slate-500">Jumlah kendaraan yang masih masuk</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Pendapatan Hari Ini</p>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Income</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-emerald-600">Rp {{ number_format((int) $incomeToday, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-500">Total dari transaksi yang keluar hari ini</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Area Parkir</p>
                        <span class="rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700">Status</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-800">{{ count($areas) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Jumlah area yang aktif</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-800">Status Area Parkir</h3>
                        <span class="text-sm text-slate-500">Kapasitas terisi</span>
                    </div>

                    <div class="space-y-4">
                        @forelse ($areas as $area)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $area['nama'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $area['lokasi'] }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-slate-700">{{ $area['terisi'] }} / {{ $area['kapasitas'] }}</p>
                                </div>

                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-sky-500 transition-all duration-300"
                                        style="width: {{ $area['persentase'] }}%">
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ $area['tersisa'] }} slot tersedia</span>
                                    <span>{{ $area['persentase'] }}%</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada area parkir.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-800">Transaksi Terbaru</h3>
                        <span class="text-xs font-medium uppercase tracking-[0.15em] text-slate-400">10</span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($recentTransactions as $transaction)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium text-slate-800">{{ $transaction->plat_nomor ?? '-' }}</p>
                                    <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-semibold {{ $transaction->status === 'keluar' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $transaction->status === 'keluar' ? 'Keluar' : 'Masuk' }}
                                    </span>
                                </div>
                                <div class="mt-2 text-xs text-slate-500">
                                    <p>{{ $transaction->jenisPelanggan?->nama ?? 'Reguler' }} • {{ $transaction->areaParkir?->nama ?? '-' }}</p>
                                    <p class="mt-1">{{ $transaction->waktu_masuk?->format('d M Y H:i') ?? '-' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada transaksi.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>