<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterJabatanFungsional;
use App\Models\MasterPangkatGolongan;
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
        return view('admin::master-data.master-jabatan.index', ['title' => 'Master Data Jabatan']);
    }

    // =========================================================================
    // JABATAN STRUKTURAL
    // =========================================================================
    public function datatableStruktural()
    {
        $data = MasterJabatanStruktural::query()->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('periode', function ($row) {
                return $row->periode_jabatan ? $row->periode_jabatan . ' Bulan' : '-';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-jabatan', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-jabatan.struktural.edit', $row->id),
                    'delete_url' => route('admin.master-jabatan.struktural.destroy', $row->id),
                ]);
            })
            ->rawColumns(['action'])
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
            'nama_jabatan' => 'required|string|max:255',
            'periode_jabatan' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            MasterJabatanStruktural::create([
                'nama_jabatan' => $request->nama_jabatan,
                'periode_jabatan' => $request->periode_jabatan,
                'keterangan' => $request->keterangan,
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
            'nama_jabatan' => 'required|string|max:255',
            'periode_jabatan' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $struktural->update([
                'nama_jabatan' => $request->nama_jabatan,
                'periode_jabatan' => $request->periode_jabatan,
                'keterangan' => $request->keterangan,
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
        $data = MasterJabatanFungsional::query()->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('periode', function ($row) {
                return $row->periode_jabatan ? $row->periode_jabatan . ' Bulan' : '-';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-jabatan', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-jabatan.fungsional.edit', $row->id),
                    'delete_url' => route('admin.master-jabatan.fungsional.destroy', $row->id),
                ]);
            })
            ->rawColumns(['action'])
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
            'nama_jabatan' => 'required|string|max:255',
            'periode_jabatan' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            MasterJabatanFungsional::create([
                'nama_jabatan' => $request->nama_jabatan,
                'periode_jabatan' => $request->periode_jabatan,
                'keterangan' => $request->keterangan,
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
            'nama_jabatan' => 'required|string|max:255',
            'periode_jabatan' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $fungsional->update([
                'nama_jabatan' => $request->nama_jabatan,
                'periode_jabatan' => $request->periode_jabatan,
                'keterangan' => $request->keterangan,
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
        $data = MasterPangkatGolongan::query()->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-jabatan', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-jabatan.pangkat.edit', $row->id),
                    'delete_url' => route('admin.master-jabatan.pangkat.destroy', $row->id),
                ]);
            })
            ->rawColumns(['action'])
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
