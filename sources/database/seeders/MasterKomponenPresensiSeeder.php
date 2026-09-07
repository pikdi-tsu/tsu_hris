<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\MasterKomponenPresensi;

class MasterKomponenPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $komponens = [
            [
                'nama_komponen' => 'Uang Transport',
                'kode_komponen' => 'TRANSPORT',
                'kategori' => 'transport',
                'nominal' => 20000.00,
                'satuan' => 'per_kehadiran',
                'keterangan' => 'Uang transport harian per kehadiran absensi valid',
                'is_active' => 'Y',
            ],
        ];

        foreach ($komponens as $item) {
            MasterKomponenPresensi::updateOrCreate(
                ['kode_komponen' => $item['kode_komponen']],
                [
                    'id' => Str::uuid()->toString(),
                    'nama_komponen' => $item['nama_komponen'],
                    'kategori' => $item['kategori'],
                    'nominal' => $item['nominal'],
                    'satuan' => $item['satuan'],
                    'keterangan' => $item['keterangan'],
                    'is_active' => $item['is_active'],
                ]
            );
        }
    }
}
