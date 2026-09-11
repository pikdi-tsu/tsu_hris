<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom kategori_cuti, memotong_kuota, khusus_pegawai_tetap pada master_cuti
        if (Schema::hasTable('master_cuti')) {
            Schema::table('master_cuti', function (Blueprint $table) {
                if (!Schema::hasColumn('master_cuti', 'kategori_cuti')) {
                    $table->enum('kategori_cuti', ['tahunan', 'khusus'])->default('tahunan')->after('jeniscuti')->index();
                }
                if (!Schema::hasColumn('master_cuti', 'memotong_kuota')) {
                    $table->boolean('memotong_kuota')->default(false)->after('kategori_cuti');
                }
                if (!Schema::hasColumn('master_cuti', 'khusus_pegawai_tetap')) {
                    $table->boolean('khusus_pegawai_tetap')->default(true)->after('memotong_kuota');
                }
                if (!Schema::hasColumn('master_cuti', 'keterangan_edaran')) {
                    $table->string('keterangan_edaran', 255)->nullable()->after('minimalhari');
                }
            });

            // Set Cuti Tahunan yang sudah ada agar memotong_kuota = true
            DB::table('master_cuti')->where('id', 1)->update([
                'kategori_cuti'        => 'tahunan',
                'memotong_kuota'       => true,
                'khusus_pegawai_tetap' => false,
            ]);
        }

        // 2. Tambah kolom file_bukti pada cuti_karyawan
        if (Schema::hasTable('cuti_karyawan')) {
            Schema::table('cuti_karyawan', function (Blueprint $table) {
                if (!Schema::hasColumn('cuti_karyawan', 'file_bukti')) {
                    $table->string('file_bukti', 255)->nullable()->after('keterangan');
                }
            });
        }

        // 3. Seed data Master Cuti Khusus sesuai Surat Edaran & Regulasi Ketenagakerjaan
        $cutiKhususDefaults = [
            [
                'jeniscuti'            => 'Cuti Melahirkan (Maternity)',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 90,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Keterangan Dokter Kandungan / Bidan',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Keguguran Kandungan',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 45,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Keterangan Dokter Spesialis Kandungan',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Pernikahan Karyawan',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 3,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Undangan Pernikahan / Surat KUA/Gereja',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Menikahkan Anak',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 2,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Undangan Pernikahan Anak',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Khitanan / Pembaptisan Anak',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 2,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Keterangan Khitanan / Baptis Anak',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Istri Melahirkan / Keguguran',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 2,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Keterangan Kelahiran / RS',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Kematian Pasangan/Ortu/Anak/Mertua',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 2,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Kematian dari RS / Kelurahan',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Kematian Anggota Serumah',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 1,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Kematian & Kartu Keluarga',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Ibadah Haji / Umrah',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 40,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Surat Panggilan / Paspor / Tiket Keberangkatan',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
            [
                'jeniscuti'            => 'Cuti Wisuda (Karyawan / Anak)',
                'kategori_cuti'        => 'khusus',
                'durasicuti'           => 1,
                'minimalhari'          => 1,
                'memotong_kuota'       => false,
                'khusus_pegawai_tetap' => true,
                'keterangan_edaran'    => 'Undangan Wisuda Resmi',
                'is_active'            => '1',
                'created_at'           => now(),
            ],
        ];

        foreach ($cutiKhususDefaults as $c) {
            $existing = DB::table('master_cuti')->where('jeniscuti', $c['jeniscuti'])->first();
            if (!$existing) {
                DB::table('master_cuti')->insert($c);
            } else {
                DB::table('master_cuti')->where('id', $existing->id)->update([
                    'kategori_cuti'        => 'khusus',
                    'memotong_kuota'       => false,
                    'khusus_pegawai_tetap' => true,
                    'keterangan_edaran'    => $c['keterangan_edaran'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cuti_karyawan') && Schema::hasColumn('cuti_karyawan', 'file_bukti')) {
            Schema::table('cuti_karyawan', function (Blueprint $table) {
                $table->dropColumn('file_bukti');
            });
        }

        if (Schema::hasTable('master_cuti')) {
            Schema::table('master_cuti', function (Blueprint $table) {
                $table->dropColumn(['kategori_cuti', 'memotong_kuota', 'khusus_pegawai_tetap', 'keterangan_edaran']);
            });
        }
    }
};
