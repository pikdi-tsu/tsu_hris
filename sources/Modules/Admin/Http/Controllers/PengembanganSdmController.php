<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterBidangKeilmuan;
use App\Models\MasterPeriodePengembangan;
use App\Models\MasterSertifikasi;
use App\Models\MasterUnit;
use App\Models\PengembanganSdmPeserta;
use App\Models\PengembanganSdmSertifikasi;
use App\Models\PengembanganSdmTimeline;
use App\Services\PengembanganSdmService;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PengembanganSdmController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:pengembangan-sdm');
    }

    /**
     * TAMPILAN 1: Dashboard Eksekutif Level Universitas (10 Prodi Dosen & 9 Unit Tendik)
     */
    public function dashboard(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $periodeList = MasterPeriodePengembangan::orderBy('tahun_mulai', 'desc')->get();
        $selectedPeriodeId = $request->get('periode_id', $periodeList->firstWhere('is_active', true)?->id ?? $periodeList->first()?->id);

        $dashboardData = PengembanganSdmService::getExecutiveDashboardData($selectedPeriodeId);

        return view('admin::pengembangan-sdm.dashboard', array_merge($dashboardData, [
            'title' => 'Dashboard & Rekapitulasi Pengembangan SDM',
            'periodeList' => $periodeList,
            'selectedPeriodeId' => $selectedPeriodeId,
        ]));
    }

    /**
     * TAMPILAN 2: Lembar Kerja Roadmap Dosen per Program Studi (10 Prodi)
     */
    public function dosen(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $periodeList = MasterPeriodePengembangan::orderBy('tahun_mulai', 'desc')->get();
        $selectedPeriodeId = $request->get('periode_id', $periodeList->firstWhere('is_active', true)?->id ?? $periodeList->first()?->id);

        $prodiList = MasterUnit::where(function ($q) {
            $q->where('nama_unit', 'LIKE', 'S1%')
              ->orWhere('nama_unit', 'LIKE', 'D3%');
        })->orderBy('nama_unit', 'asc')->get();

        $selectedUnitId = $request->get('unit_id', $prodiList->first()?->id);
        if (!$selectedUnitId) {
            return redirect()->route('admin.pengembangan-sdm.dashboard')->with('error', 'Program Studi belum terdaftar.');
        }

        $worksheetData = PengembanganSdmService::getProdiDosenData($selectedUnitId, $selectedPeriodeId);
        $masterBidang = MasterBidangKeilmuan::where('kategori', 'dosen')->where('is_active', true)->get();

        return view('admin::pengembangan-sdm.dosen_prodi', array_merge($worksheetData, [
            'title' => 'Road Map Studi Lanjut Dosen per Program Studi',
            'periodeList' => $periodeList,
            'selectedPeriodeId' => $selectedPeriodeId,
            'prodiList' => $prodiList,
            'selectedUnitId' => $selectedUnitId,
            'masterBidang' => $masterBidang,
        ]));
    }

    /**
     * Lembar Kerja Roadmap Tendik per Unit/Biro (9 Unit)
     */
    public function tendik(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $periodeList = MasterPeriodePengembangan::orderBy('tahun_mulai', 'desc')->get();
        $selectedPeriodeId = $request->get('periode_id', $periodeList->firstWhere('is_active', true)?->id ?? $periodeList->first()?->id);

        $unitList = MasterUnit::where(function ($q) {
            $q->where('nama_unit', 'NOT LIKE', 'S1%')
              ->where('nama_unit', 'NOT LIKE', 'D3%')
              ->where('nama_unit', '!=', '-');
        })->orderBy('nama_unit', 'asc')->get();

        $selectedUnitId = $request->get('unit_id', $unitList->first()?->id);
        if (!$selectedUnitId) {
            return redirect()->route('admin.pengembangan-sdm.dashboard')->with('error', 'Unit Kerja belum terdaftar.');
        }

        $worksheetData = PengembanganSdmService::getUnitTendikData($selectedUnitId, $selectedPeriodeId);
        $masterBidang = MasterBidangKeilmuan::where('kategori', 'tendik')->where('is_active', true)->get();

        return view('admin::pengembangan-sdm.tendik_unit', array_merge($worksheetData, [
            'title' => 'Perencanaan Studi Lanjut & Sertifikasi Tenaga Kependidikan',
            'periodeList' => $periodeList,
            'selectedPeriodeId' => $selectedPeriodeId,
            'unitList' => $unitList,
            'selectedUnitId' => $selectedUnitId,
            'masterBidang' => $masterBidang,
        ]));
    }

    /**
     * Monitoring Usia Pensiun Pegawai (Early Warning)
     */
    public function pensiun(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $periodeList = MasterPeriodePengembangan::orderBy('tahun_mulai', 'desc')->get();
        $selectedPeriodeId = $request->get('periode_id', $periodeList->firstWhere('is_active', true)?->id ?? $periodeList->first()?->id);

        $pensiunData = PengembanganSdmService::getMonitoringPensiunData($selectedPeriodeId);

        return view('admin::pengembangan-sdm.monitoring_pensiun', array_merge($pensiunData, [
            'title' => 'Monitoring Masa Pensiun & Regenerasi SDM',
            'periodeList' => $periodeList,
            'selectedPeriodeId' => $selectedPeriodeId,
        ]));
    }

    /**
     * AJAX Update Timeline Status (e.g. S2, S2+, S3, SS, TSS, Bidang)
     */
    public function updateTimeline(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        try {
            $request->validate([
                'peserta_id' => 'required|uuid',
                'tahun' => 'required|integer',
                'status_studi' => 'required|string|max:20',
                'bidang_kode' => 'nullable|string|max:20',
            ]);

            $peserta = PengembanganSdmPeserta::findOrFail($request->peserta_id);
            $stUpper = strtoupper($request->status_studi);
            $actStatus = (str_contains($stUpper, '+') || str_contains($stUpper, 'SS')) ? 'SS' : 'TSS';

            $timeline = PengembanganSdmTimeline::updateOrCreate(
                [
                    'peserta_id' => $peserta->id,
                    'tahun' => $request->tahun,
                ],
                [
                    'status_studi' => $request->status_studi,
                    'status_aktif_studi' => $actStatus,
                    'bidang_kode' => $request->bidang_kode ? strtoupper($request->bidang_kode) : null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Status timeline berhasil diperbarui!',
                'timeline' => $timeline,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX Update Lokasi Studi (DN vs LN)
     */
    public function updateLokasi(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        try {
            $request->validate([
                'peserta_id' => 'required|uuid',
                'lokasi_studi' => 'required|in:DN,LN',
            ]);

            $peserta = PengembanganSdmPeserta::findOrFail($request->peserta_id);
            $peserta->lokasi_studi = $request->lokasi_studi;
            $peserta->save();

            return response()->json([
                'success' => true,
                'message' => 'Lokasi studi berhasil diperbarui!',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleSertifikasi(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        try {
            $request->validate([
                'peserta_id' => 'required|uuid',
                'sertifikasi_id' => 'required|uuid',
                'tahun_target' => 'nullable|integer',
            ]);

            $status = $request->get('status', true);
            $isTrue = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isTrue === null && is_string($status) && in_array(strtolower($status), ['false', '0', 'hapus', 'delete'])) {
                $isTrue = false;
            } else {
                $isTrue = true;
            }

            if ($isTrue) {
                PengembanganSdmSertifikasi::updateOrCreate(
                    [
                        'peserta_id' => $request->peserta_id,
                        'sertifikasi_id' => $request->sertifikasi_id,
                    ],
                    [
                        'tahun_target' => $request->tahun_target ?? 2026,
                        'status' => is_string($status) && !in_array(strtolower($status), ['true', '1']) ? $status : 'Rencana',
                        'status_kepemilikan' => true,
                    ]
                );
            } else {
                PengembanganSdmSertifikasi::where('peserta_id', $request->peserta_id)
                    ->where('sertifikasi_id', $request->sertifikasi_id)
                    ->delete();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status sertifikasi berhasil diperbarui!',
                ]);
            }
            return redirect()->back()->with('success', 'Status sertifikasi berhasil diperbarui!');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tambah Slot Proyeksi Dosen Baru S3
     */
    public function addDosenBaru(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        try {
            $nama = $request->nama ?? $request->nama_lengkap;
            $tahunMulai = $request->tahun_mulai ?? $request->tahun_masuk_s3 ?? 2026;
            $request->merge([
                'nama' => $nama,
                'tahun_mulai' => $tahunMulai,
            ]);

            $request->validate([
                'unit_id' => 'required|uuid',
                'tahun_mulai' => 'required|integer',
                'nama' => 'required|string|max:100',
            ]);

            $periode = PengembanganSdmService::getActivePeriode();
            $maxOrder = PengembanganSdmPeserta::where('master_periode_id', $periode->id)
                ->where('unit_id', $request->unit_id)
                ->max('order_no') ?? 0;

            $peserta = PengembanganSdmPeserta::create([
                'master_periode_id' => $periode->id,
                'tipe_pegawai' => 'dosen',
                'unit_id' => $request->unit_id,
                'nama_placeholder' => $request->nama,
                'pendidikan_awal' => 'S3',
                'gelar' => 'Dr. / Ph.D',
                'lokasi_studi' => $request->lokasi_studi ?? 'DN',
                'order_no' => $maxOrder + 1,
            ]);

            // Inisialisasi timeline 2026-2030
            $thnRange = range($periode->tahun_mulai, $periode->tahun_selesai);
            foreach ($thnRange as $thn) {
                $status = ($thn >= $request->tahun_mulai) ? 'S3' : '-';
                $actStatus = ($status === 'S3') ? 'TSS' : 'TSS';
                PengembanganSdmTimeline::create([
                    'peserta_id' => $peserta->id,
                    'tahun' => $thn,
                    'status_studi' => $status,
                    'status_aktif_studi' => $actStatus,
                ]);
            }

            return back()->with('success', "Slot proyeksi '{$request->nama}' berhasil ditambahkan!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menambah proyeksi dosen: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Peserta / Slot Proyeksi
     */
    public function deletePeserta(Request $request, $id)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        try {
            $peserta = PengembanganSdmPeserta::findOrFail($id);
            $nama = $peserta->nama_tampil ?? 'Peserta';
            $peserta->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data '{$nama}' berhasil dihapus dari rencana pengembangan.",
                ]);
            }

            return back()->with('success', "Data '{$nama}' berhasil dihapus dari rencana pengembangan.");
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menghapus peserta: ' . $e->getMessage());
        }
    }

    /**
     * Sync / Re-import Data dari Excel Awal
     */
    public function reimportExcel(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        try {
            Artisan::call('pengembangan:import-excel');
            return back()->with('success', 'Sinkronisasi data awal dari Excel berhasil dijalankan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal sinkronisasi data: ' . $e->getMessage());
        }
    }
}
