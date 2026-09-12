<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterShift;
use App\Models\MasterShiftDetail;
use App\Models\DataDosenTendik;
use Modules\System\Models\MenuSidebar;
use App\Services\TsuErrorHandlerService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class MasterShiftController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:master-shift');
    }

    public function index()
    {
        $stats = [
            'total'   => MasterShift::count(),
            'aktif'   => MasterShift::where('is_active', 'Y')->count(),
            'jadwal'  => MasterShift::where('tipe_shift', 'jadwal')->count(),
            'durasi'  => MasterShift::where('tipe_shift', 'durasi')->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-shift.index')->value('icon') ?: 'fas fa-clock';

        return view('admin::master-data.shift.index', [
            'title'    => 'Master Data Shift & Jam Kerja',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterShift::with('details')->orderBy('nama_shift', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_shift', function ($row) {
                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_shift) . '</div>';
                if ($row->kode_shift) {
                    $html .= '<small class="text-muted d-block" style="font-size: 0.75rem;">Kode: ' . e($row->kode_shift) . '</small>';
                }
                return $html;
            })
            ->addColumn('tipe_badge', function ($row) {
                if ($row->tipe_shift === 'durasi') {
                    $jam = round($row->target_durasi_menit / 60, 1);
                    return '<span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Target ' . $jam . ' Jam</span>';
                }
                return '<span class="badge" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Jadwal Jam Harian</span>';
            })
            ->addColumn('rincian_jadwal', function ($row) {
                if ($row->tipe_shift === 'durasi') {
                    return '<div class="text-secondary" style="font-size: 0.85rem;">' . e($row->keterangan ?? 'Target jam kerja efektif per hari') . '</div>';
                }

                $details = $row->details;
                if ($details->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }

                $html = '<div style="font-size: 0.82rem; line-height: 1.5;">';
                foreach ($details as $d) {
                    if ($d->is_libur) {
                        $html .= '<div><span class="font-weight-semibold text-dark">' . $d->nama_hari . ':</span> <span class="badge badge-light border text-muted px-1">Libur</span></div>';
                    } else {
                        $jamMasuk = $d->jam_masuk ? substr($d->jam_masuk, 0, 5) : '-';
                        $jamPulang = $d->jam_pulang ? substr($d->jam_pulang, 0, 5) : '-';
                        $istirahat = '';
                        if ($d->jam_istirahat_mulai && $d->jam_istirahat_selesai) {
                            $istirahat = ' <small class="text-muted">(Istirahat: ' . substr($d->jam_istirahat_mulai, 0, 5) . '-' . substr($d->jam_istirahat_selesai, 0, 5) . ')</small>';
                        }
                        $crossDay = $d->is_cross_day ? ' <span class="badge" style="background: rgba(180, 83, 9, 0.12); color: #b45309; border: 1px solid rgba(180, 83, 9, 0.25); font-size: 0.68rem; padding: 0.15rem 0.4rem; border-radius: 4px;">Lintas Hari</span>' : '';
                        $html .= '<div><span class="font-weight-semibold text-dark">' . $d->nama_hari . ':</span> ' . $jamMasuk . ' - ' . $jamPulang . $istirahat . $crossDay . '</div>';
                    }
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('status', function ($row) {
                if ($row->is_active === 'Y') {
                    return '<span class="badge" style="background: rgba(4, 120, 87, 0.12); color: #047857; border: 1px solid rgba(4, 120, 87, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Aktif</span>';
                }
                return '<span class="badge" style="background: rgba(100, 116, 139, 0.12); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-shift:edit');
                $canDelete = auth()->user()->can('admin:master-shift:delete');

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" class="btn btn-sm btn-edit btn-modal" data-url="' . route('admin.master-shift.edit', $row->id) . '" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Master Shift">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    $isY = $row->is_active === 'Y';
                    $btn .= '<button type="button" class="btn btn-sm btn-delete" data-url="' . route('admin.master-shift.destroy', $row->id) . '" data-name="' . e($row->nama_shift) . '" style="background: ' . ($isY ? '#fef2f2' : '#f0fdf4') . '; color: ' . ($isY ? '#dc2626' : '#16a34a') . '; border: 1px solid ' . ($isY ? '#fecaca' : '#bbf7d0') . '; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="' . ($isY ? 'Nonaktifkan Shift' : 'Aktifkan Shift') . '">
                                <i class="fas fa-power-off"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_shift', 'tipe_badge', 'rincian_jadwal', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-shift');
        return view('admin::master-data.shift.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-shift');

        $request->validate([
            'nama_shift'        => 'required|string|max:255',
            'kode_shift'        => 'nullable|string|max:50',
            'tipe_shift'        => 'required|in:jadwal,durasi',
            'target_durasi_jam' => 'nullable|numeric|min:1|max:24',
            'keterangan'        => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $shiftId = Str::uuid()->toString();
            $targetDurasiMenit = $request->target_durasi_jam ? intval($request->target_durasi_jam * 60) : 420;

            MasterShift::create([
                'id'                  => $shiftId,
                'nama_shift'          => $request->nama_shift,
                'kode_shift'          => $request->kode_shift ? strtoupper($request->kode_shift) : Str::slug($request->nama_shift),
                'tipe_shift'          => $request->tipe_shift,
                'target_durasi_menit' => $targetDurasiMenit,
                'keterangan'          => $request->keterangan,
                'is_active'           => 'Y',
            ]);

            if ($request->tipe_shift === 'jadwal' && is_array($request->hari)) {
                foreach ($request->hari as $hariNum => $dataHari) {
                    $isLibur = isset($dataHari['is_libur']) && $dataHari['is_libur'] == '1';
                    MasterShiftDetail::create([
                        'id'                    => Str::uuid()->toString(),
                        'master_shift_id'       => $shiftId,
                        'hari'                  => $hariNum,
                        'jam_masuk'             => $isLibur ? null : ($dataHari['jam_masuk'] ?? null),
                        'jam_pulang'            => $isLibur ? null : ($dataHari['jam_pulang'] ?? null),
                        'jam_istirahat_mulai'   => $isLibur ? null : ($dataHari['jam_istirahat_mulai'] ?? null),
                        'jam_istirahat_selesai' => $isLibur ? null : ($dataHari['jam_istirahat_selesai'] ?? null),
                        'is_cross_day'          => isset($dataHari['is_cross_day']) && $dataHari['is_cross_day'] == '1',
                        'is_libur'              => $isLibur,
                    ]);
                }
            }

            DB::commit();
            return $this->sendSuccess('Master Shift berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_SHIFT_STORE]', 'Gagal menambah master shift.', 'Gagal Store Shift');
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-shift');
        $shift = MasterShift::with('details')->findOrFail($id);
        return view('admin::master-data.shift.edit_modal', compact('shift'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-shift');
        $request->validate([
            'nama_shift'        => 'required|string|max:255',
            'kode_shift'        => 'nullable|string|max:50',
            'tipe_shift'        => 'required|in:jadwal,durasi',
            'target_durasi_jam' => 'nullable|numeric|min:1|max:24',
            'keterangan'        => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $shift = MasterShift::findOrFail($id);
            $targetDurasiMenit = $request->target_durasi_jam ? intval($request->target_durasi_jam * 60) : 420;

            $shift->update([
                'nama_shift'          => $request->nama_shift,
                'kode_shift'          => $request->kode_shift ? strtoupper($request->kode_shift) : $shift->kode_shift,
                'tipe_shift'          => $request->tipe_shift,
                'target_durasi_menit' => $targetDurasiMenit,
                'keterangan'          => $request->keterangan,
            ]);

            if ($request->tipe_shift === 'jadwal' && is_array($request->hari)) {
                MasterShiftDetail::where('master_shift_id', $shift->id)->delete();
                foreach ($request->hari as $hariNum => $dataHari) {
                    $isLibur = isset($dataHari['is_libur']) && $dataHari['is_libur'] == '1';
                    MasterShiftDetail::create([
                        'id'                    => Str::uuid()->toString(),
                        'master_shift_id'       => $shift->id,
                        'hari'                  => $hariNum,
                        'jam_masuk'             => $isLibur ? null : ($dataHari['jam_masuk'] ?? null),
                        'jam_pulang'            => $isLibur ? null : ($dataHari['jam_pulang'] ?? null),
                        'jam_istirahat_mulai'   => $isLibur ? null : ($dataHari['jam_istirahat_mulai'] ?? null),
                        'jam_istirahat_selesai' => $isLibur ? null : ($dataHari['jam_istirahat_selesai'] ?? null),
                        'is_cross_day'          => isset($dataHari['is_cross_day']) && $dataHari['is_cross_day'] == '1',
                        'is_libur'              => $isLibur,
                    ]);
                }
            }

            DB::commit();
            return $this->sendSuccess('Master Shift berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_SHIFT_UPDATE]', 'Gagal memperbarui master shift.', 'Gagal Update Shift');
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-shift');
        DB::beginTransaction();
        try {
            $shift = MasterShift::findOrFail($id);
            $newStatus = $shift->is_active === 'Y' ? 'N' : 'Y';
            $shift->is_active = $newStatus;
            $shift->save();

            DB::commit();
            return $this->sendSuccess('Status Shift berhasil diubah menjadi ' . ($newStatus === 'Y' ? 'Aktif' : 'Tidak Aktif'));
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_SHIFT_DESTROY]', 'Gagal mengubah status shift.', 'Gagal Hapus Shift');
        }
    }
}
