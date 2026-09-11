<?php

namespace Database\Seeders;

use App\Models\AreaParkir;
use App\Models\JenisPelanggan;
use App\Models\Kendaraan;
use App\Models\Setting;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'superadmin@parking.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
            ],
        );

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $jenisPelanggan = [
            ['nama' => 'Reguler', 'status' => 'aktif', 'is_gratis_parkir' => false, 'is_parkir_flat' => false, 'is_bebas_denda' => false],
            ['nama' => 'Karyawan', 'status' => 'aktif', 'is_gratis_parkir' => false, 'is_parkir_flat' => true, 'is_bebas_denda' => false],
            ['nama' => 'Member', 'status' => 'aktif', 'is_gratis_parkir' => false, 'is_parkir_flat' => false, 'is_bebas_denda' => true],
        ];

        $jenisPelangganMap = [];
        foreach ($jenisPelanggan as $item) {
            $jenis = JenisPelanggan::query()->firstOrCreate(
                ['nama' => $item['nama']],
                [
                    'deskripsi' => $item['nama'],
                    'status' => $item['status'],
                    'is_gratis_parkir' => $item['is_gratis_parkir'],
                    'is_parkir_flat' => $item['is_parkir_flat'],
                    'is_bebas_denda' => $item['is_bebas_denda'],
                ],
            );

            $jenisPelangganMap[$item['nama']] = $jenis;
        }

        $motorTarif = Tarif::query()->firstOrCreate(
            ['jenis_kendaraan' => 'motor'],
            ['tarif_jam_pertama' => 2000, 'tarif_jam_berikutnya' => 1000],
        );

        $mobilTarif = Tarif::query()->firstOrCreate(
            ['jenis_kendaraan' => 'mobil'],
            ['tarif_jam_pertama' => 5000, 'tarif_jam_berikutnya' => 3000],
        );

        Setting::updateOrCreate(['key' => 'denda_karcis_hilang'], ['value' => '30000']);
        Setting::updateOrCreate(['key' => 'menit_grace_period'], ['value' => '30']);
        Setting::updateOrCreate(['key' => 'menit_tarif_setengah'], ['value' => '60']);

        $gedungA = AreaParkir::query()->firstOrCreate(
            ['nama' => 'Gedung A'],
            [
                'lokasi' => 'Lantai 1',
                'kapasitas' => 50,
                'tarif_id' => $motorTarif->id,
            ],
        );

        $lapanganA = AreaParkir::query()->firstOrCreate(
            ['nama' => 'Lapangan A'],
            [
                'lokasi' => 'Lapangan parkir luar',
                'kapasitas' => 50,
                'tarif_id' => $mobilTarif->id,
            ],
        );

        $gedungKaryawan = AreaParkir::query()->firstOrCreate(
            ['nama' => 'Gedung Parkir Karyawan'],
            [
                'lokasi' => 'Blok Karyawan',
                'kapasitas' => 40,
                'tarif_id' => $motorTarif->id,
            ],
        );

        $gedungA->jenisPelanggans()->syncWithoutDetaching([
            $jenisPelangganMap['Reguler']->id,
            $jenisPelangganMap['Karyawan']->id,
            $jenisPelangganMap['Member']->id,
        ]);

        $lapanganA->jenisPelanggans()->syncWithoutDetaching([
            $jenisPelangganMap['Reguler']->id,
            $jenisPelangganMap['Member']->id,
        ]);

        $gedungKaryawan->jenisPelanggans()->syncWithoutDetaching([
            $jenisPelangganMap['Karyawan']->id,
            $jenisPelangganMap['Reguler']->id,
        ]);

        Kendaraan::query()->firstOrCreate(
            ['plat_nomor' => 'D123OJI'],
            [
                'pemilik' => 'Oji',
                'jenis_kendaraan' => 'motor',
                'warna' => 'Hitam',
                'jenis_pelanggan_id' => $jenisPelangganMap['Karyawan']->id,
            ],
        );

        Kendaraan::query()->firstOrCreate(
            ['plat_nomor' => 'D888KOI'],
            [
                'pemilik' => 'Ko Ahong',
                'jenis_kendaraan' => 'mobil',
                'warna' => 'Hitam',
                'jenis_pelanggan_id' => $jenisPelangganMap['Member']->id,
            ],
        );
    }
}