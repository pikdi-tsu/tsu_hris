<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MasterAspekSurvey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MasterAspekSurveyController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-aspek-survey');
    }

    /**
     * Halaman Utama Master Aspek Survei Kepuasan (2 Tab: Training & Layanan SDM)
     */
    public function index()
    {
        $countTraining = MasterAspekSurvey::training()->count();
        $countTrainingActive = MasterAspekSurvey::training()->active()->count();

        $countLayanan = MasterAspekSurvey::layananSdm()->count();
        $countLayananActive = MasterAspekSurvey::layananSdm()->active()->count();

        return view('admin::master-data.master-aspek-survey.index', [
            'title'               => 'Master Aspek Survei Kepuasan',
            'menuIcon'            => 'fas fa-poll-h',
            'countTraining'       => $countTraining,
            'countTrainingActive' => $countTrainingActive,
            'countLayanan'        => $countLayanan,
            'countLayananActive'  => $countLayananActive,
        ]);
    }

    /**
     * Server-side DataTables
     */
    public function datatable(Request $request)
    {
        $kategori = $request->input('kategori', 'training');

        $query = MasterAspekSurvey::where('kategori', $kategori)->ordered();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('urutan_badge', function ($row) {
                return '<span class="badge badge-light border font-weight-bold px-2 py-1" style="font-size: 0.85rem;">#' . $row->urutan . '</span>';
            })
            ->addColumn('aspek_info', function ($row) {
                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.95rem;">' . e($row->nama_aspek) . '</div>';
                if ($row->deskripsi) {
                    $html .= '<div class="small text-muted mt-1 text-wrap" style="max-width: 450px;">' . e($row->deskripsi) . '</div>';
                }
                return $html;
            })
            ->addColumn('kode_badge', function ($row) {
                return $row->kode
                    ? '<span class="badge px-2 py-1 font-weight-bold" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #38bdf8; font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.88rem;"><i class="fas fa-tag mr-1" style="font-size: 0.75rem;"></i>' . e($row->kode) . '</span>'
                    : '<span class="text-muted small">-</span>';
            })
            ->addColumn('skala_info', function ($row) {
                return '<span class="badge badge-info px-2 py-1 font-weight-bold">' . $row->skala_min . ' s/d ' . $row->skala_max . ' (Likert)</span>';
            })
            ->addColumn('status_switch', function ($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '
                    <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox" class="custom-control-input btn-toggle-status" id="switch_' . $row->id . '" data-id="' . $row->id . '" ' . $checked . '>
                        <label class="custom-control-label" for="switch_' . $row->id . '" style="cursor: pointer;"></label>
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-edit-aspek" data-id="' . $row->id . '" title="Edit Aspek"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-aspek" data-id="' . $row->id . '" data-nama="' . e($row->nama_aspek) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['urutan_badge', 'aspek_info', 'kode_badge', 'skala_info', 'status_switch', 'action'])
            ->make(true);
    }

    /**
     * Modal Form Tambah
     */
    public function create(Request $request)
    {
        $kategori = $request->input('kategori', 'training');
        $maxUrutan = MasterAspekSurvey::where('kategori', $kategori)->max('urutan') ?? 0;

        return view('admin::master-data.master-aspek-survey.form_modal', [
            'item'      => new MasterAspekSurvey([
                'kategori'  => $kategori,
                'urutan'    => $maxUrutan + 1,
                'skala_min' => 1,
                'skala_max' => 5,
                'is_active' => true,
            ]),
            'action'    => route('admin.master-aspek-survey.store'),
            'method'    => 'POST',
            'isEdit'    => false,
        ]);
    }

    /**
     * Simpan Aspek Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kategori'   => 'required|in:training,layanan_sdm',
            'nama_aspek' => 'required|string|max:255',
            'deskripsi'  => 'nullable|string|max:1000',
            'urutan'     => 'required|integer|min:1',
            'kode'       => 'nullable|string|max:50',
            'skala_min'  => 'required|integer|min:1',
            'skala_max'  => 'required|integer|min:2|max:10',
        ]);

        try {
            $user = Auth::user();
            $author = $user ? ($user->name ?? $user->email) : 'Admin';

            MasterAspekSurvey::create([
                'kategori'   => $request->kategori,
                'kode'       => $request->kode ?: Str::slug($request->nama_aspek, '_'),
                'nama_aspek' => $request->nama_aspek,
                'deskripsi'  => $request->deskripsi,
                'urutan'     => $request->urutan,
                'is_active'  => $request->has('is_active') ? (bool)$request->is_active : true,
                'skala_min'  => $request->skala_min,
                'skala_max'  => $request->skala_max,
                'created_by' => $author,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Aspek penilaian survei berhasil ditambahkan!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modal Form Edit
     */
    public function edit($id)
    {
        $item = MasterAspekSurvey::findOrFail($id);

        return view('admin::master-data.master-aspek-survey.form_modal', [
            'item'   => $item,
            'action' => route('admin.master-aspek-survey.update', $item->id),
            'method' => 'PUT',
            'isEdit' => true,
        ]);
    }

    /**
     * Update Aspek
     */
    public function update(Request $request, $id)
    {
        $item = MasterAspekSurvey::findOrFail($id);

        $request->validate([
            'kategori'   => 'required|in:training,layanan_sdm',
            'nama_aspek' => 'required|string|max:255',
            'deskripsi'  => 'nullable|string|max:1000',
            'urutan'     => 'required|integer|min:1',
            'kode'       => 'nullable|string|max:50',
            'skala_min'  => 'required|integer|min:1',
            'skala_max'  => 'required|integer|min:2|max:10',
        ]);

        try {
            $user = Auth::user();
            $author = $user ? ($user->name ?? $user->email) : 'Admin';

            $item->update([
                'kategori'   => $request->kategori,
                'kode'       => $request->kode ?: Str::slug($request->nama_aspek, '_'),
                'nama_aspek' => $request->nama_aspek,
                'deskripsi'  => $request->deskripsi,
                'urutan'     => $request->urutan,
                'is_active'  => $request->has('is_active') ? (bool)$request->is_active : true,
                'skala_min'  => $request->skala_min,
                'skala_max'  => $request->skala_max,
                'updated_by' => $author,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Aspek penilaian survei berhasil diperbarui!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle Aktif / Nonaktif
     */
    public function toggleActive($id)
    {
        try {
            $item = MasterAspekSurvey::findOrFail($id);
            $item->is_active = !$item->is_active;
            $item->save();

            $label = $item->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'status'    => 'success',
                'message'   => "Aspek '{$item->nama_aspek}' berhasil {$label}.",
                'is_active' => $item->is_active,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus Aspek
     */
    public function destroy($id)
    {
        try {
            $item = MasterAspekSurvey::findOrFail($id);
            $nama = $item->nama_aspek;
            $item->delete();

            return response()->json([
                'status'  => 'success',
                'message' => "Aspek penilaian '{$nama}' berhasil dihapus.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }
}
