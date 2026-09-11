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
        if (Schema::hasTable('izin_karyawan') && !Schema::hasColumn('izin_karyawan', 'file_bukti')) {
            Schema::table('izin_karyawan', function (Blueprint $table) {
                $table->string('file_bukti', 255)->nullable()->after('keterangan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('izin_karyawan') && Schema::hasColumn('izin_karyawan', 'file_bukti')) {
            Schema::table('izin_karyawan', function (Blueprint $table) {
                $table->dropColumn('file_bukti');
            });
        }
    }
};
