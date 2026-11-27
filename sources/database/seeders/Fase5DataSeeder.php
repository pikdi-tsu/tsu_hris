<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\MasterUnit;
use App\Models\DataDosenTendik;
use App\Models\Training;
use App\Models\TrainingPeserta;
use App\Models\SurveyPelatihanResponse;
use App\Models\AuditSpiTemuan;
use App\Models\AuditLpmTemuan;
use App\Models\KpiPeriode;
use App\Models\KpiMasterIndikator;
use App\Models\KpiMasterPerspektif;
use App\Models\KpiUnitIndikator;

class Fase5DataSeeder extends Seeder
{
    public function run()
    {
        // -------------------------------------------------------------
        // 1. SEED DATA TRAINING & SURVEY KEPUASAN TRAINING (MOCKUP 11)
        // -------------------------------------------------------------
        $firstKaryawan = DataDosenTendik::first();
        $karyawanId = $firstKaryawan ? $firstKaryawan->id : null;

        $trainingsData = [
            [
                'nama_training' => 'Digital Leadership for Higher Education',
                'penyelenggara' => 'Lembaga Pelatihan Nusantara',
                'lokasi' => 'Ruang Seminar Lantai 3, Gedung A',
                'tanggal_mulai' => '2026-01-12',
                'tanggal_selesai' => '2026-01-13',
                'deskripsi' => 'Penguatan kepemimpinan digital dan transformasi teknologi perguruan tinggi.',
                'status' => 'Selesai',
                'is_survey_filled' => true,
                'skor_relevansi' => 4,
                'skor_narasumber' => 5,
                'skor_metode' => 4,
                'skor_fasilitas' => 4,
                'skor_manfaat' => 5,
                'feedback' => 'Secara keseluruhan pelatihan sangat bermanfaat. Materi relevan dan penyampaian narasumber jelas. Saran: akan lebih baik jika ada sesi praktik langsung.',
                'file_bukti' => 'Form_Survey_Training_0126.pdf',
            ],
            [
                'nama_training' => 'Pengelolaan Dokumen dan Arsip Digital',
                'penyelenggara' => 'PT Solusi Arsip Indonesia',
                'lokasi' => 'Auditorium Kampus Utama',
                'tanggal_mulai' => '2026-01-20',
                'tanggal_selesai' => '2026-01-20',
                'deskripsi' => 'Tata kelola kearsipan digital dan kepatuhan regulasi dokumen perguruan tinggi.',
                'status' => 'Selesai',
                'is_survey_filled' => true,
                'skor_relevansi' => 5,
                'skor_narasumber' => 4,
                'skor_metode' => 4,
                'skor_fasilitas' => 5,
                'skor_manfaat' => 4,
                'feedback' => 'Materi kearsipan sangat aplikatif dan mempermudah pemberkasan akreditasi.',
                'file_bukti' => 'Sertifikat_Arsip_Digital.pdf',
            ],
            [
                'nama_training' => 'Microsoft Excel Advanced for Data Analytics',
                'penyelenggara' => 'CV Edukasi Prima',
                'lokasi' => 'Lab Komputer Gedung B',
                'tanggal_mulai' => '2026-02-05',
                'tanggal_selesai' => '2026-02-05',
                'deskripsi' => 'Penguasaan formula tingkat lanjut, pivot table, dan otomasi visualisasi data.',
                'status' => 'Selesai',
                'is_survey_filled' => true,
                'skor_relevansi' => 4,
                'skor_narasumber' => 4,
                'skor_metode' => 5,
                'skor_fasilitas' => 4,
                'skor_manfaat' => 4,
                'feedback' => 'Sangat membantu pemrosesan data pelaporan kepegawaian.',
                'file_bukti' => 'Sertifikat_Excel_Advanced.pdf',
            ],
            [
                'nama_training' => 'Strategi Pembelajaran Berbasis Outcome (OBE)',
                'penyelenggara' => 'Universitas Negeri Yogyakarta',
                'lokasi' => 'Hall Pertemuan Lantai 2',
                'tanggal_mulai' => '2026-02-17',
                'tanggal_selesai' => '2026-02-18',
                'deskripsi' => 'Penyusunan kurikulum dan asesmen berbasis outcome kurikulum mandiri.',
                'status' => 'Selesai',
                'is_survey_filled' => false,
            ],
            [
                'nama_training' => 'Public Speaking & Presentation Skill',
                'penyelenggara' => 'Coach Andi Pratama',
                'lokasi' => 'Ruang Teater Kampus',
                'tanggal_mulai' => '2026-03-03',
                'tanggal_selesai' => '2026-03-03',
                'deskripsi' => 'Teknik komunikasi efektif, gestur tubuh, dan persuasive slide pitching.',
                'status' => 'Selesai',
                'is_survey_filled' => false,
            ],
            [
                'nama_training' => 'Manajemen Risiko di Perguruan Tinggi',
                'penyelenggara' => 'Pusat Kajian Kebijakan Pendidikan',
                'lokasi' => 'Hotel Sahid Jaya Surakarta',
                'tanggal_mulai' => '2026-03-12',
                'tanggal_selesai' => '2026-03-12',
                'deskripsi' => 'Mitigasi risiko operasional, reputasi, dan kepatuhan perguruan tinggi.',
                'status' => 'Selesai',
                'is_survey_filled' => true,
                'skor_relevansi' => 5,
                'skor_narasumber' => 5,
                'skor_metode' => 4,
                'skor_fasilitas' => 5,
                'skor_manfaat' => 5,
                'feedback' => 'Wawasan baru dalam mitigasi risiko tata kelola universitas.',
                'file_bukti' => 'Sertifikat_Risk_Management.pdf',
            ],
            [
                'nama_training' => 'Pengembangan Kurikulum Merdeka',
                'penyelenggara' => 'PT Cendekia Mandiri',
                'lokasi' => 'Ruang Rapat Senat',
                'tanggal_mulai' => '2026-03-25',
                'tanggal_selesai' => '2026-03-25',
                'deskripsi' => 'Riset dan perancangan mata kuliah terintegrasi industri.',
                'status' => 'Selesai',
                'is_survey_filled' => false,
            ],
        ];

        foreach ($trainingsData as $tData) {
            $training = Training::updateOrCreate(
                ['nama_training' => $tData['nama_training']],
                [
                    'penyelenggara' => $tData['penyelenggara'],
                    'lokasi' => $tData['lokasi'],
                    'tanggal_mulai' => $tData['tanggal_mulai'],
                    'tanggal_selesai' => $tData['tanggal_selesai'],
                    'deskripsi' => $tData['deskripsi'],
                    'status' => $tData['status'],
                    'created_by' => 'SDM TSU',
                ]
            );

            if ($karyawanId) {
                $peserta = TrainingPeserta::updateOrCreate(
                    [
                        'training_id' => $training->id,
                        'karyawan_id' => $karyawanId,
                    ],
                    [
                        'status_kehadiran' => 'Hadir',
                        'is_survey_filled' => $tData['is_survey_filled'],
                        'survey_filled_at' => $tData['is_survey_filled'] ? now() : null,
                        'sertifikat_file' => !empty($tData['file_bukti']) ? 'storage/sertifikat/' . $tData['file_bukti'] : null,
                    ]
                );

                if ($tData['is_survey_filled']) {
                    SurveyPelatihanResponse::updateOrCreate(
                        ['training_peserta_id' => $peserta->id],
                        [
                            'skor_relevansi' => $tData['skor_relevansi'] ?? 5,
                            'skor_narasumber' => $tData['skor_narasumber'] ?? 5,
                            'skor_metode' => $tData['skor_metode'] ?? 5,
                            'skor_fasilitas' => $tData['skor_fasilitas'] ?? 5,
                            'skor_manfaat' => $tData['skor_manfaat'] ?? 5,
                            'feedback_manfaat' => $tData['feedback'] ?? null,
                            'saran_masukan' => $tData['feedback'] ?? null,
                            'bukti_file' => $tData['file_bukti'] ?? null,
                            'is_draft' => false,
                        ]
                    );
                }
            }
        }

        // -------------------------------------------------------------
        // 2. SEED DATA AUDIT SPI (MOCKUP 12)
        // -------------------------------------------------------------
        $sdmUnit = MasterUnit::where('nama_unit', 'like', '%Sumber Daya%')->first();
        $baukUnit = MasterUnit::where('nama_unit', 'like', '%BAUK%')->first();
        $lppmUnit = MasterUnit::where('nama_unit', 'like', '%LPPM%')->first();
        $feUnit = MasterUnit::where('nama_unit', 'like', '%Ekonomi%')->orWhere('nama_unit', 'like', '%Manajemen%')->first();
        $spiUnit = MasterUnit::where('nama_unit', 'like', '%SPI%')->first();

        $spiItems = [
            [
                'kode_temuan' => 'SPI-2026-001',
                'unit_id' => $sdmUnit ? $sdmUnit->id : null,
                'unit_nama' => 'SDM',
                'tanggal_audit' => '2026-02-15',
                'area_audit' => 'Administrasi Kepegawaian',
                'temuan' => '5 dokumen perjanjian kerja belum diperbarui sesuai status kepegawaian.',
                'kategori' => 'Ketidakpatuhan Administrasi',
                'tingkat_risiko' => 'Sedang',
                'rekomendasi' => 'Melakukan pembaruan dokumen perjanjian kerja.',
                'pic' => 'SDM',
                'deadline' => '2026-03-31',
                'status' => 'Dalam Proses',
                'bukti_tindak_lanjut' => 'Bukti_Pembaruan_Dokumen.pdf',
                'catatan_tindak_lanjut' => 'Draft SK dan kontrak baru telah disiapkan dan menunggu penandatanganan rektor.',
                'riwayat_status' => [
                    ['tanggal' => '15 Feb 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '20 Feb 2026', 'status' => 'Dalam proses tindak lanjut'],
                ],
            ],
            [
                'kode_temuan' => 'SPI-2026-002',
                'unit_id' => $baukUnit ? $baukUnit->id : null,
                'unit_nama' => 'BAUK',
                'tanggal_audit' => '2026-01-10',
                'area_audit' => 'Pengelolaan Aset',
                'temuan' => 'Terdapat perbedaan data aset dengan kondisi fisik di lapangan.',
                'kategori' => 'Ketidaksesuaian Proses',
                'tingkat_risiko' => 'Tinggi',
                'rekomendasi' => 'Rekonsiliasi data aset dan verifikasi fisik berkala.',
                'pic' => 'BAUK',
                'deadline' => '2026-02-28',
                'status' => 'Terbuka',
                'bukti_tindak_lanjut' => null,
                'catatan_tindak_lanjut' => null,
                'riwayat_status' => [
                    ['tanggal' => '10 Jan 2026', 'status' => 'Temuan dibuat'],
                ],
            ],
            [
                'kode_temuan' => 'SPI-2026-003',
                'unit_id' => $lppmUnit ? $lppmUnit->id : null,
                'unit_nama' => 'LPPM',
                'tanggal_audit' => '2026-03-03',
                'area_audit' => 'Pengelolaan Penelitian',
                'temuan' => 'Laporan keuangan penelitian tidak disertai bukti pengeluaran lengkap.',
                'kategori' => 'Ketidaklengkapan Dokumen',
                'tingkat_risiko' => 'Sedang',
                'rekomendasi' => 'Melengkapi bukti pengeluaran dalam laporan pertanggungjawaban.',
                'pic' => 'LPPM',
                'deadline' => '2026-04-30',
                'status' => 'Selesai',
                'bukti_tindak_lanjut' => 'LPJ_Penelitian_Lengkap.pdf',
                'catatan_tindak_lanjut' => 'Seluruh nota dan kuitansi telah diverifikasi oleh tim verifikator BAUK.',
                'riwayat_status' => [
                    ['tanggal' => '03 Mar 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '15 Mar 2026', 'status' => 'Dalam proses verifikasi'],
                    ['tanggal' => '28 Mar 2026', 'status' => 'Selesai ditindaklanjuti'],
                ],
            ],
            [
                'kode_temuan' => 'SPI-2026-004',
                'unit_id' => $feUnit ? $feUnit->id : null,
                'unit_nama' => 'Fakultas Ekonomi',
                'tanggal_audit' => '2026-04-20',
                'area_audit' => 'Kinerja SDM',
                'temuan' => 'Keterlambatan pengisian formasi jabatan fungsional dosen.',
                'kategori' => 'Keterlambatan',
                'tingkat_risiko' => 'Sedang',
                'rekomendasi' => 'Reminder ke dosen terkait pengajuan jafung dan fasilitasi pengusulan.',
                'pic' => 'SDM',
                'deadline' => '2026-05-31',
                'status' => 'Overdue',
                'bukti_tindak_lanjut' => null,
                'catatan_tindak_lanjut' => 'Terkendala kelengkapan berkas PAK PAKSI dari beberapa dosen senior.',
                'riwayat_status' => [
                    ['tanggal' => '20 Apr 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '01 Jun 2026', 'status' => 'Melewati tenggat waktu (Overdue)'],
                ],
            ],
            [
                'kode_temuan' => 'SPI-2026-005',
                'unit_id' => null,
                'unit_nama' => 'TSU',
                'tanggal_audit' => '2026-05-18',
                'area_audit' => 'Pengadaan Barang & Jasa',
                'temuan' => 'Proses pengadaan tidak sesuai dokumen perencanaan tahunan.',
                'kategori' => 'Ketidaksesuaian Proses',
                'tingkat_risiko' => 'Tinggi',
                'rekomendasi' => 'Perbaikan prosedur dan pembinaan PIC pengadaan.',
                'pic' => 'TSU',
                'deadline' => '2026-06-30',
                'status' => 'Dalam Proses',
                'bukti_tindak_lanjut' => 'SOP_Revisi_Pengadaan.pdf',
                'catatan_tindak_lanjut' => 'Penyusunan matriks justifikasi darurat dan revisi SOP pengadaan.',
                'riwayat_status' => [
                    ['tanggal' => '18 Mei 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '25 Mei 2026', 'status' => 'Dalam proses tindak lanjut'],
                ],
            ],
        ];

        foreach ($spiItems as $spi) {
            AuditSpiTemuan::updateOrCreate(
                ['kode_temuan' => $spi['kode_temuan']],
                $spi
            );
        }

        // -------------------------------------------------------------
        // 3. SEED DATA AUDIT LPM (MOCKUP 12)
        // -------------------------------------------------------------
        $ftiUnit = MasterUnit::where('nama_unit', 'like', '%Teknik%')->orWhere('nama_unit', 'like', '%Informatika%')->first();

        $lpmItems = [
            [
                'kode_temuan' => 'LPM-2026-001',
                'unit_id' => $feUnit ? $feUnit->id : null,
                'unit_nama' => 'Fakultas Ekonomi',
                'tanggal_audit' => '2026-01-12',
                'standar_mutu' => 'Standar SDM',
                'aspek' => 'Kecukupan SDM',
                'indikator' => 'Rasio dosen/mahasiswa',
                'target_standar' => '1:30',
                'kondisi_aktual' => '1:40',
                'temuan' => 'Rasio belum sesuai standar (1:40, seharusnya 1:30).',
                'analisis_penyebab' => 'Keterbatasan rekrutmen dan pengangkatan dosen baru.',
                'rekomendasi' => 'Analisis kebutuhan dan pemenuhan SDM melalui percepatan rekrutmen.',
                'pic' => 'SDM',
                'deadline' => '2026-04-30',
                'status' => 'Dalam Proses',
                'bukti_tindak_lanjut' => 'Surat_Rencana_Rekrutmen.pdf',
                'catatan_tindak_lanjut' => 'Pengajuan formasi dosen akuntansi dan manajemen telah diserahkan ke WR 2.',
                'riwayat_status' => [
                    ['tanggal' => '12 Jan 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '25 Jan 2026', 'status' => 'Dalam proses tindak lanjut'],
                ],
            ],
            [
                'kode_temuan' => 'LPM-2026-002',
                'unit_id' => $ftiUnit ? $ftiUnit->id : null,
                'unit_nama' => 'FTI',
                'tanggal_audit' => '2026-02-18',
                'standar_mutu' => 'Standar Pembelajaran',
                'aspek' => 'Kualitas Pembelajaran',
                'indikator' => 'EDOM (Evaluasi Dosen Oleh Mahasiswa)',
                'target_standar' => '>= 80%',
                'kondisi_aktual' => '72%',
                'temuan' => 'Tingkat kepuasan mahasiswa < 80% pada metode asesmen praktikum.',
                'analisis_penyebab' => 'Modul praktikum laboratorium belum diperbarui dan kendala spesifikasi PC lab.',
                'rekomendasi' => 'Perbaikan metode pembelajaran dan workshop instructional design.',
                'pic' => 'SDM',
                'deadline' => '2026-05-31',
                'status' => 'Terbuka',
                'bukti_tindak_lanjut' => null,
                'catatan_tindak_lanjut' => null,
                'riwayat_status' => [
                    ['tanggal' => '18 Feb 2026', 'status' => 'Temuan dibuat'],
                ],
            ],
            [
                'kode_temuan' => 'LPM-2026-003',
                'unit_id' => $lppmUnit ? $lppmUnit->id : null,
                'unit_nama' => 'LPPM',
                'tanggal_audit' => '2026-03-05',
                'standar_mutu' => 'Standar Penelitian',
                'aspek' => 'Publikasi',
                'indikator' => 'Jumlah publikasi dosen',
                'target_standar' => '100% Target SINTA',
                'kondisi_aktual' => '60% dari target',
                'temuan' => 'Realisasi publikasi ilmiah dosen baru mencapai 60% dari target tahunan.',
                'analisis_penyebab' => 'Minimnya insentif publikasi Q1/Q2 dan beban administrasi pengajaran yang padat.',
                'rekomendasi' => 'Peningkatan pendampingan klinik artikel dan insentif publikasi bereputasi.',
                'pic' => 'SDM',
                'deadline' => '2026-06-30',
                'status' => 'Dalam Proses',
                'bukti_tindak_lanjut' => 'Proposal_Klinik_Artikel_LPPM.pdf',
                'catatan_tindak_lanjut' => 'Jadwal klinik manuskrip internasional telah disepakati untuk bulan Mei 2026.',
                'riwayat_status' => [
                    ['tanggal' => '05 Mar 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '20 Mar 2026', 'status' => 'Dalam proses tindak lanjut'],
                ],
            ],
            [
                'kode_temuan' => 'LPM-2026-004',
                'unit_id' => $baukUnit ? $baukUnit->id : null,
                'unit_nama' => 'BAUK',
                'tanggal_audit' => '2026-04-20',
                'standar_mutu' => 'Standar Tata Kelola',
                'aspek' => 'Sistem Informasi',
                'indikator' => 'Pengelolaan data SDM',
                'target_standar' => 'Sinkron 100%',
                'kondisi_aktual' => 'Data selisih',
                'temuan' => 'Data tidak sinkron antar sistem kepegawaian dan penggajian.',
                'analisis_penyebab' => 'Migrasi database berkala belum menerapkan webhook integrasi otomatis.',
                'rekomendasi' => 'Integrasi data dan validasi berkala melalui API terpusat.',
                'pic' => 'SDM',
                'deadline' => '2026-07-31',
                'status' => 'Selesai',
                'bukti_tindak_lanjut' => 'Laporan_Integrasi_API_SDM.pdf',
                'catatan_tindak_lanjut' => 'Sinkronisasi otomatis berhasil dijalankan tanpa galat.',
                'riwayat_status' => [
                    ['tanggal' => '20 Apr 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '28 Apr 2026', 'status' => 'Selesai ditindaklanjuti'],
                ],
            ],
            [
                'kode_temuan' => 'LPM-2026-005',
                'unit_id' => $ftiUnit ? $ftiUnit->id : null,
                'unit_nama' => 'Fakultas Teknik',
                'tanggal_audit' => '2026-05-10',
                'standar_mutu' => 'Standar Kemahasiswaan',
                'aspek' => 'Layanan Mahasiswa',
                'indikator' => 'Waktu respon layanan surat',
                'target_standar' => '<= 24 Jam',
                'kondisi_aktual' => '> 24 Jam',
                'temuan' => 'Rata-rata respon layanan surat keterangan mahasiswa > 24 jam.',
                'analisis_penyebab' => 'Validasi manual tanda tangan basah pimpinan fakultas.',
                'rekomendasi' => 'Peningkatan kapasitas layanan, e-signature, dan pembaruan SOP layanan.',
                'pic' => 'SDM',
                'deadline' => '2026-08-31',
                'status' => 'Overdue',
                'bukti_tindak_lanjut' => null,
                'catatan_tindak_lanjut' => 'Menunggu pengadaan sertifikat tanda tangan elektronik TTE BSrE.',
                'riwayat_status' => [
                    ['tanggal' => '10 Mei 2026', 'status' => 'Temuan dibuat'],
                    ['tanggal' => '01 Sep 2026', 'status' => 'Melewati tenggat waktu (Overdue)'],
                ],
            ],
        ];

        foreach ($lpmItems as $lpm) {
            AuditLpmTemuan::updateOrCreate(
                ['kode_temuan' => $lpm['kode_temuan']],
                $lpm
            );
        }

        // -------------------------------------------------------------
        // 4. SEED DATA KPI SISFO STYLE (MOCKUP 8)
        // -------------------------------------------------------------
        $periode = KpiPeriode::firstOrCreate(
            ['tahun' => 2026],
            [
                'nama_periode' => 'KPI & Cascading Scorecard Tahun 2026',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'is_active' => true,
            ]
        );

        $bauk = MasterUnit::where('nama_unit', 'like', '%BAUK%')->first() ?? $firstKaryawan->unit ?? MasterUnit::first();
        $sdm = MasterUnit::where('nama_unit', 'like', '%Sumber Daya%')->first() ?? $bauk;
        $lppm = MasterUnit::where('nama_unit', 'like', '%LPPM%')->first() ?? $bauk;

        $kpiSisfoItems = [
            [
                'unit' => $bauk,
                'unit_terkait' => 'BAUK',
                'jenis_cascading' => 'Unit',
                'definisi' => 'Persentase penyelesaian dokumen kepegawaian tepat waktu',
                'formula' => '(Jumlah dokumen tepat waktu / Total dokumen) x 100%',
                'bukti' => 'Laporan / PDF',
                'target' => 100,
                'bobot' => 10,
                'realisasi' => 95,
                'capaian' => 95.0,
                'skor' => 9.5,
            ],
            [
                'unit' => $bauk,
                'unit_terkait' => 'BAUK',
                'jenis_cascading' => 'Unit',
                'definisi' => 'Tingkat kepuasan layanan kepegawaian',
                'formula' => '(Jumlah responden puas / Total responden) x 100%',
                'bukti' => 'Hasil survei / PDF',
                'target' => 90,
                'bobot' => 10,
                'realisasi' => 92,
                'capaian' => 102.2,
                'skor' => 10.0,
            ],
            [
                'unit' => $sdm,
                'unit_terkait' => 'SDM',
                'jenis_cascading' => 'Program',
                'definisi' => 'Tingkat partisipasi pelatihan pegawai',
                'formula' => '(Jumlah peserta pelatihan / Total pegawai) x 100%',
                'bukti' => 'Daftar hadir / PDF',
                'target' => 80,
                'bobot' => 10,
                'realisasi' => 78,
                'capaian' => 97.5,
                'skor' => 9.8,
            ],
            [
                'unit' => $sdm,
                'unit_terkait' => 'SDM',
                'jenis_cascading' => 'Program',
                'definisi' => 'Tingkat retensi pegawai',
                'formula' => '(Jumlah pegawai bertahan / Total pegawai awal) x 100%',
                'bukti' => 'Data SDM / PDF',
                'target' => 95,
                'bobot' => 10,
                'realisasi' => 96,
                'capaian' => 101.1,
                'skor' => 10.0,
            ],
            [
                'unit' => $lppm,
                'unit_terkait' => 'LPPM',
                'jenis_cascading' => 'Unit',
                'definisi' => 'Jumlah publikasi terindeks SINTA',
                'formula' => 'Jumlah publikasi terindeks SINTA',
                'bukti' => 'SINTA / PDF',
                'target' => 20,
                'bobot' => 10,
                'realisasi' => 18,
                'capaian' => 90.0,
                'skor' => 9.0,
            ],
        ];

        // Ensure we have a dummy/matching perspective & master indicator
        $perspektif = KpiMasterPerspektif::firstOrCreate(
            ['kode' => 'INT'],
            ['nama_perspektif' => 'Internal Process Perspective', 'bobot_perspektif' => 25]
        );

        foreach ($kpiSisfoItems as $idx => $kItem) {
            $mIndikator = KpiMasterIndikator::firstOrCreate(
                ['nama_indikator' => $kItem['definisi']],
                [
                    'kode_indikator' => 'KPI-SISFO-00' . ($idx + 1),
                    'perspektif_id' => $perspektif->id,
                    'satuan' => str_contains((string)$kItem['target'], '%') || $kItem['target'] > 50 ? '%' : 'Dokumen',
                    'tipe_target' => 'kuantitatif',
                    'level' => 'induk',
                    'is_active' => true,
                ]
            );

            KpiUnitIndikator::updateOrCreate(
                [
                    'periode_id' => $periode->id,
                    'master_indikator_id' => $mIndikator->id,
                ],
                [
                    'master_unit_id' => $kItem['unit']->id,
                    'jenis_cascading' => $kItem['jenis_cascading'],
                    'formula_pengukuran' => $kItem['formula'],
                    'file_bukti' => $kItem['bukti'],
                    'target_angka' => $kItem['target'],
                    'target_label' => (string)$kItem['target'],
                    'bobot' => $kItem['bobot'],
                    'realisasi_angka' => $kItem['realisasi'],
                    'realisasi_label' => (string)$kItem['realisasi'],
                    'capaian_persen' => $kItem['capaian'],
                    'skor' => $kItem['skor'],
                    'unit_terkait' => $kItem['unit_terkait'],
                    'status_monev' => 'Sudah Mengisi',
                    'urutan' => $idx + 1,
                ]
            );
        }
    }
}
