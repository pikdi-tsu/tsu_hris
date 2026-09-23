<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\RkatAkun;
use App\Models\RkatAnggaranItem;
use App\Models\RkatApprovalLog;
use App\Models\RkatIndikator;
use App\Models\RkatKegiatanMaster;
use App\Models\RkatPengajuan;
use App\Models\RkatPeriode;
use App\Models\RkatProgram;
use App\Models\RkatSumberDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RkatPengajuanController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Tampilan Daftar Seluruh Pengajuan RKAT
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $query = RkatPengajuan::with(['periode', 'unit', 'program', 'pic'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'LIKE', "%{$search}%")
                  ->orWhere('sasaran_strategis', 'LIKE', "%{$search}%");
            });
        }

        $pengajuans = $query->paginate(15)->withQueryString();

        $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        $units = MasterUnit::orderBy('nama_unit')->get();
        $programs = RkatProgram::where('is_active', 1)->orderBy('kode_program')->get();

        // Summary Statistics
        $allPengajuans = RkatPengajuan::when($request->filled('periode_id'), function ($q) use ($request) {
            $q->where('periode_id', $request->periode_id);
        })->get();

        $stats = [
            'total_pengajuan' => $allPengajuans->count(),
            'total_nominal_diajukan' => $allPengajuans->sum('total_anggaran_diajukan'),
            'total_nominal_disetujui' => $allPengajuans->sum('total_anggaran_disetujui'),
            'count_draft' => $allPengajuans->where('status', 'Draft')->count(),
            'count_diajukan' => $allPengajuans->where('status', 'Diajukan')->count(),
            'count_review' => $allPengajuans->where('status', 'Review')->count(),
            'count_disetujui' => $allPengajuans->where('status', 'Disetujui')->count(),
            'count_revisi' => $allPengajuans->where('status', 'Revisi')->count(),
            'count_ditolak' => $allPengajuans->where('status', 'Ditolak')->count(),
        ];

        return view('admin::rkat.pengajuan.index', [
            'title' => 'Pengajuan RKAT',
            'pengajuans' => $pengajuans,
            'periodes' => $periodes,
            'units' => $units,
            'programs' => $programs,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Tampilan Multi-Step Wizard Form Pengajuan RKAT Baru
     */
    public function create()
    {
        $this->guard('create', 'admin:rkat');

        $periodes = RkatPeriode::active()->orderBy('tahun_anggaran', 'desc')->get();
        if ($periodes->isEmpty()) {
            $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        }

        $units = MasterUnit::orderBy('nama_unit')->get();
        $programs = RkatProgram::where('is_active', 1)->orderBy('kode_program')->get();
        $kegiatans = RkatKegiatanMaster::where('is_active', 1)->orderBy('nama_kegiatan')->get();
        $akuns = RkatAkun::where('is_active', 1)->orderBy('kode_akun')->get();
        $sumberDanas = RkatSumberDana::where('is_active', 1)->orderBy('kode')->get();
        $indikators = RkatIndikator::where('is_active', 1)->orderBy('nama_indikator')->get();
        $pics = DataDosenTendik::where('is_active', 1)->orderBy('nama')->get();

        return view('admin::rkat.pengajuan.wizard', [
            'title' => 'Form Pengajuan RKAT Baru',
            'periodes' => $periodes,
            'units' => $units,
            'programs' => $programs,
            'kegiatans' => $kegiatans,
            'akuns' => $akuns,
            'sumberDanas' => $sumberDanas,
            'indikators' => $indikators,
            'pics' => $pics,
        ]);
    }

    /**
     * Simpan Data Pengajuan RKAT (Bisa Draft atau Langsung Ajukan)
     */
    public function store(Request $request)
    {
        $this->guard('create', 'admin:rkat');

        $request->validate([
            'periode_id' => 'required|uuid|exists:rkat_periodes,id',
            'unit_id' => 'required|uuid|exists:master_units,id',
            'program_id' => 'required|uuid|exists:rkat_programs,id',
            'pic_id' => 'required|uuid|exists:data_dosen_tendiks,id',
            'nama_kegiatan' => 'required|string|max:255',
            'target_kuantitas' => 'required|integer|min:1',
            'satuan_target' => 'required|string|max:50',
            'periode_pelaksanaan_mulai' => 'nullable|date',
            'periode_pelaksanaan_selesai' => 'nullable|date|after_or_equal:periode_pelaksanaan_mulai',
            'is_draft' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.komponen_biaya' => 'required|string|max:255',
            'items.*.kuantitas' => 'required|integer|min:1',
            'items.*.satuan' => 'required|string|max:50',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $periode = RkatPeriode::findOrFail($request->periode_id);
            $unit = MasterUnit::findOrFail($request->unit_id);

            // Generate nomor pengajuan otomatis: RKAT/{TAHUN}/{KODE_UNIT}/{URUTAN}
            $unitCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $unit->nama_unit), 0, 4)) ?: 'UNIT';
            $countThisYear = RkatPengajuan::where('periode_id', $periode->id)->count() + 1;
            $nomorPengajuan = sprintf("RKAT/%s/%s/%03d", $periode->tahun_anggaran, $unitCode, $countThisYear);

            // Hitung total anggaran dari items
            $totalAnggaran = 0;
            foreach ($request->items as $item) {
                $totalAnggaran += ($item['kuantitas'] * $item['harga_satuan']);
            }

            $isDraft = $request->boolean('is_draft');
            $status = $isDraft ? 'Draft' : 'Diajukan';

            $pengajuan = RkatPengajuan::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'periode_id' => $request->periode_id,
                'unit_id' => $request->unit_id,
                'program_id' => $request->program_id,
                'pic_id' => $request->pic_id,
                'sasaran_strategis' => $request->sasaran_strategis,
                'nama_kegiatan' => $request->nama_kegiatan,
                'latar_belakang' => $request->latar_belakang,
                'tujuan' => $request->tujuan,
                'sasaran' => $request->sasaran,
                'indikator_keberhasilan' => $request->indikator_keberhasilan,
                'target_kuantitas' => $request->target_kuantitas,
                'satuan_target' => $request->satuan_target,
                'output' => $request->output,
                'outcome' => $request->outcome,
                'periode_pelaksanaan_mulai' => $request->filled('periode_pelaksanaan_mulai') ? $request->periode_pelaksanaan_mulai : null,
                'periode_pelaksanaan_selesai' => $request->filled('periode_pelaksanaan_selesai') ? $request->periode_pelaksanaan_selesai : null,
                'total_anggaran_diajukan' => $totalAnggaran,
                'total_anggaran_disetujui' => $isDraft ? 0 : $totalAnggaran, // Default disetujui sama dengan diajukan hingga reviewer menyesuaikan
                'status' => $status,
                'current_approval_level' => $isDraft ? null : 'Kepala Unit',
                'submitted_at' => $isDraft ? null : now(),
            ]);

            // Simpan rincian komponen anggaran
            foreach ($request->items as $item) {
                $subtotal = $item['kuantitas'] * $item['harga_satuan'];
                RkatAnggaranItem::create([
                    'pengajuan_id' => $pengajuan->id,
                    'komponen_biaya' => $item['komponen_biaya'],
                    'kuantitas' => $item['kuantitas'],
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'total_biaya' => $subtotal,
                    'akun_id' => $item['akun_id'] ?? null,
                    'sumber_dana_id' => $item['sumber_dana_id'] ?? null,
                ]);
            }

            // Catat log approval jika langsung diajukan
            if (!$isDraft) {
                RkatApprovalLog::create([
                    'pengajuan_id' => $pengajuan->id,
                    'user_id' => Auth::id(),
                    'level_jabatan' => 'PIC Pengusul',
                    'action' => 'Diajukan',
                    'nominal_disetujui' => $totalAnggaran,
                    'catatan' => 'Pengajuan RKAT berhasil disubmit untuk proses persetujuan.',
                ]);
            }

            DB::commit();

            $msg = $isDraft 
                ? 'Draft Pengajuan RKAT berhasil disimpan!' 
                : 'Pengajuan RKAT (' . $nomorPengajuan . ') berhasil diajukan untuk proses approval!';

            return redirect()->route('admin.rkat.pengajuan.show', $pengajuan->id)->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan pengajuan RKAT: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Detail Pengajuan RKAT (Review, Rincian, Riwayat Approval)
     */
    public function show($id)
    {
        $this->guard('view', 'admin:rkat');

        $pengajuan = RkatPengajuan::with([
            'periode', 
            'unit', 
            'program', 
            'pic.unit', 
            'anggaranItems.akun', 
            'anggaranItems.sumberDana', 
            'approvalLogs.user',
            'realisasis'
        ])->findOrFail($id);

        return view('admin::rkat.pengajuan.show', [
            'title' => 'Detail Pengajuan RKAT: ' . $pengajuan->nomor_pengajuan,
            'pengajuan' => $pengajuan,
        ]);
    }

    /**
     * Tampilan Formulir Edit / Revisi Pengajuan RKAT
     */
    public function edit($id)
    {
        $this->guard('edit', 'admin:rkat');

        $pengajuan = RkatPengajuan::with(['anggaranItems', 'approvalLogs'])->findOrFail($id);

        if (!in_array($pengajuan->status, ['Draft', 'Revisi'])) {
            return redirect()->route('admin.rkat.pengajuan.show', $pengajuan->id)
                ->with('error', 'Pengajuan dengan status ' . $pengajuan->status . ' tidak dapat diedit/direvisi.');
        }

        $periodes = RkatPeriode::active()->orderBy('tahun_anggaran', 'desc')->get();
        if ($periodes->isEmpty()) {
            $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        }

        $units = MasterUnit::orderBy('nama_unit')->get();
        $programs = RkatProgram::where('is_active', 1)->orderBy('kode_program')->get();
        $kegiatans = RkatKegiatanMaster::where('is_active', 1)->orderBy('nama_kegiatan')->get();
        $akuns = RkatAkun::where('is_active', 1)->orderBy('kode_akun')->get();
        $sumberDanas = RkatSumberDana::where('is_active', 1)->orderBy('kode')->get();
        $indikators = RkatIndikator::where('is_active', 1)->orderBy('nama_indikator')->get();
        $pics = DataDosenTendik::where('is_active', 1)->orderBy('nama')->get();

        $latestRevisionLog = $pengajuan->approvalLogs->where('action', 'Revisi')->sortByDesc('created_at')->first();

        return view('admin::rkat.pengajuan.wizard', [
            'title' => ($pengajuan->status == 'Revisi' ? 'Revisi Usulan RKAT: ' : 'Edit Usulan RKAT: ') . $pengajuan->nomor_pengajuan,
            'pengajuan' => $pengajuan,
            'latestRevisionLog' => $latestRevisionLog,
            'periodes' => $periodes,
            'units' => $units,
            'programs' => $programs,
            'kegiatans' => $kegiatans,
            'akuns' => $akuns,
            'sumberDanas' => $sumberDanas,
            'indikators' => $indikators,
            'pics' => $pics,
        ]);
    }

    /**
     * Simpan Perubahan Pengajuan RKAT (Draft atau Langsung Diajukan Kembali)
     */
    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');

        $request->validate([
            'periode_id' => 'required|uuid|exists:rkat_periodes,id',
            'unit_id' => 'required|uuid|exists:master_units,id',
            'program_id' => 'required|uuid|exists:rkat_programs,id',
            'pic_id' => 'required|uuid|exists:data_dosen_tendiks,id',
            'nama_kegiatan' => 'required|string|max:255',
            'target_kuantitas' => 'required|integer|min:1',
            'satuan_target' => 'required|string|max:50',
            'periode_pelaksanaan_mulai' => 'nullable|date',
            'periode_pelaksanaan_selesai' => 'nullable|date|after_or_equal:periode_pelaksanaan_mulai',
            'is_draft' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.komponen_biaya' => 'required|string|max:255',
            'items.*.kuantitas' => 'required|integer|min:1',
            'items.*.satuan' => 'required|string|max:50',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = RkatPengajuan::findOrFail($id);

            if (!in_array($pengajuan->status, ['Draft', 'Revisi'])) {
                return back()->with('error', 'Pengajuan dengan status ' . $pengajuan->status . ' tidak dapat diedit/direvisi.');
            }

            // Hitung total anggaran dari items baru
            $totalAnggaran = 0;
            foreach ($request->items as $item) {
                $totalAnggaran += ($item['kuantitas'] * $item['harga_satuan']);
            }

            $isDraft = $request->boolean('is_draft');
            $originalStatus = $pengajuan->status;
            
            if ($isDraft) {
                $newStatus = $originalStatus; // Tetap Draft atau Revisi
                $currentApprovalLevel = $pengajuan->current_approval_level;
                $submittedAt = $pengajuan->submitted_at;
            } else {
                $newStatus = 'Diajukan';
                $currentApprovalLevel = 'Kepala Unit';
                $submittedAt = now();
            }

            $pengajuan->update([
                'periode_id' => $request->periode_id,
                'unit_id' => $request->unit_id,
                'program_id' => $request->program_id,
                'pic_id' => $request->pic_id,
                'sasaran_strategis' => $request->sasaran_strategis,
                'nama_kegiatan' => $request->nama_kegiatan,
                'latar_belakang' => $request->latar_belakang,
                'tujuan' => $request->tujuan,
                'sasaran' => $request->sasaran,
                'indikator_keberhasilan' => $request->indikator_keberhasilan,
                'target_kuantitas' => $request->target_kuantitas,
                'satuan_target' => $request->satuan_target,
                'output' => $request->output,
                'outcome' => $request->outcome,
                'periode_pelaksanaan_mulai' => $request->filled('periode_pelaksanaan_mulai') ? $request->periode_pelaksanaan_mulai : null,
                'periode_pelaksanaan_selesai' => $request->filled('periode_pelaksanaan_selesai') ? $request->periode_pelaksanaan_selesai : null,
                'total_anggaran_diajukan' => $totalAnggaran,
                'total_anggaran_disetujui' => $isDraft ? $pengajuan->total_anggaran_disetujui : $totalAnggaran,
                'status' => $newStatus,
                'current_approval_level' => $currentApprovalLevel,
                'submitted_at' => $submittedAt,
            ]);

            // Hapus items lama dan ganti dengan yang baru
            RkatAnggaranItem::where('pengajuan_id', $pengajuan->id)->delete();
            foreach ($request->items as $item) {
                $subtotal = $item['kuantitas'] * $item['harga_satuan'];
                RkatAnggaranItem::create([
                    'pengajuan_id' => $pengajuan->id,
                    'komponen_biaya' => $item['komponen_biaya'],
                    'kuantitas' => $item['kuantitas'],
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'total_biaya' => $subtotal,
                    'akun_id' => $item['akun_id'] ?? null,
                    'sumber_dana_id' => $item['sumber_dana_id'] ?? null,
                ]);
            }

            // Catat log jika langsung diajukan kembali ke approval
            if (!$isDraft) {
                RkatApprovalLog::create([
                    'pengajuan_id' => $pengajuan->id,
                    'user_id' => Auth::id(),
                    'level_jabatan' => 'PIC Pengusul',
                    'action' => 'Diajukan',
                    'nominal_disetujui' => $totalAnggaran,
                    'catatan' => $originalStatus === 'Revisi' 
                        ? 'Pengusul telah menyelesaikan revisi usulan kegiatan dan mengajukan kembali untuk approval.' 
                        : 'Pengajuan RKAT (draf diperbarui) resmi diajukan untuk proses approval.',
                ]);
            }

            DB::commit();

            $msg = $isDraft 
                ? 'Perubahan draf usulan RKAT (' . $pengajuan->nomor_pengajuan . ') berhasil disimpan!' 
                : 'Pengajuan RKAT (' . $pengajuan->nomor_pengajuan . ') berhasil diperbarui dan diajukan ke alur approval!';

            return redirect()->route('admin.rkat.pengajuan.show', $pengajuan->id)->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui pengajuan RKAT: ' . $e->getMessage());
        }
    }

    /**
     * Ajukan Pengajuan RKAT yang berstatus Draft / Revisi ke Alur Approval
     */
    public function submit($id)
    {
        $this->guard('create', 'admin:rkat');

        DB::beginTransaction();
        try {
            $pengajuan = RkatPengajuan::with('anggaranItems')->findOrFail($id);

            if (!in_array($pengajuan->status, ['Draft', 'Revisi'])) {
                return back()->with('error', 'Pengajuan dengan status ' . $pengajuan->status . ' tidak dapat diajukan kembali.');
            }

            if ($pengajuan->anggaranItems->isEmpty()) {
                return back()->with('error', 'Pengajuan belum memiliki rincian komponen anggaran biaya.');
            }

            $pengajuan->update([
                'status' => 'Diajukan',
                'current_approval_level' => 'Kepala Unit',
                'submitted_at' => now(),
                'total_anggaran_disetujui' => $pengajuan->total_anggaran_diajukan,
            ]);

            RkatApprovalLog::create([
                'pengajuan_id' => $pengajuan->id,
                'user_id' => Auth::id(),
                'level_jabatan' => 'PIC Pengusul',
                'action' => 'Diajukan',
                'nominal_disetujui' => $pengajuan->total_anggaran_diajukan,
                'catatan' => 'Pengajuan RKAT (sebelumnya berstatus ' . $pengajuan->getOriginal('status') . ') berhasil diajukan untuk proses approval.',
            ]);

            DB::commit();

            return redirect()->route('admin.rkat.pengajuan.show', $pengajuan->id)
                ->with('success', 'Pengajuan RKAT (' . $pengajuan->nomor_pengajuan . ') berhasil diajukan untuk proses approval!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan RKAT: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Pengajuan RKAT (hanya jika masih Draft)
     */
    public function destroy($id)
    {
        $this->guard('delete', 'admin:rkat');

        try {
            $pengajuan = RkatPengajuan::findOrFail($id);
            if (!in_array($pengajuan->status, ['Draft', 'Revisi'])) {
                return back()->with('error', 'Pengajuan dengan status ' . $pengajuan->status . ' tidak dapat dihapus.');
            }

            $pengajuan->delete();
            return redirect()->route('admin.rkat.pengajuan.index')->with('success', 'Pengajuan RKAT berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
