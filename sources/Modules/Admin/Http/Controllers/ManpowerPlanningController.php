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

class ManpowerPlanningController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:mpp');
    }

    public function index()
    {
        // Permission middleware is already handling 'view' through constructor if they hit the route, but we can double check.
        // Or in route we use `permission:admin:mpp:view`. Since I'm using MiddlewareController, I don't necessarily need `$this->guard('view', 'admin:mpp')` if the route uses the middleware. But let's follow the rule.
        $this->guard('view', 'admin:mpp');

        $tahun = request('tahun', date('Y'));
        
        $stats = [
            'total' => ManpowerPlanning::where('tahun', $tahun)->sum('jumlah_kebutuhan'),
            'waiting' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'waiting')->sum('jumlah_kebutuhan'),
            'approved' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'approved')->sum('jumlah_kebutuhan'),
            'rejected' => ManpowerPlanning::where('tahun', $tahun)->where('status', 'rejected')->sum('jumlah_kebutuhan'),
        ];
        
        $units = MasterUnit::all();

        $title = 'Kelola Manpower Planning';
        $menu = 'manpower_planning';
        return view('admin::mpp.index', compact('stats', 'tahun', 'units', 'title', 'menu'));
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
                return $row->unit ? $row->unit->nama_unit : '-';
            })
            ->addColumn('jabatan', function ($row) {
                return $row->jabatan ? $row->jabatan->nama_jabatan : '-';
            })
            ->addColumn('pengaju', function ($row) {
                return $row->pengaju ? $row->pengaju->nama : '-';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'waiting') return '<span class="badge badge-warning">Menunggu</span>';
                if ($row->status == 'approved') return '<span class="badge badge-success">Disetujui</span>';
                if ($row->status == 'rejected') return '<span class="badge badge-danger">Ditolak</span>';
                return '-';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-sm btn-info" onclick="detail(\'' . $row->id . '\')" title="Detail/Tinjau"><i class="fas fa-eye"></i> Tinjau</button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
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
            $data = ManpowerPlanning::with(['jabatan', 'unit', 'pengaju'])->find($request->id);
            
            $hrdName = '-';
            if ($data->hrd) {
                $hrdProf = DataDosenTendik::where('user_id', $data->hrd->id)->first();
                $hrdName = $hrdProf ? $hrdProf->nama : $data->hrd->name;
            }

            $html = view('admin::mpp.modalapproval', compact('data', 'hrdName'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_MPP_DETAIL_FAIL]', 'Gagal memuat detail MPP.');
        }
    }
}
