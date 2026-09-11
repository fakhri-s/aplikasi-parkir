{{--
    Catatan pembelajaran:
    Versi log aktivitas untuk admin menampilkan aktivitas yang lebih luas, termasuk log sistem dan user-level.
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Log Aktivitas Admin') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                Menampilkan semua aktivitas dari seluruh user di aplikasi.
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-slate-800">{{ $log->action }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada log aktivitas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>  
            </div>
        </div>
    </div>
</x-app-layout>