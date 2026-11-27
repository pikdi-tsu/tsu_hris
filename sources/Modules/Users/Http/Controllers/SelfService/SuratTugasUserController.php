<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\SuratTugas;
use App\Models\DataDosenTendik;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SuratTugasUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getCurrentProfile()
    {
        $user = Auth::user();
        if (!$user) return null;

        $profile = DataDosenTendik::where('user_id', $user->id)->first();
        if (!$profile) {
            $profile = DataDosenTendik::where('nama', $user->name)
                ->orWhere('nik', $user->name)
                ->first();
        }
        return $profile;
    }

    public function index()
    {
        $profile = $this->getCurrentProfile();
        $title = 'Surat Tugas & Dinas Luar Saya';
        return view('users::surat-tugas.index', compact('profile', 'title'));
    }

    public function datatable(Request $request)
    {
        $profile = $this->getCurrentProfile();
        $query = SuratTugas::where('data_dosen_tendik_id', $profile ? $profile->id : 'none');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('rentang_tanggal', function ($row) {
                $mulai = Carbon::parse($row->tanggal_mulai)->translatedFormat('d M Y');
                $selesai = Carbon::parse($row->tanggal_selesai)->translatedFormat('d M Y');
                $durasi = Carbon::parse($row->tanggal_mulai)->diffInDays(Carbon::parse($row->tanggal_selesai)) + 1;

                return '<div>
                    <span class="font-weight-bold text-primary">' . $mulai . ' s.d ' . $selesai . '</span><br>
                    <small class="badge badge-light border text-muted"><i class="far fa-clock mr-1"></i>' . $durasi . ' Hari Dinas</small>
                </div>';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'Disetujui'  => 'badge-success',
                    'Menunggu'   => 'badge-warning',
                    'Dibatalkan' => 'badge-danger',
                    'Selesai'    => 'badge-secondary',
                ];
                $cls = $badges[$row->status] ?? 'badge-info';
                return '<span class="badge ' . $cls . ' px-2 py-1">' . e($row->status) . '</span>';
            })
            ->addColumn('berkas_btn', function ($row) {
                if ($row->file_surat_tugas) {
                    return '<a href="' . route('users.surat-tugas.download-berkas', $row->id) . '" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-file-pdf mr-1"></i> Unduh Berkas</a>';
                }
                return '<span class="text-muted font-italic" style="font-size: 8pt;">-</span>';
            })
            ->rawColumns(['rentang_tanggal', 'status_badge', 'berkas_btn'])
            ->make(true);
    }

    public function downloadBerkas($id)
    {
        $profile = $this->getCurrentProfile();
        $suratTugas = SuratTugas::where('id', $id)
            ->where('data_dosen_tendik_id', $profile ? $profile->id : null)
            ->firstOrFail();

        if (!$suratTugas->file_surat_tugas || !Storage::exists($suratTugas->file_surat_tugas)) {
            abort(404, 'Berkas file surat tugas tidak ditemukan.');
        }

        return Storage::download($suratTugas->file_surat_tugas);
    }
}
