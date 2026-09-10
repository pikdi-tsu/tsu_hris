<?php

namespace App\Console\Commands;

use App\Models\DataDosenTendik;
use App\Models\MasterBidangKeilmuan;
use App\Models\MasterPeriodePengembangan;
use App\Models\MasterSertifikasi;
use App\Models\MasterUnit;
use App\Models\PengembanganSdmPeserta;
use App\Models\PengembanganSdmSertifikasi;
use App\Models\PengembanganSdmTimeline;
use App\Services\PengembanganSdmService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPengembanganSdmExcel extends Command
{
    protected $signature = 'pengembangan:import-excel';
    protected $description = 'Import initial data for Pengembangan SDM (Dosen & Tendik) from Excel files';

    public function handle()
    {
        $this->info('=== MEMULAI IMPORT DATA PENGEMBANGAN SDM ===');

        DB::beginTransaction();
        try {
            $periode = PengembanganSdmService::getActivePeriode();
            $this->info("Periode Aktif: {$periode->nama_periode}");

            $units = MasterUnit::all();

            $findUnit = function($name) use ($units) {
                $clean = strtolower(trim($name));
                foreach ($units as $u) {
                    $uClean = strtolower(trim($u->nama_unit));
                    if ($uClean === $clean) return $u;
                }
                if (str_contains($clean, 'informatika') || str_contains($clean, 's1 - informatika')) {
                    return $units->firstWhere('nama_unit', 'S1 - Informatika (Inf)');
                }
                if (str_contains($clean, 's1-si') || (str_contains($clean, 'sistem informasi') && str_contains($clean, 's1'))) {
                    return $units->firstWhere('nama_unit', 'S1 - Sistem Informasi (SI)');
                }
                if (str_contains($clean, 'rekom') || str_contains($clean, 'rekayasa komputer')) {
                    return $units->firstWhere('nama_unit', 'S1 - Rekayasa Komputer (Rekom)');
                }
                if (str_contains($clean, 'manajemen')) {
                    return $units->firstWhere('nama_unit', 'S1 - Manajemen (Mnj)');
                }
                if (str_contains($clean, 'pgsd') || str_contains($clean, 'pendidikan guru')) {
                    return $units->firstWhere('nama_unit', 'S1 - Pendidikan Guru Sekolah Dasar (PGSD)');
                }
                if (str_contains($clean, 'd3-ti') || (str_contains($clean, 'd3') && str_contains($clean, 'teknologi'))) {
                    return $units->firstWhere('nama_unit', 'D3 - Teknologi Infromasi (TI)');
                }
                if (str_contains($clean, 'psikologi')) {
                    return $units->firstWhere('nama_unit', 'S1 - Psikologi (Psi)');
                }
                if (str_contains($clean, 'dkv') || str_contains($clean, 'desain komunikasi')) {
                    return $units->firstWhere('nama_unit', 'D3 - Desain Komunikasi Visual (DKV)');
                }
                if (str_contains($clean, 'dpt') || str_contains($clean, 'produk tekstil')) {
                    return $units->firstWhere('nama_unit', 'D3 - Desain Produk Tekstil (DPT)');
                }
                if (str_contains($clean, 'd3-si') || (str_contains($clean, 'd3') && str_contains($clean, 'sistem informasi'))) {
                    return $units->firstWhere('nama_unit', 'D3 - Sistem Informasi (SI)');
                }
                if (str_contains($clean, 'baak')) return $units->firstWhere('nama_unit', 'Biro Administrasi Akademik dan Kemahasiswaan (BAAK)');
                if (str_contains($clean, 'bauk')) return $units->firstWhere('nama_unit', 'Biro Administrasi Umum dan Keuangan (BAUK)');
                if (str_contains($clean, 'bakppu')) return $units->firstWhere('nama_unit', 'Biro Administrasi Kerjasama Perencanaan dan Pengembangan Usaha (BAKPPU)');
                if (str_contains($clean, 'sekretariat')) return $units->firstWhere('nama_unit', 'Sekretariat');
                if ($clean === 'ft' || str_contains($clean, 'fakultas teknik')) return $units->firstWhere('nama_unit', 'Fakultas Teknik');
                if ($clean === 'fsh' || str_contains($clean, 'sains & humaniora') || str_contains($clean, 'sains dan humaniora')) return $units->firstWhere('nama_unit', 'Fakultas Sains & Humaniora');
                if ($clean === 'sv' || str_contains($clean, 'sekolah vokasi')) return $units->firstWhere('nama_unit', 'Sekolah Vokasi');
                if ($clean === 'lpm' || str_contains($clean, 'penjaminan mutu')) return $units->firstWhere('nama_unit', 'Lembaga Penjaminan Mutu (LPM)');
                if ($clean === 'lppm' || str_contains($clean, 'penelitian')) return $units->firstWhere('nama_unit', 'Lembaga Penelitian dan Pengabdian kepada Masyarakat (LPPM)');

                return null;
            };

            // ==========================================
            // 1. IMPORT DOSEN
            // ==========================================
            $fileDosen = 'D:/Data Mentah Project/HRIS/Pengembangan DOSEN TSU 2026 FIX.xlsx';
            if (file_exists($fileDosen)) {
                $this->info("\n--- Mengimpor Data DOSEN dari {$fileDosen} ---");
                $reader = IOFactory::createReaderForFile($fileDosen);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($fileDosen);

                foreach ($spreadsheet->getSheetNames() as $sheetName) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    $unit = $findUnit($sheetName);
                    if (!$unit) {
                        $this->warn("Sheet Dosen [{$sheetName}] tidak menemukan unit di master_units!");
                        continue;
                    }

                    $this->info("Memproses Sheet Dosen: {$sheetName} -> Unit: {$unit->nama_unit}");

                    // Cari baris header
                    $headerRow = 38;
                    for ($r = 1; $r <= 50; $r++) {
                        $vA = strtoupper(trim((string)$sheet->getCell('A' . $r)->getValue()));
                        $vB = strtoupper(trim((string)$sheet->getCell('B' . $r)->getValue()));
                        if ($vA === 'NO' && ($vB === 'NAMA' || str_contains($vB, 'NAMA'))) {
                            $headerRow = $r;
                            break;
                        }
                    }

                    // Ambil nama sertifikasi dari header baris
                    $sertifikasiCols = [];
                    for ($col = 'R'; $col <= 'Y'; $col++) {
                        $sName = trim((string)$sheet->getCell($col . ($headerRow + 1))->getValue());
                        if (empty($sName)) {
                            $sName = trim((string)$sheet->getCell($col . $headerRow)->getValue());
                        }
                        if (!empty($sName) && !is_numeric($sName) && !in_array(strtoupper($sName), ['DN/ LN', 'S2', 'S3', 'NO', 'NAMA'])) {
                            $masterSert = MasterSertifikasi::firstOrCreate(
                                ['nama_sertifikasi' => $sName, 'kategori_peserta' => 'dosen'],
                                ['unit_id' => $unit->id, 'is_active' => true]
                            );
                            $sertifikasiCols[$col] = $masterSert->id;
                        }
                    }

                    $highestRow = $sheet->getHighestRow();
                    $orderNum = 1;
                    for ($r = $headerRow + 1; $r <= min($headerRow + 40, $highestRow); $r++) {
                        $rawA = $sheet->getCell('A' . $r)->getCalculatedValue();
                        $rawB = $sheet->getCell('B' . $r)->getCalculatedValue();
                        $colA = trim((string)$rawA);
                        $colB = trim((string)$rawB);

                        if (!empty($colB) && strtoupper($colB) !== 'NAMA' && !str_contains(strtoupper($colB), 'JUMLAH') && !str_contains(strtoupper($colB), 'KOMPOSISI') && (is_numeric($colA) || is_numeric($rawA) || !empty($colA))) {
                            $nama = $colB;
                            $nidn = trim((string)$sheet->getCell('C' . $r)->getCalculatedValue());
                            $pendidikan = trim((string)$sheet->getCell('E' . $r)->getCalculatedValue()) ?: 'S2';
                            $gelar = trim((string)$sheet->getCell('F' . $r)->getCalculatedValue());
                            
                            $lokasiDN = trim((string)$sheet->getCell('O' . $r)->getCalculatedValue());
                            $lokasiLN = trim((string)$sheet->getCell('P' . $r)->getCalculatedValue());
                            $lokasi = 'DN';
                            if (strtoupper($lokasiLN) === 'LN' || str_contains(strtoupper($lokasiLN), 'LN')) {
                                $lokasi = 'LN';
                            }

                            // Match dengan DataDosenTendik
                            $karyawan = null;
                            if (!empty($nidn)) {
                                $karyawan = DataDosenTendik::where('nidn', $nidn)->orWhere('nuptk', $nidn)->orWhere('nik', $nidn)->first();
                            }
                            if (!$karyawan) {
                                $karyawan = DataDosenTendik::where('nama', 'LIKE', "%{$nama}%")->first();
                            }

                            $isPlaceholder = str_contains(strtoupper($nama), 'DOSEN BARU');
                            $tglPensiun = null;
                            if ($karyawan && $karyawan->tanggal_lahir) {
                                $tglPensiun = Carbon::parse($karyawan->tanggal_lahir)->addYears(65)->format('Y-m-d');
                            }

                            $peserta = PengembanganSdmPeserta::updateOrCreate(
                                [
                                    'master_periode_id' => $periode->id,
                                    'tipe_pegawai' => 'dosen',
                                    'unit_id' => $unit->id,
                                    'order_no' => is_numeric($colA) ? (int)$colA : $orderNum,
                                ],
                                [
                                    'data_dosen_tendik_id' => $karyawan ? $karyawan->id : null,
                                    'nama_placeholder' => $isPlaceholder ? $nama : null,
                                    'pendidikan_awal' => $pendidikan,
                                    'gelar' => $gelar,
                                    'lokasi_studi' => $lokasi,
                                    'tanggal_pensiun' => $tglPensiun,
                                ]
                            );
                            $orderNum++;

                            // Simpan Sertifikasi
                            foreach ($sertifikasiCols as $col => $sertId) {
                                $valSert = trim((string)$sheet->getCell($col . $r)->getCalculatedValue());
                                if ($valSert === '1' || $valSert === 1 || strtolower($valSert) === 'v') {
                                    PengembanganSdmSertifikasi::updateOrCreate(
                                        [
                                            'peserta_id' => $peserta->id,
                                            'sertifikasi_id' => $sertId,
                                        ],
                                        [
                                            'status_kepemilikan' => true,
                                        ]
                                    );
                                }
                            }

                            // Simpan Timeline 2026 s/d 2030
                            $timelineMap = [
                                2026 => ['status' => 'Z', 'bidang' => 'AA'],
                                2027 => ['status' => 'AB', 'bidang' => 'AC'],
                                2028 => ['status' => 'AD', 'bidang' => 'AE'],
                                2029 => ['status' => 'AF', 'bidang' => 'AG'],
                                2030 => ['status' => 'AH', 'bidang' => 'AI'],
                            ];

                            foreach ($timelineMap as $thn => $colInfo) {
                                $stVal = trim((string)$sheet->getCell($colInfo['status'] . $r)->getCalculatedValue());
                                $bdVal = trim((string)$sheet->getCell($colInfo['bidang'] . $r)->getCalculatedValue());

                                if (empty($stVal)) {
                                    $stVal = $pendidikan ?: 'S2';
                                }

                                $stUpper = strtoupper($stVal);
                                $actStatus = (str_contains($stUpper, '+') || str_contains($stUpper, 'SS')) ? 'SS' : 'TSS';

                                if (!empty($bdVal) && strlen($bdVal) <= 15) {
                                    MasterBidangKeilmuan::firstOrCreate(
                                        ['kode_bidang' => strtoupper($bdVal), 'kategori' => 'dosen'],
                                        ['nama_bidang' => strtoupper($bdVal), 'unit_id' => $unit->id, 'is_active' => true]
                                    );
                                }

                                PengembanganSdmTimeline::updateOrCreate(
                                    [
                                        'peserta_id' => $peserta->id,
                                        'tahun' => $thn,
                                    ],
                                    [
                                        'status_studi' => $stVal,
                                        'status_aktif_studi' => $actStatus,
                                        'bidang_kode' => $bdVal ?: null,
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            // ==========================================
            // 2. IMPORT TENDIK
            // ==========================================
            $fileTendik = 'D:/Data Mentah Project/HRIS/Pengembangan TENDIK TSU 2026 FIX (1).xlsx';
            if (file_exists($fileTendik)) {
                $this->info("\n--- Mengimpor Data TENDIK dari {$fileTendik} ---");
                $readerTendik = IOFactory::createReaderForFile($fileTendik);
                $spreadsheetTendik = $readerTendik->load($fileTendik);

                foreach ($spreadsheetTendik->getSheetNames() as $sheetName) {
                    if ($sheetName === 'UNIVERSITAS') continue;

                    $sheet = $spreadsheetTendik->getSheetByName($sheetName);
                    $unit = $findUnit($sheetName);
                    if (!$unit) {
                        $this->warn("Sheet Tendik [{$sheetName}] tidak menemukan unit di master_units!");
                        continue;
                    }

                    $this->info("Memproses Sheet Tendik: {$sheetName} -> Unit: {$unit->nama_unit}");

                    $headerRow = 7;
                    for ($r = 1; $r <= 15; $r++) {
                        $vA = strtoupper(trim((string)$sheet->getCell('A' . $r)->getCalculatedValue()));
                        $vB = strtoupper(trim((string)$sheet->getCell('B' . $r)->getCalculatedValue()));
                        if ($vA === 'NO' && ($vB === 'NAMA' || str_contains($vB, 'NAMA'))) {
                            $headerRow = $r;
                            break;
                        }
                    }

                    // Ambil Sertifikasi
                    $sertifikasiCols = [];
                    for ($col = 'P'; $col <= 'V'; $col++) {
                        $sName = trim((string)$sheet->getCell($col . ($headerRow + 1))->getCalculatedValue());
                        if (empty($sName)) {
                            $sName = trim((string)$sheet->getCell($col . $headerRow)->getCalculatedValue());
                        }
                        if (!empty($sName) && !is_numeric($sName) && !in_array(strtoupper($sName), ['NO', 'NAMA', 'SERTIFIKASI KOMPETENSI'])) {
                            $masterSert = MasterSertifikasi::firstOrCreate(
                                ['nama_sertifikasi' => $sName, 'kategori_peserta' => 'tendik'],
                                ['unit_id' => $unit->id, 'is_active' => true]
                            );
                            $sertifikasiCols[$col] = $masterSert->id;
                        }
                    }

                    $highestRow = $sheet->getHighestRow();
                    $orderNumTendik = 1;
                    for ($r = $headerRow + 2; $r <= min($headerRow + 50, $highestRow); $r++) {
                        $rawA = $sheet->getCell('A' . $r)->getCalculatedValue();
                        $rawB = $sheet->getCell('B' . $r)->getCalculatedValue();
                        $colA = trim((string)$rawA);
                        $colB = trim((string)$rawB);

                        if (!empty($colB) && strtoupper($colB) !== 'NAMA' && !str_contains(strtoupper($colB), 'JUMLAH') && (is_numeric($colA) || is_numeric($rawA) || !empty($colA))) {
                            $nama = $colB;
                            $gelar = trim((string)$sheet->getCell('C' . $r)->getCalculatedValue());
                            $pendidikan = trim((string)$sheet->getCell('D' . $r)->getCalculatedValue()) ?: 'S1';
                            $nik = trim((string)$sheet->getCell('E' . $r)->getCalculatedValue());

                            $karyawan = null;
                            if (!empty($nik)) {
                                $karyawan = DataDosenTendik::where('nik', $nik)->first();
                            }
                            if (!$karyawan) {
                                $karyawan = DataDosenTendik::where('nama', 'LIKE', "%{$nama}%")->first();
                            }

                            $tglPensiun = null;
                            if ($karyawan && $karyawan->tanggal_lahir) {
                                $tglPensiun = Carbon::parse($karyawan->tanggal_lahir)->addYears(58)->format('Y-m-d');
                            }

                            $peserta = PengembanganSdmPeserta::updateOrCreate(
                                [
                                    'master_periode_id' => $periode->id,
                                    'tipe_pegawai' => 'tendik',
                                    'unit_id' => $unit->id,
                                    'order_no' => is_numeric($colA) ? (int)$colA : $orderNumTendik,
                                ],
                                [
                                    'data_dosen_tendik_id' => $karyawan ? $karyawan->id : null,
                                    'nama_placeholder' => $karyawan ? null : $nama,
                                    'pendidikan_awal' => $pendidikan,
                                    'gelar' => $gelar,
                                    'tanggal_pensiun' => $tglPensiun,
                                ]
                            );
                            $orderNumTendik++;

                            // Simpan Sertifikasi Tendik
                            foreach ($sertifikasiCols as $col => $sertId) {
                                $valSert = trim((string)$sheet->getCell($col . $r)->getCalculatedValue());
                                if ($valSert === '1' || $valSert === 1 || strtolower($valSert) === 'v') {
                                    PengembanganSdmSertifikasi::updateOrCreate(
                                        [
                                            'peserta_id' => $peserta->id,
                                            'sertifikasi_id' => $sertId,
                                        ],
                                        [
                                            'status_kepemilikan' => true,
                                        ]
                                    );
                                }
                            }

                            // Timeline Tendik (Col Y: 2026, AB: 2027, AE: 2028, AH: 2029, AK: 2030)
                            $timelineMap = [
                                2026 => ['status' => 'Y', 'bidang' => 'Z', 'act' => 'AA'],
                                2027 => ['status' => 'AB', 'bidang' => 'AC', 'act' => 'AD'],
                                2028 => ['status' => 'AE', 'bidang' => 'AF', 'act' => 'AG'],
                                2029 => ['status' => 'AH', 'bidang' => 'AI', 'act' => 'AJ'],
                                2030 => ['status' => 'AK', 'bidang' => 'AL', 'act' => 'AM'],
                            ];

                            foreach ($timelineMap as $thn => $colInfo) {
                                $stVal = trim((string)$sheet->getCell($colInfo['status'] . $r)->getCalculatedValue());
                                $bdVal = trim((string)$sheet->getCell($colInfo['bidang'] . $r)->getCalculatedValue());
                                $actVal = strtoupper(trim((string)$sheet->getCell($colInfo['act'] . $r)->getCalculatedValue()));

                                if (empty($stVal)) $stVal = $pendidikan ?: 'S1';
                                if (empty($actVal)) {
                                    $actVal = str_contains(strtoupper($stVal), '+') ? 'SS' : 'TSS';
                                }

                                if (!empty($bdVal) && strlen($bdVal) <= 15) {
                                    MasterBidangKeilmuan::firstOrCreate(
                                        ['kode_bidang' => strtoupper($bdVal), 'kategori' => 'tendik'],
                                        ['nama_bidang' => strtoupper($bdVal), 'unit_id' => $unit->id, 'is_active' => true]
                                    );
                                }

                                PengembanganSdmTimeline::updateOrCreate(
                                    [
                                        'peserta_id' => $peserta->id,
                                        'tahun' => $thn,
                                    ],
                                    [
                                        'status_studi' => $stVal,
                                        'status_aktif_studi' => in_array($actVal, ['SS', 'TSS']) ? $actVal : 'TSS',
                                        'bidang_kode' => $bdVal ?: null,
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            DB::commit();
            $this->info("\n=== IMPORT SUKSES SEMPURNA ===");
            $this->info("Total Peserta Dosen : " . PengembanganSdmPeserta::where('tipe_pegawai', 'dosen')->count());
            $this->info("Total Peserta Tendik: " . PengembanganSdmPeserta::where('tipe_pegawai', 'tendik')->count());
            $this->info("Total Timeline Rows : " . PengembanganSdmTimeline::count());
            $this->info("Total Sertifikasi   : " . MasterSertifikasi::count());
            $this->info("Total Bidang Ilmu   : " . MasterBidangKeilmuan::count());

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal melakukan import: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
