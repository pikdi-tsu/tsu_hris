<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Services\SaldoCutiService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\DataDosenTendik;
use App\Models\SaldoCutiKaryawan;
use App\Models\MasterUnit;
use App\Models\CutiKaryawan;
use Modules\System\Models\MenuSidebar;

class SaldoCutiController extends MiddlewareController
{
    protected SaldoCutiService $saldoService;

    public function __construct(SaldoCutiService $saldoService)
    {
        $this->saldoService = $saldoService;
        $this->registerPermissions('admin:saldo-cuti');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    /**
     * Halaman Utama Monitoring & Manajemen Saldo Cuti
     */
    public function index(Request $request)
    {
        $currentYear = (int)date('Y');
        $selectedYear = (int)($request->get('tahun', $currentYear));

        // List tahun untuk filter (ambil tahun unik di DB + tahun ini + tahun depan)
        $yearsInDb = SaldoCutiKaryawan::distinct()->pluck('tahun')->toArray();
        $availableYears = array_unique(array_merge([$currentYear - 1, $currentYear, $currentYear + 1], $yearsInDb));
        rsort($availableYears);

        $units = MasterUnit::orderBy('nama_unit')->get();

        // Statistik Ringkas Tahun Terpilih
        $activePegawais = DataDosenTendik::where('is_active', 1)->get();
        $totalPegawaiAktif = $activePegawais->count();

        $totalBerhak = 0;
        foreach ($activePegawais as $p) {
            if (SaldoCutiService::isBerhakCutiTahunan($p, $selectedYear)) {
                $totalBerhak++;
            }
        }

        $saldosInYear = SaldoCutiKaryawan::where('tahun', $selectedYear)->get();
        $totalPunyaSaldo = $saldosInYear->where('is_active', '1')->count();
        $totalTerpakai = $saldosInYear->where('is_active', '1')->sum('terpakai');
        $totalSisa = $saldosInYear->where('is_active', '1')->sum('sisa');

        $menuData = MenuSidebar::where('route', 'admin.saldo-cuti.index')->first();
        $menuIcon = $menuData->icon ?? 'fas fa-calendar-check';
        $title = $menuData->name ?? 'Saldo Cuti Karyawan';

        return view('admin::saldo-cuti.index', [
            'title' => $title,
            'menuIcon' => $menuIcon,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'units' => $units,
            'totalPegawaiAktif' => $totalPegawaiAktif,
            'totalBerhak' => $totalBerhak,
            'totalPunyaSaldo' => $totalPunyaSaldo,
            'totalTerpakai' => $totalTerpakai,
            'totalSisa' => $totalSisa,
        ]);
    }

    /**
     * DataTables Server-side
     */
    public function datatable(Request $request)
    {
        $tahun = (int)($request->get('tahun', date('Y')));

        $query = SaldoCutiKaryawan::select('saldo_cuti_karyawan.*')
            ->with(['pegawai.unit'])
            ->where('saldo_cuti_karyawan.tahun', $tahun);

        // Filter Unit
        if ($request->filled('unit_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('data_dosen_tendiks.unit_id', $request->unit_id);
            });
        }

        // Filter Tipe Karyawan
        if ($request->filled('tipe_karyawan')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('data_dosen_tendiks.tipe_karyawan', $request->tipe_karyawan);
            });
        }

        // Filter Status Aktif Saldo
        if ($request->filled('is_active')) {
            $query->where('saldo_cuti_karyawan.is_active', $request->is_active);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $p = $row->pegawai;
                if (!$p) {
                    return '<span class="text-danger font-italic">Data Pegawai Terhapus</span>';
                }

                $nama = htmlspecialchars($p->nama_lengkap ?? $p->nama);
                $nik = $p->nik ? '<span class="badge badge-light border mr-1"><i class="fas fa-id-card text-muted mr-1"></i>' . htmlspecialchars($p->nik) . '</span>' : '';
                $tipeBadge = $p->tipe_karyawan === 'Dosen'
                    ? '<span class="badge badge-info mr-1">Dosen</span>'
                    : '<span class="badge badge-primary mr-1">Tendik</span>';
                $unit = $p->unit ? '<small class="text-muted d-block mt-1"><i class="fas fa-building mr-1"></i>' . htmlspecialchars($p->unit->nama_unit) . '</small>' : '';

                return '<div>
                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">' . $nama . '</div>
                    <div class="mt-1">' . $tipeBadge . $nik . '</div>
                    ' . $unit . '
                </div>';
            })
            ->addColumn('masa_kerja_info', function ($row) use ($tahun) {
                $p = $row->pegawai;
                if (!$p) return '-';

                $tglBergabung = $p->tgl_bergabung ? Carbon::parse($p->tgl_bergabung)->translatedFormat('d M Y') : '<span class="text-muted font-italic">Belum diatur</span>';
                $tenureYears = SaldoCutiService::getMasaKerjaTahun($p, $tahun);
                $isEligible = SaldoCutiService::isBerhakCutiTahunan($p, $tahun);

                $tenureBadge = $isEligible
                    ? '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> ' . $tenureYears . ' Thn (Berhak)</span>'
                    : '<span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> ' . $tenureYears . ' Thn (&lt; 2 Thn)</span>';

                return '<div>
                    <small class="text-muted d-block">TMT: ' . $tglBergabung . '</small>
                    <div class="mt-1">' . $tenureBadge . '</div>
                </div>';
            })
            ->addColumn('jatah_badge', function ($row) {
                return '<span class="badge badge-secondary py-1 px-2 font-weight-bold" style="font-size: 0.82rem; border-radius: 6px;">' . $row->jatah . ' Hari</span>';
            })
            ->addColumn('terpakai_badge', function ($row) {
                $color = $row->terpakai > 0 ? 'text-primary' : 'text-muted';
                return '<span class="font-weight-bold ' . $color . '" style="font-size: 0.88rem;">' . $row->terpakai . ' Hari</span>';
            })
            ->addColumn('sisa_badge', function ($row) {
                if ($row->sisa <= 0) {
                    $badge = 'badge-danger';
                } elseif ($row->sisa <= 5) {
                    $badge = 'badge-warning text-dark';
                } else {
                    $badge = 'badge-success';
                }
                return '<span class="badge ' . $badge . ' py-1 px-2 font-weight-bold" style="font-size: 0.85rem; border-radius: 6px;">' . $row->sisa . ' Hari</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->is_active == '1') {
                    return '<span class="badge badge-success" style="padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.78rem;"><i class="fas fa-check-circle mr-1"></i>Aktif</span>';
                }
                return '<span class="badge badge-secondary" style="padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.78rem;"><i class="fas fa-times-circle mr-1"></i>Expired</span>';
            })
            ->addColumn('action', function ($row) {
                $btnRiwayat = '<button type="button" class="btn btn-sm btn-info btn-riwayat mr-1" data-id="' . $row->id . '" title="Lihat Pemakaian Cuti" style="border-radius: 6px; font-size: 0.78rem; padding: 0.25rem 0.55rem;">
                    <i class="fas fa-history"></i>
                </button>';

                $btnEdit = '';
                if (auth()->user()->can('admin:saldo-cuti:edit')) {
                    $btnEdit = '<button type="button" class="btn btn-sm btn-warning btn-edit-saldo mr-1" data-id="' . $row->id . '" title="Edit Penyesuaian Saldo" style="border-radius: 6px; font-size: 0.78rem; padding: 0.25rem 0.55rem;">
                        <i class="fas fa-edit"></i>
                    </button>';
                }

                $btnDelete = '';
                if (auth()->user()->can('admin:saldo-cuti:delete')) {
                    $btnDelete = '<button type="button" class="btn btn-sm btn-danger btn-delete-saldo" data-id="' . $row->id . '" title="Hapus Saldo" style="border-radius: 6px; font-size: 0.78rem; padding: 0.25rem 0.55rem;">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                return '<div class="d-flex justify-content-center align-items-center">' . $btnRiwayat . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['pegawai_info', 'masa_kerja_info', 'jatah_badge', 'terpakai_badge', 'sisa_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Modal Form Generate Saldo Massal
     */
    public function generateModal(Request $request)
    {
        $currentYear = (int)date('Y');
        $targetYear = (int)($request->get('tahun', $currentYear));

        $activePegawais = DataDosenTendik::where('is_active', 1)->get();
        $eligibleCount = 0;
        $ineligibleCount = 0;

        foreach ($activePegawais as $p) {
            if (SaldoCutiService::isBerhakCutiTahunan($p, $targetYear)) {
                $eligibleCount++;
            } else {
                $ineligibleCount++;
            }
        }

        $alreadyHaveCount = SaldoCutiKaryawan::where('tahun', $targetYear)->count();

        return view('admin::saldo-cuti.generate_modal', compact('targetYear', 'currentYear', 'activePegawais', 'eligibleCount', 'ineligibleCount', 'alreadyHaveCount'));
    }

    /**
     * Proses Eksekusi Generate Saldo Massal
     */
    public function processGenerate(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2050',
            'jatah' => 'required|integer|min:1|max:30',
        ]);

        try {
            $actor = Auth::user()->name ?? 'HRD Admin';
            $resetOld = $request->has('reset_old');
            $onlyUnassigned = $request->has('only_unassigned');

            $result = $this->saldoService->generateSaldoTahunan(
                (int)$request->tahun,
                $resetOld,
                $onlyUnassigned,
                (int)$request->jatah,
                $actor
            );

            $pesan = "Berhasil memproses saldo cuti tahun {$result['tahun']}: " .
                "{$result['generated_count']} saldo baru dibuat, " .
                "{$result['already_exists_count']} saldo dipertahankan, dan " .
                "{$result['skipped_tenure_count']} pegawai dilewati (karena masa kerja &lt; 2 tahun).";

            if ($result['deactivated_old_count'] > 0) {
                $pesan .= " {$result['deactivated_old_count']} saldo tahun lama dinonaktifkan (Opsi A: hangus per 31 Desember).";
            }

            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_SALDO_CUTI_GENERATE_FAIL]',
                'Gagal men-generate saldo cuti tahunan.',
                'Generate Saldo Cuti Massal',
                $request
            );
        }
    }

    /**
     * Modal Tambah Saldo Manual (Perorangan)
     */
    public function create()
    {
        $pegawais = DataDosenTendik::where('is_active', 1)
            ->orderBy('nama')
            ->get();

        $currentYear = (int)date('Y');

        return view('admin::saldo-cuti.create_modal', compact('pegawais', 'currentYear'));
    }

    /**
     * Simpan Saldo Manual
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|uuid|exists:data_dosen_tendiks,id',
            'tahun'   => 'required|integer|min:2020|max:2050',
            'jatah'   => 'required|integer|min:1|max:60',
            'sisa'    => 'required|integer|min:0|max:60',
            'expired' => 'required|date',
        ]);

        try {
            $actor = Auth::user()->name ?? 'HRD Admin';
            $this->saldoService->assignSaldoManual($request->all(), $actor);

            return back()->with('success', 'Saldo cuti pegawai berhasil ditambahkan/diperbarui.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_SALDO_CUTI_STORE_FAIL]',
                'Gagal menyimpan saldo cuti manual.',
                'Input Saldo Cuti Manual',
                $request
            );
        }
    }

    /**
     * Modal Edit Saldo
     */
    public function edit($id)
    {
        $saldo = SaldoCutiKaryawan::with('pegawai')->findOrFail($id);
        return view('admin::saldo-cuti.edit_modal', compact('saldo'));
    }

    /**
     * Update Saldo
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'jatah'    => 'required|integer|min:1|max:60',
            'terpakai' => 'required|integer|min:0|max:60',
            'sisa'     => 'required|integer|min:0|max:60',
            'expired'  => 'required|date',
            'is_active'=> 'required|in:0,1',
        ]);

        try {
            $actor = Auth::user()->name ?? 'HRD Admin';
            $this->saldoService->updateSaldo((int)$id, $request->all(), $actor);

            return back()->with('success', 'Saldo cuti berhasil diperbarui.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_SALDO_CUTI_UPDATE_FAIL]',
                'Gagal memperbarui saldo cuti.',
                'Update Saldo Cuti',
                $request
            );
        }
    }

    /**
     * Hapus Saldo
     */
    public function destroy(Request $request, $id)
    {
        try {
            $saldo = SaldoCutiKaryawan::findOrFail($id);
            $saldo->delete();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Saldo cuti berhasil dihapus.']);
            }

            return back()->with('success', 'Saldo cuti berhasil dihapus.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus saldo cuti: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menghapus saldo cuti: ' . $e->getMessage());
        }
    }

    /**
     * Modal Riwayat Pemakaian Cuti Karyawan
     */
    public function riwayatModal($id)
    {
        $saldo = SaldoCutiKaryawan::with(['pegawai.unit'])->findOrFail($id);
        $riwayatCutis = CutiKaryawan::with(['masterCuti', 'atasan', 'hrd'])
            ->where('id_user', $saldo->id_user)
            ->where('statushrd', 'approved')
            ->where('statusatasan', 'approved')
            ->whereYear('tanggalmulai', $saldo->tahun)
            ->orderBy('tanggalmulai', 'desc')
            ->get();

        return view('admin::saldo-cuti.riwayat_modal', compact('saldo', 'riwayatCutis'));
    }
}
