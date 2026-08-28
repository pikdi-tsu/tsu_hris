<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

use App\Models\MasterCuti;
use App\Models\CutiKaryawan;
use App\Models\SaldoCutiKaryawan;
use App\Models\DataDosenTendik;
use App\Models\KaryawanJabatanStruktural;
use App\Traits\ApiResponseTrait;
use App\Services\TsuErrorHandlerService;

class CutiController extends Controller
{
    use ApiResponseTrait;
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

        $getmcuti = MasterCuti::where('is_active', '1')->get();

        $profile = $this->getCurrentProfile();

        $listKaryawan = KaryawanJabatanStruktural::with(['karyawan'])->get();

        $getsaldo = SaldoCutiKaryawan::where('id_user', $profile->id)->where('is_active', '1')->first();

        $data = array(
            'title'     => 'Cuti Karyawan',
            'menu'      => 'dashboard',
            'mcuti'     => $getmcuti,
            'karyawans' => $listKaryawan,
            'profile'   => $profile,
            'saldo'     => $getsaldo
        );

        return view('users::cuti.index', $data);
    }

    public function simpan(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'jeniscuti' => 'required',
                'tanggal1'  => 'required|date',
                'tanggal2'  => 'required|date|after_or_equal:tanggal1',
                'alasan'    => 'required',
                'id_atasan' => 'required',
                'id_hrd'    => 'required',
            ], [
                // custom message
                'jeniscuti.required' => 'Jenis Cuti Tidak Boleh Kosong',
                'tanggal1.required' => 'Tanggal Mulai Tidak Boleh Kosong',
                'tanggal2.required' => 'Tanggal Selesai Tidak Boleh Kosong',
                'tanggal2.after_or_equal' => 'Waktu Selesai harus setelah Waktu Mulai',
                'alasan.required' => 'Alasan Tidak Boleh Kosong',
                'id_atasan.required' => 'Atasan Tidak Boleh Kosong',
                'id_hrd.required' => 'HRD Tidak Boleh Kosong',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first());
            }

            $profile = $this->getCurrentProfile();
            if (!$profile) {
                return $this->sendError('Profil karyawan tidak ditemukan.');
            }

            $iduser = $profile->id;
            $jeniscuti = $req->jeniscuti;
            $tgl1 = $req->tanggal1;
            $tgl2 = $req->tanggal2;
            $alasan = $req->alasan;
            $idatasan = $req->id_atasan;
            $idhrd = $req->id_hrd;

            if ($req->ketedit == 'no') {
                $insert = CutiKaryawan::insert([
                    'id_mcuti'        => $jeniscuti,
                    'id_user'         => $iduser,
                    'tanggalmulai'    => $tgl1,
                    'tanggalselesai'  => $tgl2,
                    'tanggaldiajukan' => date("Y-m-d H:i:s"),
                    'keterangan'      => $alasan,
                    'id_atasan'       => $idatasan,
                    'statusatasan'    => 'waiting',
                    'id_hrd'          => $idhrd,
                    'statushrd'       => 'waiting',
                    'created_at'      => date("Y-m-d H:i:s"),
                    'created_by'      => $profile->nik ?? Auth::id()
                ]);
            } else {
                $insert = CutiKaryawan::where('id', $req->idedit)->where('is_active', '1')->update([
                    'id_mcuti'        => $jeniscuti,
                    'id_user'         => $iduser,
                    'tanggalmulai'    => $tgl1,
                    'tanggalselesai'  => $tgl2,
                    'tanggaldiajukan' => date("Y-m-d H:i:s"),
                    'keterangan'      => $alasan,
                    'id_atasan'       => $idatasan,
                    'statusatasan'    => 'waiting',
                    'id_hrd'          => $idhrd,
                    'statushrd'       => 'waiting',
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'updated_by'      => $profile->nik ?? Auth::id()
                ]);
            }

            return $this->sendSuccess('Cuti berhasil disimpan');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_CUTI_SAVE_FAIL]', 'Gagal menyimpan data cuti.');
        }
    }

    public function datatables()
    {
        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        $data = CutiKaryawan::with(['masterCuti', 'atasan', 'hrd'])
            ->where('id_user', $profileId)
            ->where('is_active', '1')
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('jeniscuti', function ($data) {
                return $data->masterCuti ? $data->masterCuti->jeniscuti : '-';
            })
            ->addColumn('tanggalmulai', function ($data) {
                $formatTanggal = Carbon::parse($data->tanggalmulai)->format('d F Y');
                return $formatTanggal;
            })
            ->addColumn('tanggalselesai', function ($data) {
                $formatTanggal = Carbon::parse($data->tanggalselesai)->format('d F Y');
                return $formatTanggal;
            })
            ->addColumn('jumlah', function ($data) {
                $start = Carbon::parse($data->tanggalmulai);
                $end   = Carbon::parse($data->tanggalselesai);

                $jumlahHari = $start->diffInDays($end) + 1;

                return $jumlahHari;
                // return $data->kode_booking ?? '';
            })
            ->addColumn('statusatasan', function ($data) {
                if ($data->statusatasan == 'approved') {
                    $stat = '<span class="badge badge-success">Approved</span>';
                } elseif ($data->statusatasan == 'rejected') {
                    $stat = '<span class="badge badge-danger">Rejected</span>';
                } else {
                    $stat = '<span class="badge badge-warning">Waiting</span>';
                }

                return $stat;
            })
            ->addColumn('statushrd', function ($data) {
                if ($data->statushrd == 'approved') {
                    $stat = '<span class="badge badge-success">Approved</span>';
                } elseif ($data->statushrd == 'rejected') {
                    $stat = '<span class="badge badge-danger">Rejected</span>';
                } else {
                    $stat = '<span class="badge badge-warning">Waiting</span>';
                }

                return $stat;
            })
            ->addColumn('action', function ($data) {
                $button = '';
                if ($data->statusatasan != 'waiting' || $data->statushrd != 'waiting') {
                    $button .= '<a href="#" data-id="' . encrypt($data->id) . '" id="btndetail" class="ml-2" title="Info Detail"><i class="fa fa-info-circle fa-md text-primary"></i></a></center>';
                } else {
                    $button .= '<center><a href="#" data-id="' . encrypt($data->id) . '" id="btnedit" title="Proses Edit"><i class="fa fa-edit fa-md text-primary"></i></a>';
                }
                return $button;
            })
            ->rawColumns(['statusatasan', 'statushrd', 'action'])
            ->make(true);
    }

    public function edit(Request $req)
    {
        try {
            $myid = decrypt($req->myid);

            $getdata = CutiKaryawan::where('id', $myid)
                ->where('is_active', '1')
                ->first();

            return response()->json($getdata, 200);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_CUTI_EDIT_FAIL]', 'Gagal memuat data cuti.');
        }
    }

    public function detail(Request $req)
    {
        try {
            $myid = decrypt($req->myid);

            $profile = $this->getCurrentProfile();

            $getdata = CutiKaryawan::with(['masterCuti', 'atasan', 'hrd'])
                ->where('id', $myid)
                ->where('is_active', '1')
                ->orderByDesc('created_at')
                ->first();
            // dd($getdata);
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

            $form = view('users::cuti.modaldetail', ['data' => $getdata, 'profile' => $profile, 'jmlhari' => $jumlahHari, 'tanggal' => $tanggal]);
            return $form->render();
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_CUTI_DTL]', 'Gagal memuat detail cuti.');
        }
    }
}
