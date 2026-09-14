<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KpiPeriode;
use App\Models\KpiUnitIndikator;
use App\Models\MasterUnit;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Storage;

class KpiMonitoringController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:kpi');
    }

    public function index(Request $request)
    {
        $periodes = KpiPeriode::orderBy('tahun', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id', optional(KpiPeriode::where('is_active', 1)->first())->id ?? optional($periodes->first())->id);
        $currentPeriode = KpiPeriode::find($selectedPeriodeId);

        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();
        $selectedUnitId = $request->input('unit_id', optional($units->first())->id);
        $currentUnit = MasterUnit::find($selectedUnitId);

        // Stats for this unit & period
        $unitIndikators = KpiUnitIndikator::where('periode_id', $selectedPeriodeId)
            ->where('master_unit_id', $selectedUnitId)
            ->get();

        $totalIndikator = $unitIndikators->count();
        $totalTerisi = $unitIndikators->whereNotNull('realisasi_angka')->count();
        $avgCapaian = $unitIndikators->whereNotNull('capaian_persen')->avg('capaian_persen') ?? 0;
        $totalSkor = $unitIndikators->whereNotNull('skor')->sum('skor') ?? 0;

        return view('admin::kpi.monitoring.index', [
            'title'          => 'Monitoring & Realisasi Kinerja KPI',
            'menuIcon'       => 'fas fa-chart-line',
            'periodes'       => $periodes,
            'currentPeriode' => $currentPeriode,
            'units'          => $units,
            'currentUnit'    => $currentUnit,
            'totalIndikator' => $totalIndikator,
            'totalTerisi'    => $totalTerisi,
            'avgCapaian'     => round($avgCapaian, 2),
            'totalSkor'      => round($totalSkor, 2),
        ]);
    }

    public function dataTable(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $unitId = $request->input('master_unit_id');

        $query = KpiUnitIndikator::with([
            'masterIndikator.perspektif',
            'masterIndikator.parent',
            'unit'
        ])
        ->where('periode_id', $periodeId)
        ->where('master_unit_id', $unitId)
        ->orderBy('urutan', 'asc')
        ->orderBy('id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('perspektif_badge', function ($row) {
                if (!$row->masterIndikator || !$row->masterIndikator->perspektif) {
                    return '-';
                }
                $p = $row->masterIndikator->perspektif;
                $kode = strtoupper($p->kode ?? '');
                $style = match($kode) {
                    'FIN' => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                    'CUS' => 'background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);',
                    'INT' => 'background: rgba(139, 92, 246, 0.1); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25);',
                    'LRN' => 'background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25);',
                    default => 'background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25);',
                };
                return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="' . $style . ' font-size: 0.78rem;">' . htmlspecialchars($p->nama_perspektif) . '</span>';
            })
            ->addColumn('indikator_info', function ($row) {
                $mi = $row->masterIndikator;
                if (!$mi) return '-';

                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.9rem;"><span style="color: #094b54; font-family: monospace; font-weight: 700;">[' . e($mi->kode_indikator) . ']</span> ' . e($mi->nama_indikator) . '</div>';
                $extra = [];
                if ($mi->level === 'sub') {
                    $extra[] = '<span class="badge badge-pill px-2 py-0" style="background: rgba(2, 132, 199, 0.08); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.2); font-size: 0.72rem;"><i class="fas fa-level-down-alt mr-1"></i>Sub</span>';
                }
                if ($mi->polaritas) {
                    $polaritasStyle = $mi->polaritas === 'Maximize'
                        ? 'background: rgba(16, 185, 129, 0.08); color: #059669; border: 1px solid rgba(16, 185, 129, 0.2);'
                        : 'background: rgba(217, 119, 6, 0.08); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.2);';
                    $extra[] = '<span class="badge badge-pill px-2 py-0" style="' . $polaritasStyle . ' font-size: 0.72rem;">' . e($mi->polaritas) . '</span>';
                }
                if ($extra) {
                    $html .= '<div class="mt-1 d-flex flex-wrap" style="gap: 4px;">' . implode('', $extra) . '</div>';
                }
                return $html;
            })
            ->addColumn('bobot_formatted', function ($row) {
                return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.82rem;">' . number_format($row->bobot, 1) . '%</span>';
            })
            ->addColumn('target_satuan', function ($row) {
                $targetVal = $row->target_angka !== null ? rtrim(rtrim(number_format($row->target_angka, 2, '.', ''), '0'), '.') : ($row->target_label ?? '-');
                $satuan = $row->satuan ?? optional($row->masterIndikator)->satuan ?? '';
                return '<strong class="text-dark" style="font-size: 0.92rem;">' . e($targetVal) . '</strong> <span class="text-muted small">' . e($satuan) . '</span>';
            })
            ->addColumn('realisasi_satuan', function ($row) {
                if ($row->realisasi_angka === null && !$row->realisasi_label) {
                    return '<span class="badge badge-pill text-muted font-italic px-2 py-1" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.75rem;">Belum Diisi</span>';
                }
                $realVal = $row->realisasi_angka !== null ? rtrim(rtrim(number_format($row->realisasi_angka, 2, '.', ''), '0'), '.') : $row->realisasi_label;
                $satuan = $row->satuan ?? optional($row->masterIndikator)->satuan ?? '';
                return '<strong style="color: #094b54; font-size: 0.92rem;">' . e($realVal) . '</strong> <span class="text-muted small">' . e($satuan) . '</span>';
            })
            ->addColumn('capaian_badge', function ($row) {
                return $row->capaian_badge;
            })
            ->addColumn('skor_formatted', function ($row) {
                if ($row->skor === null) {
                    return '<span class="text-muted small">-</span>';
                }
                return '<strong class="font-weight-bold text-dark" style="font-size: 0.92rem;">' . number_format($row->skor, 2) . '</strong>';
            })
            ->addColumn('bukti_dukung', function ($row) {
                if ($row->file_bukti) {
                    $url = asset('storage/' . $row->file_bukti);
                    return '<a href="'.$url.'" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat Bukti Dukung" style="border-radius: 6px; padding: 0.2rem 0.5rem; font-size: 0.75rem;"><i class="fas fa-paperclip mr-1"></i>Bukti</a>';
                }
                return '<span class="text-muted small font-italic">-</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_monev_badge;
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-outline-primary btn-evaluasi" data-id="'.$row->id.'" title="Input / Update Realisasi Kinerja" style="border-radius: 6px; padding: 0.25rem 0.65rem; font-weight: 600; font-size: 0.8rem;"><i class="fas fa-clipboard-check mr-1"></i>Evaluasi</button>';
                return $btn;
            })
            ->rawColumns(['perspektif_badge', 'indikator_info', 'bobot_formatted', 'target_satuan', 'realisasi_satuan', 'capaian_badge', 'skor_formatted', 'bukti_dukung', 'status_badge', 'action'])
            ->make(true);
    }

    public function updateRealisasi(Request $request, $id)
    {
        $unitIndikator = KpiUnitIndikator::with('masterIndikator')->findOrFail($id);

        $validated = $request->validate([
            'realisasi_angka'        => 'nullable|numeric',
            'realisasi_label'        => 'nullable|string',
            'analisis_capaian'       => 'nullable|string',
            'kendala'                => 'nullable|string',
            'rencana_tindak_lanjut'  => 'nullable|string',
            'status_monev'           => 'required|in:Belum Mengisi,Draft,Terevaluasi,Tercapai,Tidak Tercapai',
            'file_bukti'             => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx,zip|max:10240',
        ]);

        if ($request->hasFile('file_bukti')) {
            // Delete old file if exists
            if ($unitIndikator->file_bukti && Storage::disk('public')->exists($unitIndikator->file_bukti)) {
                Storage::disk('public')->delete($unitIndikator->file_bukti);
            }
            $filePath = $request->file('file_bukti')->store('kpi_evidence', 'public');
            $unitIndikator->file_bukti = $filePath;
        }

        $unitIndikator->realisasi_angka = $validated['realisasi_angka'] !== null ? $validated['realisasi_angka'] : null;
        $unitIndikator->realisasi_label = $validated['realisasi_label'] ?? null;
        $unitIndikator->analisis_capaian = $validated['analisis_capaian'] ?? null;
        $unitIndikator->kendala = $validated['kendala'] ?? null;
        $unitIndikator->rencana_tindak_lanjut = $validated['rencana_tindak_lanjut'] ?? null;
        $unitIndikator->status_monev = $validated['status_monev'];

        // Compute Capaian % and Skor automatically
        $unitIndikator->hitungCapaianDanSkor();
        $unitIndikator->save();

        return $this->successResponse([
            'unit_indikator' => $unitIndikator,
            'capaian_persen' => $unitIndikator->capaian_persen,
            'skor'           => $unitIndikator->skor,
        ], 'Realisasi dan evaluasi capaian KPI berhasil disimpan');
    }
}
