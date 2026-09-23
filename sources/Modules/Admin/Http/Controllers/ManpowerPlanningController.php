<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ManpowerPlanning;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterUnit;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DataDosenTendik;
use App\Traits\ApiResponseTrait;
use Modules\System\Models\MenuSidebar;

class ManpowerPlanningController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:mpp');
    }

    public function index()
    {
        $this->guard('view', 'admin:mpp');

        $tahun = request('tahun', date('Y'));
        
        $stats = [
            'total' => ManpowerPlanning::where('tahun', $tahun)->sum('jumlah_kebutuhan'),
            'waiting' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'waiting')->sum('jumlah_kebutuhan'),
            'approved' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'approved')->sum('jumlah_kebutuhan'),
            'rejected' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'rejected')->sum('jumlah_kebutuhan'),
            'count_waiting' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'waiting')->count(),
            'count_history' => ManpowerPlanning::where('tahun', $tahun)->whereIn('status', ['approved', 'rejected'])->count(),
        ];
        
        $allUnits = MasterUnit::with(['parent'])->orderBy('tipe_unit', 'asc')->orderBy('nama_unit', 'asc')->get();

        $analysisData = $allUnits->map(function ($unit) use ($tahun) {
            $gapData = $unit->hitungGap();
            $pendingCount = ManpowerPlanning::where('unit_id', $unit->id)
                ->where('tahun', $tahun)
                ->whereIn('status', ['waiting', 'approved'])
                ->sum('jumlah_kebutuhan');

            return [
                'unit' => $unit,
                'id' => $unit->id,
                'tipe_unit' => $unit->tipe_unit,
                'nama_unit' => $unit->nama_unit,
                'parent_name' => $unit->parent ? $unit->parent->nama_unit : '-',
                'jumlah_mahasiswa' => $unit->jumlah_mahasiswa,
                'beban_kerja' => $unit->beban_kerja ?? 'sedang',
                'kuota_mpp' => (int)$unit->kuota_mpp,
                'supply' => $gapData['supply'],
                'demand' => $gapData['demand'],
                'gap' => $gapData['gap'],
                'status' => $gapData['status'],
                'label' => $gapData['label'],
                'badge' => $gapData['badge'],
                'rekomendasi' => $gapData['rekomendasi'],
                'action_type' => $gapData['action_type'],
                'pending_mpp' => $pendingCount,
            ];
        });

        $mppSummary = [
            'total_units' => $analysisData->count(),
            'total_supply' => $analysisData->sum(fn($i) => $i['supply']['total_hc']),
            'total_demand' => $analysisData->sum('demand'),
            'units_deficit' => $analysisData->where('gap', '>', 0)->count(),
            'units_balanced' => $analysisData->where('gap', '==', 0)->count(),
            'units_surplus' => $analysisData->where('gap', '<', 0)->count(),
            'total_gap_kebutuhan' => $analysisData->where('gap', '>', 0)->sum('gap'),
        ];

        $units = $allUnits;

        $title = 'Manpower Planning';
        $menu = 'manpower_planning';
        $menuIcon = MenuSidebar::where('route', 'admin.mpp.index')->value('icon') ?? 'fas fa-users-cog';

        return view('admin::mpp.index', compact('stats', 'tahun', 'units', 'analysisData', 'mppSummary', 'title', 'menu', 'menuIcon'));
    }

    public function datatables(Request $request)
    {
        $this->guard('view', 'admin:mpp');
        $query = ManpowerPlanning::with(['jabatan', 'unit', 'pengaju'])->orderBy('created_at', 'desc');

        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->status) {
            if ($request->status == 'history') {
                $query->whereIn('status', ['approved', 'rejected']);
            } else {
                $query->where('status', $request->status);
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('unit', function ($row) {
                if (!$row->unit) return '-';
                return '<div class="d-flex align-items-center"><i class="fas fa-building text-secondary mr-2"></i><span class="font-weight-600 text-dark">' . e($row->unit->nama_unit) . '</span></div>';
            })
            ->addColumn('jabatan', function ($row) {
                if (!$row->jabatan) return '-';
                return '<span class="font-weight-bold" style="color: var(--tsu-primary, #094b54);">' . e($row->jabatan->nama_jabatan) . '</span>';
            })
            ->editColumn('jumlah_kebutuhan', function ($row) {
                return '<span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 0.85rem;"><i class="fas fa-user-plus text-primary mr-1"></i>' . $row->jumlah_kebutuhan . ' Orang</span>';
            })
            ->editColumn('tipe_pengajuan', function ($row) {
                $badgeClass = $row->tipe_pengajuan == 'Baru' ? 'badge-info' : 'badge-secondary';
                return '<span class="badge ' . $badgeClass . ' px-2 py-1">' . e($row->tipe_pengajuan) . '</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'waiting') {
                    return '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                }
                if ($row->status == 'approved') {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
                }
                if ($row->status == 'rejected') {
                    return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary font-weight-600 px-3" style="border-radius: 6px;" onclick="detail(\'' . $row->id . '\')" title="Detail & Tinjau Usulan"><i class="fas fa-clipboard-check mr-1"></i> Tinjau</button>';
            })
            ->rawColumns(['unit', 'jabatan', 'jumlah_kebutuhan', 'tipe_pengajuan', 'status', 'action'])
            ->make(true);
    }

    public function approve(Request $request)
    {
        $this->guard('update', 'admin:mpp');
        DB::beginTransaction();
        try {
            $mpp = ManpowerPlanning::find($request->id);
            if (!$mpp) throw new \Exception('Data MPP tidak ditemukan.');

            $mpp->update([
                'status' => $request->status,
                'keterangan_hrd' => $request->keterangan_hrd,
                'approved_by' => Auth::id(),
                'approval_date' => now()
            ]);

            // Optional: Notifikasi balik ke Atasan

            DB::commit();
            return $this->sendSuccess('Status MPP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[TSU_MPP_APPROVE_FAIL]', 'Gagal memperbarui status MPP.');
        }
    }

    public function detail(Request $request)
    {
        try {
            $data = ManpowerPlanning::with(['jabatan', 'unit.kepalaJabatan', 'unit.parent', 'pengaju'])->find($request->id);
            if (!$data) throw new \Exception('Data MPP tidak ditemukan.');
            
            $hrdName = '-';
            if ($data->hrd) {
                $hrdProf = DataDosenTendik::where('user_id', $data->hrd->id)->first();
                $hrdName = $hrdProf ? $hrdProf->nama : $data->hrd->name;
            }

            // Dapatkan jumlah karyawan aktif saat ini di unit tersebut
            $existing_count = DataDosenTendik::where('unit_id', $data->unit_id)->where('is_active', 1)->count();
            $kuota_mpp = $data->unit ? (int)$data->unit->kuota_mpp : 0;

            // Pengajuan berjalan lainnya pada tahun yang sama (kecuali usulan ini)
            $pending_other_count = ManpowerPlanning::where('unit_id', $data->unit_id)
                ->where('tahun', $data->tahun)
                ->where('id', '!=', $data->id)
                ->whereIn('status', ['waiting', 'approved'])
                ->sum('jumlah_kebutuhan');

            $balance_before = $kuota_mpp > 0 ? max(0, $kuota_mpp - $existing_count - $pending_other_count) : '∞';
            $balance_after = $kuota_mpp > 0 ? max(0, $kuota_mpp - $existing_count - $pending_other_count - $data->jumlah_kebutuhan) : '∞';
            $is_over_quota = ($kuota_mpp > 0 && ($existing_count + $pending_other_count + $data->jumlah_kebutuhan) > $kuota_mpp);

            $html = view('admin::mpp.modalapproval', compact(
                'data', 
                'hrdName', 
                'existing_count', 
                'kuota_mpp',
                'pending_other_count',
                'balance_before',
                'balance_after',
                'is_over_quota'
            ))->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_MPP_DETAIL_FAIL]', 'Gagal memuat detail MPP.');
        }
    }

    /**
     * Endpoint Cepat HRD untuk Penyesuaian Kuota MPP Unit Kerja
     */
    public function updateKuota(Request $request)
    {
        $this->guard('update', 'admin:mpp');
        $request->validate([
            'unit_id'   => 'required',
            'kuota_mpp' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $unit = MasterUnit::find($request->unit_id);
            if (!$unit) throw new \Exception('Unit kerja tidak ditemukan.');

            $oldKuota = (int)$unit->kuota_mpp;
            $unit->kuota_mpp = (int)$request->kuota_mpp;
            $unit->save();

            DB::commit();
            return response()->json([
                'success'   => true,
                'message'   => "Kuota MPP unit {$unit->nama_unit} berhasil disesuaikan dari {$oldKuota} menjadi {$unit->kuota_mpp} orang.",
                'new_kuota' => $unit->kuota_mpp
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[TSU_MPP_UPDATE_KUOTA_FAIL]', 'Gagal memperbarui kuota unit.');
        }
    }
}
