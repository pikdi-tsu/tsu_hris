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
        Schema::create('pegawai_probations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pegawai_id', 36)->index();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->integer('durasi_bulan')->default(3);
            $table->decimal('skor_kedisiplinan', 5, 2)->nullable();
            $table->decimal('skor_kompetensi', 5, 2)->nullable();
            $table->decimal('skor_kerjasama', 5, 2)->nullable();
            $table->decimal('skor_total', 5, 2)->nullable();
            $table->enum('rekomendasi', ['angkat_tetap', 'perpanjang_probation', 'tidak_lolos'])->nullable();
            $table->text('catatan_evaluasi')->nullable();
            $table->string('evaluator_id', 36)->nullable();
            $table->date('tgl_evaluasi')->nullable();
            $table->enum('status', ['berjalan', 'menunggu_evaluasi', 'selesai'])->default('berjalan');
            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('data_dosen_tendiks')->onDelete('cascade');
            $table->foreign('evaluator_id')->references('id')->on('data_dosen_tendiks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_probations');
    }
};
