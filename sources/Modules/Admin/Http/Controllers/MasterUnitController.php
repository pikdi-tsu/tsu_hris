<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterUnit;
use App\Models\MasterJabatanStruktural;
use App\Models\DataDosenTendik;
use App\Models\KaryawanJabatanStruktural;
use Modules\System\Models\MenuSidebar;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Traits\ApiResponseTrait;

class MasterUnitController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:master-unit');
    }

    public function index()
    {
        $stats = [
            'total'       => MasterUnit::count(),
            'induk'       => MasterUnit::whereNull('parent_unit_id')->count(),
            'sub_unit'    => MasterUnit::whereNotNull('parent_unit_id')->count(),
            'with_kepala' => MasterUnit::whereNotNull('kepala_jabatan_id')->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-unit.index')->value('icon') ?: 'fas fa-building';

        return view('admin::master-data.unit.index', [
            'title'    => 'Master Data Unit',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterUnit::with(['kepalaJabatan', 'parent'])->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_unit', function ($row) {
                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_unit) . '</div>';
                if ($row->kuota_mpp > 0) {
                    $html .= '<small class="text-muted d-block" style="font-size: 0.75rem;">Kuota MPP: ' . (int)$row->kuota_mpp . ' Pegawai</small>';
                }
                return $html;
            })
            ->editColumn('unit_induk', function ($row) {
                if ($row->parent) {
                    return '<span class="badge badge-light border text-dark font-weight-semibold px-2 py-1" style="font-size: 0.82rem;">' . e($row->parent->nama_unit) . '</span>';
                }
                return '<span class="badge badge-light border text-muted px-2 py-1" style="font-size: 0.78rem;">Unit Tingkat Tertinggi</span>';
            })
            ->editColumn('keterangan', function ($row) {
                return $row->keterangan ? '<span class="text-secondary" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>' : '<span class="text-muted">-</span>';
            })
            ->editColumn('kepala_unit', function ($row) {
                if ($row->kepalaJabatan) {
                    return '<span class="font-weight-bold text-dark" style="font-size: 0.85rem;">' . e($row->kepalaJabatan->nama_jabatan) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-unit:edit');
                $canDelete = auth()->user()->can('admin:master-unit:delete');
                $hasChildren = MasterUnit::where('parent_unit_id', $row->id)->exists();
                $hasEmployees = DataDosenTendik::where('unit_id', $row->id)->exists()
                    || KaryawanJabatanStruktural::where('unit_id', $row->id)->exists();

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="' . route('admin.master-unit.edit', $row->id) . '" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Master Unit">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    if ($hasChildren || $hasEmployees) {
                        $reason = $hasChildren ? 'Terkunci: Memiliki sub-unit di bawahnya' : 'Terkunci: Masih digunakan oleh data pegawai';
                        $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="' . $reason . '">
                                    <i class="fas fa-lock"></i>
                                 </button>';
                    } else {
                        $btn .= '<form action="' . route('admin.master-unit.destroy', $row->id) . '" method="POST" style="display:inline;" class="form-delete">
                                    ' . csrf_field() . ' ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Master Unit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';
                    }
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_unit', 'unit_induk', 'keterangan', 'kepala_unit', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-unit');
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $parentUnits = MasterUnit::orderBy('nama_unit', 'asc')->get();
        return view('admin::master-data.unit._modal', compact('jabatans', 'parentUnits'));
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-unit');

        $request->validate([
            'nama_unit'         => 'required|string|max:255',
            'keterangan'        => 'nullable|string',
            'kepala_jabatan_id' => 'nullable|exists:master_jabatan_strukturals,id',
            'parent_unit_id'    => 'nullable|exists:master_units,id',
            'kuota_mpp'         => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $kepalaJabatanId = $request->kepala_jabatan_id;

            if ($request->has('auto_create_jabatan') && $request->auto_create_jabatan == '1') {
                $jabatan = MasterJabatanStruktural::create([
                    'nama_jabatan'     => 'Kepala ' . $request->nama_unit,
                    'is_unit_specific' => 'Y'
                ]);
                $kepalaJabatanId = $jabatan->id;
            }

            MasterUnit::create([
                'nama_unit'         => $request->nama_unit,
                'keterangan'        => $request->keterangan,
                'kepala_jabatan_id' => $kepalaJabatanId,
                'parent_unit_id'    => $request->parent_unit_id,
                'kuota_mpp'         => $request->kuota_mpp ?? 0
            ]);

            DB::commit();
            return $this->sendSuccess('Master Unit berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_MASTER_UNIT_STORE]',
                'Gagal menyimpan data master unit.',
                'Gagal Create Master Unit.'
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-unit');
        $unit = MasterUnit::findOrFail($id);
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $parentUnits = MasterUnit::where('id', '!=', $id)->orderBy('nama_unit', 'asc')->get();
        return view('admin::master-data.unit._modal', compact('unit', 'jabatans', 'parentUnits'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-unit');
        $unit = MasterUnit::findOrFail($id);

        $request->validate([
            'nama_unit'         => 'required|string|max:255',
            'keterangan'        => 'nullable|string',
            'kepala_jabatan_id' => 'nullable|exists:master_jabatan_strukturals,id',
            'parent_unit_id'    => 'nullable|exists:master_units,id',
            'kuota_mpp'         => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $unit->update([
                'nama_unit'         => $request->nama_unit,
                'keterangan'        => $request->keterangan,
                'kepala_jabatan_id' => $request->kepala_jabatan_id,
                'parent_unit_id'    => $request->parent_unit_id,
                'kuota_mpp'         => $request->kuota_mpp ?? 0
            ]);

            DB::commit();
            return $this->sendSuccess('Data Master Unit berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_MASTER_UNIT_UPDATE]',
                'Gagal memperbarui data master unit.',
                "Gagal Update Master Unit ID: $id."
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-unit');
        $unit = MasterUnit::findOrFail($id);

        if (MasterUnit::where('parent_unit_id', $id)->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unit tidak dapat dihapus karena masih memiliki sub-unit di bawahnya.'
            ], 422);
        }

        if (DataDosenTendik::where('unit_id', $id)->exists() || KaryawanJabatanStruktural::where('unit_id', $id)->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unit tidak dapat dihapus karena sedang digunakan oleh data kepegawaian.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $unit->delete();
            DB::commit();
            return $this->sendSuccess('Data Master Unit berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_MASTER_UNIT_DELETE]',
                'Gagal menghapus data karena kesalahan sistem atau data sedang digunakan.',
                "Gagal Delete Unit ID: $id."
            );
        }
    }
}
