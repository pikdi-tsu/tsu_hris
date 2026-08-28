<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\DataDosenTendik;
use App\Models\DataAbsensi;

class RiwayatAbsensiController extends MiddlewareController
{
    public function __construct()
    {
        // $this->registerPermissions('admin:master-cuti');
        $this->middleware('auth');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $bulan = $this->getBulan();

        return view('admin::riwayatabsensi.index', ['title' => 'Data Riwayat Absensi', 'bulan' => $bulan]);
    }

    public function datatableabsensi(Request $req)
    {
        // dd($req->periodebulan, $req->);
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        // dd($currentMonth, $currentYear);

        $data = DataAbsensi::with(['users'])->selectRaw('pin, nama, COUNT(*) as total_hari')->groupBy('pin', 'nama')->get();
        // $data = DataAbsensi::groupBy('pin')->get();
        // dd($data->users->nik);
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nik', function ($data) {
                return $data->users->nik ?? ' ';
            })
            ->addColumn('nama', function ($data) {
                // return $data->nama;
                return $data->users->nama ?? ' ';
            })
            // ->addColumn('periode', function ($data) {
            //     return $this->getBulan()[$data->periode_bulan] . ' ' . $data->periode_tahun;
            // })
            ->addColumn('hadir', function ($data) {
                // return $data->nama;
                return $data->total_hari;
            })
            ->addColumn('aksi', function ($data) {
                $button = '';
                $button .= '<a href="#" data-id="' . encrypt($data->pin) . '" id="btndetail" class="ml-2" title="Info Detail"><i class="fa fa-info-circle fa-md text-primary"></i></a></center>';
                return $button;
            })
            // ->addColumn('tanggal', function ($data) {
            //     return Carbon::createFromFormat('Y-m-d', $data->tanggal_absen)->format('d-m-Y');
            // })
            // ->addColumn('scan3', function ($data) {
            //     return $data->scan_3 ?? NULL;
            // })
            // ->addColumn('scan4', function ($data) {
            //     return $data->scan_4 ?? NULL;
            // })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function simpanexcel(Request $request)
    {
        $request->validate(
            [
                'periodebulan' => 'required|integer|between:1,12',
                'periodetahun' => 'required|digits:4',
                'absensiexcel' => 'required|file|mimes:xlsx,xls'
            ],
            [
                'absensiexcel.required' => 'Silahkan pilih file terlebih dahulu.',
                'absensiexcel.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
                'periodebulan.required' => 'Silahkan pilih periode bulan terlebih dahulu.',
                'periodetahun.required' => 'Silahkan pilih periode tahun terlebih dahulu.',
            ]
        );

        try {
            $data = Excel::toArray([], $request->file('absensiexcel'));

            // Ambil sheet pertama
            $rows = array_slice($data[0], 2);

            $berhasil = 0;
            $duplikat = 0;
            foreach ($rows as $index => $row) {
                $tanggal = Carbon::createFromFormat('d-m-Y', $row[6])->format('Y-m-d');
                $exist = DataAbsensi::where('pin', $row[0])->where('tanggal_absen', $tanggal)->exists();
                // dd($tanggal, $exist);
                if ($exist) {
                    $duplikat++;
                    continue;
                }

                $insert = DataAbsensi::insert([
                    'pin'           => $row[0],
                    'nama'          => $row[2],
                    'tanggal_absen' => $tanggal,
                    'scan_1'        => $row[7] ?? null,
                    'scan_2'        => $row[8] ?? null,
                    'scan_3'        => !empty($row[9]) ? $row[9] : null,
                    'scan_4'        => !empty($row[10]) ? $row[10] : null,
                    'periode_bulan' => $request->periodebulan,
                    'periode_tahun' => $request->periodetahun,
                    'uploaded_at'   => Carbon::now()->format('Y-m-d H:i:s'),
                    'uploaded_nik'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
                    'uploaded_name' => Auth::check() ? $this->getCurrentProfile()->nama : 'System',
                ]);

                $berhasil++;
            }

            return back()->with('success', 'Import Absensi. ' . $berhasil . ' data berhasil disimpan, ' . $duplikat . ' data duplikat dilewati.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_ABSENSI_STORE_FAIL]',
                'Gagal menyimpan Absensi.',
                'Upload Absensi.',
                $request
            );
        }
    }

    public function update(Request $request)
    {
        $request->validate(
            [
                'periodebulanold' => 'required|integer|between:1,12',
                'periodetahunold' => 'required|digits:4',
                'periodebulannew' => 'required|integer|between:1,12',
                'periodetahunnew' => 'required|digits:4',
            ],
            [
                'periodebulanold.required' => 'Silahkan pilih periode bulan old terlebih dahulu.',
                'periodetahunold.required' => 'Silahkan pilih periode tahun old terlebih dahulu.',
                'periodebulannew.required' => 'Silahkan pilih periode bulan new terlebih dahulu.',
                'periodetahunnew.required' => 'Silahkan pilih periode tahun new terlebih dahulu.',
            ]
        );

        $checkperiode = DataAbsensi::where('periode_bulan', $request->periodebulanold)->where('periode_tahun', $request->periodetahunold)->exists();
        if (!$checkperiode) {
            return back()->with('error', 'Data Periode Tersebut Tidak Ditemukan');
        }

        try {
            $update = DataAbsensi::where('periode_bulan', $request->periodebulanold)->where('periode_tahun', $request->periodetahunold)->update([
                'periode_bulan' => $request->periodebulannew,
                'periode_tahun' => $request->periodetahunnew,
                'updated_at'   => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_nik'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
                'updated_name' => Auth::check() ? $this->getCurrentProfile()->nama : 'System',
            ]);

            return back()->with('success', 'Periode Absensi Berhasil Diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_UPDATE_ABSENSI_FAIL]',
                'Gagal menyimpan perubahan periode absensi.',
                "Update Periode Absensi",
                $request
            );
        }
    }

    public function getBulan()
    {
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $bulan;
    }
}
