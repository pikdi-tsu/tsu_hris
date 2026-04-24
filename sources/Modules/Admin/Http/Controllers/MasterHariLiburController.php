<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MiddlewareController;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Entities\MasterHariLibur;
use Yajra\DataTables\Facades\DataTables;

class MasterHariLiburController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:hari-libur');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin::master-data.hari-libur.index', ['title' => 'Data Master Hari Libur']);
    }

    public function datatable()
    {
        // Tarik data mentah
        $data = MasterHariLibur::query()->orderBy('tanggal', 'desc');

        return DataTables::of($data)
            ->addIndexColumn() // Bikin nomor urut otomatis (DT_RowIndex)
            ->editColumn('tanggal', function($row) {
                // Format tanggal ala Indonesia
                return tglIndo($row->tanggal);
            })
            ->addColumn('status_libur', function($row) {
                // Rakit badge dari backend
                if($row->status_libur === 'Nasional') return '<span class="badge badge-danger">Nasional</span>';
                if($row->status_libur === 'Cuti Bersama') return '<span class="badge badge-warning">Cuti Bersama</span>';
                return '<span class="badge badge-info">Institusi</span>';
            })
            ->addColumn('isactive', function($row) {
                if($row->isactive === 'Y') return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>';
                return '<span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                // Cek apakah ini libur nasional
                $isNasional = $row->status_libur === 'Nasional';

                return $this->getActionButtons($row, 'admin:hari-libur', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.hari-libur.edit', $row->id),
                    'can_delete' => $isNasional ? false : null,
                    'delete_url' => route('admin.hari-libur.destroy', $row->id),
                ]);
            })
            ->rawColumns(['status_libur', 'isactive', 'action'])
            ->make(true);
    }

    public function syncForm(Request $request)
    {
        $this->guardStore($request->id, 'admin:hari-libur');


        return view('admin::master-data.hari-libur.sync_modal');
    }

    public function syncApi(Request $request)
    {
        $this->guardStore($request->id, 'admin:hari-libur');

        try {
            $tahun = date('Y'); // Ambil tahun saat ini berjalan (2026)

            if ($request->opsi_tahun === 'tahun_depan') {
                $tahun = date('Y', strtotime('+1 year'));
            } elseif ($request->opsi_tahun === 'custom') {
                // Validasi agar input custom benar-benar angka tahun
                $request->validate(['tahun_custom' => 'required|numeric|digits:4']);
                $tahun = $request->tahun_custom;
            }

            // Hit API ke penyedia data libur nasional
            $response = Http::timeout(15)->get("https://libur.deno.dev/api?year={$tahun}");

            if ($response->successful()) {
                $liburNasional = $response->json();

                // Looping dan simpan/update ke database lokal
                foreach ($liburNasional as $libur) {
                    MasterHariLibur::updateOrCreate(
                        ['tanggal' => $libur['date']], // Patokan unik
                        [
                            'keterangan'   => $libur['name'],
                            'status_libur' => 'nasional', // Sesuaikan dengan value Enum di databasemu!
                            'isactive'     => 'Y',        // Sesuaikan dengan value Enum aktif di databasemu (misal 'Y', '1', atau 'Aktif')
                            'created_by'   => auth()->check() ? auth()->user()->name : 'System API',
                            'updated_by'   => auth()->check() ? auth()->user()->name : 'System API',
                        ]
                    );
                }

                // Balik ke halaman sebelumnya bawa notif sukses
                return redirect()->back()->with('success', "Mantap! Data libur nasional tahun {$tahun} berhasil disinkronisasi.");
            }

            return redirect()->back()->with('error', 'Waduh, gagal terhubung ke server API penyedia data libur.');

        } catch (\Exception $e) {
            Log::error('Error Sync API Libur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat sinkronisasi data.');
        }
    }

    public function create()
    {
        $this->guard('create', 'admin:hari-libur');

        // Langsung return view form modalnya
        return view('admin::master-data.hari-libur.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:hari-libur');

        $request->validate([
            'tanggal'      => 'required|date|unique:master_harilibur,tanggal',
            'keterangan'   => 'required|string|max:255',
            'status_libur' => 'required|in:Nasional,Institusi,Cuti Bersama'
        ]);

        try {
            // Eksekusi create via Eloquent murni
            MasterHariLibur::create([
                'tanggal'      => $request->tanggal,
                'keterangan'   => $request->keterangan,
                'status_libur' => $request->status_libur,
                'isactive'     => 'Y', // Default selalu aktif saat baru dibuat
                'created_by'   => auth()->check() ? auth()->user()->name : 'System',
                'updated_by'   => auth()->check() ? auth()->user()->name : 'System',
            ]);

            return back()->with('success', 'Mantap! Hari libur institusi berhasil ditambahkan.');

        } catch (\Exception $e) {
            // Error Handling ala PIKDI TSU
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_LIBUR_STORE_FAIL]";
            $userMsg    = "Gagal menyimpan data libur baru.";

            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = trim(str_replace($errorCode, '', $rawMessage));
            }

            Log::error("$errorCode Gagal Create Libur.", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $finalErrorMsg = "<div class='text-center'>
                                <h4 class='text-bold text-danger mb-2'>$errorCode</h4>
                                <p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>
                                <p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>
                              </div>";

            return back()->withInput($request->all())->with('error', $finalErrorMsg);
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:hari-libur');

        // Pastikan narik berdasarkan UUID
        $libur = MasterHariLibur::findOrFail($id);

        // Return view spesifik untuk modal TSU
        return view('admin::master-data.hari-libur.edit_modal', compact('libur'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:hari-libur');

        $libur = MasterHariLibur::findOrFail($id);

        $request->validate([
            'tanggal'      => 'required|date|unique:master_hari_libur,tanggal,' . $id,
            'keterangan'   => 'required|string|max:255',
            'status_libur' => 'required|in:Nasional,Institusi,Cuti Bersama',
            'isactive'     => 'required|in:Y,N'
        ]);

        try {
            // Langsung eksekusi update via Eloquent (Lebih safe untuk single-table)
            $libur->update([
                'tanggal'      => $request->tanggal,
                'keterangan'   => $request->keterangan,
                'status_libur' => $request->status_libur,
                'isactive'     => $request->isactive,
                'updated_by'   => auth()->check() ? auth()->user()->name : 'System',
            ]);

            return back()->with('success', 'Data libur berhasil diperbarui!');

        } catch (\Exception $e) {
            // Error Handling ala PIKDI TSU tetap jalan
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_LIBUR_UPD_FAIL]";
            $userMsg    = "Gagal menyimpan perubahan data libur.";

            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = trim(str_replace($errorCode, '', $rawMessage));
            }

            Log::error("$errorCode Gagal Update Libur ID: $id.", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $finalErrorMsg = "<div class='text-center'>
                                <h4 class='text-bold text-danger mb-2'>$errorCode</h4>
                                <p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>
                                <p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>
                              </div>";

            return back()->withInput($request->all())->with('error', $finalErrorMsg);
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:hari-libur');

        $libur = MasterHariLibur::findOrFail($id);

        try {
            // Langsung delete via Eloquent
            $libur->delete();

            return back()->with('success', 'Data Libur berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("[TSU_LIBUR_DEL_FAIL] Gagal Delete Libur ID: $id. Error: " . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data libur karena kesalahan sistem.');
        }
    }
}
