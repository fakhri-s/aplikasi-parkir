{{--
    Catatan pembelajaran:
    View Blade ini menampilkan tampilan halaman aplikasi. Komponen utama seperti form, tabel, dan modal dipasang di sini, lalu diberi data dari controller melalui compact() atau session().
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Kelola Area') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{
        modalOpen: false,
        mode: 'create',
        formAction: '{{ route('management.area.store') }}',
        method: 'POST',
        form: { nama: '', lokasi: '', kapasitas: '', tarif_id: '', jenis_pelanggan_ids: [] },
        openCreate() {
            this.mode = 'create';
            this.formAction = '{{ route('management.area.store') }}';
            this.method = 'POST';
            this.form = { nama: '', lokasi: '', kapasitas: '', tarif_id: '', jenis_pelanggan_ids: [] };
            this.modalOpen = true;
        },
        openEdit(item) {
            this.mode = 'edit';
            this.formAction = '/management/area/' + item.id;
            this.method = 'PUT';
            this.form = {
                nama: item.nama,
                lokasi: item.lokasi,
                kapasitas: item.kapasitas,
                tarif_id: item.tarif_id ?? '',
                jenis_pelanggan_ids: Array.isArray(item.jenis_pelanggan_ids) ? item.jenis_pelanggan_ids : []
            };
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
                <div>
                    <p class="text-sm text-slate-500">Total area: {{ $areas->count() }}</p>
                </div>
                <button type="button" @click="openCreate()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Tambah Area
                </button>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Kapasitas</th>
                                <th class="px-4 py-3">Tarif</th>
                                <th class="px-4 py-3">Jenis Pengguna</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($areas as $area)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $area->nama }}</td>
                                    <td class="px-4 py-3">{{ $area->lokasi }}</td>
                                    <td class="px-4 py-3">{{ $area->kapasitas }}</td>
                                    <td class="px-4 py-3">{{ $area->tarif ? ucfirst($area->tarif->jenis_kendaraan) . ' / Rp ' . number_format($area->tarif->tarif_jam_pertama, 0, ',', '.') . ' / Rp ' . number_format($area->tarif->tarif_jam_berikutnya, 0, ',', '.') : '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($area->jenisPelanggans->isNotEmpty())
                                            {{ $area->jenisPelanggans->pluck('nama')->implode(', ') }}
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="openEdit({ id: '{{ $area->id }}', nama: '{{ addslashes($area->nama) }}', lokasi: '{{ addslashes($area->lokasi) }}', kapasitas: '{{ $area->kapasitas }}', tarif_id: '{{ $area->tarif_id ?? '' }}', jenis_pelanggan_ids: @js($area->jenisPelanggans->pluck('id')->all()) })" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-100">
                                                Edit
                                            </button>
                                            <form action="{{ route('management.area.destroy', $area) }}" method="POST" onsubmit="return confirm('Yakin hapus area ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 font-medium text-rose-700 hover:bg-rose-100">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada area parkir.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="modalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            {{--
                Modal area dipakai untuk menambah atau mengubah area parkir.
                Input yang penting:
                - nama: nama area seperti Gedung A atau Lapangan A.
                - lokasi: penempatan lokasi area.
                - kapasitas: jumlah kendaraan maksimal.
                - tarif_id: tarif yang berlaku di area tersebut.
                - jenis_pelanggan_ids: pelanggan yang boleh masuk ke area tertentu.
            --}}
            <div class="absolute inset-0 bg-slate-900/50" @click="modalOpen = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Area' : 'Edit Area'"></h3>
                    <button type="button" @click="modalOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="formAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" :value="method">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Area</label>
                            <input x-model="form.nama" type="text" name="nama" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Lokasi</label>
                            <input x-model="form.lokasi" type="text" name="lokasi" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kapasitas</label>
                            <input x-model="form.kapasitas" type="number" min="1" name="kapasitas" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tarif</label>
                            <select x-model="form.tarif_id" name="tarif_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="">-- Tidak dipilih --</option>
                                @foreach ($tarifs as $tarif)
                                    <option value="{{ $tarif->id }}">{{ ucfirst($tarif->jenis_kendaraan) }} - Rp {{ number_format($tarif->tarif_jam_pertama, 0, ',', '.') }} / Rp {{ number_format($tarif->tarif_jam_berikutnya, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Pengguna yang Bisa Masuk Area Ini</label>
                            <div class="grid gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2">
                                @foreach ($JenisPelanggans as $jenis)
                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                        <input
                                            type="checkbox"
                                            name="jenis_pelanggan_ids[]"
                                            value="{{ $jenis->id }}"
                                            :checked="form.jenis_pelanggan_ids.includes('{{ $jenis->id }}')"
                                            @change="
                                                const id = '{{ $jenis->id }}';
                                                if ($event.target.checked) {
                                                    if (!form.jenis_pelanggan_ids.includes(id)) {
                                                        form.jenis_pelanggan_ids.push(id);
                                                    }
                                                } else {
                                                    form.jenis_pelanggan_ids = form.jenis_pelanggan_ids.filter((item) => item !== id);
                                                }
                                            "
                                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <span>{{ $jenis->nama }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="modalOpen = false" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>