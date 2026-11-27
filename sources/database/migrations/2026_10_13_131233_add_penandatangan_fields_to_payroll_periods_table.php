<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->uuid('karyawan_ttd_id')->nullable()->after('catatan');
            $table->string('penandatangan_nama', 150)->nullable()->after('karyawan_ttd_id');
            $table->string('penandatangan_jabatan', 150)->nullable()->after('penandatangan_nama');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn(['karyawan_ttd_id', 'penandatangan_nama', 'penandatangan_jabatan']);
        });
    }
};
