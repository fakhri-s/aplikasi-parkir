{{--
    Catatan pembelajaran:
    View Blade ini menampilkan tampilan halaman aplikasi. Komponen utama seperti form, tabel, dan modal dipasang di sini, lalu diberi data dari controller melalui compact() atau session().
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Data Kendaraan Terparkir') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-3">
                <p class="text-sm text-slate-600">Daftar kendaraan yang masih masuk dan belum keluar.</p>
                <a href="{{ route('parkir.keluar') }}"
                    class="rounded-lg border border-slate-200 bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Kembali ke kendaraan keluar
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{--
                    Tabel ini menampilkan kendaraan yang saat ini masih terparkir dan belum keluar.
                    Kolom yang penting:
                    - Nomor Polisi: identitas kendaraan.
                    - Nomor Karcis: bukti masuk kendaraan.
                    - Jenis Pelanggan: status pelanggan seperti reguler/karyawan/member.
                    - Area: area parkir tempat kendaraan masuk.
                    - Aksi: tombol untuk membuka preview tiket PDF.
                --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                            <tr>
                                <th class="px-4 py-3">Nomor Polisi</th>
                                <th class="px-4 py-3">Nomor Karcis</th>
                                <th class="px-4 py-3">Jenis Pelanggan</th>
                                <th class="px-4 py-3">Jenis Kendaraan</th>
                                <th class="px-4 py-3">Area</th>
                                <th class="px-4 py-3">Waktu Masuk</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($activeTransactions as $transaction)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $transaction->plat_nomor }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">
                                        {{ $transaction->nomor_karcis ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->jenisPelanggan?->nama ?? 'Reguler' }}</td>
                                    <td class="px-4 py-3">
                                        {{ $transaction->jenis_kendaraan ?? ($transaction->kendaraan?->jenis_kendaraan ?? 'Tidak diketahui') }}
                                    </td>
                                    <td class="px-4 py-3">{{ $transaction->areaParkir?->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $transaction->waktu_masuk?->format('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <button type="button"
                                            data-open-ticket="{{ route('parkir.ticket.download', $transaction) }}"
                                            class="rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                            Lihat Tiket
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada kendaraan
                                        yang sedang parkir.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="ticket-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div
            class="flex h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h3 class="text-base font-semibold text-slate-800">Preview Tiket Parkir</h3>
                <button type="button" id="close-ticket-modal"
                    class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
            </div>
            <iframe id="ticket-frame" src="" class="h-full w-full bg-white" title="Tiket Parkir"></iframe>
        </div>
    </div>

    <script>
        const ticketModal = document.getElementById('ticket-modal');
        const ticketFrame = document.getElementById('ticket-frame');
        const closeTicketModalButton = document.getElementById('close-ticket-modal');

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-open-ticket]');
            if (!trigger) {
                return;
            }

            if (ticketModal && ticketFrame) {
                ticketFrame.src = trigger.dataset.openTicket;
                ticketModal.classList.remove('hidden');
                ticketModal.classList.add('flex');
            }
        });

        if (closeTicketModalButton && ticketModal) {
            closeTicketModalButton.addEventListener('click', function() {
                ticketModal.classList.add('hidden');
                ticketModal.classList.remove('flex');
                ticketFrame.src = '';
            });
        }

        if (ticketModal) {
            ticketModal.addEventListener('click', function(event) {
                if (event.target === ticketModal) {
                    ticketModal.classList.add('hidden');
                    ticketModal.classList.remove('flex');
                    ticketFrame.src = '';
                }
            });
        }
    </script>
</x-app-layout>