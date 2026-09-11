<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiPeriode;
use App\Models\KpiMasterPerspektif;
use App\Models\KpiMasterIndikator;
use App\Models\KpiUnitIndikator;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\DB;

class KpiInitialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed 4 Perspektif BSC
        $perspektifs = [
            [
                'kode'            => 'FIN',
                'nama_perspektif' => 'Financial (Keuangan)',
                'deskripsi'       => 'Pilar orientasi kinerja keuangan, efisiensi anggaran, akuntabilitas audit, dan diversifikasi revenue stream universitas.',
                'warna_badge'     => 'success',
                'urutan'          => 1,
            ],
            [
                'kode'            => 'CUS',
                'nama_perspektif' => 'Customer (Pelanggan / Pemangku Kepentingan)',
                'deskripsi'       => 'Pilar kepuasan mahasiswa, orang tua, alumni, employability lulusan di dunia kerja, dan kepuasan mitra industri.',
                'warna_badge'     => 'info',
                'urutan'          => 2,
            ],
            [
                'kode'            => 'INT',
                'nama_perspektif' => 'Internal Process (Proses Bisnis Internal)',
                'deskripsi'       => 'Pilar keunggulan operasional kurikulum MBKM, riset inovasi, akreditasi prodi, efektivitas persuratan, dan sarana prasarana.',
                'warna_badge'     => 'primary',
                'urutan'          => 3,
            ],
            [
                'kode'            => 'LRN',
                'nama_perspektif' => 'Learning & Growth (Pembelajaran & Pertumbuhan)',
                'deskripsi'       => 'Pilar peningkatan kapasitas SDM dosen S3, jabatan fungsional Lektor Kepala/Guru Besar, sertifikasi tendik, dan budaya organisasi.',
                'warna_badge'     => 'warning',
                'urutan'          => 4,
            ],
        ];

        $perspMap = [];
        foreach ($perspektifs as $p) {
            $model = KpiMasterPerspektif::updateOrCreate(
                ['kode' => $p['kode']],
                $p
            );
            $perspMap[$p['kode']] = $model->id;
        }

        // 2. Seed Periode Aktif 2026
        $periode2026 = KpiPeriode::updateOrCreate(
            ['tahun' => 2026],
            [
                'nama_periode'    => 'KPI & Cascading Scorecard Tahun 2026',
                'tanggal_mulai'   => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'is_active'       => 1,
                'is_locked'       => 0,
                'keterangan'      => 'Tahun kalender acuan penyusunan KPI Balanced Scorecard Universitas Tiga Serangkai (TSU)',
            ]
        );

        // 3. Seed Kamus Master Indikator (Hierarkis Induk & Sub-Indikator Pendekatan 1)
        $masterData = [
            // LEARNING & GROWTH
            [
                'perspektif' => 'LRN',
                'kode'       => 'LRN-01',
                'nama'       => 'Dosen Berpendidikan S3 (Doktor)',
                'definisi'   => 'Persentase dosen tetap yang telah menyelesaikan studi doktoral (S3)',
                'formula'    => '(Jumlah Dosen S3 ÷ Total Dosen Tetap) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'LRN',
                'kode'       => 'LRN-02',
                'nama'       => 'Jabatan Fungsional LK / GB',
                'definisi'   => 'Persentase dosen yang memiliki jabatan akademik Lektor Kepala (LK) dan/atau Guru Besar (GB)',
                'formula'    => '(Jumlah Dosen LK & GB ÷ Total Dosen) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'LRN',
                'kode'       => 'LRN-03',
                'nama'       => 'Dosen Magang Industri',
                'definisi'   => 'Dosen melaksanakan magang industri selama minimal 2 (dua) bulan',
                'formula'    => '(Jumlah Dosen Magang ÷ Total Dosen) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
                'subs'       => [
                    [
                        'kode'      => 'LRN-03a',
                        'nama'      => 'Dosen Magang di Tiga Serangkai (TS) Grup',
                        'definisi'  => 'Dosen melaksanakan magang industri pada perusahaan di lingkungan TS Grup',
                        'formula'   => '(Jumlah Dosen Magang TS Grup ÷ Total Dosen) × 100%',
                        'satuan'    => '%',
                    ],
                    [
                        'kode'      => 'LRN-03b',
                        'nama'      => 'Dosen Magang di Mitra Industri Program Studi',
                        'definisi'  => 'Dosen melaksanakan magang industri pada perusahaan mitra prodi',
                        'formula'   => '(Jumlah Dosen Magang Mitra ÷ Total Dosen) × 100%',
                        'satuan'    => '%',
                    ]
                ]
            ],
            [
                'perspektif' => 'LRN',
                'kode'       => 'LRN-04',
                'nama'       => 'Sertifikasi Dosen (Serdos)',
                'definisi'   => 'Persentase dosen tetap yang telah memiliki Sertifikat Pendidik Profesional',
                'formula'    => '(Jumlah Dosen Bersertifikat ÷ Total Dosen) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'LRN',
                'kode'       => 'LRN-05',
                'nama'       => 'Pengembangan Kompetensi SDM Tendik',
                'definisi'   => 'Persentase tenaga kependidikan yang memiliki sertifikasi kompetensi/profesi sesuai bidang',
                'formula'    => '(Jumlah Tendik Bersertifikat ÷ Total Tendik) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],

            // FINANCIAL
            [
                'perspektif' => 'FIN',
                'kode'       => 'FIN-01',
                'nama'       => 'Hasil Audit Laporan Keuangan Institusi',
                'definisi'   => 'Opini wajar tanpa pengecualian atas laporan keuangan perguruan tinggi oleh auditor independen',
                'formula'    => 'Opini Audit Independen',
                'satuan'     => 'Opini WTP',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'FIN',
                'kode'       => 'FIN-02',
                'nama'       => 'Ketepatan Waktu Penerimaan & Rekonsiliasi UKT',
                'definisi'   => 'Penyusunan laporan penerimaan UKT tepat waktu maksimal tanggal 10 setiap bulan',
                'formula'    => '(Laporan Tepat Waktu ÷ Total Periode) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'FIN',
                'kode'       => 'FIN-03',
                'nama'       => 'Optimalisasi Pendapatan dari Aset Tetap',
                'definisi'   => 'Pendapatan sewa dan komersialisasi aset tetap dibanding target pendapatan',
                'formula'    => '(Realisasi Pendapatan ÷ Target) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'FIN',
                'kode'       => 'FIN-04',
                'nama'       => 'Kesejahteraan Dosen dan Standar Penggajian',
                'definisi'   => 'Kesesuaian penyusunan struktur remunerasi dan standar penggajian dosen/tendik',
                'formula'    => 'Tingkat Kepatuhan Standar Gaji Acuan',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],

            // INTERNAL PROCESS
            [
                'perspektif' => 'INT',
                'kode'       => 'INT-01',
                'nama'       => 'Mahasiswa Mengikuti Program Magang Bersertifikat',
                'definisi'   => 'Persentase mahasiswa aktif yang mengikuti magang bersertifikat minimal 1 semester',
                'formula'    => '(Mahasiswa Magang ÷ Total Mahasiswa Aktif) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
                'subs'       => [
                    [
                        'kode'      => 'INT-01a',
                        'nama'      => 'Mahasiswa Magang di Perusahaan TS Grup',
                        'definisi'  => 'Mahasiswa magang di entitas bisnis Tiga Serangkai',
                        'formula'   => '(Mahasiswa Magang TS ÷ Total Mahasiswa Magang) × 100%',
                        'satuan'    => '%',
                    ],
                    [
                        'kode'      => 'INT-01b',
                        'nama'      => 'Mahasiswa Magang di Perusahaan Mitra Eksternal',
                        'definisi'  => 'Mahasiswa magang pada BUMN atau industri mitra eksternal',
                        'formula'   => '(Mahasiswa Magang Eksternal ÷ Total Mahasiswa Magang) × 100%',
                        'satuan'    => '%',
                    ]
                ]
            ],
            [
                'perspektif' => 'INT',
                'kode'       => 'INT-02',
                'nama'       => 'Akreditasi Program Studi (Unggul / Baik Sekali)',
                'definisi'   => 'Persentase program studi dengan peringkat akreditasi Unggul atau Baik Sekali',
                'formula'    => '(Prodi Terakreditasi Unggul/Baik Sekali ÷ Total Prodi) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'INT',
                'kode'       => 'INT-03',
                'nama'       => 'Pemeliharaan Sarana dan Prasarana Kampus',
                'definisi'   => 'Keandalan sarana pembelajaran, ruang kelas, laboratorium, dan utilitas kampus',
                'formula'    => 'Indeks Keandalan Sarpras',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
                'subs'       => [
                    [
                        'kode'      => 'INT-03a',
                        'nama'      => 'Tingkat Ketersediaan Sarana Utama',
                        'definisi'  => 'Ketersediaan sarana ruang kuliah, lab, dan listrik',
                        'formula'   => '(Sarana Siap Pakai ÷ Total Sarana) × 100%',
                        'satuan'    => '%',
                    ],
                    [
                        'kode'      => 'INT-03b',
                        'nama'      => 'Kecepatan Penanganan Perbaikan Sarpras',
                        'definisi'  => 'Maksimal hari kerja dalam penyelesaian tiket perbaikan fasilitas',
                        'formula'   => 'Maksimal Hari Kerja',
                        'satuan'    => 'Hari',
                        'polaritas' => 'Minimize',
                    ]
                ]
            ],

            // CUSTOMER
            [
                'perspektif' => 'CUS',
                'kode'       => 'CUS-01',
                'nama'       => 'Indeks Kepuasan Mahasiswa (Student Satisfaction)',
                'definisi'   => 'Kepuasan mahasiswa terhadap pembelajaran, sarana, dan layanan akademik',
                'formula'    => 'Nilai Survei Kepuasan',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
            [
                'perspektif' => 'CUS',
                'kode'       => 'CUS-02',
                'nama'       => 'Employability Lulusan Bekerja ≤ 6 Bulan',
                'definisi'   => 'Persentase lulusan yang telah bekerja atau berwirausaha dalam waktu ≤ 6 bulan',
                'formula'    => '(Lulusan Bekerja ÷ Total Lulusan) × 100%',
                'satuan'     => '%',
                'polaritas'  => 'Maximize',
            ],
        ];

        $masterIndMap = [];
        $seq = 1;
        foreach ($masterData as $md) {
            $subs = $md['subs'] ?? [];
            unset($md['subs']);

            $ind = KpiMasterIndikator::updateOrCreate(
                ['kode_indikator' => $md['kode']],
                [
                    'perspektif_id'         => $perspMap[$md['perspektif']],
                    'parent_id'             => null,
                    'level'                 => 'induk',
                    'nama_indikator'        => $md['nama'],
                    'deskripsi'             => $md['definisi'],
                    'formula_penghitungan'  => $md['formula'],
                    'satuan'                => $md['satuan'],
                    'polaritas'             => $md['polaritas'],
                    'tipe_target'           => $md['satuan'] === '%' ? 'Persentase' : 'Angka',
                    'urutan'                => $seq++,
                    'is_active'             => 1,
                ]
            );
            $masterIndMap[$md['kode']] = $ind->id;

            // Sub-indikator
            if (!empty($subs)) {
                $subSeq = 1;
                foreach ($subs as $s) {
                    KpiMasterIndikator::updateOrCreate(
                        ['kode_indikator' => $s['kode']],
                        [
                            'perspektif_id'         => $perspMap[$md['perspektif']],
                            'parent_id'             => $ind->id,
                            'level'                 => 'sub',
                            'nama_indikator'        => $s['nama'],
                            'deskripsi'             => $s['definisi'],
                            'formula_penghitungan'  => $s['formula'],
                            'satuan'                => $s['satuan'],
                            'polaritas'             => $s['polaritas'] ?? $md['polaritas'],
                            'tipe_target'           => $s['satuan'] === '%' ? 'Persentase' : 'Angka',
                            'urutan'                => $subSeq++,
                            'is_active'             => 1,
                        ]
                    );
                }
            }
        }

        // 4. Seed Cascading Scorecard Unit Riil TSU
        $unitBauk = MasterUnit::where('nama_unit', 'like', '%bauk%')
            ->orWhere('nama_unit', 'like', '%administrasi umum%')
            ->first() ?? MasterUnit::first();

        $unitSekretariat = MasterUnit::where('nama_unit', 'like', '%sekretariat%')->first()
            ?? $unitBauk;

        if ($unitBauk) {
            // Indikator Pimpinan (WR 2) - Kesejahteraan Dosen
            $kpiWr2 = KpiUnitIndikator::updateOrCreate(
                [
                    'periode_id'          => $periode2026->id,
                    'master_unit_id'      => $unitBauk->id,
                    'master_indikator_id' => $masterIndMap['FIN-04'],
                ],
                [
                    'parent_unit_indikator_id' => null,
                    'jenis_cascading'          => 'Direct',
                    'target_angka'             => 85.0,
                    'target_label'             => '85%',
                    'satuan'                   => '%',
                    'bobot'                    => 15.0,
                    'target_2026'              => '85%',
                    'target_2027'              => '90%',
                    'target_2028'              => '92%',
                    'target_2029'              => '95%',
                    'keterkaitan_iku'          => 'IKU 1',
                    'sumber_data'              => 'Standar Renstra Remunerasi Yayasan',
                    'pic_data'                 => 'Wakil Rektor Bidang Sumber Daya',
                    'realisasi_angka'          => 85.0,
                    'realisasi_label'          => '85%',
                    'capaian_persen'           => 100.0,
                    'skor'                     => 15.0,
                    'status_monev'             => 'Terevaluasi',
                ]
            );

            // Turunan Cascading ke Kepala BAUK (Direct)
            $kpiBauk = KpiUnitIndikator::updateOrCreate(
                [
                    'periode_id'               => $periode2026->id,
                    'master_unit_id'           => $unitBauk->id,
                    'master_indikator_id'      => $masterIndMap['FIN-04'],
                    'parent_unit_indikator_id' => $kpiWr2->id,
                ],
                [
                    'jenis_cascading'  => 'Direct',
                    'target_angka'     => 85.0,
                    'target_label'     => '85%',
                    'satuan'           => '%',
                    'bobot'            => 20.0,
                    'keterkaitan_iku'  => 'IKU 1',
                    'sumber_data'      => 'Tabel Gaji Pokok & Remunerasi',
                    'pic_data'         => 'Kepala Biro BAUK',
                    'realisasi_angka'  => 85.0,
                    'realisasi_label'  => '85%',
                    'capaian_persen'   => 100.0,
                    'skor'             => 20.0,
                    'status_monev'     => 'Terevaluasi',
                ]
            );

            // Turunan Cascading ke Bagian Keuangan (Enabler - Ketepatan UKT)
            KpiUnitIndikator::updateOrCreate(
                [
                    'periode_id'          => $periode2026->id,
                    'master_unit_id'      => $unitBauk->id,
                    'master_indikator_id' => $masterIndMap['FIN-02'],
                ],
                [
                    'parent_unit_indikator_id' => $kpiWr2->id,
                    'jenis_cascading'          => 'Enabler',
                    'target_angka'             => 100.0,
                    'target_label'             => '100%',
                    'satuan'                   => '%',
                    'bobot'                    => 15.0,
                    'keterkaitan_iku'          => 'IKU 8',
                    'sumber_data'              => 'Laporan Penerimaan Bank & SIAKAD',
                    'pic_data'                 => 'Bendahara Penerimaan BAUK',
                    'realisasi_angka'          => 100.0,
                    'realisasi_label'          => '100%',
                    'capaian_persen'           => 100.0,
                    'skor'                     => 15.0,
                    'status_monev'             => 'Terevaluasi',
                ]
            );

            // Indikator Tendik Bersertifikat (LRN-05) di BAUK
            KpiUnitIndikator::updateOrCreate(
                [
                    'periode_id'          => $periode2026->id,
                    'master_unit_id'      => $unitBauk->id,
                    'master_indikator_id' => $masterIndMap['LRN-05'],
                ],
                [
                    'parent_unit_indikator_id' => null,
                    'jenis_cascading'          => 'Contribution',
                    'target_angka'             => 75.0,
                    'target_label'             => '75%',
                    'satuan'                   => '%',
                    'bobot'                    => 25.0,
                    'keterkaitan_iku'          => 'IKU 4',
                    'sumber_data'              => 'Database Sertifikasi SDM HRIS',
                    'pic_data'                 => 'Kasubag SDM BAUK',
                    'realisasi_angka'          => 70.0,
                    'realisasi_label'          => '70%',
                    'capaian_persen'           => 93.33,
                    'skor'                     => 23.33,
                    'status_monev'             => 'Terevaluasi',
                ]
            );

            // Indikator Sarpras Kecepatan Perbaikan (INT-03b)
            $sarprasSubId = KpiMasterIndikator::where('kode_indikator', 'INT-03b')->value('id');
            if ($sarprasSubId) {
                KpiUnitIndikator::updateOrCreate(
                    [
                        'periode_id'          => $periode2026->id,
                        'master_unit_id'      => $unitBauk->id,
                        'master_indikator_id' => $sarprasSubId,
                    ],
                    [
                        'parent_unit_indikator_id' => null,
                        'jenis_cascading'          => 'Direct',
                        'target_angka'             => 3.0,
                        'target_label'             => '3 Hari',
                        'satuan'                   => 'Hari',
                        'bobot'                    => 25.0,
                        'keterkaitan_iku'          => 'Standar Sarpras SPMI',
                        'sumber_data'              => 'Logbook Penanganan Tiket Sarpras',
                        'pic_data'                 => 'Kasubag Sarpras & RT',
                        'realisasi_angka'          => 2.0,
                        'realisasi_label'          => '2 Hari',
                        'capaian_persen'           => 150.0,
                        'skor'                     => 37.5,
                        'status_monev'             => 'Terevaluasi',
                    ]
                );
            }
        }

        // Indikator Sekretariat (INT-01 / MBKM & Persuratan)
        if ($unitSekretariat && $unitSekretariat->id != $unitBauk->id) {
            KpiUnitIndikator::updateOrCreate(
                [
                    'periode_id'          => $periode2026->id,
                    'master_unit_id'      => $unitSekretariat->id,
                    'master_indikator_id' => $masterIndMap['LRN-05'],
                ],
                [
                    'parent_unit_indikator_id' => null,
                    'jenis_cascading'          => 'Direct',
                    'target_angka'             => 100.0,
                    'target_label'             => '100%',
                    'satuan'                   => '%',
                    'bobot'                    => 100.0,
                    'keterkaitan_iku'          => 'Standar Pelayanan Prima',
                    'sumber_data'              => 'Buku Agenda Surat & Sertifikat',
                    'pic_data'                 => 'Kepala Sekretariat Rektorat',
                    'realisasi_angka'          => 95.0,
                    'realisasi_label'          => '95%',
                    'capaian_persen'           => 95.0,
                    'skor'                     => 95.0,
                    'status_monev'             => 'Terevaluasi',
                ]
            );
        }

        echo "KPI Initial Seeder completed successfully!\n";
    }
}
