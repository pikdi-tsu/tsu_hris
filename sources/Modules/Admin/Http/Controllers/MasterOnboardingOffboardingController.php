<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MasterOnboardingOffboarding;
use App\Traits\ApiResponseTrait;

class MasterOnboardingOffboardingController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:master-data');
    }

    /**
     * Halaman Utama Master Onboarding & Offboarding
     */
    public function index()
    {
        $counts = [
            'total'       => MasterOnboardingOffboarding::count(),
            'onboarding'  => MasterOnboardingOffboarding::where('kategori', 'onboarding')->count(),
            'offboarding' => MasterOnboardingOffboarding::where('kategori', 'offboarding')->count(),
        ];

        return view('admin::master-data.onboarding-offboarding.index', [
            'title'    => 'Master Onboarding & Offboarding',
            'menuIcon' => 'fas fa-clipboard-check',
            'counts'   => $counts,
        ]);
    }

    /**
     * DataTables Data Master
     */
    public function datatable(Request $request)
    {
        $query = MasterOnboardingOffboarding::query()->orderBy('urutan', 'asc')->orderBy('id', 'asc');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kategori_badge', function ($row) {
                if ($row->kategori === 'onboarding') {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-user-plus mr-1"></i> Onboarding</span>';
                }
                return '<span class="badge badge-warning px-2 py-1"><i class="fas fa-user-minus mr-1"></i> Offboarding</span>';
            })
            ->addColumn('sasaran_badge', function ($row) {
                return match($row->sasaran) {
                    'dosen'  => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-chalkboard-teacher mr-1"></i> Khusus Dosen</span>',
                    'tendik' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-user-cog mr-1"></i> Khusus Tendik</span>',
                    default  => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-users mr-1"></i> Semua Pegawai</span>',
                };
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>';
                }
                return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-modal" data-url="' . route('admin.master-onboarding-offboarding.edit', $row->id) . '" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-outline-danger btn-delete" data-url="' . route('admin.master-onboarding-offboarding.destroy', $row->id) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['kategori_badge', 'sasaran_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Modal Form Tambah Item
     */
    public function create()
    {
        return view('admin::master-data.onboarding-offboarding.form_modal', [
            'item'   => new MasterOnboardingOffboarding(['urutan' => 0, 'is_active' => true, 'kategori' => 'onboarding', 'sasaran' => 'semua']),
            'action' => route('admin.master-onboarding-offboarding.store'),
            'method' => 'POST',
            'title'  => 'Tambah Tugas Onboarding / Offboarding',
        ]);
    }

    /**
     * Simpan Data
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_tugas' => 'required|string|max:200',
            'kategori'   => 'required|in:onboarding,offboarding',
            'sasaran'    => 'required|in:semua,dosen,tendik',
            'urutan'     => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);

        MasterOnboardingOffboarding::create([
            'nama_tugas' => $request->nama_tugas,
            'kategori'   => $request->kategori,
            'sasaran'    => $request->sasaran,
            'urutan'     => $request->urutan ?? 0,
            'keterangan' => $request->keterangan,
            'is_active'  => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil ditambahkan ke master data.',
        ]);
    }

    /**
     * Modal Form Edit Item
     */
    public function edit($id)
    {
        $item = MasterOnboardingOffboarding::findOrFail($id);
        return view('admin::master-data.onboarding-offboarding.form_modal', [
            'item'   => $item,
            'action' => route('admin.master-onboarding-offboarding.update', $item->id),
            'method' => 'PUT',
            'title'  => 'Edit Tugas: ' . $item->nama_tugas,
        ]);
    }

    /**
     * Update Data
     */
    public function update(Request $request, $id)
    {
        $item = MasterOnboardingOffboarding::findOrFail($id);

        $request->validate([
            'nama_tugas' => 'required|string|max:200',
            'kategori'   => 'required|in:onboarding,offboarding',
            'sasaran'    => 'required|in:semua,dosen,tendik',
            'urutan'     => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $item->update([
            'nama_tugas' => $request->nama_tugas,
            'kategori'   => $request->kategori,
            'sasaran'    => $request->sasaran,
            'urutan'     => $request->urutan ?? 0,
            'keterangan' => $request->keterangan,
            'is_active'  => $request->has('is_active') ? (bool) $request->is_active : false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas master berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus Data
     */
    public function destroy($id)
    {
        $item = MasterOnboardingOffboarding::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas master berhasil dihapus.',
        ]);
    }
}
