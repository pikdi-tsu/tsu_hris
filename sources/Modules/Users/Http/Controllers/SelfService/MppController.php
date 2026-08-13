<?php

namespace Modules\Users\Http\Controllers\SelfService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ManpowerPlanning;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterUnit;
use App\Services\TsuErrorHandlerService;
use App\Models\DataDosenTendik;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Notifications\MppDiajukanNotification;
use App\Models\User;
use App\Traits\ApiResponseTrait;

class MppController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    protected function checkIsAtasan($profile)
    {
        if (Auth::user()->hasRole(['super admin hris', 'admin hris'])) {
            return;
        }

        if (!$profile) abort(403, 'Profil tidak ditemukan.');

        $jabatanStrukturalIds = \App\Models\KaryawanJabatanStruktural::where('data_dosen_tendik_id', $profile->id)
            ->where('is_active', 'Y')
            ->pluck('jabatan_struktural_id');

        $isAtasan = MasterUnit::whereIn('kepala_jabatan_id', $jabatanStrukturalIds)->exists();
        
        if (!$isAtasan) {
            abort(403, 'Akses ditolak. Fitur ini khusus untuk Kepala Unit/Atasan.');
        }
    }

    public function index()
    {
        $profile = $this->getCurrentProfile();
        $this->checkIsAtasan($profile);
        
        $jabatans = [];
        if ($profile) {
            // Ambil semua ID jabatan struktural yang sedang dipegang user ini
            $jabatanStrukturalIds = \App\Models\KaryawanJabatanStruktural::where('data_dosen_tendik_id', $profile->id)
                ->where('is_active', 'Y')
                ->pluck('jabatan_struktural_id');

            // Cari unit-unit di mana jabatan tersebut adalah kepalanya
            $ledUnitIds = MasterUnit::whereIn('kepala_jabatan_id', $jabatanStrukturalIds)->pluck('id')->toArray();
            
            // Tambahkan unit_id bawaan profil (homebase) jika ada
            if ($profile->unit_id) {
                $ledUnitIds[] = $profile->unit_id;
            }

            if (!empty($ledUnitIds)) {
                // Ambil unit-unit tersebut beserta semua anak unit di bawahnya
                $unitIds = MasterUnit::whereIn('id', $ledUnitIds)
                    ->orWhereIn('parent_unit_id', $ledUnitIds)
                    ->pluck('id');

                $jabatanIds = MasterUnit::whereIn('id', $unitIds)
                    ->whereNotNull('kepala_jabatan_id')
                    ->pluck('kepala_jabatan_id');

                $jabatans = MasterJabatanStruktural::whereIn('id', $jabatanIds)->get();
            }
        }

        $title = 'Manpower Planning (MPP)';
        $menu = 'dashboard';
        return view('users::mpp.index', compact('jabatans', 'profile', 'title', 'menu'));
    }

    public function datatables(Request $request)
    {
        $profile = $this->getCurrentProfile();
        $this->checkIsAtasan($profile);
        $query = ManpowerPlanning::with(['jabatan', 'hrd'])->where('id_pengaju', $profile->id)->orderBy('created_at', 'desc');

        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jabatan', function ($row) {
                return $row->jabatan ? $row->jabatan->nama_jabatan : '-';
            })
            ->addColumn('tanggal', function ($row) {
                return Carbon::parse($row->created_at)->translatedFormat('d F Y');
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'waiting') return '<span class="badge badge-warning">Menunggu SDM</span>';
                if ($row->status == 'approved') return '<span class="badge badge-success">Disetujui</span>';
                if ($row->status == 'rejected') return '<span class="badge badge-danger">Ditolak</span>';
                return '-';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-sm btn-info" onclick="detail(\'' . $row->id . '\')" title="Detail/Catatan"><i class="fas fa-eye"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function simpan(Request $request)
    {
        DB::beginTransaction();
        try {
            $profile = $this->getCurrentProfile();
            if (!$profile || !$profile->unit_id) {
                throw new \Exception('Anda tidak memiliki unit kerja. Hubungi administrator.');
            }

            $mpp = ManpowerPlanning::create([
                'id_pengaju'       => $profile->id,
                'unit_id'          => $profile->unit_id,
                'jabatan_id'       => $request->jabatan_id,
                'tahun'            => $request->tahun,
                'jumlah_kebutuhan' => $request->jumlah_kebutuhan,
                'tipe_pengajuan'   => $request->tipe_pengajuan,
                'alasan'           => $request->alasan,
                'status'           => 'waiting'
            ]);

            // Notify HRD
            $hrds = User::permission('admin:mpp:view')->get(); // Adjust permission as needed
            if($hrds->isEmpty()) {
                // Fallback yang aman (tidak melempar exception meskipun nama role diganti/dihapus di DB)
                $hrds = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super admin hris', 'admin hris']);
                })->get();
            }

            foreach ($hrds as $hrd) {
                $hrd->notify(new MppDiajukanNotification(
                    $mpp,
                    'Pengajuan MPP baru dari ' . $profile->nama . ' untuk tahun ' . $mpp->tahun . '.',
                    'hrd'
                ));
            }

            DB::commit();
            return $this->sendSuccess('Pengajuan MPP berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[TSU_MPP_SUBMIT_FAIL]', 'Gagal mengajukan MPP.');
        }
    }

    public function detail(Request $request)
    {
        $profile = $this->getCurrentProfile();
        $this->checkIsAtasan($profile);
        try {
            $data = ManpowerPlanning::with(['jabatan', 'hrd'])->find($request->id);

            $hrdName = '-';
            if ($data->hrd) {
                $hrdProf = DataDosenTendik::where('user_id', $data->hrd->id)->first();
                $hrdName = $hrdProf ? $hrdProf->nama : $data->hrd->name;
            }

            $html = view('users::mpp.modaldetail', compact('data', 'hrdName'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_MPP_DETAIL_FAIL]', 'Gagal memuat detail MPP.');
        }
    }
}
