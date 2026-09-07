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
        Schema::create('master_tarif_honorariums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_jafung', 10)->unique(); // TP, AA, L, LK
            $table->string('nama_jafung', 100);          // Tenaga Pengajar, Asisten Ahli, Lektor, Lektor Kepala

            // 8A. Kelebihan SKS & Dosen Tidak Tetap
            $table->decimal('tarif_sks_hadir', 15, 2)->default(0);

            // 8B. Pembimbing & Penguji TA / KP
            $table->decimal('tarif_bimbingan_ta', 15, 2)->default(0);
            $table->decimal('tarif_penguji_ta', 15, 2)->default(0);
            $table->decimal('tarif_kerja_praktek', 15, 2)->default(0);

            // 8C. Ujian (UTS & UAS)
            $table->decimal('tarif_soal_teori', 15, 2)->default(0);
            $table->decimal('tarif_soal_teori_praktik', 15, 2)->default(0);
            $table->decimal('tarif_koreksi_teori', 15, 2)->default(0);
            $table->decimal('tarif_koreksi_teori_praktik', 15, 2)->default(0);

            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tarif_honorariums');
    }
};
