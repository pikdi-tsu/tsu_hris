<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\PeriodeBkd;
use App\Models\LaporanBkd;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Traits\ApiResponseTrait;

class LaporanBkdAdminController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:pengembangan-sdm');
    }

    /**
     * Dashboard Rekap Monitoring BKD per Program Studi
     */
    public function index(Request $request)
    {
        $periodeList = PeriodeBkd::orderBy('id', 'desc')->get();
        $selectedPeriodeId = $request->get('periode_id', PeriodeBkd::getActivePeriode()?->id);
        $activePeriode = PeriodeBkd::find($selectedPeriodeId) ?? PeriodeBkd::getActivePeriode();

        // Ambil daftar Program Studi
        $prodiList = MasterUnit::where(function ($q) {
            $q->where('nama_unit', 'LIKE', 'S1%')
              ->orWhere('nama_unit', 'LIKE', 'D3%')
              ->orWhere('nama_unit', 'LIKE', 'Prodi%');
        })->orderBy('nama_unit', 'asc')->get();

        // Ambil seluruh dosen aktif
        $dosenList = DataDosenTendik::with(['unit', 'laporanBkds' => function($q) use ($activePeriode) {
            if ($activePeriode) {
                $q->where('periode_bkd_id', $activePeriode->id);
            }
        }])
        ->where('is_active', 1)
        ->where(function($q) {
            $q->where('tipe_karyawan', 'dosen')
              ->orWhereNotNull('nidn');
        })
        ->get();

        // Hitung batas orientasi dosen baru (< 1 tahun dari tanggal akhir periode atau hari ini)
        $refDate = $activePeriode?->tgl_selesai ? Carbon::parse($activePeriode->tgl_selesai) : now();

        $totalDosen = $dosenList->count();
        $sudahLapor = 0;
        $belumLapor = 0;
        $dosenBaruCount = 0;

        foreach ($dosenList as $dosen) {
            $tglMasuk = $dosen->tgl_bergabung ? Carbon::parse($dosen->tgl_bergabung) : null;
            $isDosenBaru = $tglMasuk && $tglMasuk->diffInMonths($refDate) < 12;
            $hasLaporan = $dosen->laporanBkds->isNotEmpty();

            if ($isDosenBaru) {
                $dosenBaruCount++;
                if ($hasLaporan) $sudahLapor++;
            } elseif ($hasLaporan) {
                $sudahLapor++;
            } else {
                $belumLapor++;
            }
        }

        return view('admin::monitoring-bkd.index', [
            'title'             => 'Monitoring Laporan BKD / LKD Dosen',
            'menuIcon'          => 'fas fa-graduation-cap',
            'periodeList'       => $periodeList,
            'activePeriode'     => $activePeriode,
            'selectedPeriodeId' => $selectedPeriodeId,
            'prodiList'         => $prodiList,
            'totalDosen'        => $totalDosen,
            'sudahLapor'        => $sudahLapor,
            'belumLapor'        => $belumLapor,
            'dosenBaruCount'    => $dosenBaruCount,
        ]);
    }

    /**
     * DataTables Daftar Dosen & Status BKD
     */
    public function datatable(Request $request)
    {
        $periodeId = $request->get('periode_id', PeriodeBkd::getActivePeriode()?->id);
        $activePeriode = PeriodeBkd::find($periodeId);
        $refDate = $activePeriode?->tgl_selesai ? Carbon::parse($activePeriode->tgl_selesai) : now();

        $query = DataDosenTendik::with([
            'unit',
            'laporanBkds' => function ($q) use ($periodeId) {
                $q->where('periode_bkd_id', $periodeId);
            }
        ])
        ->where('is_active', 1)
        ->where(function($q) {
            $q->where('tipe_karyawan', 'dosen')
              ->orWhereNotNull('nidn');
        });

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('dosen_info', function ($row) {
                $nama = $row->nama_lengkap ?? $row->nama;
                $nidn = $row->nidn ? '<span class="badge badge-light border text-primary mr-1">NIDN: ' . $row->nidn . '</span>' : '';
                $prodi = $row->unit ? $row->unit->nama_unit : 'Homebase Belum Ditentukan';
                return '<div><strong>' . htmlspecialchars($nama) . '</strong></div>' .
                       '<small class="text-muted">' . $nidn . htmlspecialchars($prodi) . '</small>';
            })
            ->addColumn('tgl_bergabung_fmt', function ($row) {
                return $row->tgl_bergabung ? Carbon::parse($row->tgl_bergabung)->format('d M Y') : '-';
            })
            ->addColumn('status_bkd', function ($row) use ($refDate) {
                $tglMasuk = $row->tgl_bergabung ? Carbon::parse($row->tgl_bergabung) : null;
                $isDosenBaru = $tglMasuk && $tglMasuk->diffInMonths($refDate) < 12;

                $laporan = $row->laporanBkds->first();

                if ($laporan) {
                    $badge = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Sudah Lapor</span>';
                    if ($isDosenBaru) {
                        $badge .= '<br><span class="badge badge-secondary px-2 py-1 mt-1"><i class="fas fa-user-clock mr-1"></i> Dosen Baru</span>';
                    }
                    return $badge;
                }

                if ($isDosenBaru) {
                    return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-user-clock mr-1"></i> Dosen Baru (Orientasi)</span><br><small class="text-muted">Masa kerja &lt; 1 thn</small>';
                }

                return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Belum Lapor</span>';
            })
            ->addColumn('file_laporan', function ($row) {
                $laporan = $row->laporanBkds->first();
                if ($laporan) {
                    $size = $laporan->file_size ? ' (' . round($laporan->file_size / 1024) . ' KB)' : '';
                    return '<a href="' . route('admin.monitoring-bkd.stream', $laporan->id) . '" target="_blank" class="btn btn-xs btn-outline-danger font-weight-bold">
                        <i class="fas fa-file-pdf mr-1"></i> Lihat PDF ' . $size . '
                    </a><br><small class="text-muted">' . $laporan->tanggal_upload->format('d/m/Y H:i') . '</small>';
                }
                return '<span class="text-muted small font-italic">- Belum ada berkas -</span>';
            })
            ->addColumn('aksi', function ($row) use ($periodeId) {
                $laporan = $row->laporanBkds->first();
                $btn = '<div class="btn-group btn-group-sm">';
                if ($laporan) {
                    $btn .= '<button type="button" class="btn btn-outline-info btn-verif" data-url="' . route('admin.monitoring-bkd.modal-verif', $laporan->id) . '" title="Verifikasi / Beri Catatan"><i class="fas fa-tasks mr-1"></i> Verifikasi</button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-outline-secondary btn-upload-admin" data-dosen-id="' . $row->id . '" data-periode-id="' . $periodeId . '" data-nama="' . htmlspecialchars($row->nama) . '" title="Bantu Upload PDF"><i class="fas fa-upload mr-1"></i> Upload</button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['dosen_info', 'status_bkd', 'file_laporan', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Form Verifikasi Laporan BKD
     */
    public function modalVerif($id)
    {
        $laporan = LaporanBkd::with(['dosen.unit', 'periode'])->findOrFail($id);
        return view('admin::monitoring-bkd.verif_modal', compact('laporan'));
    }

    /**
     * Simpan Verifikasi Laporan BKD
     */
    public function storeVerif(Request $request, $id)
    {
        $laporan = LaporanBkd::findOrFail($id);

        $request->validate([
            'status_verifikasi' => 'required|in:draft,diverifikasi,perlu_revisi',
            'catatan'           => 'nullable|string',
        ]);

        $laporan->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan'           => $request->catatan,
            'diverifikasi_by'   => Auth::id(),
            'diverifikasi_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status verifikasi laporan BKD berhasil disimpan.',
        ]);
    }

    /**
     * Admin bantu upload berkas PDF BKD dosen
     */
    public function storeAdminUpload(Request $request)
    {
        $request->validate([
            'data_dosen_tendik_id' => 'required|exists:data_dosen_tendiks,id',
            'periode_bkd_id'       => 'required|exists:periode_bkds,id',
            'file_pdf'             => 'required|file|mimes:pdf|max:15360',
            'catatan'              => 'nullable|string',
        ]);

        $dosen = DataDosenTendik::findOrFail($request->data_dosen_tendik_id);
        $file = $request->file('file_pdf');
        $filename = "LKD_BKD_{$dosen->nik}_{$request->periode_bkd_id}_" . time() . ".pdf";

        $file->storeAs('private/laporan_bkd', $filename);
        $filePath = 'private/laporan_bkd/' . $filename;

        $laporan = LaporanBkd::updateOrCreate(
            [
                'data_dosen_tendik_id' => $dosen->id,
                'periode_bkd_id'       => $request->periode_bkd_id,
            ],
            [
                'file_pdf'          => $filePath,
                'file_size'         => $file->getSize(),
                'tanggal_upload'    => now(),
                'status_verifikasi' => 'diverifikasi',
                'catatan'           => $request->catatan,
                'diverifikasi_by'   => Auth::id(),
                'diverifikasi_at'   => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Laporan BKD untuk dosen {$dosen->nama} berhasil diunggah.",
        ]);
    }

    /**
     * Secure Stream Berkas PDF Laporan BKD
     */
    public function stream($id)
    {
        $laporan = LaporanBkd::findOrFail($id);

        if (!Storage::exists($laporan->file_pdf)) {
            abort(404, 'Berkas PDF laporan BKD tidak ditemukan.');
        }

        return response()->file(Storage::path($laporan->file_pdf), [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan_BKD_' . basename($laporan->file_pdf) . '"',
        ]);
    }
}
