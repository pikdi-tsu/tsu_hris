<?php

namespace Modules\Users\Http\Controllers\SelfService;

use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\RequestEmail;
use App\Models\User;
use App\Notifications\RequestEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RequestEmailUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dapatkan profil DataDosenTendik user yang sedang login
     */
    private function getCurrentProfile()
    {
        $user = Auth::user();
        if (!$user) return null;

        $profile = DataDosenTendik::with('unit')->where('user_id', $user->id)->first();
        if (!$profile) {
            $profile = DataDosenTendik::with('unit')->where('nama', $user->name)
                ->orWhere('nik', $user->username ?? '')
                ->orWhere('nip', $user->username ?? '')
                ->first();
        }
        return $profile;
    }

    /**
     * Halaman Layanan Pengajuan Akun Email Saya
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = $this->getCurrentProfile();

        $baseQuery = RequestEmail::with(['validator', 'processor'])
            ->where(function ($q) use ($profile, $user) {
                if ($profile) {
                    $q->where('pegawai_id', $profile->id);
                }
                if ($user) {
                    $q->orWhere('email_alternatif', $user->email)
                      ->orWhere('nama_pemohon', $user->name);
                }
            });

        // Hitung statistik menyeluruh untuk kartu metrik
        $allMyRequests = (clone $baseQuery)->get();
        $totalPengajuan = $allMyRequests->count();
        $menungguSdm = $allMyRequests->where('status', 'Diajukan')->count();
        $prosesPikdi = $allMyRequests->whereIn('status', ['Divalidasi SDM', 'Diproses PIKDI'])->count();
        $akunSelesai = $allMyRequests->where('status', 'Selesai')->count();
        $ditolak = $allMyRequests->whereIn('status', ['Ditolak SDM', 'Ditolak PIKDI'])->count();

        $query = clone $baseQuery;

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_tiket', 'like', "%{$s}%")
                  ->orWhere('email_usulan', 'like', "%{$s}%")
                  ->orWhere('email_rekomendasi_sdm', 'like', "%{$s}%")
                  ->orWhere('email_final_dibuat', 'like', "%{$s}%")
                  ->orWhere('keperluan', 'like', "%{$s}%");
            });
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());
        $units = MasterUnit::orderBy('nama_unit')->get(['id', 'nama_unit']);

        return view('users::selfservice.request-email.index', [
            'title'          => 'Layanan Pengajuan Akun Email Resmi (@tsu.ac.id)',
            'user'           => $user,
            'profile'        => $profile,
            'requests'       => $requests,
            'totalPengajuan' => $totalPengajuan,
            'menungguSdm'    => $menungguSdm,
            'prosesPikdi'    => $prosesPikdi,
            'akunSelesai'    => $akunSelesai,
            'ditolak'        => $ditolak,
            'units'          => $units,
            'filters'        => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Kirim Pengajuan Akun Email Baru dari Sisi Pegawai
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $profile = $this->getCurrentProfile();

        $request->validate([
            'tipe_pemohon'  => 'required|in:Dosen,Tendik,Unit Kerja,Organisasi / Kepanitiaan',
            'email_usulan'  => 'required|string|max:150',
            'keperluan'     => 'required|string|max:255',
            'kontak_hp'     => 'required|string|max:30',
        ]);

        $emailUsulan = trim(strtolower($request->email_usulan));
        if (!str_contains($emailUsulan, '@tsu.ac.id')) {
            $emailUsulan = rtrim($emailUsulan, '@') . '@tsu.ac.id';
        }

        $namaPemohon = $request->nama_pemohon ?: ($profile->nama ?? $user->name);
        $nikNip = $request->nik_nip ?: ($profile->nomor_induk ?? ($user->username ?? '-'));
        $unitId = $request->unit_id ?: ($profile->unit_id ?? null);
        $unitNama = $request->unit_nama ?: ($profile->unit->nama_unit ?? ($user->unit ?? 'Universitas Tiga Serangkai'));
        $jabatan = $request->jabatan ?: ($profile->posisi ?? ($profile->tipe_karyawan ?? 'Pegawai'));

        $item = RequestEmail::create([
            'pegawai_id'            => $profile ? $profile->id : null,
            'nama_pemohon'          => $namaPemohon,
            'nik_nip'               => $nikNip,
            'unit_id'               => $unitId,
            'unit_nama'             => $unitNama,
            'jabatan'               => $jabatan,
            'tipe_pemohon'          => $request->tipe_pemohon,
            'keperluan'             => $request->keperluan,
            'email_usulan'          => $emailUsulan,
            'email_rekomendasi_sdm' => null,
            'kontak_hp'             => $request->kontak_hp,
            'email_alternatif'      => $request->email_alternatif ?: ($user->email ?? null),
            'status'                => 'Diajukan',
            'catatan_pemohon'       => $request->catatan_pemohon,
            'riwayat_status'        => [
                [
                    'tanggal' => Carbon::now()->format('d M Y H:i'),
                    'status'  => 'Permintaan Email Masuk (Diajukan mandiri oleh pemohon ' . $namaPemohon . ')'
                ]
            ],
        ]);

        // Dispatch notifikasi ke Tim SDM & Pemohon
        $this->dispatchNotifications($item, 'diajukan');

        return back()->with('success', "Permohonan akun email Anda [{$item->nomor_tiket}] berhasil diajukan! Berkas saat ini dalam antrean verifikasi Tim SDM.");
    }

    /**
     * Ambil Detail Permohonan (JSON untuk Modal)
     */
    public function detail($id)
    {
        $user = Auth::user();
        $profile = $this->getCurrentProfile();

        $item = RequestEmail::with(['validator', 'processor'])->findOrFail($id);

        // Security check: pastikan hanya pemilik atau admin yang bisa mengakses
        $isOwner = ($profile && $item->pegawai_id === $profile->id) 
            || ($user && $item->email_alternatif === $user->email)
            || ($user && $item->nama_pemohon === $user->name)
            || ($user && ($user->hasRole(['super admin hris', 'admin hris testing', 'pikdi'])));

        if (!$isOwner) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $item
        ]);
    }

    /**
     * Dispatch notifikasi sistem & real-time WebSocket
     */
    protected function dispatchNotifications(RequestEmail $item, string $step)
    {
        try {
            $pemohonUser = Auth::user();

            // Ambil Tim SDM
            $sdmUsers = User::role(['super admin hris', 'admin hris testing'])->get();
            if ($sdmUsers->isEmpty()) {
                $sdmUsers = User::where('email', 'like', '%sdm%')->orWhere('name', 'like', '%sdm%')->get();
            }

            if ($step === 'diajukan') {
                $msgSdm = "Permohonan akun email baru [{$item->nomor_tiket}] dari {$item->nama_pemohon} ({$item->unit_nama}) usulan [{$item->email_usulan}] memerlukan validasi SDM.";
                foreach ($sdmUsers as $sdm) {
                    $sdm->notify(new RequestEmailNotification($item, 'diajukan', 'sdm', $msgSdm));
                }

                if ($pemohonUser) {
                    $msgPemohon = "Permohonan akun email Anda [{$item->nomor_tiket}] ({$item->email_usulan}) telah berhasil diajukan dan sedang dalam antrean verifikasi Tim SDM.";
                    $pemohonUser->notify(new RequestEmailNotification($item, 'diajukan', 'pemohon', $msgPemohon));
                }
            }
        } catch (\Throwable $th) {
            Log::warning("Gagal mengirim notifikasi mandiri request email: " . $th->getMessage());
        }
    }
}
