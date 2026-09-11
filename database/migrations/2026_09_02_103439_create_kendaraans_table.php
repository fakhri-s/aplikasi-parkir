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
    Schema::create('kendaraans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pemilik', 100);
            $table->string('plat_nomor', 20)->unique();
            $table->enum('jenis_kendaraan', ['mobil', 'motor', 'truk']);
            $table->string('warna', 30);
            // Nullable agar kendaraan tetap dapat disimpan saat jenis pelanggan dihapus.
            $table->foreignUuid('jenis_pelanggan_id')->nullable()
                ->constrained('jenis_pelanggans')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraans');
    }
};
