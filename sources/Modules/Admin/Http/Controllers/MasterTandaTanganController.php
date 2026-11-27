<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MasterTandaTangan;
use App\Models\DataDosenTendik;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MasterTandaTanganController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-tanda-tangan');
    }

    public function index()
    {
        $totalTtd = MasterTandaTangan::count();
        $totalAktif = MasterTandaTangan::where('is_active', 1)->count();
        $totalSlipGaji = MasterTandaTangan::whereIn('peruntukan', ['semua', 'slip_gaji'])->where('is_active', 1)->count();
        $totalSlipThrHonor = MasterTandaTangan::whereIn('peruntukan', ['semua', 'slip_thr', 'slip_honorarium'])->where('is_active', 1)->count();

        return view('admin::master-data.master-tanda-tangan.index', [
            'title'             => 'Master Tanda Tangan & Stempel Digital',
            'menuIcon'          => 'fas fa-signature',
            'totalTtd'          => $totalTtd,
            'totalAktif'        => $totalAktif,
            'totalSlipGaji'     => $totalSlipGaji,
            'totalSlipThrHonor' => $totalSlipThrHonor,
        ]);
    }

    public function datatable(Request $request)
    {
        $query = MasterTandaTangan::with('karyawan')->select('master_tanda_tangans.*');

        if ($request->filled('peruntukan') && $request->peruntukan !== 'all') {
            $query->where('peruntukan', $request->peruntukan);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', (int)$request->status);
        }

        $query->orderBy('urutan', 'asc')->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pejabat_info', function ($row) {
                $html = '<div class="d-flex align-items-center">';
                $html .= '<div class="rounded-circle d-flex align-items-center justify-content-center mr-2 text-white font-weight-bold" style="width: 36px; height: 36px; background: linear-gradient(135deg, #094b54, #0c6170); font-size: 0.85rem; flex-shrink: 0;">';
                $html .= strtoupper(substr($row->nama_pejabat, 0, 1));
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<div class="font-weight-bold text-dark" style="font-size: 0.9rem;">' . e($row->nama_pejabat) . '</div>';
                $sub = [];
                if (!empty($row->nip_nik)) $sub[] = 'NIP/NIK: ' . e($row->nip_nik);
                if (!empty($row->kode)) $sub[] = 'Kode: ' . e($row->kode);
                $html .= '<small class="text-muted">' . implode(' &bull; ', $sub) . '</small>';
                $html .= '</div></div>';
                return $html;
            })
            ->addColumn('jabatan_badge', function ($row) {
                return '<div class="font-weight-600 text-dark" style="font-size: 0.85rem;">' . e($row->jabatan) . '</div>';
            })
            ->addColumn('peruntukan_badge', function ($row) {
                $badgeClass = match ($row->peruntukan) {
                    'slip_gaji'        => 'badge-primary',
                    'slip_thr'         => 'badge-success',
                    'slip_honorarium'  => 'badge-warning text-dark',
                    'surat_sdm'        => 'badge-info',
                    default            => 'badge-dark',
                };
                return '<span class="badge ' . $badgeClass . ' px-2 py-1 font-weight-bold" style="font-size: 0.78rem;">' . e($row->peruntukan_label) . '</span>';
            })
            ->addColumn('preview_ttd', function ($row) {
                if ($row->file_ttd_url) {
                    return '<div class="text-center">
                        <img src="' . $row->file_ttd_url . '" alt="TTD" class="img-thumbnail bg-white border" style="max-height: 42px; max-width: 90px; object-fit: contain; cursor: pointer;" onclick="previewImageLarge(\'' . $row->file_ttd_url . '\', \'Tanda Tangan - ' . e($row->nama_pejabat) . '\')">
                    </div>';
                }
                return '<div class="text-center text-muted small"><span class="badge badge-light border">Belum Ada TTD</span></div>';
            })
            ->addColumn('preview_stempel', function ($row) {
                if ($row->file_stempel_url) {
                    return '<div class="text-center">
                        <img src="' . $row->file_stempel_url . '" alt="Stempel" class="img-thumbnail bg-white border" style="max-height: 42px; max-width: 60px; object-fit: contain; cursor: pointer;" onclick="previewImageLarge(\'' . $row->file_stempel_url . '\', \'Stempel Resmi - ' . e($row->nama_pejabat) . '\')">
                    </div>';
                }
                return '<div class="text-center text-muted small">-</div>';
            })
            ->addColumn('status_toggle', function ($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '<div class="custom-control custom-switch text-center">
                    <input type="checkbox" class="custom-control-input chk-toggle-active" id="switch-active-' . $row->id . '" data-id="' . $row->id . '" ' . $checked . '>
                    <label class="custom-control-label cursor-pointer" for="switch-active-' . $row->id . '"></label>
                </div>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-secondary btn-preview-modal" data-url="' . route('admin.master-tanda-tangan.preview', $row->id) . '" title="Preview Lengkap"><i class="fas fa-eye"></i></button>';
                $btn .= '<button type="button" class="btn btn-outline-info btn-modal" data-url="' . route('admin.master-tanda-tangan.edit', $row->id) . '" title="Edit"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-outline-danger btn-delete" data-url="' . route('admin.master-tanda-tangan.destroy', $row->id) . '" data-name="' . e($row->nama_pejabat) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['pejabat_info', 'jabatan_badge', 'peruntukan_badge', 'preview_ttd', 'preview_stempel', 'status_toggle', 'action'])
            ->make(true);
    }

    public function create()
    {
        $karyawans = DataDosenTendik::where('is_active', 1)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nik', 'nip', 'posisi', 'tipe_karyawan']);

        return view('admin::master-data.master-tanda-tangan.form_modal', [
            'item'      => new MasterTandaTangan(['is_active' => 1, 'peruntukan' => 'semua']),
            'karyawans' => $karyawans,
            'action'    => route('admin.master-tanda-tangan.store'),
            'method'    => 'POST',
            'title'     => 'Tambah Master Tanda Tangan & Stempel',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pejabat'   => 'required|string|max:150',
            'jabatan'        => 'required|string|max:150',
            'karyawan_id'    => 'nullable|exists:data_dosen_tendiks,id',
            'nip_nik'        => 'nullable|string|max:50',
            'peruntukan'     => 'required|in:semua,slip_gaji,slip_thr,slip_honorarium,surat_sdm',
            'file_ttd'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'file_stempel'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'keterangan'     => 'nullable|string',
            'is_active'      => 'required|in:0,1',
        ]);

        $fileTtdPath = null;
        if ($request->hasFile('file_ttd')) {
            $ext = $request->file('file_ttd')->getClientOriginalExtension();
            $filename = 'ttd_' . Str::slug($request->nama_pejabat) . '_' . time() . '.' . $ext;
            $fileTtdPath = $request->file('file_ttd')->storeAs('tanda_tangan', $filename, 'public');
        }

        $fileStempelPath = null;
        if ($request->hasFile('file_stempel')) {
            $ext = $request->file('file_stempel')->getClientOriginalExtension();
            $filename = 'stempel_' . Str::slug($request->nama_pejabat) . '_' . time() . '.' . $ext;
            $fileStempelPath = $request->file('file_stempel')->storeAs('tanda_tangan', $filename, 'public');
        }

        MasterTandaTangan::create([
            'karyawan_id'    => $request->karyawan_id,
            'kode'           => null,
            'nama_pejabat'   => $request->nama_pejabat,
            'nip_nik'        => $request->nip_nik,
            'jabatan'        => $request->jabatan,
            'peran_approval' => null,
            'peruntukan'     => $request->peruntukan,
            'file_ttd'       => $fileTtdPath,
            'file_stempel'   => $fileStempelPath,
            'keterangan'     => $request->keterangan,
            'urutan'         => 0,
            'is_active'      => (int)$request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Master Tanda Tangan & Stempel berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        $item = MasterTandaTangan::findOrFail($id);
        $karyawans = DataDosenTendik::where('is_active', 1)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nik', 'nip', 'posisi', 'tipe_karyawan']);

        return view('admin::master-data.master-tanda-tangan.form_modal', [
            'item'      => $item,
            'karyawans' => $karyawans,
            'action'    => route('admin.master-tanda-tangan.update', $item->id),
            'method'    => 'PUT',
            'title'     => 'Edit Master Tanda Tangan & Stempel',
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = MasterTandaTangan::findOrFail($id);

        $request->validate([
            'nama_pejabat'   => 'required|string|max:150',
            'jabatan'        => 'required|string|max:150',
            'karyawan_id'    => 'nullable|exists:data_dosen_tendiks,id',
            'nip_nik'        => 'nullable|string|max:50',
            'peruntukan'     => 'required|in:semua,slip_gaji,slip_thr,slip_honorarium,surat_sdm',
            'file_ttd'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'file_stempel'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'keterangan'     => 'nullable|string',
            'is_active'      => 'required|in:0,1',
        ]);

        $fileTtdPath = $item->file_ttd;
        if ($request->hasFile('file_ttd')) {
            if ($item->file_ttd && Storage::disk('public')->exists($item->file_ttd)) {
                Storage::disk('public')->delete($item->file_ttd);
            }
            $ext = $request->file('file_ttd')->getClientOriginalExtension();
            $filename = 'ttd_' . Str::slug($request->nama_pejabat) . '_' . time() . '.' . $ext;
            $fileTtdPath = $request->file('file_ttd')->storeAs('tanda_tangan', $filename, 'public');
        }

        $fileStempelPath = $item->file_stempel;
        if ($request->hasFile('file_stempel')) {
            if ($item->file_stempel && Storage::disk('public')->exists($item->file_stempel)) {
                Storage::disk('public')->delete($item->file_stempel);
            }
            $ext = $request->file('file_stempel')->getClientOriginalExtension();
            $filename = 'stempel_' . Str::slug($request->nama_pejabat) . '_' . time() . '.' . $ext;
            $fileStempelPath = $request->file('file_stempel')->storeAs('tanda_tangan', $filename, 'public');
        }

        $item->update([
            'karyawan_id'    => $request->karyawan_id,
            'nama_pejabat'   => $request->nama_pejabat,
            'nip_nik'        => $request->nip_nik,
            'jabatan'        => $request->jabatan,
            'peran_approval' => null,
            'peruntukan'     => $request->peruntukan,
            'file_ttd'       => $fileTtdPath,
            'file_stempel'   => $fileStempelPath,
            'keterangan'     => $request->keterangan,
            'urutan'         => 0,
            'is_active'      => (int)$request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Master Tanda Tangan & Stempel berhasil diperbarui.',
        ]);
    }

    public function toggleActive($id)
    {
        $item = MasterTandaTangan::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();

        return response()->json([
            'success'   => true,
            'is_active' => $item->is_active,
            'message'   => 'Status aktif tanda tangan ' . $item->nama_pejabat . ' berhasil diubah menjadi ' . ($item->is_active ? 'Aktif' : 'Non-Aktif') . '.',
        ]);
    }

    public function previewModal($id)
    {
        $item = MasterTandaTangan::with('karyawan')->findOrFail($id);

        return view('admin::master-data.master-tanda-tangan.preview_modal', compact('item'));
    }

    public function destroy($id)
    {
        $item = MasterTandaTangan::findOrFail($id);

        if ($item->file_ttd && Storage::disk('public')->exists($item->file_ttd)) {
            Storage::disk('public')->delete($item->file_ttd);
        }
        if ($item->file_stempel && Storage::disk('public')->exists($item->file_stempel)) {
            Storage::disk('public')->delete($item->file_stempel);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Master Tanda Tangan berhasil dihapus.',
        ]);
    }
}
