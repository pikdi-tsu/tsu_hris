<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Modules\System\Models\MenuSidebar;

use App\Models\MasterCuti;
use App\Models\CutiKaryawan;
use App\Models\SaldoCutiKaryawan;
use App\Models\DataDosenTendik;
use App\Models\KaryawanJabatanStruktural;
use App\Traits\ApiResponseTrait;
use App\Services\TsuErrorHandlerService;
use App\Models\User;
use App\Notifications\CutiDiajukanNotification;
use App\Models\MasterUnit;
use App\Services\OrgStructureService;

class CutiController extends Controller
{
    use ApiResponseTrait;
    protected $orgService;

    public function __construct(OrgStructureService $orgService)
    {
        //        $this->middleware('checklogin');
        $this->middleware('auth');
        $this->orgService = $orgService;
        //        $this->middleware('verified');
    }

    private function getCurrentProfile()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $profile = DataDosenTendik::where('user_id', $user->id)->first();

        // Fallback: Jika belum tertaut user_id, cari berdasarkan nama atau NIK
        if (!$profile) {
            $profile = DataDosenTendik::where('nama', $user->name)
                ->orWhere('nik', $user->name)
                ->first();

            if ($profile && empty($profile->user_id)) {
                $profile->update(['user_id' => $user->id]);
            }
        }

        return $profile;
    }

    public function index()
    {

        if (Session::has('tmp')) {
            Session::forget('tmp');
        }

        $getmcuti = MasterCuti::where('is_active', '1')->get();
        $profile = $this->getCurrentProfile();

        // Get list of SDM for dropdown selection
        $listSdm = DataDosenTendik::whereNotNull('nama')
                        ->where('tipe_karyawan', 'Tendik')
                        ->where(function ($q) {
                            $q->where('posisi', 'like', '%SDM%')
                              ->orWhere('posisi', 'like', '%Sumber Daya Manusia%');
                        })
                        ->orderBy('nama', 'asc')
                        ->get(['id', 'nama', 'nik']);

        $isAtasan = false;
        $namaAtasan = 'Belum/Tidak Ada Atasan (Silakan hubungi SDM)';
        $atasanId = null;
        if ($profile) {
            $isKepala = KaryawanJabatanStruktural::where('data_dosen_tendik_id', $profile->id)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])->exists();

            if ($profile->unit_id) {
                $unit = MasterUnit::find($profile->unit_id);
                if ($unit) {
                    $atasanId = $this->orgService->findAtasanId($unit, $profile->id);
                    if ($atasanId) {
                        $atasan = DataDosenTendik::find($atasanId);
                        if ($atasan) {
                            $namaAtasan = $atasan->nama;
                        }
                    }
                }
            }
        }
        $saldoService = app(\App\Services\SaldoCutiService::class);
        $getsaldo = $profile ? SaldoCutiKaryawan::where('id_user', $profile->id)->where('is_active', '1')->first() : null;
        if ($profile && !$getsaldo) {
            $getsaldo = $saldoService->ensureSaldoKaryawan($profile);
        }

        $menuData = MenuSidebar::where('route', 'users.cuti.index')->first();
        $data = array(
            'title'     => 'Cuti Karyawan',
            'menu'      => 'dashboard',
            'mcuti'     => $getmcuti,
            'karyawans' => $listSdm,
            'profile'   => $profile,
            'saldo'     => $getsaldo,
            'namaAtasan' => $namaAtasan,
            'menuIcon'  => $menuData->icon ?? 'fas fa-calendar-minus',
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
                'id_hrd'    => 'required',
            ], [
                // custom message
                'jeniscuti.required' => 'Jenis Cuti Tidak Boleh Kosong',
                'tanggal1.required' => 'Tanggal Mulai Tidak Boleh Kosong',
                'tanggal2.required' => 'Tanggal Selesai Tidak Boleh Kosong',
                'tanggal2.after_or_equal' => 'Waktu Selesai harus setelah Waktu Mulai',
                'alasan.required' => 'Alasan Tidak Boleh Kosong',
                'id_hrd.required' => 'HRD Tidak Boleh Kosong',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first());
            }

            $profile = $this->getCurrentProfile();
            if (!$profile) {
                return $this->sendError('Profil karyawan tidak ditemukan.');
            }

            if (!$profile->unit_id) {
                return $this->sendError('Unit Anda tidak ditemukan di sistem.');
            }

            $unit = MasterUnit::find($profile->unit_id);
            if (!$unit) {
                return $this->sendError('Unit Anda tidak ditemukan di sistem.');
            }

            $idatasan = $this->orgService->findAtasanId($unit, $profile->id);
            if (!$idatasan) {
                return $this->sendError('Unit Anda (atau Unit Induk) belum memiliki Kepala Unit. Silakan hubungi SDM.');
            }

            $iduser = $profile->id;
            $jeniscuti = $req->jeniscuti;
            $tgl1 = $req->tanggal1;
            $tgl2 = $req->tanggal2;
            $alasan = $req->alasan;
            $idhrd = $req->id_hrd;

            // Validasi Hari Kerja Efektif & Saldo Cuti
            $jumlahHari = CutiKaryawan::hitungHariEfektif($tgl1, $tgl2);
            if ($jumlahHari <= 0) {
                return $this->sendError('Gagal mengajukan: Rentang tanggal yang dipilih tidak memuat hari kerja efektif (semua tanggal merupakan akhir pekan atau hari libur nasional).');
            }

            $checksaldo = SaldoCutiKaryawan::where('id_user', $iduser)->where('is_active', '1')->first();
            if (!$checksaldo && $profile) {
                $checksaldo = app(\App\Services\SaldoCutiService::class)->ensureSaldoKaryawan($profile);
            }

            if (!$checksaldo) {
                return $this->sendError('Gagal mengajukan: Anda belum memiliki data Saldo Cuti aktif karena masa kerja belum mencapai 2 tahun. Silakan hubungi SDM untuk konfirmasi kebijakan lebih lanjut.');
            }

            // Hitung pengajuan cuti berstatus waiting (belum di-reject) milik karyawan ini
            $pendingCutiList = CutiKaryawan::where('id_user', $iduser)
                ->where('is_active', '1')
                ->where('statushrd', 'waiting')
                ->where('statusatasan', '!=', 'rejected')
                ->when($req->ketedit != 'no' && $req->idedit, function ($q) use ($req) {
                    $q->where('id', '!=', $req->idedit);
                })
                ->get();

            $totalPendingHari = 0;
            foreach ($pendingCutiList as $pCuti) {
                $totalPendingHari += CutiKaryawan::hitungHariEfektif($pCuti->tanggalmulai, $pCuti->tanggalselesai);
            }

            $sisaTersedia = $checksaldo->sisa - $totalPendingHari;
            if ($sisaTersedia < $jumlahHari) {
                return $this->sendError('Gagal mengajukan: Sisa saldo cuti Anda tidak mencukupi. Sisa saldo saat ini: ' . $checksaldo->sisa . ' hari, pengajuan lain yang menunggu persetujuan: ' . $totalPendingHari . ' hari, kuota tersedia: ' . max(0, $sisaTersedia) . ' hari, sedangkan pengajuan ini membutuhkan: ' . $jumlahHari . ' hari kerja.');
            }

            $cutiId = null;
            if ($req->ketedit == 'no') {
                $cutiId = CutiKaryawan::insertGetId([
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
                $cutiId = $req->idedit;
                CutiKaryawan::where('id', $cutiId)->where('is_active', '1')->update([
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

            // Real-Time Notifications
            if ($cutiId) {
                $cutiCreated = CutiKaryawan::find($cutiId);

                // Notify Atasan
                if ($idatasan) {
                    $atasanProfile = DataDosenTendik::find($idatasan);
                    if ($atasanProfile && $atasanProfile->user_id) {
                        $atasanUser = User::find($atasanProfile->user_id);
                        if ($atasanUser) {
                            $atasanUser->notify(new CutiDiajukanNotification(
                                $cutiCreated,
                                'Pengajuan cuti baru dari ' . ($profile->nama ?? 'Bawahan') . ' menunggu persetujuan Anda.'
                            ));
                        }
                    }
                }

                // Notify HRD
                if ($idhrd) {
                    $hrdProfile = DataDosenTendik::find($idhrd);
                    if ($hrdProfile && $hrdProfile->user_id) {
                        $hrdUser = User::find($hrdProfile->user_id);
                        if ($hrdUser) {
                            $hrdUser->notify(new CutiDiajukanNotification(
                                $cutiCreated,
                                'Ada pengajuan cuti baru dari ' . ($profile->nama ?? 'Karyawan') . ' yang diajukan ke Atasan.',
                                'hrd'
                            ));
                        }
                    }
                }
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
                return '<span class="font-weight-bold" style="color:var(--tsu-primary-dark);">' . ($data->masterCuti ? e($data->masterCuti->jeniscuti) : '-') . '</span>';
            })
            ->addColumn('tanggalmulai', function ($data) {
                return '<span class="text-nowrap"><i class="far fa-calendar-alt text-muted mr-1"></i>' . Carbon::parse($data->tanggalmulai)->translatedFormat('d M Y') . '</span>';
            })
            ->addColumn('tanggalselesai', function ($data) {
                return '<span class="text-nowrap"><i class="far fa-calendar-check text-muted mr-1"></i>' . Carbon::parse($data->tanggalselesai)->translatedFormat('d M Y') . '</span>';
            })
            ->addColumn('jumlah', function ($data) {
                $jml = CutiKaryawan::hitungHariEfektif($data->tanggalmulai, $data->tanggalselesai);
                return '<span class="badge px-2 py-1 font-weight-bold" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-size:0.8rem;">' . $jml . ' Hari</span>';
            })
            ->addColumn('statusatasan', function ($data) {
                if ($data->statusatasan == 'approved') {
                    $stat = '<span class="badge px-2 py-1" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;font-size:0.78rem;font-weight:600;"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>';
                } elseif ($data->statusatasan == 'rejected') {
                    $stat = '<span class="badge px-2 py-1" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;font-size:0.78rem;font-weight:600;"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>';
                } else {
                    $stat = '<span class="badge px-2 py-1" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-size:0.78rem;font-weight:600;"><i class="fas fa-hourglass-half mr-1"></i> Menunggu</span>';
                }

                return $stat;
            })
            ->addColumn('statushrd', function ($data) {
                if ($data->statushrd == 'approved') {
                    $stat = '<span class="badge px-2 py-1" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;font-size:0.78rem;font-weight:600;"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>';
                } elseif ($data->statushrd == 'rejected') {
                    $stat = '<span class="badge px-2 py-1" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;font-size:0.78rem;font-weight:600;"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>';
                } else {
                    $stat = '<span class="badge px-2 py-1" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-size:0.78rem;font-weight:600;"><i class="fas fa-hourglass-half mr-1"></i> Menunggu</span>';
                }

                return $stat;
            })
            ->addColumn('action', function ($data) {
                $encId = encrypt($data->id);
                $button = '<div class="d-inline-flex align-items-center" style="gap:0.35rem;">';
                if ($data->statusatasan == 'waiting' && $data->statushrd == 'waiting') {
                    $button .= '<button type="button" data-id="' . $encId . '" id="btnedit" class="btn btn-xs btn-outline-primary" style="padding:0.25rem 0.6rem;font-size:0.78rem;border-radius:var(--tsu-radius);font-weight:600;" title="Edit Pengajuan"><i class="fas fa-edit mr-1"></i> Edit</button>';
                }
                $button .= '<button type="button" data-id="' . $encId . '" id="btndetail" class="btn btn-xs btn-outline-info" style="padding:0.25rem 0.6rem;font-size:0.78rem;border-radius:var(--tsu-radius);font-weight:600;" title="Lihat Detail"><i class="fas fa-eye mr-1"></i> Detail</button>';
                $button .= '</div>';
                return $button;
            })
            ->rawColumns(['jeniscuti', 'tanggalmulai', 'tanggalselesai', 'jumlah', 'statusatasan', 'statushrd', 'action'])
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

            $jumlahHari = CutiKaryawan::hitungHariEfektif($getdata->tanggalmulai, $getdata->tanggalselesai);

            $form = view('users::cuti.modaldetail', ['data' => $getdata, 'profile' => $profile, 'jmlhari' => $jumlahHari, 'tanggal' => $tanggal]);
            return $form->render();
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_CUTI_DTL]', 'Gagal memuat detail cuti.');
        }
    }
}
