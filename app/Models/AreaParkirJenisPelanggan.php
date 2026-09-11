<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['area_parkir_id', 'jenis_pelanggan_id'])]
class AreaParkirJenisPelanggan extends Model
{
    use HasUuids;

    protected $table = 'area_jenis_parkir_pelanggans';
}
