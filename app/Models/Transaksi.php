<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kendaraan_id', 'tarif_id', 'area_parkir_id', 'user_id', 'plat_nomor', 'nomor_karcis', 'jenis_kendaraan', 'jenis_pelanggan_id', 'waktu_masuk', 'waktu_keluar', 'durasi', 'denda', 'total_bayar', 'status'])]
class Transaksi extends Model
{
    use HasUuids;
      protected $table = 'transaksis';

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function jenisPelanggan(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggan::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function areaParkir(): BelongsTo
    {
        return $this->belongsTo(AreaParkir::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'waktu_masuk' => 'datetime',
            'waktu_keluar' => 'datetime',
            'durasi' => 'integer',
            'denda' => 'integer',
            'total_bayar' => 'integer',
        ];
    }
}
