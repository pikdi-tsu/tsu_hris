<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterPengaturanTunjangan;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterJabatanFungsional;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Traits\ApiResponseTrait;

class MasterTunjanganController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:master-tunjangan');
    }

    public function index()
    {
        $settingKeluarga = MasterPengaturanTunjangan::getSettingKeluarga();
        return view('admin::master-data.master-tunjangan.index', [
            'title'           => 'Master Data Tunjangan Pegawai',
            'settingKeluarga' => $settingKeluarga
        ]);
    }

    // =========================================================================
    // 1. TUNJANGAN STRUKTURAL (CRUD)
    // =========================================================================
    public function datatableStruktural()
    {
        $data = MasterPengaturanTunjangan::struktural()
            ->with('jabatanStruktural')
            ->orderBy('nama_tunjangan', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama_display', function ($row) {
                $nama = e($row->nama_tunjangan);
                if ($row->jabatanStruktural) {
                    return '<div><strong>' . $nama . '</strong><br><small class="text-success"><i class="fas fa-link mr-1"></i> Terhubung ke Master Jabatan</small></div>';
                }
                return '<div><strong>' . $nama . '</strong></div>';
            })
            ->addColumn('nominal_dasar_formatted', function ($row) {
                return '<span class="text-muted font-monospace" style="font-size: 0.9rem;">Rp ' . number_format($row->nominal_dasar ?: 0, 0, ',', '.') . '</span>';
            })
            ->addColumn('persen_bayar_badge', function ($row) {
                $persen = floatval($row->persen_bayar);
                $badgeClass = ($persen >= 100) ? 'badge-success' : (($persen >= 50) ? 'badge-primary' : 'badge-warning');
                return '<span class="badge ' . $badgeClass . ' px-2 py-1 font-weight-bold">' . number_format($persen, 0) . '%</span>';
            })
            ->addColumn('nominal_formatted', function ($row) {
                return '<span class="font-weight-bold text-success font-monospace" style="font-size: 0.95rem;">Rp ' . number_format($row->nominal_tunjangan ?: 0, 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('admin.master-tunjangan.struktural.edit', $row->id);
                $deleteUrl = route('admin.master-tunjangan.struktural.destroy', $row->id);

                $btnEdit = '<button type="button" class="btn btn-outline-primary btn-xs btn-modal mr-1" data-url="' . $editUrl . '">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>';
                $btnDelete = '<button type="button" class="btn btn-outline-danger btn-xs btn-delete" data-url="' . $deleteUrl . '" data-name="' . e($row->nama_tunjangan) . '">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                            </button>';

                return '<div class="d-flex justify-content-center">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['nama_display', 'nominal_dasar_formatted', 'persen_bayar_badge', 'nominal_formatted', 'action'])
            ->make(true);
    }

    public function createStruktural()
    {
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $existingIds = MasterPengaturanTunjangan::struktural()->pluck('jabatan_struktural_id')->filter()->toArray();

        return view('admin::master-data.master-tunjangan.create_struktural_modal', compact('jabatans', 'existingIds'));
    }

    public function storeStruktural(Request $request)
    {
        $request->validate([
            'jabatan_struktural_id' => 'required|exists:master_jabatan_strukturals,id',
            'nominal_dasar'         => 'required|numeric|min:0',
            'persen_bayar'          => 'required|numeric|min:0|max:100',
            'nominal_tunjangan'     => 'required|numeric|min:0',
            'keterangan'            => 'nullable|string'
        ]);

        $jabatan = MasterJabatanStruktural::findOrFail($request->jabatan_struktural_id);

        // Cek apakah sudah pernah ada tarif untuk jabatan ini
        $existing = MasterPengaturanTunjangan::struktural()
            ->where('jabatan_struktural_id', $jabatan->id)
            ->first();

        if ($existing) {
            return $this->sendError("Tarif tunjangan untuk jabatan '{$jabatan->nama_jabatan}' sudah ada. Silakan gunakan tombol Edit untuk memperbarui.");
        }

        DB::beginTransaction();
        try {
            MasterPengaturanTunjangan::create([
                'kategori'              => 'struktural',
                'nama_tunjangan'        => $jabatan->nama_jabatan,
                'jabatan_struktural_id' => $jabatan->id,
                'nominal_dasar'         => floatval($request->nominal_dasar),
                'persen_bayar'          => floatval($request->persen_bayar),
                'nominal_tunjangan'     => floatval($request->nominal_tunjangan),
                'keterangan'            => $request->keterangan,
                'is_active'             => true,
            ]);

            DB::commit();
            return $this->sendSuccess("Tunjangan untuk jabatan '{$jabatan->nama_jabatan}' berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_STRUKTURAL_STORE]',
                'Gagal menambahkan tunjangan struktural.',
                'Gagal Tambah Tunjangan Struktural'
            );
        }
    }

    public function editStruktural($id)
    {
        $tunjangan = MasterPengaturanTunjangan::findOrFail($id);
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();

        return view('admin::master-data.master-tunjangan.edit_struktural_modal', compact('tunjangan', 'jabatans'));
    }

    public function updateStruktural(Request $request, $id)
    {
        $tunjangan = MasterPengaturanTunjangan::findOrFail($id);

        $request->validate([
            'jabatan_struktural_id' => 'nullable|exists:master_jabatan_strukturals,id',
            'nominal_dasar'         => 'required|numeric|min:0',
            'persen_bayar'          => 'required|numeric|min:0|max:100',
            'nominal_tunjangan'     => 'required|numeric|min:0',
            'keterangan'            => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'nominal_dasar'     => floatval($request->nominal_dasar),
                'persen_bayar'      => floatval($request->persen_bayar),
                'nominal_tunjangan' => floatval($request->nominal_tunjangan),
                'keterangan'        => $request->keterangan,
            ];

            if ($request->filled('jabatan_struktural_id')) {
                $jabatan = MasterJabatanStruktural::find($request->jabatan_struktural_id);
                if ($jabatan) {
                    $updateData['jabatan_struktural_id'] = $jabatan->id;
                    $updateData['nama_tunjangan'] = $jabatan->nama_jabatan;
                }
            }

            $tunjangan->update($updateData);

            DB::commit();
            return $this->sendSuccess('Tarif Tunjangan Struktural berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_STRUKTURAL_UPDATE]',
                'Gagal memperbarui nominal tunjangan struktural.',
                "Gagal Update Tunjangan Struktural ID: $id."
            );
        }
    }

    public function destroyStruktural($id)
    {
        $tunjangan = MasterPengaturanTunjangan::findOrFail($id);

        DB::beginTransaction();
        try {
            $tunjangan->delete();
            DB::commit();
            return $this->sendSuccess('Tarif Tunjangan Struktural berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_STRUKTURAL_DELETE]',
                'Gagal menghapus tunjangan struktural.',
                "Gagal Hapus Tunjangan Struktural ID: $id."
            );
        }
    }

    // =========================================================================
    // 2. TUNJANGAN FUNGSIONAL (CRUD)
    // =========================================================================
    public function datatableFungsional()
    {
        $data = MasterPengaturanTunjangan::fungsional()
            ->with('jabatanFungsional')
            ->orderBy('nominal_tunjangan', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('kode_badge', function ($row) {
                return $row->kode ? '<span class="badge badge-primary px-2 py-1 font-monospace" style="font-size: 0.85rem;">' . $row->kode . '</span>' : '-';
            })
            ->addColumn('nama_display', function ($row) {
                $nama = e($row->nama_tunjangan);
                if ($row->jabatanFungsional) {
                    return '<div><strong>' . $nama . '</strong><br><small class="text-success"><i class="fas fa-link mr-1"></i> Terhubung ke Master Fungsional</small></div>';
                }
                return '<div><strong>' . $nama . '</strong></div>';
            })
            ->addColumn('nominal_formatted', function ($row) {
                return '<span class="font-weight-bold text-success font-monospace" style="font-size: 0.95rem;">Rp ' . number_format($row->nominal_tunjangan ?: 0, 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('admin.master-tunjangan.fungsional.edit', $row->id);
                $deleteUrl = route('admin.master-tunjangan.fungsional.destroy', $row->id);

                $btnEdit = '<button type="button" class="btn btn-outline-primary btn-xs btn-modal mr-1" data-url="' . $editUrl . '">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>';
                $btnDelete = '<button type="button" class="btn btn-outline-danger btn-xs btn-delete" data-url="' . $deleteUrl . '" data-name="' . e($row->nama_tunjangan) . '">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                            </button>';

                return '<div class="d-flex justify-content-center">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['kode_badge', 'nama_display', 'nominal_formatted', 'action'])
            ->make(true);
    }

    public function createFungsional()
    {
        $jabatans = MasterJabatanFungsional::orderBy('nama_jabatan', 'asc')->get();
        $existingIds = MasterPengaturanTunjangan::fungsional()->pluck('jabatan_fungsional_id')->filter()->toArray();

        return view('admin::master-data.master-tunjangan.create_fungsional_modal', compact('jabatans', 'existingIds'));
    }

    public function storeFungsional(Request $request)
    {
        $request->validate([
            'jabatan_fungsional_id' => 'required|exists:master_jabatan_fungsionals,id',
            'kode'                  => 'required|string|max:20',
            'nominal_tunjangan'     => 'required|numeric|min:0',
            'keterangan'            => 'nullable|string'
        ]);

        $jabatan = MasterJabatanFungsional::findOrFail($request->jabatan_fungsional_id);

        $existing = MasterPengaturanTunjangan::fungsional()
            ->where('jabatan_fungsional_id', $jabatan->id)
            ->first();

        if ($existing) {
            return $this->sendError("Tarif tunjangan untuk jabatan fungsional '{$jabatan->nama_jabatan}' sudah ada. Silakan gunakan tombol Edit.");
        }

        DB::beginTransaction();
        try {
            MasterPengaturanTunjangan::create([
                'kategori'              => 'fungsional',
                'kode'                  => strtoupper(trim($request->kode)),
                'nama_tunjangan'        => $jabatan->nama_jabatan,
                'jabatan_fungsional_id' => $jabatan->id,
                'nominal_dasar'         => floatval($request->nominal_tunjangan),
                'persen_bayar'          => 100.00,
                'nominal_tunjangan'     => floatval($request->nominal_tunjangan),
                'keterangan'            => $request->keterangan,
                'is_active'             => true,
            ]);

            DB::commit();
            return $this->sendSuccess("Jenjang Tunjangan Fungsional '{$jabatan->nama_jabatan}' berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_FUNGSIONAL_STORE]',
                'Gagal menambahkan tunjangan fungsional.',
                'Gagal Tambah Tunjangan Fungsional'
            );
        }
    }

    public function editFungsional($id)
    {
        $tunjangan = MasterPengaturanTunjangan::findOrFail($id);
        $jabatans = MasterJabatanFungsional::orderBy('nama_jabatan', 'asc')->get();

        return view('admin::master-data.master-tunjangan.edit_fungsional_modal', compact('tunjangan', 'jabatans'));
    }

    public function updateFungsional(Request $request, $id)
    {
        $tunjangan = MasterPengaturanTunjangan::findOrFail($id);

        $request->validate([
            'kode'                  => 'nullable|string|max:20',
            'nominal_tunjangan'     => 'required|numeric|min:0',
            'keterangan'            => 'nullable|string',
            'jabatan_fungsional_id' => 'nullable|exists:master_jabatan_fungsionals,id'
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'kode'              => $request->kode ? strtoupper(trim($request->kode)) : null,
                'nominal_dasar'     => floatval($request->nominal_tunjangan),
                'nominal_tunjangan' => floatval($request->nominal_tunjangan),
                'keterangan'        => $request->keterangan,
            ];

            if ($request->filled('jabatan_fungsional_id')) {
                $jabatan = MasterJabatanFungsional::find($request->jabatan_fungsional_id);
                if ($jabatan) {
                    $updateData['jabatan_fungsional_id'] = $jabatan->id;
                    $updateData['nama_tunjangan'] = $jabatan->nama_jabatan;
                }
            }

            $tunjangan->update($updateData);

            DB::commit();
            return $this->sendSuccess('Nominal Tunjangan Fungsional berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_FUNGSIONAL_UPDATE]',
                'Gagal memperbarui nominal tunjangan fungsional.',
                "Gagal Update Tunjangan Fungsional ID: $id."
            );
        }
    }

    public function destroyFungsional($id)
    {
        $tunjangan = MasterPengaturanTunjangan::findOrFail($id);

        DB::beginTransaction();
        try {
            $tunjangan->delete();
            DB::commit();
            return $this->sendSuccess('Tunjangan Fungsional berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_FUNGSIONAL_DELETE]',
                'Gagal menghapus tunjangan fungsional.',
                "Gagal Hapus Tunjangan Fungsional ID: $id."
            );
        }
    }

    // =========================================================================
    // 3. TUNJANGAN KELUARGA & ANAK
    // =========================================================================
    public function updateKeluarga(Request $request)
    {
        $request->validate([
            'persen_suami_istri' => 'required|numeric|min:0|max:100',
            'persen_anak'        => 'required|numeric|min:0|max:100',
            'maksimal_anak'      => 'required|integer|min:0|max:10',
            'basis_perhitungan'  => 'required|in:gaji_tetap,gaji_pokok',
            'keterangan'         => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $setting = MasterPengaturanTunjangan::getSettingKeluarga();
            $setting->update([
                'persen_suami_istri' => floatval($request->persen_suami_istri),
                'persen_anak'        => floatval($request->persen_anak),
                'maksimal_anak'      => intval($request->maksimal_anak),
                'basis_perhitungan'  => $request->basis_perhitungan,
                'keterangan'         => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Pengaturan Tunjangan Keluarga & Anak berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_TUNJANGAN_KELUARGA_UPDATE]',
                'Gagal menyimpan pengaturan tunjangan keluarga.',
                "Gagal Update Pengaturan Tunjangan Keluarga."
            );
        }
    }
}
