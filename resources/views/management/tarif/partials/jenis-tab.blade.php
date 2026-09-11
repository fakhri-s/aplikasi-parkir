<div x-show="tab === 'jenis'" x-transition class="space-y-6" style="display: none;">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Daftar jenis pelanggan</p>
        </div>
        <button type="button" @click="openJenisCreate()"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Tambah Jenis
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jenisPelanggan as $jenis)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $jenis->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                    {{ ucfirst($jenis->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        @click="openJenisEdit({ id: '{{ $jenis->id }}', nama: '{{ addslashes($jenis->nama) }}', deskripsi: '{{ addslashes($jenis->deskripsi ?? '') }}', is_gratis_parkir: {{ $jenis->is_gratis_parkir ? 'true' : 'false' }}, is_parkir_flat: {{ $jenis->is_parkir_flat ? 'true' : 'false' }}, is_bebas_denda: {{ $jenis->is_bebas_denda ? 'true' : 'false' }}, status: '{{ $jenis->status }}' })"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-100">
                                        Edit
                                    </button>
                                    <form action="{{ route('management.jenis-pelanggan.destroy', $jenis) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus jenis pelanggan ini?')">
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
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada jenis pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>