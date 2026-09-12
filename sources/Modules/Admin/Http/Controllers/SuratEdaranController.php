<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SuratEdaranSdm;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class SuratEdaranController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:persuratan-sdm');
    }

    /**
     * Halaman Arsip Surat Edaran & SK Resmi SDM
     */
    public function index()
    {
        $counts = [
            'total'            => SuratEdaranSdm::where('is_active', true)->count(),
            'edaran_libur'     => SuratEdaranSdm::where('is_active', true)->where('kategori', 'edaran_libur')->count(),
            'edaran_jam_kerja' => SuratEdaranSdm::where('is_active', true)->where('kategori', 'edaran_jam_kerja')->count(),
            'sk_rektor'        => SuratEdaranSdm::where('is_active', true)->where('kategori', 'sk_rektor')->count(),
            'kebijakan_sdm'    => SuratEdaranSdm::where('is_active', true)->where('kategori', 'kebijakan_sdm')->count(),
        ];

        return view('admin::persuratan.edaran_index', [
            'title'    => 'Pusat Surat Edaran & SK Rektorat',
            'menuIcon' => 'fas fa-newspaper',
            'counts'   => $counts,
        ]);
    }

    /**
     * DataTables Surat Edaran
     */
    public function datatable(Request $request)
    {
        $query = SuratEdaranSdm::query()->orderBy('tanggal_surat', 'desc')->orderBy('id', 'desc');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kategori_badge', function ($row) {
                return $row->kategori_badge;
            })
            ->addColumn('target_badge', function ($row) {
                return $row->target_badge;
            })
            ->addColumn('tgl_format', function ($row) {
                return $row->tanggal_surat ? $row->tanggal_surat->format('d M Y') : '-';
            })
            ->addColumn('kalender_badge', function ($row) {
                return $row->kalender_badge;
            })
            ->addColumn('unduhan', function ($row) {
                return '<span class="badge badge-light border text-muted"><i class="fas fa-download mr-1"></i> ' . number_format($row->download_count) . 'x</span>';
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<a href="' . route('admin.surat-edaran.download', $row->id) . '" target="_blank" class="btn btn-info" title="Lihat / Unduh Dokumen"><i class="fas fa-download mr-1"></i> Unduh</a>';

                // Jika user memiliki wewenang admin:persuratan-sdm:edit
                if (Auth::user() && (Auth::user()->can('admin:persuratan-sdm:edit') || Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin SDM'))) {
                    $btn .= '<button type="button" class="btn btn-outline-secondary btn-modal-edit" data-url="' . route('admin.surat-edaran.edit', $row->id) . '" title="Edit Edaran"><i class="fas fa-pencil-alt"></i></button>';
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-edaran" data-url="' . route('admin.surat-edaran.destroy', $row->id) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['kategori_badge', 'target_badge', 'kalender_badge', 'unduhan', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Form Tambah Edaran Baru
     */
    public function create()
    {
        return view('admin::persuratan.edaran_form_modal', [
            'item'   => new SuratEdaranSdm(),
            'action' => route('admin.surat-edaran.store'),
            'method' => 'POST',
            'title'  => 'Terbitkan Surat Edaran / SK Baru',
        ]);
    }

    /**
     * Simpan Surat Edaran Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat'              => 'required|string|max:150',
            'perihal'                  => 'required|string|max:255',
            'kategori'                 => 'required|string',
            'tanggal_surat'            => 'required|date',
            'tanggal_berlaku'          => 'nullable|date',
            'target_audience'          => 'required|string',
            'tampilkan_di_kalender'    => 'required|in:0,1',
            'tanggal_kalender'         => 'required_if:tampilkan_di_kalender,1|nullable|date',
            'tanggal_kalender_selesai' => 'nullable|date|after_or_equal:tanggal_kalender',
            'file_dokumen'             => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('file_dokumen');
        $cleanNomor = preg_replace('/[^A-Za-z0-9]/', '_', $request->nomor_surat);
        $filename = "SE_{$cleanNomor}_" . time() . ".pdf";
        $file->storeAs('public/surat_sdm/edaran', $filename);
        $filePath = 'storage/surat_sdm/edaran/' . $filename;

        $tampilkanKalender = $request->input('tampilkan_di_kalender') == '1';

        SuratEdaranSdm::create([
            'nomor_surat'              => $request->nomor_surat,
            'perihal'                  => $request->perihal,
            'kategori'                 => $request->kategori,
            'tanggal_surat'            => $request->tanggal_surat,
            'tanggal_berlaku'          => $request->tanggal_berlaku,
            'target_audience'          => $request->target_audience,
            'tampilkan_di_kalender'    => $tampilkanKalender,
            'tanggal_kalender'         => $tampilkanKalender ? $request->tanggal_kalender : null,
            'tanggal_kalender_selesai' => $tampilkanKalender ? $request->tanggal_kalender_selesai : null,
            'file_dokumen'             => $filePath,
            'file_size'                => $file->getSize(),
            'is_active'                => true,
            'created_by'               => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat edaran resmi berhasil diterbitkan.',
        ]);
    }

    /**
     * Modal Form Edit Edaran
     */
    public function edit($id)
    {
        $item = SuratEdaranSdm::findOrFail($id);
        return view('admin::persuratan.edaran_form_modal', [
            'item'   => $item,
            'action' => route('admin.surat-edaran.update', $item->id),
            'method' => 'PUT',
            'title'  => 'Edit Surat Edaran: ' . $item->nomor_surat,
        ]);
    }

    /**
     * Update Surat Edaran
     */
    public function update(Request $request, $id)
    {
        $item = SuratEdaranSdm::findOrFail($id);

        $request->validate([
            'nomor_surat'              => 'required|string|max:150',
            'perihal'                  => 'required|string|max:255',
            'kategori'                 => 'required|string',
            'tanggal_surat'            => 'required|date',
            'tanggal_berlaku'          => 'nullable|date',
            'target_audience'          => 'required|string',
            'tampilkan_di_kalender'    => 'required|in:0,1',
            'tanggal_kalender'         => 'required_if:tampilkan_di_kalender,1|nullable|date',
            'tanggal_kalender_selesai' => 'nullable|date|after_or_equal:tanggal_kalender',
            'file_dokumen'             => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $filePath = $item->file_dokumen;
        $fileSize = $item->file_size;

        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $cleanNomor = preg_replace('/[^A-Za-z0-9]/', '_', $request->nomor_surat);
            $filename = "SE_{$cleanNomor}_" . time() . ".pdf";
            $file->storeAs('public/surat_sdm/edaran', $filename);
            $filePath = 'storage/surat_sdm/edaran/' . $filename;
            $fileSize = $file->getSize();
        }

        $tampilkanKalender = $request->input('tampilkan_di_kalender') == '1';

        $item->update([
            'nomor_surat'              => $request->nomor_surat,
            'perihal'                  => $request->perihal,
            'kategori'                 => $request->kategori,
            'tanggal_surat'            => $request->tanggal_surat,
            'tanggal_berlaku'          => $request->tanggal_berlaku,
            'target_audience'          => $request->target_audience,
            'tampilkan_di_kalender'    => $tampilkanKalender,
            'tanggal_kalender'         => $tampilkanKalender ? $request->tanggal_kalender : null,
            'tanggal_kalender_selesai' => $tampilkanKalender ? $request->tanggal_kalender_selesai : null,
            'file_dokumen'             => $filePath,
            'file_size'                => $fileSize,
            'is_active'                => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat edaran berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus Surat Edaran
     */
    public function destroy($id)
    {
        $item = SuratEdaranSdm::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surat edaran berhasil dihapus.',
        ]);
    }

    /**
     * Unduh Berkas & Tambah Counter Unduhan
     */
    public function download($id)
    {
        $item = SuratEdaranSdm::findOrFail($id);
        $item->increment('download_count');

        if (str_starts_with($item->file_dokumen, 'http')) {
            return redirect($item->file_dokumen);
        }

        $filePath = public_path($item->file_dokumen);
        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        return redirect($item->file_url);
    }
}
