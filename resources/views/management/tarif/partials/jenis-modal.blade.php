<div x-show="jenisModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div class="absolute inset-0 bg-slate-900/50" @click="jenisModalOpen = false"></div>
    <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">Jenis Pelanggan</h3>
            <button type="button" @click="jenisModalOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form :action="jenisAction" method="POST">
            @csrf
            <input type="hidden" name="_method" :value="jenisMethod">
            <input type="hidden" name="active_tab" :value="tab">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                    <input x-model="jenisForm.nama" type="text" name="nama" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea x-model="jenisForm.deskripsi" name="deskripsi" rows="3"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select x-model="jenisForm.status" name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <span class="text-sm font-medium text-slate-700">Gratis Parkir</span>
                    <input x-model="jenisForm.is_gratis_parkir" type="checkbox" name="is_gratis_parkir"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <span class="text-sm font-medium text-slate-700">Parkir Flat</span>
                    <input x-model="jenisForm.is_parkir_flat" type="checkbox" name="is_parkir_flat"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <span class="text-sm font-medium text-slate-700">Bebas Denda</span>
                    <input x-model="jenisForm.is_bebas_denda" type="checkbox" name="is_bebas_denda"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="jenisModalOpen = false"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
            </div>
        </form>
    </div>
</div>