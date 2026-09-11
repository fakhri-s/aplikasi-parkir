<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
            <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                    <th class="px-4 py-3">Pemilik</th>
                    <th class="px-4 py-3">Plat Nomor</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Warna</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($vehicles as $vehicle)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $vehicle->pemilik }}</td>
                        <td class="px-4 py-3">{{ $vehicle->plat_nomor }}</td>
                        <td class="px-4 py-3">{{ ucfirst($vehicle->jenis_kendaraan) }}</td>
                        <td class="px-4 py-3">{{ $vehicle->warna }}</td>
                        <td class="px-4 py-3">{{ $vehicle->jenisPelanggan?->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button"
                                    @click="openEdit({ id: '{{ $vehicle->id }}', pemilik: '{{ addslashes($vehicle->pemilik) }}', plat_nomor: '{{ addslashes($vehicle->plat_nomor) }}', jenis_kendaraan: '{{ $vehicle->jenis_kendaraan }}', warna: '{{ addslashes($vehicle->warna) }}', jenis_pelanggan_id: '{{ $vehicle->jenis_pelanggan_id ?? '' }}' })"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-100">
                                    Edit
                                </button>
                                <form action="{{ route('management.vehicle.destroy', $vehicle) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus kendaraan ini?')">
                                    @csrf
                                    @method('DELETE')
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
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada data kendaraan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>