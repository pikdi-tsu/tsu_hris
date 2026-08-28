<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\LemburKaryawan;
use App\Jobs\ExportRiwayatLemburJob;
use Illuminate\Support\Facades\Auth;

class RiwayatLemburController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:riwayat-lembur');
    }

    public function index()
    {
        $this->guard('view', 'admin:riwayat-lembur');
        return view('admin::riwayat-lembur.index', ['title' => 'Data Riwayat Lembur']);
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
                return $data->user ? $data->user->nama : '-';
            })
            ->addColumn('jenislembur', function ($data) {
                return $data->masterLembur ? $data->masterLembur->jenislembur : '-';
            })
            ->addColumn('tanggalwaktu', function ($data) {
                $tgl = Carbon::parse($data->tanggal_lembur)->translatedFormat('d M Y');
                $mulai = Carbon::parse($data->jam_mulai)->format('H:i');
                $selesai = Carbon::parse($data->jam_selesai)->format('H:i');
                return $tgl . '<br><small class="text-muted">' . $mulai . ' - ' . $selesai . '</small>';
            })
            ->addColumn('total_jam', function ($data) {
                return $data->total_jam . ' Jam';
            })
            ->addColumn('keterangan', function ($data) {
                return $data->keterangan;
            })
            ->addColumn('approvalatasan', function ($data) {
                if ($data->statusatasan == 'approved') {
                    $stat = '<span class="badge badge-success">Approved</span>';
                } elseif ($data->statusatasan == 'rejected') {
                    $stat = '<span class="badge badge-danger">Rejected</span>';
                } else {
                    $stat = '<span class="badge badge-warning">Waiting</span>';
                }

                $alasan = $data->alasanatasan ? '<br><small>' . $data->alasanatasan . '</small>' : '';
                $nama = $data->atasan ? $data->atasan->nama : '-';
                return $nama . ' ' . $stat . $alasan;
            })
            ->addColumn('approvalsdm', function ($data) {
                if ($data->statushrd == 'approved') {
                    $stat = '<span class="badge badge-success">Approved</span>';
                } elseif ($data->statushrd == 'rejected') {
                    $stat = '<span class="badge badge-danger">Rejected</span>';
                } else {
                    $stat = '<span class="badge badge-warning">Waiting</span>';
                }

                $alasan = $data->alasanhrd ? '<br><small>' . $data->alasanhrd . '</small>' : '';
                $nama = $data->hrd ? $data->hrd->nama : '-';
                return $nama . ' ' . $stat . $alasan;
            })
            ->rawColumns(['tanggalwaktu', 'approvalatasan', 'approvalsdm'])
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
