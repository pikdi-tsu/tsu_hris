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
use Modules\System\Models\MenuSidebar;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;

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
        $stats = [
            'total'             => MasterHariLibur::count(),
            'nasional'          => MasterHariLibur::whereRaw('LOWER(status_libur) = ?', ['nasional'])->count(),
            'institusi_bersama' => MasterHariLibur::whereRaw('LOWER(status_libur) IN (?, ?)', ['institusi', 'cuti bersama'])->count(),
            'active'            => MasterHariLibur::where('isactive', 'Y')->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.hari-libur.index')->value('icon') ?? 'far fa-calendar-alt';

        return view('admin::master-data.hari-libur.index', [
            'title'    => 'Data Master Hari Libur',
            'menu'     => 'hari-libur',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterHariLibur::query()->orderBy('tanggal', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tanggal', function($row) {
                $dayName = Carbon::parse($row->tanggal)->translatedFormat('l');
                return '<div>
                            <span class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . tglIndo($row->tanggal) . '</span>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">' . $dayName . '</small>
                        </div>';
            })
            ->editColumn('keterangan', function($row) {
                return '<span class="font-weight-600 text-dark" style="font-size: 0.88rem;">' . e($row->keterangan) . '</span>';
            })
            ->addColumn('status_libur', function($row) {
                $statusLower = strtolower($row->status_libur);
                if ($statusLower === 'nasional') {
                    return '<span class="badge" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Nasional</span>';
                }
                if ($statusLower === 'cuti bersama') {
                    return '<span class="badge" style="background: rgba(217, 119, 6, 0.1); color: #d97706; border: 1px solid rgba(217, 119, 6, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Cuti Bersama</span>';
                }
                return '<span class="badge" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Institusi</span>';
            })
            ->addColumn('isactive', function($row) {
                if ($row->isactive === 'Y') {
                    return '<div class="text-center"><span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #047857; border: 1px solid rgba(16, 185, 129, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-check-circle mr-1"></i> Aktif</span></div>';
                }
                return '<div class="text-center"><span class="badge" style="background: rgba(100, 116, 139, 0.12); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-times-circle mr-1"></i> Non-Aktif</span></div>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:hari-libur:edit');
                $canDelete = auth()->user()->can('admin:hari-libur:delete');
                $isNasional = strtolower($row->status_libur) === 'nasional';

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="'.route('admin.hari-libur.edit', $row->id).'" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Hari Libur">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    if ($isNasional) {
                        $btn .= '<span class="badge" style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.4rem 0.6rem; font-size: 0.75rem;" title="Libur Nasional Resmi (Terkunci)">
                                    <i class="fas fa-lock"></i>
                                 </span>';
                    } else {
                        $btn .= '<form action="'.route('admin.hari-libur.destroy', $row->id).'" method="POST" style="display:inline;" class="form-delete">
                                    '.csrf_field().' '.method_field('DELETE').'
                                    <button type="submit" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Libur Internal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';
                    }
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['tanggal', 'keterangan', 'status_libur', 'isactive', 'action'])
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
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_LIBUR_STORE_FAIL]', 
                'Gagal menyimpan data libur baru.', 
                'Gagal Create Libur.', 
                $request
            );
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
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_LIBUR_UPD_FAIL]', 
                'Gagal menyimpan perubahan data libur.', 
                "Gagal Update Libur ID: $id.", 
                $request
            );
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
