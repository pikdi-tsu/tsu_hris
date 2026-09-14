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
use App\Models\MasterKomponenPresensi;

class RiwayatAbsensiController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:riwayat-absensi');
        $this->middleware('permission:admin:riwayat-absensi:view')->only(['datatableabsensi', 'detail']);
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $bulan = $this->getBulan();
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        $komponenTransport = MasterKomponenPresensi::where('kategori', 'transport')->where('is_active', 'Y')->first();
        $defaultNominal = $komponenTransport ? $komponenTransport->nominal : 20000;

        $totalPegawai = DataDosenTendik::count();
        $totalHadirBulanIni = DataAbsensi::where('periode_bulan', $currentMonth)
            ->where('periode_tahun', $currentYear)
            ->sum('akumulasi_validasi');
        $totalPayrollBulanIni = $totalHadirBulanIni * $defaultNominal;

        $stats = [
            'total_pegawai' => $totalPegawai,
            'default_nominal' => $defaultNominal,
            'total_hadir_bulan_ini' => $totalHadirBulanIni,
            'total_payroll_bulan_ini' => $totalPayrollBulanIni,
        ];

        return view('admin::riwayatabsensi.index', [
            'title' => 'Rekap Riwayat Presensi & Payroll Transport',
            'bulan' => $bulan,
            'defaultBulan' => $currentMonth,
            'defaultTahun' => $currentYear,
            'defaultNominal' => $defaultNominal,
            'stats' => $stats,
        ]);
    }

    public function datatableabsensi(Request $request)
    {
        $defaultNominal = MasterKomponenPresensi::where('kategori', 'transport')->where('is_active', 'Y')->value('nominal') ?? 20000;
        $nominal = $request->input('nominal', $defaultNominal);

        $query = DataAbsensi::with(['users.unit'])
            ->selectRaw('pin, nama, SUM(akumulasi_validasi) as total_hadir, COUNT(*) as total_record, periode_bulan, periode_tahun');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_absen', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('periode_bulan') && $request->filled('periode_tahun')) {
            $query->where('periode_bulan', $request->periode_bulan)->where('periode_tahun', $request->periode_tahun);
        } else {
            // Default latest period
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $hasCurrent = DataAbsensi::where('periode_bulan', $currentMonth)->where('periode_tahun', $currentYear)->exists();
            if ($hasCurrent) {
                $query->where('periode_bulan', $currentMonth)->where('periode_tahun', $currentYear);
            } else {
                $latest = DataAbsensi::orderByDesc('periode_tahun')->orderByDesc('periode_bulan')->first();
                if ($latest) {
                    $query->where('periode_bulan', $latest->periode_bulan)->where('periode_tahun', $latest->periode_tahun);
                }
            }
        }

        $data = $query->groupBy('pin', 'nama', 'periode_bulan', 'periode_tahun')->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nik', function ($data) {
                $nikVal = $data->users ? ($data->users->nik ?? '-') : '-';
                return '<span class="tsu-badge-nik">' . e($nikVal) . '</span>';
            })
            ->addColumn('nama_lengkap', function ($data) {
                $nama = $data->users ? e($data->users->nama_lengkap) : e($data->nama);
                $unit = $data->users && $data->users->unit ? '<br><small class="text-muted font-weight-normal">' . e($data->users->unit->nama_unit) . '</small>' : '';
                return '<strong class="text-dark">' . $nama . '</strong>' . $unit;
            })
            ->addColumn('hadir', function ($data) {
                $valid = floatval($data->total_hadir ?? 0);
                return '<span class="tsu-badge-hadir">' . number_format($valid, 0) . ' Hari</span>';
            })
            ->addColumn('nominal_transport', function ($data) use ($nominal) {
                if ($data->users && !$data->users->dapat_uang_transport) {
                    return '<span class="tsu-badge-ineligible" title="Tidak berhak menerima uang transport">Rp 0 (Non-Eligible)</span>';
                }
                return '<span class="text-dark font-weight-bold">Rp ' . number_format($nominal, 0, ',', '.') . '</span>';
            })
            ->addColumn('total_transport', function ($data) use ($nominal) {
                if ($data->users && !$data->users->dapat_uang_transport) {
                    return '<span class="text-muted font-italic">Rp 0</span>';
                }
                $valid = floatval($data->total_hadir ?? 0);
                $total = $valid * $nominal;
                return '<strong class="text-success" style="font-size: 0.95rem;">Rp ' . number_format($total, 0, ',', '.') . '</strong>';
            })
            ->addColumn('aksi', function ($data) use ($request) {
                $params = http_build_query([
                    'periode_bulan' => $data->periode_bulan,
                    'periode_tahun' => $data->periode_tahun,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]);

                $btnDetail = '<button type="button" class="btn btn-xs tsu-btn-action tsu-btn-action--detail btn-detail-rekap mr-1" data-pin="' . $data->pin . '" data-nama="' . e($data->users ? $data->users->nama_lengkap : $data->nama) . '" title="Rincian Hari Presensi"><i class="fas fa-list mr-1"></i> Rincian</button>';
                $btnPdf = '<a href="' . route('admin.absensi.downloadslip', $data->pin) . '?' . $params . '" class="btn btn-xs tsu-btn-action tsu-btn-action--pdf" target="_blank" title="Cetak Slip PDF"><i class="fas fa-file-pdf mr-1"></i> Slip PDF</a>';

                return '<div class="text-center text-nowrap">' . $btnDetail . $btnPdf . '</div>';
            })
            ->rawColumns(['nik', 'nama_lengkap', 'hadir', 'nominal_transport', 'total_transport', 'aksi'])
            ->make(true);
    }

    public function detail(Request $request)
    {
        $pin = $request->pin;
        $query = DataAbsensi::where('pin', $pin);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_absen', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('periode_bulan') && $request->filled('periode_tahun')) {
            $query->where('periode_bulan', $request->periode_bulan)->where('periode_tahun', $request->periode_tahun);
        }

        $records = $query->orderBy('tanggal_absen', 'asc')->get();
        $karyawan = DataDosenTendik::with('unit')->where('pin_absensi', $pin)->first();

        return response()->json([
            'success' => true,
            'karyawan' => $karyawan,
            'records' => $records,
            'total_valid' => $records->sum('akumulasi_validasi'),
        ]);
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
