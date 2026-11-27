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
use App\Services\OrgStructureService;

use Modules\System\Models\MenuSidebar;

class MppController extends Controller
{
    use ApiResponseTrait;

    protected $orgService;

    public function __construct(OrgStructureService $orgService)
    {
        $this->middleware('auth');
        $this->orgService = $orgService;
    }

    protected function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    protected function checkIsAtasan($profile)
    {
        if (Auth::user()->hasRole(['super admin', 'super admin hris', 'admin', 'admin hris'])) {
            return;
        }

        if (!$profile) abort(403, 'Profil tidak ditemukan.');

        $isAtasan = $this->orgService->isKepalaUnit($profile->id);
        
        if (!$isAtasan) {
            abort(403, 'Akses ditolak. Fitur ini khusus untuk Kepala Unit/Atasan.');
        }
    }

    public function index()
    {
        $profile = $this->getCurrentProfile();
        $this->checkIsAtasan($profile);
        
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $unit = \App\Models\MasterUnit::find($profile->unit_id);
        $kuota = $unit ? (int)$unit->kuota_mpp : 0;
        $existingCount = $unit ? \App\Models\DataDosenTendik::where('unit_id', $unit->id)->where('is_active', 1)->count() : 0;
        
        $tahunSekarang = (int)date('Y');
        // Usulan formasi berjalan tahun ini (status waiting atau approved)
        $pendingCount = $unit ? (int)ManpowerPlanning::where('unit_id', $unit->id)
            ->where('tahun', $tahunSekarang)
            ->whereIn('status', ['waiting', 'approved'])
            ->sum('jumlah_kebutuhan') : 0;

        $balance = $kuota > 0 ? max(0, $kuota - $existingCount - $pendingCount) : '∞';
        $isFull = ($kuota > 0 && is_numeric($balance) && $balance <= 0);

        $menuData = MenuSidebar::where('route', 'users.mpp.index')->first();
        $menuIcon = $menuData->icon ?? 'fas fa-users-cog';
        $title = $menuData->name ?? 'MPP Kebutuhan SDM';
        $menu = 'dashboard';
        
        return view('users::mpp.index', compact('jabatans', 'profile', 'title', 'menu', 'unit', 'kuota', 'existingCount', 'pendingCount', 'balance', 'isFull', 'menuIcon'));
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
                $jabatan = $row->jabatan ? e($row->jabatan->nama_jabatan) : '-';
                $tipe = $row->tipe_pengajuan ? '<br><small class="text-muted"><i class="fas fa-tag mr-1" style="color:var(--tsu-primary,#094b54);"></i>' . e($row->tipe_pengajuan) . '</small>' : '';
                return $jabatan . $tipe;
            })
            ->addColumn('tanggal', function ($row) {
                return Carbon::parse($row->created_at)->translatedFormat('d M Y');
            })
            ->editColumn('jumlah_kebutuhan', function ($row) {
                return '<strong>' . $row->jumlah_kebutuhan . '</strong> <span class="text-muted" style="font-size:.82rem;">Orang</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'waiting') {
                    return '<span class="badge badge-warning" style="font-weight:600;padding:.38rem .7rem;border-radius:6px;font-size:.78rem;"><i class="fas fa-clock mr-1"></i>Menunggu SDM</span>';
                }
                if ($row->status == 'approved') {
                    return '<span class="badge badge-success" style="font-weight:600;padding:.38rem .7rem;border-radius:6px;font-size:.78rem;"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
                }
                if ($row->status == 'rejected') {
                    return '<span class="badge badge-danger" style="font-weight:600;padding:.38rem .7rem;border-radius:6px;font-size:.78rem;"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm tsu-btn-view" onclick="detail(\'' . $row->id . '\')" title="Lihat Detail & Catatan"><i class="fas fa-eye mr-1"></i>Detail</button>';
            })
            ->rawColumns(['jabatan', 'jumlah_kebutuhan', 'status', 'action'])
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

            // Validasi Kuota & Balance MPP
            $unit = \App\Models\MasterUnit::find($profile->unit_id);
            if ($unit && $unit->kuota_mpp > 0) {
                $kuota = (int)$unit->kuota_mpp;
                $existingCount = \App\Models\DataDosenTendik::where('unit_id', $unit->id)->where('is_active', 1)->count();
                $pendingCount = (int)ManpowerPlanning::where('unit_id', $unit->id)
                    ->where('tahun', $request->tahun)
                    ->whereIn('status', ['waiting', 'approved'])
                    ->sum('jumlah_kebutuhan');

                $balance = max(0, $kuota - $existingCount - $pendingCount);

                if ($balance <= 0) {
                    throw new \Exception("Pengajuan ditolak. Kuota formasi MPP unit Anda ({$kuota} orang) saat ini telah terpenuhi (Staf aktif: {$existingCount}, Usulan berjalan: {$pendingCount}). Tidak ada sisa Balance kuota yang tersedia.");
                }

                if ($request->jumlah_kebutuhan > $balance) {
                    throw new \Exception("Pengajuan ditolak. Jumlah kebutuhan yang diajukan ({$request->jumlah_kebutuhan} orang) melebihi sisa Balance kuota yang tersedia ({$balance} orang). Silakan sesuaikan jumlah usulan Anda.");
                }
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
                    $q->whereIn('name', ['super admin', 'super admin hris', 'admin', 'admin hris']);
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
