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
        // 1. Pastikan kolom nominal_tunjangan tidak ada di tabel master jabatan umum
        if (Schema::hasTable('master_jabatan_strukturals')) {
            Schema::table('master_jabatan_strukturals', function (Blueprint $table) {
                if (Schema::hasColumn('master_jabatan_strukturals', 'nominal_tunjangan')) {
                    $table->dropColumn('nominal_tunjangan');
                }
            });
        }

        if (Schema::hasTable('master_jabatan_fungsionals')) {
            Schema::table('master_jabatan_fungsionals', function (Blueprint $table) {
                if (Schema::hasColumn('master_jabatan_fungsionals', 'nominal_tunjangan')) {
                    $table->dropColumn('nominal_tunjangan');
                }
                if (Schema::hasColumn('master_jabatan_fungsionals', 'kode_jabatan')) {
                    $table->dropColumn('kode_jabatan');
                }
            });
        }

        // 2. Buat tabel master_pengaturan_tunjangans
        if (Schema::hasTable('master_pengaturan_tunjangans')) {
            Schema::dropIfExists('master_pengaturan_tunjangans');
        }

        Schema::create('master_pengaturan_tunjangans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('kategori', ['struktural', 'fungsional', 'keluarga'])->index()->comment('Kategori tunjangan');
            $table->string('kode', 50)->nullable()->comment('Kode jabatan / tunjangan');
            $table->string('nama_tunjangan', 255)->comment('Nama jabatan atau jenis tunjangan');
            
            // Relasi opsional ke master jabatan
            $table->char('jabatan_struktural_id', 36)->nullable()->index();
            $table->char('jabatan_fungsional_id', 36)->nullable()->index();

            // Nominal dan Persentase
            $table->decimal('nominal_dasar', 15, 2)->default(0)->comment('Nominal 100% / dasar per bulan');
            $table->decimal('persen_bayar', 5, 2)->default(100.00)->comment('Persentase pembayaran (%)');
            $table->decimal('nominal_tunjangan', 15, 2)->default(0)->comment('Nominal final yang dibayarkan per bulan');

            // Pengaturan khusus kategori Keluarga
            $table->decimal('persen_suami_istri', 5, 2)->nullable()->default(5.00)->comment('Persentase tunjangan suami/istri (%)');
            $table->decimal('persen_anak', 5, 2)->nullable()->default(2.00)->comment('Persentase tunjangan per anak (%)');
            $table->integer('maksimal_anak')->nullable()->default(2)->comment('Maksimal anak ditanggung');
            $table->string('basis_perhitungan', 50)->nullable()->default('gaji_tetap')->comment('gaji_tetap atau gaji_pokok');

            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Seeding Data Pengaturan Tunjangan Keluarga (Slide / Image 3)
        DB::table('master_pengaturan_tunjangans')->insert([
            'id'                 => (string) Str::uuid(),
            'kategori'           => 'keluarga',
            'kode'               => 'KELUARGA',
            'nama_tunjangan'     => 'Tunjangan Keluarga & Anak',
            'nominal_dasar'      => 0,
            'persen_bayar'       => 100.00,
            'nominal_tunjangan'  => 0,
            'persen_suami_istri' => 5.00,
            'persen_anak'        => 2.00,
            'maksimal_anak'      => 2,
            'basis_perhitungan'  => 'gaji_tetap',
            'keterangan'         => '5% dari gaji tetap untuk Suami/Istri, 2% dari gaji tetap per anak (Maksimal 2 anak)',
            'is_active'          => true,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 4. Seeding Data Tunjangan Fungsional (Slide / Image 3)
        $fungsionalList = [
            ['kode' => 'AA', 'nama' => 'Asisten Ahli',  'nominal' => 375000,  'keterangan' => 'Tunjangan Fungsional Asisten Ahli'],
            ['kode' => 'L',  'nama' => 'Lektor',        'nominal' => 700000,  'keterangan' => 'Tunjangan Fungsional Lektor'],
            ['kode' => 'LK', 'nama' => 'Lektor Kepala', 'nominal' => 900000,  'keterangan' => 'Tunjangan Fungsional Lektor Kepala'],
            ['kode' => 'GB', 'nama' => 'Guru Besar',    'nominal' => 1350000, 'keterangan' => 'Tunjangan Fungsional Guru Besar'],
            ['kode' => 'TP', 'nama' => 'Tenaga Pengajar','nominal' => 0,      'keterangan' => 'Tenaga Pengajar (Non-Fungsional)'],
        ];

        foreach ($fungsionalList as $item) {
            $jabFung = DB::table('master_jabatan_fungsionals')->where('nama_jabatan', 'like', "%{$item['nama']}%")->first();
            DB::table('master_pengaturan_tunjangans')->insert([
                'id'                    => (string) Str::uuid(),
                'kategori'              => 'fungsional',
                'kode'                  => $item['kode'],
                'nama_tunjangan'        => $item['nama'],
                'jabatan_fungsional_id' => $jabFung ? $jabFung->id : null,
                'nominal_dasar'         => $item['nominal'],
                'persen_bayar'          => 100.00,
                'nominal_tunjangan'     => $item['nominal'],
                'keterangan'            => $item['keterangan'],
                'is_active'             => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        // 5. Seeding Data Tunjangan Struktural (Slide / Image 1 & 2)
        $strukturalList = [
            // Jabatan Utama & Rektorat
            ['nama' => 'Rektor', 'dasar' => 7500000, 'persen' => 100.0, 'dibayar' => 7500000, 'ket' => 'Rektor (100%)'],
            ['nama' => 'Wakil Rektor', 'dasar' => 5600000, 'persen' => 100.0, 'dibayar' => 5600000, 'ket' => 'Wakil Rektor (100%)'],
            ['nama' => 'Sekretaris Universitas', 'dasar' => 3250000, 'persen' => 70.0, 'dibayar' => 2275000, 'ket' => 'Sekretaris Universitas (70%)'],

            // Dekanat & Fakultas
            ['nama' => 'Dekan Fakultas Teknik', 'dasar' => 3250000, 'persen' => 55.0, 'dibayar' => 1787500, 'ket' => 'Dekan FT (55%)'],
            ['nama' => 'Dekan Fakultas Sains dan Humaniora', 'dasar' => 3250000, 'persen' => 35.0, 'dibayar' => 1137500, 'ket' => 'Dekan FSH (35%)'],
            ['nama' => 'Dekan Sekolah Vokasi', 'dasar' => 3250000, 'persen' => 45.0, 'dibayar' => 1462500, 'ket' => 'Dekan SV (45%)'],
            ['nama' => 'Sekretaris Fakultas Teknik dan CIT', 'dasar' => 2025000, 'persen' => 55.0, 'dibayar' => 1175000, 'ket' => 'Sekretaris FT (55% = 1.113.750) + CIT (61.250)'],
            ['nama' => 'Sekretaris Fakultas Sains dan Humaniora dan Kepala Kantor Urusan Kemahasiswaan', 'dasar' => 2025000, 'persen' => 35.0, 'dibayar' => 1149750, 'ket' => 'Sekretaris FSH (35% = 708.750) + KUK (441.000)'],
            ['nama' => 'Sekretaris Sekolah Vokasi dan Pencegahan dan Penanganan Kekerasan di Perguruan Tinggi (PPKPT)', 'dasar' => 2025000, 'persen' => 45.0, 'dibayar' => 972500, 'ket' => 'Sekretaris SV (45% = 911.250) + PPKPT (61.250)'],

            // Direktur & Lembaga
            ['nama' => 'Direktur Akademik dan Kemahasiswaan', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 686000, 'ket' => 'Direktur Akademik & Kemahasiswaan (70%)'],
            ['nama' => 'Direktur Sumber Daya Manusia', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 686000, 'ket' => 'Direktur SDM (70%)'],
            ['nama' => 'Kepala LPM', 'dasar' => 2025000, 'persen' => 70.0, 'dibayar' => 1417500, 'ket' => 'Kepala LPM (70%)'],
            ['nama' => 'Kepala LPPM', 'dasar' => 2025000, 'persen' => 70.0, 'dibayar' => 1417500, 'ket' => 'Kepala LPPM (70%)'],

            // Biro & UPT
            ['nama' => 'Kepala BAAK', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala BAAK (70%)'],
            ['nama' => 'Kepala BAKPPU', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala BAKPPU (70%)'],
            ['nama' => 'Kepala BAUK', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala BAUK (70%)'],
            ['nama' => 'Kepala Pusat Informasi dan Komunikasi Digital (PIKDI)', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala PIKDI (70%)'],
            ['nama' => 'Kepala Perpustakaan', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala Perpustakaan (70%)'],
            ['nama' => 'Character Career Development Center (CCDC)', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala CCDC (70%)'],
            ['nama' => 'Kepala Kantor Urusan Kemahasiswaan', 'dasar' => 1260000, 'persen' => 70.0, 'dibayar' => 882000, 'ket' => 'Kepala Kantor Urusan Kemahasiswaan (70%)'],
            ['nama' => 'Sekretaris LPM', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 686000, 'ket' => 'Sekretaris LPM (70%)'],
            ['nama' => 'Sekretaris LPPM', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 686000, 'ket' => 'Sekretaris LPPM (70%)'],
            ['nama' => 'SPI', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 686000, 'ket' => 'Satuan Pengawas Internal (70%)'],
            ['nama' => 'Kepala Bidang Kerjasama dan Program Kreativitas Mahasiswa (PKM)', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 747250, 'ket' => 'Kabid Kerjasama (686.000) + PKM (61.250)'],
            ['nama' => 'Kepala Bidang KUI dan Humas', 'dasar' => 980000, 'persen' => 70.0, 'dibayar' => 686000, 'ket' => 'Kabid KUI & Humas (70%)'],
            ['nama' => 'SPMI dan Sistem Informasi Kinerja dan Tata Kelola Kemahasiswaan (SIMKATMAWA)', 'dasar' => 700000, 'persen' => 70.0, 'dibayar' => 551250, 'ket' => 'SPMI (490.000) + SIMKATMAWA (61.250)'],
            ['nama' => 'SPME', 'dasar' => 700000, 'persen' => 70.0, 'dibayar' => 490000, 'ket' => 'SPME (70%)'],
            ['nama' => 'Kepala Sub-bagian', 'dasar' => 700000, 'persen' => 70.0, 'dibayar' => 490000, 'ket' => 'Kepala Sub-bagian (70%)'],

            // Program Studi (Kaprodi & Sekprodi)
            ['nama' => 'Kepala Program Studi S1 SI', 'dasar' => 1260000, 'persen' => 55.0, 'dibayar' => 693000, 'ket' => 'Kaprodi S1 SI (55%)'],
            ['nama' => 'Kepala Program Studi S1 INF', 'dasar' => 1260000, 'persen' => 55.0, 'dibayar' => 693000, 'ket' => 'Kaprodi S1 INF (55%)'],
            ['nama' => 'Sekretaris Program Studi S1 SI', 'dasar' => 980000, 'persen' => 45.0, 'dibayar' => 441000, 'ket' => 'Sekprodi S1 SI (45%)'],
            ['nama' => 'Sekretaris Program Studi S1 INF', 'dasar' => 980000, 'persen' => 45.0, 'dibayar' => 441000, 'ket' => 'Sekprodi S1 INF (45%)'],
            ['nama' => 'Kepala Program Studi D3 TI dan Persekutuan Mahasiswa Kristen Katolik (PMKK)', 'dasar' => 1260000, 'persen' => 45.0, 'dibayar' => 628250, 'ket' => 'Kaprodi D3 TI (45% = 567.000) + PMKK (61.250)'],
            ['nama' => 'Kepala Program Studi D3 SI', 'dasar' => 1260000, 'persen' => 45.0, 'dibayar' => 567000, 'ket' => 'Kaprodi D3 SI (45%)'],
            ['nama' => 'Sekretaris Program Studi D3 SI dan Akhlakul Karimah', 'dasar' => 980000, 'persen' => 35.0, 'dibayar' => 404250, 'ket' => 'Sekprodi D3 SI (35% = 343.000) + Akhlakul Karimah (61.250)'],
            ['nama' => 'Kepala Program Studi S1 RK', 'dasar' => 1260000, 'persen' => 35.0, 'dibayar' => 441000, 'ket' => 'Kaprodi S1 RK (35%)'],
            ['nama' => 'Kepala Program Studi S1 MNJ', 'dasar' => 1260000, 'persen' => 35.0, 'dibayar' => 441000, 'ket' => 'Kaprodi S1 MNJ (35%)'],
            ['nama' => 'Kepala Program Studi S1 PGSD', 'dasar' => 1260000, 'persen' => 35.0, 'dibayar' => 441000, 'ket' => 'Kaprodi S1 PGSD (35%)'],
            ['nama' => 'Kepala Program Studi S1 PSI', 'dasar' => 1260000, 'persen' => 35.0, 'dibayar' => 441000, 'ket' => 'Kaprodi S1 PSI (35%)'],
            ['nama' => 'Kepala Program Studi D3 DKV', 'dasar' => 1260000, 'persen' => 35.0, 'dibayar' => 441000, 'ket' => 'Kaprodi D3 DKV (35%)'],
            ['nama' => 'Kepala Program Studi D3 DPT', 'dasar' => 1260000, 'persen' => 35.0, 'dibayar' => 441000, 'ket' => 'Kaprodi D3 DPT (35%)'],
            ['nama' => 'Sekretaris Program Studi S1 RK', 'dasar' => 980000, 'persen' => 25.0, 'dibayar' => 245000, 'ket' => 'Sekprodi S1 RK (25%)'],
            ['nama' => 'Sekretaris Program Studi S1 MNJ', 'dasar' => 980000, 'persen' => 25.0, 'dibayar' => 245000, 'ket' => 'Sekprodi S1 MNJ (25%)'],
            ['nama' => 'Sekretaris Program Studi S1 PGSD dan Forum of Volunteer (FORVOL)', 'dasar' => 980000, 'persen' => 25.0, 'dibayar' => 306250, 'ket' => 'Sekprodi S1 PGSD (25% = 245.000) + FORVOL (61.250)'],
            ['nama' => 'Sekretaris Program Studi S1 PSI dan Lembaga Dakwah Kampus (LDK)', 'dasar' => 980000, 'persen' => 25.0, 'dibayar' => 306250, 'ket' => 'Sekprodi S1 PSI (25% = 245.000) + LDK (61.250)'],
            ['nama' => 'Sekretaris Program Studi D3 DKV', 'dasar' => 980000, 'persen' => 25.0, 'dibayar' => 245000, 'ket' => 'Sekprodi D3 DKV (25%)'],
            ['nama' => 'Sekretaris Program Studi D3 DPT', 'dasar' => 980000, 'persen' => 25.0, 'dibayar' => 245000, 'ket' => 'Sekprodi D3 DPT (25%)'],

            // Ormawa & Penugasan Khusus Kemahasiswaan
            ['nama' => 'Program Pembinaan Mahasiswa Wirausaha (P2MW) dan Kewirausahaan Kelompok Studi Pasar Modal (KSPM)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 183750, 'ket' => 'P2MW & KSPM (122.500 + 61.250)'],
            ['nama' => 'Badan Eksekutif Mahasiswa (BEM)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina BEM (50%)'],
            ['nama' => 'Akhlakul Karimah', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina Akhlakul Karimah (50%)'],
            ['nama' => 'English Club', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina English Club (50%)'],
            ['nama' => 'Persekutuan Mahasiswa Kristen Katolik (PMKK)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina PMKK (50%)'],
            ['nama' => 'Beasiswa KIP Kuliah', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pengelola Beasiswa KIP Kuliah (50%)'],
            ['nama' => 'All of Visual Capture (ALVIC)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina ALVIC (50%)'],
            ['nama' => 'Community of Information Technology (CIT)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina CIT (50%)'],
            ['nama' => 'Beasiswa Siti Aminah (BSA)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pengelola BSA (50%)'],
            ['nama' => 'Paduan Suara Mahasiswa (PSM)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina PSM (50%)'],
            ['nama' => 'MPA - PASTERA', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina PASTERA (50%)'],
            ['nama' => 'Pencegahan dan Penanganan Kekerasan di Perguruan Tinggi (PPKPT)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Ketua Satgas PPKPT (50%)'],
            ['nama' => 'Korps Suka Rela (KSR)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina KSR (50%)'],
            ['nama' => 'TSU Sport', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina TSU Sport (50%)'],
            ['nama' => 'Sistem Informasi Kinerja dan Tata Kelola Kemahasiswaan (SIMKATMAWA)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pengelola SIMKATMAWA (50%)'],
            ['nama' => 'Program Kreativitas Mahasiswa (PKM)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pengelola PKM (50%)'],
            ['nama' => 'Lembaga Dakwah Kampus (LDK)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina LDK (50%)'],
            ['nama' => 'Forum of Volunteer (FORVOL)', 'dasar' => 245000, 'persen' => 50.0, 'dibayar' => 122500, 'ket' => 'Pembina FORVOL (50%)'],
        ];

        foreach ($strukturalList as $item) {
            $jabStruk = DB::table('master_jabatan_strukturals')->where('nama_jabatan', 'like', "%{$item['nama']}%")->first();
            DB::table('master_pengaturan_tunjangans')->insert([
                'id'                    => (string) Str::uuid(),
                'kategori'              => 'struktural',
                'kode'                  => null,
                'nama_tunjangan'        => $item['nama'],
                'jabatan_struktural_id' => $jabStruk ? $jabStruk->id : null,
                'nominal_dasar'         => $item['dasar'],
                'persen_bayar'          => $item['persen'],
                'nominal_tunjangan'     => $item['dibayar'],
                'keterangan'            => $item['ket'],
                'is_active'             => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pengaturan_tunjangans');
    }
};
