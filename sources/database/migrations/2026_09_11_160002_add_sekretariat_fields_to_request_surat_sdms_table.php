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
            if (!Schema::hasColumn('request_surat_sdms', 'status_hardfile')) {
                $table->enum('status_hardfile', ['belum_tersedia', 'siap_diambil', 'telah_diterima_sdm'])
                    ->default('belum_tersedia')
                    ->after('catatan_terusan');
            }
            if (!Schema::hasColumn('request_surat_sdms', 'catatan_sekretariat')) {
                $table->text('catatan_sekretariat')
                    ->nullable()
                    ->after('status_hardfile');
            }
            if (!Schema::hasColumn('request_surat_sdms', 'diselesaikan_oleh_sekretariat')) {
                $table->char('diselesaikan_oleh_sekretariat', 36)
                    ->nullable()
                    ->after('catatan_sekretariat');
            }
            if (!Schema::hasColumn('request_surat_sdms', 'tgl_diselesaikan_sekretariat')) {
                $table->datetime('tgl_diselesaikan_sekretariat')
                    ->nullable()
                    ->after('diselesaikan_oleh_sekretariat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_surat_sdms', function (Blueprint $table) {
            $columns = [
                'status_hardfile',
                'catatan_sekretariat',
                'diselesaikan_oleh_sekretariat',
                'tgl_diselesaikan_sekretariat'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('request_surat_sdms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
