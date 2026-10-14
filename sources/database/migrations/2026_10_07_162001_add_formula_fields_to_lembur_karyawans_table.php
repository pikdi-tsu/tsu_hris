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
        Schema::table('lembur_karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('lembur_karyawans', 'kategori_lembur')) {
                $table->string('kategori_lembur', 50)->default('tendik')->after('id_mlembur');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'jenis_shift')) {
                $table->string('jenis_shift', 50)->nullable()->after('kategori_lembur');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'durasi_efektif_jam')) {
                $table->decimal('durasi_efektif_jam', 6, 2)->nullable()->after('tanggalselesai');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'koefisien_pengali')) {
                $table->decimal('koefisien_pengali', 6, 2)->nullable()->after('durasi_efektif_jam');
            }
            if (!Schema::hasColumn('lembur_karyawans', 'nominal_estimasi')) {
                $table->decimal('nominal_estimasi', 15, 2)->nullable()->after('koefisien_pengali');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lembur_karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'kategori_lembur',
                'jenis_shift',
                'durasi_efektif_jam',
                'koefisien_pengali',
                'nominal_estimasi',
            ]);
        });
    }
};
