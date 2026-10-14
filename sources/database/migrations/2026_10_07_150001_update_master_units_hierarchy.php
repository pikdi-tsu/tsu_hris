<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Dapatkan atau Buat Unit SPI (Satuan Pengawas Internal)
        $spi = DB::table('master_units')
            ->where('nama_unit', 'LIKE', '%Satuan Pengawas Internal%')
            ->orWhere('nama_unit', 'LIKE', '%SPI%')
            ->first();

        if (!$spi) {
            $spiId = (string) Str::uuid();
            DB::table('master_units')->insert([
                'id' => $spiId,
                'parent_unit_id' => null,
                'nama_unit' => 'Satuan Pengawas Internal (SPI)',
                'tipe_unit' => 'non_akademik',
                'beban_kerja' => 'sedang',
                'keterangan' => 'Unit Utama Satuan Pengawas Internal',
                'kuota_mpp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('master_units')->where('id', $spi->id)->update([
                'parent_unit_id' => null,
                'updated_at' => now(),
            ]);
        }

        // 2. Dapatkan Unit Sekretariat
        $sekretariat = DB::table('master_units')
            ->where('id', 'a21564dc-abdc-400a-9f2d-37bc33965921')
            ->orWhere('nama_unit', 'Sekretariat')
            ->first();

        if ($sekretariat) {
            DB::table('master_units')->where('id', $sekretariat->id)->update([
                'parent_unit_id' => null,
                'updated_at' => now(),
            ]);

            // Set Sub-unit di bawah Sekretariat: PMB, KUI, PIKDI, Marcomm
            DB::table('master_units')
                ->where(function($q) {
                    $q->where('nama_unit', 'LIKE', '%Marcomm%')
                      ->orWhere('nama_unit', 'LIKE', '%Marketing Communication%')
                      ->orWhere('nama_unit', 'LIKE', '%PIKDI%')
                      ->orWhere('nama_unit', 'LIKE', '%Pusat Informasi Komunikasi dan Digital%')
                      ->orWhere('nama_unit', 'LIKE', '%PMB%')
                      ->orWhere('nama_unit', 'LIKE', '%Penerimaan Mahasiswa Baru%')
                      ->orWhere('nama_unit', 'LIKE', '%KUI%')
                      ->orWhere('nama_unit', 'LIKE', '%Kantor Urusan Internasional%');
                })
                ->update([
                    'parent_unit_id' => $sekretariat->id,
                    'updated_at' => now(),
                ]);
        }

        // 3. Dapatkan Unit WR 1 (Bidang Akademik, Inovasi, dan Kemahasiswaan)
        $wr1 = DB::table('master_units')
            ->where('id', 'a2a3ac48-39be-4740-820d-51b38bbd3101')
            ->orWhere('nama_unit', 'LIKE', '%Bidang Akademik, Inovasi%')
            ->first();

        if ($wr1) {
            DB::table('master_units')->where('id', $wr1->id)->update([
                'parent_unit_id' => null,
                'updated_at' => now(),
            ]);

            // Set Sub-unit di bawah WR 1: Perpustakaan
            DB::table('master_units')
                ->where('nama_unit', 'LIKE', '%Perpustakaan%')
                ->update([
                    'parent_unit_id' => $wr1->id,
                    'updated_at' => now(),
                ]);
        }

        // 4. Pastikan WR 2 dan WR 3 adalah Unit Utama (parent_unit_id = null)
        DB::table('master_units')
            ->where('id', 'a2a3ad78-2749-4857-a251-29a0448b5505')
            ->orWhere('nama_unit', 'LIKE', '%Bidang Sumber Daya dan Kepemimpinan%')
            ->update([
                'parent_unit_id' => null,
                'updated_at' => now(),
            ]);

        DB::table('master_units')
            ->where('id', 'a2a3adaa-f6f5-4a3d-828c-e0027293c42c')
            ->orWhere('nama_unit', 'LIKE', '%Bidang Kerjasama, Perencanaan%')
            ->update([
                'parent_unit_id' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert parent_unit_id of PMB, KUI, PIKDI, Marcomm, Perpustakaan back to null
        DB::table('master_units')
            ->where(function($q) {
                $q->where('nama_unit', 'LIKE', '%Marcomm%')
                  ->orWhere('nama_unit', 'LIKE', '%PIKDI%')
                  ->orWhere('nama_unit', 'LIKE', '%PMB%')
                  ->orWhere('nama_unit', 'LIKE', '%KUI%')
                  ->orWhere('nama_unit', 'LIKE', '%Perpustakaan%');
            })
            ->update([
                'parent_unit_id' => null,
                'updated_at' => now(),
            ]);
    }
};
