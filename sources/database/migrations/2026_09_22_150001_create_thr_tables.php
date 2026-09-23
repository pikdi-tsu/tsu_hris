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
        // 1. Tabel Periode THR
        Schema::create('thr_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_periode', 150); // Contoh: THR Idul Fitri 1447 H - Maret 2026
            $table->unsignedSmallInteger('tahun'); // 2026
            $table->date('tanggal_cutoff'); // Tanggal patokan hitung masa kerja
            $table->date('tanggal_surat')->nullable(); // Tanggal di slip PDF (misal 1 Maret 2026)
            $table->string('kota_surat', 100)->default('Surakarta');
            $table->string('penandatangan_nama', 150)->default('Afifah Raisya Putri Sanjaya, S.I.P.');
            $table->string('penandatangan_jabatan', 150)->default('Bagian SDM');
            $table->enum('status', ['draft', 'final', 'locked'])->default('draft');
            $table->unsignedInteger('total_pegawai')->default(0);
            $table->decimal('total_anggaran_thr', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Rincian THR Karyawan
        Schema::create('thr_karyawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('thr_period_id')->constrained('thr_periods')->cascadeOnDelete();
            $table->uuid('data_dosen_tendik_id')->nullable();
            $table->string('nik', 50)->nullable();
            $table->string('nama', 150);
            $table->string('tipe_karyawan', 20)->default('Tendik'); // Dosen / Tendik
            $table->string('posisi', 150)->nullable();
            $table->string('nama_unit', 150)->nullable();
            $table->date('tgl_awal_kerja')->nullable(); // Tanggal awal kerja TSU
            $table->unsignedSmallInteger('masa_kerja_bulan')->default(0); // Dalam bulan
            $table->string('status_thr', 50)->default('Pro Rata'); // 'Penuh' atau 'Pro Rata'
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan_tetap', 15, 2)->default(0);
            $table->decimal('upah_tetap', 15, 2)->default(0); // Gaji Pokok + Tunj Tetap
            $table->decimal('nominal_thr', 15, 2)->default(0); // Hasil perhitungan rumus
            $table->decimal('penyesuaian', 15, 2)->default(0); // Penyesuaian/koreksi manual
            $table->decimal('total_thr', 15, 2)->default(0); // nominal_thr + penyesuaian
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('data_dosen_tendik_id')->references('id')->on('data_dosen_tendiks')->nullOnDelete();
            $table->index(['thr_period_id', 'status_thr']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thr_karyawans');
        Schema::dropIfExists('thr_periods');
    }
};
