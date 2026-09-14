<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterStatusKaryawan;
use App\Models\DataDosenTendik;
use Modules\System\Models\MenuSidebar;
use App\Services\TsuErrorHandlerService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class MasterStatusKaryawanController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:master-status-karyawan');
    }

    public function index()
    {
        $stats = [
            'total'         => MasterStatusKaryawan::count(),
            'aktif'         => MasterStatusKaryawan::where('is_active', 'Y')->count(),
            'non_aktif'     => MasterStatusKaryawan::where('is_active', '!=', 'Y')->count(),
            'total_pegawai' => DataDosenTendik::whereNotNull('status_karyawan_id')->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-status-karyawan.index')->value('icon') ?: 'fas fa-id-badge';

        return view('admin::master-data.status-karyawan.index', [
            'title'    => 'Master Data Status Karyawan',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterStatusKaryawan::orderBy('nama_status', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_status', function($row){
                $empCount = DataDosenTendik::where('status_karyawan_id', $row->id)->count();
                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_status) . '</div>';
                if ($empCount > 0) {
                    $html .= '<small class="text-muted d-block" style="font-size: 0.75rem;">' . $empCount . ' Pegawai Terdaftar</small>';
                }
                return $html;
            })
            ->editColumn('keterangan', function($row){
                return $row->keterangan ? '<span class="text-secondary" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>' : '<span class="text-muted">-</span>';
            })
            ->editColumn('status', function($row){
                if ($row->is_active == 'Y') {
                    return '<span class="badge" style="background: rgba(4, 120, 87, 0.12); color: #047857; border: 1px solid rgba(4, 120, 87, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Aktif</span>';
                }
                return '<span class="badge" style="background: rgba(100, 116, 139, 0.12); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row){
                $canEdit   = auth()->user()->can('admin:master-status-karyawan:edit');
                $canDelete = auth()->user()->can('admin:master-status-karyawan:delete');

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="' . route('admin.master-status-karyawan.edit', $row->id) . '" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Status Karyawan">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    $isY = $row->is_active == 'Y';
                    $bg = $isY ? '#fef2f2' : '#f0fdf4';
                    $color = $isY ? '#dc2626' : '#16a34a';
                    $border = $isY ? '#fecaca' : '#bbf7d0';
                    $icon = $isY ? 'fas fa-trash' : 'fas fa-power-off';
                    $title = $isY ? 'Nonaktifkan / Hapus Status' : 'Aktifkan Kembali Status';

                    $btn .= '<form action="' . route('admin.master-status-karyawan.destroy', $row->id) . '" method="POST" style="display:inline;" class="form-delete">
                                ' . csrf_field() . ' ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-delete" style="background: ' . $bg . '; color: ' . $color . '; border: 1px solid ' . $border . '; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="' . $title . '">
                                    <i class="' . $icon . '"></i>
                                </button>
                            </form>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_status', 'keterangan', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-status-karyawan');
        return view('admin::master-data.status-karyawan.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-status-karyawan');

        $request->validate([
            'nama_status' => 'required|string|max:255',
            'keterangan'  => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            MasterStatusKaryawan::create([
                'id'          => Str::uuid()->toString(),
                'nama_status' => $request->nama_status,
                'keterangan'  => $request->keterangan,
                'is_active'   => 'Y'
            ]);
            
            DB::commit();
            return $this->sendSuccess('Status Karyawan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_STAT_STORE]', 'Gagal menambah status karyawan.', 'Gagal Store Status');
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-status-karyawan');
        $status = MasterStatusKaryawan::findOrFail($id);
        return view('admin::master-data.status-karyawan.edit_modal', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-status-karyawan');
        $request->validate([
            'nama_status' => 'required|string|max:255',
            'keterangan'  => 'nullable|string',
            'is_active'   => 'nullable|in:Y,N'
        ]);

        DB::beginTransaction();
        try {
            $status = MasterStatusKaryawan::findOrFail($id);
            $dataUpdate = [
                'nama_status' => $request->nama_status,
                'keterangan'  => $request->keterangan
            ];
            if ($request->has('is_active')) {
                $dataUpdate['is_active'] = $request->is_active;
            }
            $status->update($dataUpdate);
            
            DB::commit();
            return $this->sendSuccess('Status Karyawan berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_STAT_UPDATE]', 'Gagal memperbarui status karyawan.', 'Gagal Update Status');
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-status-karyawan');
        
        DB::beginTransaction();
        try {
            $status = MasterStatusKaryawan::findOrFail($id);
            $hasEmployees = DataDosenTendik::where('status_karyawan_id', $id)->exists();

            if ($hasEmployees) {
                $newStatus = $status->is_active == 'Y' ? 'N' : 'Y';
                $status->update(['is_active' => $newStatus]);
                
                $msg = $newStatus == 'Y' 
                    ? 'Status Karyawan berhasil diaktifkan kembali.' 
                    : 'Status Karyawan dinonaktifkan (data riwayat pegawai tetap terlindungi).';
                
                DB::commit();
                return $this->sendSuccess($msg);
            }

            // Jika tidak ada data pegawai yang terhubung, lakukan penghapusan tuntas
            $status->delete();
            DB::commit();
            return $this->sendSuccess('Status Karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_STAT_DESTROY]', 'Gagal menonaktifkan status karyawan.', 'Gagal Destroy Status');
        }
    }
}
