<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;

use App\Models\MasterIzin;
use App\Models\IzinKaryawan;
use App\Models\DataDosenTendik;
use Modules\System\Models\MenuSidebar;

class MasterIzinController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-izin');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $stats = [
            'total'           => MasterIzin::count(),
            'aktif'           => MasterIzin::where('is_active', '1')->count(),
            'non_aktif'       => MasterIzin::where('is_active', '0')->count(),
            'total_pengajuan' => IzinKaryawan::count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-izin.index')->value('icon') ?: 'fas fa-envelope-open-text';

        return view('admin::master-data.izin.index', [
            'title'    => 'Data Master Izin',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterIzin::query()->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('jenisizin', function ($row) {
                return '<div class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->jenisizin) . '</div>';
            })
            ->editColumn('is_active', function ($row) {
                if ($row->is_active === '1' || $row->is_active == 1) {
                    return '<span class="badge badge-success px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">Aktif</span>';
                }
                return '<span class="badge badge-secondary px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-izin:edit');
                $canDelete = auth()->user()->can('admin:master-izin:delete');
                $usedInLeaves = IzinKaryawan::where('id_mizin', $row->id)->exists();

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="' . route('admin.master-izin.edit', $row->id) . '" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Master Izin">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    if ($usedInLeaves) {
                        $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="Terkunci: Sudah pernah digunakan dalam riwayat permohonan izin pegawai">
                                    <i class="fas fa-lock"></i>
                                 </button>';
                    } else {
                        $btn .= '<form action="' . route('admin.master-izin.destroy', $row->id) . '" method="POST" style="display:inline;" class="form-delete">
                                    ' . csrf_field() . ' ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Master Izin">
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
            ->rawColumns(['jenisizin', 'is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-izin');
        return view('admin::master-data.izin.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-izin');

        $request->validate([
            'jenisizin' => 'required|string|max:255',
        ]);

        try {
            $userProfile = $this->getCurrentProfile();
            $creator = $userProfile ? $userProfile->nik : (Auth::check() ? Auth::user()->name : 'System');

            MasterIzin::create([
                'jenisizin'  => $request->jenisizin,
                'is_active'  => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => $creator,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Master Izin berhasil ditambahkan.'
                ]);
            }

            return back()->with('success', 'Master Izin Berhasil Ditambahkan.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_IZIN_STORE_FAIL]',
                'Gagal menyimpan master izin.',
                'Create Master Izin.',
                $request
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-izin');
        $izin = MasterIzin::findOrFail($id);
        return view('admin::master-data.izin.edit_modal', compact('izin'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-izin');
        $izin = MasterIzin::findOrFail($id);

        $request->validate([
            'jenisizin' => 'required|string|max:255',
            'is_active' => 'required|in:0,1'
        ]);

        try {
            $userProfile = $this->getCurrentProfile();
            $updater = $userProfile ? $userProfile->nik : (Auth::check() ? Auth::user()->name : 'System');

            $izin->update([
                'jenisizin'  => $request->jenisizin,
                'is_active'  => $request->is_active,
                'updated_at' => date("Y-m-d H:i:s"),
                'updated_by' => $updater,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Master Izin berhasil diperbarui.'
                ]);
            }

            return back()->with('success', 'Master Izin berhasil diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_IZIN_UPD_FAIL]',
                'Gagal menyimpan perubahan master izin.',
                "Update Master Izin ID: $id.",
                $request
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-izin');
        $izin = MasterIzin::findOrFail($id);

        try {
            if (IzinKaryawan::where('id_mizin', $id)->exists()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Jenis izin tidak dapat dihapus karena sudah terdapat riwayat pengajuan izin pegawai.'
                    ], 422);
                }
                return back()->with('error', 'Jenis izin tidak dapat dihapus karena sudah terdapat riwayat pengajuan izin pegawai.');
            }

            $izin->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Master Izin berhasil dihapus.'
                ]);
            }

            return back()->with('success', 'Master Izin berhasil dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_IZIN_DEL_FAIL]',
                'Gagal menghapus master izin karena masih digunakan atau kesalahan sistem.',
                "Delete Master Izin ID: $id."
            );
        }
    }
}
