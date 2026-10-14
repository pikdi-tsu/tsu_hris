<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_lembur_periods', function (Blueprint $table) {
            $table->uuid('karyawan_ttd_id')->nullable()->after('penandatangan_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_lembur_periods', function (Blueprint $table) {
            $table->dropColumn('karyawan_ttd_id');
        });
    }
};
