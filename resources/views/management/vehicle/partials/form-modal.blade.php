<div x-show="modalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div class="absolute inset-0 bg-slate-900/50" @click="modalOpen = false"></div>
    <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800" x-text="mode === 'create' ? 'Tambah Kendaraan' : 'Edit Kendaraan'"></h3>
            <button type="button" @click="modalOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form :action="formAction" method="POST">
            @csrf
            <input type="hidden" name="_method" :value="method">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Pemilik</label>
                    <input x-model="form.pemilik" type="text" name="pemilik" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Plat Nomor</label>
                    <input x-model="form.plat_nomor" type="text" name="plat_nomor" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Kendaraan</label>
                    <select x-model="form.jenis_kendaraan" name="jenis_kendaraan"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="mobil">Mobil</option>
                        <option value="motor">Motor</option>
                        <option value="truk">Truk</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Warna</label>
                    <input x-model="form.warna" type="text" name="warna" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Pelanggan</label>
                    <select x-model="form.jenis_pelanggan_id" name="jenis_pelanggan_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="">-- Tidak dipilih --</option>
                        @foreach ($jenisPelanggan as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="modalOpen = false"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
            </div>
        </form>
    </div>
</div>