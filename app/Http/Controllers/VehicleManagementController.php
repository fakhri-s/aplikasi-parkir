<?php

/*
 * Catatan pembelajaran
 * Controller ini menangani data kendaraan yang sudah terdaftar, seperti pemilik, merk, warna, jenis kendaraan, dan nomor polisi untuk keperluan pencatatan parkir.
 * Prinsip umum: request -> validasi -> model -> response.
 */

namespace App\Http\Controllers;

use App\Models\JenisPelanggan;
use App\Models\Kendaraan;
use App\Models\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VehicleManagementController extends Controller
{
    /**
     * Menampilkan halaman manajemen kendaraan.
     *
     * Fungsi index mengambil data kendaraan yang sudah terdaftar dan data jenis pelanggan
     * agar form tambah/edit bisa menampilkan opsi yang relevan. Dengan begitu operator
     * tidak perlu menulis data kendaraan dari awal setiap kali kendaraan masuk.
     */
    public function index(): View
    {
        // Mengambil kendaraan beserta jenis pelanggannya untuk ditampilkan tanpa query tambahan di view.
        $vehicles = Kendaraan::with('jenisPelanggan')->orderBy('created_at', 'desc')->get();
        $jenisPelanggan = JenisPelanggan::orderBy('created_at', 'desc')->get();

        return view('management.vehicle.index', compact('vehicles', 'jenisPelanggan'));
    }

    /**
     * Menyimpan kendaraan baru yang sudah terdaftar.
     *
     * Input yang biasanya dipakai:
     * - pemilik: nama pemilik kendaraan.
     * - plat_nomor: nomor polisi kendaraan.
     * - jenis_kendaraan: mobil, motor, atau truk.
     * - warna: warna kendaraan.
     * - jenis_pelanggan_id: relasi dengan jenis pelanggan tertentu.
     *
     * Fungsi ini memastikan data kendaraan tetap konsisten dan bisa dipakai untuk
     * validasi saat kendaraan masuk ke parkir berikutnya.
     */
    public function store(Request $request): RedirectResponse
    {
        // Memvalidasi identitas kendaraan dan relasi jenis pelanggan sebelum disimpan.
        $validated = $request->validate([
            'pemilik' => ['required', 'string', 'max:100'],
            'plat_nomor' => ['required', 'string', 'max:20', 'unique:kendaraans,plat_nomor'],
            'jenis_kendaraan' => ['required', 'in:mobil,motor,truk'],
            'warna' => ['required', 'string', 'max:30'],
            'jenis_pelanggan_id' => ['nullable', 'uuid', 'exists:jenis_pelanggans,id'],
        ]);

        // Membuat data kendaraan terdaftar dari input yang sudah tervalidasi.
        $kendaraan = Kendaraan::create($validated);

        // Mencatat penambahan kendaraan ke log aktivitas user.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Kendaraan ditambahkan: '.$kendaraan->plat_nomor.' ('.$kendaraan->pemilik.')',
        ]);

        return redirect()->route('management.vehicle.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    /**
     * Mengubah data kendaraan yang sudah ada.
     *
     * Fungsi update dipakai saat data pemilik, nomor polisi, jenis kendaraan, atau warna
     * berubah. Tujuannya agar data kendaraan tetap akurat untuk transaksi parkir masa depan.
     */
    public function update(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        // Memvalidasi perubahan data dan mengecualikan kendaraan saat memeriksa nomor polisi unik.
        $validated = $request->validate([
            'pemilik' => ['required', 'string', 'max:100'],
            'plat_nomor' => ['required', 'string', 'max:20', 'unique:kendaraans,plat_nomor,'.$kendaraan->id],
            'jenis_kendaraan' => ['required', 'in:mobil,motor,truk'],
            'warna' => ['required', 'string', 'max:30'],
            'jenis_pelanggan_id' => ['nullable', 'uuid', 'exists:jenis_pelanggans,id'],
        ]);

        // Memperbarui data kendaraan dengan nilai yang sudah tervalidasi.
        $kendaraan->update($validated);

        // Mencatat perubahan data kendaraan ke log aktivitas.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Kendaraan diperbarui: '.$kendaraan->plat_nomor.' ('.$kendaraan->pemilik.')',
        ]);

        return redirect()->route('management.vehicle.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    /**
     * Menghapus data kendaraan dari database.
     *
     * Fungsi destroy biasanya dipanggil jika kendaraan sudah tidak berlaku lagi, misalnya
     * karena dijual atau data sudah tidak relevan. Setelah dihapus, sistem akan mencatat
     * log agar aktivitas penghapusan tetap terlihat.
     */
    public function destroy(Kendaraan $kendaraan): RedirectResponse
    {
        // Menyimpan identitas kendaraan sebelum soft delete agar log tetap informatif.
        $platNomor = $kendaraan->plat_nomor;
        $pemilik = $kendaraan->pemilik;

        // Menghapus kendaraan menggunakan mekanisme soft delete dari model.
        $kendaraan->delete();

        // Mencatat penghapusan kendaraan ke log aktivitas.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Kendaraan dihapus: '.$platNomor.' ('.$pemilik.')',
        ]);

        return redirect()->route('management.vehicle.index')->with('success', 'Kendaraan berhasil dihapus.');
    }
}