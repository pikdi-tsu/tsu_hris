<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Crypt;
use DB;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Admin\Entities\MasterHariLibur;

use Carbon\Carbon;
use App\Models\CutiKaryawan;
use App\Models\IzinKaryawan;

class DashboardController extends Controller
{
    public function __construct()
    {
//        $this->middleware('checklogin');
        $this->middleware('auth');
//        $this->middleware('verified');
    }

    public function index(Request $request){

        if(Session::has('tmp')){
            Session::forget('tmp');
        }

        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        try {
            $parsedDate = Carbon::parse($tanggal);
            $selectedDate = $parsedDate->format('Y-m-d');
        } catch (\Exception $e) {
            $selectedDate = Carbon::today()->format('Y-m-d');
            $parsedDate = Carbon::today();
        }

        // 1. Ambil data cuti yang aktif, mencakup tanggal terpilih, dan sudah diapprove penuh (atasan & hrd)
        $cutiHariIni = CutiKaryawan::with(['user.unit', 'masterCuti'])
            ->where('is_active', '1')
            ->where('statusatasan', 'approved')
            ->where('statushrd', 'approved')
            ->whereDate('tanggalmulai', '<=', $selectedDate)
            ->whereDate('tanggalselesai', '>=', $selectedDate)
            ->get();

        // 2. Ambil data izin yang aktif, mencakup tanggal terpilih, dan sudah diapprove penuh (atasan & hrd)
        $izinHariIni = IzinKaryawan::with(['user.unit', 'masterIzin'])
            ->where('is_active', '1')
            ->where('statusatasan', 'approved')
            ->where('statushrd', 'approved')
            ->whereDate('tanggalmulai', '<=', $selectedDate)
            ->whereDate('tanggalselesai', '>=', $selectedDate)
            ->get();

        // Satukan ke dalam koleksi informatif
        $karyawanAbsen = collect();

        foreach ($cutiHariIni as $c) {
            $user = $c->user;
            $karyawanAbsen->push((object)[
                'id'             => $c->id,
                'kategori'       => 'Cuti',
                'badge_color'    => 'primary',
                'badge_icon'     => 'fa-umbrella-beach',
                'user'           => $user,
                'nama'           => $user->nama_lengkap ?? $user->nama ?? 'Pegawai',
                'identitas'      => $user->nip ?? $user->nidn ?? $user->nik ?? '-',
                'unit'           => $user->unit->nama_unit ?? '-',
                'posisi'         => $user->posisi ?? ($user->tipe_karyawan ?? '-'),
                'tipe_karyawan'  => $user->tipe_karyawan ?? '-',
                'jenis'          => $c->masterCuti->jeniscuti ?? 'Cuti Tahunan',
                'tanggalmulai'   => $c->tanggalmulai,
                'tanggalselesai' => $c->tanggalselesai,
                'durasi'         => CutiKaryawan::hitungHariEfektif($c->tanggalmulai, $c->tanggalselesai),
                'keterangan'     => $c->keterangan ?? '-',
            ]);
        }

        foreach ($izinHariIni as $i) {
            $user = $i->user;
            $karyawanAbsen->push((object)[
                'id'             => $i->id,
                'kategori'       => 'Izin',
                'badge_color'    => 'warning text-dark',
                'badge_icon'     => 'fa-calendar-check',
                'user'           => $user,
                'nama'           => $user->nama_lengkap ?? $user->nama ?? 'Pegawai',
                'identitas'      => $user->nip ?? $user->nidn ?? $user->nik ?? '-',
                'unit'           => $user->unit->nama_unit ?? '-',
                'posisi'         => $user->posisi ?? ($user->tipe_karyawan ?? '-'),
                'tipe_karyawan'  => $user->tipe_karyawan ?? '-',
                'jenis'          => $i->masterIzin->jenisizin ?? 'Izin Kerja',
                'tanggalmulai'   => $i->tanggalmulai,
                'tanggalselesai' => $i->tanggalselesai,
                'durasi'         => IzinKaryawan::hitungHariEfektif($i->tanggalmulai, $i->tanggalselesai),
                'keterangan'     => $i->keterangan ?? '-',
            ]);
        }

        $data = array(
            'title'          => 'Dashboard',
            'menu'           => 'dashboard',
            'selectedDate'   => $selectedDate,
            'isToday'        => $selectedDate === Carbon::today()->format('Y-m-d'),
            'formattedDate'  => $parsedDate->locale('id')->translatedFormat('l, d F Y'),
            'totalCuti'      => $cutiHariIni->count(),
            'totalIzin'      => $izinHariIni->count(),
            'totalAbsen'     => $karyawanAbsen->count(),
            'karyawanAbsen'  => $karyawanAbsen,
        );

        return view('users::selfservice.dashboard', $data);
    }

    /**
     * API Internal FullCalendar: Hari Libur (Danger) & Jadwal Piket Sabtu (Primary)
     */
    public function getHolidays(Request $request)
    {
        // FullCalendar otomatis mengirim parameter 'start' dan 'end' setiap kali bulan berganti
        $start = $request->start;
        $end = $request->end;

        $events = [];

        // 1. Tarik data hari libur yang aktif dan sesuai rentang bulan
        $dataLibur = MasterHariLibur::whereIn('isactive', ['Y', 'y', 1, '1'])
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('tanggal', [
                    Carbon::parse($start)->format('Y-m-d'),
                    Carbon::parse($end)->format('Y-m-d')
                ]);
            })
            ->get();

        foreach ($dataLibur as $libur) {
            $events[] = [
                'id'              => 'libur-' . $libur->id,
                'title'           => $libur->keterangan,
                'start'           => Carbon::parse($libur->tanggal)->format('Y-m-d'),
                'backgroundColor' => '#dc3545', // Danger (Merah) sesuai permintaan
                'borderColor'     => '#dc3545',
                'textColor'       => '#ffffff',
                'allDay'          => true,
                'extendedProps'   => [
                    'type'        => 'libur',
                    'status'      => $libur->status_libur ?? 'Hari Libur',
                    'keterangan'  => $libur->keterangan,
                ]
            ];
        }

        // 2. Tarik data jadwal piket hari Sabtu dari menu Absensi > Jadwal Piket
        $dataPiket = \App\Models\DataJadwalPiket::with(['karyawan.unit'])
            ->where('is_active', true)
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('tanggal_piket', [
                    Carbon::parse($start)->format('Y-m-d'),
                    Carbon::parse($end)->format('Y-m-d')
                ]);
            })
            ->get();

        foreach ($dataPiket as $piket) {
            $tgl = Carbon::parse($piket->tanggal_piket);
            // Sesuai permintaan: tampilkan siapa saja yang piket di hari Sabtu
            if ($tgl->isSaturday()) {
                $karyawan = $piket->karyawan;
                $nama = $karyawan->nama_lengkap ?? $karyawan->nama ?? 'Pegawai';
                $jam = '';
                if ($piket->jam_mulai && $piket->jam_selesai) {
                    $jam = substr($piket->jam_mulai, 0, 5) . ' - ' . substr($piket->jam_selesai, 0, 5);
                }

                $events[] = [
                    'id'              => 'piket-' . $piket->id,
                    'title'           => 'Piket: ' . $nama,
                    'start'           => $tgl->format('Y-m-d'),
                    'backgroundColor' => '#007bff', // Primary (Biru) untuk membedakan dari libur
                    'borderColor'     => '#007bff',
                    'textColor'       => '#ffffff',
                    'allDay'          => true,
                    'extendedProps'   => [
                        'type'        => 'piket',
                        'nama'        => $nama,
                        'unit'        => $karyawan->unit->nama_unit ?? '-',
                        'jam'         => $jam ?: '-',
                        'keterangan'  => $piket->keterangan ?? '-',
                    ]
                ];
            }
        }

        return response()->json($events);
    }
}
