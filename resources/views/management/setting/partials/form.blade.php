<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-800">Pengaturan Sistem</h3>
        <p class="mt-1 text-sm text-slate-500">Kelola menit grace period dan menit tarif setengah jam.</p>
    </div>

    <form action="{{ route('management.setting.bulk') }}" method="POST" class="grid gap-4 md:grid-cols-2">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Menit grace period</label>
            <input type="number" min="0" name="settings[menit_grace_period]"
                value="{{ $settingsMap->get('menit_grace_period')?->value ?? 0 }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Menit tarif setengah</label>
            <input type="number" min="0" name="settings[menit_tarif_setengah]"
                value="{{ $settingsMap->get('menit_tarif_setengah')?->value ?? 0 }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>

        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>