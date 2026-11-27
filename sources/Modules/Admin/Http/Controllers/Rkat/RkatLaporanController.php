<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterUnit;
use App\Models\RkatAkun;
use App\Models\RkatAnggaranItem;
use App\Models\RkatPengajuan;
use App\Models\RkatPeriode;
use App\Models\RkatProgram;
use App\Models\RkatSumberDana;
use Illuminate\Http\Request;

class RkatLaporanController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Halaman Laporan Komprehensif RKAT
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id', optional(RkatPeriode::active()->first())->id ?? optional($periodes->first())->id);
        $selectedType = $request->input('type', 'unit'); // unit, program, kegiatan, akun, sumber_dana

        $pengajuans = RkatPengajuan::with(['periode', 'unit', 'program', 'anggaranItems.akun', 'anggaranItems.sumberDana'])
            ->when($selectedPeriodeId, function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })
            ->get();

        $dataLaporan = [];

        if ($selectedType === 'unit') {
            $units = MasterUnit::orderBy('nama_unit')->get();
            foreach ($units as $u) {
                $uPeng = $pengajuans->where('unit_id', $u->id);
                $anggaran = $uPeng->where('status', 'Disetujui')->sum('total_anggaran_disetujui');
                $realisasi = $uPeng->sum('total_realisasi');
                if ($uPeng->count() > 0 || $anggaran > 0) {
                    $dataLaporan[] = [
                        'nama' => $u->nama_unit,
                        'total_kegiatan' => $uPeng->count(),
                        'anggaran_diajukan' => $uPeng->sum('total_anggaran_diajukan'),
                        'anggaran_disetujui' => $anggaran,
                        'realisasi' => $realisasi,
                        'sisa' => max(0, $anggaran - $realisasi),
                        'persen_serapan' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 1) : 0,
                    ];
                }
            }
        } elseif ($selectedType === 'program') {
            $programs = RkatProgram::where('is_active', 1)->get();
            foreach ($programs as $prg) {
                $pPeng = $pengajuans->where('program_id', $prg->id);
                $anggaran = $pPeng->where('status', 'Disetujui')->sum('total_anggaran_disetujui');
                $realisasi = $pPeng->sum('total_realisasi');
                if ($pPeng->count() > 0 || $anggaran > 0) {
                    $dataLaporan[] = [
                        'kode' => $prg->kode_program,
                        'nama' => $prg->nama_program,
                        'total_kegiatan' => $pPeng->count(),
                        'anggaran_diajukan' => $pPeng->sum('total_anggaran_diajukan'),
                        'anggaran_disetujui' => $anggaran,
                        'realisasi' => $realisasi,
                        'sisa' => max(0, $anggaran - $realisasi),
                        'persen_serapan' => $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 1) : 0,
                    ];
                }
            }
        } elseif ($selectedType === 'akun') {
            $akuns = RkatAkun::where('is_active', 1)->get();
            foreach ($akuns as $ak) {
                $items = RkatAnggaranItem::whereHas('pengajuan', function ($q) use ($selectedPeriodeId) {
                    $q->when($selectedPeriodeId, fn($sq) => $sq->where('periode_id', $selectedPeriodeId))
                      ->where('status', 'Disetujui');
                })->where('akun_id', $ak->id)->get();

                $totalPagu = $items->sum('total_biaya');
                if ($totalPagu > 0) {
                    $dataLaporan[] = [
                        'kode' => $ak->kode_akun,
                        'nama' => $ak->nama_akun,
                        'kategori' => $ak->kategori ?? 'Operasional',
                        'total_item' => $items->count(),
                        'anggaran_disetujui' => $totalPagu,
                    ];
                }
            }
        } else {
            // Default Kegiatan
            foreach ($pengajuans as $k) {
                $dataLaporan[] = [
                    'nomor' => $k->nomor_pengajuan,
                    'nama' => $k->nama_kegiatan,
                    'unit' => $k->unit->nama_unit ?? '-',
                    'program' => $k->program->nama_program ?? '-',
                    'status' => $k->status,
                    'anggaran_diajukan' => $k->total_anggaran_diajukan,
                    'anggaran_disetujui' => $k->total_anggaran_disetujui,
                    'realisasi' => $k->total_realisasi,
                    'sisa' => $k->sisa_anggaran,
                    'persen_serapan' => $k->persen_serapan,
                ];
            }
        }

        return view('admin::rkat.laporan.index', [
            'title' => 'Laporan RKAT & Anggaran',
            'periodes' => $periodes,
            'selectedPeriodeId' => $selectedPeriodeId,
            'selectedType' => $selectedType,
            'dataLaporan' => $dataLaporan,
        ]);
    }
}
