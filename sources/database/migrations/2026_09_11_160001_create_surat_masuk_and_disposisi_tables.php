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
        // 1. Tabel Surat Masuk Eksternal
        if (!Schema::hasTable('surat_masuks')) {
            Schema::create('surat_masuks', function (Blueprint $table) {
                $table->id();
                $table->string('no_agenda', 50)->nullable()->index();
                $table->string('no_surat_asal', 100)->index();
                $table->string('pengirim_instansi', 255);
                $table->date('tgl_surat');
                $table->date('tgl_diterima');
                $table->string('perihal', 255);
                $table->text('ringkasan_isi')->nullable();
                $table->enum('sifat_surat', ['biasa', 'penting', 'segera', 'rahasia'])->default('biasa');
                $table->string('file_surat', 255);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->enum('status', ['terdaftar', 'didisposisi', 'proses_unit', 'selesai'])->default('terdaftar')->index();
                $table->char('created_by', 36)->nullable();
                $table->char('updated_by', 36)->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Disposisi Surat Masuk ke Unit Kerja
        if (!Schema::hasTable('disposisi_surat_masuks')) {
            Schema::create('disposisi_surat_masuks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('surat_masuk_id');
                $table->char('dari_user_id', 36)->nullable();
                $table->char('ke_unit_id', 36)->nullable();
                $table->char('ke_pegawai_id', 36)->nullable();
                $table->string('instruksi', 255);
                $table->text('catatan_disposisi')->nullable();
                $table->datetime('tgl_disposisi');
                $table->date('batas_waktu')->nullable();
                $table->enum('status_tindak_lanjut', ['menunggu', 'diterima', 'diproses', 'selesai'])->default('menunggu')->index();
                $table->text('catatan_tindak_lanjut')->nullable();
                $table->string('file_tindak_lanjut', 255)->nullable();
                $table->datetime('tgl_selesai')->nullable();
                $table->char('diselesaikan_oleh', 36)->nullable();
                $table->timestamps();

                $table->index('surat_masuk_id', 'idx_sm_disp_surat');
                $table->index('ke_unit_id', 'idx_sm_disp_unit');
                $table->index('ke_pegawai_id', 'idx_sm_disp_pegawai');

                $table->foreign('surat_masuk_id', 'fk_disp_sm')
                    ->references('id')
                    ->on('surat_masuks')
                    ->onDelete('cascade');

                $table->foreign('ke_unit_id', 'fk_disp_unit')
                    ->references('id')
                    ->on('master_units')
                    ->onDelete('set null');

                $table->foreign('ke_pegawai_id', 'fk_disp_pegawai')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi_surat_masuks');
        Schema::dropIfExists('surat_masuks');
    }
};
