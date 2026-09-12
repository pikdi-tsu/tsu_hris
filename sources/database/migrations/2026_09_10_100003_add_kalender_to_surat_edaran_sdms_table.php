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
        Schema::table('surat_edaran_sdms', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_edaran_sdms', 'tampilkan_di_kalender')) {
                $table->boolean('tampilkan_di_kalender')->default(false)->after('target_audience');
            }
            if (!Schema::hasColumn('surat_edaran_sdms', 'tanggal_kalender')) {
                $table->date('tanggal_kalender')->nullable()->after('tampilkan_di_kalender');
            }
            if (!Schema::hasColumn('surat_edaran_sdms', 'tanggal_kalender_selesai')) {
                $table->date('tanggal_kalender_selesai')->nullable()->after('tanggal_kalender');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_edaran_sdms', function (Blueprint $table) {
            if (Schema::hasColumn('surat_edaran_sdms', 'tanggal_kalender_selesai')) {
                $table->dropColumn('tanggal_kalender_selesai');
            }
            if (Schema::hasColumn('surat_edaran_sdms', 'tanggal_kalender')) {
                $table->dropColumn('tanggal_kalender');
            }
            if (Schema::hasColumn('surat_edaran_sdms', 'tampilkan_di_kalender')) {
                $table->dropColumn('tampilkan_di_kalender');
            }
        });
    }
};
