<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\RkatApprovalLog;
use App\Models\RkatPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RkatApprovalController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Daftar Pengajuan RKAT yang Menunggu Persetujuan
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $query = RkatPengajuan::with(['periode', 'unit', 'program', 'pic'])
            ->whereIn('status', ['Diajukan', 'Review'])
            ->orderBy('created_at', 'asc');

        if ($request->filled('level')) {
            $query->where('current_approval_level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'LIKE', "%{$search}%");
            });
        }

        $pendingApprovals = $query->paginate(15)->withQueryString();

        // History Approval yang telah diproses
        $processedHistory = RkatApprovalLog::with(['pengajuan.unit', 'user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin::rkat.approval.index', [
            'title' => 'Persetujuan (Approval) RKAT',
            'pendingApprovals' => $pendingApprovals,
            'processedHistory' => $processedHistory,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Halaman Review & Verifikasi Pengajuan RKAT
     */
    public function show($id)
    {
        $this->guard('approve', 'admin:rkat');

        $pengajuan = RkatPengajuan::with([
            'periode', 
            'unit', 
            'program', 
            'pic.unit', 
            'anggaranItems.akun', 
            'anggaranItems.sumberDana',
            'approvalLogs.user'
        ])->findOrFail($id);

        return view('admin::rkat.approval.review', [
            'title' => 'Review Persetujuan RKAT: ' . $pengajuan->nomor_pengajuan,
            'pengajuan' => $pengajuan,
        ]);
    }

    /**
     * Proses Persetujuan (Approve)
     */
    public function approve(Request $request, $id)
    {
        $this->guard('approve', 'admin:rkat');

        $pengajuan = RkatPengajuan::findOrFail($id);

        $request->validate([
            'nominal_disetujui' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $nominalDisetujui = $request->nominal_disetujui;
            $currentLevel = $pengajuan->current_approval_level ?: 'Kepala Unit';

            // Menentukan tahapan berikutnya berdasarkan Plafon Anggaran (Sesuai PDF hal. 11-12)
            // < 10 Juta: Kepala Unit -> Keuangan (Final)
            // 10 - 100 Juta: Kepala Unit -> Keuangan -> Wakil Rektor (Final)
            // > 100 Juta: Kepala Unit -> Keuangan -> Wakil Rektor -> Rektor (Final)
            $nextLevel = null;
            $isFinal = false;

            if ($currentLevel === 'Kepala Unit') {
                $nextLevel = 'Bagian Keuangan';
            } elseif ($currentLevel === 'Bagian Keuangan') {
                if ($nominalDisetujui < 10000000) {
                    $isFinal = true;
                } else {
                    $nextLevel = 'Wakil Rektor';
                }
            } elseif ($currentLevel === 'Wakil Rektor') {
                if ($nominalDisetujui <= 100000000) {
                    $isFinal = true;
                } else {
                    $nextLevel = 'Rektor';
                }
            } elseif ($currentLevel === 'Rektor') {
                $isFinal = true;
            } else {
                $isFinal = true;
            }

            // Catat Log Approval
            RkatApprovalLog::create([
                'pengajuan_id' => $pengajuan->id,
                'user_id' => Auth::id(),
                'level_jabatan' => $currentLevel,
                'action' => 'Setujui',
                'nominal_disetujui' => $nominalDisetujui,
                'catatan' => $request->catatan ?: 'Pengajuan telah disetujui pada tahapan ' . $currentLevel . '.',
            ]);

            if ($isFinal) {
                $pengajuan->update([
                    'status' => 'Disetujui',
                    'current_approval_level' => 'Selesai',
                    'total_anggaran_disetujui' => $nominalDisetujui,
                    'approved_at' => now(),
                ]);
                $msg = 'Pengajuan RKAT (' . $pengajuan->nomor_pengajuan . ') telah disetujui secara FINAL!';
            } else {
                $pengajuan->update([
                    'status' => 'Review',
                    'current_approval_level' => $nextLevel,
                    'total_anggaran_disetujui' => $nominalDisetujui,
                ]);
                $msg = 'Pengajuan RKAT disetujui dan diteruskan ke tahap: ' . $nextLevel . '.';
            }

            DB::commit();
            return redirect()->route('admin.rkat.approval.index')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses approval: ' . $e->getMessage());
        }
    }

    /**
     * Kembalikan untuk Revisi
     */
    public function revise(Request $request, $id)
    {
        $this->guard('approve', 'admin:rkat');

        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        $pengajuan = RkatPengajuan::findOrFail($id);

        DB::beginTransaction();
        try {
            RkatApprovalLog::create([
                'pengajuan_id' => $pengajuan->id,
                'user_id' => Auth::id(),
                'level_jabatan' => $pengajuan->current_approval_level ?: 'Reviewer',
                'action' => 'Revisi',
                'catatan' => $request->catatan,
            ]);

            $pengajuan->update([
                'status' => 'Revisi',
                'catatan_revisi' => $request->catatan,
            ]);

            DB::commit();
            return redirect()->route('admin.rkat.approval.index')->with('success', 'Pengajuan RKAT dikembalikan untuk revisi ke pengusul.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Tolak Pengajuan
     */
    public function reject(Request $request, $id)
    {
        $this->guard('approve', 'admin:rkat');

        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        $pengajuan = RkatPengajuan::findOrFail($id);

        DB::beginTransaction();
        try {
            RkatApprovalLog::create([
                'pengajuan_id' => $pengajuan->id,
                'user_id' => Auth::id(),
                'level_jabatan' => $pengajuan->current_approval_level ?: 'Reviewer',
                'action' => 'Tolak',
                'catatan' => $request->catatan,
            ]);

            $pengajuan->update([
                'status' => 'Ditolak',
                'catatan_revisi' => $request->catatan,
            ]);

            DB::commit();
            return redirect()->route('admin.rkat.approval.index')->with('success', 'Pengajuan RKAT telah ditolak.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
