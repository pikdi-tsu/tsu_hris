<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DataDosenTendik;
use App\Models\RiwayatPerubahanData;
use App\Notifications\PerubahanDataDiajukanNotification;
use Illuminate\Http\Request;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $title = 'Profil Saya';
        $hasPhoto = !empty($user->avatar_url);
        $formattedRoles = $user->getRoleNames()->map(function($role) {
            $badgeClass = match($role) {
                'super admin' => 'badge-danger',    // Merah
                'dosen'       => 'badge-success',   // Hijau
                'mahasiswa'   => 'badge-primary',   // Biru
                'tendik'      => 'badge-warning',   // Kuning
                default       => 'badge-secondary'  // Abu-abu
            };

            return [
                'label' => ucwords($role), // Ubah 'super admin' -> 'Super Admin'
                'class' => $badgeClass
            ];
        });

        $isMahasiswa = $user->hasRole('mahasiswa');
        $identityLabel = $isMahasiswa ? 'NIM' : 'NIK / NIDN';
        $identityValue = $user->username ?? $user->nim ?? '-';

        $unitKerja = $user->unit ?? 'Menunggu Sinkronisasi';

        $accountStatus = [
            'isActive' => $user->isactive ?? true, // Default true kalau kolom belum ada
            'text'     => ($user->isactive ?? true) ? 'Akun Aktif' : 'Dibekukan',
            'class'    => ($user->isactive ?? true) ? 'btn-primary' : 'btn-danger',
            'icon'     => ($user->isactive ?? true) ? 'fa-check-circle' : 'fa-ban',
        ];

        // Cari record kepegawaian (Dosen/Tendik)
        $karyawan = DataDosenTendik::where('user_id', $user->id)
            ->orWhere(function($q) use ($user) {
                if (!empty($user->username)) {
                    $q->where('nik', $user->username)->orWhere('nidn', $user->username);
                }
            })->first();

        $riwayatPerubahan = $karyawan
            ? RiwayatPerubahanData::with('reviewer')->where('data_dosen_tendik_id', $karyawan->id)->latest()->get()
            : collect();

        return view('users::profile.index', compact(
            'user',
            'title',
            'hasPhoto',
            'formattedRoles',
            'identityLabel',
            'identityValue',
            'unitKerja',
            'accountStatus',
            'karyawan',
            'riwayatPerubahan'
        ));
    }

    /**
     * Handle Update Ganti Password (Kirim Data ke API Homebase)
     */
    public function updatePassword(Request $request)
    {
        // Validasi Input di Sisi Client
        $request->validate([
            'current_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ]);


        try {
            $token = session('homebase_access_token');
            if (!$token) {
                throw new \Exception('[TSU_SESSION_EXPIRED] Sesi kadaluarsa. Silakan login ulang.');
            }

            $response = Http::acceptJson()->withoutVerifying()
                ->withHeaders(['X-Sync-Secret' => config('app.pikdi.key.sync')])
                ->withToken($token)
                ->post(config('app.tsu_homebase.url') . '/api/v1/profile/change-password', [
                    'current_password'          => $request->current_password,
                    'password'              => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Password berhasil diperbarui di Pusat Data!');
            }

            if ($response->status() === 422 && isset($response->json()['errors'])) {
                throw ValidationException::withMessages($response->json()['errors']);
            }

            $errorMessage = $response->json()['message'] ?? 'Gagal update password.';
            throw ValidationException::withMessages(['current_password' => [$errorMessage]]);

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PWD_UPD_FAIL]', 'Gagal menghubungi server pusat.', 'Gagal Update Password.', $request);
        }
    }

    /**
     * Handle Update Foto Profil (Kirim File ke API Homebase)
     */
    public function updatePhoto(Request $request)
    {
        // Validasi
        $request->validate([
            'photoprofile' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user  = auth()->user();
            $file  = $request->file('photoprofile');
            $token = session('homebase_access_token');

            $response = Http::withToken($token)->acceptJson()->withoutVerifying()
                ->withHeaders(['X-Sync-Secret' => config('app.pikdi.key.sync')])
                ->attach('photoprofile', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post(config('app.tsu_homebase.url') . '/api/v1/profile/change-photo');

            if ($response->successful()) {
                $homebaseUrl = $response->json()['data']['photo_url'];

                $oldPhoto = $user->avatar_url;
                if ($oldPhoto && !str_starts_with($oldPhoto, 'http') && Storage::disk('public')->exists($oldPhoto)) {
                    Storage::disk('public')->delete($oldPhoto);
                }

                User::query()->where('id', $user->id)->update(['avatar_url' => $homebaseUrl]);

                $user->avatar_url = $homebaseUrl;
                $user->save();

                return back()->with('success', 'Foto profil berhasil disinkronkan ke Pusat & Lokal!');
            }

            $errorMessage = $response->json()['message'] ?? 'Unknown Error';
            throw new \Exception("[TSU_PHOTO_UPD_FAIL] Gagal update ke Homebase: $errorMessage");

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PHOTO_UPD_FAIL]', 'Terjadi kesalahan sistem.', 'Gagal Update Foto Profil.', $request);
        }
    }

    /**
     * Handle Permohonan Pengajuan Perubahan Data Mandiri Pegawai (ESS)
     */
    public function ajukanPerubahanData(Request $request)
    {
        $user = auth()->user();
        $karyawan = DataDosenTendik::where('user_id', $user->id)
            ->orWhere(function($q) use ($user) {
                if (!empty($user->username)) {
                    $q->where('nik', $user->username)->orWhere('nidn', $user->username);
                }
            })->first();

        if (!$karyawan) {
            return back()->with('error', 'Profil data kepegawaian Anda tidak ditemukan.');
        }

        // Cek apakah ada pengajuan yang masih berstatus 'menunggu'
        $adaPending = RiwayatPerubahanData::where('data_dosen_tendik_id', $karyawan->id)
            ->where('status', 'menunggu')
            ->exists();

        if ($adaPending) {
            return back()->with('error', 'Anda masih memiliki permohonan perubahan data yang sedang menunggu persetujuan SDM. Mohon tunggu proses verifikasi selesai sebelum mengajukan perubahan baru.');
        }

        $fields = [
            'no_hp' => 'No. WhatsApp / HP',
            'nik_ktp' => 'NIK KTP',
            'no_npwp' => 'No. NPWP',
            'tempat_lahir' => 'Tempat Lahir',
            'tgl_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'status_perkawinan' => 'Status Perkawinan',
            'alamat_lengkap' => 'Alamat Lengkap (KTP)',
            'alamat_domisili' => 'Alamat Domisili',
            'nama_bank' => 'Nama Bank',
            'no_rekening' => 'No. Rekening',
            'atas_nama_rekening' => 'Atas Nama Rekening',
            'kontak_darurat_nama' => 'Nama Kontak Darurat',
            'kontak_darurat_hubungan' => 'Hubungan Kontak Darurat',
            'kontak_darurat_no_hp' => 'No. HP Kontak Darurat',
            'kontak_darurat_alamat' => 'Alamat Kontak Darurat',
        ];

        $diffs = [];
        foreach ($fields as $field => $label) {
            $oldVal = trim((string)($karyawan->{$field} ?? ''));
            $newVal = trim((string)$request->input($field, ''));

            if ($field === 'tgl_lahir') {
                if (!empty($newVal)) {
                    try {
                        $newVal = \Carbon\Carbon::parse($newVal)->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
                if (!empty($oldVal)) {
                    try {
                        $oldVal = \Carbon\Carbon::parse($oldVal)->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
            }

            if ($oldVal !== $newVal) {
                $diffs[$field] = [
                    'label' => $label,
                    'old'   => $oldVal,
                    'new'   => $newVal,
                ];
            }
        }

        if (empty($diffs)) {
            return back()->with('error', 'Tidak ada perbedaan data yang Anda masukkan.');
        }

        $perubahan = RiwayatPerubahanData::create([
            'data_dosen_tendik_id' => $karyawan->id,
            'user_id'              => $user->id,
            'kategori_perubahan'   => 'Data Pribadi, Kontak & Rekening',
            'perubahan_data'       => $diffs,
            'status'               => 'menunggu',
            'catatan_pemohon'      => $request->catatan_pemohon,
        ]);

        // Notifikasi ke Tim SDM / HRD
        try {
            $hrds = User::permission('admin:data-karyawan:view')->get();
            if ($hrds->isEmpty()) {
                $hrds = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super admin', 'super admin hris', 'admin', 'admin hris', 'admin sdm']);
                })->get();
            }

            foreach ($hrds as $hrd) {
                $hrd->notify(new PerubahanDataDiajukanNotification(
                    $perubahan,
                    $karyawan,
                    'Pengajuan perubahan data mandiri dari ' . $karyawan->nama . '.'
                ));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim notif perubahan data: ' . $e->getMessage());
        }

        return back()->with('success', 'Permohonan perubahan data berhasil dikirim! Menunggu verifikasi dan persetujuan (ACC) dari Bagian SDM.');
    }
}
