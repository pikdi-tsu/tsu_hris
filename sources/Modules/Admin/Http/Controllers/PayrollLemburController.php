<?php

namespace Modules\Admin\Http\Controllers;

use App\Exports\PayrollLemburExport;
use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\MasterTandaTangan;
use App\Models\PayrollLemburDetail;
use App\Models\PayrollLemburKaryawan;
use App\Models\PayrollLemburPeriod;
use App\Models\PayrollLemburPeriodApproval;
use App\Services\PayrollLemburCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;

class PayrollLemburController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:payroll-lembur');
    }

    /**
     * Halaman Utama: Daftar Periode Payroll Lembur
     */
    public function index()
    {
        $periods = PayrollLemburPeriod::with(['creator', 'validator1', 'validator2', 'approvalKaryawan'])
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $currentYear = now()->year;
        $totalAnggaranTahun = PayrollLemburPeriod::where('tahun', $currentYear)->sum('total_anggaran_lembur');
        $totalJamTahun = PayrollLemburPeriod::where('tahun', $currentYear)->sum('total_jam_lembur');
        $totalPegawaiTahun = PayrollLemburPeriod::where('tahun', $currentYear)->sum('total_pegawai');
        $totalPeriodCount = $periods->count();

        return view('admin::payroll-lembur.index', [
            'title'              => 'Payroll Lembur Karyawan & Tendik',
            'menuIcon'           => 'fas fa-business-time',
            'periods'            => $periods,
            'currentYear'        => $currentYear,
            'totalAnggaranTahun' => $totalAnggaranTahun,
            'totalJamTahun'      => $totalJamTahun,
            'totalPegawaiTahun'  => $totalPegawaiTahun,
            'totalPeriodCount'   => $totalPeriodCount,
        ]);
    }

    /**
     * Modal Tambah Periode Baru
     */
    public function createPeriodModal()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $karyawans = DataDosenTendik::where('is_active', 1)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'nik', 'posisi', 'tipe_karyawan']);

        // Default pejabat penandatangan SDM dari Master Tanda Tangan
        $ttdDefault = MasterTandaTangan::where('is_active', 1)
            ->whereIn('peruntukan', ['semua', 'slip_lembur', 'slip_gaji', 'surat_sdm'])
            ->first();

        $masterTtdList = MasterTandaTangan::where('is_active', 1)->get()->keyBy('karyawan_id');

        return view('admin::payroll-lembur.modal_create_period', [
            'currentMonth'  => $currentMonth,
            'currentYear'   => $currentYear,
            'karyawans'     => $karyawans,
            'masterTtdList' => $masterTtdList,
            'ttdDefault'    => $ttdDefault,
        ]);
    }

    /**
     * Simpan Periode Baru & Generate Kalkulasi
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            'nama_periode'          => 'required|string|max:150',
            'bulan'                 => 'required|integer|between:1,12',
            'tahun'                 => 'required|integer|between:2020,2035',
            'start_date_cutoff'     => 'required|date',
            'end_date_cutoff'       => 'required|date|after_or_equal:start_date_cutoff',
            'tanggal_surat'         => 'nullable|date',
            'kota_surat'            => 'nullable|string|max:100',
            'karyawan_ttd_id'       => 'nullable|string',
            'penandatangan_nama'    => 'required|string|max:150',
            'penandatangan_jabatan' => 'required|string|max:150',
            'validator_1_id'        => 'nullable|exists:data_dosen_tendiks,id',
            'validator_2_id'        => 'nullable|exists:data_dosen_tendiks,id',
            'approval_id'           => 'nullable|exists:data_dosen_tendiks,id',
            'keterangan'            => 'nullable|string',
        ]);

        $period = PayrollLemburPeriod::create([
            'nama_periode'          => $request->nama_periode,
            'bulan'                 => $request->bulan,
            'tahun'                 => $request->tahun,
            'start_date_cutoff'     => $request->start_date_cutoff,
            'end_date_cutoff'       => $request->end_date_cutoff,
            'tanggal_surat'         => $request->tanggal_surat ?: now()->format('Y-m-d'),
            'kota_surat'            => $request->kota_surat ?: 'Surakarta',
            'karyawan_ttd_id'       => $request->karyawan_ttd_id,
            'penandatangan_nama'    => $request->penandatangan_nama,
            'penandatangan_jabatan' => $request->penandatangan_jabatan,
            'validator_1_id'        => $request->validator_1_id,
            'validator_2_id'        => $request->validator_2_id,
            'approval_id'           => $request->approval_id,
            'status'                => 'draft',
            'keterangan'            => $request->keterangan,
            'created_by'            => Auth::id(),
        ]);

        // Catat approval log awal
        PayrollLemburPeriodApproval::create([
            'payroll_lembur_period_id' => $period->id,
            'step'                     => 'draft',
            'role_label'               => 'Pembuat Draft',
            'user_id'                  => Auth::id(),
            'karyawan_id'              => Auth::user()->karyawan->id ?? null,
            'karyawan_name'            => Auth::user()->name,
            'action'                   => 'created',
            'note'                     => 'Periode baru dibuat oleh ' . Auth::user()->name,
        ]);

        // Generate data otomatis dari lembur yang approved
        PayrollLemburCalculationService::generateOrRefreshPeriod($period);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Periode lembur berhasil dibuat dan data lembur otomatis ditarik!',
            'redirect' => route('admin.payroll.lembur.show', $period->id),
        ]);
    }

    /**
     * Halaman Detail Periode: Rincian Karyawan Penerima Lembur
     */
    public function show($id)
    {
        $period = PayrollLemburPeriod::with(['creator', 'validator1', 'validator2', 'approvalKaryawan'])
            ->findOrFail($id);

        $karyawans = DataDosenTendik::where('is_active', 1)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'nik', 'posisi', 'tipe_karyawan']);

        $summary = [
            'total_pegawai'         => $period->total_pegawai,
            'total_jam_lembur'      => $period->total_jam_lembur,
            'total_anggaran_lembur' => $period->total_anggaran_lembur,
            'jam_kerja_biasa'       => $period->karyawans()->sum('jam_kerja_biasa'),
            'jam_hari_libur'        => $period->karyawans()->sum('jam_hari_libur'),
        ];

        return view('admin::payroll-lembur.show', [
            'title'     => 'Detail ' . $period->nama_periode,
            'menuIcon'  => 'fas fa-business-time',
            'period'    => $period,
            'summary'   => $summary,
            'karyawans' => $karyawans,
        ]);
    }

    /**
     * Yajra DataTables Karyawan dalam Periode
     */
    public function datatable(Request $request, $id)
    {
        $period = PayrollLemburPeriod::findOrFail($id);
        $query = PayrollLemburKaryawan::where('payroll_lembur_period_id', $period->id);

        if ($request->filled('filter_tipe') && $request->filter_tipe !== 'all') {
            $query->where('tipe_karyawan', $request->filter_tipe);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $badge = ($row->tipe_karyawan === 'Dosen') ? 'badge-primary' : 'badge-info';
                $html = '<div class="d-flex align-items-center">';
                $html .= '<div class="mr-2 rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 36px; height: 36px; background: #094b54; font-size: 0.85rem; flex-shrink: 0;">';
                $html .= strtoupper(substr($row->nama, 0, 1));
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<div class="font-weight-bold text-dark">' . e($row->nama) . ' <span class="badge ' . $badge . ' px-2 py-0 ml-1" style="font-size: 0.72rem;">' . e($row->tipe_karyawan) . '</span></div>';
                $sub = [];
                if (!empty($row->nik)) $sub[] = 'NIK: ' . e($row->nik);
                if (!empty($row->nama_unit)) $sub[] = e($row->nama_unit);
                if (!empty($row->posisi)) $sub[] = e($row->posisi);
                $html .= '<small class="text-muted">' . implode(' &bull; ', $sub) . '</small>';
                $html .= '</div></div>';
                return $html;
            })
            ->addColumn('gaji_tetap_format', function ($row) {
                return '<div class="text-right font-weight-600">Rp ' . number_format($row->gaji_tetap, 0, ',', '.') . '</div>';
            })
            ->addColumn('upah_jam_format', function ($row) {
                return '<div class="text-right text-muted small">Rp ' . number_format($row->upah_per_jam, 0, ',', '.') . '</div>';
            })
            ->addColumn('jam_lembur_info', function ($row) {
                $html = '<div class="text-center">';
                $html .= '<span class="font-weight-bold text-primary" style="font-size: 0.95rem;">' . number_format($row->total_jam_lembur, 1) . ' Jam</span>';
                $html .= '<div class="small text-muted">' . $row->total_hari_lembur . ' Hari (Kerja: ' . number_format($row->jam_kerja_biasa, 1) . ' | Libur: ' . number_format($row->jam_hari_libur, 1) . ')</div>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('nominal_sistem_format', function ($row) {
                return '<div class="text-right text-muted">Rp ' . number_format($row->nominal_lembur_sistem, 0, ',', '.') . '</div>';
            })
            ->addColumn('penyesuaian_format', function ($row) {
                if ($row->penyesuaian == 0) {
                    return '<div class="text-center text-muted">-</div>';
                }
                $cls = ($row->penyesuaian > 0) ? 'text-success' : 'text-danger';
                $sign = ($row->penyesuaian > 0) ? '+' : '';
                return '<div class="text-right font-weight-bold ' . $cls . '">' . $sign . 'Rp ' . number_format($row->penyesuaian, 0, ',', '.') . '</div>';
            })
            ->addColumn('total_final_format', function ($row) {
                return '<div class="text-right font-weight-bold text-success" style="font-size: 0.95rem;">Rp ' . number_format($row->total_lembur_final, 0, ',', '.') . '</div>';
            })
            ->addColumn('bank_info', function ($row) {
                if (!$row->no_rekening) {
                    return '<div class="text-center text-muted small">-</div>';
                }
                return '<div class="small"><strong>' . e($row->nama_bank ?: 'Bank') . '</strong>: ' . e($row->no_rekening) . '<br><span class="text-muted">a.n. ' . e($row->atas_nama_rekening ?: $row->nama) . '</span></div>';
            })
            ->addColumn('action', function ($row) use ($period) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-detail-lembur" data-id="' . $row->id . '" title="Lihat Rincian Harian"><i class="fas fa-list-ul"></i></button>';
                if (!$period->is_locked && in_array($period->status, ['draft', 'revision_requested'])) {
                    $btn .= '<button type="button" class="btn btn-outline-warning btn-edit-karyawan" data-id="' . $row->id . '" title="Koreksi Nominal"><i class="fas fa-pencil-alt"></i></button>';
                }
                $btn .= '<a href="' . route('admin.payroll.lembur.slip-pdf', $row->id) . '" target="_blank" class="btn btn-outline-primary" title="Cetak Slip PDF"><i class="fas fa-file-pdf"></i></a>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['pegawai_info', 'gaji_tetap_format', 'upah_jam_format', 'jam_lembur_info', 'nominal_sistem_format', 'penyesuaian_format', 'total_final_format', 'bank_info', 'action'])
            ->make(true);
    }

    /**
     * Modal Detail Hari Lembur Karyawan (Tampilan Tabel sesuai Slip)
     */
    public function detailModal($karyawanId)
    {
        $karyawan = PayrollLemburKaryawan::with(['period', 'details'])->findOrFail($karyawanId);

        return view('admin::payroll-lembur.modal_detail_lembur', [
            'karyawan' => $karyawan,
            'details'  => $karyawan->details,
        ]);
    }

    /**
     * Hitung Ulang Periode Lembur
     */
    public function recalculate($id)
    {
        $period = PayrollLemburPeriod::findOrFail($id);

        if ($period->is_locked || !in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode tidak dapat dihitung ulang karena sedang dalam proses approval atau sudah terkunci.'
            ], 403);
        }

        try {
            PayrollLemburCalculationService::generateOrRefreshPeriod($period);

            return response()->json([
                'status'  => 'success',
                'message' => 'Kalkulasi ulang lembur seluruh pegawai berhasil diperbarui dari data presensi/lembur terkini.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghitung ulang lembur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modal Edit Koreksi Karyawan
     */
    public function editKaryawan($id)
    {
        $karyawan = PayrollLemburKaryawan::with('period')->findOrFail($id);

        return view('admin::payroll-lembur.modal_edit_karyawan', [
            'karyawan' => $karyawan
        ]);
    }

    /**
     * Update Koreksi Karyawan
     */
    public function updateKaryawan(Request $request, $id)
    {
        $karyawan = PayrollLemburKaryawan::with('period')->findOrFail($id);

        if ($karyawan->period->is_locked || !in_array($karyawan->period->status, ['draft', 'revision_requested'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak dapat melakukan koreksi karena periode lembur telah terkunci atau sedang dalam proses approval.'
            ], 403);
        }

        $request->validate([
            'penyesuaian'     => 'required|numeric',
            'catatan_koreksi' => 'nullable|string|max:255',
        ]);

        $penyesuaian = floatval($request->penyesuaian);
        $totalFinal = max(0, floatval($karyawan->nominal_lembur_sistem) + $penyesuaian);

        $karyawan->update([
            'penyesuaian'        => $penyesuaian,
            'total_lembur_final' => $totalFinal,
            'catatan_koreksi'    => $request->catatan_koreksi,
        ]);

        // Update total anggaran periode
        $period = $karyawan->period;
        $period->update([
            'total_anggaran_lembur' => $period->karyawans()->sum('total_lembur_final'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Penyesuaian lembur untuk {$karyawan->nama} berhasil disimpan.",
        ]);
    }

    /**
     * Kunci / Buka Kunci Periode Lembur
     */
    public function toggleLock($id)
    {
        $period = PayrollLemburPeriod::findOrFail($id);
        $newLocked = !$period->is_locked;
        $newStatus = $newLocked ? 'locked' : 'draft';

        $period->update([
            'is_locked' => $newLocked,
            'status'    => $newStatus,
            'locked_at' => $newLocked ? now() : null,
            'locked_by' => $newLocked ? Auth::id() : null,
        ]);

        $msg = $newLocked ? 'Periode lembur berhasil dikunci (Locked).' : 'Kunci periode lembur berhasil dibuka.';

        return response()->json([
            'status'    => 'success',
            'message'   => $msg,
            'is_locked' => $newLocked,
        ]);
    }

    /**
     * Hapus Periode Lembur
     */
    public function destroy($id)
    {
        $period = PayrollLemburPeriod::findOrFail($id);

        if ($period->is_locked) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode lembur yang terkunci tidak dapat dihapus.'
            ], 403);
        }

        $nama = $period->nama_periode;
        $period->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Periode lembur '{$nama}' berhasil dihapus."
        ]);
    }

    /**
     * Cetak / Preview Slip PDF Resmi Karyawan (Sesuai Format TSU di Screenshot)
     */
    public function slipPdf($karyawanId)
    {
        $karyawan = PayrollLemburKaryawan::with(['period', 'details'])->findOrFail($karyawanId);
        $period = $karyawan->period;

        $ttdMaster = $this->resolveTtdMaster($period);

        $pdf = Pdf::loadView('admin::payroll-lembur.slip_pdf', compact('karyawan', 'period', 'ttdMaster'))
            ->setPaper('A4', 'portrait');

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $karyawan->nama);
        $fileName = 'SLIP_LEMBUR_' . ($karyawan->nik ?: 'PEG') . '_' . $cleanName . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Export Rekap Excel
     */
    public function exportExcel($id)
    {
        $period = PayrollLemburPeriod::findOrFail($id);
        $fileName = 'REKAP_LEMBUR_' . Str::slug($period->nama_periode) . '.xlsx';

        return Excel::download(new PayrollLemburExport($period), $fileName);
    }

    /**
     * Download Semua Slip PDF dalam ZIP
     */
    public function downloadAllSlipZip($id)
    {
        $period = PayrollLemburPeriod::with(['karyawans.details'])->findOrFail($id);
        $karyawans = $period->karyawans;

        if ($karyawans->isEmpty()) {
            return back()->with('error', 'Tidak ada data pegawai lembur pada periode ini.');
        }

        $ttdMaster = $this->resolveTtdMaster($period);

        $zipFileName = 'ALL_SLIP_LEMBUR_' . Str::slug($period->nama_periode) . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($karyawans as $karyawan) {
                $pdf = Pdf::loadView('admin::payroll-lembur.slip_pdf', compact('karyawan', 'period', 'ttdMaster'))
                    ->setPaper('A4', 'portrait');

                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $karyawan->nama);
                $pdfFileName = 'SLIP_LEMBUR_' . ($karyawan->nik ?: 'PEG') . '_' . $cleanName . '.pdf';

                $zip->addFromString($pdfFileName, $pdf->output());
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file arsip ZIP.');
    }

    private function resolveTtdMaster($period)
    {
        // Tanda tangan & stempel HANYA muncul ketika approval final sudah ACC (periode terkunci/final)
        $isFinalApproved = in_array($period->status, ['locked', 'approved', 'final']) || !empty($period->is_locked) || !empty($period->locked_at);
        if (!$isFinalApproved) {
            return null;
        }

        $ttdMaster = null;

        // 1. Cari berdasarkan karyawan_ttd_id
        if (!empty($period->karyawan_ttd_id)) {
            $ttdMaster = MasterTandaTangan::where('karyawan_id', $period->karyawan_ttd_id)->first();
        }

        // 2. Jika belum ketemu, cari berdasarkan nama pejabat penandatangan
        if (!$ttdMaster && !empty($period->penandatangan_nama)) {
            $cleanName = trim(preg_replace('/^(dr\.|drg\.|drs\.|dra\.|prof\.|ir\.)\s*/i', '', $period->penandatangan_nama));
            $cleanName = trim(explode(',', $cleanName)[0]);
            if (strlen($cleanName) >= 3) {
                $ttdMaster = MasterTandaTangan::where('nama_pejabat', 'like', '%' . $cleanName . '%')->first();
            }
        }

        // 3. Verifikasi kesesuaian nama agar TIDAK SALAH TTD ORANG LAIN
        if ($ttdMaster && !empty($period->penandatangan_nama)) {
            $cleanTtd = trim(explode(',', preg_replace('/^(dr\.|drg\.|drs\.|dra\.|prof\.|ir\.)\s*/i', '', $ttdMaster->nama_pejabat))[0]);
            $cleanPena = trim(explode(',', preg_replace('/^(dr\.|drg\.|drs\.|dra\.|prof\.|ir\.)\s*/i', '', $period->penandatangan_nama))[0]);
            if (stripos($cleanTtd, $cleanPena) === false && stripos($cleanPena, $cleanTtd) === false && $ttdMaster->karyawan_id !== $period->karyawan_ttd_id) {
                $ttdMaster = null;
            }
        }

        return $ttdMaster;
    }
}
