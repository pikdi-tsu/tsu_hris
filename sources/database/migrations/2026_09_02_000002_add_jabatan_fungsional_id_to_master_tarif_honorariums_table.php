<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\MasterJabatanFungsional;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_tarif_honorariums')) {
            Schema::table('master_tarif_honorariums', function (Blueprint $table) {
                if (!Schema::hasColumn('master_tarif_honorariums', 'jabatan_fungsional_id')) {
                    $table->uuid('jabatan_fungsional_id')->nullable()->after('id')->index();
                }
            });

            // Hubungkan data tarif yang sudah ada ke master_jabatan_fungsionals
            $jafungs = MasterJabatanFungsional::all();
            foreach ($jafungs as $j) {
                $namaUpper = strtoupper($j->nama_jabatan);
                $kode = null;
                if (str_contains($namaUpper, 'GURU BESAR') || str_contains($namaUpper, 'PROFESOR')) {
                    $kode = 'GB';
                } elseif (str_contains($namaUpper, 'LEKTOR KEPALA')) {
                    $kode = 'LK';
                } elseif (str_contains($namaUpper, 'LEKTOR')) {
                    $kode = 'L';
                } elseif (str_contains($namaUpper, 'ASISTEN AHLI')) {
                    $kode = 'AA';
                } elseif (str_contains($namaUpper, 'TENAGA PENGAJAR')) {
                    $kode = 'TP';
                }

                if ($kode) {
                    DB::table('master_tarif_honorariums')
                        ->where('kode_jafung', $kode)
                        ->update(['jabatan_fungsional_id' => $j->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_tarif_honorariums') && Schema::hasColumn('master_tarif_honorariums', 'jabatan_fungsional_id')) {
            Schema::table('master_tarif_honorariums', function (Blueprint $table) {
                $table->dropColumn('jabatan_fungsional_id');
            });
        }
    }
};
