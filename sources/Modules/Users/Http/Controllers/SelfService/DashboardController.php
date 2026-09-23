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

        // 3. Ambil 5 riwayat permohonan surat SDM saya
        $myRecentSurat = \App\Models\RequestSuratSdm::where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // 4. Pengecekan Survei Kepuasan Layanan SDM (Pop-up Wajib jika ada periode aktif)
        $activeSurveyPeriode = \App\Models\MasterPeriodeSurvey::activeNow()->first();
        $needsSurveyLayanan = false;
        $userKaryawan = null;

        if (auth()->check()) {
            $user = auth()->user();
            $userKaryawan = \App\Models\DataDosenTendik::where('user_id', $user->id)->first();
            if (!$userKaryawan && $user) {
                $userKaryawan = \App\Models\DataDosenTendik::where('nama', $user->name)
                    ->orWhere('nik', $user->username ?? '')
                    ->orWhere('nip', $user->username ?? '')
                    ->first();
            }

            if ($activeSurveyPeriode && $userKaryawan) {
                $hasFilled = \App\Models\SurveyLayananResponse::where('periode_id', $activeSurveyPeriode->id)
                    ->where('karyawan_id', $userKaryawan->id)
                    ->exists();
                $needsSurveyLayanan = !$hasFilled;
            }
        }

        // 5. Pengecekan Pelatihan yang Belum Diisi Surveinya / Belum Upload Sertifikat
        $pendingTrainingSurveys = collect();
        if ($userKaryawan) {
            $pendingTrainingSurveys = \App\Models\TrainingPeserta::with('training')
                ->where('karyawan_id', $userKaryawan->id)
                ->where('is_survey_filled', false)
                ->get();
        }

        $data = array(
            'title'                  => 'Dashboard HRIS',
            'menu'                   => 'Dashboard',
            'selectedDate'           => $selectedDate,
            'isToday'                => $selectedDate === Carbon::today()->format('Y-m-d'),
            'formattedDate'          => $parsedDate->locale('id')->translatedFormat('l, d F Y'),
            'totalCuti'              => $cutiHariIni->count(),
            'totalIzin'              => $izinHariIni->count(),
            'totalAbsen'             => $karyawanAbsen->count(),
            'karyawanAbsen'          => $karyawanAbsen,
            'myRecentSurat'          => $myRecentSurat,
            'activeSurveyPeriode'    => $activeSurveyPeriode,
            'needsSurveyLayanan'     => $needsSurveyLayanan,
            'pendingTrainingSurveys' => $pendingTrainingSurveys,
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

        // 3. Tarik data Surat Edaran & SK yang disetel tampil di kalender
        $dataEdaran = \App\Models\SuratEdaranSdm::where('is_active', true)
            ->where('tampilkan_di_kalender', true)
            ->whereNotNull('tanggal_kalender')
            ->when($start && $end, function ($query) use ($start, $end) {
                $startDate = Carbon::parse($start)->format('Y-m-d');
                $endDate = Carbon::parse($end)->format('Y-m-d');
                return $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal_kalender', [$startDate, $endDate])
                      ->orWhere(function($sub) use ($startDate, $endDate) {
                          $sub->whereNotNull('tanggal_kalender_selesai')
                              ->where('tanggal_kalender', '<=', $endDate)
                              ->where('tanggal_kalender_selesai', '>=', $startDate);
                      });
                });
            })
            ->get();

        foreach ($dataEdaran as $edaran) {
            $startDate = Carbon::parse($edaran->tanggal_kalender)->format('Y-m-d');
            $endDate = null;
            if ($edaran->tanggal_kalender_selesai && $edaran->tanggal_kalender_selesai->gt($edaran->tanggal_kalender)) {
                // FullCalendar 'end' is exclusive for all-day events, so add 1 day
                $endDate = Carbon::parse($edaran->tanggal_kalender_selesai)->addDay()->format('Y-m-d');
            }

            // Kategori label dan warna tematik
            $katLabel = match($edaran->kategori) {
                'edaran_libur'     => 'Edaran Libur & Cuti',
                'edaran_jam_kerja' => 'Edaran Jam Kerja',
                'sk_rektor'        => 'SK Rektorat',
                'kebijakan_sdm'    => 'Kebijakan SDM',
                default            => 'Surat Edaran',
            };

            $bgColor = match($edaran->kategori) {
                'edaran_libur'     => '#28a745', // Hijau segar untuk libur/cuti
                'sk_rektor'        => '#6f42c1', // Ungu megah untuk SK Rektor
                'edaran_jam_kerja' => '#fd7e14', // Oranye untuk jam kerja
                default            => '#17a2b8', // Teal info untuk edaran umum
            };

            $eventItem = [
                'id'              => 'edaran-' . $edaran->id,
                'title'           => '[' . $katLabel . '] ' . $edaran->perihal,
                'start'           => $startDate,
                'backgroundColor' => $bgColor,
                'borderColor'     => $bgColor,
                'textColor'       => '#ffffff',
                'allDay'          => true,
                'extendedProps'   => [
                    'type'                 => 'edaran',
                    'id_edaran'            => $edaran->id,
                    'nomor_surat'          => $edaran->nomor_surat,
                    'perihal'              => $edaran->perihal,
                    'kategori_label'       => $katLabel,
                    'tanggal_surat'        => $edaran->tanggal_surat ? $edaran->tanggal_surat->locale('id')->translatedFormat('d F Y') : '-',
                    'tanggal_kalender'     => $edaran->tanggal_kalender ? $edaran->tanggal_kalender->locale('id')->translatedFormat('d F Y') : '-',
                    'tanggal_selesai'      => $edaran->tanggal_kalender_selesai ? $edaran->tanggal_kalender_selesai->locale('id')->translatedFormat('d F Y') : null,
                    'target'               => match($edaran->target_audience) {
                        'dosen'  => 'Khusus Dosen',
                        'tendik' => 'Khusus Tendik',
                        default  => 'Semua Civitas (Dosen & Tendik)',
                    },
                    'keterangan'           => $edaran->keterangan ?? '-',
                    'file_url'             => $edaran->file_url,
                    'download_url'         => route('admin.surat-edaran.download', $edaran->id),
                ]
            ];

            if ($endDate) {
                $eventItem['end'] = $endDate;
            }

            $events[] = $eventItem;
        }

        // 4. Tarik data Kegiatan Universitas yang sesuai dengan target pesertanya
        $user = auth()->user();
        $karyawan = $user ? ($user->karyawan ?? \App\Models\DataDosenTendik::where('user_id', $user->id)->first()) : null;
        $isSuperAdmin = session('is_superadmin') || ($user && method_exists($user, 'hasRole') && $user->hasRole(['Super Admin', 'Admin']));

        $dataKegiatan = \App\Models\Kegiatan::with(['penyelenggaraUnit', 'penanggungJawab'])
            ->where('status', '!=', 'Dibatalkan')
            ->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereBetween('tanggal_kegiatan', [
                    Carbon::parse($start)->format('Y-m-d'),
                    Carbon::parse($end)->format('Y-m-d')
                ]);
            })
            ->get();

        foreach ($dataKegiatan as $kegiatan) {
            $target = $kegiatan->target_peserta;
            $isParticipant = false;

            if ($isSuperAdmin || empty($target) || $target === 'Semua Pegawai') {
                $isParticipant = true;
            } elseif ($karyawan) {
                if ($target === 'Dosen Saja' && ($karyawan->tipe_karyawan === 'Dosen' || !empty($karyawan->nidn))) {
                    $isParticipant = true;
                } elseif ($target === 'Tendik Saja' && $karyawan->tipe_karyawan === 'Tendik') {
                    $isParticipant = true;
                } elseif ($target === 'Unit Tertentu' && $karyawan->unit_id == $kegiatan->penyelenggara_unit_id) {
                    $isParticipant = true;
                } elseif ($kegiatan->penanggung_jawab_id == $karyawan->id) {
                    $isParticipant = true;
                }
            }

            if (!$isParticipant) {
                continue;
            }

            $jamText = substr($kegiatan->jam_mulai, 0, 5) . ' - ' . substr($kegiatan->jam_selesai, 0, 5) . ' WIB';

            $events[] = [
                'id'              => 'kegiatan-' . $kegiatan->id,
                'title'           => $kegiatan->nama_kegiatan,
                'start'           => $kegiatan->tanggal_kegiatan->format('Y-m-d'),
                'backgroundColor' => '#094b54', // Warna tema TSU
                'borderColor'     => '#1d7a87',
                'textColor'       => '#ffffff',
                'allDay'          => true,
                'extendedProps'   => [
                    'type'           => 'kegiatan',
                    'kegiatan_id'    => $kegiatan->id,
                    'nama_kegiatan'  => $kegiatan->nama_kegiatan,
                    'kategori'       => $kegiatan->kategori,
                    'tanggal'        => $kegiatan->tanggal_kegiatan->isoFormat('dddd, D MMMM Y') ?? $kegiatan->tanggal_kegiatan->format('d/m/Y'),
                    'jam'            => $jamText,
                    'lokasi'         => $kegiatan->lokasi,
                    'target_peserta' => $kegiatan->target_peserta,
                    'penyelenggara'  => optional($kegiatan->penyelenggaraUnit)->nama_unit ?? 'Universitas',
                    'pic'            => $kegiatan->penanggungJawab ? ($kegiatan->penanggungJawab->nomor_induk . ' - ' . $kegiatan->penanggungJawab->nama) : '-',
                    'keterangan'     => $kegiatan->keterangan ?: '-',
                    'status'         => $kegiatan->status,
                ]
            ];
        }

        // 5. Tarik data Pelatihan / Training (khusus peserta yang terdaftar mengikuti)
        if ($karyawan) {
            $dataTraining = \App\Models\Training::with(['pesertas' => function($q) use ($karyawan) {
                    $q->where('karyawan_id', $karyawan->id);
                }])
                ->whereHas('pesertas', function($q) use ($karyawan) {
                    $q->where('karyawan_id', $karyawan->id);
                })
                ->when($start && $end, function ($query) use ($start, $end) {
                    $startDate = Carbon::parse($start)->format('Y-m-d');
                    $endDate = Carbon::parse($end)->format('Y-m-d');
                    return $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                          ->orWhere(function($sub) use ($startDate, $endDate) {
                              $sub->whereNotNull('tanggal_selesai')
                                  ->where('tanggal_mulai', '<=', $endDate)
                                  ->where('tanggal_selesai', '>=', $startDate);
                          });
                    });
                })
                ->get();

            foreach ($dataTraining as $tr) {
                $pesertaInfo = $tr->pesertas->first();
                $startDate = Carbon::parse($tr->tanggal_mulai)->format('Y-m-d');
                $endDate = null;
                if ($tr->tanggal_selesai && Carbon::parse($tr->tanggal_selesai)->gt(Carbon::parse($tr->tanggal_mulai))) {
                    // FullCalendar 'end' is exclusive for all-day events, so add 1 day
                    $endDate = Carbon::parse($tr->tanggal_selesai)->addDay()->format('Y-m-d');
                }

                $tglMulaiFormatted = $tr->tanggal_mulai ? Carbon::parse($tr->tanggal_mulai)->locale('id')->translatedFormat('d F Y') : '-';
                $tglSelesaiFormatted = $tr->tanggal_selesai ? Carbon::parse($tr->tanggal_selesai)->locale('id')->translatedFormat('d F Y') : null;

                $eventItem = [
                    'id'              => 'training-' . $tr->id,
                    'title'           => $tr->nama_training,
                    'start'           => $startDate,
                    'backgroundColor' => '#4f46e5', // Indigo modern untuk Training/Kompetensi
                    'borderColor'     => '#4338ca',
                    'textColor'       => '#ffffff',
                    'allDay'          => true,
                    'extendedProps'   => [
                        'type'             => 'training',
                        'training_id'      => $tr->id,
                        'nama_training'    => $tr->nama_training,
                        'penyelenggara'    => $tr->penyelenggara ?? 'Internal TSU',
                        'lokasi'           => $tr->lokasi ?? '-',
                        'tanggal_mulai'    => $tglMulaiFormatted,
                        'tanggal_selesai'  => $tglSelesaiFormatted,
                        'status'           => $tr->status,
                        'status_kehadiran' => $pesertaInfo ? $pesertaInfo->status_kehadiran : 'Terdaftar',
                        'deskripsi'        => $tr->deskripsi ?: '-',
                    ]
                ];

                if ($endDate) {
                    $eventItem['end'] = $endDate;
                }

                $events[] = $eventItem;
            }
        }

        return response()->json($events);
    }
}
