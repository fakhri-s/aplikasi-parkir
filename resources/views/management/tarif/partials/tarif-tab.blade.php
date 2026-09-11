<div x-show="tab === 'tarif'" x-transition class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Daftar tarif parkir</p>
        </div>
        <button type="button" @click="openTarifCreate()"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Tambah Tarif
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                    <tr>
                        <th class="px-4 py-3">Jenis Kendaraan</th>
                        <th class="px-4 py-3">Tarif Jam Pertama</th>
                        <th class="px-4 py-3">Tarif Jam Berikutnya</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($tarifs as $tarif)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ ucfirst($tarif->jenis_kendaraan) }}
                            </td>
                            <td class="px-4 py-3">Rp {{ number_format($tarif->tarif_jam_pertama ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">Rp {{ number_format($tarif->tarif_jam_berikutnya ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        @click="openTarifEdit({ id: '{{ $tarif->id }}', jenis_kendaraan: '{{ $tarif->jenis_kendaraan }}', tarif_jam_pertama: '{{ $tarif->tarif_jam_pertama ?? 0 }}', tarif_jam_berikutnya: '{{ $tarif->tarif_jam_berikutnya ?? 0 }}' })"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-100">
                                        Edit
                                    </button>
                                    <form action="{{ route('management.tarif.destroy', $tarif) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus tarif ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="active_tab" :value="tab">
                                        <button type="submit"
                                            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 font-medium text-rose-700 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada data tarif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>