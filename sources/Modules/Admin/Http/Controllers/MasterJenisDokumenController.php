<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MasterJenisDokumen;
use Illuminate\Support\Str;

class MasterJenisDokumenController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-jenis-dokumen');
    }

    public function index()
    {
        return view('admin::master-data.jenis-dokumen.index', [
            'title' => 'Master Jenis Dokumen Berkas',
            'menuIcon' => 'fas fa-folder-open',
        ]);
    }

    public function datatable()
    {
        $data = MasterJenisDokumen::query()->withCount('berkasKaryawans')->orderBy('urutan', 'asc')->orderBy('id', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>';
                }
                return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Non-Aktif</span>';
            })
            ->addColumn('wajib_badge', function ($row) {
                if ($row->is_wajib) {
                    return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-star mr-1"></i> Wajib</span>';
                }
                return '<span class="badge badge-light border text-muted px-2 py-1">Opsional</span>';
            })
            ->addColumn('total_terunggah', function ($row) {
                return '<span class="badge badge-info px-2 py-1"><i class="fas fa-file-alt mr-1"></i> ' . number_format($row->berkas_karyawans_count) . ' berkas</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-modal" data-url="' . route('admin.master-jenis-dokumen.edit', $row->id) . '" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
                if ($row->berkas_karyawans_count == 0) {
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete" data-url="' . route('admin.master-jenis-dokumen.destroy', $row->id) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'wajib_badge', 'total_terunggah', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin::master-data.jenis-dokumen.form_modal', [
            'item' => new MasterJenisDokumen(),
            'action' => route('admin.master-jenis-dokumen.store'),
            'method' => 'POST',
            'title' => 'Tambah Jenis Dokumen',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:150',
            'kode_dokumen' => 'nullable|string|max:50|unique:master_jenis_dokumens,kode_dokumen',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
        ]);

        $kode = $request->kode_dokumen ? Str::upper(Str::slug($request->kode_dokumen, '_')) : Str::upper(Str::slug($request->nama_dokumen, '_'));

        MasterJenisDokumen::create([
            'nama_dokumen' => $request->nama_dokumen,
            'kode_dokumen' => $kode,
            'deskripsi' => $request->deskripsi,
            'is_wajib' => $request->boolean('is_wajib'),
            'is_active' => $request->boolean('is_active', true),
            'urutan' => $request->input('urutan', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jenis dokumen berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        $item = MasterJenisDokumen::findOrFail($id);
        return view('admin::master-data.jenis-dokumen.form_modal', [
            'item' => $item,
            'action' => route('admin.master-jenis-dokumen.update', $item->id),
            'method' => 'PUT',
            'title' => 'Edit Jenis Dokumen: ' . $item->nama_dokumen,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = MasterJenisDokumen::findOrFail($id);

        $request->validate([
            'nama_dokumen' => 'required|string|max:150',
            'kode_dokumen' => 'nullable|string|max:50|unique:master_jenis_dokumens,kode_dokumen,' . $id,
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
        ]);

        $kode = $request->kode_dokumen ? Str::upper(Str::slug($request->kode_dokumen, '_')) : $item->kode_dokumen;

        $item->update([
            'nama_dokumen' => $request->nama_dokumen,
            'kode_dokumen' => $kode,
            'deskripsi' => $request->deskripsi,
            'is_wajib' => $request->boolean('is_wajib'),
            'is_active' => $request->boolean('is_active'),
            'urutan' => $request->input('urutan', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jenis dokumen berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $item = MasterJenisDokumen::withCount('berkasKaryawans')->findOrFail($id);

        if ($item->berkas_karyawans_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus jenis dokumen ini karena terdapat ' . $item->berkas_karyawans_count . ' berkas karyawan yang tertaut.',
            ], 422);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jenis dokumen berhasil dihapus.',
        ]);
    }
}
