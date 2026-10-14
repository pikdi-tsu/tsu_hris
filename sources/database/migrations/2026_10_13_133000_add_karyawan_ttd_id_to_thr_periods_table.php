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
        if (Schema::hasTable('thr_periods')) {
            Schema::table('thr_periods', function (Blueprint $table) {
                if (!Schema::hasColumn('thr_periods', 'karyawan_ttd_id')) {
                    $table->uuid('karyawan_ttd_id')->nullable()->after('kota_surat');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('thr_periods')) {
            Schema::table('thr_periods', function (Blueprint $table) {
                if (Schema::hasColumn('thr_periods', 'karyawan_ttd_id')) {
                    $table->dropColumn('karyawan_ttd_id');
                }
            });
        }
    }
};
