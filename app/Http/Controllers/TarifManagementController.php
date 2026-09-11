<?php

/*
 * Catatan pembelajaran
 * Controller ini mengelola tarif parkir dan daftar jenis pelanggan. Data ini sangat penting karena menentukan aturan perhitungan tarif dan validasi parkir.
 * Prinsip umum: request -> validasi -> model -> response.
 */

namespace App\Http\Controllers;

use App\Models\JenisPelanggan;
use App\Models\Log;
use App\Models\Setting;
use App\Models\Tarif;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TarifManagementController extends Controller
{
    /**
     * Menampilkan halaman utama manajemen tarif dan jenis pelanggan.
     *
     * Fungsi index menyiapkan tiga kumpulan data utama:
     * - tarif parkir: harga berdasarkan jenis kendaraan.
     * - jenis pelanggan: klasifikasi pelanggan seperti reguler, karyawan, atau member.
     * - settings: konfigurasi sistem yang memengaruhi denda dan aturan parkir.
     *
     * Data ini dipakai di halaman management agar admin bisa mengatur seluruh aturan
     * perhitungan parkir dari satu tempat.
     */
    public function index(): View
    {
        // Mengambil tarif, jenis pelanggan, dan setting untuk mengisi seluruh tab manajemen.
        $tarifs = Tarif::orderBy('created_at', 'desc')->get();
        $jenisPelanggan = JenisPelanggan::orderBy('created_at', 'desc')->get();
        $settings = Setting::orderBy('key')->get()->keyBy('key');

        return view('management.tarif.index', compact('tarifs', 'jenisPelanggan', 'settings'));
    }

    /**
     * Menyimpan tarif baru untuk jenis kendaraan tertentu.
     *
     * Input penting:
     * - jenis_kendaraan: mobil atau motor.
     * - tarif_jam_pertama: harga untuk jam pertama.
     * - tarif_jam_berikutnya: harga untuk jam berikutnya.
     *
     * Nilai ini sangat penting karena semua perhitungan saat kendaraan keluar bergantung
     * pada tarif ini. Jika tarif salah, total pembayaran juga akan salah.
     */
    public function storeTarif(Request $request): RedirectResponse
    {
        // Memastikan tarif kendaraan yang dimasukkan memiliki nilai angka yang valid.
        $validated = $request->validate([
            'jenis_kendaraan' => ['required', 'in:mobil,motor,truk'],
            'tarif_jam_pertama' => ['required', 'integer', 'min:0'],
            'tarif_jam_berikutnya' => ['required', 'integer', 'min:0'],
        ]);

        // Membuat tarif baru berdasarkan jenis kendaraan.
        $tarif = Tarif::create($validated);

        // Mencatat penambahan tarif agar perubahan harga dapat dilacak.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Tarif ditambahkan: '.$tarif->jenis_kendaraan.' | Rp '.$tarif->tarif_jam_pertama.' / jam pertama, Rp '.$tarif->tarif_jam_berikutnya.' / jam berikutnya',
        ]);

        return redirect()->route('management.tarif.index')
            ->with('success', 'Tarif berhasil ditambahkan.')
            ->with('active_tab', $request->input('active_tab', 'tarif'));
    }

    /**
     * Mengubah tarif yang sudah ada.
     *
     * Fungsi updateTarif dipanggil saat admin ingin memperbarui harga parkir,
     * misalnya karena ada perubahan kebijakan atau saat data lama perlu dikoreksi.
     */
    public function updateTarif(Request $request, Tarif $tarif): RedirectResponse
    {
        // Memvalidasi perubahan tarif sebelum memperbarui data yang sudah ada.
        $validated = $request->validate([
            'jenis_kendaraan' => ['required', 'in:mobil,motor'],
            'tarif_jam_pertama' => ['required', 'integer', 'min:0'],
            'tarif_jam_berikutnya' => ['required', 'integer', 'min:0'],
        ]);

        // Menyimpan tarif baru pada data tarif yang dipilih.
        $tarif->update($validated);

        // Mencatat perubahan tarif untuk kebutuhan audit.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Tarif diperbarui: '.$tarif->jenis_kendaraan.' | Rp '.$tarif->tarif_jam_pertama.' / jam pertama, Rp '.$tarif->tarif_jam_berikutnya.' / jam berikutnya',
        ]);

        return redirect()->route('management.tarif.index')
            ->with('success', 'Tarif berhasil diperbarui.')
            ->with('active_tab', $request->input('active_tab', 'tarif'));
    }

    /**
     * Menghapus tarif lama.
     *
     * Fungsi ini dipakai saat tarif tidak lagi relevan. Sebelum benar-benar dihapus,
     * sistem menyimpan data lama ke log agar admin bisa melihat perubahan yang terjadi.
     */
    public function destroyTarif(Request $request, Tarif $tarif): RedirectResponse
    {
        // Menyimpan nilai tarif lama agar informasi penghapusan tetap lengkap di log.
        $jenisKendaraan = $tarif->jenis_kendaraan;
        $tarifJamPertama = $tarif->tarif_jam_pertama;
        $tarifJamBerikutnya = $tarif->tarif_jam_berikutnya;

        // Menghapus tarif yang sudah tidak digunakan.
        $tarif->delete();

        // Mencatat penghapusan tarif parkir.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Tarif dihapus: '.$jenisKendaraan.' | Rp '.$tarifJamPertama.' / jam pertama, Rp '.$tarifJamBerikutnya.' / jam berikutnya',
        ]);

        return redirect()->route('management.tarif.index')
            ->with('success', 'Tarif berhasil dihapus.')
            ->with('active_tab', $request->input('active_tab', 'tarif'));
    }

    /**
     * Menyimpan data jenis pelanggan baru.
     *
     * Input utama adalah nama jenis pelanggan dan status aktif/nonaktif. Selain itu,
     * ada juga checkbox khusus seperti:
     * - is_gratis_parkir: kalau true, pelanggan tidak bayar parkir.
     * - is_parkir_flat: kalau true, tarif dihitung flat sesuai kebijakan.
     * - is_bebas_denda: kalau true, pelanggan tidak terkena denda karcis hilang.
     *
     * Nilai-nilai ini sangat penting karena memengaruhi logika pembayaran akhir saat
     * kendaraan keluar.
     */
    public function storeJenisPelanggan(Request $request): RedirectResponse
    {
        // Memvalidasi identitas dan status jenis pelanggan sebelum disimpan.
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        // Checkbox tanpa value cuma kirim "on" saat dicentang & gak kirim apa-apa saat tidak.
        // request()->boolean() otomatis handle keduanya jadi true/false, jadi gak perlu masuk rule validasi.
        // Mengubah nilai checkbox menjadi boolean agar tersimpan konsisten di database.
        $validated['is_gratis_parkir'] = $request->boolean('is_gratis_parkir');
        $validated['is_parkir_flat'] = $request->boolean('is_parkir_flat');
        $validated['is_bebas_denda'] = $request->boolean('is_bebas_denda');

        // Membuat jenis pelanggan baru dengan seluruh aturan khususnya.
        $jenisPelanggan = JenisPelanggan::create($validated);

        // Mencatat penambahan jenis pelanggan untuk audit.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Jenis pelanggan ditambahkan: '.$jenisPelanggan->nama.' ('.$jenisPelanggan->status.')',
        ]);

        return redirect()->route('management.tarif.index')
            ->with('success', 'Jenis pelanggan berhasil ditambahkan.')
            ->with('active_tab', $request->input('active_tab', 'jenis'));
    }

    /**
     * Mengubah data jenis pelanggan yang sudah dibuat.
     *
     * Fungsi ini dipanggil ketika admin menyesuaikan kebijakan pelanggan, misalnya
     * mengubah status aktif/nonaktif atau memberi hak bebas parkir/denda.
     */
    public function updateJenisPelanggan(Request $request, JenisPelanggan $jenisPelanggan): RedirectResponse
    {
        // Memvalidasi perubahan aturan dan status jenis pelanggan.
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        // Menormalkan checkbox sebelum data jenis pelanggan diperbarui.
        $validated['is_gratis_parkir'] = $request->boolean('is_gratis_parkir');
        $validated['is_parkir_flat'] = $request->boolean('is_parkir_flat');
        $validated['is_bebas_denda'] = $request->boolean('is_bebas_denda');

        // Menyimpan perubahan jenis pelanggan.
        $jenisPelanggan->update($validated);

        // Mencatat perubahan jenis pelanggan ke log.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Jenis pelanggan diperbarui: '.$jenisPelanggan->nama.' ('.$jenisPelanggan->status.')',
        ]);

        return redirect()->route('management.tarif.index')
            ->with('success', 'Jenis pelanggan berhasil diperbarui.')
            ->with('active_tab', $request->input('active_tab', 'jenis'));
    }

    /**
     * Menghapus jenis pelanggan.
     *
     * Biasanya dipakai saat registrasi pelanggan sudah tidak berlaku lagi. Data yang
     * dihapus tetap dicatat di log agar admin tahu perubahan yang terjadi.
     */
    public function destroyJenisPelanggan(Request $request, JenisPelanggan $jenisPelanggan): RedirectResponse
    {
        // Menyimpan identitas jenis pelanggan sebelum dihapus agar log tetap jelas.
        $namaJenis = $jenisPelanggan->nama;
        $statusJenis = $jenisPelanggan->status;

        // Menghapus jenis pelanggan yang tidak lagi digunakan.
        $jenisPelanggan->delete();

        // Mencatat penghapusan jenis pelanggan.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Jenis pelanggan dihapus: '.$namaJenis.' ('.$statusJenis.')',
        ]);

        return redirect()->route('management.tarif.index')
            ->with('success', 'Jenis pelanggan berhasil dihapus.')
            ->with('active_tab', $request->input('active_tab', 'jenis'));
    }
}