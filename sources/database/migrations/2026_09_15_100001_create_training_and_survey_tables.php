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
        // 1. Master Periode Survey Layanan SDM
        Schema::create('master_periode_surveys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_periode'); // e.g. "Semester Ganjil 2026/2027"
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->string('tahun_ajaran', 20)->default('2026/2027');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Respon Survey Kepuasan Layanan SDM
        Schema::create('survey_layanan_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('periode_id');
            $table->uuid('karyawan_id');
            $table->uuid('user_id')->nullable();
            $table->integer('skor_responsiveness')->default(5);
            $table->integer('skor_keramahan')->default(5);
            $table->integer('skor_kecepatan')->default(5);
            $table->integer('skor_fasilitas')->default(5);
            $table->text('kritik_saran')->nullable();
            $table->timestamps();

            $table->foreign('periode_id')->references('id')->on('master_periode_surveys')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('data_dosen_tendiks')->onDelete('cascade');
        });

        // 3. Modul Training / Kompetensi
        Schema::create('trainings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_training');
            $table->string('penyelenggara')->default('Internal TSU');
            $table->string('lokasi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['Terjadwal', 'Berjalan', 'Selesai'])->default('Selesai');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        // 4. Peserta Training, Catatan Absensi & Sertifikat
        Schema::create('training_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('training_id');
            $table->uuid('karyawan_id');
            $table->enum('status_kehadiran', ['Hadir', 'Izin', 'Tidak Hadir'])->default('Hadir');
            $table->string('catatan_kehadiran')->nullable();
            $table->boolean('is_survey_filled')->default(false);
            $table->dateTime('survey_filled_at')->nullable();
            $table->string('sertifikat_file')->nullable();
            $table->string('sertifikat_nomor')->nullable();
            $table->dateTime('sertifikat_uploaded_at')->nullable();
            $table->timestamps();

            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('data_dosen_tendiks')->onDelete('cascade');
        });

        // 5. Respon Survey Kepuasan Pelatihan
        Schema::create('survey_pelatihan_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('training_peserta_id');
            $table->integer('skor_materi')->default(5);
            $table->integer('skor_narasumber')->default(5);
            $table->integer('skor_fasilitas')->default(5);
            $table->integer('skor_relevansi')->default(5);
            $table->text('feedback_manfaat')->nullable();
            $table->timestamps();

            $table->foreign('training_peserta_id')->references('id')->on('training_pesertas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_pelatihan_responses');
        Schema::dropIfExists('training_pesertas');
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('survey_layanan_responses');
        Schema::dropIfExists('master_periode_surveys');
    }
};
