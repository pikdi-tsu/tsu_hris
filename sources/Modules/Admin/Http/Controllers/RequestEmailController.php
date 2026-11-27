<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\RequestEmail;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\User;
use App\Notifications\RequestEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\System\Helpers\ToastHelper;

class RequestEmailController extends MiddlewareController
{
    /**
     * Dashboard & Pengelolaan Request Email (Role-Based 2 Tab: SDM & PIKDI)
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:persuratan');

        $user = Auth::user();
        $isPikdiUser = $user && ($user->hasRole('pikdi') || str_contains(strtolower($user->email ?? ''), 'pikdi') || str_contains(strtolower($user->username ?? ''), 'pikdi'));
        $defaultTab = $isPikdiUser ? 'pikdi' : 'sdm';
        $activeTab = $request->input('tab', $defaultTab);
        if (!in_array($activeTab, ['sdm', 'pikdi', 'semua'])) {
            $activeTab = $defaultTab;
        }

        $query = RequestEmail::with(['pegawai', 'unit', 'validator', 'processor']);

        // Search Keyword
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_tiket', 'like', "%{$s}%")
                  ->orWhere('nama_pemohon', 'like', "%{$s}%")
                  ->orWhere('email_usulan', 'like', "%{$s}%")
                  ->orWhere('email_rekomendasi_sdm', 'like', "%{$s}%")
                  ->orWhere('email_final_dibuat', 'like', "%{$s}%")
                  ->orWhere('unit_nama', 'like', "%{$s}%")
                  ->orWhere('keperluan', 'like', "%{$s}%");
            });
        }

        // Filter Tipe Pemohon
        if ($request->filled('tipe') && $request->tipe !== 'all') {
            $query->where('tipe_pemohon', $request->tipe);
        }

        // Filter Sesuai Tab Aktif
        $sdmFilter = $request->input('sdm_filter', 'pending');
        $pikdiFilter = $request->input('pikdi_filter', 'pending');

        if ($activeTab === 'sdm') {
            if ($sdmFilter === 'pending') {
                $query->where('status', 'Diajukan');
            } elseif ($sdmFilter === 'ditolak') {
                $query->where('status', 'Ditolak SDM');
            } else { // 'all' di tab SDM
                $query->whereIn('status', ['Diajukan', 'Ditolak SDM', 'Divalidasi SDM']);
            }
        } elseif ($activeTab === 'pikdi') {
            if ($pikdiFilter === 'pending') {
                $query->whereIn('status', ['Divalidasi SDM', 'Diproses PIKDI']);
            } elseif ($pikdiFilter === 'selesai') {
                $query->where('status', 'Selesai');
            } elseif ($pikdiFilter === 'kendala') {
                $query->where('status', 'Ditolak PIKDI');
            } else { // 'all' di tab PIKDI
                $query->whereIn('status', ['Divalidasi SDM', 'Diproses PIKDI', 'Selesai', 'Ditolak PIKDI']);
            }
        } else {
            // Tab 'semua'
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }

        // Metrics Stat Cards
        $all = RequestEmail::all();
        $totalRequest = $all->count();
        $menungguSdm = $all->where('status', 'Diajukan')->count();
        $ditolakSdm = $all->where('status', 'Ditolak SDM')->count();
        $menungguPikdi = $all->whereIn('status', ['Divalidasi SDM', 'Diproses PIKDI'])->count();
        $selesai = $all->where('status', 'Selesai')->count();
        $ditolakPikdi = $all->where('status', 'Ditolak PIKDI')->count();
        $ditolak = $ditolakSdm + $ditolakPikdi;

        $requests = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        $pegawais = DataDosenTendik::where('is_active', 1)->orderBy('nama')->get(['id', 'nama', 'nip', 'nik', 'unit_id', 'tipe_karyawan', 'no_hp']);
        $units = MasterUnit::orderBy('nama_unit')->get(['id', 'nama_unit']);

        return view('admin::request-email.index', [
            'title'          => 'Pengelolaan Request Email (@tsu.ac.id)',
            'requests'       => $requests,
            'totalRequest'   => $totalRequest,
            'menungguSdm'    => $menungguSdm,
            'ditolakSdm'     => $ditolakSdm,
            'menungguPikdi'  => $menungguPikdi,
            'selesai'        => $selesai,
            'ditolakPikdi'   => $ditolakPikdi,
            'ditolak'        => $ditolak,
            'pegawais'       => $pegawais,
            'units'          => $units,
            'activeTab'      => $activeTab,
            'sdmFilter'      => $sdmFilter,
            'pikdiFilter'    => $pikdiFilter,
            'filters'        => $request->only(['search', 'status', 'tipe', 'tab', 'sdm_filter', 'pikdi_filter']),
        ]);
    }

    /**
     * Input Permohonan Baru (dari sisi Admin/Unit)
     */
    public function store(Request $request)
    {
        $this->guard('create', 'admin:persuratan');

        $request->validate([
            'tipe_pemohon'  => 'required|in:Dosen,Tendik,Unit Kerja,Organisasi / Kepanitiaan',
            'nama_pemohon'  => 'required|string|max:150',
            'email_usulan'  => 'required|string|max:150',
            'keperluan'     => 'required|string|max:255',
            'kontak_hp'     => 'required|string|max:30',
        ]);

        $pegawaiId = $request->input('pegawai_id');
        $unitId = $request->input('unit_id');
        $unitNama = $request->input('unit_nama');

        if ($pegawaiId) {
            $peg = DataDosenTendik::find($pegawaiId);
            if ($peg) {
                $unitId = $unitId ?: $peg->unit_id;
                $unitNama = $unitNama ?: ($peg->unit->nama_unit ?? '-');
            }
        }

        $emailUsulan = trim(strtolower($request->email_usulan));
        if (!str_contains($emailUsulan, '@tsu.ac.id')) {
            $emailUsulan = rtrim($emailUsulan, '@') . '@tsu.ac.id';
        }

        $item = RequestEmail::create([
            'pegawai_id'            => $pegawaiId,
            'nama_pemohon'          => $request->nama_pemohon,
            'nik_nip'               => $request->nik_nip,
            'unit_id'               => $unitId,
            'unit_nama'             => $unitNama,
            'jabatan'               => $request->jabatan,
            'tipe_pemohon'          => $request->tipe_pemohon,
            'keperluan'             => $request->keperluan,
            'email_usulan'          => $emailUsulan,
            'email_rekomendasi_sdm' => null,
            'kontak_hp'             => $request->kontak_hp,
            'email_alternatif'      => $request->email_alternatif,
            'status'                => 'Diajukan',
            'catatan_pemohon'       => $request->catatan_pemohon,
            'riwayat_status'        => [
                [
                    'tanggal' => Carbon::now()->format('d M Y H:i'),
                    'status'  => 'Permintaan Email Masuk (Diajukan oleh ' . (Auth::user()->name ?? 'Admin') . ')'
                ]
            ],
        ]);

        // Dispatch Notifikasi ke SDM dan Pemohon
        $this->dispatchNotifications($item, 'diajukan');

        return back()->with('success', "Permohonan akun email baru [{$item->nomor_tiket}] berhasil diajukan dan masuk ke antrean validasi SDM.");
    }

    /**
     * Step BPO 2: Validasi oleh SDM (SDM bisa mengoreksi/edit rekomendasi alamat email)
     */
    public function validateSdm(Request $request, $id)
    {
        $this->guard('edit', 'admin:persuratan');

        $item = RequestEmail::findOrFail($id);

        $request->validate([
            'action_type'           => 'required|in:approve,reject',
            'email_rekomendasi_sdm' => 'nullable|string|max:150',
            'catatan_sdm'           => 'nullable|string',
        ]);

        $currentUser = Auth::user();
        $riwayat = is_array($item->riwayat_status) ? $item->riwayat_status : [];

        if ($request->action_type === 'approve') {
            $emailRekomendasi = trim(strtolower($request->email_rekomendasi_sdm ?: $item->email_usulan));
            if (!str_contains($emailRekomendasi, '@tsu.ac.id')) {
                $emailRekomendasi = rtrim($emailRekomendasi, '@') . '@tsu.ac.id';
            }

            $item->status = 'Divalidasi SDM';
            $item->email_rekomendasi_sdm = $emailRekomendasi;
            $item->catatan_sdm = $request->catatan_sdm;
            $item->validated_by = $currentUser ? $currentUser->id : null;
            $item->validated_at = Carbon::now();

            $riwayat[] = [
                'tanggal' => Carbon::now()->format('d M Y H:i'),
                'status'  => "Divalidasi oleh Tim SDM ({$currentUser->name}). Alamat resmi disetujui: {$emailRekomendasi}. Diteruskan ke PIKDI untuk penerbitan akun."
            ];
            $item->riwayat_status = $riwayat;
            $item->save();

            // Dispatch Notifikasi ke PIKDI dan Pemohon
            $this->dispatchNotifications($item, 'divalidasi_sdm');

            return back()->with('success', "Permohonan [{$item->nomor_tiket}] berhasil divalidasi oleh SDM dengan rekomendasi alamat [{$emailRekomendasi}]. Data telah diteruskan ke PIKDI.");
        } else {
            // Tolak oleh SDM
            $item->status = 'Ditolak SDM';
            $item->catatan_sdm = $request->catatan_sdm ?: 'Permohonan ditolak oleh tim SDM.';
            $item->validated_by = $currentUser ? $currentUser->id : null;
            $item->validated_at = Carbon::now();

            $riwayat[] = [
                'tanggal' => Carbon::now()->format('d M Y H:i'),
                'status'  => "Permohonan ditolak oleh Tim SDM ({$currentUser->name}). Alasan: " . ($request->catatan_sdm ?: '-')
            ];
            $item->riwayat_status = $riwayat;
            $item->save();

            // Dispatch Notifikasi Penolakan ke Pemohon
            $this->dispatchNotifications($item, 'ditolak_sdm');

            return back()->with('warning', "Permohonan [{$item->nomor_tiket}] telah ditolak oleh SDM.");
        }
    }

    /**
     * Step BPO 3: Alamat Email Dibuat oleh PIKDI -> Permintaan Selesai
     */
    public function processPikdi(Request $request, $id)
    {
        $this->guard('edit', 'admin:persuratan');

        $item = RequestEmail::findOrFail($id);

        $request->validate([
            'action_type'        => 'required|in:complete,reject',
            'email_final_dibuat' => 'nullable|string|max:150',
            'password_sementara' => 'nullable|string|max:100',
            'catatan_pikdi'      => 'nullable|string',
        ]);

        $currentUser = Auth::user();
        $riwayat = is_array($item->riwayat_status) ? $item->riwayat_status : [];

        if ($request->action_type === 'complete') {
            $emailFinal = trim(strtolower($request->email_final_dibuat ?: ($item->email_rekomendasi_sdm ?: $item->email_usulan)));
            if (!str_contains($emailFinal, '@tsu.ac.id')) {
                $emailFinal = rtrim($emailFinal, '@') . '@tsu.ac.id';
            }

            $item->status = 'Selesai';
            $item->email_final_dibuat = $emailFinal;
            $item->password_sementara = $request->password_sementara ?: 'TigaSerangkai#' . date('Y');
            $item->catatan_pikdi = $request->catatan_pikdi ?: 'Akun Google Workspace universitas telah dibuat dan siap digunakan.';
            $item->processed_by = $currentUser ? $currentUser->id : null;
            $item->processed_at = Carbon::now();

            $riwayat[] = [
                'tanggal' => Carbon::now()->format('d M Y H:i'),
                'status'  => "Akun resmi [{$emailFinal}] berhasil diterbitkan & diaktifkan oleh PIKDI ({$currentUser->name}). Permintaan Selesai."
            ];
            $item->riwayat_status = $riwayat;
            $item->save();

            // Dispatch Notifikasi ke Pemohon dan SDM
            $this->dispatchNotifications($item, 'selesai');

            return back()->with('success', "Akun email [{$emailFinal}] berhasil dibuat oleh PIKDI! Permohonan [{$item->nomor_tiket}] dinyatakan SELESAI.");
        } else {
            // Kendala / Tolak oleh PIKDI
            $item->status = 'Ditolak PIKDI';
            $item->catatan_pikdi = $request->catatan_pikdi ?: 'Gagal membuat akun karena kendala teknis.';
            $item->processed_by = $currentUser ? $currentUser->id : null;
            $item->processed_at = Carbon::now();

            $riwayat[] = [
                'tanggal' => Carbon::now()->format('d M Y H:i'),
                'status'  => "Pembuatan akun ditolak/gagal oleh PIKDI ({$currentUser->name}). Alasan: " . ($request->catatan_pikdi ?: '-')
            ];
            $item->riwayat_status = $riwayat;
            $item->save();

            // Dispatch Notifikasi Kendala ke Pemohon dan SDM
            $this->dispatchNotifications($item, 'ditolak_pikdi');

            return back()->with('warning', "Permohonan [{$item->nomor_tiket}] ditandai mengalami kendala oleh PIKDI.");
        }
    }

    /**
     * Kirim Ulang Notifikasi Sistem ke Stakeholder Terkait
     */
    public function resendNotification($id)
    {
        $this->guard('edit', 'admin:persuratan');

        $item = RequestEmail::findOrFail($id);

        $step = match ($item->status) {
            'Diajukan'        => 'diajukan',
            'Divalidasi SDM',
            'Diproses PIKDI'  => 'divalidasi_sdm',
            'Selesai'         => 'selesai',
            'Ditolak SDM'     => 'ditolak_sdm',
            'Ditolak PIKDI'   => 'ditolak_pikdi',
            default           => 'diajukan',
        };

        $this->dispatchNotifications($item, $step);

        return back()->with('success', "Notifikasi sistem untuk tiket [{$item->nomor_tiket}] berhasil dikirim ulang ke seluruh stakeholder terkait.");
    }

    /**
     * Dispatch notifikasi sistem & real-time WebSocket ke stakeholder terkait
     * (SDM, PIKDI, dan Pemohon)
     */
    protected function dispatchNotifications(RequestEmail $item, string $step)
    {
        try {
            // 1. Dapatkan akun User pemohon jika terhubung
            $pemohonUser = null;
            if ($item->pegawai_id) {
                $pegawai = DataDosenTendik::find($item->pegawai_id);
                if ($pegawai && $pegawai->user_id) {
                    $pemohonUser = User::find($pegawai->user_id);
                }
            }
            if (!$pemohonUser && $item->email_alternatif) {
                $pemohonUser = User::where('email', $item->email_alternatif)->first();
            }
            if (!$pemohonUser && Auth::check() && $step === 'diajukan') {
                $pemohonUser = Auth::user();
            }

            // 2. Dapatkan Tim SDM / Admin
            $sdmUsers = User::role(['super admin hris', 'admin hris testing'])->get();
            if ($sdmUsers->isEmpty()) {
                $sdmUsers = User::where('email', 'like', '%sdm%')->orWhere('name', 'like', '%sdm%')->get();
            }

            // 3. Dapatkan Tim PIKDI
            $pikdiUsers = User::role('pikdi')->get();
            if ($pikdiUsers->isEmpty()) {
                $pikdiUsers = User::where('email', 'like', '%pikdi%')->orWhere('name', 'like', '%pikdi%')->get();
            }

            switch ($step) {
                case 'diajukan':
                    // Notifikasi ke Tim SDM
                    $msgSdm = "Permohonan akun email baru [{$item->nomor_tiket}] dari {$item->nama_pemohon} ({$item->unit_nama}) usulan [{$item->email_usulan}] masuk dan memerlukan validasi SDM.";
                    foreach ($sdmUsers as $sdm) {
                        $sdm->notify(new RequestEmailNotification($item, 'diajukan', 'sdm', $msgSdm));
                    }

                    // Notifikasi ke Pemohon (jika ada akun user)
                    if ($pemohonUser) {
                        $msgPemohon = "Permohonan akun email Anda [{$item->nomor_tiket}] ({$item->email_usulan}) telah diajukan dan sedang menunggu proses validasi dari Tim SDM.";
                        $pemohonUser->notify(new RequestEmailNotification($item, 'diajukan', 'pemohon', $msgPemohon));
                    }
                    break;

                case 'divalidasi_sdm':
                    $rekom = $item->email_rekomendasi_sdm ?: $item->email_usulan;
                    
                    // Notifikasi ke Tim PIKDI
                    $msgPikdi = "Permohonan email [{$item->nomor_tiket}] ({$item->nama_pemohon}) telah divalidasi oleh Tim SDM dengan rekomendasi alamat [{$rekom}]. Siap diterbitkan di Google Workspace.";
                    foreach ($pikdiUsers as $pikdi) {
                        $pikdi->notify(new RequestEmailNotification($item, 'divalidasi_sdm', 'pikdi', $msgPikdi));
                    }

                    // Notifikasi ke Pemohon
                    if ($pemohonUser) {
                        $msgPemohon = "Permohonan akun email Anda [{$item->nomor_tiket}] telah divalidasi oleh Tim SDM (rekomendasi: {$rekom}) dan diteruskan ke Tim PIKDI untuk penerbitan akun resmi.";
                        $pemohonUser->notify(new RequestEmailNotification($item, 'divalidasi_sdm', 'pemohon', $msgPemohon));
                    }
                    break;

                case 'ditolak_sdm':
                    if ($pemohonUser) {
                        $alasan = $item->catatan_sdm ?: 'Permohonan ditolak oleh Tim SDM.';
                        $msgPemohon = "Permohonan akun email [{$item->nomor_tiket}] ditolak oleh Tim SDM. Alasan: {$alasan}";
                        $pemohonUser->notify(new RequestEmailNotification($item, 'ditolak_sdm', 'pemohon', $msgPemohon));
                    }
                    break;

                case 'selesai':
                    $finalEmail = $item->email_final_dibuat ?: ($item->email_rekomendasi_sdm ?: $item->email_usulan);
                    $pass = $item->password_sementara ?: 'TigaSerangkai#' . date('Y');

                    // Notifikasi ke Pemohon
                    if ($pemohonUser) {
                        $msgPemohon = "Selamat! Akun email resmi universitas Anda [{$finalEmail}] telah aktif dan siap digunakan. Password sementara: {$pass}.";
                        $pemohonUser->notify(new RequestEmailNotification($item, 'selesai', 'pemohon', $msgPemohon));
                    }

                    // Notifikasi ke Tim SDM
                    $msgSdm = "Penerbitan akun email resmi untuk {$item->nama_pemohon} ([{$finalEmail}]) telah berhasil diselesaikan oleh Tim PIKDI.";
                    foreach ($sdmUsers as $sdm) {
                        $sdm->notify(new RequestEmailNotification($item, 'selesai', 'sdm', $msgSdm));
                    }
                    break;

                case 'ditolak_pikdi':
                    $alasan = $item->catatan_pikdi ?: 'Kendala teknis pada proses penerbitan akun.';
                    // Notifikasi ke Pemohon
                    if ($pemohonUser) {
                        $msgPemohon = "Penerbitan akun email [{$item->nomor_tiket}] mengalami kendala oleh Tim PIKDI. Catatan: {$alasan}";
                        $pemohonUser->notify(new RequestEmailNotification($item, 'ditolak_pikdi', 'pemohon', $msgPemohon));
                    }

                    // Notifikasi ke Tim SDM
                    $msgSdm = "Penerbitan akun email [{$item->nomor_tiket}] ({$item->nama_pemohon}) terkendala/ditolak di PIKDI: {$alasan}";
                    foreach ($sdmUsers as $sdm) {
                        $sdm->notify(new RequestEmailNotification($item, 'ditolak_pikdi', 'sdm', $msgSdm));
                    }
                    break;
            }
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim notifikasi request email [{$item->nomor_tiket}]: " . $th->getMessage());
        }
    }

    /**
     * Hapus Data Permohonan
     */
    public function destroy($id)
    {
        $this->guard('delete', 'admin:persuratan');

        $item = RequestEmail::findOrFail($id);
        $no = $item->nomor_tiket;
        $item->delete();

        return back()->with('success', "Data permohonan email [{$no}] berhasil dihapus.");
    }
}
