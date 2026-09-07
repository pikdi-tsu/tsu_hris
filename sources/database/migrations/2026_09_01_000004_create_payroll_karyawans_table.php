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
        $tableKaryawan = 'data_dosen_tendiks';

        Schema::create('payroll_karyawans', function (Blueprint $table) use ($tableKaryawan) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
            $table->foreignUuid('data_dosen_tendik_id')->nullable()->constrained($tableKaryawan)->onDelete('set null');

            // Snapshot Informasi Pegawai
            $table->string('nik', 50)->nullable();
            $table->string('nama', 150);
            $table->string('tipe_karyawan', 50)->nullable();
            $table->string('posisi', 100)->nullable();
            $table->string('nama_unit', 150)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('atas_nama_rekening', 150)->nullable();

            // Snapshot Pangkat / Golongan & Jabatan
            $table->string('golongan_pangkat', 50)->nullable();
            $table->string('jabatan_fungsional', 150)->nullable();
            $table->string('jabatan_struktural', 150)->nullable();

            // Komponen Penerimaan / Gaji Pokok & Tunjangan Tetap
            $table->unsignedTinyInteger('persen_gapok')->default(100);
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan_fungsional', 15, 2)->default(0);
            $table->decimal('tunjangan_struktural', 15, 2)->default(0);
            $table->decimal('tunjangan_khusus', 15, 2)->default(0);
            $table->decimal('tunjangan_keluarga', 15, 2)->default(0);
            $table->decimal('tunjangan_anak', 15, 2)->default(0);
            $table->decimal('tunjangan_kesehatan', 15, 2)->default(0);
            $table->decimal('gaji_tetap', 15, 2)->default(0);

            // Komponen Kehadiran & Lembur (Variabel)
            $table->decimal('hari_hadir_valid', 6, 2)->default(0);
            $table->decimal('tarif_transport', 15, 2)->default(20000);
            $table->decimal('total_transport', 15, 2)->default(0);
            $table->decimal('total_jam_lembur', 6, 2)->default(0);
            $table->decimal('total_lembur', 15, 2)->default(0);

            // Komponen Potongan
            $table->decimal('hari_unpaid_leave', 6, 2)->default(0);
            $table->decimal('rate_unpaid_leave', 15, 2)->default(0);
            $table->decimal('potongan_unpaid_leave', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_kes', 15, 2)->default(0);
            $table->decimal('potongan_lainnya', 15, 2)->default(0);
            $table->string('keterangan_potongan', 255)->nullable();

            // Akumulasi
            $table->decimal('gaji_kotor', 15, 2)->default(0); // Subtotal penerimaan
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2)->default(0); // Take Home Pay

            // Metadata Koreksi
            $table->text('catatan_koreksi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_karyawans');
    }
};
