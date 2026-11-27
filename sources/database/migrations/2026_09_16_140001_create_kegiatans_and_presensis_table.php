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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kegiatan');
            $table->string('kategori')->default('Rapat Dinas'); // Rapat Dinas, Seminar/Workshop, Upacara, Sosialisasi, Gathering, Lainnya
            $table->date('tanggal_kegiatan');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi')->default('Ruang Rapat Kampus');
            $table->uuid('penyelenggara_unit_id')->nullable();
            $table->uuid('penanggung_jawab_id')->nullable(); // DataDosenTendik ID
            $table->string('target_peserta')->default('Semua Pegawai'); // Semua Pegawai, Dosen Saja, Tendik Saja, Unit Tertentu
            $table->string('status')->default('Dibuka'); // Draft, Dibuka, Selesai, Dibatalkan
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tanggal_kegiatan', 'status']);
        });

        Schema::create('kegiatan_presensis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kegiatan_id');
            $table->uuid('pegawai_id')->nullable(); // DataDosenTendik ID
            $table->uuid('user_id');
            $table->enum('status_kehadiran', ['Ya', 'Tidak', 'Terlambat'])->default('Ya');
            $table->string('foto_selfie')->nullable(); // path file foto di storage
            $table->text('keterangan')->nullable(); // Alasan/catatan (tidak wajib)
            $table->dateTime('waktu_presensi');
            $table->timestamps();

            $table->foreign('kegiatan_id')->references('id')->on('kegiatans')->onDelete('cascade');
            $table->index(['kegiatan_id', 'status_kehadiran']);
            $table->index(['kegiatan_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_presensis');
        Schema::dropIfExists('kegiatans');
    }
};
