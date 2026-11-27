<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('data_dosen_tendiks', 'pangkat_golongan_id')) {
            Schema::table('data_dosen_tendiks', function (Blueprint $table) {
                $table->uuid('pangkat_golongan_id')->nullable()->after('posisi');
            });
        }

        // Sinkronisasi data pangkat_golongan dari tabel karyawan_jabatan_fungsionals yang aktif
        $fungsionalAktif = DB::table('karyawan_jabatan_fungsionals')
            ->where('is_active', 'Y')
            ->whereNotNull('pangkat_golongan_id')
            ->get();

        foreach ($fungsionalAktif as $f) {
            DB::table('data_dosen_tendiks')
                ->where('id', $f->data_dosen_tendik_id)
                ->whereNull('pangkat_golongan_id')
                ->update(['pangkat_golongan_id' => $f->pangkat_golongan_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('data_dosen_tendiks', 'pangkat_golongan_id')) {
            Schema::table('data_dosen_tendiks', function (Blueprint $table) {
                $table->dropColumn('pangkat_golongan_id');
            });
        }
    }
};
