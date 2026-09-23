<?php

namespace Modules\Admin\Http\Controllers\EmployeeLifecycle;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\MasterGajiPokok;
use App\Models\MasterUnit;
use App\Models\PegawaiKontrak;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class KontrakKerjaController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        // Dapat diakses oleh admin HR
    }

    /**
     * Halaman Utama Monitoring Kontrak Kerja (PKWT)
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $in30Days = Carbon::today()->addDays(30)->toDateString();
        $in60Days = Carbon::today()->addDays(60)->toDateString();

        // 1. Total Kontrak Aktif
        $totalAktif = PegawaiKontrak::where('status', 'aktif')->count();

        // 2. Kritis (≤ 30 hari atau sudah lewat tapi masih berstatus aktif)
        $kritisCount = PegawaiKontrak::where('status', 'aktif')
            ->where('tgl_selesai', '<=', $in30Days)
            ->count();

        // 3. Perhatian (31 - 60 hari ke depan)
        $perhatianCount = PegawaiKontrak::where('status', 'aktif')
            ->whereBetween('tgl_selesai', [Carbon::today()->addDays(31)->toDateString(), $in60Days])
            ->count();

        // 4. Riwayat Selesai / Diperpanjang
        $riwayatCount = PegawaiKontrak::whereIn('status', ['diperpanjang', 'selesai', 'diputus'])->count();

        $units = MasterUnit::orderBy('nama_unit')->get();

        return view('admin::employee-lifecycle.kontrak.index', [
            'title'          => 'Monitoring Kontrak Kerja (PKWT)',
            'menu'           => 'kontrak-kerja',
            'menuIcon'       => 'fas fa-file-contract',
            'totalAktif'     => $totalAktif,
            'kritisCount'    => $kritisCount,
            'perhatianCount' => $perhatianCount,
            'riwayatCount'   => $riwayatCount,
            'units'          => $units,
        ]);
    }

    /**
     * DataTables Kontrak Kerja
     */
    public function datatable(Request $request)
    {
        $query = PegawaiKontrak::with(['pegawai.unit'])
            ->select('pegawai_kontraks.*');

        // Filter status tab
        if ($request->filled('filter_status')) {
            $fStatus = $request->filter_status;
            if ($fStatus === 'kritis') {
                $query->where('status', 'aktif')
                    ->where('tgl_selesai', '<=', Carbon::today()->addDays(30)->toDateString());
            } elseif ($fStatus === 'perhatian') {
                $query->where('status', 'aktif')
                    ->whereBetween('tgl_selesai', [Carbon::today()->addDays(31)->toDateString(), Carbon::today()->addDays(60)->toDateString()]);
            } elseif ($fStatus === 'aktif') {
                $query->where('status', 'aktif');
            } elseif ($fStatus === 'riwayat') {
                $query->whereIn('status', ['diperpanjang', 'selesai', 'diputus']);
            }
        }

        // Filter unit
        if ($request->filled('unit_id')) {
            $unitId = $request->unit_id;
            $query->whereHas('pegawai', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $pegawai = $row->pegawai;
                if (!$pegawai) {
                    return '<span class="text-muted font-italic">Data Pegawai Tidak Ditemukan</span>';
                }

                $namaLengkap = trim(($pegawai->gelar_depan ? $pegawai->gelar_depan . ' ' : '') . $pegawai->nama . ($pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : ''));
                $unitNama = $pegawai->unit->nama_unit ?? '-';
                $nik = $pegawai->nik ?? $pegawai->nip ?? '-';
                $posisi = $row->posisi ?: ($pegawai->posisi ?? '-');
                $tipeBadge = $pegawai->tipe_karyawan === 'Dosen'
                    ? '<span class="badge badge-primary px-2 py-1">Dosen</span>'
                    : '<span class="badge badge-info px-2 py-1">Tendik</span>';

                return '
                    <div class="d-flex align-items-center">
                        <div class="mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: #094b54; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            ' . strtoupper(substr($pegawai->nama, 0, 1)) . '
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">' . e($namaLengkap) . ' ' . $tipeBadge . '</div>
                            <div class="text-muted small">NIK: <span class="font-weight-bold">' . e($nik) . '</span> | Unit: ' . e($unitNama) . '</div>
                            <div class="text-muted small">Posisi: <span class="text-dark font-weight-bold">' . e($posisi) . '</span></div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('kontrak_info', function ($row) {
                $noKontrak = e($row->no_kontrak);
                $badgeKe = '<span class="badge badge-light border text-dark">Kontrak ke-' . $row->kontrak_ke . '</span>';
                $gaji = $row->gaji_pokok_disepakati ? 'Rp ' . number_format($row->gaji_pokok_disepakati, 0, ',', '.') : '-';

                return '
                    <div>
                        <div class="font-weight-bold text-dark">' . $noKontrak . ' ' . $badgeKe . '</div>
                        <div class="text-muted small">Durasi: <strong>' . $row->durasi_bulan . ' Bulan</strong></div>
                        <div class="text-muted small">Gaji Pokok: ' . $gaji . '</div>
                    </div>
                ';
            })
            ->addColumn('periode', function ($row) {
                $mulai = $row->tgl_mulai ? Carbon::parse($row->tgl_mulai)->format('d M Y') : '-';
                $selesai = $row->tgl_selesai ? Carbon::parse($row->tgl_selesai)->format('d M Y') : '-';

                return '
                    <div>
                        <div><i class="far fa-calendar-alt text-muted mr-1"></i> ' . $mulai . ' s/d</div>
                        <div class="font-weight-bold text-dark">' . $selesai . '</div>
                    </div>
                ';
            })
            ->addColumn('sisa_waktu', function ($row) {
                if ($row->status !== 'aktif') {
                    if ($row->status === 'diperpanjang') {
                        return '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-history mr-1"></i> Diperpanjang</span>';
                    } elseif ($row->status === 'selesai') {
                        return '<span class="badge badge-dark px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai</span>';
                    } else {
                        return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> ' . ucfirst($row->status) . '</span>';
                    }
                }

                $sisa = $row->sisa_hari;
                if ($sisa < 0) {
                    return '<span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Lewat ' . abs($sisa) . ' Hari</span>';
                } elseif ($sisa <= 30) {
                    return '<span class="badge badge-danger px-2 py-1 font-weight-bold animate__animated animate__pulse animate__infinite"><i class="fas fa-exclamation-circle mr-1"></i> Sisa ' . $sisa . ' Hari</span>';
                } elseif ($sisa <= 60) {
                    return '<span class="badge badge-warning px-2 py-1 text-dark font-weight-bold"><i class="fas fa-clock mr-1"></i> Sisa ' . $sisa . ' Hari</span>';
                } else {
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Sisa ' . $sisa . ' Hari</span>';
                }
            })
            ->addColumn('dokumen', function ($row) {
                if ($row->dokumen_kontrak && Storage::disk('public')->exists($row->dokumen_kontrak)) {
                    $url = asset('storage/' . $row->dokumen_kontrak);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;" title="Lihat PDF"><i class="fas fa-file-pdf mr-1"></i> PDF</a>';
                }
                return '<span class="text-muted small font-italic"><i class="fas fa-minus"></i></span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';

                if ($row->status === 'aktif') {
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-success btn-perpanjang" data-id="' . $row->id . '" title="Perpanjang Kontrak" style="border-radius: 6px 0 0 6px;"><i class="fas fa-calendar-plus mr-1"></i> Perpanjang</button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-warning btn-akhiri" data-id="' . $row->id . '" data-nama="' . e($row->pegawai->nama ?? 'Pegawai') . '" title="Selesaikan Kontrak"><i class="fas fa-flag-checkered mr-1"></i> Selesai</button>';
                }

                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' . $row->id . '" title="Hapus Data" style="' . ($row->status !== 'aktif' ? 'border-radius: 6px;' : 'border-radius: 0 6px 6px 0;') . '"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['pegawai_info', 'kontrak_info', 'periode', 'sisa_waktu', 'dokumen', 'action'])
            ->make(true);
    }

    /**
     * Modal Form Tambah Kontrak Baru
     */
    public function modalAdd()
    {
        $pegawais = DataDosenTendik::with([
                'unit',
                'jabatanFungsionals' => function ($q) {
                    $q->where('is_active', 'Y')->with('pangkatGolongan');
                }
            ])
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get();

        $masterGapoks = MasterGajiPokok::all()->keyBy(function ($item) {
            return strtoupper(trim($item->golongan));
        });

        foreach ($pegawais as $p) {
            $golStr = '-';
            $activeFung = $p->jabatanFungsionals ? $p->jabatanFungsionals->first() : null;
            if ($activeFung && $activeFung->pangkatGolongan) {
                $golStr = $activeFung->pangkatGolongan->nama_pangkat_golongan;
            }
            $cleanGol = strtoupper(trim(explode(' ', $golStr)[0] ?? ''));
            $mg = $masterGapoks->get($cleanGol);
            if ($mg) {
                $p->estimasi_gapok = floatval($mg->gaji_pokok_100);
                $p->estimasi_golongan = $cleanGol;
            } else {
                if ($p->tipe_karyawan === 'Dosen') {
                    $defaultItem = $masterGapoks->get('III/A');
                    $p->estimasi_gapok = $defaultItem ? floatval($defaultItem->gaji_pokok_100) : 3037000;
                    $p->estimasi_golongan = 'III/A (Standar Dosen)';
                } else {
                    $defaultItem = $masterGapoks->get('II/A');
                    $p->estimasi_gapok = $defaultItem ? floatval($defaultItem->gaji_pokok_100) : 2184000;
                    $p->estimasi_golongan = 'II/A (Standar Tendik)';
                }
            }
        }

        return view('admin::employee-lifecycle.kontrak.modal_add', [
            'pegawais' => $pegawais,
        ]);
    }

    /**
     * Simpan Data Kontrak Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id'            => 'required|string|exists:data_dosen_tendiks,id',
            'no_kontrak'            => 'required|string|max:100',
            'kontrak_ke'            => 'required|integer|min:1',
            'tgl_mulai'             => 'required|date',
            'tgl_selesai'           => 'required|date|after_or_equal:tgl_mulai',
            'posisi'                => 'nullable|string|max:150',
            'gaji_pokok_disepakati' => 'nullable|numeric|min:0',
            'dokumen_kontrak'       => 'nullable|file|mimes:pdf|max:5120',
            'keterangan'            => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $pegawai = DataDosenTendik::findOrFail($request->pegawai_id);

            // Hitung durasi bulan
            $mulai = Carbon::parse($request->tgl_mulai);
            $selesai = Carbon::parse($request->tgl_selesai);
            $durasiBulan = max(1, $mulai->diffInMonths($selesai));

            $filePath = null;
            if ($request->hasFile('dokumen_kontrak')) {
                $file = $request->file('dokumen_kontrak');
                $filename = 'kontrak_' . time() . '_' . $pegawai->id . '.pdf';
                $filePath = $file->storeAs('dokumen_kontrak', $filename, 'public');
            }

            PegawaiKontrak::create([
                'pegawai_id'            => $pegawai->id,
                'no_kontrak'            => $request->no_kontrak,
                'kontrak_ke'            => $request->kontrak_ke,
                'tgl_mulai'             => $request->tgl_mulai,
                'tgl_selesai'           => $request->tgl_selesai,
                'durasi_bulan'          => $durasiBulan,
                'posisi'                => $request->posisi ?: $pegawai->posisi,
                'gaji_pokok_disepakati' => $request->gaji_pokok_disepakati,
                'dokumen_kontrak'       => $filePath,
                'status'                => 'aktif',
                'keterangan'            => $request->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Kontrak kerja berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan kontrak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modal Perpanjang Kontrak
     */
    public function modalPerpanjang($id)
    {
        $kontrakLama = PegawaiKontrak::with([
            'pegawai.unit',
            'pegawai.jabatanFungsionals' => function ($q) {
                $q->where('is_active', 'Y')->with('pangkatGolongan');
            }
        ])->findOrFail($id);

        // Rekomendasi tanggal mulai baru adalah h+1 dari tgl selesai lama
        $saranMulai = Carbon::parse($kontrakLama->tgl_selesai)->addDay()->format('Y-m-d');
        $saranSelesai = Carbon::parse($saranMulai)->addMonths($kontrakLama->durasi_bulan ?: 12)->format('Y-m-d');

        // Master gapok estimasi
        $masterGapoks = MasterGajiPokok::all()->keyBy(function ($item) {
            return strtoupper(trim($item->golongan));
        });
        $pegawai = $kontrakLama->pegawai;
        $golStr = '-';
        if ($pegawai) {
            $activeFung = $pegawai->jabatanFungsionals ? $pegawai->jabatanFungsionals->first() : null;
            if ($activeFung && $activeFung->pangkatGolongan) {
                $golStr = $activeFung->pangkatGolongan->nama_pangkat_golongan;
            }
        }
        $cleanGol = strtoupper(trim(explode(' ', $golStr)[0] ?? ''));
        $mg = $masterGapoks->get($cleanGol);
        $estimasiGapok = $mg ? floatval($mg->gaji_pokok_100) : ($pegawai && $pegawai->tipe_karyawan === 'Dosen' ? 3037000 : 2184000);
        $estimasiGolongan = $cleanGol ?: ($pegawai && $pegawai->tipe_karyawan === 'Dosen' ? 'III/A' : 'II/A');

        return view('admin::employee-lifecycle.kontrak.modal_perpanjang', [
            'kontrakLama'       => $kontrakLama,
            'saranMulai'        => $saranMulai,
            'saranSelesai'      => $saranSelesai,
            'saranKe'           => ($kontrakLama->kontrak_ke ?: 1) + 1,
            'estimasiGapok'     => $estimasiGapok,
            'estimasiGolongan'  => $estimasiGolongan,
        ]);
    }

    /**
     * Simpan Perpanjangan Kontrak
     */
    public function storePerpanjang(Request $request, $id)
    {
        $kontrakLama = PegawaiKontrak::findOrFail($id);

        $request->validate([
            'no_kontrak'            => 'required|string|max:100',
            'tgl_mulai'             => 'required|date',
            'tgl_selesai'           => 'required|date|after:tgl_mulai',
            'posisi'                => 'nullable|string|max:150',
            'gaji_pokok_disepakati' => 'nullable|numeric|min:0',
            'dokumen_kontrak'       => 'nullable|file|mimes:pdf|max:5120',
            'keterangan'            => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Arsipkan kontrak lama menjadi 'diperpanjang'
            $kontrakLama->update([
                'status' => 'diperpanjang',
            ]);

            // 2. Unggah dokumen baru jika ada
            $filePath = null;
            if ($request->hasFile('dokumen_kontrak')) {
                $file = $request->file('dokumen_kontrak');
                $filename = 'kontrak_' . time() . '_' . $kontrakLama->pegawai_id . '.pdf';
                $filePath = $file->storeAs('dokumen_kontrak', $filename, 'public');
            }

            // 3. Hitung durasi bulan
            $mulai = Carbon::parse($request->tgl_mulai);
            $selesai = Carbon::parse($request->tgl_selesai);
            $durasiBulan = max(1, $mulai->diffInMonths($selesai));

            // 4. Buat kontrak baru
            PegawaiKontrak::create([
                'pegawai_id'            => $kontrakLama->pegawai_id,
                'no_kontrak'            => $request->no_kontrak,
                'kontrak_ke'            => ($kontrakLama->kontrak_ke ?: 1) + 1,
                'tgl_mulai'             => $request->tgl_mulai,
                'tgl_selesai'           => $request->tgl_selesai,
                'durasi_bulan'          => $durasiBulan,
                'posisi'                => $request->posisi ?: $kontrakLama->posisi,
                'gaji_pokok_disepakati' => $request->gaji_pokok_disepakati ?: $kontrakLama->gaji_pokok_disepakati,
                'dokumen_kontrak'       => $filePath,
                'status'                => 'aktif',
                'keterangan'            => $request->keterangan ?: 'Perpanjangan dari kontrak no: ' . $kontrakLama->no_kontrak,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Kontrak kerja berhasil diperpanjang.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperpanjang kontrak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Akhiri Kontrak (Selesai Masa Tugas)
     */
    public function akhiriKontrak(Request $request, $id)
    {
        $kontrak = PegawaiKontrak::findOrFail($id);

        $kontrak->update([
            'status'     => 'selesai',
            'keterangan' => ($kontrak->keterangan ? $kontrak->keterangan . ' | ' : '') . 'Kontrak diakhiri per ' . Carbon::now()->format('d M Y H:i'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kontrak kerja telah diselesaikan.',
        ]);
    }

    /**
     * Hapus Data Kontrak
     */
    public function destroy($id)
    {
        $kontrak = PegawaiKontrak::findOrFail($id);

        if ($kontrak->dokumen_kontrak && Storage::disk('public')->exists($kontrak->dokumen_kontrak)) {
            Storage::disk('public')->delete($kontrak->dokumen_kontrak);
        }

        $kontrak->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kontrak berhasil dihapus.',
        ]);
    }
}
