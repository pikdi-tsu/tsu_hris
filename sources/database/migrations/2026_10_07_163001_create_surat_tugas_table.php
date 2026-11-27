<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('surat_tugas')) {
            Schema::create('surat_tugas', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nomor_surat', 150)->index();
                $table->char('data_dosen_tendik_id', 36)->nullable()->index();
                $table->string('nama_pegawai', 255)->nullable();
                $table->date('tanggal_mulai')->index();
                $table->date('tanggal_selesai')->index();
                $table->text('keperluan');
                $table->string('lokasi_tugas', 255)->nullable();
                $table->string('pejabat_pemberi_tugas', 255)->nullable();
                $table->string('file_surat_tugas', 255)->nullable();
                $table->string('status', 50)->default('Disetujui'); // Disetujui, Menunggu, Dibatalkan, Selesai
                $table->text('catatan')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->foreign('data_dosen_tendik_id')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surat_tugas');
    }
};
