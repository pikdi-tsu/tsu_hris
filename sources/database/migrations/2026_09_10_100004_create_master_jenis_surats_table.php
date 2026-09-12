<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('master_jenis_surats')) {
            Schema::create('master_jenis_surats', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama_surat');
                $table->string('kode_surat', 50)->nullable();
                $table->text('deskripsi')->nullable();
                $table->tinyInteger('perlu_lampiran')->default(0); // 1: Wajib, 0: Opsional
                $table->string('format_penomoran', 100)->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });

            // Seed default letter types
            $defaultSurats = [
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Keterangan Kerja Aktif',
                    'kode_surat' => 'SKK',
                    'deskripsi' => 'Surat keterangan resmi bahwa pegawai masih aktif bekerja di lingkungan Tiga Serangkai University.',
                    'perlu_lampiran' => 0,
                    'is_active' => 1,
                    'urutan' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Pengantar Pengajuan KPR / Bank',
                    'kode_surat' => 'KPR',
                    'deskripsi' => 'Surat pengantar rekomendasi untuk pengajuan kredit kepemilikan rumah, pinjaman perbankan, atau pembukaan rekening.',
                    'perlu_lampiran' => 0,
                    'is_active' => 1,
                    'urutan' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Pengantar Studi Lanjut / Beasiswa',
                    'kode_surat' => 'STUDI',
                    'deskripsi' => 'Surat izin atau rekomendasi pimpinan universitas untuk mengikuti seleksi beasiswa atau pendaftaran studi lanjut.',
                    'perlu_lampiran' => 1,
                    'is_active' => 1,
                    'urutan' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Keterangan Visa / Pembuatan Paspor',
                    'kode_surat' => 'VISA',
                    'deskripsi' => 'Surat jaminan kepegawaian (employment certificate) untuk pengajuan visa kedutaan atau pembuatan paspor.',
                    'perlu_lampiran' => 1,
                    'is_active' => 1,
                    'urutan' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Rekomendasi Kepegawaian',
                    'kode_surat' => 'REK',
                    'deskripsi' => 'Surat rekomendasi kinerja atau tugas profesional di luar kampus.',
                    'perlu_lampiran' => 0,
                    'is_active' => 1,
                    'urutan' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Keterangan Bebas Tanggungan',
                    'kode_surat' => 'SBT',
                    'deskripsi' => 'Surat keterangan bebas kewajiban dan tanggungan kerja/fasilitas kampus.',
                    'perlu_lampiran' => 0,
                    'is_active' => 1,
                    'urutan' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'nama_surat' => 'Surat Keterangan Lainnya',
                    'kode_surat' => 'LAIN',
                    'deskripsi' => 'Permohonan surat dinas kepegawaian lainnya yang belum terdaftar.',
                    'perlu_lampiran' => 0,
                    'is_active' => 1,
                    'urutan' => 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('master_jenis_surats')->insert($defaultSurats);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jenis_surats');
    }
};
