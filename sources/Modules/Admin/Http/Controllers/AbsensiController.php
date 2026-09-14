<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use App\Services\TsuErrorHandlerService;
use App\Services\PresensiCalculationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xml;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

use App\Models\DataDosenTendik;
use App\Models\DataAbsensi;
use App\Models\MasterShift;

class AbsensiController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:absensi');
        $this->middleware('permission:admin:absensi:create')->only(['previewexcel', 'simpanexcel']);
        $this->middleware('permission:admin:absensi:view')->only(['downloadslip']);
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $bulan = $this->getBulan();
        $currentM = (int) date('n');
        $currentY = (int) date('Y');

        $stats = [
            'total_employees' => DataDosenTendik::where('is_active', true)->count(),
            'total_logs' => DataAbsensi::count(),
            'current_month_logs' => DataAbsensi::where('periode_bulan', $currentM)->where('periode_tahun', $currentY)->count(),
            'total_shifts' => MasterShift::count(),
        ];

        return view('admin::absensi.index', [
            'title' => 'Upload Raw Data Presensi',
            'bulan' => $bulan,
            'stats' => $stats,
        ]);
    }

    /**
     * Helper untuk membaca berkas spreadsheet secara fleksibel
     * dengan fallback multi-reader (Xlsx, Xls, BIFF Binary Stream, Html, Csv, Xml, DOM, TSV)
     */
    private function parseSpreadsheetRows($uploadedFile)
    {
        $realPath = $uploadedFile->getRealPath();
        $origExt = $uploadedFile->getClientOriginalExtension() ?: 'xls';

        // 0. Siapkan copy sementara dengan ekstensi aslinya (beberapa reader PhpOffice mengecek ekstensi)
        $tempCopy = null;
        $targetPath = $realPath;
        if (!empty($origExt)) {
            $tempCopy = tempnam(sys_get_temp_dir(), 'hris_att_') . '.' . ltrim($origExt, '.');
            @copy($realPath, $tempCopy);
            $targetPath = $tempCopy;
        }

        $spreadsheet = null;

        // 1. Coba deteksi otomatis via IOFactory::load
        try {
            $spreadsheet = IOFactory::load($targetPath);
        } catch (\Throwable $e) {}

        // 2. Coba via Reader spesifik: Xlsx
        if (!$spreadsheet) {
            try {
                $reader = new Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($targetPath);
            } catch (\Throwable $e) {}
        }

        // 3. Coba via Reader spesifik: Xls (OLE2 Compound Document)
        if (!$spreadsheet) {
            try {
                $reader = new Xls();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($targetPath);
            } catch (\Throwable $e) {}
        }

        // 4. Coba Parser Native BIFF Binary Stream (Ekspor mesin presensi yang menghasilkan raw BIFF2/3/4/5/8 tanpa OLE wrapper)
        $biffRows = $this->parseRawBiffStream($realPath);
        if (!empty($biffRows)) {
            if ($tempCopy && file_exists($tempCopy)) {
                @unlink($tempCopy);
            }
            return $biffRows;
        }

        // 5. Coba via Reader: Html (banyak mesin absensi mengekspor format HTML table dengan ekstensi .xls)
        if (!$spreadsheet) {
            try {
                $reader = new Html();
                $spreadsheet = $reader->load($targetPath);
            } catch (\Throwable $e) {}
        }

        // 6. Coba via Reader: Csv dengan ragam delimiter umum
        if (!$spreadsheet) {
            $delimiters = ["\t", ";", ",", "|"];
            foreach ($delimiters as $delim) {
                try {
                    $reader = new Csv();
                    $reader->setDelimiter($delim);
                    $ss = $reader->load($targetPath);
                    $sample = $ss->getActiveSheet()->toArray(null, true, true, false);
                    if (!empty($sample) && count($sample[0] ?? []) > 1) {
                        $spreadsheet = $ss;
                        break;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 7. Coba via Reader: Xml (Microsoft Office 2003 XML format)
        if (!$spreadsheet) {
            try {
                $reader = new Xml();
                $spreadsheet = $reader->load($targetPath);
            } catch (\Throwable $e) {}
        }

        if ($tempCopy && file_exists($tempCopy)) {
            @unlink($tempCopy);
        }

        if ($spreadsheet) {
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, false);
            if (!empty($rows)) {
                return $rows;
            }
        }

        // 8. Fallback: Direct Content Inspection & Parsing (HTML Table & TSV/CSV)
        $rawContent = @file_get_contents($realPath);
        if ($rawContent !== false && strlen($rawContent) > 0) {
            // Deteksi & normalisasi encoding (UTF-16LE biometric, Windows-1252, dsb)
            $encoding = mb_detect_encoding($rawContent, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'Windows-1252', 'ISO-8859-1'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $rawContent = mb_convert_encoding($rawContent, 'UTF-8', $encoding);
            }

            // A. HTML Table parsing menggunakan DOMDocument
            if (stripos($rawContent, '<table') !== false || stripos($rawContent, '<tr') !== false) {
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $dom->loadHTML('<?xml encoding="UTF-8">' . $rawContent);
                libxml_clear_errors();

                $extractedRows = [];
                $trs = $dom->getElementsByTagName('tr');
                foreach ($trs as $tr) {
                    $row = [];
                    $cells = $tr->childNodes;
                    foreach ($cells as $cell) {
                        if (in_array(strtolower($cell->nodeName), ['td', 'th'])) {
                            $row[] = html_entity_decode(trim($cell->textContent), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }
                    }
                    if (!empty($row)) {
                        $extractedRows[] = $row;
                    }
                }
                if (!empty($extractedRows)) {
                    return $extractedRows;
                }
            }

            // B. Delimited Text (CSV/TSV) parsing
            $lines = preg_split('/\r\n|\r|\n/', trim($rawContent));
            if (!empty($lines)) {
                $bestRows = [];
                $delimiters = ["\t", ";", ",", "|"];
                foreach ($delimiters as $delim) {
                    $parsed = [];
                    foreach ($lines as $line) {
                        if (trim($line) === '') continue;
                        $parsed[] = str_getcsv($line, $delim);
                    }
                    if (!empty($parsed) && count($parsed[0] ?? []) > count($bestRows[0] ?? [])) {
                        $bestRows = $parsed;
                    }
                }
                if (!empty($bestRows) && count($bestRows[0] ?? []) > 1) {
                    return $bestRows;
                }
            }
        }

        throw new \Exception('Format berkas tidak didukung atau berkas rusak. Pastikan file berformat Excel (.xls / .xlsx) atau CSV valid.');
    }

    /**
     * Parser berkas biner BIFF mentah (BIFF2/3/4/5/8) langsung dari stream
     * Menangani file .xls yang dihasilkan oleh mesin fingerprint/biometric (ZKTeco, Solution, Fingerspot)
     */
    private function parseRawBiffStream($filePath)
    {
        try {
            $data = @file_get_contents($filePath);
            if (!$data || strlen($data) < 8) {
                return null;
            }

            $firstType = unpack('v', substr($data, 0, 2))[1];
            // Cek apakah header BOF BIFF (0x0809, 0x0409, 0x0209, 0x0009)
            if (!in_array($firstType, [0x0809, 0x0409, 0x0209, 0x0009])) {
                return null;
            }

            $len = strlen($data);
            $offset = 0;
            $rows = [];

            while ($offset < $len) {
                if ($offset + 4 > $len) break;
                $type = unpack('v', substr($data, $offset, 2))[1];
                $length = unpack('v', substr($data, $offset + 2, 2))[1];
                $recordData = substr($data, $offset + 4, $length);
                $offset += 4 + $length;

                // BIFF2/3/4/5/8 LABEL (0x0204 or 0x0004)
                if ($type === 0x0204 || $type === 0x0004) {
                    $row = unpack('v', substr($recordData, 0, 2))[1];
                    $col = unpack('v', substr($recordData, 2, 2))[1];
                    if ($length >= 8) {
                        $strLen = unpack('v', substr($recordData, 6, 2))[1];
                        if ($strLen + 8 <= $length) {
                            $str = substr($recordData, 8, $strLen);
                        } elseif ($strLen + 7 <= $length) {
                            $str = substr($recordData, 7, $strLen);
                        } else {
                            $str = trim(substr($recordData, 6));
                        }
                    } else {
                        $str = trim(substr($recordData, 4));
                    }
                    $rows[$row][$col] = trim($str);
                }
                // RK (0x027E or 0x007E)
                elseif ($type === 0x027E || $type === 0x007E) {
                    $row = unpack('v', substr($recordData, 0, 2))[1];
                    $col = unpack('v', substr($recordData, 2, 2))[1];
                    $rknum = unpack('V', substr($recordData, 6, 4))[1];
                    $rows[$row][$col] = $this->decodeBiffRk($rknum);
                }
                // NUMBER (0x0203 or 0x0003)
                elseif ($type === 0x0203 || $type === 0x0003) {
                    $row = unpack('v', substr($recordData, 0, 2))[1];
                    $col = unpack('v', substr($recordData, 2, 2))[1];
                    $num = unpack('d', substr($recordData, 6, 8))[1];
                    $rows[$row][$col] = $num;
                }
                // MULRK (0x00BD or 0x02BD)
                elseif ($type === 0x00BD || $type === 0x02BD) {
                    $row = unpack('v', substr($recordData, 0, 2))[1];
                    $firstCol = unpack('v', substr($recordData, 2, 2))[1];
                    $numRk = ($length - 6) / 6;
                    for ($i = 0; $i < $numRk; $i++) {
                        $col = $firstCol + $i;
                        $rknum = unpack('V', substr($recordData, 6 + $i * 6 + 2, 4))[1];
                        $rows[$row][$col] = $this->decodeBiffRk($rknum);
                    }
                }
                // LABELSST (0x00FD or 0x02FD)
                elseif ($type === 0x00FD || $type === 0x02FD) {
                    $row = unpack('v', substr($recordData, 0, 2))[1];
                    $col = unpack('v', substr($recordData, 2, 2))[1];
                    $sstIndex = unpack('V', substr($recordData, 6, 4))[1];
                    $rows[$row][$col] = $sstIndex;
                }
            }

            if (empty($rows)) {
                return null;
            }

            ksort($rows);
            $result = [];
            foreach ($rows as $r => $cols) {
                ksort($cols);
                $maxCol = !empty($cols) ? max(array_keys($cols)) : 0;
                $rowArr = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $rowArr[$c] = $cols[$c] ?? null;
                }
                $result[] = $rowArr;
            }
            return $result;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Decode BIFF RK number
     */
    private function decodeBiffRk($rk)
    {
        if ($rk & 0x02) {
            $val = ($rk >> 2);
            if ($rk & 0x80000000) {
                $val |= 0xC0000000;
            }
        } else {
            $val = unpack('d', pack('VV', 0, $rk & 0xFFFFFFFC))[1];
        }
        if ($rk & 0x01) {
            $val /= 100;
        }
        return $val;
    }

    /**
     * Deteksi posisi kolom otomatis dari baris header jika tersedia
     */
    private function detectColumnMapping(array $row)
    {
        $mapping = [
            'pin' => 0,
            'nama' => 2,
            'tanggal' => 6,
            'scan1' => 7,
            'scan2' => 8,
            'scan3' => 9,
            'scan4' => 10,
        ];

        $foundHeader = false;
        $hasExactPin = false;

        foreach ($row as $colIdx => $val) {
            $val = strtolower(trim((string)$val));
            if ($val === '') continue;

            if (preg_match('/^(pin|user\s*id|badgenumber|ac-no\.?|enroll\s*id)$/i', $val)) {
                $mapping['pin'] = $colIdx;
                $hasExactPin = true;
                $foundHeader = true;
            } elseif (!$hasExactPin && preg_match('/^(id|no\.?\s*id|nik|nip)$/i', $val)) {
                $mapping['pin'] = $colIdx;
                $foundHeader = true;
            } elseif (preg_match('/^(nama|nama\s*karyawan|nama\s*pegawai|employee\s*name|name)$/i', $val)) {
                $mapping['nama'] = $colIdx;
                $foundHeader = true;
            } elseif (preg_match('/^(tanggal|tgl|date|att\s*date|waktu)$/i', $val)) {
                $mapping['tanggal'] = $colIdx;
                $foundHeader = true;
            } elseif (preg_match('/^(scan\s*1|masuk|in|jam\s*masuk|checkin|clock\s*in|on\s*duty)$/i', $val)) {
                $mapping['scan1'] = $colIdx;
                $foundHeader = true;
            } elseif (preg_match('/^(scan\s*2|keluar|out|jam\s*pulang|checkout|clock\s*out|off\s*duty)$/i', $val)) {
                $mapping['scan2'] = $colIdx;
                $foundHeader = true;
            } elseif (preg_match('/^(scan\s*3)$/i', $val)) {
                $mapping['scan3'] = $colIdx;
                $foundHeader = true;
            } elseif (preg_match('/^(scan\s*4)$/i', $val)) {
                $mapping['scan4'] = $colIdx;
                $foundHeader = true;
            }
        }

        return $foundHeader ? $mapping : null;
    }

    /**
     * Format nilai waktu/jam dari cell Excel
     */
    private function formatScanTime($val)
    {
        if (empty($val)) {
            return null;
        }

        $val = trim((string) $val);
        if ($val === '' || $val === '-' || $val === '0') {
            return null;
        }

        // Jika berbentuk desimal Excel time (misal 0.3340277)
        if (is_numeric($val) && floatval($val) > 0 && floatval($val) < 1) {
            try {
                return ExcelDate::excelToDateTimeObject(floatval($val))->format('H:i:s');
            } catch (\Exception $e) {}
        }

        // Jika format waktu jam:menit[:detik]
        try {
            return Carbon::parse($val)->format('H:i:s');
        } catch (\Exception $e) {
            return $val;
        }
    }

    /**
     * Format nilai tanggal dari cell Excel
     */
    private function parseTanggalAbsen($val)
    {
        if (empty($val)) {
            return null;
        }

        // Jika berbentuk serial number Excel (contoh: 46223)
        if (is_numeric($val) && floatval($val) > 30000) {
            try {
                return ExcelDate::excelToDateTimeObject(floatval($val))->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        $val = trim((string) $val);

        // Coba format umum d-m-Y atau d/m/Y atau Y-m-d atau Y/m/d
        try {
            return Carbon::createFromFormat('d-m-Y', $val)->format('Y-m-d');
        } catch (\Exception $e) {}

        try {
            return Carbon::createFromFormat('d/m/Y', $val)->format('Y-m-d');
        } catch (\Exception $e) {}

        try {
            return Carbon::createFromFormat('Y-m-d', $val)->format('Y-m-d');
        } catch (\Exception $e) {}

        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Preview dan validasi file Excel sebelum disimpan ke database
     */
    public function previewexcel(Request $request)
    {
        $request->validate(
            [
                'periodebulan' => 'required|integer|between:1,12',
                'periodetahun' => 'required|digits:4',
                'absensiexcel' => 'required|file'
            ],
            [
                'absensiexcel.required' => 'Silahkan pilih file terlebih dahulu.',
                'periodebulan.required' => 'Silahkan pilih periode bulan terlebih dahulu.',
                'periodetahun.required' => 'Silahkan pilih periode tahun terlebih dahulu.',
            ]
        );

        try {
            $rawRows = $this->parseSpreadsheetRows($request->file('absensiexcel'));

            if (empty($rawRows)) {
                return response()->json(['success' => false, 'message' => 'File Excel kosong atau tidak memiliki data.'], 422);
            }

            $karyawans = DataDosenTendik::with(['unit', 'jabatanFungsionals.masterFungsional', 'jabatanStrukturals.masterStruktural', 'shift'])->get()->keyBy('pin_absensi');

            $previewRows = [];
            $totalValid = 0;
            $totalInvalid = 0;
            $totalDuplicate = 0;

            // Default column index mapping
            $mapping = [
                'pin' => 0,
                'nama' => 2,
                'tanggal' => 6,
                'scan1' => 7,
                'scan2' => 8,
                'scan3' => 9,
                'scan4' => 10,
            ];

            $headerDetected = false;

            foreach ($rawRows as $index => $row) {
                // Check if this row is a header row
                if (!$headerDetected) {
                    $detected = $this->detectColumnMapping($row);
                    if ($detected) {
                        $mapping = $detected;
                        $headerDetected = true;
                        continue;
                    }
                }

                $pinVal = isset($row[$mapping['pin']]) ? trim((string) $row[$mapping['pin']]) : '';
                if ($pinVal === '') {
                    continue;
                }

                // Abaikan jika baris header manual
                $firstColUpper = strtoupper($pinVal);
                if (in_array($firstColUpper, ['PIN', 'NO', 'NO.', 'ID', 'NIP', 'NIK', 'NAMA', 'USER ID', 'BADGENUMBER', 'AC-NO', 'ENROLL ID'])) {
                    continue;
                }

                // Kolom Tanggal
                $tanggalRaw = $row[$mapping['tanggal']] ?? null;
                $tanggal = $this->parseTanggalAbsen($tanggalRaw);

                if (!$tanggal) {
                    continue;
                }

                $pin = $pinVal;
                $namaRaw = $row[$mapping['nama']] ?? '-';
                $scan1 = $this->formatScanTime($row[$mapping['scan1']] ?? null);
                $scan2 = $this->formatScanTime($row[$mapping['scan2']] ?? null);
                $scan3 = $this->formatScanTime($row[$mapping['scan3']] ?? null);
                $scan4 = $this->formatScanTime($row[$mapping['scan4']] ?? null);

                // Cek duplikasi di DB
                $isDuplicate = DataAbsensi::where('pin', $pin)->where('tanggal_absen', $tanggal)->exists();
                if ($isDuplicate) {
                    $totalDuplicate++;
                }

                // Dapatkan karyawan & shift (Auto-Detect berdasarkan scan pertama jika profil bebas)
                $karyawan = $karyawans->get($pin);
                $dayOfWeek = Carbon::parse($tanggal)->dayOfWeekIso;
                $shift = PresensiCalculationService::resolveShiftForEmployee($karyawan, $scan1, $dayOfWeek);

                // Buat dummy model untuk kalkulasi record
                $tempAbsensi = new DataAbsensi([
                    'pin' => $pin,
                    'nama' => $namaRaw,
                    'tanggal_absen' => $tanggal,
                    'scan_1' => $scan1,
                    'scan_2' => $scan2,
                    'scan_3' => $scan3,
                    'scan_4' => $scan4,
                ]);
                $tempAbsensi->setRelation('users', $karyawan);

                $calc = PresensiCalculationService::calculateRecord($tempAbsensi, $shift);

                if ($calc['akumulasi_validasi'] > 0) {
                    $totalValid++;
                } else {
                    $totalInvalid++;
                }

                $previewRows[] = [
                    'index' => count($previewRows) + 1,
                    'pin' => $pin,
                    'nama' => $karyawan ? $karyawan->nama_lengkap : $namaRaw,
                    'nama_raw' => $namaRaw,
                    'unit' => $karyawan && $karyawan->unit ? $karyawan->unit->nama_unit : '-',
                    'tanggal_absen' => $tanggal,
                    'tanggal_formatted' => Carbon::parse($tanggal)->format('d-m-Y'),
                    'scan_1' => $scan1,
                    'scan_2' => $scan2,
                    'scan_3' => $scan3,
                    'scan_4' => $scan4,
                    'master_shift_id' => $calc['master_shift_id'],
                    'nama_shift' => $shift ? $shift->nama_shift : 'Auto Detect',
                    'durasi_kerja' => $calc['durasi_kerja'],
                    'durasi_menit' => $calc['durasi_menit'],
                    'akumulasi_validasi' => $calc['akumulasi_validasi'],
                    'keterangan_validasi' => $calc['keterangan_validasi'],
                    'is_duplicate' => $isDuplicate,
                ];
            }

            if (empty($previewRows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ditemukan baris data presensi yang valid pada file tersebut. Pastikan berkas memiliki kolom PIN dan Tanggal presensi.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'summary' => [
                    'total' => count($previewRows),
                    'valid' => $totalValid,
                    'invalid' => $totalInvalid,
                    'duplicate' => $totalDuplicate,
                    'new' => count($previewRows) - $totalDuplicate,
                ],
                'rows' => $previewRows,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan data yang sudah di-preview ke database
     */
    public function simpanexcel(Request $request)
    {
        $request->validate([
            'periodebulan' => 'required|integer|between:1,12',
            'periodetahun' => 'required|digits:4',
            'rows' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $userNik = Auth::check() && $this->getCurrentProfile() ? $this->getCurrentProfile()->nik : 'System';
            $userName = Auth::check() && $this->getCurrentProfile() ? $this->getCurrentProfile()->nama : 'System';
            $now = Carbon::now()->format('Y-m-d H:i:s');

            $berhasil = 0;
            $duplikat = 0;

            foreach ($request->rows as $row) {
                $exist = DataAbsensi::where('pin', $row['pin'])->where('tanggal_absen', $row['tanggal_absen'])->exists();
                if ($exist) {
                    $duplikat++;
                    continue;
                }

                DataAbsensi::create([
                    'pin'                 => $row['pin'],
                    'nama'                => $row['nama_raw'] ?? $row['nama'],
                    'tanggal_absen'       => $row['tanggal_absen'],
                    'scan_1'              => $row['scan_1'] ?? null,
                    'scan_2'              => $row['scan_2'] ?? null,
                    'scan_3'              => $row['scan_3'] ?? null,
                    'scan_4'              => $row['scan_4'] ?? null,
                    'master_shift_id'     => $row['master_shift_id'] ?? null,
                    'durasi_kerja'        => $row['durasi_kerja'] ?? null,
                    'durasi_menit'        => $row['durasi_menit'] ?? 0,
                    'akumulasi_validasi'  => $row['akumulasi_validasi'] ?? 0.0,
                    'keterangan_validasi' => $row['keterangan_validasi'] ?? null,
                    'periode_bulan'       => $request->periodebulan,
                    'periode_tahun'       => $request->periodetahun,
                    'uploaded_at'         => $now,
                    'uploaded_nik'        => $userNik,
                    'uploaded_name'       => $userName,
                ]);

                $berhasil++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data presensi berhasil disimpan ke database! ' . $berhasil . ' data baru disimpan, ' . $duplikat . ' duplikat dilewati.',
                'berhasil' => $berhasil,
                'duplikat' => $duplikat,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBulan()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
