<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;

use App\Models\DataDosenTendik;
use App\Models\MasterCuti;
use App\Models\CutiKaryawan;
use Modules\System\Models\MenuSidebar;

class MasterCutiController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-cuti');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $stats = [
            'total'      => MasterCuti::count(),
            'aktif'      => MasterCuti::where('is_active', '1')->count(),
            'non_aktif'  => MasterCuti::where('is_active', '0')->count(),
            'max_durasi' => MasterCuti::max('durasicuti') ?? 0,
            'avg_durasi' => round(MasterCuti::avg('durasicuti') ?? 0, 0),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-cuti.index')->value('icon') ?: 'fas fa-calendar-check';

        return view('admin::master-data.cuti.index', [
            'title'    => 'Data Master Cuti',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterCuti::query()->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('jeniscuti', function ($row) {
                return '<div class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->jeniscuti) . '</div>';
            })
            ->editColumn('durasicuti', function ($row) {
                return '<span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 0.82rem;">' . (int)$row->durasicuti . ' Hari</span>';
            })
            ->editColumn('minimalhari', function ($row) {
                return '<span class="badge badge-light border text-secondary font-weight-semibold px-2 py-1" style="font-size: 0.82rem;">H-' . (int)$row->minimalhari . ' Hari Pengajuan</span>';
            })
            ->editColumn('is_active', function ($row) {
                if ($row->is_active === '1' || $row->is_active == 1) {
                    return '<span class="badge badge-success px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">Aktif</span>';
                }
                return '<span class="badge badge-secondary px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-cuti:edit');
                $canDelete = auth()->user()->can('admin:master-cuti:delete');
                $usedInLeaves = CutiKaryawan::where('id_mcuti', $row->id)->exists();

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="' . route('admin.master-cuti.edit', $row->id) . '" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Master Cuti">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    if ($usedInLeaves) {
                        $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="Terkunci: Sudah pernah digunakan dalam riwayat cuti pegawai">
                                    <i class="fas fa-lock"></i>
                                 </button>';
                    } else {
                        $btn .= '<form action="' . route('admin.master-cuti.destroy', $row->id) . '" method="POST" style="display:inline;" class="form-delete">
                                    ' . csrf_field() . ' ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Master Cuti">
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
            ->rawColumns(['jeniscuti', 'durasicuti', 'minimalhari', 'is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-cuti');
        return view('admin::master-data.cuti.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-cuti');

        $request->validate([
            'jeniscuti'     => 'required|string|max:255',
            'durasicuti'    => 'required|integer|min:0',
            'minimalhari'   => 'required|integer|min:0',
        ]);

        try {
            $userProfile = $this->getCurrentProfile();
            $creator = $userProfile ? $userProfile->nik : (Auth::check() ? Auth::user()->name : 'System');

            MasterCuti::create([
                'jeniscuti'   => $request->jeniscuti,
                'durasicuti'  => $request->durasicuti,
                'minimalhari' => $request->minimalhari,
                'is_active'   => '1',
                'created_at'  => date("Y-m-d H:i:s"),
                'created_by'  => $creator,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Master Cuti berhasil ditambahkan.'
                ]);
            }

            return back()->with('success', 'Master Cuti Berhasil Ditambahkan.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_STORE_FAIL]',
                'Gagal menyimpan master cuti.',
                'Create Master Cuti.',
                $request
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);
        return view('admin::master-data.cuti.edit_modal', compact('cuti'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);

        $request->validate([
            'jeniscuti'   => 'required|string|max:255',
            'durasicuti'  => 'required|integer|min:0',
            'minimalhari' => 'required|integer|min:0',
            'is_active'   => 'required|in:0,1'
        ]);

        try {
            $userProfile = $this->getCurrentProfile();
            $updater = $userProfile ? $userProfile->nik : (Auth::check() ? Auth::user()->name : 'System');

            $cuti->update([
                'jeniscuti'   => $request->jeniscuti,
                'durasicuti'  => $request->durasicuti,
                'minimalhari' => $request->minimalhari,
                'is_active'   => $request->is_active,
                'updated_at'  => date("Y-m-d H:i:s"),
                'updated_by'  => $updater,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Master Cuti berhasil diperbarui.'
                ]);
            }

            return back()->with('success', 'Master Cuti Berhasil Diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_UPD_FAIL]',
                'Gagal menyimpan perubahan master cuti.',
                "Update Master Cuti ID: $id.",
                $request
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);

        try {
            if (CutiKaryawan::where('id_mcuti', $id)->exists()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Jenis cuti tidak dapat dihapus karena sudah terdapat riwayat pengajuan cuti pegawai.'
                    ], 422);
                }
                return back()->with('error', 'Jenis cuti tidak dapat dihapus karena sudah terdapat riwayat pengajuan cuti pegawai.');
            }

            $cuti->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Master Cuti berhasil dihapus.'
                ]);
            }

            return back()->with('success', 'Master Cuti Berhasil Dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_DEL_FAIL]',
                'Gagal menghapus master cuti karena masih digunakan atau kesalahan sistem.',
                "Delete Master Cuti ID: $id."
            );
        }
    }
}
