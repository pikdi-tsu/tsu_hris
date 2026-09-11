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
        Schema::table('request_surat_sdms', function (Blueprint $table) {
            if (!Schema::hasColumn('request_surat_sdms', 'diteruskan_ke')) {
                $table->char('diteruskan_ke', 36)->nullable()->after('catatan_petugas');
            }
            if (!Schema::hasColumn('request_surat_sdms', 'diteruskan_at')) {
                $table->timestamp('diteruskan_at')->nullable()->after('diteruskan_ke');
            }
            if (!Schema::hasColumn('request_surat_sdms', 'catatan_terusan')) {
                $table->text('catatan_terusan')->nullable()->after('diteruskan_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_surat_sdms', function (Blueprint $table) {
            $table->dropColumn(['diteruskan_ke', 'diteruskan_at', 'catatan_terusan']);
        });
    }
};
