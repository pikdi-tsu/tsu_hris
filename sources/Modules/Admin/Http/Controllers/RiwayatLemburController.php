<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Models\LemburKaryawan;
use App\Jobs\ExportRiwayatLemburJob;
use Illuminate\Support\Facades\Auth;
use Modules\System\Models\MenuSidebar;

class RiwayatLemburController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:riwayat-lembur');
    }

    public function index()
    {
        $this->guard('view', 'admin:riwayat-lembur');

        $menuData = MenuSidebar::where('route', 'admin.riwayat-lembur.index')->first();
        $menuIcon = $menuData->icon ?? 'fas fa-history';
        $title = $menuData->name ?? 'Data Riwayat Lembur';

        $totalLembur = LemburKaryawan::count();
        $totalWaiting = LemburKaryawan::where(function ($q) {
            $q->where('statusatasan', 'waiting')
              ->orWhere('statushrd', 'waiting');
        })->count();
        $totalApproved = LemburKaryawan::where('statusatasan', 'approved')
            ->where('statushrd', 'approved')
            ->count();
        $totalRejected = LemburKaryawan::where('statusatasan', 'rejected')
            ->orWhere('statushrd', 'rejected')
            ->count();

        return view('admin::riwayat-lembur.index', compact(
            'title',
            'menuIcon',
            'totalLembur',
            'totalWaiting',
            'totalApproved',
            'totalRejected'
        ));
    }

    public function datatable()
    {
        $this->guard('view', 'admin:riwayat-lembur');
        
        $data = LemburKaryawan::with(['masterLembur', 'user', 'atasan', 'hrd'])
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', function ($data) {
                $nama = $data->user ? e($data->user->nama) : '-';
                $nik = $data->user && $data->user->nik ? '<br><small class="text-muted">' . e($data->user->nik) . '</small>' : '';
                return $nama . $nik;
            })
            ->addColumn('jenislembur', function ($data) {
                return $data->masterLembur ? e($data->masterLembur->jenislembur) : '-';
            })
            ->addColumn('tanggalwaktu', function ($data) {
                if (!$data->tanggalmulai) return '-';
                $tgl = Carbon::parse($data->tanggalmulai)->translatedFormat('d M Y');
                $mulai = Carbon::parse($data->tanggalmulai)->format('H:i');
                $selesai = $data->tanggalselesai ? Carbon::parse($data->tanggalselesai)->format('H:i') : '-';
                return '<strong>' . $tgl . '</strong><br><small class="text-muted"><i class="fas fa-clock mr-1" style="color:var(--tsu-primary,#094b54);"></i>' . $mulai . ' - ' . $selesai . '</small>';
            })
            ->addColumn('total_jam', function ($data) {
                return '<span class="badge badge-light border font-weight-bold" style="font-size:.82rem;padding:.35rem .6rem;">' . $data->total_jam . ' Jam</span>';
            })
            ->addColumn('keterangan', function ($data) {
                if (!$data->keterangan) return '-';
                return '<span title="' . e($data->keterangan) . '">' . e(Str::limit($data->keterangan, 60)) . '</span>';
            })
            ->addColumn('approvalatasan', function ($data) {
                if ($data->statusatasan == 'approved') {
                    $stat = '<span class="badge badge-success" style="font-weight:600;padding:.25rem .55rem;border-radius:6px;font-size:.75rem;"><i class="fas fa-check-circle mr-1"></i>Approved</span>';
                } elseif ($data->statusatasan == 'rejected') {
                    $stat = '<span class="badge badge-danger" style="font-weight:600;padding:.25rem .55rem;border-radius:6px;font-size:.75rem;"><i class="fas fa-times-circle mr-1"></i>Rejected</span>';
                } else {
                    $stat = '<span class="badge badge-warning" style="font-weight:600;padding:.25rem .55rem;border-radius:6px;font-size:.75rem;"><i class="fas fa-clock mr-1"></i>Waiting</span>';
                }

                $nama = $data->atasan ? '<div class="font-weight-600 text-truncate" style="max-width:140px;font-size:.8rem;">' . e($data->atasan->nama) . '</div>' : '<div class="text-muted" style="font-size:.8rem;">-</div>';
                $alasan = $data->alasanatasan ? '<small class="text-danger d-block text-truncate" style="max-width:140px;" title="' . e($data->alasanatasan) . '">' . e($data->alasanatasan) . '</small>' : '';
                return $nama . '<div class="mt-1">' . $stat . '</div>' . $alasan;
            })
            ->addColumn('approvalsdm', function ($data) {
                if ($data->statushrd == 'approved') {
                    $stat = '<span class="badge badge-success" style="font-weight:600;padding:.25rem .55rem;border-radius:6px;font-size:.75rem;"><i class="fas fa-check-circle mr-1"></i>Approved</span>';
                } elseif ($data->statushrd == 'rejected') {
                    $stat = '<span class="badge badge-danger" style="font-weight:600;padding:.25rem .55rem;border-radius:6px;font-size:.75rem;"><i class="fas fa-times-circle mr-1"></i>Rejected</span>';
                } else {
                    $stat = '<span class="badge badge-warning" style="font-weight:600;padding:.25rem .55rem;border-radius:6px;font-size:.75rem;"><i class="fas fa-clock mr-1"></i>Waiting</span>';
                }

                $nama = $data->hrd ? '<div class="font-weight-600 text-truncate" style="max-width:140px;font-size:.8rem;">' . e($data->hrd->nama) . '</div>' : '<div class="text-muted" style="font-size:.8rem;">-</div>';
                $alasan = $data->alasanhrd ? '<small class="text-danger d-block text-truncate" style="max-width:140px;" title="' . e($data->alasanhrd) . '">' . e($data->alasanhrd) . '</small>' : '';
                return $nama . '<div class="mt-1">' . $stat . '</div>' . $alasan;
            })
            ->rawColumns(['nama', 'tanggalwaktu', 'total_jam', 'keterangan', 'approvalatasan', 'approvalsdm'])
            ->make(true);
    }

    public function export()
    {
        $this->guard('view', 'admin:riwayat-lembur');

        $user = Auth::user();
        $fileName = 'Riwayat_Lembur_' . date('Ymd_His') . '.xlsx';

        // Dispatch job ke background
        ExportRiwayatLemburJob::dispatch($user->id, $fileName);

        return response()->json([
            'status' => 'success',
            'message' => 'Proses export sedang berjalan di background. Anda akan menerima notifikasi jika file sudah siap diunduh.'
        ]);
    }
}
