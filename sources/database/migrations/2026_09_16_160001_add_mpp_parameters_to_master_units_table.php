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
        Schema::table('master_units', function (Blueprint $table) {
            if (!Schema::hasColumn('master_units', 'tipe_unit')) {
                $table->enum('tipe_unit', ['akademik', 'non_akademik'])->default('non_akademik')->after('nama_unit');
            }
            if (!Schema::hasColumn('master_units', 'jumlah_mahasiswa')) {
                $table->integer('jumlah_mahasiswa')->nullable()->after('tipe_unit');
            }
            if (!Schema::hasColumn('master_units', 'beban_kerja')) {
                $table->enum('beban_kerja', ['rendah', 'sedang', 'tinggi'])->default('sedang')->after('jumlah_mahasiswa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_units', function (Blueprint $table) {
            if (Schema::hasColumn('master_units', 'beban_kerja')) {
                $table->dropColumn('beban_kerja');
            }
            if (Schema::hasColumn('master_units', 'jumlah_mahasiswa')) {
                $table->dropColumn('jumlah_mahasiswa');
            }
            if (Schema::hasColumn('master_units', 'tipe_unit')) {
                $table->dropColumn('tipe_unit');
            }
        });
    }
};
