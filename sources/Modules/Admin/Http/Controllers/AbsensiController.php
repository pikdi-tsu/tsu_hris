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

        return view('admin::absensi.index', [
            'title' => 'Upload Presensi',
            'bulan' => $bulan,
        ]);
    }

    /**
     * Helper untuk membaca berkas spreadsheet secara fleksibel
     * dengan fallback multi-reader (Xlsx, Xls, Html, Csv, Xml)
     */
    private function parseSpreadsheetRows($uploadedFile)
    {
        $realPath = $uploadedFile->getRealPath();
        $spreadsheet = null;

        // 1. Coba deteksi otomatis via IOFactory::load
        try {
            $spreadsheet = IOFactory::load($realPath);
        } catch (\Throwable $e1) {
            // 2. Coba via Reader spesifik: Xlsx
            try {
                $reader = new Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($realPath);
            } catch (\Throwable $e2) {
                // 3. Coba via Reader spesifik: Xls
                try {
                    $reader = new Xls();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($realPath);
                } catch (\Throwable $e3) {
                    // 4. Coba via Reader: Html (fingerprint software sering export format HTML table dengan ekstensi .xls)
                    try {
                        $reader = new Html();
                        $spreadsheet = $reader->load($realPath);
                    } catch (\Throwable $e4) {
                        // 5. Coba via Reader: Csv
                        try {
                            $reader = new Csv();
                            $spreadsheet = $reader->load($realPath);
                        } catch (\Throwable $e5) {
                            // 6. Coba via Reader: Xml
                            $reader = new Xml();
                            $spreadsheet = $reader->load($realPath);
                        }
                    }
                }
            }
        }

        if (!$spreadsheet) {
            throw new \Exception('Format berkas tidak didukung atau rusak.');
        }

        $worksheet = $spreadsheet->getActiveSheet();
        return $worksheet->toArray(null, true, true, false);
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

        // Coba format umum d-m-Y atau d/m/Y
        try {
            return Carbon::createFromFormat('d-m-Y', $val)->format('Y-m-d');
        } catch (\Exception $e) {}

        try {
            return Carbon::createFromFormat('d/m/Y', $val)->format('Y-m-d');
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

            foreach ($rawRows as $index => $row) {
                if (empty($row[0])) {
                    continue;
                }

                // Abaikan header
                $firstCol = strtoupper(trim((string) $row[0]));
                if (in_array($firstCol, ['PIN', 'NO', 'NO.', 'ID', 'NIP', 'NIK', 'NAMA'])) {
                    continue;
                }

                // Kolom Tanggal (index 6 pada template mesin)
                $tanggalRaw = $row[6] ?? null;
                $tanggal = $this->parseTanggalAbsen($tanggalRaw);

                if (!$tanggal) {
                    continue;
                }

                $pin = (string) $row[0];
                $namaRaw = $row[2] ?? '-';
                $scan1 = $this->formatScanTime($row[7] ?? null);
                $scan2 = $this->formatScanTime($row[8] ?? null);
                $scan3 = $this->formatScanTime($row[9] ?? null);
                $scan4 = $this->formatScanTime($row[10] ?? null);

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
                    'message' => 'Tidak ditemukan baris data presensi yang valid pada file tersebut. Pastikan kolom PIN berada di kolom 1 dan Tanggal di kolom 7.'
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
