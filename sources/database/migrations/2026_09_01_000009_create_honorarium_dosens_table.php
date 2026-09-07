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
        Schema::create('honorarium_dosens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payroll_period_id');
            $table->uuid('data_dosen_tendik_id')->nullable();

            $table->string('kategori_honor', 30); // kelebihan_sks, pembimbing_penguji, ujian
            $table->string('nama_dosen', 150);
            $table->string('nik_nip', 50)->nullable();
            $table->string('nama_unit', 100)->nullable();
            $table->string('struktural', 100)->nullable();
            $table->string('kode_jafung', 10)->nullable(); // TP, AA, L, LK, GB
            $table->string('nama_jafung', 100)->nullable();

            // === Field Khusus 8A: Kelebihan SKS & Dosen Tidak Tetap ===
            $table->decimal('sks_struktural', 6, 2)->default(0);
            $table->decimal('sks_mengajar', 6, 2)->default(0);
            $table->decimal('total_sks', 6, 2)->default(0);
            $table->decimal('sks_wajib', 6, 2)->default(12);
            $table->decimal('sks_lebih', 6, 2)->default(0);
            $table->decimal('tarif_sks', 15, 2)->default(0);
            $table->integer('jumlah_pertemuan')->default(0);
            $table->decimal('total_honor_sks', 15, 2)->default(0);

            // === Field Khusus 8B: Pembimbing & Penguji TA / KP ===
            $table->integer('jml_bimbingan_ta')->default(0);
            $table->decimal('tarif_bimbingan_ta', 15, 2)->default(0);
            $table->decimal('total_bimbingan_ta', 15, 2)->default(0);

            $table->integer('jml_penguji_ta')->default(0);
            $table->decimal('tarif_penguji_ta', 15, 2)->default(0);
            $table->decimal('total_penguji_ta', 15, 2)->default(0);

            $table->integer('jml_kerja_praktek')->default(0);
            $table->decimal('tarif_kerja_praktek', 15, 2)->default(0);
            $table->decimal('total_kerja_praktek', 15, 2)->default(0);

            // === Field Khusus 8C: Ujian (UTS & UAS) ===
            $table->string('mata_kuliah', 150)->nullable();
            $table->string('tipe_kelas', 10)->nullable(); // T, T/P
            $table->integer('jml_kelas_uts')->default(0);
            $table->integer('jml_kelas_uas')->default(0);
            $table->integer('total_kelas_soal')->default(0);
            $table->decimal('tarif_soal', 15, 2)->default(0);
            $table->decimal('total_honor_soal', 15, 2)->default(0);

            $table->integer('jml_peserta_uts')->default(0);
            $table->integer('jml_peserta_uas')->default(0);
            $table->integer('total_peserta_koreksi')->default(0);
            $table->decimal('tarif_koreksi', 15, 2)->default(0);
            $table->decimal('total_honor_koreksi', 15, 2)->default(0);

            // === Ringkasan Honorarium & Potongan ===
            $table->decimal('total_honor_kotor', 15, 2)->default(0);
            $table->decimal('potongan_pajak', 15, 2)->default(0);
            $table->decimal('potongan_lainnya', 15, 2)->default(0);
            $table->string('keterangan_potongan', 255)->nullable();
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('total_transfer', 15, 2)->default(0); // Take Home Pay

            // Data Rekening
            $table->string('rekening_bank', 50)->nullable();
            $table->string('nomor_rekening', 50)->nullable();
            $table->string('nama_rekening', 150)->nullable();

            $table->text('catatan_koreksi')->nullable();
            $table->timestamps();

            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honorarium_dosens');
    }
};
