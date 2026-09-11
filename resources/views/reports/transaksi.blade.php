{{--
    Catatan pembelajaran:
    Halaman laporan transaksi menampilkan ringkasan data parkir dengan filter dan tombol ekspor PDF.
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Laporan Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('report.transaksi.index') }}" class="grid gap-4 md:grid-cols-4 md:items-end">
                    <div>
                        <label for="start_date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                        <input id="start_date" name="start_date" type="date" value="{{ $startDate }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    </div>

                    <div>
                        <label for="end_date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                        <input id="end_date" name="end_date" type="date" value="{{ $endDate }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end gap-3">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Tampilkan
                        </button>
                        <a href="{{ route('report.transaksi.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                            Export PDF
                        </a>
                    </div>
                </form>
            </div>

            <div class="mb-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Jumlah Transaksi</p>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $totals['count'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Pendapatan</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">Rp {{ number_format($totals['income'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Masuk</p>
                    <p class="mt-2 text-2xl font-bold text-sky-600">{{ $totals['active'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Keluar</p>
                    <p class="mt-2 text-2xl font-bold text-violet-600">{{ $totals['closed'] }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Plat</th>
                                <th class="px-4 py-3">Nomor Karcis</th>
                                <th class="px-4 py-3">Jenis Pelanggan</th>
                                <th class="px-4 py-3">Area</th>
                                <th class="px-4 py-3">Masuk</th>
                                <th class="px-4 py-3">Keluar</th>
                                <th class="px-4 py-3">Durasi</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($transactions as $index => $transaction)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $transaction->plat_nomor ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->nomor_karcis ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->jenisPelanggan?->nama ?? 'Reguler' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->areaParkir?->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->waktu_masuk?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->waktu_keluar?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->durasi !== null ? $transaction->durasi . ' menit' : '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Rp {{ number_format((int) ($transaction->total_bayar ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $transaction->status === 'keluar' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $transaction->status === 'keluar' ? 'Keluar' : 'Masuk' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada data transaksi pada rentang tanggal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>