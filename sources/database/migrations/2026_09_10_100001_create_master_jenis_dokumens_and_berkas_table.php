<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Master Jenis Dokumen
        if (!Schema::hasTable('master_jenis_dokumens')) {
            Schema::create('master_jenis_dokumens', function (Blueprint $table) {
                $table->id();
                $table->string('nama_dokumen', 150);
                $table->string('kode_dokumen', 50)->unique();
                $table->text('deskripsi')->nullable();
                $table->boolean('is_wajib')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });

            // Seed jenis dokumen default
            $defaultJenis = [
                ['nama_dokumen' => 'Kartu Tanda Penduduk (KTP)', 'kode_dokumen' => 'KTP', 'deskripsi' => 'Scan KTP asli berwarna', 'is_wajib' => true, 'urutan' => 1],
                ['nama_dokumen' => 'Kartu Keluarga (KK)', 'kode_dokumen' => 'KK', 'deskripsi' => 'Scan Kartu Keluarga terbaru', 'is_wajib' => true, 'urutan' => 2],
                ['nama_dokumen' => 'Nomor Pokok Wajib Pajak (NPWP)', 'kode_dokumen' => 'NPWP', 'deskripsi' => 'Kartu NPWP / bukti pendaftaran NPWP', 'is_wajib' => false, 'urutan' => 3],
                ['nama_dokumen' => 'Ijazah & Transkrip Terakhir', 'kode_dokumen' => 'IJAZAH', 'deskripsi' => 'Scan ijazah & transkrip nilai pendidikan terakhir', 'is_wajib' => true, 'urutan' => 4],
                ['nama_dokumen' => 'Surat Perjanjian Kerja (SPK) Yayasan', 'kode_dokumen' => 'SPK_YAYASAN', 'deskripsi' => 'Perjanjian kerja antara karyawan dan yayasan', 'is_wajib' => false, 'urutan' => 5],
                ['nama_dokumen' => 'Surat Perjanjian Kerja (SPK) Universitas', 'kode_dokumen' => 'SPK_UNIV', 'deskripsi' => 'Perjanjian kerja/penugasan di lingkungan TSU', 'is_wajib' => false, 'urutan' => 6],
                ['nama_dokumen' => 'SK Pengangkatan / Jabatan Struktural', 'kode_dokumen' => 'SK_STRUKTURAL', 'deskripsi' => 'Surat Keputusan jabatan struktural', 'is_wajib' => false, 'urutan' => 7],
                ['nama_dokumen' => 'SK Jabatan Fungsional Akademik', 'kode_dokumen' => 'SK_FUNGSIONAL', 'deskripsi' => 'SK Jafung (Asisten Ahli, Lektor, dll.)', 'is_wajib' => false, 'urutan' => 8],
                ['nama_dokumen' => 'SK Tugas Tambahan / PIC Kegiatan', 'kode_dokumen' => 'SK_TUGAS_TAMBAHAN', 'deskripsi' => 'SK kepanitiaan, PIC, atau penugasan khusus', 'is_wajib' => false, 'urutan' => 9],
                ['nama_dokumen' => 'Sertifikat Pendidik / Serdos', 'kode_dokumen' => 'SERDOS', 'deskripsi' => 'Sertifikat Pendidik Profesional Dosen', 'is_wajib' => false, 'urutan' => 10],
                ['nama_dokumen' => 'Sertifikat Kompetensi / Pelatihan', 'kode_dokumen' => 'SERTIFIKAT_PELATIHAN', 'deskripsi' => 'Sertifikat keahlian, uji kompetensi, atau workshop', 'is_wajib' => false, 'urutan' => 11],
                ['nama_dokumen' => 'Dokumen Lain-Lain', 'kode_dokumen' => 'LAINNYA', 'deskripsi' => 'Dokumen pendukung kepegawaian lainnya', 'is_wajib' => false, 'urutan' => 99],
            ];

            foreach ($defaultJenis as $item) {
                DB::table('master_jenis_dokumens')->insert(array_merge($item, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Tabel Dokumen Berkas Dinamis Karyawan
        if (!Schema::hasTable('karyawan_dokumen_berkas')) {
            Schema::create('karyawan_dokumen_berkas', function (Blueprint $table) {
                $table->id();
                $table->char('data_dosen_tendik_id', 36)->index();
                $table->unsignedBigInteger('master_jenis_dokumen_id')->nullable()->index();
                $table->string('nama_berkas', 200);
                $table->string('nomor_dokumen', 100)->nullable();
                $table->date('tanggal_dokumen')->nullable();
                $table->string('file_path', 255);
                $table->bigInteger('file_size')->nullable()->comment('Ukuran file dalam bytes');
                $table->string('file_extension', 10)->nullable();
                $table->text('keterangan')->nullable();
                $table->char('uploaded_by', 36)->nullable();
                $table->timestamps();

                $table->foreign('master_jenis_dokumen_id')
                    ->references('id')
                    ->on('master_jenis_dokumens')
                    ->onDelete('set null');

                $table->foreign('data_dosen_tendik_id')
                    ->references('id')
                    ->on('data_dosen_tendiks')
                    ->onDelete('cascade');
            });
        }

        // 3. Tambah Kontak Darurat di data_dosen_tendiks jika belum ada
        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            if (!Schema::hasColumn('data_dosen_tendiks', 'kontak_darurat_nama')) {
                $table->string('kontak_darurat_nama', 150)->nullable()->after('alamat_domisili');
            }
            if (!Schema::hasColumn('data_dosen_tendiks', 'kontak_darurat_hubungan')) {
                $table->string('kontak_darurat_hubungan', 50)->nullable()->after('kontak_darurat_nama');
            }
            if (!Schema::hasColumn('data_dosen_tendiks', 'kontak_darurat_no_hp')) {
                $table->string('kontak_darurat_no_hp', 50)->nullable()->after('kontak_darurat_hubungan');
            }
            if (!Schema::hasColumn('data_dosen_tendiks', 'kontak_darurat_alamat')) {
                $table->text('kontak_darurat_alamat')->nullable()->after('kontak_darurat_no_hp');
            }
        });

        // 4. Tambah file_sk dan sk_jabatan di karyawan_jabatan_strukturals
        Schema::table('karyawan_jabatan_strukturals', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan_jabatan_strukturals', 'sk_jabatan')) {
                $table->string('sk_jabatan', 255)->nullable()->after('tgl_akhir');
            }
            if (!Schema::hasColumn('karyawan_jabatan_strukturals', 'file_sk')) {
                $table->string('file_sk', 255)->nullable()->after('sk_jabatan');
            }
        });

        // 5. Tambah file_sk di karyawan_jabatan_fungsionals
        Schema::table('karyawan_jabatan_fungsionals', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawan_jabatan_fungsionals', 'file_sk')) {
                $table->string('file_sk', 255)->nullable()->after('sk_jabatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan_jabatan_fungsionals', function (Blueprint $table) {
            if (Schema::hasColumn('karyawan_jabatan_fungsionals', 'file_sk')) {
                $table->dropColumn('file_sk');
            }
        });

        Schema::table('karyawan_jabatan_strukturals', function (Blueprint $table) {
            if (Schema::hasColumn('karyawan_jabatan_strukturals', 'file_sk')) {
                $table->dropColumn('file_sk');
            }
            if (Schema::hasColumn('karyawan_jabatan_strukturals', 'sk_jabatan')) {
                $table->dropColumn('sk_jabatan');
            }
        });

        Schema::table('data_dosen_tendiks', function (Blueprint $table) {
            $cols = ['kontak_darurat_nama', 'kontak_darurat_hubungan', 'kontak_darurat_no_hp', 'kontak_darurat_alamat'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('data_dosen_tendiks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('karyawan_dokumen_berkas');
        Schema::dropIfExists('master_jenis_dokumens');
    }
};
