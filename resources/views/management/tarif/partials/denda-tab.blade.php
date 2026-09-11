<div x-show="tab === 'denda'" x-transition class="space-y-6" style="display: none;">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Pengaturan Denda</h3>
                <p class="mt-1 text-sm text-slate-500">Nilai denda karcis hilang disimpan untuk dipakai sistem parkir.</p>
            </div>
        </div>

        <form action="{{ route('management.setting.bulk') }}" method="POST" class="grid gap-4 md:grid-cols-2">
            @csrf
            <input type="hidden" name="active_tab" :value="tab">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">Denda karcis hilang (Rp)</label>
                <input type="number" min="0" name="settings[denda_karcis_hilang]"
                    value="{{ $settingsMap->get('denda_karcis_hilang')?->value ?? 0 }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div class="md:col-span-2 flex justify-end">
                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>