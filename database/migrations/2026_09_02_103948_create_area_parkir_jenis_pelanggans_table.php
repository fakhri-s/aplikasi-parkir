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
        Schema::create('area_parkir_jenis_pelanggan', function (Blueprint $table) {
            $table->foreignUuid('area_parkir_id')->constrained('area_parkirs')->cascadeOnDelete();
            $table->foreignUuid('jenis_pelanggan_id')->constrained('jenis_pelanggans')->cascadeOnDelete();

            $table->primary(['area_parkir_id', 'jenis_pelanggan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_parkir_jenis_pelanggans');
    }
};
