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
use App\Models\SaldoCutiKaryawan;
use App\Models\DataDosenTendik;

use function PHPUnit\Framework\isEmpty;

class ApprovalCutiController extends Controller
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
            'title'     => 'Approval Cuti Karyawan',
            'menu'      => 'dashboard',
        );

        return view('users::approvalcuti.index', $data);
    }

    public function datatables()
    {
        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        $data = CutiKaryawan::with(['masterCuti', 'user', 'atasan', 'hrd'])
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
            ->addColumn('jeniscuti', function ($data) {
                return $data->masterCuti ? $data->masterCuti->jeniscuti : '-';
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

            $getdata = CutiKaryawan::with(['masterCuti', 'user', 'atasan', 'hrd'])
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

            $form = view('users::approvalcuti.modaldetail', ['data' => $getdata, 'profile' => $profile, 'jmlhari' => $jumlahHari, 'tanggal' => $tanggal]);
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
                'approval.required' => 'Jenis Cuti Tidak Boleh Kosong',
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
            $idcutikaryawan = $req->idcutikaryawan;
            $iduserinput = $req->iduser;
            $approval = $req->approval;
            $ketapproval = $req->ketapproval;

            $check = CutiKaryawan::where('id', $idcutikaryawan)
                ->where('id_user', $iduserinput)
                ->where(function ($q) {
                    $q->where('statusatasan', 'waiting')->orWhere('statushrd', 'waiting');
                })
                ->where('is_active', '1')
                ->first();

            $checkatasan = $check->id_atasan == $iduserlogin;
            $checkhrd = $check->id_hrd == $iduserlogin;

            $start = Carbon::parse($check->tanggalmulai);
            $end   = Carbon::parse($check->tanggalselesai);

            $jumlahHari = $start->diffInDays($end) + 1;

            if ($checkatasan) {
                $update = CutiKaryawan::where('id', $idcutikaryawan)->where('id_user', $iduserinput)->where('id_atasan', $check->id_atasan)->where('is_active', '1')->update([
                    'statusatasan'       => $approval,
                    'alasanatasan'       => $ketapproval,
                    'atasanapprovaldate' => date("Y-m-d H:i:s")
                ]);

                if ($approval == 'approved') {
                    $checksaldo = SaldoCutiKaryawan::lockForUpdate()->where('id_user', $iduserinput)->where('is_active', '1')->first();
                    $saldoterpakai = $checksaldo->terpakai + $jumlahHari;
                    $saldosisa = $checksaldo->sisa - $jumlahHari;
                    $update2 = SaldoCutiKaryawan::where('id_user', $iduserinput)->where('is_active', '1')->update([
                        'terpakai'    => $saldoterpakai,
                        'sisa'        => $saldosisa,
                        'updated_at'  => date("Y-m-d H:i:s"),
                        'updated_by'  => $profile->nik ?? Auth::id()
                    ]);
                }
            } else if ($checkhrd) {
                $update = CutiKaryawan::where('id', $idcutikaryawan)->where('id_user', $iduserinput)->where('id_hrd', $check->id_hrd)->where('is_active', '1')->update([
                    'statushrd'       => $approval,
                    'alasanhrd'       => $ketapproval,
                    'hrdapprovaldate' => date("Y-m-d H:i:s")
                ]);
            }

            DB::commit();
            return response()->json([
                'title' => 'Success!',
                'status' => 'success',
                'message' => 'Approval Cuti Berhasil Disimpan'
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
