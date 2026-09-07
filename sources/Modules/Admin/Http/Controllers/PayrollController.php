<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\PayrollKaryawan;
use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodApproval;
use App\Services\PayrollCalculationService;
use App\Exports\RekapPayrollExport;
use App\Exports\RekapBankTransferExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;
use App\Notifications\PayrollApprovalNotification;
use App\Models\User;

class PayrollController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:payroll');
        $this->middleware('permission:admin:payroll:view')->only(['datatable', 'getKaryawanData', 'approvalHistory', 'slipPdf', 'downloadAllSlip', 'exportExcel', 'exportBank']);
        $this->middleware('permission:admin:payroll:edit')->only(['updateKaryawan', 'recalculate', 'submitApproval', 'approveStep', 'rejectStep', 'updateApprovers', 'lock', 'unlock']);
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    /**
     * Helper untuk mengirim notifikasi ke user terkait DataDosenTendik
     */
    private function notifyKaryawanUser($karyawan, $notification)
    {
        if (!$karyawan) return;
        $user = null;
        if (!empty($karyawan->user_id)) {
            $user = User::find($karyawan->user_id);
        }
        if (!$user && !empty($karyawan->nik)) {
            $user = User::where('nik', $karyawan->nik)->first();
        }
        if ($user) {
            try {
                $user->notify($notification);
            } catch (\Exception $e) {
                // Ignore safe notification failure
            }
        }
    }

    /**
     * Helper untuk mengirim notifikasi ke user_id
     */
    private function notifyUserById($userId, $notification)
    {
        if (!$userId) return;
        $user = User::find($userId);
        if ($user) {
            try {
                $user->notify($notification);
            } catch (\Exception $e) {
                // Ignore safe notification failure
            }
        }
    }

    public function index()
    {
        $periods = PayrollPeriod::where('tipe', 'karyawan')
            ->with(['validator1', 'validator2', 'approvalKaryawan', 'createdUser'])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $bulanList = $this->getBulan();
        $currentYear = date('Y');

        $stats = [
            'total_periods' => $periods->count(),
            'total_locked'  => $periods->where('status', 'locked')->count(),
            'total_draft'   => $periods->whereIn('status', ['draft', 'revision_requested'])->count(),
            'latest_payout' => $periods->first() ? $periods->first()->total_gaji_bersih : 0,
        ];

        // Daftar seluruh karyawan untuk Select2 pencarian Validator 1, Validator 2, dan Approval
        $allKaryawans = DataDosenTendik::where('is_active', 1)
            ->with('unit')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nik', 'posisi', 'unit_id', 'tipe_karyawan']);

        return view('admin::payroll.index', [
            'title'        => 'Payroll Karyawan (Gaji Bulanan)',
            'periods'      => $periods,
            'bulanList'    => $bulanList,
            'currentYear'  => $currentYear,
            'stats'        => $stats,
            'allKaryawans' => $allKaryawans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan'             => 'required|integer|between:1,12',
            'tahun'             => 'required|integer|min:2020|max:2050',
            'start_date_cutoff' => 'nullable|date',
            'end_date_cutoff'   => 'nullable|date|after_or_equal:start_date_cutoff',
            'validator_1_id'    => 'required|uuid',
            'validator_2_id'    => 'required|uuid',
            'approval_id'       => 'required|uuid',
        ], [
            'validator_1_id.required' => 'Silahkan pilih Validator 1.',
            'validator_2_id.required' => 'Silahkan pilih Validator 2.',
            'approval_id.required'    => 'Silahkan pilih Approval (Paling Atas).',
        ]);

        $bulan = intval($request->bulan);
        $tahun = intval($request->tahun);
        $bulanList = $this->getBulan();
        $namaBulan = $bulanList[$bulan] ?? 'Bulan ' . $bulan;

        $kodePeriode = sprintf('PAY-%04d%02d-KARYAWAN', $tahun, $bulan);
        $namaPeriode = "Payroll {$namaBulan} {$tahun}";

        // Cek apakah periode sudah ada
        $existing = PayrollPeriod::where('kode_periode', $kodePeriode)->first();
        if ($existing) {
            return redirect()->route('admin.payroll.show', $existing->id)
                ->with('info', "Periode {$namaPeriode} sudah ada sebelumnya.");
        }

        // Buat Periode Baru
        $period = PayrollPeriod::create([
            'kode_periode'      => $kodePeriode,
            'nama_periode'      => $namaPeriode,
            'tipe'              => 'karyawan',
            'bulan'             => $bulan,
            'tahun'             => $tahun,
            'start_date_cutoff' => $request->start_date_cutoff,
            'end_date_cutoff'   => $request->end_date_cutoff,
            'validator_1_id'    => $request->validator_1_id,
            'validator_2_id'    => $request->validator_2_id,
            'approval_id'       => $request->approval_id,
            'status'            => 'draft',
            'created_by'        => Auth::id(),
            'catatan'           => $request->catatan,
        ]);

        // Catat di log riwayat approval
        $myProfile = $this->getCurrentProfile();
        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => 'draft',
            'role_label'        => 'Pembuat Draft',
            'user_id'           => Auth::id(),
            'karyawan_id'       => $myProfile ? $myProfile->id : null,
            'karyawan_name'     => $myProfile ? $myProfile->nama : Auth::user()->name,
            'action'            => 'submitted',
            'note'              => 'Periode penggajian dibuat dan data awal berhasil digenerate otomatis.',
        ]);

        // Generate data awal otomatis
        PayrollCalculationService::generateOrRefreshPeriod($period);

        return redirect()->route('admin.payroll.show', $period->id)
            ->with('success', "Periode {$namaPeriode} berhasil dibuat! Silahkan kroscek data sebelum diajukan ke Validator 1.");
    }

    public function show($id)
    {
        $period = PayrollPeriod::with([
            'lockedUser',
            'createdUser',
            'validator1',
            'validator2',
            'approvalKaryawan',
            'approvals.user'
        ])->findOrFail($id);

        $karyawans = $period->karyawans;

        $summary = [
            'total_pegawai'     => $karyawans->count(),
            'total_gapok'       => $karyawans->sum('gaji_pokok'),
            'total_tunjangan'   => $karyawans->sum(function ($k) {
                return $k->tunjangan_fungsional + $k->tunjangan_struktural + $k->tunjangan_khusus + $k->tunjangan_keluarga + $k->tunjangan_anak + $k->tunjangan_kesehatan;
            }),
            'total_transport'   => $karyawans->sum('total_transport'),
            'total_lembur'      => $karyawans->sum('total_lembur'),
            'total_gaji_kotor'  => $karyawans->sum('gaji_kotor'),
            'total_potongan'    => $karyawans->sum('total_potongan'),
            'total_gaji_bersih' => $karyawans->sum('gaji_bersih'),
        ];

        $myProfile = $this->getCurrentProfile();
        $isCreator = ($period->created_by == Auth::id());
        $myKaryawanId = $myProfile ? $myProfile->id : null;

        // Cek wewenang saat ini
        $isValidator1 = ($period->validator_1_id == $myKaryawanId);
        $isValidator2 = ($period->validator_2_id == $myKaryawanId);
        $isApproval   = ($period->approval_id == $myKaryawanId);

        // Auto mark as read notifikasi lonceng terkait periode ini untuk user saat ini
        try {
            Auth::user()->unreadNotifications()
                ->where('data->payroll_period_id', $period->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            // Ignore
        }

        $allKaryawans = DataDosenTendik::where('is_active', 1)
            ->with('unit')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nik', 'posisi', 'unit_id', 'tipe_karyawan']);

        return view('admin::payroll.show', [
            'title'        => 'Kroscek & Review: ' . $period->nama_periode,
            'period'       => $period,
            'summary'      => $summary,
            'isCreator'    => $isCreator,
            'isValidator1' => $isValidator1,
            'isValidator2' => $isValidator2,
            'isApproval'   => $isApproval,
            'myProfile'    => $myProfile,
            'allKaryawans' => $allKaryawans,
        ]);
    }

    public function datatable(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $query = PayrollKaryawan::where('payroll_period_id', $period->id);

        if ($request->filled('tipe_karyawan')) {
            $query->where('tipe_karyawan', $request->tipe_karyawan);
        }

        $isCreator = ($period->created_by == Auth::id());
        $canEdit = $isCreator && in_array($period->status, ['draft', 'revision_requested']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $posisi = $row->posisi ? '<br><small class="text-muted">' . e($row->posisi) . ' (' . e($row->nama_unit) . ')</small>' : '<br><small class="text-muted">' . e($row->nama_unit) . '</small>';
                $badgeTipe = $row->tipe_karyawan === 'Dosen'
                    ? '<span class="badge badge-primary px-1 mr-1" style="font-size: 7pt;">Dosen</span>'
                    : '<span class="badge badge-secondary px-1 mr-1" style="font-size: 7pt;">Tendik</span>';
                return '<div>' . $badgeTipe . '<strong>' . e($row->nama) . '</strong>' . $posisi . '</div>';
            })
            ->addColumn('gol_jabatan', function ($row) {
                $gol = '<strong>' . e($row->golongan_pangkat ?: '-') . '</strong> (' . $row->persen_gapok . '%)';
                $struk = $row->jabatan_struktural && $row->jabatan_struktural !== '-' ? '<br><small class="text-info"><i class="fas fa-sitemap mr-1"></i>' . e($row->jabatan_struktural) . '</small>' : '';
                return $gol . $struk;
            })
            ->addColumn('gapok_formatted', function ($row) {
                return 'Rp ' . number_format($row->gaji_pokok, 0, ',', '.');
            })
            ->addColumn('gaji_tetap_formatted', function ($row) {
                return '<strong>Rp ' . number_format($row->gaji_tetap, 0, ',', '.') . '</strong>';
            })
            ->addColumn('transport_info', function ($row) {
                $hadir = number_format($row->hari_hadir_valid, 0) . ' Hari';
                $total = '<strong>Rp ' . number_format($row->total_transport, 0, ',', '.') . '</strong>';
                return '<div class="text-center">' . $total . '<br><small class="text-muted">' . $hadir . '</small></div>';
            })
            ->addColumn('lembur_info', function ($row) {
                if ($row->total_lembur <= 0) return '<div class="text-center text-muted">-</div>';
                return '<div class="text-center"><strong>Rp ' . number_format($row->total_lembur, 0, ',', '.') . '</strong><br><small class="text-muted">' . number_format($row->total_jam_lembur, 1) . ' Jam</small></div>';
            })
            ->addColumn('potongan_info', function ($row) {
                if ($row->total_potongan <= 0) return '<div class="text-center text-muted">-</div>';
                return '<div class="text-center text-danger font-weight-bold">Rp ' . number_format($row->total_potongan, 0, ',', '.') . '</div>';
            })
            ->addColumn('gaji_bersih_formatted', function ($row) {
                return '<div class="text-right font-weight-bold text-success" style="font-size: 1rem;">Rp ' . number_format($row->gaji_bersih, 0, ',', '.') . '</div>';
            })
            ->addColumn('keterangan', function ($row) {
                if (!empty($row->catatan_koreksi)) {
                    return '<div class="text-wrap" style="min-width: 130px; max-width: 220px;">
                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mb-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-edit mr-1"></i> Koreksi Manual
                                </span><br>
                                <span class="text-dark small font-weight-bold" style="font-size: 8pt;">' . e($row->catatan_koreksi) . '</span>
                            </div>';
                }
                return '<div class="text-center text-muted">-</div>';
            })
            ->addColumn('aksi', function ($row) use ($canEdit) {
                $btnDetail = '<button type="button" class="btn btn-xs btn-info btn-detail-karyawan mr-1" data-id="' . $row->id . '" title="Lihat Rincian Penggajian" style="white-space: nowrap;"><i class="fas fa-eye mr-1"></i>Detail</button>';

                // Hanya Pembuat Draft yang memiliki tombol Edit (saat draft/revisi)
                $btnEdit = $canEdit
                    ? '<button type="button" class="btn btn-xs btn-warning btn-edit-karyawan mr-1" data-id="' . $row->id . '" title="Koreksi / Penyesuaian Manual" style="white-space: nowrap;"><i class="fas fa-edit mr-1"></i>Edit</button>'
                    : '';
                $btnPdf = '<a href="' . route('admin.payroll.slip-pdf', $row->id) . '" target="_blank" class="btn btn-xs btn-danger" title="Cetak Slip Gaji PDF" style="white-space: nowrap;"><i class="fas fa-file-pdf mr-1"></i>Slip</a>';

                return '<div class="d-inline-flex align-items-center justify-content-center" style="white-space: nowrap;">' . $btnDetail . $btnEdit . $btnPdf . '</div>';
            })
            ->rawColumns(['pegawai_info', 'gol_jabatan', 'gaji_tetap_formatted', 'transport_info', 'lembur_info', 'potongan_info', 'gaji_bersih_formatted', 'keterangan', 'aksi'])
            ->make(true);
    }

    public function getKaryawanData($id)
    {
        $karyawan = PayrollKaryawan::with('period')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $karyawan
        ]);
    }

    public function updateKaryawan(Request $request, $id)
    {
        $karyawan = PayrollKaryawan::with('period')->findOrFail($id);
        $period = $karyawan->period;

        // Validasi hak akses: Hanya pembuat draft
        if ($period->created_by != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat draft yang berwenang membenahi data penggajian.'
            ], 403);
        }

        // Validasi status: hanya saat draft atau revision_requested
        if (!in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak dapat diedit saat sedang dalam proses verifikasi atau sudah terkunci.'
            ], 422);
        }

        $request->validate([
            'gaji_pokok'            => 'required|numeric|min:0',
            'tunjangan_fungsional'  => 'nullable|numeric|min:0',
            'tunjangan_struktural'  => 'nullable|numeric|min:0',
            'tunjangan_khusus'      => 'nullable|numeric|min:0',
            'tunjangan_keluarga'    => 'nullable|numeric|min:0',
            'tunjangan_anak'        => 'nullable|numeric|min:0',
            'tunjangan_kesehatan'   => 'nullable|numeric|min:0',
            'hari_hadir_valid'      => 'nullable|numeric|min:0',
            'tarif_transport'       => 'nullable|numeric|min:0',
            'total_lembur'          => 'nullable|numeric|min:0',
            'hari_unpaid_leave'     => 'nullable|numeric|min:0',
            'potongan_bpjs_kes'     => 'nullable|numeric|min:0',
            'potongan_lainnya'      => 'nullable|numeric|min:0',
            'keterangan_potongan'   => 'nullable|string|max:255',
            'catatan_koreksi'       => 'nullable|string',
        ]);

        $karyawan->gaji_pokok            = floatval($request->gaji_pokok);
        $karyawan->tunjangan_fungsional  = floatval($request->tunjangan_fungsional ?: 0);
        $karyawan->tunjangan_struktural  = floatval($request->tunjangan_struktural ?: 0);
        $karyawan->tunjangan_khusus      = floatval($request->tunjangan_khusus ?: 0);
        $karyawan->tunjangan_keluarga    = floatval($request->tunjangan_keluarga ?: 0);
        $karyawan->tunjangan_anak        = floatval($request->tunjangan_anak ?: 0);
        $karyawan->tunjangan_kesehatan   = floatval($request->tunjangan_kesehatan ?: 0);
        $karyawan->hari_hadir_valid      = floatval($request->hari_hadir_valid ?: 0);
        $karyawan->tarif_transport       = floatval($request->tarif_transport ?: 20000);
        $karyawan->total_lembur          = floatval($request->total_lembur ?: 0);
        $karyawan->hari_unpaid_leave     = floatval($request->hari_unpaid_leave ?: 0);
        $karyawan->potongan_bpjs_kes     = floatval($request->potongan_bpjs_kes ?: 0);
        $karyawan->potongan_lainnya      = floatval($request->potongan_lainnya ?: 0);
        $karyawan->keterangan_potongan   = $request->keterangan_potongan;
        $karyawan->catatan_koreksi       = $request->catatan_koreksi;

        $karyawan->recalculateTotals();
        $karyawan->save();

        // Sinkronisasi total periode
        PayrollCalculationService::syncPeriodTotals($period);

        return response()->json([
            'success' => true,
            'message' => 'Data payroll pegawai berhasil diperbarui.',
            'row'     => $karyawan,
            'period'  => $period->fresh(),
        ]);
    }

    public function recalculate($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->created_by != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat draft yang berwenang melakukan hitung ulang.'
            ], 403);
        }

        if (!in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak dapat dihitung ulang saat sedang dalam proses verifikasi atau sudah terkunci.'
            ], 422);
        }

        try {
            $res = PayrollCalculationService::generateOrRefreshPeriod($period);
            return response()->json([
                'success' => true,
                'message' => 'Kalkulasi ulang berhasil dilakukan untuk ' . $res['total_pegawai'] . ' pegawai.',
                'period'  => $period->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ulang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pembuat mengajukan draft ke Validator 1
     */
    public function submitApproval(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->created_by != Auth::id()) {
            return back()->with('error', 'Hanya pembuat draft yang berwenang mengajukan payroll ke Validator.');
        }

        if (!in_array($period->status, ['draft', 'revision_requested'])) {
            return back()->with('error', 'Periode ini sedang dalam proses verifikasi atau sudah terkunci.');
        }

        $myProfile = $this->getCurrentProfile();
        $isResubmit = ($period->status === 'revision_requested');

        $period->update([
            'status'            => 'pending_val_1',
            'rejection_note'    => null,
            'rejection_by_role' => null,
        ]);

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => 'validator_1',
            'role_label'        => 'Pembuat Draft',
            'user_id'           => Auth::id(),
            'karyawan_id'       => $myProfile ? $myProfile->id : null,
            'karyawan_name'     => $myProfile ? $myProfile->nama : Auth::user()->name,
            'action'            => 'submitted',
            'note'              => $request->catatan_pengajuan ?: ($isResubmit ? 'Revisi telah dibenahi dan diajukan ulang ke Validator 1.' : 'Diajukan ke Validator 1 untuk pemeriksaan data.'),
        ]);

        // Kirim Notifikasi Lonceng ke Validator 1
        $this->notifyKaryawanUser(
            $period->validator1,
            new PayrollApprovalNotification(
                $period,
                "Periode penggajian {$period->nama_periode} telah diajukan oleh " . (Auth::user()->name) . ". Mohon lakukan pemeriksaan dan validasi data.",
                'validator_1',
                'Kroscek & Validasi'
            )
        );

        return back()->with('success', 'Payroll berhasil diajukan ke Validator 1 (' . ($period->validator1->nama ?? '-') . '). Menunggu verifikasi.');
    }

    /**
     * Aksi Persetujuan Bertingkat (Validator 1 -> Validator 2 -> Approval Paling Atas)
     */
    public function approveStep(Request $request, $id)
    {
        $period = PayrollPeriod::with(['validator1', 'validator2', 'approvalKaryawan'])->findOrFail($id);
        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;
        $karyawanId = $myProfile ? $myProfile->id : null;

        $nextStatus = '';
        $stepName = '';
        $roleLabel = '';
        $pesanSukses = '';

        if ($period->status === 'pending_val_1') {
            $nextStatus = 'pending_val_2';
            $stepName = 'validator_1';
            $roleLabel = 'Validator 1';
            $pesanSukses = 'Verifikasi Tingkat 1 disetujui. Berkas diteruskan ke Validator 2 (' . ($period->validator2->nama ?? '-') . ').';
        } elseif ($period->status === 'pending_val_2') {
            $nextStatus = 'pending_approval';
            $stepName = 'validator_2';
            $roleLabel = 'Validator 2';
            $pesanSukses = 'Verifikasi Tingkat 2 disetujui. Berkas diteruskan ke Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? '-') . ').';
        } elseif ($period->status === 'pending_approval') {
            // Approval Paling Atas: SISTEM OTOMATIS MENGUNCI (LOCKED)
            $nextStatus = 'locked';
            $stepName = 'approval';
            $roleLabel = 'Approval (Paling Atas)';
            $period->locked_at = now();
            $period->locked_by = Auth::id();
            $pesanSukses = 'Persetujuan Final berhasil diberikan! Sistem telah OTOMATIS MENGUNCI (LOCKED) periode penggajian ini.';
        } else {
            return back()->with('error', 'Status periode saat ini tidak membutuhkan persetujuan.');
        }

        $period->status = $nextStatus;
        $period->save();

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => $stepName,
            'role_label'        => $roleLabel,
            'user_id'           => Auth::id(),
            'karyawan_id'       => $karyawanId,
            'karyawan_name'     => $userName,
            'action'            => 'approved',
            'note'              => $request->note ?: 'Disetujui tanpa catatan.',
        ]);

        // Kirim Notifikasi Lonceng ke pihak berikutnya
        if ($nextStatus === 'pending_val_2') {
            $this->notifyKaryawanUser(
                $period->validator2,
                new PayrollApprovalNotification(
                    $period,
                    "Periode penggajian {$period->nama_periode} telah disetujui oleh Validator 1 (" . ($period->validator1->nama ?? 'Validator 1') . "). Menunggu verifikasi Anda.",
                    'validator_2',
                    'Kroscek & Validasi'
                )
            );
        } elseif ($nextStatus === 'pending_approval') {
            $this->notifyKaryawanUser(
                $period->approvalKaryawan,
                new PayrollApprovalNotification(
                    $period,
                    "Periode penggajian {$period->nama_periode} telah diverifikasi oleh Validator 1 & 2. Menunggu persetujuan final & penguncian Anda.",
                    'approval',
                    'Persetujuan Final & Lock'
                )
            );
        } elseif ($nextStatus === 'locked') {
            $this->notifyUserById(
                $period->created_by,
                new PayrollApprovalNotification(
                    $period,
                    "Selamat! Periode penggajian {$period->nama_periode} telah disetujui secara final oleh " . ($period->approvalKaryawan->nama ?? 'Approval Paling Atas') . " dan resmi TERKUNCI.",
                    'locked',
                    'Lihat Rekap Final'
                )
            );
        }

        // Auto mark as read notifikasi periode ini untuk approver saat ini
        try {
            Auth::user()->unreadNotifications()
                ->where('data->payroll_period_id', $period->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            // Ignore
        }

        return back()->with('success', $pesanSukses);
    }

    /**
     * Aksi Meminta Revisi (Tolak untuk perbaikan)
     */
    public function rejectStep(Request $request, $id)
    {
        $request->validate([
            'catatan_revisi' => 'required|string|min:5',
        ], [
            'catatan_revisi.required' => 'Mohon isi catatan revisi agar pembuat draft mengetahui hal yang perlu diperbaiki.',
            'catatan_revisi.min'      => 'Catatan revisi minimal 5 karakter.',
        ]);

        $period = PayrollPeriod::with(['validator1', 'validator2', 'approvalKaryawan'])->findOrFail($id);
        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;
        $karyawanId = $myProfile ? $myProfile->id : null;

        $roleLabel = 'Approver';
        $stepName = $period->status;

        if ($period->status === 'pending_val_1') {
            $roleLabel = 'Validator 1 (' . ($period->validator1->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_val_2') {
            $roleLabel = 'Validator 2 (' . ($period->validator2->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_approval') {
            $roleLabel = 'Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? $userName) . ')';
        } else {
            return back()->with('error', 'Periode saat ini tidak sedang dalam tahapan approval.');
        }

        $period->update([
            'status'            => 'revision_requested',
            'rejection_by_role' => $roleLabel,
            'rejection_note'    => $request->catatan_revisi,
        ]);

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => $stepName,
            'role_label'        => $roleLabel,
            'user_id'           => Auth::id(),
            'karyawan_id'       => $karyawanId,
            'karyawan_name'     => $userName,
            'action'            => 'revision_requested',
            'note'              => $request->catatan_revisi,
        ]);

        // Kirim Notifikasi Lonceng ke Pembuat Draft
        $this->notifyUserById(
            $period->created_by,
            new PayrollApprovalNotification(
                $period,
                "{$roleLabel} meminta revisi untuk periode {$period->nama_periode}. Catatan: \"{$request->catatan_revisi}\".",
                'revision',
                'Benahi Data Payroll'
            )
        );

        // Auto mark as read notifikasi periode ini untuk approver saat ini
        try {
            Auth::user()->unreadNotifications()
                ->where('data->payroll_period_id', $period->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            // Ignore
        }

        return back()->with('warning', "Catatan revisi berhasil dikirim ke Pembuat Draft. Status periode kini: PERLU REVISI.");
    }

    /**
     * Update susunan Validator 1, Validator 2, dan Approval jika terjadi kesalahan input
     */
    public function updateApprovers(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->created_by != Auth::id()) {
            return back()->with('error', 'Hanya pembuat draft yang berwenang mengubah susunan validator & approval.');
        }

        if ($period->is_locked) {
            return back()->with('error', 'Tidak dapat mengubah susunan approval pada periode yang sudah terkunci.');
        }

        $request->validate([
            'validator_1_id' => 'required|uuid',
            'validator_2_id' => 'required|uuid',
            'approval_id'    => 'required|uuid',
        ], [
            'validator_1_id.required' => 'Pilih Validator 1.',
            'validator_2_id.required' => 'Pilih Validator 2.',
            'approval_id.required'    => 'Pilih Approval Paling Atas.',
        ]);

        $period->update([
            'validator_1_id' => $request->validator_1_id,
            'validator_2_id' => $request->validator_2_id,
            'approval_id'    => $request->approval_id,
        ]);

        $myProfile = $this->getCurrentProfile();
        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => 'draft',
            'role_label'        => 'Pembuat Draft',
            'user_id'           => Auth::id(),
            'karyawan_id'       => $myProfile ? $myProfile->id : null,
            'karyawan_name'     => $myProfile ? $myProfile->nama : Auth::user()->name,
            'action'            => 'submitted',
            'note'              => 'Susunan Validator & Approval diperbarui oleh Pembuat Draft.',
        ]);

        return back()->with('success', 'Susunan Validator & Approval untuk periode ' . $period->nama_periode . ' berhasil diperbarui!');
    }

    /**
     * Buka Kunci (Unlock) Khusus oleh Pembuat Draft
     */
    public function unlock(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        // Validasi: HANYA pembuat draft yang bisa membuka kunci
        if ($period->created_by != Auth::id()) {
            return back()->with('error', 'Akses ditolak! Hanya Pembuat Draft yang berhak membuka kunci periode ini.');
        }

        if (!$period->is_locked) {
            return back()->with('info', 'Periode ini memang belum dalam keadaan terkunci.');
        }

        $request->validate([
            'alasan_buka_kunci' => 'required|string|min:5',
        ], [
            'alasan_buka_kunci.required' => 'Mohon sertakan alasan pembukaan kunci.',
        ]);

        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;

        $period->update([
            'status'          => 'draft',
            'locked_at'       => null,
            'locked_by'       => null,
            'unlocked_reason' => $request->alasan_buka_kunci,
        ]);

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => 'unlocked',
            'role_label'        => 'Pembuat Draft',
            'user_id'           => Auth::id(),
            'karyawan_id'       => $myProfile ? $myProfile->id : null,
            'karyawan_name'     => $userName,
            'action'            => 'unlocked',
            'note'              => $request->alasan_buka_kunci,
        ]);

        return back()->with('warning', "Kunci pada periode {$period->nama_periode} telah berhasil DIBUKA oleh Pembuat Draft. Anda dapat membenahi data kembali.");
    }

    /**
     * Log Riwayat Approval
     */
    public function approvalHistory($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $approvals = PayrollPeriodApproval::where('payroll_period_id', $period->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success'   => true,
            'approvals' => $approvals,
        ]);
    }

    public function destroy($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->is_locked) {
            return back()->with('error', 'Tidak dapat menghapus periode yang berstatus terkunci (Locked). Buka kunci terlebih dahulu jika ingin menghapus.');
        }

        $nama = $period->nama_periode;
        $period->delete();

        return redirect()->route('admin.payroll.index')
            ->with('success', "Periode {$nama} berhasil dihapus.");
    }

    public function exportExcel($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $fileName = 'REKAP_PAYROLL_' . Str::slug($period->nama_periode) . '.xlsx';
        return Excel::download(new RekapPayrollExport($period), $fileName);
    }

    public function exportBank($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $fileName = 'REKAP_TRANSFER_BANK_' . Str::slug($period->nama_periode) . '.xlsx';
        return Excel::download(new RekapBankTransferExport($period), $fileName);
    }

    public function slipPdf($karyawanId)
    {
        $row = PayrollKaryawan::with(['period', 'pegawai'])->findOrFail($karyawanId);
        $period = $row->period;

        $pdf = Pdf::loadView('admin::payroll.slip_pdf', compact('row', 'period'))
            ->setPaper('A4', 'portrait');

        $fileName = 'SLIP_GAJI_' . Str::slug($row->nama) . '_' . Str::slug($period->nama_periode) . '.pdf';
        return $pdf->stream($fileName);
    }

    public function downloadAllSlip($id)
    {
        $period = PayrollPeriod::with('karyawans')->findOrFail($id);
        $karyawans = $period->karyawans;

        if ($karyawans->isEmpty()) {
            return back()->with('error', 'Tidak ada data pegawai pada periode ini.');
        }

        $zipFileName = 'SLIP_GAJI_ALL_' . Str::slug($period->nama_periode) . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($karyawans as $row) {
                $pdf = Pdf::loadView('admin::payroll.slip_pdf', compact('row', 'period'))
                    ->setPaper('A4', 'portrait');

                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row->nama);
                $pdfFileName = 'SLIP_' . ($row->nik ?: 'PEG') . '_' . $cleanName . '.pdf';

                $zip->addFromString($pdfFileName, $pdf->output());
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file arsip ZIP.');
    }

    public function getBulan()
    {
        return [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
