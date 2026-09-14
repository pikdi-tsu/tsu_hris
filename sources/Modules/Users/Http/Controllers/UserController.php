<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;

class UserController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('users:user');
    }

    // Halaman Utama
    public function index()
    {
        $this->guard('view', 'users:user');

        $stats = [
            'total' => User::count(),
            'tendik' => User::role('tendik')->count(),
            'dosen' => User::role('dosen')->count(),
            'admin' => User::role(['super admin hris', 'admin hris testing'])->distinct('id')->count(),
        ];

        $title = 'Data Pengguna Modul';
        $menu = 'users';
        $menuIcon = \Modules\System\Models\MenuSidebar::where('route', 'users.user.index')->value('icon') ?? 'fas fa-users';

        return view('users::user.index', compact('stats', 'title', 'menu', 'menuIcon'));
    }

    // JSON DataTables
    public function datatable()
    {
        $this->guard('view', 'users:user');

        // Eager load roles biar performa cepat
        $data = User::query()->with('roles')->orderBy('last_login_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('avatar', function($row){
                // Avatar Otomatis dari Inisial Nama
                $url = $row->profile_photo_url;
                $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($row->name) . '&color=094B54&background=D0EEF2&bold=true';
                return '<div class="d-flex justify-content-center align-items-center"><img src="'.$url.'" onerror="this.onerror=null;this.src=\''.$fallback.'\';" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #ffffff;" alt="User Image"></div>';
            })
            ->editColumn('name', function($row) {
                return '<div class="font-weight-600 text-dark" style="font-size: 0.88rem;">' . e($row->name) . '</div>';
            })
            ->editColumn('email', function($row) {
                return '<span class="text-secondary font-weight-500" style="font-size: 0.84rem;"><i class="far fa-envelope mr-1 text-muted"></i>' . e($row->email) . '</span>';
            })
            ->editColumn('roles', function ($row) {
                if ($row->roles->isEmpty()) {
                    return '<span class="badge badge-light border text-muted" style="padding: 0.35rem 0.6rem; border-radius: 6px;">Belum Ada Role</span>';
                }
                $badges = '<div class="d-flex flex-wrap gap-1" style="gap: 4px;">';
                foreach ($row->roles as $role) {
                    $rName = strtolower($role->name);
                    if ($rName === 'super admin hris') {
                        $badges .= '<span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-weight:600; padding:0.32rem 0.6rem; border-radius:6px;"><i class="fas fa-crown mr-1"></i>' . e($role->name) . '</span>';
                    } elseif (str_contains($rName, 'admin')) {
                        $badges .= '<span class="badge" style="background:#cce6e9; color:#094b54; border:1px solid #99cdd3; font-weight:600; padding:0.32rem 0.6rem; border-radius:6px;"><i class="fas fa-user-shield mr-1"></i>' . e($role->name) . '</span>';
                    } elseif ($rName === 'dosen') {
                        $badges .= '<span class="badge" style="background:#dcfce7; color:#166534; border:1px solid #bbf7d0; font-weight:600; padding:0.32rem 0.6rem; border-radius:6px;"><i class="fas fa-chalkboard-teacher mr-1"></i>' . e($role->name) . '</span>';
                    } elseif ($rName === 'tendik') {
                        $badges .= '<span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-weight:600; padding:0.32rem 0.6rem; border-radius:6px;"><i class="fas fa-user-tie mr-1"></i>' . e($role->name) . '</span>';
                    } else {
                        $badges .= '<span class="badge badge-light border text-dark" style="font-weight:600; padding:0.32rem 0.6rem; border-radius:6px;">' . e($role->name) . '</span>';
                    }
                }
                $badges .= '</div>';
                return $badges;
            })
            ->addColumn('action', function ($row) {
                if ($row->email === config('app.pikdi.email', 'pikdi@tsu.ac.id')) {
                    return '<div class="text-center"><span class="badge px-2 py-1 font-weight-bold" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; border-radius: 6px;"><i class="fas fa-shield-alt mr-1"></i>PROTECTED</span></div>';
                }

                if (auth()->id() === $row->id) {
                    return '<div class="text-center"><span class="badge px-2 py-1 font-weight-bold" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; border-radius: 6px;"><i class="fas fa-circle mr-1" style="font-size: 7px; vertical-align: middle;"></i>Sedang Online</span></div>';
                }

                $editUrl = route('users.user.edit', $row->id);
                $deleteUrl = route('users.user.destroy', $row->id);
                $token = csrf_token();

                $btnEdit = '<button type="button" class="btn btn-sm btn-outline-primary btn-edit px-2 py-1 mr-1"
                                data-url="'.$editUrl.'"
                                data-toggle="tooltip"
                                title="Atur Role Aplikasi" style="border-radius: 6px; font-weight: 600;">
                                <i class="fas fa-user-tag mr-1"></i> Role
                            </button>';

                $btnDelete = '
                                <form action="'.$deleteUrl.'" method="POST" style="display:inline-block; margin: 0;">
                                    <input type="hidden" name="_token" value="'.$token.'">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete px-2 py-1" data-toggle="tooltip" data-name="'. htmlspecialchars($row->name) .'" title="Keluarkan User dari Modul" style="border-radius: 6px;">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            ';

                return '<div class="text-center" style="white-space: nowrap;">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['avatar', 'name', 'email', 'roles', 'action'])
            ->make(true);
    }

    public function sync(UserSyncService $syncer)
    {
        $this->guard('create', 'users:user');

        try {
            $homebaseUrl  = config('app.tsu_homebase.url');
            $clientId     = config('app.oauth.client.id');
            $clientSecret = config('app.oauth.client.secret');

            // Access Token Client Credential
            $responseToken = Http::withoutVerifying()
                ->withHeaders(['X-Sync-Secret' => config('app.pikdi.key.sync')])
                ->post($homebaseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => '', // Sesuaikan jika ada scope khusus
            ]);

            if ($responseToken->failed()) {
                throw new \Exception("[TSU_AUTH_FAIL] Gagal Otorisasi Client (Status: {$responseToken->status()}). Cek Client ID/Secret.");
            }

            $accessToken = $responseToken->json()['access_token'];
            if (!$accessToken) {
                throw new \Exception("[TSU_TOKEN_EMPTY] Respon token dari Homebase kosong.");
            }

            // TARIK DATA USER (Pakai Bearer Token)
            $apiUrl = $homebaseUrl . '/api/v1/users/sync';
            $stats = ['processed' => 0, 'updated' => 0, 'uptodate' => 0, 'failed' => 0, 'skipped' => 0];
            $skippedEmails = [];
            $failedEmails = [];

            User::query()->whereNotNull('email')->chunk(50, function ($users) use ($apiUrl, $accessToken, $syncer, &$stats, &$skippedEmails, &$failedEmails) {
                $emailList = $users->pluck('email')->toArray();
                try {
                    $response = Http::withoutVerifying()->withToken($accessToken)->withHeaders(['Accept' => 'application/json', 'X-Sync-Secret' => config('app.pikdi.key.sync')])->timeout(30)->post($apiUrl, ['emails' => $emailList]);

                    if ($response->successful()) {
                        $usersData = $response->json()['data'] ?? [];
                        foreach ($usersData as $userData) {
                            $email = $userData['email'] ?? 'Unknown';
                            try {
                                $result = $syncer->handle($userData, null, true);
                                $stats['processed']++;
                                if ($result['affected'] === true) {
                                    $stats['updated']++;
                                } else {
                                    $stats['uptodate']++;
                                }
                            } catch (\Exception $e) {
                                if (str_contains($e->getMessage(), '[TSU_DENIED_ACCESS]')) {
                                    $stats['skipped']++;
                                    $skippedEmails[] = $email;
                                } else {
                                    $stats['failed']++;
                                    $failedEmails[] = $email;
                                    Log::error("[TSU_USER_FAIL] Gagal proses user: " . $email, ['error_msg' => $e->getMessage()]);
                                }
                            }
                        }
                    } else {
                        $stats['failed'] += count($emailList);
                        Log::error("[TSU_BATCH_API_ERR] Gagal Sync Batch: ", ['status_code' => $response->status(), 'response_body' => $response->body(), 'target_emails' => $emailList]);
                    }
                } catch (\Exception $e) {
                    $stats['failed'] += count($emailList);
                    Log::error("[TSU_BATCH_CONN_ERR] Koneksi Error Saat Sync Batch: ", ['error' => $e->getMessage(), 'emails' => $emailList]);
                }
                return true;
            });

            if ($stats['processed'] === 0 && $stats['failed'] > 0) {
                throw new \Exception("[TSU_SYNC_ZERO] Sinkronisasi gagal total. Tidak ada data yang berhasil diambil.");
            }

            // Generate File Report if there are skips or fails
            $errorDetail = null;
            if ($stats['skipped'] > 0 || $stats['failed'] > 0) {
                $errorDetail = "";
                if ($stats['skipped'] > 0) {
                    $errorDetail .= "Dilewati (Bukan Dosen/Tendik): " . implode(', ', $skippedEmails) . ". ";
                }
                if ($stats['failed'] > 0) {
                    $errorDetail .= "Gagal Diproses: " . implode(', ', $failedEmails) . ".";
                }
            }

            // Kirim Notifikasi ke User yang menekan tombol
            $pesanNotif = "Sync Selesai. Total: {$stats['processed']}, Diperbarui: {$stats['updated']}.";
            if ($stats['skipped'] > 0 || $stats['failed'] > 0) {
                $pesanNotif .= " Terdapat " . ($stats['skipped'] + $stats['failed']) . " peringatan/kegagalan.";
            }
            try {
                auth()->user()->notify(new \App\Notifications\LaporanSyncUserNotification($pesanNotif, $errorDetail));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal mengirim notifikasi real-time: ' . $e->getMessage());
            }

            // LAPORAN TOAST
            $msg = "<h6 class='font-weight-bold mb-2'>Laporan Sinkronisasi User</h6>";
            $msg .= "<ul class='mb-0 pl-3' style='list-style-type: disc;'>";
            $msg .= "<li>Total user diperiksa: <b>{$stats['processed']}</b></li>";
            if ($stats['updated'] > 0) {
                $msg .= "<li>Data diperbarui: <b>{$stats['updated']}</b> user</li>";
            }
            if ($stats['uptodate'] > 0) {
                $msg .= "<li>Data up to date: {$stats['uptodate']} user</li>";
            }
            if ($stats['skipped'] > 0) {
                $msg .= "<li class='text-warning font-weight-bold'>Dilewati (Bukan Dosen/Tendik): {$stats['skipped']} user</li>";
            }
            if ($stats['failed'] > 0) {
                $msg .= "<li class='text-danger font-weight-bold'>Gagal diproses: {$stats['failed']} user (Cek Log)</li>";
            }
            $msg .= "</ul>";

            if ($stats['failed'] > 0 && $stats['processed'] === 0) {
                return back()->with('error', 'Gagal melakukan sinkronisasi. Hubungi PIKDI untuk tindak lanjut!');
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            $defaultError = 'Terjadi kesalahan sistem yang tidak terduga.';
            if ($e instanceof ConnectionException) {
                $defaultError = 'Tidak dapat menghubungi Server Homebase. Cek koneksi internet.';
            }
            return TsuErrorHandlerService::handleHtml($e, '[TSU_SYS_CRITICAL]', $defaultError, 'Gagal Sync User.');
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'users:user');

        $user = User::with('roles')->findOrFail($id);

        // Ambil Role Lokal
        $allRoles = Role::where('is_identity', 0)->pluck('name', 'name')->all();

        // Default selected ambil Role Lokal user
        $userRoles = $user->roles()->where('is_identity', 0)->pluck('name')->toArray();

        return view('users::user._modal_pasang_role', compact('user', 'allRoles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'roles' => 'nullable|array',
        ]);

        try {
            $user = User::findOrFail($id);

            // Protect Role Global user
            $globalRoles = $user->roles()->where('is_identity', 1)->pluck('name')->toArray();

            // Ambil Role Lokal submit form modal
            $submittedLocalRoles = $request->roles ?? [];

            // Merge Role global dan lokal
            $finalRolesToSync = array_merge($globalRoles, $submittedLocalRoles);

            $user->syncRoles($finalRolesToSync);

            return redirect()->back()->with('success', 'Role lokal untuk user ' . $user->name . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_UPD_FAIL]', 'Gagal menyimpan perubahan data.', "Gagal Update User ID: $id", $request);
        }
    }

    // Hapus User
    public function destroy($id)
    {
        $this->guard('delete', 'users:user');

        try {
            $user = User::query()->findOrFail($id);

            // Proteksi Tambahan: Jangan hapus diri sendiri
            if(auth()->id() == $id){
                return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
            }

            $user->delete();
            return back()->with('success', 'User berhasil dikeluarkan dari modul ini!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_USER_DELETE_FAIL]', 'Gagal menghapus user.', "Gagal Hapus User ID: $id");
        }
    }
}
