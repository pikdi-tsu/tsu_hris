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
        // Drop in case created halfway
        Schema::dropIfExists('laporan_kegiatan_sdm_dokumens');
        Schema::dropIfExists('laporan_kegiatan_sdm_pesertas');
        Schema::dropIfExists('laporan_kegiatan_sdms');

        Schema::create('laporan_kegiatan_sdms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_laporan', 100)->unique();
            $table->string('nama_kegiatan');
            $table->string('kategori_kegiatan')->nullable(); // Pelatihan, Workshop, Sosialisasi, Rapat Kerja, Rekrutmen, Evaluasi, dll.
            $table->longText('latar_belakang')->nullable(); // Bab B
            $table->json('tujuan')->nullable(); // Bab C: [{no, tujuan}]
            $table->json('dasar_pelaksanaan')->nullable(); // Bab D: [{no, dasar, nomor_tanggal, dokumen}]
            
            // Bab E: Waktu dan Tempat
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('waktu_pelaksanaan')->nullable(); // e.g. "08:30 - 16:00 WIB"
            $table->enum('tipe_tempat', ['luring', 'daring', 'hybrid'])->default('luring');
            $table->string('tempat_pelaksanaan')->nullable();
            $table->string('link_daring')->nullable();
            $table->text('keterangan_waktu_tempat')->nullable();

            // Bab F: Rekap Peserta
            $table->unsignedInteger('jumlah_peserta_rencana')->default(0);
            $table->unsignedInteger('jumlah_peserta_hadir')->default(0);
            $table->unsignedInteger('jumlah_peserta_tidak_hadir')->default(0);

            // Bab G: Pelaksana / Panitia
            $table->json('panitia')->nullable(); // [{nama, unit_instansi, jabatan, peran}]

            // Bab H: Narasumber / Fasilitator
            $table->json('narasumber')->nullable(); // [{nama, instansi, jabatan, materi_peran}]

            // Bab I: Rangkaian Kegiatan (Rundown)
            $table->json('rundown')->nullable(); // [{waktu, agenda, uraian}]

            // Bab J: Hasil dan Capaian
            $table->json('target_kegiatan')->nullable(); // [{indikator, target}]
            $table->json('capaian_kegiatan')->nullable(); // [{indikator, target, realisasi, capaian_persen, status}]
            $table->longText('uraian_hasil')->nullable(); // J.3

            // Bab K: Evaluasi
            $table->json('evaluasi')->nullable(); // [{aspek_indikator, metode, hasil}]
            $table->longText('catatan_evaluasi')->nullable();

            // Bab L: Tindak Lanjut
            $table->json('tindak_lanjut')->nullable(); // [{tindak_lanjut, pic, target_waktu, status}]

            // Bab M: Realisasi Anggaran
            $table->json('anggaran')->nullable(); // [{komponen, anggaran, realisasi, selisih}]
            $table->decimal('total_anggaran', 15, 2)->default(0);
            $table->decimal('total_realisasi', 15, 2)->default(0);
            $table->decimal('total_selisih', 15, 2)->default(0);

            // Bab O: Kesimpulan
            $table->longText('kesimpulan')->nullable();

            // Bab P: Pengesahan
            $table->uuid('mengetahui_pejabat_id')->nullable();
            $table->string('mengetahui_nama')->nullable();
            $table->string('mengetahui_nip')->nullable();
            $table->string('mengetahui_jabatan')->default('Direktur Sumber Daya Manusia');

            $table->uuid('disusun_pejabat_id')->nullable();
            $table->string('disusun_nama')->nullable();
            $table->string('disusun_nip')->nullable();
            $table->string('disusun_jabatan')->default('PIC / Kepala Subbagian SDM');

            $table->date('tanggal_pengesahan')->nullable();

            // Status & Audit
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->uuid('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('mengetahui_pejabat_id')->references('id')->on('data_dosen_tendiks')->nullOnDelete();
            $table->foreign('disusun_pejabat_id')->references('id')->on('data_dosen_tendiks')->nullOnDelete();
        });

        // Tabel Daftar Peserta Kegiatan (Bab F detail)
        Schema::create('laporan_kegiatan_sdm_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laporan_kegiatan_sdm_id')->constrained('laporan_kegiatan_sdms')->cascadeOnDelete();
            $table->uuid('data_dosen_tendik_id')->nullable();
            $table->string('nama');
            $table->string('nip_nidn')->nullable();
            $table->string('unit')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('kehadiran')->default('Hadir'); // Hadir, Tidak Hadir, Izin
            $table->timestamps();

            $table->foreign('data_dosen_tendik_id')->references('id')->on('data_dosen_tendiks')->nullOnDelete();
        });

        // Tabel Dokumentasi Foto (Bab N) & Lampiran (Lampiran 1 s.d. 6)
        Schema::create('laporan_kegiatan_sdm_dokumens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laporan_kegiatan_sdm_id')->constrained('laporan_kegiatan_sdms')->cascadeOnDelete();
            $table->string('kategori', 50); // foto_dokumentasi, lampiran_hadir, lampiran_materi, lampiran_evaluasi, lampiran_keuangan, lampiran_lainnya
            $table->string('file_path');
            $table->string('nama_file');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kegiatan_sdm_dokumens');
        Schema::dropIfExists('laporan_kegiatan_sdm_pesertas');
        Schema::dropIfExists('laporan_kegiatan_sdms');
    }
};
