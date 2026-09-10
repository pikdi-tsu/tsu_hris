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
        // 1. Master Periode Renstra Pengembangan SDM
        Schema::create('master_periode_pengembangans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_periode'); // e.g. "Renstra Pengembangan SDM 2026 - 2030"
            $table->integer('tahun_mulai')->default(2026);
            $table->integer('tahun_selesai')->default(2030);
            $table->decimal('target_persen_doktor', 5, 2)->default(53.60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Master Bidang Keilmuan / Konsentrasi Kepakaran
        Schema::create('master_bidang_keilmuans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_bidang', 20); // e.g. AI, CV, DS, KOM, SDM, IP
            $table->string('nama_bidang'); // e.g. "Artificial Intelligence", "Data Science"
            $table->enum('kategori', ['dosen', 'tendik', 'umum'])->default('umum');
            $table->uuid('unit_id')->nullable(); // Relasi ke master_units jika spesifik prodi
            $table->string('warna_badge', 30)->nullable()->default('badge-info');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('master_units')->onDelete('set null');
        });

        // 3. Master Sertifikasi Kompetensi
        Schema::create('master_sertifikasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_sertifikasi'); // e.g. "AWS Cloud", "Cisco CCNA", "Microsoft Office"
            $table->enum('kategori_peserta', ['dosen', 'tendik', 'umum'])->default('umum');
            $table->string('lembaga_penerbit')->nullable(); // e.g. "BNSP", "Microsoft", "Cisco"
            $table->uuid('unit_id')->nullable(); // Opsional jika khusus prodi
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('master_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sertifikasis');
        Schema::dropIfExists('master_bidang_keilmuans');
        Schema::dropIfExists('master_periode_pengembangans');
    }
};
