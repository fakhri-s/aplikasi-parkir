<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Tarif & Pengaturan Parkir') }}
        </h2>
    </x-slot>

    @php
        $settingsMap = $settings->keyBy('key');
    @endphp

    <div class="py-8" x-data="{
        tab: '{{ old('active_tab', session('active_tab', 'tarif')) }}',
        tarifModalOpen: false,
        jenisModalOpen: false,
        tarifAction: '{{ route('management.tarif.store') }}',
        tarifMethod: 'POST',
        tarifForm: { jenis_kendaraan: 'mobil', tarif_jam_pertama: '', tarif_jam_berikutnya: '' },
        jenisAction: '{{ route('management.jenis-pelanggan.store') }}',
        jenisMethod: 'POST',
        jenisForm: { nama: '', deskripsi: '', is_gratis_parkir: false, is_parkir_flat: false, is_bebas_denda: false, status: 'aktif' },
        openTarifCreate() {
            this.tarifAction = '{{ route('management.tarif.store') }}';
            this.tarifMethod = 'POST';
            this.tarifForm = { jenis_kendaraan: 'mobil', tarif_jam_pertama: '', tarif_jam_berikutnya: '' };
            this.tarifModalOpen = true;
        },
        openTarifEdit(item) {
            this.tarifAction = '/management/tarif/' + item.id;
            this.tarifMethod = 'PUT';
            this.tarifForm = {
                jenis_kendaraan: item.jenis_kendaraan,
                tarif_jam_pertama: item.tarif_jam_pertama ?? '',
                tarif_jam_berikutnya: item.tarif_jam_berikutnya ?? ''
            };
            this.tarifModalOpen = true;
        },
        openJenisCreate() {
            this.jenisAction = '{{ route('management.jenis-pelanggan.store') }}';
            this.jenisMethod = 'POST';
            this.jenisForm = { nama: '', deskripsi: '', is_gratis_parkir: false, is_parkir_flat: false, is_bebas_denda: false, status: 'aktif' };
            this.jenisModalOpen = true;
        },
        openJenisEdit(item) {
            this.jenisAction = '/management/jenis-pelanggan/' + item.id;
            this.jenisMethod = 'PUT';
            this.jenisForm = {
                nama: item.nama,
                deskripsi: item.deskripsi ?? '',
                is_gratis_parkir: item.is_gratis_parkir,
                is_parkir_flat: item.is_parkir_flat,
                is_bebas_denda: item.is_bebas_denda,
                status: item.status
            };
            this.jenisModalOpen = true;
        }
    }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="mb-1 font-medium">Terjadi kesalahan:</p>
                    <ul class="list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                <button type="button" @click="tab = 'tarif'"
                    :class="tab === 'tarif' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition">Tarif</button>
                <button type="button" @click="tab = 'jenis'"
                    :class="tab === 'jenis' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition">Jenis Pelanggan</button>
                <button type="button" @click="tab = 'denda'"
                    :class="tab === 'denda' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition">Denda</button>
            </div>

            @include('management.tarif.partials.tarif-tab')
            @include('management.tarif.partials.jenis-tab')
            @include('management.tarif.partials.denda-tab')
        </div>

        @include('management.tarif.partials.tarif-modal')
        @include('management.tarif.partials.jenis-modal')
    </div>
</x-app-layout>