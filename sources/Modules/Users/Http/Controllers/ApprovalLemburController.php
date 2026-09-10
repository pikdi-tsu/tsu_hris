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

use App\Models\LemburKaryawan;
use App\Models\DataDosenTendik;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Services\TsuErrorHandlerService;
use Modules\System\Models\MenuSidebar;
use App\Notifications\LemburDiajukanNotification;
use App\Notifications\PengajuanDiprosesNotification;

class ApprovalLemburController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
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

        $menuIcon = MenuSidebar::where('route', 'users.approval-lembur.index')->value('icon') ?? 'fas fa-business-time';

        $data = [
            'title'    => 'Approval Lembur Karyawan',
            'menu'     => 'dashboard',
            'menuIcon' => $menuIcon,
        ];

        return view('users::approvallembur.index', $data);
    }

    public function datatables()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['super admin', 'super admin hris', 'admin', 'admin hris']);
        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        $query = LemburKaryawan::with(['masterLembur', 'user', 'atasan', 'hrd'])
            ->where('is_active', '1');

        if ($isAdmin) {
            $query->where(function ($q) {
                $q->where('statusatasan', 'waiting')
                  ->orWhere('statushrd', 'waiting');
            });
        } else {
            $query->where(function ($q) use ($profileId) {
                $q->where(function ($q2) use ($profileId) {
                    $q2->where('id_atasan', $profileId)
                       ->where('statusatasan', 'waiting');
                })->orWhere(function ($q2) use ($profileId) {
                    $q2->where('id_hrd', $profileId)
                       ->where('statushrd', 'waiting');
                });
            });
        }

        $data = $query->orderByDesc('created_at')->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', function ($data) {
                $nama = $data->user ? e($data->user->nama) : '-';
                $nik = $data->user && $data->user->nik ? '<br><small class="text-muted">' . e($data->user->nik) . '</small>' : '';
                return $nama . $nik;
            })
            ->addColumn('jenislembur', function ($data) {
                return $data->masterLembur ? e($data->masterLembur->jenislembur) : '-';
            })
            ->addColumn('tanggal', function ($data) {
                if (!$data->tanggalmulai) return '-';
                return Carbon::parse($data->tanggalmulai)->translatedFormat('d M Y');
            })
            ->addColumn('waktu', function ($data) {
                if (!$data->tanggalmulai || !$data->tanggalselesai) return '-';
                $jamMulai = Carbon::parse($data->tanggalmulai)->format('H:i');
                $jamSelesai = Carbon::parse($data->tanggalselesai)->format('H:i');
                return $jamMulai . ' - ' . $jamSelesai . ' <br><span class="badge badge-secondary">' . $data->total_jam . ' Jam</span>';
            })
            ->addColumn('status', function ($data) {
                if ($data->statusatasan == 'waiting') {
                    return '<span class="badge badge-warning">Menunggu Atasan</span>';
                } elseif ($data->statusatasan == 'approved' && $data->statushrd == 'waiting') {
                    return '<span class="badge badge-info">Menunggu SDM</span>';
                } elseif ($data->statusatasan == 'approved' && $data->statushrd == 'approved') {
                    return '<span class="badge badge-success">Disetujui</span>';
                } elseif ($data->statusatasan == 'rejected' || $data->statushrd == 'rejected') {
                    return '<span class="badge badge-danger">Ditolak</span>';
                }
                return '<span class="badge badge-secondary">' . e($data->statusatasan) . '</span>';
            })
            ->addColumn('keterangan', function ($data) {
                return e($data->keterangan ?? '-');
            })
            ->addColumn('action', function ($data) use ($profileId, $isAdmin) {
                $canApprove = false;
                if ($isAdmin) {
                    $canApprove = true;
                } elseif ($data->id_hrd == $profileId && $data->statusatasan != 'waiting') {
                    $canApprove = true;
                } elseif ($data->id_atasan == $profileId) {
                    $canApprove = true;
                }

                if ($canApprove) {
                    return '<center><a href="#" data-id="' . encrypt($data->id) . '" id="btnapproval" class="btn btn-sm btn-outline-primary" title="Proses Approval"><i class="fa fa-angle-double-right fa-md"></i></a></center>';
                }
                return '';
            })
            ->rawColumns(['nama', 'waktu', 'status', 'action'])
            ->make(true);
    }

    public function detail(Request $req)
    {
        try {
            $myid = decrypt($req->myid);
            $profile = $this->getCurrentProfile();
            $user = Auth::user();
            $isAdmin = $user->hasRole(['super admin', 'super admin hris', 'admin', 'admin hris']);

            $getdata = LemburKaryawan::with(['masterLembur', 'user', 'atasan', 'hrd'])
                ->where('id', $myid)
                ->where('is_active', '1')
                ->first();

            if (!$getdata) {
                throw new \Exception("Data lembur tidak ditemukan.");
            }

            $mulai = Carbon::parse($getdata->tanggalmulai);
            $selesai = Carbon::parse($getdata->tanggalselesai);

            if ($mulai->format('Y-m-d') == $selesai->format('Y-m-d')) {
                $tanggal = $mulai->translatedFormat('d M Y');
            } else {
                $tanggal = $mulai->translatedFormat('d M Y') . ' - ' . $selesai->translatedFormat('d M Y');
            }
            $waktu = $mulai->format('H:i') . ' - ' . $selesai->format('H:i') . ' (' . $getdata->total_jam . ' Jam)';

            $form = view('users::approvallembur.modaldetail', [
                'data'     => $getdata,
                'profile'  => $profile,
                'isAdmin'  => $isAdmin,
                'tanggal'  => $tanggal,
                'waktu'    => $waktu
            ]);
            return $form->render();
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_APV_LEMBUR_DTL]', 'Gagal memuat detail persetujuan lembur.');
        }
    }

    public function simpan(Request $req)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($req->all(), [
                'approval' => 'required|in:approved,rejected',
            ], [
                'approval.required' => 'Keputusan Approval Tidak Boleh Kosong',
                'approval.in'       => 'Pilihan Approval tidak valid',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first());
            }

            $user = Auth::user();
            $isAdmin = $user->hasRole(['super admin', 'super admin hris', 'admin', 'admin hris']);
            $profile = $this->getCurrentProfile();
            $iduserlogin = $profile ? $profile->id : null;

            $idlemburkaryawan = $req->idlemburkaryawan;
            $approval = $req->approval;
            $ketapproval = $req->ketapproval;

            if ($approval == 'rejected' && empty($ketapproval)) {
                return $this->sendError('Jika Approval Ditolak, Alasan Penolakan Wajib Diisi.');
            }

            $check = LemburKaryawan::where('id', $idlemburkaryawan)
                ->where('is_active', '1')
                ->where(function ($q) {
                    $q->where('statusatasan', 'waiting')
                      ->orWhere('statushrd', 'waiting');
                })
                ->first();

            if (!$check) {
                DB::rollback();
                return $this->sendError('Data lembur tidak ditemukan atau status sudah berubah.');
            }

            // Determine if acting as Atasan or HRD
            $isAtasan = ($iduserlogin && $check->id_atasan == $iduserlogin) || ($isAdmin && $check->statusatasan == 'waiting');
            $isHrd = ($iduserlogin && $check->id_hrd == $iduserlogin) || ($isAdmin && $check->statusatasan == 'approved' && $check->statushrd == 'waiting');

            if ($isAtasan && $check->statusatasan == 'waiting') {
                $updateData = [
                    'statusatasan'       => $approval,
                    'alasanatasan'       => $ketapproval,
                    'atasanapprovaldate' => date('Y-m-d H:i:s'),
                    'updated_by'         => $profile->nik ?? Auth::id()
                ];

                if ($approval == 'rejected') {
                    $updateData['statushrd'] = 'rejected';
                    $updateData['alasanhrd'] = 'Dibatalkan karena ditolak Atasan: ' . ($ketapproval ?? '-');
                    $updateData['hrdapprovaldate'] = date('Y-m-d H:i:s');
                }

                $check->update($updateData);

                // Notify HRD if Atasan approves
                if ($approval == 'approved' && $check->id_hrd) {
                    $hrdProfile = DataDosenTendik::find($check->id_hrd);
                    if ($hrdProfile && $hrdProfile->user_id) {
                        $hrdUser = User::find($hrdProfile->user_id);
                        if ($hrdUser) {
                            $karyawanProfile = DataDosenTendik::find($check->id_user);
                            $namaKaryawan = $karyawanProfile ? $karyawanProfile->nama : 'Karyawan';
                            $hrdUser->notify(new LemburDiajukanNotification(
                                $check,
                                'Pengajuan lembur dari ' . $namaKaryawan . ' telah disetujui Atasan dan menunggu persetujuan Anda.',
                                'hrd'
                            ));
                        }
                    }
                }
            } elseif ($isHrd && $check->statusatasan == 'approved' && $check->statushrd == 'waiting') {
                $check->update([
                    'statushrd'       => $approval,
                    'alasanhrd'       => $ketapproval,
                    'hrdapprovaldate' => date('Y-m-d H:i:s'),
                    'updated_by'      => $profile->nik ?? Auth::id()
                ]);
            } else {
                DB::rollback();
                return $this->sendError('Anda tidak memiliki wewenang atau status pengajuan belum valid untuk diproses.');
            }

            // Real-Time Notification to Karyawan (Feedback)
            $karyawanProfile = DataDosenTendik::find($check->id_user);
            if ($karyawanProfile && $karyawanProfile->user_id) {
                $karyawanUser = User::find($karyawanProfile->user_id);
                if ($karyawanUser) {
                    $statusText = $approval == 'approved' ? 'Disetujui' : 'Ditolak';
                    $roleText = $isAtasan ? 'Atasan' : 'SDM';
                    $iconClass = $approval == 'approved' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                    $karyawanUser->notify(new PengajuanDiprosesNotification(
                        "Pengajuan Lembur Anda telah {$statusText} oleh {$roleText}.",
                        'feedback',
                        route('users.lembur.index'),
                        'Cek Riwayat',
                        $iconClass
                    ));
                }
            }

            DB::commit();
            return $this->sendSuccess('Approval Lembur Berhasil Disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return TsuErrorHandlerService::handleJson($e, '[TSU_APV_LEMBUR_SAVE_FAIL]', 'Gagal menyimpan persetujuan lembur.');
        }
    }
}
