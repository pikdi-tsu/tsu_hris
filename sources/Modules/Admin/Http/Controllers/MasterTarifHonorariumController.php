<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterTarifHonorarium;
use App\Models\MasterJabatanFungsional;
use App\Services\TsuErrorHandlerService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class MasterTarifHonorariumController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:master-tarif-honorarium');
    }

    public function index()
    {
        return view('admin::master-data.tarif-honorarium.index', [
            'title' => 'Master Tarif Honorarium Dosen',
        ]);
    }

    public function datatable()
    {
        $data = MasterTarifHonorarium::orderByRaw("
            CASE kode_jafung
                WHEN 'TP' THEN 1
                WHEN 'AA' THEN 2
                WHEN 'L' THEN 3
                WHEN 'LK' THEN 4
                WHEN 'GB' THEN 5
                ELSE 6
            END ASC
        ");

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('jafung_badge', function ($row) {
                $nama = $row->jabatanFungsional->nama_jabatan ?? $row->nama_jafung;
                return '<div><span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 0.9rem;">' . e($row->kode_jafung) . '</span><br><strong class="text-dark">' . e($nama) . '</strong></div>';
            })
            ->addColumn('sks_lebih_formatted', function ($row) {
                return '<strong class="text-success" style="font-size: 0.95rem;">Rp ' . number_format($row->tarif_sks_hadir, 0, ',', '.') . '</strong><br><small class="text-muted">/ SKS / Pertemuan</small>';
            })
            ->addColumn('bimbing_uji_formatted', function ($row) {
                $html = '<div style="font-size: 8.5pt;" class="text-dark">';
                $html .= '<div><strong>Pembimbing TA:</strong> Rp ' . number_format($row->tarif_bimbingan_ta, 0, ',', '.') . '</div>';
                $html .= '<div><strong>Penguji TA:</strong> Rp ' . number_format($row->tarif_penguji_ta, 0, ',', '.') . '</div>';
                $html .= '<div><strong>Kerja Praktek:</strong> Rp ' . number_format($row->tarif_kerja_praktek, 0, ',', '.') . '</div>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('ujian_formatted', function ($row) {
                $html = '<div style="font-size: 8.5pt;" class="text-dark">';
                $html .= '<div><strong>Soal T:</strong> Rp ' . number_format($row->tarif_soal_teori, 0, ',', '.') . ' | <strong>T/P:</strong> Rp ' . number_format($row->tarif_soal_teori_praktik, 0, ',', '.') . '</div>';
                $html .= '<div><strong>Koreksi T:</strong> Rp ' . number_format($row->tarif_koreksi_teori, 0, ',', '.') . ' | <strong>T/P:</strong> Rp ' . number_format($row->tarif_koreksi_teori_praktik, 0, ',', '.') . '</div>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('keterangan_display', function ($row) {
                return $row->keterangan ? '<span class="text-secondary small">' . nl2br(e($row->keterangan)) . '</span>' : '<span class="text-muted font-italic">-</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-primary btn-modal mr-1" data-url="' . route('admin.master-tarif-honorarium.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i> Edit</button>';
                $btnDelete = '<button type="button" class="btn btn-xs btn-danger btn-delete" data-url="' . route('admin.master-tarif-honorarium.destroy', $row->id) . '" data-name="' . e($row->nama_jafung) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                return '<div class="text-center text-nowrap">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['jafung_badge', 'sks_lebih_formatted', 'bimbing_uji_formatted', 'ujian_formatted', 'keterangan_display', 'action'])
            ->make(true);
    }

    public function create()
    {
        $jabatans = MasterJabatanFungsional::orderBy('nama_jabatan', 'asc')->get();
        $existingIds = MasterTarifHonorarium::pluck('jabatan_fungsional_id')->filter()->toArray();
        return view('admin::master-data.tarif-honorarium.create_modal', compact('jabatans', 'existingIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jabatan_fungsional_id'     => 'nullable|exists:master_jabatan_fungsionals,id',
            'kode_jafung'               => 'required|string|max:10|unique:master_tarif_honorariums,kode_jafung',
            'nama_jafung'               => 'required|string|max:100',
            'tarif_sks_hadir'           => 'required|numeric|min:0',
            'tarif_bimbingan_ta'        => 'required|numeric|min:0',
            'tarif_penguji_ta'          => 'required|numeric|min:0',
            'tarif_kerja_praktek'       => 'required|numeric|min:0',
            'tarif_soal_teori'          => 'required|numeric|min:0',
            'tarif_soal_teori_praktik'  => 'required|numeric|min:0',
            'tarif_koreksi_teori'       => 'required|numeric|min:0',
            'tarif_koreksi_teori_praktik'=> 'required|numeric|min:0',
            'keterangan'                => 'nullable|string',
        ], [
            'kode_jafung.required' => 'Kode Jabatan Fungsional wajib diisi.',
            'kode_jafung.unique'   => 'Kode Jabatan Fungsional tersebut sudah terdaftar.',
            'nama_jafung.required' => 'Nama Jabatan Fungsional wajib diisi.',
            'tarif_sks_hadir.required' => 'Tarif SKS Lebih wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            MasterTarifHonorarium::create([
                'id'                         => (string) Str::uuid(),
                'jabatan_fungsional_id'      => $request->jabatan_fungsional_id ?: null,
                'kode_jafung'                => strtoupper(trim($request->kode_jafung)),
                'nama_jafung'                => trim($request->nama_jafung),
                'tarif_sks_hadir'            => floatval($request->tarif_sks_hadir),
                'tarif_bimbingan_ta'         => floatval($request->tarif_bimbingan_ta),
                'tarif_penguji_ta'           => floatval($request->tarif_penguji_ta),
                'tarif_kerja_praktek'        => floatval($request->tarif_kerja_praktek),
                'tarif_soal_teori'           => floatval($request->tarif_soal_teori),
                'tarif_soal_teori_praktik'   => floatval($request->tarif_soal_teori_praktik),
                'tarif_koreksi_teori'        => floatval($request->tarif_koreksi_teori),
                'tarif_koreksi_teori_praktik' => floatval($request->tarif_koreksi_teori_praktik),
                'keterangan'                 => $request->keterangan,
                'is_active'                  => 1,
            ]);

            DB::commit();
            return $this->successResponse(null, 'Master Tarif Honorarium Dosen berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, 'TSU_TARIF_STORE_ERR', 'Gagal menambahkan Master Tarif Honorarium.');
        }
    }

    public function edit($id)
    {
        $data = MasterTarifHonorarium::findOrFail($id);
        $jabatans = MasterJabatanFungsional::orderBy('nama_jabatan', 'asc')->get();
        return view('admin::master-data.tarif-honorarium.edit_modal', compact('data', 'jabatans'));
    }

    public function update(Request $request, $id)
    {
        $data = MasterTarifHonorarium::findOrFail($id);

        $request->validate([
            'jabatan_fungsional_id'     => 'nullable|exists:master_jabatan_fungsionals,id',
            'kode_jafung'               => 'required|string|max:10|unique:master_tarif_honorariums,kode_jafung,' . $id,
            'nama_jafung'               => 'required|string|max:100',
            'tarif_sks_hadir'           => 'required|numeric|min:0',
            'tarif_bimbingan_ta'        => 'required|numeric|min:0',
            'tarif_penguji_ta'          => 'required|numeric|min:0',
            'tarif_kerja_praktek'       => 'required|numeric|min:0',
            'tarif_soal_teori'          => 'required|numeric|min:0',
            'tarif_soal_teori_praktik'  => 'required|numeric|min:0',
            'tarif_koreksi_teori'       => 'required|numeric|min:0',
            'tarif_koreksi_teori_praktik'=> 'required|numeric|min:0',
            'keterangan'                => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $data->update([
                'jabatan_fungsional_id'      => $request->jabatan_fungsional_id ?: $data->jabatan_fungsional_id,
                'kode_jafung'                => strtoupper(trim($request->kode_jafung)),
                'nama_jafung'                => trim($request->nama_jafung),
                'tarif_sks_hadir'            => floatval($request->tarif_sks_hadir),
                'tarif_bimbingan_ta'         => floatval($request->tarif_bimbingan_ta),
                'tarif_penguji_ta'           => floatval($request->tarif_penguji_ta),
                'tarif_kerja_praktek'        => floatval($request->tarif_kerja_praktek),
                'tarif_soal_teori'           => floatval($request->tarif_soal_teori),
                'tarif_soal_teori_praktik'   => floatval($request->tarif_soal_teori_praktik),
                'tarif_koreksi_teori'        => floatval($request->tarif_koreksi_teori),
                'tarif_koreksi_teori_praktik' => floatval($request->tarif_koreksi_teori_praktik),
                'keterangan'                 => $request->keterangan,
            ]);

            DB::commit();
            return $this->successResponse(null, 'Master Tarif Honorarium Dosen berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, 'TSU_TARIF_UPDATE_ERR', 'Gagal memperbarui Master Tarif Honorarium.');
        }
    }

    public function destroy($id)
    {
        $data = MasterTarifHonorarium::findOrFail($id);

        DB::beginTransaction();
        try {
            $data->delete();
            DB::commit();
            return $this->successResponse(null, 'Master Tarif Honorarium Dosen berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, 'TSU_TARIF_DEL_ERR', 'Gagal menghapus Master Tarif Honorarium.');
        }
    }
}
