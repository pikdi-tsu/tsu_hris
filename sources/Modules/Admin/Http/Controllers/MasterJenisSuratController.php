<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MasterJenisSurat;
use App\Models\RequestSuratSdm;
use Illuminate\Support\Str;

class MasterJenisSuratController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-jenis-surat');
    }

    public function index()
    {
        return view('admin::master-data.master-jenis-surat.index', [
            'title' => 'Master Jenis Surat SDM',
            'menuIcon' => 'fas fa-envelope-open-text',
        ]);
    }

    public function datatable()
    {
        $data = MasterJenisSurat::query()->orderBy('urutan', 'asc')->orderBy('created_at', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('kode_badge', function ($row) {
                if (!empty($row->kode_surat)) {
                    return '<span class="badge badge-light border font-weight-bold px-2 py-1">' . e($row->kode_surat) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('lampiran_badge', function ($row) {
                if ($row->perlu_lampiran) {
                    return '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-paperclip mr-1"></i> Wajib Berkas</span>';
                }
                return '<span class="badge badge-light border text-muted px-2 py-1">Opsional</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>';
                }
                return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Non-Aktif</span>';
            })
            ->addColumn('total_diajukan', function ($row) {
                $count = RequestSuratSdm::where('jenis_surat', $row->nama_surat)->count();
                return '<span class="badge badge-info px-2 py-1"><i class="fas fa-file-alt mr-1"></i> ' . number_format($count) . ' Pengajuan</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-modal" data-url="' . route('admin.master-jenis-surat.edit', $row->id) . '" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
                
                $count = RequestSuratSdm::where('jenis_surat', $row->nama_surat)->count();
                if ($count == 0) {
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete" data-url="' . route('admin.master-jenis-surat.destroy', $row->id) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['kode_badge', 'lampiran_badge', 'status_badge', 'total_diajukan', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin::master-data.master-jenis-surat.form_modal', [
            'item' => new MasterJenisSurat(),
            'action' => route('admin.master-jenis-surat.store'),
            'method' => 'POST',
            'title' => 'Tambah Jenis Surat SDM',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_surat' => 'required|string|max:150|unique:master_jenis_surats,nama_surat',
            'kode_surat' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'perlu_lampiran' => 'required|in:0,1',
            'is_active' => 'required|in:0,1',
            'urutan' => 'nullable|integer',
        ]);

        try {
            MasterJenisSurat::create([
                'id' => (string) Str::uuid(),
                'nama_surat' => $request->nama_surat,
                'kode_surat' => $request->kode_surat ? strtoupper($request->kode_surat) : null,
                'deskripsi' => $request->deskripsi,
                'perlu_lampiran' => (int)$request->perlu_lampiran,
                'is_active' => (int)$request->is_active,
                'urutan' => (int)($request->urutan ?? 0),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis Surat SDM berhasil ditambahkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $item = MasterJenisSurat::findOrFail($id);

        return view('admin::master-data.master-jenis-surat.form_modal', [
            'item' => $item,
            'action' => route('admin.master-jenis-surat.update', $item->id),
            'method' => 'PUT',
            'title' => 'Edit Jenis Surat SDM',
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = MasterJenisSurat::findOrFail($id);

        $request->validate([
            'nama_surat' => 'required|string|max:150|unique:master_jenis_surats,nama_surat,' . $id,
            'kode_surat' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'perlu_lampiran' => 'required|in:0,1',
            'is_active' => 'required|in:0,1',
            'urutan' => 'nullable|integer',
        ]);

        try {
            $item->update([
                'nama_surat' => $request->nama_surat,
                'kode_surat' => $request->kode_surat ? strtoupper($request->kode_surat) : null,
                'deskripsi' => $request->deskripsi,
                'perlu_lampiran' => (int)$request->perlu_lampiran,
                'is_active' => (int)$request->is_active,
                'urutan' => (int)($request->urutan ?? 0),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis Surat SDM berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $item = MasterJenisSurat::findOrFail($id);

        // Pastikan tidak ada request yang menggunakannya
        $usageCount = RequestSuratSdm::where('jenis_surat', $item->nama_surat)->count();
        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis Surat tidak dapat dihapus karena sudah ada ' . $usageCount . ' riwayat permohonan surat.',
            ], 422);
        }

        try {
            $item->delete();
            return response()->json([
                'success' => true,
                'message' => 'Jenis Surat berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
