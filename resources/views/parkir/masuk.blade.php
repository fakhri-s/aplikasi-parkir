{{--
    Catatan pembelajaran:
    View Blade ini menampilkan tampilan halaman aplikasi. Komponen utama seperti form, tabel, dan modal dipasang di sini, lalu diberi data dari controller melalui compact() atau session().
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Kendaraan Masuk') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <div>{{ session('success') }}</div>
                    @if (session('ticket_transaction_id'))
                        <button type="button" data-open-ticket="{{ route('parkir.ticket.download', ['transaction' => session('ticket_transaction_id')]) }}" class="mt-2 inline-block font-semibold underline text-emerald-700 hover:text-emerald-800">
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

            @php
                $hasRegularType = $jenisPelanggan->contains(fn ($jenis) => strtolower($jenis->nama) === 'reguler');
            @endphp

            @if (! $hasRegularType)
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Jenis pelanggan <strong>Reguler</strong> belum ada. Tambahkan data jenis pelanggan <strong>Reguler</strong> agar transaksi parkir bisa dilakukan.
                    <a href="{{ route('management.tarif.index', ['active_tab' => 'jenis']) }}" class="ml-1 font-semibold underline">
                        Tambah sekarang
                    </a>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                {{--
                    Form ini dipakai untuk mencatat kendaraan yang baru masuk ke area parkir.
                    Input yang utama:
                    - plat_nomor: nomor polisi kendaraan yang ingin masuk.
                    - jenis_kendaraan: mobil/motor/truk supaya sistem bisa pilih tarif yang benar.
                    - area_parkir_id: area parkir tujuan (misalnya area motor atau area mobil).
                    Semua data ini dikirim ke controller ParkirController@storeMasuk untuk diproses.
                --}}
                <form method="POST" action="{{ route('parkir.masuk.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="plat_nomor" class="mb-1 block text-sm font-medium text-slate-700">Nomor Polisi</label>
                            <input id="plat_nomor" name="plat_nomor" type="text" value="{{ old('plat_nomor') }}" required
                                placeholder="Contoh: B 1234 ABC"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            <p id="vehicle-info" class="mt-2 hidden text-xs text-slate-600"></p>
                        </div>

                        <div>
                            <label for="jenis_kendaraan" class="mb-1 block text-sm font-medium text-slate-700">Jenis Kendaraan</label>
                            <select id="jenis_kendaraan" name="jenis_kendaraan" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Pilih jenis kendaraan</option>
                                <option value="mobil" {{ old('jenis_kendaraan') === 'mobil' ? 'selected' : '' }}>Mobil</option>
                                <option value="motor" {{ old('jenis_kendaraan') === 'motor' ? 'selected' : '' }}>Motor</option>
                                <option value="truk" {{ old('jenis_kendaraan') === 'truk' ? 'selected' : '' }}>Truk</option>
                            </select>
                        </div>

                        <div>
                            <label for="area_parkir_id" class="mb-1 block text-sm font-medium text-slate-700">Area Parkir</label>
                            <select id="area_parkir_id" name="area_parkir_id" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Pilih area parkir</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" data-jenis="{{ optional($area->tarif)->jenis_kendaraan }}" {{ old('area_parkir_id') == $area->id ? 'selected' : '' }}>
                                        {{ $area->nama }} - {{ $area->lokasi }}
                                        @if ($area->tarif)
                                            - {{ ucfirst($area->tarif->jenis_kendaraan) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                            Batal
                        </a>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            {{ $hasRegularType ? '' : 'disabled' }}>
                            Simpan Kendaraan Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('ticket_transaction_id'))
        <div id="ticket-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="flex h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h3 class="text-base font-semibold text-slate-800">Preview Karcis</h3>
                    <button type="button" id="close-ticket-modal" class="rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-600 hover:bg-slate-100">Tutup</button>
                </div>
                <iframe src="{{ route('parkir.ticket.download', ['transaction' => session('ticket_transaction_id')]) }}" class="h-full w-full bg-white" title="Karcis PDF"></iframe>
            </div>
        </div>
    @endif

    <script>
        const registeredVehicles = @json($registeredVehicles);
        const platInput = document.getElementById('plat_nomor');
        const vehicleTypeSelect = document.getElementById('jenis_kendaraan');
        const areaSelect = document.getElementById('area_parkir_id');
        const vehicleInfo = document.getElementById('vehicle-info');
        const ticketModal = document.getElementById('ticket-modal');
        const closeTicketModalButton = document.getElementById('close-ticket-modal');

        function filterAreaByVehicleType(selectedType) {
            const optionList = Array.from(areaSelect.options);
            let selectedValueStillValid = false;

            optionList.forEach((option) => {
                const isPlaceholder = option.value === '';
                const optionType = option.dataset.jenis || '';
                const isVisible = isPlaceholder || !selectedType || optionType === selectedType;

                option.hidden = !isVisible;
                option.disabled = !isVisible && !isPlaceholder;

                if (option.value === areaSelect.value && isVisible) {
                    selectedValueStillValid = true;
                }
            });

            if (!selectedType) {
                areaSelect.value = '';
                return;
            }

            if (!selectedValueStillValid && areaSelect.value !== '') {
                areaSelect.value = '';
            }
        }

        function updateVehicleInfo() {
            const rawValue = (platInput.value || '').trim();
            const formatted = rawValue.toUpperCase();

            if (!formatted) {
                vehicleInfo.classList.add('hidden');
                vehicleInfo.textContent = '';
                return;
            }

            const match = registeredVehicles.find((vehicle) => vehicle.plat_nomor === formatted);
            const jenisPelanggan = match ? match.jenis_pelanggan : 'Reguler';

            if (match && match.jenis_kendaraan) {
                vehicleTypeSelect.value = match.jenis_kendaraan;
            }

            filterAreaByVehicleType(vehicleTypeSelect.value);
            vehicleInfo.textContent = 'Jenis pelanggan: ' + jenisPelanggan;
            vehicleInfo.classList.remove('hidden');
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

        vehicleTypeSelect.addEventListener('change', function () {
            filterAreaByVehicleType(this.value);
        });

        platInput.addEventListener('input', updateVehicleInfo);
        filterAreaByVehicleType(vehicleTypeSelect.value || '');
        updateVehicleInfo();
    </script>
</x-app-layout>