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
        $this->registerPermissions('admin:data-karyawan');
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
                    return '<span class="badge" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Onboarding</span>';
                }
                return '<span class="badge" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Offboarding</span>';
            })
            ->addColumn('sasaran_badge', function ($row) {
                return match($row->sasaran) {
                    'dosen'  => '<span class="badge" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Khusus Dosen</span>',
                    'tendik' => '<span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Khusus Tendik</span>',
                    default  => '<span class="badge" style="background: rgba(107, 114, 128, 0.1); color: #4b5563; border: 1px solid rgba(107, 114, 128, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Semua Pegawai</span>',
                };
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Aktif</span>';
                }
                return '<span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Nonaktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px;">';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-info btn-modal" data-url="' . route('admin.master-onboarding-offboarding.edit', $row->id) . '" title="Edit" style="border-radius: 6px; padding: 3px 8px;"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="' . route('admin.master-onboarding-offboarding.destroy', $row->id) . '" title="Hapus" style="border-radius: 6px; padding: 3px 8px;"><i class="fas fa-trash"></i></button>';
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
