<div x-show="tarifModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div class="absolute inset-0 bg-slate-900/50" @click="tarifModalOpen = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">Tarif</h3>
            <button type="button" @click="tarifModalOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form :action="tarifAction" method="POST">
            @csrf
            <input type="hidden" name="_method" :value="tarifMethod">
            <input type="hidden" name="active_tab" :value="tab">
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Kendaraan</label>
                    <select x-model="tarifForm.jenis_kendaraan" name="jenis_kendaraan"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="mobil">Mobil</option>
                        <option value="motor">Motor</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tarif Jam Pertama (Rp)</label>
                    <input x-model="tarifForm.tarif_jam_pertama" type="number" min="0" name="tarif_jam_pertama"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tarif Jam Berikutnya (Rp)</label>
                    <input x-model="tarifForm.tarif_jam_berikutnya" type="number" min="0" name="tarif_jam_berikutnya"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="tarifModalOpen = false"
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
            </div>
        </form>
    </div>
</div>  