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
        // 1. Periode BKD (Semester Ganjil/Genap)
        if (!Schema::hasTable('periode_bkds')) {
            Schema::create('periode_bkds', function (Blueprint $table) {
                $table->id();
                $table->string('nama_periode', 100);
                $table->string('tahun_ajaran', 20);
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->date('tgl_mulai')->nullable();
                $table->date('tgl_selesai')->nullable();
                $table->boolean('is_active')->default(false)->index();
                $table->timestamps();
            });
        }

        // 2. Laporan BKD / LKD Dosen
        if (!Schema::hasTable('laporan_bkds')) {
            Schema::create('laporan_bkds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('periode_bkd_id');
                $table->char('data_dosen_tendik_id', 36);
                $table->string('file_pdf', 255);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamp('tanggal_upload');
                $table->enum('status_verifikasi', ['draft', 'diverifikasi', 'perlu_revisi'])->default('diverifikasi');
                $table->text('catatan')->nullable();
                $table->char('diverifikasi_by', 36)->nullable();
                $table->timestamp('diverifikasi_at')->nullable();
                $table->timestamps();

                $table->index('periode_bkd_id', 'idx_bkd_periode');
                $table->index('data_dosen_tendik_id', 'idx_bkd_dosen');

                $table->foreign('periode_bkd_id', 'fk_bkd_periode')
                    ->references('id')
                    ->on('periode_bkds')
                    ->onDelete('cascade');

                $table->foreign('data_dosen_tendik_id', 'fk_bkd_dosen')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_bkds');
        Schema::dropIfExists('periode_bkds');
    }
};
