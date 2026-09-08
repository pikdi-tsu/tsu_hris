<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Services\PresensiCalculationService;
use App\Exports\RekapPresensiExport;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

use App\Models\DataDosenTendik;
use App\Models\DataAbsensi;
use App\Models\MasterShift;
use App\Models\MasterKomponenPresensi;

class RekapAbsensiController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:rekap-absensi');
        $this->middleware('permission:admin:rekap-absensi:edit')->only(['kalkulasiulang', 'updateperiode', 'updateDailyAttendance']);
        $this->middleware('permission:admin:rekap-absensi:view')->only(['datatable', 'detail', 'exportrekap', 'downloadallslip', 'downloadslip']);
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $bulan = $this->getBulan();
        $komponenTransport = MasterKomponenPresensi::where('kategori', 'transport')->where('is_active', 'Y')->first();
        $defaultNominal = $komponenTransport ? $komponenTransport->nominal : 20000;
        $shifts = MasterShift::where('is_active', 'Y')->get();

        // Cari periode terbaru yang memiliki data di database
        $latest = DataAbsensi::orderByDesc('periode_tahun')->orderByDesc('periode_bulan')->first();
        $defaultBulan = $latest ? $latest->periode_bulan : date('n');
        $defaultTahun = $latest ? $latest->periode_tahun : date('Y');

        return view('admin::rekap-absensi.index', [
            'title' => 'Rekap Data Absensi',
            'bulan' => $bulan,
            'defaultNominal' => $defaultNominal,
            'shifts' => $shifts,
            'defaultBulan' => $defaultBulan,
            'defaultTahun' => $defaultTahun,
        ]);
    }

    /**
     * Helper untuk menentukan rentang tanggal cut-off dari parameter request
     */
    private function resolveDateRange(Request $request): array
    {
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!empty($startDate) && !empty($endDate)) {
            return [
                'start' => $startDate,
                'end' => $endDate,
                'title' => Carbon::parse($startDate)->format('d F Y') . ' - ' . Carbon::parse($endDate)->format('d F Y'),
            ];
        }

        if (!empty($bulan) && !empty($tahun)) {
            $minDate = DataAbsensi::where('periode_bulan', $bulan)->where('periode_tahun', $tahun)->min('tanggal_absen');
            $maxDate = DataAbsensi::where('periode_bulan', $bulan)->where('periode_tahun', $tahun)->max('tanggal_absen');

            if ($minDate && $maxDate) {
                return [
                    'start' => $minDate,
                    'end' => $maxDate,
                    'title' => ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun,
                ];
            }

            $firstDay = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $lastDay = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            return [
                'start' => $firstDay,
                'end' => $lastDay,
                'title' => ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun,
            ];
        }

        return ['start' => null, 'end' => null, 'title' => ''];
    }

    /**
     * DataTables Rekap Presensi Per Orang (Summary per Karyawan)
     */
    public function datatable(Request $request)
    {
        $dateRange = $this->resolveDateRange($request);
        if (!$dateRange['start'] || !$dateRange['end']) {
            return DataTables::of(collect([]))->make(true);
        }

        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Jika belum ada data absensi yang di-upload pada rentang ini, kembalikan tabel kosong
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');
        $hasAbsensiQuery = DataAbsensi::query();
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $hasAbsensiQuery->whereBetween('tanggal_absen', [$startDate, $endDate]);
        } elseif (!empty($bulan) && !empty($tahun)) {
            $hasAbsensiQuery->where(function($q) use ($bulan, $tahun, $startDate, $endDate) {
                $q->where(function($sub) use ($bulan, $tahun) {
                    $sub->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
                })->orWhereBetween('tanggal_absen', [$startDate, $endDate]);
            });
        } else {
            $hasAbsensiQuery->whereBetween('tanggal_absen', [$startDate, $endDate]);
        }

        if (!$hasAbsensiQuery->exists()) {
            return DataTables::of(collect([]))->make(true);
        }

        // Ambil data karyawan yang memiliki PIN dan presensi pada periode ini (atau semua karyawan aktif)
        $karyawans = DataDosenTendik::with(['unit', 'jabatanFungsionals.masterFungsional', 'jabatanStrukturals.masterStruktural', 'shift'])
            ->whereNotNull('pin_absensi')
            ->where('pin_absensi', '!=', '')
            ->get();

        $rows = [];
        foreach ($karyawans as $karyawan) {
            $matrix = PresensiCalculationService::getEmployeeAttendanceLogs($karyawan, $startDate, $endDate);
            $summary = $matrix['summary'];

            // Lewati jika karyawan sama sekali tidak memiliki log kerja / hari di rentang ini
            if ($summary['total_hari'] === 0) {
                continue;
            }

            $rows[] = (object) [
                'id_user' => $karyawan->id,
                'pin' => $karyawan->pin_absensi,
                'nama' => $karyawan->nama_lengkap,
                'unit' => $karyawan->unit ? $karyawan->unit->nama_unit : '-',
                'tipe_karyawan' => $karyawan->tipe_karyawan ?? 'Tendik',
                'shift_name' => $matrix['shift'] ? $matrix['shift']->nama_shift : 'Auto Detect',
                'akumulasi_validasi' => $summary['total_valid'],
                'cuti' => $summary['total_cuti'],
                'izin' => $summary['total_izin'],
                'alpha' => $summary['total_alpha'],
                'libur' => $summary['total_libur'],
                'kurang_durasi' => $summary['total_kurang_durasi'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'periode_bulan' => $request->periode_bulan,
                'periode_tahun' => $request->periode_tahun,
            ];
        }

        return DataTables::of(collect($rows))
            ->addIndexColumn()
            ->addColumn('nama_karyawan', function ($row) {
                return '<strong>' . $row->nama . '</strong><br><small class="text-muted">' . $row->unit . ' | ' . $row->shift_name . '</small>';
            })
            ->addColumn('validasi_badge', function ($row) {
                return '<span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.95rem;"><i class="fas fa-check-circle mr-1"></i>' . $row->akumulasi_validasi . ' Hari</span>';
            })
            ->addColumn('cuti_badge', function ($row) {
                if ($row->cuti > 0) {
                    return '<span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">' . $row->cuti . ' Hari</span>';
                }
                return '<span class="text-muted">0</span>';
            })
            ->addColumn('izin_badge', function ($row) {
                if ($row->izin > 0) {
                    return '<span class="badge badge-purple px-2 py-1 font-weight-bold text-white" style="font-size: 0.95rem; background-color: #6f42c1;">' . $row->izin . ' Hari</span>';
                }
                return '<span class="text-muted">0</span>';
            })
            ->addColumn('alpha_badge', function ($row) {
                if ($row->alpha > 0) {
                    return '<span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">' . $row->alpha . ' Hari</span>';
                }
                return '<span class="text-muted">0</span>';
            })
            ->addColumn('aksi', function ($row) {
                $params = http_build_query([
                    'periode_bulan' => $row->periode_bulan,
                    'periode_tahun' => $row->periode_tahun,
                    'start_date' => $row->start_date,
                    'end_date' => $row->end_date,
                ]);

                $btnDetail = '<button type="button" class="btn btn-xs btn-info btn-detail-rekap mr-1" data-pin="' . $row->pin . '" data-id="' . $row->id_user . '" data-nama="' . addslashes($row->nama) . '" title="Rincian Hari Presensi"><i class="fas fa-calendar-alt mr-1"></i> Detail</button>';
                $btnPdf = '<a href="' . route('admin.rekap-absensi.downloadslip', $row->pin) . '?' . $params . '" class="btn btn-xs btn-danger" target="_blank" title="Cetak Slip PDF"><i class="fas fa-file-pdf mr-1"></i> Slip PDF</a>';

                return '<div class="text-center">' . $btnDetail . $btnPdf . '</div>';
            })
            ->rawColumns(['nama_karyawan', 'validasi_badge', 'cuti_badge', 'izin_badge', 'alpha_badge', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Detail Log Harian Presensi Per Karyawan
     */
    public function detail(Request $request)
    {
        $pin = $request->input('pin');
        $karyawan = DataDosenTendik::with(['unit', 'jabatanFungsionals.masterFungsional', 'jabatanStrukturals.masterStruktural', 'shift'])
            ->where('pin_absensi', $pin)
            ->first();

        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $dateRange = $this->resolveDateRange($request);
        if (!$dateRange['start'] || !$dateRange['end']) {
            return response()->json(['success' => false, 'message' => 'Rentang tanggal tidak valid.'], 422);
        }

        $matrix = PresensiCalculationService::getEmployeeAttendanceLogs($karyawan, $dateRange['start'], $dateRange['end']);

        return response()->json([
            'success' => true,
            'karyawan' => [
                'pin' => $karyawan->pin_absensi,
                'nama' => $karyawan->nama_lengkap,
                'unit' => $karyawan->unit ? $karyawan->unit->nama_unit : '-',
                'shift' => $matrix['shift'] ? $matrix['shift']->nama_shift : 'Auto Detect',
                'periode_text' => $dateRange['title'],
            ],
            'summary' => $matrix['summary'],
            'logs' => $matrix['logs'],
        ]);
    }

    /**
     * Update Koreksi Jam Kerja Harian (Scan 1, Scan 2, Scan 3, Scan 4)
     */
    public function updateDailyAttendance(Request $request)
    {
        try {
            $request->validate([
                'pin' => 'required',
                'tanggal_absen' => 'required|date',
            ]);

            $pin = $request->input('pin');
            $tanggal = $request->input('tanggal_absen');
            $absensiId = $request->input('absensi_id');

            $scan1 = $request->input('scan_1');
            $scan2 = $request->input('scan_2');
            $scan3 = $request->input('scan_3');
            $scan4 = $request->input('scan_4');

            // Normalisasi string kosong / tanda strip ke null
            $cleanTime = function($val) {
                if (empty($val) || $val === '-' || $val === '00:00:00') {
                    return null;
                }
                // Pastikan format H:i:s
                if (strlen($val) === 5) {
                    return $val . ':00';
                }
                return $val;
            };

            $scan1 = $cleanTime($scan1);
            $scan2 = $cleanTime($scan2);
            $scan3 = $cleanTime($scan3);
            $scan4 = $cleanTime($scan4);

            // Cari atau buat record DataAbsensi
            if ($absensiId) {
                $absensi = DataAbsensi::find($absensiId);
            } else {
                $absensi = DataAbsensi::where('pin', $pin)->where('tanggal_absen', $tanggal)->first();
            }

            $karyawan = DataDosenTendik::with(['unit', 'jabatanFungsionals.masterFungsional', 'jabatanStrukturals.masterStruktural', 'shift'])
                ->where('pin_absensi', $pin)
                ->first();

            if (!$absensi) {
                $absensi = new DataAbsensi([
                    'pin' => $pin,
                    'nama' => $karyawan ? $karyawan->nama_lengkap : 'Karyawan',
                    'tanggal_absen' => $tanggal,
                    'periode_bulan' => Carbon::parse($tanggal)->month,
                    'periode_tahun' => Carbon::parse($tanggal)->year,
                ]);
            }

            $absensi->scan_1 = $scan1;
            $absensi->scan_2 = $scan2;
            $absensi->scan_3 = $scan3;
            $absensi->scan_4 = $scan4;

            if ($karyawan) {
                $absensi->setRelation('users', $karyawan);
            }

            // Hitung ulang menggunakan service
            $calc = PresensiCalculationService::calculateRecord($absensi);

            $absensi->master_shift_id = $calc['master_shift_id'];
            $absensi->durasi_kerja = $calc['durasi_kerja'];
            $absensi->durasi_menit = $calc['durasi_menit'];
            $absensi->akumulasi_validasi = $calc['akumulasi_validasi'];
            $absensi->keterangan_validasi = $calc['keterangan_validasi'];

            $user = Auth::user();
            if ($user) {
                $absensi->updated_nik = $user->nik ?? ($user->username ?? 'admin');
                $absensi->updated_name = $user->name ?? 'Administrator';
            }
            $absensi->updated_at = now();

            $absensi->save();

            return response()->json([
                'success' => true,
                'message' => 'Jam kerja tanggal ' . Carbon::parse($tanggal)->translatedFormat('d F Y') . ' berhasil diperbarui dan dikalkulasi ulang.',
                'data' => $absensi,
                'calc' => $calc,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jam kerja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hitung ulang durasi dan validitas presensi
     */
    public function kalkulasiulang(Request $request)
    {
        try {
            $bulan = $request->input('periode_bulan');
            $tahun = $request->input('periode_tahun');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $result = PresensiCalculationService::processDateRange($request->start_date, $request->end_date);
            } elseif (!empty($bulan) && !empty($tahun)) {
                $result = PresensiCalculationService::processPeriode($bulan, $tahun);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih periode bulan/tahun atau rentang tanggal terlebih dahulu.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kalkulasi presensi berhasil! Total: ' . $result['total'] . ' data (' . $result['valid'] . ' valid, ' . $result['invalid'] . ' tidak memenuhi).',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan kalkulasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Rekap Presensi & Payroll Transport (Excel - Slide 4)
     */
    public function exportrekap(Request $request)
    {
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');
        $defaultNominal = MasterKomponenPresensi::where('kategori', 'transport')->where('is_active', 'Y')->value('nominal') ?? 20000;
        $nominal = $request->input('nominal', $defaultNominal);

        $query = DataAbsensi::with('users')->selectRaw('pin, nama, SUM(akumulasi_validasi) as total_hadir');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_absen', [$request->start_date, $request->end_date]);
            $periodeText = Carbon::parse($request->start_date)->format('d F Y') . ' - ' . Carbon::parse($request->end_date)->format('d F Y');
        } elseif (!empty($bulan) && !empty($tahun)) {
            $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
            $periodeText = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
        } else {
            return back()->with('error', 'Silahkan pilih periode terlebih dahulu.');
        }

        $data = $query->groupBy('pin', 'nama')->orderBy('nama', 'asc')->get();

        $fileName = 'REKAP_PRESENSI_' . str_replace(' ', '_', strtoupper($periodeText)) . '.xlsx';
        return Excel::download(new RekapPresensiExport($data, $periodeText, $nominal), $fileName);
    }

    /**
     * Download Batch ZIP Slip Presensi untuk semua karyawan (Slide 5)
     */
    public function downloadallslip(Request $request)
    {
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');

        $query = DataAbsensi::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_absen', [$request->start_date, $request->end_date]);
            $periodeTitle = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');
            $periodeText = Carbon::parse($request->start_date)->format('d F Y') . ' - ' . Carbon::parse($request->end_date)->format('d F Y');
            $prefixPeriod = Carbon::parse($request->start_date)->format('ym');
        } elseif (!empty($bulan) && !empty($tahun)) {
            $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
            $periodeTitle = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
            $periodeText = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
            $prefixPeriod = sprintf('%02d%02d', substr($tahun, -2), $bulan);
        } else {
            return back()->with('error', 'Silahkan pilih periode terlebih dahulu.');
        }

        $allAbsensi = $query->orderBy('tanggal_absen', 'asc')->get()->groupBy('pin');

        if ($allAbsensi->isEmpty()) {
            return back()->with('error', 'Tidak ada data presensi pada periode tersebut.');
        }

        $tempDir = storage_path('app/temp_slips_' . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $zipFileName = 'SLIP_PRESENSI_' . $prefixPeriod . '.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($allAbsensi as $pin => $records) {
                $firstRow = $records->first();
                $karyawan = DataDosenTendik::with('unit')->where('pin_absensi', $pin)->first();
                $karyawanName = $karyawan ? $karyawan->nama_lengkap : $firstRow->nama;
                $unitName = $karyawan && $karyawan->unit ? $karyawan->unit->nama_unit : '-';

                $pdf = Pdf::loadView('admin::absensi.slip_pdf', [
                    'karyawanName' => $karyawanName,
                    'pin' => $pin,
                    'unitName' => $unitName,
                    'periodeTitle' => $periodeTitle,
                    'periodeText' => $periodeText,
                    'records' => $records,
                ])->setPaper('a4', 'portrait');

                $cleanName = strtoupper(preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($karyawanName)));
                $pdfName = $prefixPeriod . ' SLIP PRESENSI - ' . $cleanName . '.pdf';
                $pdfFilePath = $tempDir . '/' . $pdfName;

                $pdf->save($pdfFilePath);
                $zip->addFile($pdfFilePath, $pdfName);
            }

            $zip->close();

            // Cleanup temp PDF files
            array_map('unlink', glob("$tempDir/*"));
            rmdir($tempDir);

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file ZIP.');
    }

    /**
     * Download Slip Presensi Individu (PDF)
     */
    public function downloadslip(Request $request, $pin)
    {
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');

        $query = DataAbsensi::where('pin', $pin);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_absen', [$request->start_date, $request->end_date]);
            $periodeTitle = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');
            $periodeText = Carbon::parse($request->start_date)->format('d F Y') . ' - ' . Carbon::parse($request->end_date)->format('d F Y');
            $prefixPeriod = Carbon::parse($request->start_date)->format('ym');
        } elseif (!empty($bulan) && !empty($tahun)) {
            $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
            $periodeTitle = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
            $periodeText = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
            $prefixPeriod = sprintf('%02d%02d', substr($tahun, -2), $bulan);
        } else {
            $latest = DataAbsensi::where('pin', $pin)->orderByDesc('tanggal_absen')->first();
            $bulan = $latest ? $latest->periode_bulan : date('n');
            $tahun = $latest ? $latest->periode_tahun : date('Y');
            $query->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
            $periodeTitle = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
            $periodeText = ($this->getBulan()[$bulan] ?? $bulan) . ' ' . $tahun;
            $prefixPeriod = sprintf('%02d%02d', substr($tahun, -2), $bulan);
        }

        $records = $query->orderBy('tanggal_absen', 'asc')->get();

        if ($records->isEmpty()) {
            return back()->with('error', 'Tidak ada data absensi untuk karyawan ini pada periode yang dipilih.');
        }

        $firstRow = $records->first();
        $karyawan = DataDosenTendik::with('unit')->where('pin_absensi', $pin)->first();
        $karyawanName = $karyawan ? $karyawan->nama_lengkap : $firstRow->nama;
        $unitName = $karyawan && $karyawan->unit ? $karyawan->unit->nama_unit : '-';

        $pdf = Pdf::loadView('admin::absensi.slip_pdf', [
            'karyawanName' => $karyawanName,
            'pin' => $pin,
            'unitName' => $unitName,
            'periodeTitle' => $periodeTitle,
            'periodeText' => $periodeText,
            'records' => $records,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $cleanName = strtoupper(preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($karyawanName)));
        $pdfFileName = $prefixPeriod . ' SLIP PRESENSI - ' . $cleanName . '.pdf';

        return $pdf->download($pdfFileName);
    }

    /**
     * Update Periode Absensi Massal
     */
    public function updateperiode(Request $request)
    {
        $request->validate(
            [
                'periodebulanold' => 'required|integer|between:1,12',
                'periodetahunold' => 'required|digits:4',
                'periodebulannew' => 'required|integer|between:1,12',
                'periodetahunnew' => 'required|digits:4',
            ],
            [
                'periodebulanold.required' => 'Silahkan pilih periode bulan lama terlebih dahulu.',
                'periodetahunold.required' => 'Silahkan pilih periode tahun lama terlebih dahulu.',
                'periodebulannew.required' => 'Silahkan pilih periode bulan baru terlebih dahulu.',
                'periodetahunnew.required' => 'Silahkan pilih periode tahun baru terlebih dahulu.',
            ]
        );

        $checkperiode = DataAbsensi::where('periode_bulan', $request->periodebulanold)->where('periode_tahun', $request->periodetahunold)->exists();
        if (!$checkperiode) {
            return back()->with('error', 'Data Periode Tersebut Tidak Ditemukan');
        }

        try {
            DataAbsensi::where('periode_bulan', $request->periodebulanold)->where('periode_tahun', $request->periodetahunold)->update([
                'periode_bulan' => $request->periodebulannew,
                'periode_tahun' => $request->periodetahunnew,
                'updated_at'   => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_nik'  => Auth::check() && $this->getCurrentProfile() ? $this->getCurrentProfile()->nik : 'System',
                'updated_name' => Auth::check() && $this->getCurrentProfile() ? $this->getCurrentProfile()->nama : 'System',
            ]);

            PresensiCalculationService::processPeriode($request->periodebulannew, $request->periodetahunnew);

            return back()->with('success', 'Periode Absensi Berhasil Diperbarui & Dikalkulasi Ulang!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_UPDATE_ABSENSI_FAIL]',
                'Gagal menyimpan perubahan periode absensi.',
                "Update Periode Absensi",
                $request
            );
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
