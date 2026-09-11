<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">{{ __('Kelola Kendaraan') }}</h2>
    </x-slot>

    <div class="py-8" x-data="{
        modalOpen: false,
        mode: 'create',
        formAction: '{{ route('management.vehicle.store') }}',
        method: 'POST',
        form: { pemilik: '', plat_nomor: '', jenis_kendaraan: 'mobil', warna: '', jenis_pelanggan_id: '' },
        openCreate() {
            this.mode = 'create';
            this.formAction = '{{ route('management.vehicle.store') }}';
            this.method = 'POST';
            this.form = { pemilik: '', plat_nomor: '', jenis_kendaraan: 'mobil', warna: '', jenis_pelanggan_id: '' };
            this.modalOpen = true;
        },
        openEdit(item) {
            this.mode = 'edit';
            this.formAction = '/management/kendaraan/' + item.id;
            this.method = 'PUT';
            this.form = { pemilik: item.pemilik, plat_nomor: item.plat_nomor, jenis_kendaraan: item.jenis_kendaraan, warna: item.warna, jenis_pelanggan_id: item.jenis_pelanggan_id ?? '' };
            this.modalOpen = true;
        }
    }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex items-center justify-between gap-3">
                <p class="text-sm text-slate-500">Total kendaraan: {{ $vehicles->count() }}</p>
                <button type="button" @click="openCreate()"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Tambah Kendaraan
                </button>
            </div>

            @include('management.vehicle.partials.table')
        </div>

        @include('management.vehicle.partials.form-modal')
    </div>
</x-app-layout>