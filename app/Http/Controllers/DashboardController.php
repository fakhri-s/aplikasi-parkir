<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\Transaksi;
use Illuminate\View\View;

class DashboardController extends Controller
{

public function index(): View
    {
        // Tanggal ini dipakai sebagai batas filter untuk ringkasan aktivitas hari ini.
        $today = now()->toDateString();

        // Menghitung semua transaksi yang waktu masuknya terjadi pada hari ini.
        $transactionsToday = Transaksi::query()
            ->whereDate('waktu_masuk', $today)
            ->count();

        // Menghitung kendaraan yang sudah masuk tetapi belum memiliki waktu keluar.
        $currentlyParked = Transaksi::query()
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->count();

        // Menjumlahkan pembayaran dari kendaraan yang sudah keluar pada hari ini.
        $incomeToday = Transaksi::query()
            ->where('status', 'keluar')
            ->whereDate('waktu_keluar', $today)
            ->sum('total_bayar');

        // Mengambil area dan tarifnya, lalu menghitung jumlah kendaraan per area.
        $areas = AreaParkir::query()
            ->with('tarif')
            ->get()
            ->map(function ($area) {
                // Menghitung kendaraan aktif pada setiap area parkir.
                $occupied = Transaksi::query()
                    ->where('area_parkir_id', $area->id)
                    ->where('status', 'masuk')
                    ->whereNull('waktu_keluar')
                    ->count();

                // Mengubah jumlah terisi menjadi persentase kapasitas yang aman ditampilkan.
                $capacity = max(1, (int) $area->kapasitas);
                $percent = min(100, (int) round(($occupied / $capacity) * 100));

                return [
                    'id' => $area->id,
                    'nama' => $area->nama,
                    'lokasi' => $area->lokasi,
                    'kapasitas' => $capacity,
                    'terisi' => $occupied,
                    'tersisa' => max(0, $capacity - $occupied),
                    'persentase' => $percent,
                ];
            });

        // Menampilkan maksimal sepuluh transaksi terakhir di panel dashboard.
        $recentTransactions = Transaksi::query()
            ->with(['areaParkir', 'jenisPelanggan'])
            ->latest('waktu_masuk')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'transactionsToday',
            'currentlyParked',
            'incomeToday',
            'areas',
            'recentTransactions',
        ));
    }
}