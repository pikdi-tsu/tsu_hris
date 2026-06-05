<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

use App\Models\CutiKaryawan;
use App\Models\IzinKaryawan;
use App\Models\SaldoCutiKaryawan;
use App\Models\DataDosenTendik;

use function PHPUnit\Framework\isEmpty;

class ApprovalIzinController extends Controller
{
    public function __construct()
    {
        //        $this->middleware('checklogin');
        $this->middleware('auth');
        //        $this->middleware('verified');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {

        if (Session::has('tmp')) {
            Session::forget('tmp');
        }

        $data = array(
            'title'     => 'Approval Izin Karyawan',
            'menu'      => 'dashboard',
        );

        return view('users::approvalizin.index', $data);
    }

    public function datatables()
    {
        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        $data = IzinKaryawan::with(['masterIzin', 'user', 'atasan', 'hrd'])
            ->where('is_active', '1')
            ->where(function ($query) use ($profileId) {
                $query->where(function ($q) use ($profileId) {
                    $q->where('id_atasan', $profileId)
                        ->where('statusatasan', 'waiting');
                });

                $query->orWhere(function ($q) use ($profileId) {
                    $q->where('id_hrd', $profileId)
                        ->where('statushrd', 'waiting');
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', function ($data) {
                return $data->user ? $data->user->nama : '-';
            })
            ->addColumn('jenisizin', function ($data) {
                return $data->masterIzin ? $data->masterIzin->jenisizin : '-';
            })
            ->addColumn('jumlah', function ($data) {
                $start = Carbon::parse($data->tanggalmulai);
                $end   = Carbon::parse($data->tanggalselesai);

                $jumlahHari = $start->diffInDays($end) + 1;

                return $jumlahHari;
            })
            ->addColumn('keterangan', function ($data) {
                return $data->keterangan;
            })
            ->addColumn('action', function ($data) use ($profileId) {
                $button = '';
                if ($data->id_hrd == $profileId && $data->statusatasan != 'waiting') {
                    $button .= '<center><a href="#" data-id="' . encrypt($data->id) . '" id="btnapproval" title="Proses Approval"><i class="fa fa-angle-double-right fa-md text-primary"></i></a>';
                } elseif ($data->id_atasan == $profileId) {
                    $button .= '<center><a href="#" data-id="' . encrypt($data->id) . '" id="btnapproval" title="Proses Approval"><i class="fa fa-angle-double-right fa-md text-primary"></i></a>';
                }

                return $button;
            })
            ->make(true);
    }

    public function detail(Request $req)
    {
        try {
            $myid = decrypt($req->myid);

            $profile = $this->getCurrentProfile();

            $getdata = IzinKaryawan::with(['masterIzin', 'user', 'atasan', 'hrd'])
                ->where('id', $myid)
                ->where('is_active', '1')
                ->orderByDesc('created_at')
                ->first();

            $mulai = Carbon::parse($getdata->tanggalmulai);
            $selesai = Carbon::parse($getdata->tanggalselesai);

            if ($mulai->format('Y-m') == $selesai->format('Y-m')) {
                // bulan & tahun sama
                $tanggal = $mulai->translatedFormat('d') . '–' . $selesai->translatedFormat('d M Y');
            } else {
                // bulan atau tahun beda
                $tanggal = $mulai->translatedFormat('d M Y') . ' - ' . $selesai->translatedFormat('d M Y');
            }

            $jumlahHari = $mulai->diffInDays($selesai) + 1;

            $form = view('users::approvalizin.modaldetail', ['data' => $getdata, 'profile' => $profile, 'jmlhari' => $jumlahHari, 'tanggal' => $tanggal]);
            return $form->render();
        } catch (\Exception $e) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function simpan(Request $req)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($req->all(), [
                'approval' => 'required',
            ], [
                // custom message
                'approval.required' => 'Approval Izin Tidak Boleh Kosong',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Failed!',
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $profile = $this->getCurrentProfile();
            if (!$profile) {
                return response()->json([
                    'title' => 'Failed!',
                    'status' => 'error',
                    'message' => 'Profil karyawan tidak ditemukan.'
                ], 422);
            }

            $iduserlogin = $profile->id;
            $idizinkaryawan = $req->idizinkaryawan;
            $iduserinput = $req->iduser;
            $approval = $req->approval;
            $ketapproval = $req->ketapproval;

            $check = IzinKaryawan::where('id', $idizinkaryawan)
                ->where('id_user', $iduserinput)
                ->where(function ($q) {
                    $q->where('statusatasan', 'waiting')->orWhere('statushrd', 'waiting');
                })
                ->where('is_active', '1')
                ->first();

            $checkatasan = $check->id_atasan == $iduserlogin;
            $checkhrd = $check->id_hrd == $iduserlogin;

            if ($checkatasan) {
                $update = IzinKaryawan::where('id', $idizinkaryawan)->where('id_user', $iduserinput)->where('id_atasan', $check->id_atasan)->where('is_active', '1')->update([
                    'statusatasan'       => $approval,
                    'alasanatasan'       => $ketapproval,
                    'atasanapprovaldate' => date("Y-m-d H:i:s")
                ]);
            } else if ($checkhrd) {
                $update = IzinKaryawan::where('id', $idizinkaryawan)->where('id_user', $iduserinput)->where('id_hrd', $check->id_hrd)->where('is_active', '1')->update([
                    'statushrd'       => $approval,
                    'alasanhrd'       => $ketapproval,
                    'hrdapprovaldate' => date("Y-m-d H:i:s")
                ]);
            }

            DB::commit();
            return response()->json([
                'title' => 'Success!',
                'status' => 'success',
                'message' => 'Approval Izin Berhasil Disimpan'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'title' => 'Error!',
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
