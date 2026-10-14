<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\RiwayatPerubahanData;
use App\Notifications\PengajuanDiprosesNotification;
use App\Services\TsuErrorHandlerService;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\System\Models\MenuSidebar;

class ApprovalPerubahanDataController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:data-karyawan');
    }

    /**
     * Halaman Utama Approval Perubahan Data Mandiri Pegawai
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:data-karyawan');

        $statusFilter = $request->get('status', 'menunggu');
        $search = $request->get('q', '');

        // Query Utama
        $query = RiwayatPerubahanData::with(['dataDosenTendik.unit', 'user', 'reviewer'])->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $query->whereHas('dataDosenTendik', function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('nidn', 'LIKE', "%{$search}%");
            });
        }

        $perubahans = $query->paginate(15)->withQueryString();

        // Hitung Statistik
        $statTotal = RiwayatPerubahanData::count();
        $statMenunggu = RiwayatPerubahanData::where('status', 'menunggu')->count();
        $statDisetujui = RiwayatPerubahanData::where('status', 'disetujui')->count();
        $statDitolak = RiwayatPerubahanData::where('status', 'ditolak')->count();

        $menuData = MenuSidebar::where('route', 'admin.approval-perubahan-data.index')->first();
        $title = 'Approval Perubahan Data Pegawai';
        $menuIcon = $menuData->icon ?? 'fas fa-user-check';

        return view('admin::approval-perubahan-data.index', compact(
            'title',
            'menuIcon',
            'perubahans',
            'statusFilter',
            'search',
            'statTotal',
            'statMenunggu',
            'statDisetujui',
            'statDitolak'
        ));
    }

    /**
     * Setujui (ACC) Pengajuan Perubahan Data
     */
    public function approve($id)
    {
        $this->guard('edit', 'admin:data-karyawan');

        $perubahan = RiwayatPerubahanData::with('dataDosenTendik.user')->findOrFail($id);

        if ($perubahan->status !== 'menunggu') {
            return $this->sendError('Pengajuan perubahan data ini sudah diproses sebelumnya.');
        }

        $karyawan = $perubahan->dataDosenTendik;
        if (!$karyawan) {
            return $this->sendError('Data pegawai tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            $diffs = $perubahan->perubahan_data ?: [];
            $updateData = [];

            foreach ($diffs as $field => $valItem) {
                if (Schema::hasColumn('data_dosen_tendiks', $field)) {
                    $updateData[$field] = $valItem['new'] ?? null;
                }
            }

            if (!empty($updateData)) {
                $karyawan->update($updateData);
            }

            $perubahan->update([
                'status' => 'disetujui',
                'disetujui_oleh_user_id' => Auth::id(),
                'approved_at' => Carbon::now(),
            ]);

            // Kirim notifikasi ke pegawai yang mengajukan
            $pegawaiUser = $karyawan->user ?? $perubahan->user;
            if ($pegawaiUser) {
                $pegawaiUser->notify(new PengajuanDiprosesNotification(
                    'Pengajuan perubahan data mandiri Anda telah disetujui oleh Bagian SDM.',
                    'perubahan_data',
                    route('users.profile.index'),
                    'Lihat Profil',
                    'fas fa-check-circle text-success'
                ));
            }

            DB::commit();
            return $this->sendSuccess('Pengajuan perubahan data berhasil disetujui (ACC) dan data profil pegawai telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[TSU_PERUBAHAN_ACC_FAIL]', 'Gagal menyetujui perubahan data: ' . $e->getMessage(), 'Gagal ACC Perubahan Data');
        }
    }

    /**
     * Tolak Pengajuan Perubahan Data
     */
    public function reject(Request $request, $id)
    {
        $this->guard('edit', 'admin:data-karyawan');

        $perubahan = RiwayatPerubahanData::with('dataDosenTendik.user')->findOrFail($id);

        if ($perubahan->status !== 'menunggu') {
            return $this->sendError('Pengajuan perubahan data ini sudah diproses sebelumnya.');
        }

        $alasan = $request->catatan_sdm ?: 'Ditolak oleh admin SDM.';

        try {
            $perubahan->update([
                'status' => 'ditolak',
                'disetujui_oleh_user_id' => Auth::id(),
                'catatan_sdm' => $alasan,
                'approved_at' => Carbon::now(),
            ]);

            // Kirim notifikasi penolakan ke pegawai
            $pegawaiUser = $perubahan->dataDosenTendik->user ?? $perubahan->user;
            if ($pegawaiUser) {
                $pegawaiUser->notify(new PengajuanDiprosesNotification(
                    'Pengajuan perubahan data mandiri Anda ditolak oleh Bagian SDM. Catatan: ' . $alasan,
                    'perubahan_data',
                    route('users.profile.index'),
                    'Lihat Profil',
                    'fas fa-times-circle text-danger'
                ));
            }

            return $this->sendSuccess('Pengajuan perubahan data berhasil ditolak.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_PERUBAHAN_REJ_FAIL]', 'Gagal menolak perubahan data: ' . $e->getMessage(), 'Gagal Tolak Perubahan Data');
        }
    }
}
