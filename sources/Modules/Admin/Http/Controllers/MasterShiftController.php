<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterShift;
use App\Models\MasterShiftDetail;
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
        return view('admin::master-data.shift.index', ['title' => 'Master Data Shift & Jam Kerja']);
    }

    public function datatable()
    {
        $data = MasterShift::with('details')->orderBy('nama_shift', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('tipe_badge', function ($row) {
                if ($row->tipe_shift === 'durasi') {
                    $jam = round($row->target_durasi_menit / 60, 1);
                    return '<span class="badge badge-info"><i class="fas fa-hourglass-half mr-1"></i> Target ' . $jam . ' Jam</span>';
                }
                return '<span class="badge badge-primary"><i class="fas fa-calendar-alt mr-1"></i> Jadwal Jam Harian</span>';
            })
            ->addColumn('rincian_jadwal', function ($row) {
                if ($row->tipe_shift === 'durasi') {
                    return '<small class="text-muted">' . ($row->keterangan ?? 'Target jam kerja efektif per hari') . '</small>';
                }
                
                $details = $row->details;
                if ($details->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }

                $html = '<div style="font-size: 0.85rem;">';
                foreach ($details as $d) {
                    if ($d->is_libur) {
                        $html .= '<div><strong>' . $d->nama_hari . ':</strong> <span class="badge badge-secondary">Libur</span></div>';
                    } else {
                        $jamMasuk = $d->jam_masuk ? substr($d->jam_masuk, 0, 5) : '-';
                        $jamPulang = $d->jam_pulang ? substr($d->jam_pulang, 0, 5) : '-';
                        $istirahat = '';
                        if ($d->jam_istirahat_mulai && $d->jam_istirahat_selesai) {
                            $istirahat = ' <small class="text-muted">(Istirahat: ' . substr($d->jam_istirahat_mulai, 0, 5) . '-' . substr($d->jam_istirahat_selesai, 0, 5) . ')</small>';
                        }
                        $crossDay = $d->is_cross_day ? ' <span class="badge badge-warning" style="font-size: 0.65rem;">Lintas Hari</span>' : '';
                        $html .= '<div><strong>' . $d->nama_hari . ':</strong> ' . $jamMasuk . ' - ' . $jamPulang . $istirahat . $crossDay . '</div>';
                    }
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('status', function ($row) {
                if ($row->is_active === 'Y') {
                    return '<span class="badge badge-success">Aktif</span>';
                }
                return '<span class="badge badge-secondary">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-primary btn-modal mr-1" data-url="' . route('admin.master-shift.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnToggle = '<button type="button" class="btn btn-xs ' . ($row->is_active === 'Y' ? 'btn-warning' : 'btn-success') . ' btn-delete" data-url="' . route('admin.master-shift.destroy', $row->id) . '" data-name="' . $row->nama_shift . '" title="' . ($row->is_active === 'Y' ? 'Nonaktifkan' : 'Aktifkan') . '"><i class="fas fa-power-off"></i></button>';
                return '<div class="text-center">' . $btnEdit . $btnToggle . '</div>';
            })
            ->rawColumns(['tipe_badge', 'rincian_jadwal', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin::master-data.shift.create_modal');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_shift' => 'required|string|max:255',
            'kode_shift' => 'nullable|string|max:50',
            'tipe_shift' => 'required|in:jadwal,durasi',
            'target_durasi_jam' => 'nullable|numeric|min:1|max:24',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $shiftId = Str::uuid()->toString();
            $targetDurasiMenit = $request->target_durasi_jam ? intval($request->target_durasi_jam * 60) : 420;

            $shift = MasterShift::create([
                'id' => $shiftId,
                'nama_shift' => $request->nama_shift,
                'kode_shift' => $request->kode_shift ? strtoupper($request->kode_shift) : Str::slug($request->nama_shift),
                'tipe_shift' => $request->tipe_shift,
                'target_durasi_menit' => $targetDurasiMenit,
                'keterangan' => $request->keterangan,
                'is_active' => 'Y',
            ]);

            if ($request->tipe_shift === 'jadwal' && is_array($request->hari)) {
                foreach ($request->hari as $hariNum => $dataHari) {
                    $isLibur = isset($dataHari['is_libur']) && $dataHari['is_libur'] == '1';
                    MasterShiftDetail::create([
                        'id' => Str::uuid()->toString(),
                        'master_shift_id' => $shiftId,
                        'hari' => $hariNum,
                        'jam_masuk' => $isLibur ? null : ($dataHari['jam_masuk'] ?? null),
                        'jam_pulang' => $isLibur ? null : ($dataHari['jam_pulang'] ?? null),
                        'jam_istirahat_mulai' => $isLibur ? null : ($dataHari['jam_istirahat_mulai'] ?? null),
                        'jam_istirahat_selesai' => $isLibur ? null : ($dataHari['jam_istirahat_selesai'] ?? null),
                        'is_cross_day' => isset($dataHari['is_cross_day']) && $dataHari['is_cross_day'] == '1',
                        'is_libur' => $isLibur,
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
        $shift = MasterShift::with('details')->findOrFail($id);
        return view('admin::master-data.shift.edit_modal', compact('shift'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_shift' => 'required|string|max:255',
            'kode_shift' => 'nullable|string|max:50',
            'tipe_shift' => 'required|in:jadwal,durasi',
            'target_durasi_jam' => 'nullable|numeric|min:1|max:24',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $shift = MasterShift::findOrFail($id);
            $targetDurasiMenit = $request->target_durasi_jam ? intval($request->target_durasi_jam * 60) : 420;

            $shift->update([
                'nama_shift' => $request->nama_shift,
                'kode_shift' => $request->kode_shift ? strtoupper($request->kode_shift) : $shift->kode_shift,
                'tipe_shift' => $request->tipe_shift,
                'target_durasi_menit' => $targetDurasiMenit,
                'keterangan' => $request->keterangan,
            ]);

            if ($request->tipe_shift === 'jadwal' && is_array($request->hari)) {
                MasterShiftDetail::where('master_shift_id', $shift->id)->delete();
                foreach ($request->hari as $hariNum => $dataHari) {
                    $isLibur = isset($dataHari['is_libur']) && $dataHari['is_libur'] == '1';
                    MasterShiftDetail::create([
                        'id' => Str::uuid()->toString(),
                        'master_shift_id' => $shift->id,
                        'hari' => $hariNum,
                        'jam_masuk' => $isLibur ? null : ($dataHari['jam_masuk'] ?? null),
                        'jam_pulang' => $isLibur ? null : ($dataHari['jam_pulang'] ?? null),
                        'jam_istirahat_mulai' => $isLibur ? null : ($dataHari['jam_istirahat_mulai'] ?? null),
                        'jam_istirahat_selesai' => $isLibur ? null : ($dataHari['jam_istirahat_selesai'] ?? null),
                        'is_cross_day' => isset($dataHari['is_cross_day']) && $dataHari['is_cross_day'] == '1',
                        'is_libur' => $isLibur,
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
