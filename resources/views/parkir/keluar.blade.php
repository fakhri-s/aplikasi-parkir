{{--
    Catatan pembelajaran:
    View Blade ini menampilkan tampilan halaman aplikasi. Komponen utama seperti form, tabel, dan modal dipasang di sini, lalu diberi data dari controller melalui compact() atau session().
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Kendaraan Keluar') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <div>{{ session('success') }}</div>
                    @if (session('exit_ticket_transaction_id'))
                        <button type="button" data-open-ticket="{{ route('parkir.ticket.exit.download', ['transaction' => session('exit_ticket_transaction_id')]) }}" class="mt-2 inline-block font-semibold underline text-emerald-700 hover:text-emerald-800">
                            Buka karcis PDF
                        </button>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                {{--
                    Form keluar digunakan untuk menutup transaksi parkir yang sedang aktif.
                    Input yang harus diisi:
                    - plat_nomor atau nomor_karcis: identitas kendaraan yang keluar.
                    - karcis_hilang: checkbox kalau karcis hilang dan perlu denda.
                    Setelah submit, controller ParkirController@previewKeluar menghitung durasi,
                    tarif, dan total bayar untuk ditampilkan di modal. Data baru disimpan
                    setelah tombol "Proses & Simpan" dikonfirmasi.
                --}}
                <form id="exit-form" method="POST" action="{{ route('parkir.keluar.preview') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="plat_nomor" class="mb-1 block text-sm font-medium text-slate-700">Nomor Polisi / Nomor Karcis</label>
                        <input id="plat_nomor" name="plat_nomor" type="text" value="{{ old('plat_nomor') }}"
                            placeholder="Contoh: B 1234 ABC atau KRC-240901-ABC123"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    </div>

                    <label class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        <input type="checkbox" name="karcis_hilang" value="1" {{ old('karcis_hilang') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        Karcis hilang (tambahkan denda sesuai jenis pelanggan)
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" id="preview-exit-button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Lihat Rincian
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                Lihat daftar kendaraan yang masih terparkir di halaman
                <a href="{{ route('parkir.terparkir') }}" class="font-semibold text-indigo-600 underline">Data Kendaraan Terparkir</a>.
            </div>
        </div>
    </div>

    <div id="exit-preview-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Konfirmasi</p>
                    <h3 class="text-lg font-bold text-slate-800">Preview Kendaraan Keluar</h3>
                </div>
                <button type="button" id="close-exit-preview" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
            </div>

            <div class="space-y-4 p-5">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><p class="text-slate-500">Nomor Polisi</p><p id="preview-plat" class="font-semibold text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Nomor Karcis</p><p id="preview-ticket" class="font-semibold text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Jenis Kendaraan</p><p id="preview-vehicle" class="font-semibold capitalize text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Jenis Pelanggan</p><p id="preview-customer" class="font-semibold text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Area Parkir</p><p id="preview-area" class="font-semibold text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Durasi</p><p id="preview-duration" class="font-semibold text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Waktu Masuk</p><p id="preview-entry-time" class="font-semibold text-slate-800">-</p></div>
                    <div><p class="text-slate-500">Waktu Keluar</p><p id="preview-exit-time" class="font-semibold text-slate-800">-</p></div>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-600">Denda karcis hilang</span>
                    <span id="preview-fine" class="font-semibold text-slate-800">Rp 0</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-indigo-50 px-4 py-4">
                    <span class="font-semibold text-indigo-900">Total pembayaran</span>
                    <span id="preview-total" class="text-xl font-bold text-indigo-700">Rp 0</span>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
                <button type="button" id="cancel-exit-preview" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Batal</button>
                <button type="button" id="confirm-exit-button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Proses &amp; Simpan</button>
            </div>
        </div>
    </div>

    @if (session('exit_ticket_transaction_id'))
        <div id="ticket-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="flex h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h3 class="text-base font-semibold text-slate-800">Preview Karcis</h3>
                    <button type="button" id="close-ticket-modal" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
                </div>
                <iframe src="{{ route('parkir.ticket.exit.download', ['transaction' => session('exit_ticket_transaction_id')]) }}" class="h-full w-full bg-white" title="Karcis PDF"></iframe>
            </div>
        </div>
    @endif

    <script>
        const exitForm = document.getElementById('exit-form');
        const exitPreviewModal = document.getElementById('exit-preview-modal');
        const closeExitPreview = document.getElementById('close-exit-preview');
        const cancelExitPreview = document.getElementById('cancel-exit-preview');
        const confirmExitButton = document.getElementById('confirm-exit-button');
        const previewExitButton = document.getElementById('preview-exit-button');
        const ticketModal = document.getElementById('ticket-modal');
        const closeTicketModalButton = document.getElementById('close-ticket-modal');

        const formatCurrency = (amount) => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(amount);

        const closeExitPreviewModal = () => {
            if (exitPreviewModal) {
                exitPreviewModal.classList.add('hidden');
                exitPreviewModal.classList.remove('flex');
            }
        };

        if (exitForm) {
            exitForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                previewExitButton.disabled = true;
                previewExitButton.textContent = 'Menghitung...';

                try {
                    const response = await fetch(exitForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': exitForm.querySelector('input[name="_token"]').value
                        },
                        body: new FormData(exitForm)
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(Object.values(data.errors || {}).flat()[0] || 'Rincian kendaraan tidak dapat dihitung.');
                    }

                    document.getElementById('preview-plat').textContent = data.plat_nomor;
                    document.getElementById('preview-ticket').textContent = data.nomor_karcis;
                    document.getElementById('preview-vehicle').textContent = data.jenis_kendaraan;
                    document.getElementById('preview-customer').textContent = data.jenis_pelanggan;
                    document.getElementById('preview-area').textContent = data.area;
                    document.getElementById('preview-duration').textContent = `${data.durasi} menit`;
                    document.getElementById('preview-entry-time').textContent = data.waktu_masuk;
                    document.getElementById('preview-exit-time').textContent = data.waktu_keluar;
                    document.getElementById('preview-fine').textContent = formatCurrency(data.denda);
                    document.getElementById('preview-total').textContent = formatCurrency(data.total_bayar);
                    exitPreviewModal.classList.remove('hidden');
                    exitPreviewModal.classList.add('flex');
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('parking-notification', {
                        detail: { type: 'error', message: error.message }
                    }));
                } finally {
                    previewExitButton.disabled = false;
                    previewExitButton.textContent = 'Lihat Rincian';
                }
            });
        }

        [closeExitPreview, cancelExitPreview].forEach((button) => {
            if (button) {
                button.addEventListener('click', closeExitPreviewModal);
            }
        });

        if (exitPreviewModal) {
            exitPreviewModal.addEventListener('click', function (event) {
                if (event.target === exitPreviewModal) {
                    closeExitPreviewModal();
                }
            });
        }

        if (confirmExitButton && exitForm) {
            confirmExitButton.addEventListener('click', function () {
                exitForm.action = @js(route('parkir.keluar.store'));
                exitForm.submit();
            });
        }

        if (ticketModal && closeTicketModalButton) {
            closeTicketModalButton.addEventListener('click', function () {
                ticketModal.classList.add('hidden');
            });

            ticketModal.addEventListener('click', function (event) {
                if (event.target === ticketModal) {
                    ticketModal.classList.add('hidden');
                }
            });
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-open-ticket]');
            if (!trigger) {
                return;
            }

            if (ticketModal) {
                ticketModal.classList.remove('hidden');
                const iframe = ticketModal.querySelector('iframe');
                if (iframe) {
                    iframe.src = trigger.dataset.openTicket;
                }
            } else {
                window.open(trigger.dataset.openTicket, '_blank', 'noopener,noreferrer');
            }
        });
    </script>
</x-app-layout>