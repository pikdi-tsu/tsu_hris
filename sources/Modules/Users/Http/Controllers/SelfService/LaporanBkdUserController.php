<?php

namespace Modules\Users\Http\Controllers\SelfService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataDosenTendik;
use App\Models\PeriodeBkd;
use App\Models\LaporanBkd;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Traits\ApiResponseTrait;

class LaporanBkdUserController extends Controller
{
    use ApiResponseTrait;

    private function getLoggedInDosen()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    /**
     * Halaman Upload & Riwayat BKD Dosen Mandiri
     */
    public function index()
    {
        $dosen = $this->getLoggedInDosen();
        $activePeriode = PeriodeBkd::getActivePeriode();

        $myLaporan = null;
        $isDosenBaru = false;

        if ($dosen) {
            $tglMasuk = $dosen->tgl_bergabung ? Carbon::parse($dosen->tgl_bergabung) : null;
            $refDate = $activePeriode?->tgl_selesai ? Carbon::parse($activePeriode->tgl_selesai) : now();
            $isDosenBaru = $tglMasuk && $tglMasuk->diffInMonths($refDate) < 12;

            if ($activePeriode) {
                $myLaporan = LaporanBkd::where('data_dosen_tendik_id', $dosen->id)
                    ->where('periode_bkd_id', $activePeriode->id)
                    ->first();
            }

            $riwayatLaporan = LaporanBkd::with('periode')
                ->where('data_dosen_tendik_id', $dosen->id)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $riwayatLaporan = collect([]);
        }

        return view('users::selfservice.bkd.index', [
            'title'          => 'Laporan BKD / LKD Dosen',
            'menuIcon'       => 'fas fa-graduation-cap',
            'dosen'          => $dosen,
            'activePeriode'  => $activePeriode,
            'myLaporan'      => $myLaporan,
            'isDosenBaru'    => $isDosenBaru,
            'riwayatLaporan' => $riwayatLaporan,
        ]);
    }

    /**
     * Upload File PDF Laporan BKD oleh Dosen
     */
    public function store(Request $request)
    {
        $dosen = $this->getLoggedInDosen();
        if (!$dosen) {
            return response()->json([
                'success' => false,
                'message' => 'Profil kepegawaian Anda belum terdaftar sebagai Dosen.',
            ], 422);
        }

        $activePeriode = PeriodeBkd::getActivePeriode();
        if (!$activePeriode) {
            return response()->json([
                'success' => false,
                'message' => 'Saat ini belum ada periode pelaporan BKD yang aktif.',
            ], 422);
        }

        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:15360',
            'catatan'  => 'nullable|string',
        ]);

        $file = $request->file('file_pdf');
        $filename = "LKD_BKD_{$dosen->nik}_{$activePeriode->id}_" . time() . ".pdf";

        $file->storeAs('private/laporan_bkd', $filename);
        $filePath = 'private/laporan_bkd/' . $filename;

        $laporan = LaporanBkd::updateOrCreate(
            [
                'data_dosen_tendik_id' => $dosen->id,
                'periode_bkd_id'       => $activePeriode->id,
            ],
            [
                'file_pdf'          => $filePath,
                'file_size'         => $file->getSize(),
                'tanggal_upload'    => now(),
                'status_verifikasi' => 'draft',
                'catatan'           => $request->catatan,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan BKD Anda berhasil diunggah dan tersimpan.',
        ]);
    }

    /**
     * Stream Berkas PDF Laporan Saya
     */
    public function stream($id)
    {
        $dosen = $this->getLoggedInDosen();
        $laporan = LaporanBkd::where('data_dosen_tendik_id', $dosen?->id)->findOrFail($id);

        if (!Storage::exists($laporan->file_pdf)) {
            abort(404, 'Berkas PDF tidak ditemukan.');
        }

        return response()->file(Storage::path($laporan->file_pdf), [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan_BKD_' . basename($laporan->file_pdf) . '"',
        ]);
    }
}
