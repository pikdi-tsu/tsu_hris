<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Crypt;
use DB;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

use App\Models\MasterCuti;
use App\Models\CutiKaryawan;
use App\Models\DataKaryawan;

class CutiController extends Controller
{
    public function __construct()
    {
        //        $this->middleware('checklogin');
        $this->middleware('auth');
        //        $this->middleware('verified');
    }

    public function index()
    {

        if (Session::has('tmp')) {
            Session::forget('tmp');
        }

        $getmcuti = MasterCuti::where('is_active', '1')->get();

        $data = array(
            'title' => 'Cuti Karyawan',
            'menu'  => 'dashboard',
            'mcuti' => $getmcuti
        );

        return view('users::cuti.index', $data);
    }

    public function simpan(Request $req)
    {
        try {
            // dd($req);
            $validator = Validator::make($req->all(), [
                'namasaya' => 'required',
                'niksaya' => 'required',
                'jenisabsen' => 'required',
                'tanggal1' => 'required',
                'tanggal2' => 'required',
                'alasan' => 'required',
                'nikatasan' => 'required',
                'namaatasan' => 'required',
                'nikhrd' => 'required',
                'namahrd' => 'required',
            ], [
                // custom message
                'namasaya.required' => 'Nama Tidak Boleh Kosong',
                'niksaya.required' => 'NIK Tidak Boleh Kosong',
                'jenisabsen.required' => 'Jenis Absen Tidak Boleh Kosong',
                'tanggal1.required' => 'Tanggal 1 Tidak Boleh Kosong',
                'tanggal2.required' => 'Tanggal 2 Tidak Boleh Kosong',
                'alasan.required' => 'Alasan Tidak Boleh Kosong',
                'nikatasan.required' => 'NIK Atasan Tidak Boleh Kosong',
                'namaatasan.required' => 'Nama Atasan Tidak Boleh Kosong',
                'nikhrd.required' => 'NIK HRD Tidak Boleh Kosong',
                'namahrd.required' => 'Nama HRD Tidak Boleh Kosong',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Failed!',
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $nama = $req->namasaya;
            $nik = $req->niksaya;
            $jenisabsen = $req->jenisabsen;
            $tgl1 = $req->tanggal1;
            $tgl2 = $req->tanggal2;
            $alasan = $req->alasan;
            $nikatasan = $req->nikatasan;
            $namaatasan = $req->namaatasan;
            $nikhrd = $req->nikhrd;
            $namahrd = $req->namahrd;

            if ($req->ketedit == 'no') {
                $insert = CutiKaryawan::insert([
                    'idmcuti'         => $jenisabsen,
                    'nik'             => $nik,
                    'nama'            => $nama,
                    'tanggalmulai'    => $tgl1,
                    'tanggalselesai'  => $tgl2,
                    'tanggaldiajukan' => date("Y-m-d H:i:s"),
                    'keterangan'      => $alasan,
                    'nikatasan'       => $nikatasan,
                    'namaatasan'      => $namaatasan,
                    'statusatasan'    => 'waiting',
                    'nikhrd'          => $nikhrd,
                    'namahrd'         => $namahrd,
                    'statushrd'       => 'waiting',
                    'created_at'      => date("Y-m-d H:i:s"),
                    'created_by'      => $nik
                ]);
            } else {
                $insert = CutiKaryawan::where('id', $req->idedit)->where('is_active', '1')->update([
                    'idmcuti'         => $jenisabsen,
                    'nik'             => $nik,
                    'nama'            => $nama,
                    'tanggalmulai'    => $tgl1,
                    'tanggalselesai'  => $tgl2,
                    'keterangan'      => $alasan,
                    'nikatasan'       => $nikatasan,
                    'namaatasan'      => $namaatasan,
                    'statusatasan'    => 'waiting',
                    'nikhrd'          => $nikhrd,
                    'nikhrd'          => $nikhrd,
                    'statushrd'       => 'waiting',
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'updated_by'      => $nik
                ]);
            }

            return response()->json([
                'title' => 'Success!',
                'status' => 'success',
                'message' => 'Data berhasil disimpan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function datatables()
    {
        $data = CutiKaryawan::join('master_cuti',  'master_cuti.id', 'cuti_karyawan.idmcuti')
            ->where('master_cuti.is_active', '1')
            ->where('cuti_karyawan.is_active', '1')
            ->selectRaw(' cuti_karyawan.id as idcuti, cuti_karyawan.tanggalmulai, cuti_karyawan.tanggalselesai, cuti_karyawan.keterangan, cuti_karyawan.statusatasan, cuti_karyawan.statushrd, master_cuti.jeniscuti ')
            ->orderByDesc('cuti_karyawan.created_at')
            ->get();
        // dd($data);
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('jenisabsen', function ($data) {
                return $data->jeniscuti;
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
            ->addColumn('action', function ($data) {
                $button = '';
                if ($data->statusatasan == 'waiting') {
                    $button .= '<center><a href="#" data-id="' . encrypt($data->idcuti) . '" id="btnedit" title="Proses Edit"><i class="fa fa-edit fa-md text-primary"></i></a>';
                } else {
                    $button .= '<a href="#" data-id="' . encrypt($data->idcuti) . '" id="btndetail" class="ml-2" title="Info Detail"><i class="fa fa-info-circle fa-md text-primary"></i></a></center>';
                }
                return $button;
            })
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
            return response()->json([
                'title' => 'Error!',
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $req)
    {
        try {
            $myid = decrypt($req->myid);

            $getdata = CutiKaryawan::join('master_cuti',  'master_cuti.id', 'cuti_karyawan.idmcuti')
                ->where('cuti_karyawan.id', $myid)
                ->where('master_cuti.is_active', '1')
                ->where('cuti_karyawan.is_active', '1')
                ->selectRaw(' cuti_karyawan.*, master_cuti.jeniscuti ')
                ->orderByDesc('cuti_karyawan.created_at')
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

            $form = view('users::cuti.modaldetail', ['data' => $getdata, 'jmlhari' => $jumlahHari, 'tanggal' => $tanggal]);
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
}
