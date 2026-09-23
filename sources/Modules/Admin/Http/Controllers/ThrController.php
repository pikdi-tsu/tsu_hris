<?php

namespace Modules\Admin\Http\Controllers;

use App\Exports\ThrExport;
use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\ThrKaryawan;
use App\Models\ThrPeriod;
use App\Models\ThrPeriodApproval;
use App\Models\User;
use App\Notifications\ThrApprovalNotification;
use App\Services\ThrCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;

class ThrController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    private function notifyKaryawanUser($karyawan, $notification)
    {
        if (!$karyawan) return;
        $user = null;
        if (!empty($karyawan->user_id)) {
            $user = User::find($karyawan->user_id);
        }
        if (!$user && !empty($karyawan->nik)) {
            $user = User::where('username', $karyawan->nik)->first();
        }
        if ($user) {
            try {
                $user->notify($notification);
            } catch (\Exception $e) {
                // Ignore safe notification failure
            }
        }
    }

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

    /**
     * Halaman Daftar Periode THR
     */
    public function index()
    {
        $periods = ThrPeriod::withCount('karyawans')
            ->orderByDesc('tahun')
            ->orderByDesc('created_at')
            ->get();

        $currentYear = date('Y');
        $stats = [
            'total_periods'  => $periods->count(),
            'total_locked'   => $periods->where('status', 'locked')->count(),
            'total_penerima' => $periods->sum('total_pegawai'),
            'total_anggaran' => $periods->where('tahun', $currentYear)->sum('total_anggaran_thr'),
        ];

        return view('admin::payroll.thr.index', [
            'title'       => 'Tunjangan Hari Raya (THR)',
            'menu'        => 'thr',
            'menuIcon'    => 'fas fa-gifts',
            'periods'     => $periods,
            'stats'       => $stats,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Modal Form Buat Periode THR Baru
     */
    public function createPeriodModal()
    {
        $karyawans = DataDosenTendik::with('unit')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('admin::payroll.thr.modal_create', compact('karyawans'));
    }

    /**
     * Simpan Periode Baru & Otomatis Hitung THR Seluruh Karyawan
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            'nama_periode'          => 'required|string|max:150',
            'tahun'                 => 'required|integer|min:2020|max:2099',
            'tanggal_cutoff'        => 'required|date',
            'tanggal_surat'         => 'required|date',
            'kota_surat'            => 'required|string|max:100',
            'penandatangan_nama'    => 'required|string|max:150',
            'penandatangan_jabatan' => 'required|string|max:150',
            'validator_1_id'        => 'required|uuid',
            'validator_2_id'        => 'nullable|uuid',
            'approval_id'           => 'required|uuid',
            'keterangan'            => 'nullable|string',
        ], [
            'validator_1_id.required' => 'Silahkan pilih pejabat Validator 1.',
            'approval_id.required'    => 'Silahkan pilih pejabat Approval (Paling Atas).',
        ]);

        try {
            DB::beginTransaction();

            $period = ThrPeriod::create([
                'nama_periode'          => $request->nama_periode,
                'tahun'                 => $request->tahun,
                'tanggal_cutoff'        => $request->tanggal_cutoff,
                'tanggal_surat'         => $request->tanggal_surat,
                'kota_surat'            => $request->kota_surat,
                'penandatangan_nama'    => $request->penandatangan_nama,
                'penandatangan_jabatan' => $request->penandatangan_jabatan,
                'validator_1_id'        => $request->validator_1_id,
                'validator_2_id'        => $request->validator_2_id ?: null,
                'approval_id'           => $request->approval_id,
                'keterangan'            => $request->keterangan,
                'status'                => 'draft',
                'created_by'            => Auth::id(),
            ]);

            // Catat log approval awal
            $myProfile = $this->getCurrentProfile();
            ThrPeriodApproval::create([
                'thr_period_id' => $period->id,
                'step'          => 'draft',
                'role_label'    => 'Pembuat Draft',
                'user_id'       => Auth::id(),
                'karyawan_id'   => $myProfile ? $myProfile->id : null,
                'karyawan_name' => $myProfile ? $myProfile->nama : Auth::user()->name,
                'action'        => 'submitted',
                'note'          => 'Periode THR dibuat dengan status Draft.',
            ]);

            // Hitung otomatis seluruh karyawan
            ThrCalculationService::calculatePeriod($period);

            DB::commit();

            return response()->json([
                'status'   => 'success',
                'message'  => "Periode THR {$period->nama_periode} berhasil dibuat dan dihitung.",
                'redirect' => route('admin.payroll.thr.show', $period->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat periode THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman Detail Periode THR & Rekap Penerima
     */
    public function show($id)
    {
        $period = ThrPeriod::with([
            'karyawans',
            'validator1',
            'validator2',
            'approvalKaryawan',
            'createdUser',
            'lockedUser',
            'approvals.user'
        ])->findOrFail($id);

        $karyawans = $period->karyawans;
        $stats = [
            'total_pegawai'    => $karyawans->count(),
            'total_penuh'      => $karyawans->where('status_thr', 'Penuh')->count(),
            'total_prorata'    => $karyawans->where('status_thr', 'Pro Rata')->count(),
            'total_upah_tetap' => $karyawans->sum('upah_tetap'),
            'total_anggaran'   => $karyawans->sum('total_thr'),
        ];

        $myProfile = $this->getCurrentProfile();
        $myKaryawanId = $myProfile ? $myProfile->id : null;

        $isCreator = ($period->created_by == Auth::id()) || Auth::user()->can('admin:payroll:edit');
        $isValidator1 = ($period->validator_1_id && $period->validator_1_id == $myKaryawanId) || Auth::user()->can('admin:payroll:edit');
        $isValidator2 = ($period->validator_2_id && $period->validator_2_id == $myKaryawanId) || Auth::user()->can('admin:payroll:edit');
        $isApproval   = ($period->approval_id && $period->approval_id == $myKaryawanId) || Auth::user()->can('admin:payroll:edit');

        $allKaryawan = DataDosenTendik::with('unit')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('admin::payroll.thr.show', [
            'title'        => 'Detail ' . $period->nama_periode,
            'menu'         => 'thr',
            'menuIcon'     => 'fas fa-gifts',
            'period'       => $period,
            'stats'        => $stats,
            'isCreator'    => $isCreator,
            'isValidator1' => $isValidator1,
            'isValidator2' => $isValidator2,
            'isApproval'   => $isApproval,
            'allKaryawan'  => $allKaryawan,
        ]);
    }

    /**
     * Pembuat mengajukan draft ke Validator 1
     */
    public function submitApproval(Request $request, $id)
    {
        $period = ThrPeriod::with('validator1')->findOrFail($id);

        if ($period->created_by != Auth::id() && !Auth::user()->can('admin:payroll:edit')) {
            return back()->with('error', 'Hanya pembuat draft yang berwenang mengajukan THR ke Validator.');
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

        ThrPeriodApproval::create([
            'thr_period_id' => $period->id,
            'step'          => 'validator_1',
            'role_label'    => 'Pembuat Draft',
            'user_id'       => Auth::id(),
            'karyawan_id'   => $myProfile ? $myProfile->id : null,
            'karyawan_name' => $myProfile ? $myProfile->nama : Auth::user()->name,
            'action'        => 'submitted',
            'note'          => $request->catatan_pengajuan ?: ($isResubmit ? 'Revisi telah dibenahi dan diajukan ulang ke Validator 1.' : 'Diajukan ke Validator 1 untuk pemeriksaan data.'),
        ]);

        // Kirim Notifikasi ke Validator 1
        $this->notifyKaryawanUser(
            $period->validator1,
            new ThrApprovalNotification(
                $period,
                "Periode THR {$period->nama_periode} telah diajukan oleh " . (Auth::user()->name) . ". Mohon lakukan pemeriksaan dan validasi data.",
                'validator_1',
                'Kroscek & Validasi'
            )
        );

        return back()->with('success', 'THR berhasil diajukan ke Validator 1 (' . ($period->validator1->nama ?? '-') . '). Menunggu verifikasi.');
    }

    /**
     * Aksi Persetujuan Bertingkat (Validator 1 -> Validator 2 -> Approval Paling Atas)
     */
    public function approveStep(Request $request, $id)
    {
        $period = ThrPeriod::with(['validator1', 'validator2', 'approvalKaryawan'])->findOrFail($id);
        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;
        $karyawanId = $myProfile ? $myProfile->id : null;

        $nextStatus = '';
        $stepName = '';
        $roleLabel = '';
        $pesanSukses = '';

        if ($period->status === 'pending_val_1') {
            if ($period->validator_2_id) {
                $nextStatus = 'pending_val_2';
                $pesanSukses = 'Verifikasi Tingkat 1 disetujui. Berkas diteruskan ke Validator 2 (' . ($period->validator2->nama ?? '-') . ').';
            } else {
                $nextStatus = 'pending_approval';
                $pesanSukses = 'Verifikasi Tingkat 1 disetujui. Berkas diteruskan ke Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? '-') . ').';
            }
            $stepName = 'validator_1';
            $roleLabel = 'Validator 1 (' . ($period->validator1->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_val_2') {
            $nextStatus = 'pending_approval';
            $stepName = 'validator_2';
            $roleLabel = 'Validator 2 (' . ($period->validator2->nama ?? $userName) . ')';
            $pesanSukses = 'Verifikasi Tingkat 2 disetujui. Berkas diteruskan ke Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? '-') . ').';
        } elseif ($period->status === 'pending_approval') {
            // Approval Paling Atas: SISTEM OTOMATIS MENGUNCI (LOCKED)
            $nextStatus = 'locked';
            $stepName = 'approval';
            $roleLabel = 'Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? $userName) . ')';
            $period->locked_at = now();
            $period->locked_by = Auth::id();
            $pesanSukses = 'Persetujuan Final berhasil diberikan! Sistem telah OTOMATIS MENGUNCI (LOCKED) periode THR ini.';
        } else {
            return back()->with('error', 'Status periode saat ini tidak membutuhkan persetujuan.');
        }

        $period->status = $nextStatus;
        $period->save();

        ThrPeriodApproval::create([
            'thr_period_id' => $period->id,
            'step'          => $stepName,
            'role_label'    => $roleLabel,
            'user_id'       => Auth::id(),
            'karyawan_id'   => $karyawanId,
            'karyawan_name' => $userName,
            'action'        => 'approved',
            'note'          => $request->note ?: 'Disetujui tanpa catatan.',
        ]);

        // Dispatch Notifikasi ke approver berikutnya
        if ($nextStatus === 'pending_val_2') {
            $this->notifyKaryawanUser(
                $period->validator2,
                new ThrApprovalNotification(
                    $period,
                    "Periode THR {$period->nama_periode} telah disetujui oleh Validator 1. Menunggu verifikasi Anda.",
                    'validator_2',
                    'Kroscek & Validasi'
                )
            );
        } elseif ($nextStatus === 'pending_approval') {
            $this->notifyKaryawanUser(
                $period->approvalKaryawan,
                new ThrApprovalNotification(
                    $period,
                    "Periode THR {$period->nama_periode} telah diverifikasi. Menunggu persetujuan final & penguncian Anda.",
                    'approval',
                    'Persetujuan Final & Lock'
                )
            );
        } elseif ($nextStatus === 'locked') {
            $this->notifyUserById(
                $period->created_by,
                new ThrApprovalNotification(
                    $period,
                    "Selamat! Periode THR {$period->nama_periode} telah disetujui secara final oleh " . ($period->approvalKaryawan->nama ?? 'Approval Paling Atas') . " dan resmi TERKUNCI.",
                    'locked',
                    'Lihat Rekap Final'
                )
            );
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

        $period = ThrPeriod::with(['validator1', 'validator2', 'approvalKaryawan'])->findOrFail($id);
        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;
        $karyawanId = $myProfile ? $myProfile->id : null;

        $stepName = '';
        $roleLabel = '';

        if ($period->status === 'pending_val_1') {
            $stepName = 'validator_1';
            $roleLabel = 'Validator 1 (' . ($period->validator1->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_val_2') {
            $stepName = 'validator_2';
            $roleLabel = 'Validator 2 (' . ($period->validator2->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_approval') {
            $stepName = 'approval';
            $roleLabel = 'Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? $userName) . ')';
        } else {
            return back()->with('error', 'Periode saat ini tidak sedang dalam tahapan approval.');
        }

        $period->status = 'revision_requested';
        $period->rejection_by_role = $roleLabel;
        $period->rejection_note = $request->catatan_revisi;
        $period->save();

        ThrPeriodApproval::create([
            'thr_period_id' => $period->id,
            'step'          => $stepName,
            'role_label'    => $roleLabel,
            'user_id'       => Auth::id(),
            'karyawan_id'   => $karyawanId,
            'karyawan_name' => $userName,
            'action'        => 'revision_requested',
            'note'          => $request->catatan_revisi,
        ]);

        // Notifikasi ke pembuat draft
        $this->notifyUserById(
            $period->created_by,
            new ThrApprovalNotification(
                $period,
                "Periode THR {$period->nama_periode} dikembalikan untuk revisi oleh {$roleLabel}. Catatan: \"{$request->catatan_revisi}\"",
                'revision',
                'Buka & Perbaiki'
            )
        );

        return back()->with('warning', "Catatan revisi berhasil dikirimkan ke Pembuat Draft. Status periode berubah menjadi 'Perlu Revisi'.");
    }

    /**
     * Buka Kunci (Unlock) Periode yang sudah terkunci
     */
    public function unlock(Request $request, $id)
    {
        $request->validate([
            'alasan_buka_kunci' => 'required|string|min:5',
        ], [
            'alasan_buka_kunci.required' => 'Mohon sertakan alasan resmi membuka kunci periode.',
            'alasan_buka_kunci.min'      => 'Alasan pembukaan kunci minimal 5 karakter.',
        ]);

        $period = ThrPeriod::findOrFail($id);

        if (!$period->is_locked) {
            return back()->with('error', 'Periode ini tidak sedang terkunci.');
        }

        if ($period->created_by != Auth::id() && !Auth::user()->can('admin:payroll:edit')) {
            return back()->with('error', 'Hanya pembuat draft atau administrator yang berwenang membuka kunci periode.');
        }

        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;

        $period->status = 'draft';
        $period->locked_at = null;
        $period->locked_by = null;
        $period->unlocked_reason = $request->alasan_buka_kunci;
        $period->save();

        ThrPeriodApproval::create([
            'thr_period_id' => $period->id,
            'step'          => 'unlocked',
            'role_label'    => 'Pembuat Draft',
            'user_id'       => Auth::id(),
            'karyawan_id'   => $myProfile ? $myProfile->id : null,
            'karyawan_name' => $userName,
            'action'        => 'unlocked',
            'note'          => 'Kunci periode dibuka: ' . $request->alasan_buka_kunci,
        ]);

        return back()->with('warning', "Kunci pada periode {$period->nama_periode} telah berhasil DIBUKA. Anda dapat membenahi data kembali.");
    }

    /**
     * Update susunan Validator 1, Validator 2, dan Approval
     */
    public function updateApprovers(Request $request, $id)
    {
        $period = ThrPeriod::findOrFail($id);

        if ($period->created_by != Auth::id() && !Auth::user()->can('admin:payroll:edit')) {
            return back()->with('error', 'Hanya pembuat draft yang berwenang mengubah susunan validator & approval.');
        }

        if ($period->is_locked) {
            return back()->with('error', 'Tidak dapat mengubah susunan approval pada periode yang sudah terkunci.');
        }

        $request->validate([
            'validator_1_id' => 'required|uuid',
            'validator_2_id' => 'nullable|uuid',
            'approval_id'    => 'required|uuid',
        ], [
            'validator_1_id.required' => 'Pilih Validator 1.',
            'approval_id.required'    => 'Pilih Approval Paling Atas.',
        ]);

        $period->update([
            'validator_1_id' => $request->validator_1_id,
            'validator_2_id' => $request->validator_2_id ?: null,
            'approval_id'    => $request->approval_id,
        ]);

        ThrPeriodApproval::create([
            'thr_period_id' => $period->id,
            'step'          => 'draft',
            'role_label'    => 'Pembuat Draft',
            'user_id'       => Auth::id(),
            'karyawan_id'   => $this->getCurrentProfile()?->id,
            'karyawan_name' => $this->getCurrentProfile()?->nama ?? Auth::user()->name,
            'action'        => 'submitted',
            'note'          => 'Susunan Validator & Approval diperbarui oleh Pembuat Draft.',
        ]);

        return back()->with('success', 'Susunan Validator & Approval untuk periode ' . $period->nama_periode . ' berhasil diperbarui!');
    }

    /**
     * Log Riwayat Approval (JSON untuk Modal)
     */
    public function approvalHistory($id)
    {
        $period = ThrPeriod::findOrFail($id);
        $approvals = ThrPeriodApproval::where('thr_period_id', $period->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success'   => true,
            'approvals' => $approvals,
        ]);
    }

    /**
     * DataTables Karyawan Penerima THR
     */
    public function datatable(Request $request, $id)
    {
        $period = ThrPeriod::findOrFail($id);

        $query = ThrKaryawan::where('thr_period_id', $period->id);

        if ($request->filled('filter_status')) {
            $query->where('status_thr', $request->filter_status);
        }

        if ($request->filled('filter_tipe')) {
            $query->where('tipe_karyawan', $request->filter_tipe);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $tipeBadge = $row->tipe_karyawan === 'Dosen'
                    ? '<span class="badge badge-primary px-2 py-1">Dosen</span>'
                    : '<span class="badge badge-info px-2 py-1">Tendik</span>';

                return '
                    <div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 36px; height: 36px; border-radius: 50%; background: #094b54; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">
                            ' . strtoupper(substr($row->nama, 0, 1)) . '
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">' . e($row->nama) . ' ' . $tipeBadge . '</div>
                            <div class="text-muted small">NIK: <strong>' . e($row->nik) . '</strong> | Unit: ' . e($row->nama_unit) . '</div>
                            <div class="text-muted small">Posisi: ' . e($row->posisi) . '</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('tgl_awal_format', function ($row) {
                if (!$row->tgl_awal_kerja) return '-';
                return Carbon::parse($row->tgl_awal_kerja)->format('d/m/Y');
            })
            ->addColumn('masa_kerja_formatted', function ($row) {
                return '<span class="font-weight-bold">' . $row->masa_kerja_formatted . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status_thr === 'Penuh') {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Penuh (100%)</span>';
                }
                return '<span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-percentage mr-1"></i>Pro Rata</span>';
            })
            ->addColumn('upah_tetap_format', function ($row) {
                return '<div class="text-right font-weight-bold">Rp ' . number_format($row->upah_tetap, 0, ',', '.') . '</div>';
            })
            ->addColumn('nominal_thr_format', function ($row) {
                return '<div class="text-right text-muted">Rp ' . number_format($row->nominal_thr, 0, ',', '.') . '</div>';
            })
            ->addColumn('penyesuaian_format', function ($row) {
                if ($row->penyesuaian == 0) {
                    return '<div class="text-center text-muted">-</div>';
                }
                $color = $row->penyesuaian > 0 ? 'text-success' : 'text-danger';
                $prefix = $row->penyesuaian > 0 ? '+Rp ' : '-Rp ';
                return '<div class="text-right font-weight-bold ' . $color . '">' . $prefix . number_format(abs($row->penyesuaian), 0, ',', '.') . '</div>';
            })
            ->addColumn('total_thr_format', function ($row) {
                return '<div class="text-right font-weight-bold text-success" style="font-size: 1.05rem;">Rp ' . number_format($row->total_thr, 0, ',', '.') . '</div>';
            })
            ->addColumn('action', function ($row) use ($period) {
                $btn = '<div class="btn-group" role="group">';
                
                // Tombol Cetak Slip PDF
                $pdfUrl = route('admin.payroll.thr.slip-pdf', $row->id);
                $btn .= '<a href="' . $pdfUrl . '" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Slip PDF" style="border-radius: 6px 0 0 6px;"><i class="fas fa-file-pdf"></i></a>';

                // Tombol Edit Penyesuaian (Jika periode belum di-lock)
                if (!$period->is_locked && in_array($period->status, ['draft', 'revision_requested'])) {
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-karyawan" data-id="' . $row->id . '" title="Edit Penyesuaian" style="border-radius: 0 6px 6px 0;"><i class="fas fa-pencil-alt"></i></button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary" disabled style="border-radius: 0 6px 6px 0;"><i class="fas fa-lock text-muted"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['pegawai_info', 'tgl_awal_format', 'masa_kerja_formatted', 'status_badge', 'upah_tetap_format', 'nominal_thr_format', 'penyesuaian_format', 'total_thr_format', 'action'])
            ->make(true);
    }

    /**
     * Hitung Ulang Periode THR
     */
    public function recalculate($id)
    {
        $period = ThrPeriod::findOrFail($id);

        if ($period->is_locked || !in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode tidak dapat dihitung ulang karena sedang dalam proses approval atau sudah terkunci.'
            ], 403);
        }

        try {
            ThrCalculationService::calculatePeriod($period);

            return response()->json([
                'status'  => 'success',
                'message' => 'Kalkulasi ulang THR seluruh pegawai berhasil diperbarui berdasarkan data master terkini.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghitung ulang THR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Form Modal Koreksi / Penyesuaian THR Karyawan
     */
    public function editKaryawan($id)
    {
        $karyawan = ThrKaryawan::with('period')->findOrFail($id);
        return view('admin::payroll.thr.modal_edit_karyawan', [
            'karyawan' => $karyawan
        ]);
    }

    /**
     * Simpan Koreksi Penyesuaian THR Karyawan
     */
    public function updateKaryawan(Request $request, $id)
    {
        $karyawan = ThrKaryawan::with('period')->findOrFail($id);

        if ($karyawan->period->is_locked || !in_array($karyawan->period->status, ['draft', 'revision_requested'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak dapat melakukan koreksi karena periode THR sedang dalam proses approval atau telah terkunci.'
            ], 403);
        }

        $request->validate([
            'penyesuaian' => 'required|numeric',
            'keterangan'  => 'nullable|string|max:255',
        ]);

        $penyesuaian = floatval($request->penyesuaian);
        $totalThr = max(0, floatval($karyawan->nominal_thr) + $penyesuaian);

        $karyawan->update([
            'penyesuaian' => $penyesuaian,
            'total_thr'   => $totalThr,
            'keterangan'  => $request->keterangan,
        ]);

        // Recalculate total anggaran periode
        $period = $karyawan->period;
        $period->update([
            'total_anggaran_thr' => $period->karyawans()->sum('total_thr'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Penyesuaian THR untuk {$karyawan->nama} berhasil disimpan.",
        ]);
    }

    /**
     * Buka / Kunci Periode THR
     */
    public function toggleLock($id)
    {
        $period = ThrPeriod::findOrFail($id);
        $newStatus = ($period->status === 'locked') ? 'draft' : 'locked';

        $period->update([
            'status'    => $newStatus,
            'locked_at' => ($newStatus === 'locked') ? now() : null,
            'locked_by' => ($newStatus === 'locked') ? Auth::id() : null,
        ]);

        $msg = ($newStatus === 'locked') ? 'Periode THR berhasil dikunci.' : 'Kunci periode THR berhasil dibuka.';

        return response()->json([
            'status'     => 'success',
            'message'    => $msg,
            'is_locked'  => ($newStatus === 'locked'),
            'status_val' => $newStatus,
        ]);
    }

    /**
     * Hapus Periode THR
     */
    public function destroy($id)
    {
        $period = ThrPeriod::findOrFail($id);

        if ($period->is_locked) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode yang berstatus terkunci tidak dapat dihapus. Buka kunci terlebih dahulu.',
            ], 403);
        }

        $nama = $period->nama_periode;
        $period->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Periode '{$nama}' beserta data rinciannya berhasil dihapus.",
        ]);
    }

    /**
     * Export Excel Rekap THR
     */
    public function exportExcel($id)
    {
        $period = ThrPeriod::findOrFail($id);
        $fileName = 'THR_' . Str::slug($period->nama_periode) . '.xlsx';
        return Excel::download(new ThrExport($period), $fileName);
    }

    /**
     * Cetak / Preview Slip PDF Resmi Karyawan
     */
    public function slipPdf($karyawanId)
    {
        $karyawan = ThrKaryawan::with('period')->findOrFail($karyawanId);
        $period = $karyawan->period;

        $pdf = Pdf::loadView('admin::payroll.thr.slip_pdf', compact('karyawan', 'period'))
            ->setPaper('A4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $karyawan->nama);
        $fileName = 'SLIP_THR_' . ($karyawan->nik ?: 'PEG') . '_' . $cleanName . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Download Semua Slip PDF dalam ZIP
     */
    public function downloadAllSlipZip($id)
    {
        $period = ThrPeriod::with('karyawans')->findOrFail($id);
        $karyawans = $period->karyawans;

        if ($karyawans->isEmpty()) {
            return back()->with('error', 'Tidak ada data pegawai pada periode ini.');
        }

        $zipFileName = 'ALL_SLIP_THR_' . Str::slug($period->nama_periode) . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($karyawans as $karyawan) {
                $pdf = Pdf::loadView('admin::payroll.thr.slip_pdf', compact('karyawan', 'period'))
                    ->setPaper('A4', 'portrait');

                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $karyawan->nama);
                $pdfFileName = 'SLIP_THR_' . ($karyawan->nik ?: 'PEG') . '_' . $cleanName . '.pdf';

                $zip->addFromString($pdfFileName, $pdf->output());
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file arsip ZIP.');
    }
}
