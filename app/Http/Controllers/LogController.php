<?php

/*
 * Catatan pembelajaran
 * Controller ini menampilkan log aktivitas pengguna dan admin. Biasanya log diambil dari tabel log lalu dikirim ke view untuk ditampilkan dalam bentuk tabel atau laporan.
 * Prinsip umum: request -> validasi -> model -> response.
 */

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * Menampilkan log aktivitas user yang sedang login.
     *
     * Fungsi ini menampilkan histori tindakan yang dilakukan oleh user tertentu, seperti
     * tambah data, edit data, atau login/logout. Data log diambil dari tabel log dan
     * dipasang ke halaman log aktivitas.
     */
    public function index(): View
    {
        // Mengambil user yang sedang login untuk membatasi log hanya pada aktivitasnya.
        $user = Auth::user();

        // Mengambil log user beserta relasi user untuk ditampilkan di halaman aktivitas.
        $logs = Log::query()
            ->with('user')
            ->where('user_id', $user?->id)
            ->latest()
            ->get();

        return view('logs.index', compact('logs'));
    }

    /**
     * Menampilkan semua log aktivitas untuk admin.
     *
     * Fungsi ini biasanya dipakai di halaman admin supaya pimpinan atau operator dapat
     * melihat seluruh kejadian yang terjadi di sistem. Data log ini berguna untuk audit
     * trail dan troubleshooting.
     */
    public function adminIndex(): View
    {
        // Mengambil seluruh log beserta user untuk kebutuhan audit administrator.
        $logs = Log::query()
            ->with('user')
            ->latest()
            ->get();

        return view('logs.admin', compact('logs'));
    }
}
