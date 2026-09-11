<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // FK histori dibuat nullable agar penghapusan data master tidak
            // ikut menghapus transaksi yang sudah menjadi bukti pembayaran.
            $table->foreignUuid('kendaraan_id')->nullable()->constrained('kendaraans')->nullOnDelete();
            $table->foreignUuid('tarif_id')->nullable()->constrained('tarifs')->nullOnDelete();
            $table->foreignUuid('area_parkir_id')->nullable()->constrained('area_parkirs')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('plat_nomor', 20)->nullable();
            $table->string('nomor_karcis', 50)->nullable()->unique();
            $table->enum('jenis_kendaraan', ['mobil', 'motor', 'truk'])->nullable();
            $table->foreignUuid('jenis_pelanggan_id')->nullable()->constrained('jenis_pelanggans')->nullOnDelete();
            $table->datetime('waktu_masuk');
            $table->datetime('waktu_keluar')->nullable();
            $table->unsignedInteger('durasi')->nullable();
            $table->unsignedInteger('denda')->nullable();
            $table->unsignedInteger('total_bayar')->nullable();
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
