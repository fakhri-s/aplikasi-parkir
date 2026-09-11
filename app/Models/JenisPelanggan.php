<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama', 'deskripsi', 'is_gratis_parkir', 'is_parkir_flat', 'is_bebas_denda', 'status'])]

class JenisPelanggan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'jenis_pelanggans';

    /** Satu jenis pelanggan dapat dipakai oleh banyak kendaraan. */
    public function kendaraans(): HasMany
    {
        return $this->hasMany(Kendaraan::class);
    }

    public function areaParkirs(): BelongsToMany
    {
        return $this->belongsToMany(AreaParkir::class, 'area_parkir_jenis_pelanggan');
    }

    protected function casts(): array
    {
        return [
            'is_gratis_parkir' => 'boolean',
            'is_parkir_flat' => 'boolean',
            'is_bebas_denda' => 'boolean',
        ];
    }
}