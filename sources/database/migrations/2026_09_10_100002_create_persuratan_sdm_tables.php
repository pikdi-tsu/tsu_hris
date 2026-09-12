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
        // 1. Tabel Permohonan / Request Surat Mandiri Pegawai ke SDM
        if (!Schema::hasTable('request_surat_sdms')) {
            Schema::create('request_surat_sdms', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_tiket', 50)->unique();
                $table->char('data_dosen_tendik_id', 36)->index();
                $table->char('user_id', 36)->nullable()->index();
                $table->string('jenis_surat', 100);
                $table->text('keperluan');
                $table->text('keterangan_tambahan')->nullable();
                $table->string('file_lampiran', 255)->nullable();
                $table->enum('status', ['menunggu', 'diproses', 'selesai', 'ditolak'])->default('menunggu')->index();
                $table->string('file_surat_hasil', 255)->nullable()->comment('File PDF surat resmi hasil cetak/ttd/stempel dari SDM');
                $table->string('nomor_surat_keluar', 100)->nullable()->comment('Nomor surat resmi yang diterbitkan SDM');
                $table->text('catatan_petugas')->nullable();
                $table->char('processed_by', 36)->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('data_dosen_tendik_id')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('cascade');
            });
        }

        // 2. Tabel Pusat Surat Edaran & SK Resmi dari SDM/Rektorat
        if (!Schema::hasTable('surat_edaran_sdms')) {
            Schema::create('surat_edaran_sdms', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_surat', 150);
                $table->string('perihal', 255);
                $table->string('kategori', 50)->default('edaran_umum')->comment('edaran_libur, edaran_jam_kerja, sk_rektor, kebijakan_sdm, pengumuman');
                $table->date('tanggal_surat');
                $table->date('tanggal_berlaku')->nullable();
                $table->string('file_dokumen', 255);
                $table->bigInteger('file_size')->nullable();
                $table->string('target_audience', 50)->default('semua')->comment('semua, dosen, tendik');
                $table->boolean('is_active')->default(true);
                $table->integer('download_count')->default(0);
                $table->char('created_by', 36)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_edaran_sdms');
        Schema::dropIfExists('request_surat_sdms');
    }
};
