<?php

/*
 * Catatan pembelajaran
 * Controller ini mengelola pengaturan aplikasi seperti batas waktu grace period, tarif denda, dan konfigurasi sistem yang dipakai saat proses parkir.
 * Prinsip umum: request -> validasi -> model -> response.
 */

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingManagementController extends Controller
{
    /**
     * Menampilkan halaman pengaturan aplikasi.
     *
     * Fungsi index menyiapkan semua setting yang dipakai sistem parkir, seperti denda,
     * masa tenggang, dan aturan lain yang memengaruhi hitung biaya. Ini penting karena
     * banyak fitur parkir membacanya secara dinamis, bukan hardcode di controller.
     */
    public function index(): View
    {
        // Mengambil semua pengaturan berdasarkan nama key agar tampil konsisten di halaman admin.
        $settings = Setting::orderBy('key')->get();

        return view('management.setting.index', compact('settings'));
    }

    /**
     * Menyimpan satu setting baru atau memperbarui setting yang sudah ada.
     *
     * Input yang umum masuk:
     * - key: nama pengaturan, misalnya denda_karcis_hilang atau menit_grace_period.
     * - value: nilai pengaturan.
     * - description: keterangan singkat untuk admin.
     *
     * Karena pengaturan ini dibaca di banyak tempat, struktur key harus konsisten supaya
     * logic parkir tetap berjalan sesuai yang diinginkan.
     */
    public function store(Request $request): RedirectResponse
    {
        // Memastikan key dan nilai pengaturan memiliki format yang aman sebelum disimpan.
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        // Membuat pengaturan baru atau memperbarui pengaturan dengan key yang sama.
        $setting = Setting::updateOrCreate(
            ['key' => $validated['key']],
            [
                'value' => $validated['value'] ?? '',
                'description' => $validated['description'] ?? null,
            ],
        );

        // Mencatat perubahan pengaturan untuk kebutuhan audit aktivitas admin.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Pengaturan disimpan: ' . $setting->key . ' = ' . $setting->value,
        ]);

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Menyimpan banyak setting sekaligus dalam satu kali submit.
     *
     * Biasanya dipakai untuk form pengaturan yang banyak item, misalnya semua konfigurasi
     * parkir dalam satu halaman. Fungsi ini menyimpan semua value ke tabel settings secara
     * bersamaan agar admin tidak perlu satu per satu.
     */
    public function saveBulk(Request $request): RedirectResponse
    {
        // Mengambil kumpulan setting yang dikirim dari form pengaturan massal.
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            // Melewati key yang tidak valid agar tidak membuat data pengaturan rusak.
            if (! is_string($key) || $key === '') {
                continue;
            }

            // Menyimpan setiap key tanpa membuat duplikasi pengaturan.
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value],
            );
        }

        // Mencatat daftar pengaturan yang diubah dalam satu proses.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Mengubah pengaturan parkir: ' . json_encode(array_keys($settings)),
        ]);

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }

    /**
     * Menghapus setting tertentu.
     *
     * Fungsi ini digunakan jika sebuah pengaturan tidak lagi dipakai atau ingin dihapus
     * karena kebijakan berubah. Log dibuat supaya perubahan pengaturan terdokumentasi.
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        // Menyimpan nilai lama sebelum pengaturan dihapus agar dapat dicatat di log.
        $keySetting = $setting->key;
        $valueSetting = $setting->value;

        // Menghapus pengaturan yang tidak lagi digunakan.
        $setting->delete();

        // Mencatat penghapusan pengaturan untuk kebutuhan audit.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Pengaturan dihapus: ' . $keySetting . ' = ' . $valueSetting,
        ]);

        return redirect()->route('management.setting.index')->with('success', 'Pengaturan berhasil dihapus.');
    }
}
