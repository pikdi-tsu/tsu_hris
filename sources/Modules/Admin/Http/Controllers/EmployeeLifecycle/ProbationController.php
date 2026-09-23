<?php

namespace Modules\Admin\Http\Controllers\EmployeeLifecycle;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\PegawaiProbation;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProbationController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        // Akses admin HR
    }

    /**
     * Halaman Monitoring Evaluasi Masa Percobaan (Probation)
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $in14Days = Carbon::today()->addDays(14)->toDateString();

        $berjalanCount = PegawaiProbation::where('status', 'berjalan')->count();

        // Butuh evaluasi segera (sisa <= 14 hari atau sudah lewat dan belum dinilai)
        $evaluasiCount = PegawaiProbation::where('status', 'berjalan')
            ->where('tgl_selesai', '<=', $in14Days)
            ->whereNull('skor_total')
            ->count();

        $lulusCount = PegawaiProbation::where('rekomendasi', 'angkat_tetap')->count();
        $totalCount = PegawaiProbation::count();

        $units = MasterUnit::orderBy('nama_unit')->get();

        return view('admin::employee-lifecycle.probation.index', [
            'title'          => 'Evaluasi Masa Percobaan (Probation)',
            'menu'           => 'probation',
            'menuIcon'       => 'fas fa-user-clock',
            'berjalanCount'  => $berjalanCount,
            'evaluasiCount'  => $evaluasiCount,
            'lulusCount'     => $lulusCount,
            'totalCount'     => $totalCount,
            'units'          => $units,
        ]);
    }

    /**
     * DataTables Pegawai Probation
     */
    public function datatable(Request $request)
    {
        $query = PegawaiProbation::with(['pegawai.unit', 'evaluator'])
            ->select('pegawai_probations.*');

        // Filter status tab
        if ($request->filled('filter_status')) {
            $fStatus = $request->filter_status;
            if ($fStatus === 'berjalan') {
                $query->where('status', 'berjalan');
            } elseif ($fStatus === 'butuh_evaluasi') {
                $query->where('status', 'berjalan')
                    ->where('tgl_selesai', '<=', Carbon::today()->addDays(14)->toDateString())
                    ->whereNull('skor_total');
            } elseif ($fStatus === 'selesai') {
                $query->where('status', 'selesai');
            }
        }

        // Filter unit
        if ($request->filled('unit_id')) {
            $unitId = $request->unit_id;
            $query->whereHas('pegawai', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $pegawai = $row->pegawai;
                if (!$pegawai) {
                    return '<span class="text-muted font-italic">Data Pegawai Tidak Ditemukan</span>';
                }

                $namaLengkap = trim(($pegawai->gelar_depan ? $pegawai->gelar_depan . ' ' : '') . $pegawai->nama . ($pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : ''));
                $unitNama = $pegawai->unit->nama_unit ?? '-';
                $nik = $pegawai->nik ?? $pegawai->nip ?? '-';
                $tipeBadge = $pegawai->tipe_karyawan === 'Dosen'
                    ? '<span class="badge badge-primary px-2 py-1">Dosen</span>'
                    : '<span class="badge badge-info px-2 py-1">Tendik</span>';

                return '
                    <div class="d-flex align-items-center">
                        <div class="mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: #0c6170; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            ' . strtoupper(substr($pegawai->nama, 0, 1)) . '
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">' . e($namaLengkap) . ' ' . $tipeBadge . '</div>
                            <div class="text-muted small">NIK: <strong>' . e($nik) . '</strong> | Unit: ' . e($unitNama) . '</div>
                            <div class="text-muted small">Posisi: <span class="text-dark font-weight-bold">' . e($pegawai->posisi ?? '-') . '</span></div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('periode', function ($row) {
                $mulai = $row->tgl_mulai ? Carbon::parse($row->tgl_mulai)->format('d M Y') : '-';
                $selesai = $row->tgl_selesai ? Carbon::parse($row->tgl_selesai)->format('d M Y') : '-';

                return '
                    <div>
                        <div class="small text-muted"><i class="far fa-calendar-alt mr-1"></i> ' . $mulai . ' s/d</div>
                        <div class="font-weight-bold text-dark">' . $selesai . '</div>
                        <div class="small text-muted">Durasi: ' . $row->durasi_bulan . ' Bulan</div>
                    </div>
                ';
            })
            ->addColumn('sisa_waktu', function ($row) {
                if ($row->status === 'selesai') {
                    return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai Evaluasi</span>';
                }

                $sisa = $row->sisa_hari;
                if ($sisa < 0) {
                    return '<span class="badge badge-danger px-2 py-1 font-weight-bold animate__animated animate__flash"><i class="fas fa-exclamation-triangle mr-1"></i> Jatuh Tempo (' . abs($sisa) . ' Hari Lalu)</span>';
                } elseif ($sisa <= 14) {
                    return '<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold"><i class="fas fa-clock mr-1"></i> Sisa ' . $sisa . ' Hari</span>';
                } else {
                    return '<span class="badge badge-info px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i> Sisa ' . $sisa . ' Hari</span>';
                }
            })
            ->addColumn('skor_info', function ($row) {
                if ($row->skor_total === null) {
                    return '<span class="badge badge-light border text-muted px-2 py-1"><i class="fas fa-edit mr-1"></i> Belum Dinilai</span>';
                }

                $skorClass = $row->skor_total >= 80 ? 'text-success' : ($row->skor_total >= 70 ? 'text-info' : 'text-danger');

                return '
                    <div>
                        <div class="font-weight-bold ' . $skorClass . '" style="font-size: 1.1rem;">' . number_format($row->skor_total, 1) . ' <small>/ 100</small></div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Disiplin: ' . number_format($row->skor_kedisiplinan, 0) . ' | Komp: ' . number_format($row->skor_kompetensi, 0) . ' | Tim: ' . number_format($row->skor_kerjasama, 0) . '
                        </div>
                    </div>
                ';
            })
            ->addColumn('rekomendasi_badge', function ($row) {
                if (!$row->rekomendasi) {
                    return '<span class="text-muted small font-italic">Menunggu Keputusan</span>';
                }

                if ($row->rekomendasi === 'angkat_tetap') {
                    return '<span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-user-check mr-1"></i> Angkat Tetap</span>';
                } elseif ($row->rekomendasi === 'perpanjang_probation') {
                    return '<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold"><i class="fas fa-redo mr-1"></i> Perpanjang</span>';
                } else {
                    return '<span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fas fa-user-times mr-1"></i> Tidak Lolos</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary btn-evaluasi" data-id="' . $row->id . '" title="Form Penilaian & Evaluasi" style="border-radius: 6px 0 0 6px;"><i class="fas fa-clipboard-check mr-1"></i> Evaluasi</button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . $row->id . '" title="Hapus Data" style="border-radius: 0 6px 6px 0;"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['pegawai_info', 'periode', 'sisa_waktu', 'skor_info', 'rekomendasi_badge', 'action'])
            ->make(true);
    }

    /**
     * Modal Form Tambah Pegawai ke Masa Probation
     */
    public function modalAdd()
    {
        $pegawais = DataDosenTendik::with('unit')
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get();

        return view('admin::employee-lifecycle.probation.modal_add', [
            'pegawais' => $pegawais,
        ]);
    }

    /**
     * Simpan Data Probation Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id'   => 'required|string|exists:data_dosen_tendiks,id',
            'tgl_mulai'    => 'required|date',
            'tgl_selesai'  => 'required|date|after_or_equal:tgl_mulai',
            'durasi_bulan' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $pegawai = DataDosenTendik::findOrFail($request->pegawai_id);

            PegawaiProbation::create([
                'pegawai_id'   => $pegawai->id,
                'tgl_mulai'    => $request->tgl_mulai,
                'tgl_selesai'  => $request->tgl_selesai,
                'durasi_bulan' => $request->durasi_bulan,
                'status'       => 'berjalan',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pegawai berhasil didaftarkan ke masa probation.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mendaftarkan probation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modal Form Penilaian / Evaluasi Probation
     */
    public function modalEvaluasi($id)
    {
        $probation = PegawaiProbation::with(['pegawai.unit', 'evaluator'])->findOrFail($id);

        $evaluators = DataDosenTendik::where('is_active', 1)->orderBy('nama')->get();

        return view('admin::employee-lifecycle.probation.modal_evaluasi', [
            'probation'  => $probation,
            'evaluators' => $evaluators,
        ]);
    }

    /**
     * Simpan Penilaian Evaluasi Probation
     */
    public function storeEvaluasi(Request $request, $id)
    {
        $probation = PegawaiProbation::findOrFail($id);

        $request->validate([
            'skor_kedisiplinan' => 'required|numeric|min:0|max:100',
            'skor_kompetensi'   => 'required|numeric|min:0|max:100',
            'skor_kerjasama'    => 'required|numeric|min:0|max:100',
            'rekomendasi'       => 'required|in:angkat_tetap,perpanjang_probation,tidak_lolos',
            'catatan_evaluasi'  => 'nullable|string',
            'evaluator_id'      => 'nullable|string|exists:data_dosen_tendiks,id',
        ]);

        try {
            DB::beginTransaction();

            // Hitung rata-rata skor
            $skorTotal = ($request->skor_kedisiplinan + $request->skor_kompetensi + $request->skor_kerjasama) / 3;

            $status = $request->rekomendasi === 'angkat_tetap' ? 'selesai' : 'berjalan';

            $probation->update([
                'skor_kedisiplinan' => $request->skor_kedisiplinan,
                'skor_kompetensi'   => $request->skor_kompetensi,
                'skor_kerjasama'    => $request->skor_kerjasama,
                'skor_total'        => round($skorTotal, 2),
                'rekomendasi'       => $request->rekomendasi,
                'catatan_evaluasi'  => $request->catatan_evaluasi,
                'evaluator_id'      => $request->evaluator_id,
                'tgl_evaluasi'      => Carbon::today()->toDateString(),
                'status'            => $status,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Penilaian evaluasi probation berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan evaluasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus Data Probation
     */
    public function destroy($id)
    {
        $probation = PegawaiProbation::findOrFail($id);
        $probation->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data probation berhasil dihapus.',
        ]);
    }
}
