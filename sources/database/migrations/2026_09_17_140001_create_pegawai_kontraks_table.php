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
        Schema::create('pegawai_kontraks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pegawai_id', 36)->index();
            $table->string('no_kontrak', 100);
            $table->integer('kontrak_ke')->default(1);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->integer('durasi_bulan')->default(12);
            $table->string('posisi', 150)->nullable();
            $table->decimal('gaji_pokok_disepakati', 15, 2)->nullable();
            $table->string('dokumen_kontrak', 255)->nullable();
            $table->enum('status', ['aktif', 'hampir_habis', 'diperpanjang', 'selesai', 'diputus'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('data_dosen_tendiks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_kontraks');
    }
};
