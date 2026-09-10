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
        // 1. Peserta / Rencana Pegawai dalam Pengembangan SDM
        Schema::create('pengembangan_sdm_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('master_periode_id');
            $table->uuid('data_dosen_tendik_id')->nullable(); // Nullable jika ini slot proyeksi "DOSEN BARU 2027 S3"
            $table->string('nama_placeholder')->nullable(); // Nama custom / placeholder jika belum ada ID pegawai
            $table->enum('tipe_pegawai', ['dosen', 'tendik'])->default('dosen');
            $table->uuid('unit_id')->nullable(); // Unit / Prodi / Biro
            $table->string('sub_unit')->nullable(); // Posisi spesifik tendik (misal: "Programmer", "Desain Grafis")
            $table->string('pendidikan_awal', 20)->default('S2'); // S1, S2, D3
            $table->string('gelar', 50)->nullable();
            $table->enum('lokasi_studi', ['DN', 'LN'])->nullable()->default('DN'); // Dalam Negeri vs Luar Negeri
            $table->string('institusi_tujuan')->nullable(); // Nama universitas tujuan
            $table->uuid('bidang_keilmuan_id')->nullable(); // Relasi ke master_bidang_keilmuans
            $table->string('kode_bidang_custom', 50)->nullable(); // Backup kode bidang jika ada singkatan custom
            $table->date('tanggal_pensiun')->nullable(); // Estimasi pensiun (65th dosen / 58th tendik)
            $table->text('keterangan')->nullable();
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('master_periode_id')->references('id')->on('master_periode_pengembangans')->onDelete('cascade');
            $table->foreign('data_dosen_tendik_id')->references('id')->on('data_dosen_tendiks')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('master_units')->onDelete('set null');
            $table->foreign('bidang_keilmuan_id')->references('id')->on('master_bidang_keilmuans')->onDelete('set null');
        });

        // 2. Timeline Status Tahunan Peserta (2026 s/d 2030)
        Schema::create('pengembangan_sdm_timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('peserta_id');
            $table->integer('tahun'); // 2026, 2027, 2028, 2029, 2030
            $table->string('status_studi', 20)->default('S2'); // S2, S2+, S3, D3, D3+, S1, S1+
            $table->enum('status_aktif_studi', ['SS', 'TSS'])->default('TSS'); // SS = Sedang Studi, TSS = Tidak Sedang Studi
            $table->string('bidang_kode', 50)->nullable(); // Kode bidang ilmu di tahun tersebut (e.g. AI, CV, DS, KOM)
            $table->timestamps();

            $table->foreign('peserta_id')->references('id')->on('pengembangan_sdm_pesertas')->onDelete('cascade');
            $table->unique(['peserta_id', 'tahun']);
        });

        // 3. Matriks Kepemilikan Sertifikasi Kompetensi
        Schema::create('pengembangan_sdm_sertifikasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('peserta_id');
            $table->uuid('sertifikasi_id');
            $table->boolean('status_kepemilikan')->default(true); // 1 = memiliki
            $table->year('tahun_perolehan')->nullable();
            $table->string('no_sertifikat')->nullable();
            $table->timestamps();

            $table->foreign('peserta_id')->references('id')->on('pengembangan_sdm_pesertas')->onDelete('cascade');
            $table->foreign('sertifikasi_id')->references('id')->on('master_sertifikasis')->onDelete('cascade');
            $table->unique(['peserta_id', 'sertifikasi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembangan_sdm_sertifikasis');
        Schema::dropIfExists('pengembangan_sdm_timelines');
        Schema::dropIfExists('pengembangan_sdm_pesertas');
    }
};
