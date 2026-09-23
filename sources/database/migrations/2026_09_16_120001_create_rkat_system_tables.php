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
        // 1. Periode Tahun Anggaran RKAT
        Schema::create('rkat_periodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tahun_anggaran', 20); // e.g. "2026", "2027"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['Aktif', 'Draft', 'Arsip'])->default('Draft');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 2. Master Program Universitas
        Schema::create('rkat_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_program', 50)->unique();
            $table->string('nama_program');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Master Kegiatan
        Schema::create('rkat_kegiatans_master', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id')->nullable();
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('rkat_programs')->onDelete('cascade');
        });

        // 4. Master Akun Anggaran (COA)
        Schema::create('rkat_akuns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_akun', 50)->unique();
            $table->string('nama_akun');
            $table->string('kategori', 100)->nullable(); // e.g. Belanja Pegawai, Operasional, Pengadaan
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Master Sumber Dana
        Schema::create('rkat_sumber_danas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();
            $table->string('nama_sumber_dana');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Master Indikator Kinerja
        Schema::create('rkat_indikators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_indikator');
            $table->string('satuan_target', 50)->default('Orang'); // e.g. Orang, Kegiatan, Dokumen, %, Sertifikat
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Pengajuan RKAT
        Schema::create('rkat_pengajuans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_pengajuan')->unique();
            $table->uuid('periode_id');
            $table->uuid('unit_id');
            $table->uuid('program_id');
            $table->uuid('pic_id'); // FK ke data_dosen_tendiks
            $table->string('sasaran_strategis')->nullable();
            $table->string('nama_kegiatan');
            $table->text('latar_belakang')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('sasaran')->nullable();
            $table->string('indikator_keberhasilan')->nullable();
            $table->integer('target_kuantitas')->default(1);
            $table->string('satuan_target', 50)->default('Kegiatan');
            $table->text('output')->nullable();
            $table->text('outcome')->nullable();
            $table->date('periode_pelaksanaan_mulai')->nullable();
            $table->date('periode_pelaksanaan_selesai')->nullable();
            $table->decimal('total_anggaran_diajukan', 15, 2)->default(0);
            $table->decimal('total_anggaran_disetujui', 15, 2)->default(0);
            $table->decimal('total_realisasi', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Diajukan', 'Review', 'Disetujui', 'Revisi', 'Ditolak'])->default('Draft');
            $table->string('current_approval_level', 100)->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('periode_id')->references('id')->on('rkat_periodes')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('master_units')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('rkat_programs')->onDelete('cascade');
            $table->foreign('pic_id')->references('id')->on('data_dosen_tendiks')->onDelete('cascade');
        });

        // 8. Rincian Komponen Anggaran
        Schema::create('rkat_anggaran_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pengajuan_id');
            $table->string('komponen_biaya');
            $table->integer('kuantitas')->default(1);
            $table->string('satuan', 50)->default('Paket');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->uuid('akun_id')->nullable();
            $table->uuid('sumber_dana_id')->nullable();
            $table->timestamps();

            $table->foreign('pengajuan_id')->references('id')->on('rkat_pengajuans')->onDelete('cascade');
            $table->foreign('akun_id')->references('id')->on('rkat_akuns')->onDelete('set null');
            $table->foreign('sumber_dana_id')->references('id')->on('rkat_sumber_danas')->onDelete('set null');
        });

        // 9. Workflow Approval & Log Audit Trail
        Schema::create('rkat_approval_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pengajuan_id');
            $table->char('user_id', 36)->nullable();
            $table->string('level_jabatan', 100);
            $table->enum('action', ['Diajukan', 'Setujui', 'Tolak', 'Revisi']);
            $table->decimal('nominal_disetujui', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pengajuan_id')->references('id')->on('rkat_pengajuans')->onDelete('cascade');
        });

        // 10. Realisasi Anggaran & Bukti SPJ
        Schema::create('rkat_realisasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pengajuan_id');
            $table->uuid('anggaran_item_id')->nullable();
            $table->date('tanggal_transaksi');
            $table->text('uraian_pengeluaran');
            $table->decimal('jumlah_realisasi', 15, 2)->default(0);
            $table->string('jenis_bukti', 100)->nullable(); // e.g. Invoice, Kwitansi, SPJ, Bukti Transfer
            $table->string('nomor_bukti', 100)->nullable();
            $table->string('file_bukti')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('pengajuan_id')->references('id')->on('rkat_pengajuans')->onDelete('cascade');
            $table->foreign('anggaran_item_id')->references('id')->on('rkat_anggaran_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rkat_realisasis');
        Schema::dropIfExists('rkat_approval_logs');
        Schema::dropIfExists('rkat_anggaran_items');
        Schema::dropIfExists('rkat_pengajuans');
        Schema::dropIfExists('rkat_indikators');
        Schema::dropIfExists('rkat_sumber_danas');
        Schema::dropIfExists('rkat_akuns');
        Schema::dropIfExists('rkat_kegiatans_master');
        Schema::dropIfExists('rkat_programs');
        Schema::dropIfExists('rkat_periodes');
    }
};
