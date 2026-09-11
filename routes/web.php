<?php

use App\Http\Controllers\AreaManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelolaUserController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ParkirController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingManagementController;
use App\Http\Controllers\TarifManagementController;
use App\Http\Controllers\VehicleManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
return view('welcome');
});

//pegawai
Route::middleware(['auth', 'role:user,admin,super_admin'])->group(function () {
    Route::get('/kendaraan-masuk', [ParkirController::class, 'masuk'])->name('parkir.masuk');
    Route::post('/kendaraan-masuk', [ParkirController::class, 'storeMasuk'])->name('parkir.masuk.store');

    Route::get('/kendaraan-keluar', [ParkirController::class, 'keluar'])->name('parkir.keluar');
    Route::post('/kendaraan-keluar/preview', [ParkirController::class, 'previewKeluar'])->name('parkir.keluar.preview');
    Route::post('/kendaraan-keluar', [ParkirController::class, 'storeKeluar'])->name('parkir.keluar.store');
    Route::get('/kendaraan-terparkir', [ParkirController::class, 'terparkir'])->name('parkir.terparkir');

    Route::get('/log-aktivitas', [LogController::class, 'index'])->name('logs.index');
    Route::get('/parkir/tiket/{transaction}', [ParkirController::class, 'previewTicket'])->name('parkir.ticket.download');
    Route::get('/parkir/tiket-keluar/{transaction}', [ParkirController::class, 'previewExitTicket'])->name('parkir.ticket.exit.download');
});

//admin
Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::prefix('management')->name('management.')->group(function () {
        Route::get('/tarif', [TarifManagementController::class, 'index'])->name('tarif.index');
        Route::post('/tarif', [TarifManagementController::class, 'storeTarif'])->name('tarif.store');
        Route::put('/tarif/{tarif}', [TarifManagementController::class, 'updateTarif'])->name('tarif.update');
        Route::delete('/tarif/{tarif}', [TarifManagementController::class, 'destroyTarif'])->name('tarif.destroy');

        Route::post('/jenis-pelanggan', [TarifManagementController::class, 'storeJenisPelanggan'])->name('jenis-pelanggan.store');
        Route::put('/jenis-pelanggan/{jenisPelanggan}', [TarifManagementController::class, 'updateJenisPelanggan'])->name('jenis-pelanggan.update');
        Route::delete('/jenis-pelanggan/{jenisPelanggan}', [TarifManagementController::class, 'destroyJenisPelanggan'])->name('jenis-pelanggan.destroy');

        Route::get('/area', [AreaManagementController::class, 'index'])->name('area.index');
        Route::post('/area', [AreaManagementController::class, 'store'])->name('area.store');
        Route::put('/area/{areaParkir}', [AreaManagementController::class, 'update'])->name('area.update');
        Route::delete('/area/{areaParkir}', [AreaManagementController::class, 'destroy'])->name('area.destroy');

        Route::get('/kendaraan', [VehicleManagementController::class, 'index'])->name('vehicle.index');
        Route::post('/kendaraan', [VehicleManagementController::class, 'store'])->name('vehicle.store');
        Route::put('/kendaraan/{kendaraan}', [VehicleManagementController::class, 'update'])->name('vehicle.update');
        Route::delete('/kendaraan/{kendaraan}', [VehicleManagementController::class, 'destroy'])->name('vehicle.destroy');

        Route::get('/setting', [SettingManagementController::class, 'index'])->name('setting.index');
        Route::post('/setting', [SettingManagementController::class, 'store'])->name('setting.store');
        Route::post('/setting/bulk', [SettingManagementController::class, 'saveBulk'])->name('setting.bulk');
        Route::delete('/setting/{setting}', [SettingManagementController::class, 'destroy'])->name('setting.destroy');
    });

    Route::prefix('user-management')->name('user-management.')->group(function () {
        Route::get('/', [KelolaUserController::class, 'index'])->name('index');
        Route::post('/', [KelolaUserController::class, 'store'])->name('store');
        Route::put('/{user}', [KelolaUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [KelolaUserController::class, 'destroy'])->name('destroy');
    });
    Route::get('/log-aktivitas/admin', [LogController::class, 'adminIndex'])->name('logs.admin.index');
});

//owner
route::middleware(['auth', 'verified', 'role:admin,super_admin,owner'])->group(function () {
    Route::get('/laporan-transaksi', [ReportController::class, 'index'])->name('report.transaksi.index');
    Route::get('/laporan-transaksi/export-pdf', [ReportController::class, 'exportPdf'])->name('report.transaksi.export');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
