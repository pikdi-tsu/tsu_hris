<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterJabatanFungsional;
use App\Models\MasterPangkatGolongan;
use Modules\System\Models\MenuSidebar;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;

use App\Traits\ApiResponseTrait;

class MasterJabatanController extends MiddlewareController
{
    use ApiResponseTrait;
    public function __construct()
    {
        $this->registerPermissions('admin:master-jabatan');
    }

    public function index()
    {
        $stats = [
            'total_jabatan' => MasterJabatanStruktural::count() + MasterJabatanFungsional::count(),
            'struktural'    => MasterJabatanStruktural::count(),
            'fungsional'    => MasterJabatanFungsional::count(),
            'pangkat'       => MasterPangkatGolongan::count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-jabatan.index')->value('icon') ?? 'fas fa-sitemap';

        return view('admin::master-data.master-jabatan.index', [
            'title'    => 'Master Data Jabatan',
            'menu'     => 'master-jabatan',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    // =========================================================================
    // JABATAN STRUKTURAL
    // =========================================================================
    public function datatableStruktural()
    {
        $data = MasterJabatanStruktural::withCount('karyawanAktifs')->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_jabatan', function($row) {
                $badge = '';
                if ($row->is_unit_specific === 'Y') {
                    $badge = ' <span class="badge ml-1" style="background: rgba(9, 75, 84, 0.08); color: #094b54; font-size: 0.7rem; font-weight: 600; border-radius: 4px;">Unit Spesifik</span>';
                }
                return '<span class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_jabatan) . '</span>' . $badge;
            })
            ->addColumn('periode', function ($row) {
                return $row->periode_jabatan ? '<span class="badge badge-light border font-weight-600">' . $row->periode_jabatan . ' Bulan</span>' : '<span class="text-muted text-xs">-</span>';
            })
            ->editColumn('keterangan', function($row) {
                return $row->keterangan ? '<span class="text-dark" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>' : '<span class="text-muted text-xs font-italic">-</span>';
            })
            ->addColumn('jumlah_karyawan', function($row) {
                return '<span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">'.$row->karyawan_aktifs_count.' Pegawai</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-jabatan:edit');
                $canDelete = auth()->user()->can('admin:master-jabatan:delete');

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-jabatan.struktural.edit', $row->id).'" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Jabatan">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access"><i class="fas fa-lock"></i></button>';
                }

                if ($canDelete) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-jabatan.struktural.destroy', $row->id).'" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Jabatan">
                                <i class="fas fa-trash"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access"><i class="fas fa-lock"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_jabatan', 'periode', 'keterangan', 'jumlah_karyawan', 'action'])
            ->make(true);
    }

    public function createStruktural()
    {
        $this->guard('create', 'admin:master-jabatan');
        return view('admin::master-data.master-jabatan.create_struktural_modal');
    }

    public function storeStruktural(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-jabatan');

        $request->validate([
            'nama_jabatan'      => 'required|string|max:255',
            'periode_jabatan'   => 'nullable|integer|min:1',
            'keterangan'        => 'nullable|string',
            'is_unit_specific'  => 'required|in:Y,N'
        ]);

        DB::beginTransaction();
        try {
            MasterJabatanStruktural::create([
                'nama_jabatan'      => $request->nama_jabatan,
                'periode_jabatan'   => $request->periode_jabatan,
                'keterangan'        => $request->keterangan,
                'is_unit_specific'  => $request->is_unit_specific,
            ]);

            DB::commit();
            return $this->sendSuccess('Master Jabatan Struktural berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_JABATAN_STRUKTURAL_STORE]',
                'Gagal menyimpan data master jabatan struktural.',
                'Gagal Create Master Jabatan Struktural.'
            );
        }
    }

    public function editStruktural($id)
    {
        $this->guard('edit', 'admin:master-jabatan');
        $struktural = MasterJabatanStruktural::findOrFail($id);
        return view('admin::master-data.master-jabatan.edit_struktural_modal', compact('struktural'));
    }

    public function updateStruktural(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-jabatan');
        $struktural = MasterJabatanStruktural::findOrFail($id);

        $request->validate([
            'nama_jabatan'      => 'required|string|max:255',
            'periode_jabatan'   => 'nullable|integer|min:1',
            'keterangan'        => 'nullable|string',
            'is_unit_specific'  => 'required|in:Y,N'
        ]);

        DB::beginTransaction();
        try {
            $struktural->update([
                'nama_jabatan'      => $request->nama_jabatan,
                'periode_jabatan'   => $request->periode_jabatan,
                'keterangan'        => $request->keterangan,
                'is_unit_specific'  => $request->is_unit_specific,
            ]);

            DB::commit();
            return $this->sendSuccess('Data Jabatan Struktural berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_JABATAN_STRUKTURAL_UPDATE]',
                'Gagal memperbarui data master jabatan struktural.',
                "Gagal Update Master Jabatan Struktural ID: $id."
            );
        }
    }

    public function destroyStruktural($id)
    {
        $this->guard('delete', 'admin:master-jabatan');
        $struktural = MasterJabatanStruktural::findOrFail($id);

        $isInUse = \App\Models\KaryawanJabatanStruktural::where('jabatan_struktural_id', $id)->exists();
        if ($isInUse) {
            return $this->sendError('Data tidak bisa dihapus karena masih digunakan oleh Karyawan (Riwayat/Aktif).');
        }

        DB::beginTransaction();
        try {
            $struktural->delete();
            DB::commit();
            return $this->sendSuccess('Data Jabatan Struktural berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_JABATAN_STRUKTURAL_DELETE]',
                'Gagal menghapus data karena kesalahan sistem atau data sedang digunakan.',
                "Gagal Delete ID: $id."
            );
        }
    }

    // =========================================================================
    // JABATAN FUNGSIONAL
    // =========================================================================
    public function datatableFungsional()
    {
        $data = MasterJabatanFungsional::withCount('karyawanAktifs')->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_jabatan', function($row) {
                return '<span class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_jabatan) . '</span>';
            })
            ->addColumn('periode', function ($row) {
                return $row->periode_jabatan ? '<span class="badge badge-light border font-weight-600">' . $row->periode_jabatan . ' Bulan</span>' : '<span class="text-muted text-xs">-</span>';
            })
            ->editColumn('keterangan', function($row) {
                return $row->keterangan ? '<span class="text-dark" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>' : '<span class="text-muted text-xs font-italic">-</span>';
            })
            ->addColumn('jumlah_karyawan', function($row) {
                return '<span class="badge" style="background: rgba(180, 83, 9, 0.12); color: #b45309; border: 1px solid rgba(180, 83, 9, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">' . $row->karyawan_aktifs_count . ' Pegawai</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-jabatan:edit');
                $canDelete = auth()->user()->can('admin:master-jabatan:delete');

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-jabatan.fungsional.edit', $row->id).'" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Jabatan">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access"><i class="fas fa-lock"></i></button>';
                }

                if ($canDelete) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-jabatan.fungsional.destroy', $row->id).'" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Jabatan">
                                <i class="fas fa-trash"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access"><i class="fas fa-lock"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_jabatan', 'periode', 'keterangan', 'jumlah_karyawan', 'action'])
            ->make(true);
    }

    public function createFungsional()
    {
        $this->guard('create', 'admin:master-jabatan');
        return view('admin::master-data.master-jabatan.create_fungsional_modal');
    }

    public function storeFungsional(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-jabatan');

        $request->validate([
            'nama_jabatan'      => 'required|string|max:255',
            'periode_jabatan'   => 'nullable|integer|min:1',
            'keterangan'        => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            MasterJabatanFungsional::create([
                'nama_jabatan'      => $request->nama_jabatan,
                'periode_jabatan'   => $request->periode_jabatan,
                'keterangan'        => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Master Jabatan Fungsional berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_JABATAN_FUNGSIONAL_STORE]',
                'Gagal menyimpan data master jabatan fungsional.',
                'Gagal Create Master Jabatan Fungsional.'
            );
        }
    }

    public function editFungsional($id)
    {
        $this->guard('edit', 'admin:master-jabatan');
        $fungsional = MasterJabatanFungsional::findOrFail($id);
        return view('admin::master-data.master-jabatan.edit_fungsional_modal', compact('fungsional'));
    }

    public function updateFungsional(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-jabatan');
        $fungsional = MasterJabatanFungsional::findOrFail($id);

        $request->validate([
            'nama_jabatan'      => 'required|string|max:255',
            'periode_jabatan'   => 'nullable|integer|min:1',
            'keterangan'        => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $fungsional->update([
                'nama_jabatan'      => $request->nama_jabatan,
                'periode_jabatan'   => $request->periode_jabatan,
                'keterangan'        => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Data Jabatan Fungsional berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_JABATAN_FUNGSIONAL_UPDATE]',
                'Gagal memperbarui data master jabatan fungsional.',
                "Gagal Update Master Jabatan Fungsional ID: $id."
            );
        }
    }

    public function destroyFungsional($id)
    {
        $this->guard('delete', 'admin:master-jabatan');
        $fungsional = MasterJabatanFungsional::findOrFail($id);

        $isInUse = \App\Models\KaryawanJabatanFungsional::where('jabatan_fungsional_id', $id)->exists();
        if ($isInUse) {
            return $this->sendError('Data tidak bisa dihapus karena masih digunakan oleh Karyawan (Riwayat/Aktif).');
        }

        DB::beginTransaction();
        try {
            $fungsional->delete();
            DB::commit();
            return $this->sendSuccess('Data Jabatan Fungsional berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_JABATAN_FUNGSIONAL_DELETE]',
                'Gagal menghapus data karena kesalahan sistem atau data sedang digunakan.',
                "Gagal Delete ID: $id."
            );
        }
    }

    // =========================================================================
    // PANGKAT & GOLONGAN
    // =========================================================================
    public function datatablePangkat()
    {
        $data = MasterPangkatGolongan::withCount('karyawanAktifs')->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_pangkat_golongan', function($row) {
                return '<span class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_pangkat_golongan) . '</span>';
            })
            ->editColumn('keterangan', function($row) {
                return $row->keterangan ? '<span class="text-dark" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>' : '<span class="text-muted text-xs font-italic">-</span>';
            })
            ->addColumn('jumlah_karyawan', function($row) {
                return '<span class="badge" style="background: rgba(4, 120, 87, 0.12); color: #047857; border: 1px solid rgba(4, 120, 87, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">' . $row->karyawan_aktifs_count . ' Pegawai</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-jabatan:edit');
                $canDelete = auth()->user()->can('admin:master-jabatan:delete');

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-jabatan.pangkat.edit', $row->id).'" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Pangkat/Golongan">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access"><i class="fas fa-lock"></i></button>';
                }

                if ($canDelete) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-jabatan.pangkat.destroy', $row->id).'" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Pangkat/Golongan">
                                <i class="fas fa-trash"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access"><i class="fas fa-lock"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_pangkat_golongan', 'keterangan', 'jumlah_karyawan', 'action'])
            ->make(true);
    }

    public function createPangkat()
    {
        $this->guard('create', 'admin:master-jabatan');
        return view('admin::master-data.master-jabatan.create_pangkat_modal');
    }

    public function storePangkat(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-jabatan');

        $request->validate([
            'nama_pangkat_golongan' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            MasterPangkatGolongan::create([
                'nama_pangkat_golongan' => $request->nama_pangkat_golongan,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Master Pangkat Golongan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_PANGKAT_GOLONGAN_STORE]',
                'Gagal menyimpan data master pangkat golongan.',
                'Gagal Create Master Pangkat Golongan.'
            );
        }
    }

    public function editPangkat($id)
    {
        $this->guard('edit', 'admin:master-jabatan');
        $pangkat = MasterPangkatGolongan::findOrFail($id);
        return view('admin::master-data.master-jabatan.edit_pangkat_modal', compact('pangkat'));
    }

    public function updatePangkat(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-jabatan');
        $pangkat = MasterPangkatGolongan::findOrFail($id);

        $request->validate([
            'nama_pangkat_golongan' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $pangkat->update([
                'nama_pangkat_golongan' => $request->nama_pangkat_golongan,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Data Pangkat Golongan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_PANGKAT_GOLONGAN_UPDATE]',
                'Gagal memperbarui data master pangkat golongan.',
                "Gagal Update Master Pangkat Golongan ID: $id."
            );
        }
    }

    public function destroyPangkat($id)
    {
        $this->guard('delete', 'admin:master-jabatan');
        $pangkat = MasterPangkatGolongan::findOrFail($id);

        $isInUse = \App\Models\KaryawanJabatanFungsional::where('pangkat_golongan_id', $id)->exists();
        if ($isInUse) {
            return $this->sendError('Data tidak bisa dihapus karena masih digunakan oleh Karyawan (Riwayat/Aktif).');
        }

        DB::beginTransaction();
        try {
            $pangkat->delete();
            DB::commit();
            return $this->sendSuccess('Data Pangkat Golongan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_PANGKAT_GOLONGAN_DELETE]',
                'Gagal menghapus data karena kesalahan sistem atau data sedang digunakan.',
                "Gagal Delete ID: $id."
            );
        }
    }
}
