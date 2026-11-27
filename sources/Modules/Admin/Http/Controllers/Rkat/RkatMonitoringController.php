<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterUnit;
use App\Models\RkatPengajuan;
use App\Models\RkatPeriode;
use App\Models\RkatProgram;
use Illuminate\Http\Request;

class RkatMonitoringController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Halaman Monitoring Progress & Capaian Kinerja RKAT
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id', optional(RkatPeriode::active()->first())->id ?? optional($periodes->first())->id);

        $query = RkatPengajuan::with(['periode', 'unit', 'program', 'pic'])
            ->whereIn('status', ['Disetujui', 'Selesai'])
            ->when($selectedPeriodeId, function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })
            ->orderBy('total_anggaran_disetujui', 'desc');

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'LIKE', "%{$search}%");
            });
        }

        $kegiatans = $query->paginate(15)->withQueryString();

        // Anomali / Alert Detection
        $allApproved = RkatPengajuan::where('status', 'Disetujui')
            ->when($selectedPeriodeId, function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })->get();

        $alerts = [
            'over_budget' => $allApproved->filter(fn($k) => $k->total_realisasi > $k->total_anggaran_disetujui),
            'high_spending' => $allApproved->filter(fn($k) => $k->persen_serapan >= 90),
            'low_spending' => $allApproved->filter(fn($k) => $k->persen_serapan < 30),
            'no_spending' => $allApproved->filter(fn($k) => $k->total_realisasi == 0),
        ];

        $units = MasterUnit::orderBy('nama_unit')->get();

        return view('admin::rkat.monitoring.index', [
            'title' => 'Monitoring RKAT & Capaian Kinerja',
            'kegiatans' => $kegiatans,
            'periodes' => $periodes,
            'selectedPeriodeId' => $selectedPeriodeId,
            'units' => $units,
            'alerts' => $alerts,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Evaluasi Akhir Tahun Anggaran
     */
    public function evaluasi(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id', optional(RkatPeriode::active()->first())->id ?? optional($periodes->first())->id);

        $kegiatans = RkatPengajuan::with(['periode', 'unit', 'program', 'pic'])
            ->where('status', 'Disetujui')
            ->when($selectedPeriodeId, function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })
            ->orderBy('total_anggaran_disetujui', 'desc')
            ->paginate(15)->withQueryString();

        return view('admin::rkat.monitoring.evaluasi', [
            'title' => 'Evaluasi Akhir Tahun RKAT',
            'kegiatans' => $kegiatans,
            'periodes' => $periodes,
            'selectedPeriodeId' => $selectedPeriodeId,
        ]);
    }
}
