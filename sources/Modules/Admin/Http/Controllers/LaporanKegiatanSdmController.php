<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\LaporanKegiatanSdm;
use App\Models\LaporanKegiatanSdmDokumen;
use App\Models\LaporanKegiatanSdmPeserta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LaporanKegiatanSdmController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:laporan-kegiatan-sdm');
        $this->middleware('permission:admin:laporan-kegiatan-sdm:view')->only(['data', 'cetakPdf', 'ajaxPegawai']);
        $this->middleware('permission:admin:laporan-kegiatan-sdm:delete')->only(['destroyDokumen']);
    }

    /**
     * Halaman Utama Laporan Kegiatan SDM
     */
    public function index()
    {
        // Statistik Ringkasan
        $totalLaporan = LaporanKegiatanSdm::count();
        $totalDisetujui = LaporanKegiatanSdm::where('status', 'approved')->count();
        $totalAnggaran = LaporanKegiatanSdm::sum('total_anggaran');
        $totalRealisasi = LaporanKegiatanSdm::sum('total_realisasi');

        $kategoriList = [
            'Pelatihan & Workshop',
            'Sosialisasi & Pembekalan',
            'Rapat Kerja & Koordinasi',
            'Rekrutmen & Seleksi',
            'Evaluasi & Monitoring',
            'Pengembangan Kompetensi',
            'Gathering & Keakraban',
            'Lainnya',
        ];

        $tahunList = LaporanKegiatanSdm::selectRaw('YEAR(tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($tahunList)) {
            $tahunList = [date('Y')];
        }

        return view('admin::laporan-kegiatan-sdm.index', [
            'title'          => 'Laporan Kegiatan SDM',
            'menu'           => 'laporan-kegiatan-sdm',
            'menuIcon'       => 'fas fa-file-invoice',
            'totalLaporan'   => $totalLaporan,
            'totalDisetujui' => $totalDisetujui,
            'totalAnggaran'  => $totalAnggaran,
            'totalRealisasi' => $totalRealisasi,
            'kategoriList'   => $kategoriList,
            'tahunList'      => $tahunList,
        ]);
    }

    /**
     * AJAX DataTables Server-Side
     */
    public function data(Request $request)
    {
        $query = LaporanKegiatanSdm::query();

        // Filter Tahun
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        // Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori_kegiatan', $request->kategori);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nomor_info', function ($row) {
                $tgl = $row->tanggal_mulai ? $row->tanggal_mulai->format('d/m/Y') : '-';
                return '<div><span class="font-weight-bold text-dark">' . e($row->nomor_laporan) . '</span><br><small class="text-muted"><i class="far fa-calendar-alt mr-1"></i>' . $tgl . '</small></div>';
            })
            ->addColumn('kegiatan_info', function ($row) {
                $badgeKat = $row->kategori_kegiatan
                    ? '<span class="badge badge-light border text-primary ml-1" style="font-size:0.75rem;">' . e($row->kategori_kegiatan) . '</span>'
                    : '';
                return '<div><strong class="text-dark">' . e($row->nama_kegiatan) . '</strong>' . $badgeKat . '<br><small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>' . e($row->tempat_pelaksanaan ?? ($row->tipe_tempat == 'daring' ? 'Online' : '-')) . '</small></div>';
            })
            ->addColumn('peserta_info', function ($row) {
                return '<div class="text-center"><span class="badge badge-info px-2 py-1"><i class="fas fa-users mr-1"></i>' . $row->jumlah_peserta_hadir . ' / ' . $row->jumlah_peserta_rencana . '</span></div>';
            })
            ->addColumn('anggaran_info', function ($row) {
                $realisasi = 'Rp ' . number_format($row->total_realisasi, 0, ',', '.');
                $anggaran = 'Rp ' . number_format($row->total_anggaran, 0, ',', '.');
                return '<div class="text-right"><strong class="text-success">' . $realisasi . '</strong><br><small class="text-muted">Target: ' . $anggaran . '</small></div>';
            })
            ->addColumn('status_badge', function ($row) {
                return '<div class="text-center">' . $row->status_badge . '</div>';
            })
            ->addColumn('action', function ($row) {
                $showUrl = route('admin.laporan-kegiatan-sdm.show', $row->id);
                $editUrl = route('admin.laporan-kegiatan-sdm.edit', $row->id);
                $pdfUrl  = route('admin.laporan-kegiatan-sdm.cetak-pdf', $row->id);

                return '
                    <div class="text-center text-nowrap">
                        <a href="' . $showUrl . '" class="btn btn-sm btn-info mr-1" title="Lihat Detail Laporan">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . $pdfUrl . '" target="_blank" class="btn btn-sm btn-danger mr-1" title="Cetak Dokumen Resmi (PDF)">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-warning mr-1" title="Edit Laporan">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" data-nama="' . e($row->nama_kegiatan) . '" title="Hapus Laporan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['nomor_info', 'kegiatan_info', 'peserta_info', 'anggaran_info', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Form Tambah Laporan Baru (Wizard Multi-Tab)
     */
    public function create()
    {
        $nomorLaporan = LaporanKegiatanSdm::generateNomorLaporan();

        // Cari pegawai SDM (Afifah Raisya Putri Sanjaya, S.I.P.)
        $kasubbagSdm = DataDosenTendik::where(function ($q) {
            $q->where('posisi', 'like', '%Sumber Daya Manusia%')
              ->orWhere('nama', 'like', '%Afifah%');
        })->where('is_active', 1)->first();

        // Cari pimpinan / pimpinan unit / pimpinan SDM
        $direkturSdm = DataDosenTendik::where(function ($q) {
            $q->where('posisi', 'like', '%Direktur%')
              ->orWhere('posisi', 'like', '%Kepala%')
              ->orWhere('posisi', 'like', '%Ketua%');
        })->where('is_active', 1)->first();

        // Seluruh master pegawai aktif untuk autocomplete & modal
        $pegawaiList = DataDosenTendik::with('unit')
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get();

        $kategoriList = [
            'Pelatihan & Workshop',
            'Sosialisasi & Pembekalan',
            'Rapat Kerja & Koordinasi',
            'Rekrutmen & Seleksi',
            'Evaluasi & Monitoring',
            'Pengembangan Kompetensi',
            'Gathering & Keakraban',
            'Lainnya',
        ];

        return view('admin::laporan-kegiatan-sdm.create', [
            'title'        => 'Buat Laporan Kegiatan SDM Baru',
            'menu'         => 'laporan-kegiatan-sdm',
            'menuIcon'     => 'fas fa-file-invoice',
            'nomorLaporan' => $nomorLaporan,
            'direkturSdm'  => $direkturSdm,
            'kasubbagSdm'  => $kasubbagSdm,
            'pegawaiList'  => $pegawaiList,
            'kategoriList' => $kategoriList,
        ]);
    }

    /**
     * Simpan Laporan Kegiatan SDM
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_laporan'   => 'required|unique:laporan_kegiatan_sdms,nomor_laporan',
            'nama_kegiatan'   => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:draft,submitted,approved',
        ]);

        DB::beginTransaction();
        try {
            // Hitung Anggaran
            $anggaranList = $this->parseJsonArray($request->anggaran);
            $totalAnggaran = 0;
            $totalRealisasi = 0;
            foreach ($anggaranList as &$item) {
                $a = (float) str_replace(['.', ','], ['', '.'], $item['anggaran'] ?? 0);
                $r = (float) str_replace(['.', ','], ['', '.'], $item['realisasi'] ?? 0);
                $item['anggaran'] = $a;
                $item['realisasi'] = $r;
                $item['selisih'] = $a - $r;
                $totalAnggaran += $a;
                $totalRealisasi += $r;
            }
            unset($item);
            $totalSelisih = $totalAnggaran - $totalRealisasi;

            // Target & Capaian kalkulasi otomatis %
            $capaianList = $this->parseJsonArray($request->capaian_kegiatan);
            foreach ($capaianList as &$cap) {
                $t = (float) ($cap['target'] ?? 0);
                $r = (float) ($cap['realisasi'] ?? 0);
                if ($t > 0 && !isset($cap['capaian_persen'])) {
                    $cap['capaian_persen'] = round(($r / $t) * 100, 1);
                }
            }
            unset($cap);

            // Waktu Pelaksanaan (Bab E)
            $waktuPelaksanaan = $request->waktu_pelaksanaan;
            if ($request->filled('jam_mulai') && $request->filled('jam_selesai')) {
                $zona = $request->zona_waktu ?? 'WIB';
                $waktuPelaksanaan = "{$request->jam_mulai} - {$request->jam_selesai} {$zona}";
            } elseif ($request->filled('jam_mulai')) {
                $zona = $request->zona_waktu ?? 'WIB';
                $waktuPelaksanaan = "{$request->jam_mulai} {$zona}";
            }

            // Rundown (Bab I)
            $rundownList = $this->parseJsonArray($request->rundown);
            foreach ($rundownList as &$rd) {
                if (!empty($rd['jam_mulai']) && !empty($rd['jam_selesai'])) {
                    $rd['waktu'] = "{$rd['jam_mulai']} - {$rd['jam_selesai']} WIB";
                } elseif (!empty($rd['jam_mulai'])) {
                    $rd['waktu'] = "{$rd['jam_mulai']} WIB";
                }
            }
            unset($rd);

            // Data Laporan Utama
            $laporan = LaporanKegiatanSdm::create([
                'nomor_laporan'             => $request->nomor_laporan,
                'nama_kegiatan'             => $request->nama_kegiatan,
                'kategori_kegiatan'         => $request->kategori_kegiatan,
                'latar_belakang'            => $request->latar_belakang,
                'tujuan'                    => $this->parseJsonArray($request->tujuan),
                'dasar_pelaksanaan'         => $this->parseJsonArray($request->dasar_pelaksanaan),
                'tanggal_mulai'             => $request->tanggal_mulai,
                'tanggal_selesai'           => $request->tanggal_selesai ?: $request->tanggal_mulai,
                'waktu_pelaksanaan'         => $waktuPelaksanaan,
                'tipe_tempat'               => $request->tipe_tempat ?? 'luring',
                'tempat_pelaksanaan'        => $request->tempat_pelaksanaan,
                'link_daring'               => $request->link_daring,
                'keterangan_waktu_tempat'   => $request->keterangan_waktu_tempat,
                'jumlah_peserta_rencana'    => (int) $request->jumlah_peserta_rencana,
                'jumlah_peserta_hadir'      => (int) $request->jumlah_peserta_hadir,
                'jumlah_peserta_tidak_hadir'=> (int) $request->jumlah_peserta_tidak_hadir,
                'panitia'                   => $this->parseJsonArray($request->panitia),
                'narasumber'                => $this->parseJsonArray($request->narasumber),
                'rundown'                   => $rundownList,
                'target_kegiatan'           => $this->parseJsonArray($request->target_kegiatan),
                'capaian_kegiatan'          => $capaianList,
                'uraian_hasil'              => $request->uraian_hasil,
                'evaluasi'                  => $this->parseJsonArray($request->evaluasi),
                'catatan_evaluasi'          => $request->catatan_evaluasi,
                'tindak_lanjut'             => $this->parseJsonArray($request->tindak_lanjut),
                'anggaran'                  => $anggaranList,
                'total_anggaran'            => $totalAnggaran,
                'total_realisasi'           => $totalRealisasi,
                'total_selisih'             => $totalSelisih,
                'kesimpulan'                => $request->kesimpulan,
                'mengetahui_pejabat_id'     => $request->mengetahui_pejabat_id,
                'mengetahui_nama'           => $request->mengetahui_nama,
                'mengetahui_nip'            => $request->mengetahui_nip,
                'mengetahui_jabatan'        => $request->mengetahui_jabatan ?? 'Direktur Sumber Daya Manusia',
                'disusun_pejabat_id'        => $request->disusun_pejabat_id,
                'disusun_nama'              => $request->disusun_nama,
                'disusun_nip'               => $request->disusun_nip,
                'disusun_jabatan'           => $request->disusun_jabatan ?? 'PIC / Kepala Subbagian SDM',
                'tanggal_pengesahan'        => $request->tanggal_pengesahan ?: date('Y-m-d'),
                'status'                    => $request->status ?? 'draft',
                'created_by'                => Auth::id(),
            ]);

            // Simpan Daftar Peserta
            if ($request->filled('peserta')) {
                $pesertaData = $this->parseJsonArray($request->peserta);
                foreach ($pesertaData as $p) {
                    if (!empty($p['nama'])) {
                        LaporanKegiatanSdmPeserta::create([
                            'laporan_kegiatan_sdm_id' => $laporan->id,
                            'data_dosen_tendik_id'    => $p['data_dosen_tendik_id'] ?? null,
                            'nama'                    => $p['nama'],
                            'nip_nidn'                => $p['nip_nidn'] ?? null,
                            'unit'                    => $p['unit'] ?? null,
                            'jabatan'                 => $p['jabatan'] ?? null,
                            'kehadiran'               => $p['kehadiran'] ?? 'Hadir',
                        ]);
                    }
                }
            }

            // Upload Foto Dokumentasi (Bab N)
            if ($request->hasFile('foto_dokumentasi')) {
                $keteranganFoto = $request->keterangan_foto ?? [];
                foreach ($request->file('foto_dokumentasi') as $idx => $file) {
                    if ($file->isValid()) {
                        $filename = 'foto_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('public/laporan_kegiatan_sdm/foto', $filename);
                        LaporanKegiatanSdmDokumen::create([
                            'laporan_kegiatan_sdm_id' => $laporan->id,
                            'kategori'                => 'foto_dokumentasi',
                            'file_path'               => $path,
                            'nama_file'               => $file->getClientOriginalName(),
                            'keterangan'              => $keteranganFoto[$idx] ?? 'Dokumentasi Pelaksanaan Kegiatan',
                        ]);
                    }
                }
            }

            // Upload Berkas Lampiran Pendukung (Lampiran 1 s.d. 6)
            if ($request->hasFile('berkas_lampiran')) {
                $kategoriLampiran = $request->kategori_lampiran ?? [];
                $keteranganLampiran = $request->keterangan_lampiran ?? [];
                foreach ($request->file('berkas_lampiran') as $idx => $file) {
                    if ($file->isValid()) {
                        $filename = 'lampiran_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('public/laporan_kegiatan_sdm/lampiran', $filename);
                        LaporanKegiatanSdmDokumen::create([
                            'laporan_kegiatan_sdm_id' => $laporan->id,
                            'kategori'                => $kategoriLampiran[$idx] ?? 'lampiran_lainnya',
                            'file_path'               => $path,
                            'nama_file'               => $file->getClientOriginalName(),
                            'keterangan'              => $keteranganLampiran[$idx] ?? 'Lampiran Dokumen Kegiatan',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.laporan-kegiatan-sdm.show', $laporan->id)
                ->with('success', 'Laporan Kegiatan SDM berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan laporan kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Detail Tinjauan Laporan Kegiatan SDM
     */
    public function show($id)
    {
        $laporan = LaporanKegiatanSdm::with(['pesertas', 'dokumens', 'mengetahuiPejabat', 'disusunPejabat'])
            ->findOrFail($id);

        return view('admin::laporan-kegiatan-sdm.show', [
            'title'    => 'Detail Laporan Kegiatan SDM',
            'menu'     => 'laporan-kegiatan-sdm',
            'menuIcon' => 'fas fa-file-invoice',
            'laporan'  => $laporan,
        ]);
    }

    /**
     * Form Edit Laporan Kegiatan SDM
     */
    public function edit($id)
    {
        $laporan = LaporanKegiatanSdm::with(['pesertas', 'dokumens', 'mengetahuiPejabat', 'disusunPejabat'])
            ->findOrFail($id);

        $kategoriList = [
            'Pelatihan & Workshop',
            'Sosialisasi & Pembekalan',
            'Rapat Kerja & Koordinasi',
            'Rekrutmen & Seleksi',
            'Evaluasi & Monitoring',
            'Pengembangan Kompetensi',
            'Gathering & Keakraban',
            'Lainnya',
        ];

        $pegawaiList = DataDosenTendik::with('unit')
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get();

        return view('admin::laporan-kegiatan-sdm.edit', [
            'title'        => 'Edit Laporan Kegiatan SDM',
            'menu'         => 'laporan-kegiatan-sdm',
            'menuIcon'     => 'fas fa-file-invoice',
            'laporan'      => $laporan,
            'pegawaiList'  => $pegawaiList,
            'kategoriList' => $kategoriList,
        ]);
    }

    /**
     * Update Laporan Kegiatan SDM
     */
    public function update(Request $request, $id)
    {
        $laporan = LaporanKegiatanSdm::findOrFail($id);

        $request->validate([
            'nomor_laporan'   => 'required|unique:laporan_kegiatan_sdms,nomor_laporan,' . $id,
            'nama_kegiatan'   => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'required|in:draft,submitted,approved',
        ]);

        DB::beginTransaction();
        try {
            // Hitung Anggaran
            $anggaranList = $this->parseJsonArray($request->anggaran);
            $totalAnggaran = 0;
            $totalRealisasi = 0;
            foreach ($anggaranList as &$item) {
                $a = (float) str_replace(['.', ','], ['', '.'], $item['anggaran'] ?? 0);
                $r = (float) str_replace(['.', ','], ['', '.'], $item['realisasi'] ?? 0);
                $item['anggaran'] = $a;
                $item['realisasi'] = $r;
                $item['selisih'] = $a - $r;
                $totalAnggaran += $a;
                $totalRealisasi += $r;
            }
            unset($item);
            $totalSelisih = $totalAnggaran - $totalRealisasi;

            // Target & Capaian
            $capaianList = $this->parseJsonArray($request->capaian_kegiatan);
            foreach ($capaianList as &$cap) {
                $t = (float) ($cap['target'] ?? 0);
                $r = (float) ($cap['realisasi'] ?? 0);
                if ($t > 0 && !isset($cap['capaian_persen'])) {
                    $cap['capaian_persen'] = round(($r / $t) * 100, 1);
                }
            }
            unset($cap);

            // Waktu Pelaksanaan (Bab E)
            $waktuPelaksanaan = $request->waktu_pelaksanaan;
            if ($request->filled('jam_mulai') && $request->filled('jam_selesai')) {
                $zona = $request->zona_waktu ?? 'WIB';
                $waktuPelaksanaan = "{$request->jam_mulai} - {$request->jam_selesai} {$zona}";
            } elseif ($request->filled('jam_mulai')) {
                $zona = $request->zona_waktu ?? 'WIB';
                $waktuPelaksanaan = "{$request->jam_mulai} {$zona}";
            }

            // Rundown (Bab I)
            $rundownList = $this->parseJsonArray($request->rundown);
            foreach ($rundownList as &$rd) {
                if (!empty($rd['jam_mulai']) && !empty($rd['jam_selesai'])) {
                    $rd['waktu'] = "{$rd['jam_mulai']} - {$rd['jam_selesai']} WIB";
                } elseif (!empty($rd['jam_mulai'])) {
                    $rd['waktu'] = "{$rd['jam_mulai']} WIB";
                }
            }
            unset($rd);

            // Update Data Utama
            $laporan->update([
                'nomor_laporan'             => $request->nomor_laporan,
                'nama_kegiatan'             => $request->nama_kegiatan,
                'kategori_kegiatan'         => $request->kategori_kegiatan,
                'latar_belakang'            => $request->latar_belakang,
                'tujuan'                    => $this->parseJsonArray($request->tujuan),
                'dasar_pelaksanaan'         => $this->parseJsonArray($request->dasar_pelaksanaan),
                'tanggal_mulai'             => $request->tanggal_mulai,
                'tanggal_selesai'           => $request->tanggal_selesai ?: $request->tanggal_mulai,
                'waktu_pelaksanaan'         => $waktuPelaksanaan,
                'tipe_tempat'               => $request->tipe_tempat ?? 'luring',
                'tempat_pelaksanaan'        => $request->tempat_pelaksanaan,
                'link_daring'               => $request->link_daring,
                'keterangan_waktu_tempat'   => $request->keterangan_waktu_tempat,
                'jumlah_peserta_rencana'    => (int) $request->jumlah_peserta_rencana,
                'jumlah_peserta_hadir'      => (int) $request->jumlah_peserta_hadir,
                'jumlah_peserta_tidak_hadir'=> (int) $request->jumlah_peserta_tidak_hadir,
                'panitia'                   => $this->parseJsonArray($request->panitia),
                'narasumber'                => $this->parseJsonArray($request->narasumber),
                'rundown'                   => $rundownList,
                'target_kegiatan'           => $this->parseJsonArray($request->target_kegiatan),
                'capaian_kegiatan'          => $capaianList,
                'uraian_hasil'              => $request->uraian_hasil,
                'evaluasi'                  => $this->parseJsonArray($request->evaluasi),
                'catatan_evaluasi'          => $request->catatan_evaluasi,
                'tindak_lanjut'             => $this->parseJsonArray($request->tindak_lanjut),
                'anggaran'                  => $anggaranList,
                'total_anggaran'            => $totalAnggaran,
                'total_realisasi'           => $totalRealisasi,
                'total_selisih'             => $totalSelisih,
                'kesimpulan'                => $request->kesimpulan,
                'mengetahui_pejabat_id'     => $request->mengetahui_pejabat_id,
                'mengetahui_nama'           => $request->mengetahui_nama,
                'mengetahui_nip'            => $request->mengetahui_nip,
                'mengetahui_jabatan'        => $request->mengetahui_jabatan ?? 'Direktur Sumber Daya Manusia',
                'disusun_pejabat_id'        => $request->disusun_pejabat_id,
                'disusun_nama'              => $request->disusun_nama,
                'disusun_nip'               => $request->disusun_nip,
                'disusun_jabatan'           => $request->disusun_jabatan ?? 'PIC / Kepala Subbagian SDM',
                'tanggal_pengesahan'        => $request->tanggal_pengesahan ?: date('Y-m-d'),
                'status'                    => $request->status ?? 'draft',
            ]);

            // Sync Peserta (Hapus lama, isi baru)
            if ($request->has('peserta')) {
                $laporan->pesertas()->delete();
                $pesertaData = $this->parseJsonArray($request->peserta);
                foreach ($pesertaData as $p) {
                    if (!empty($p['nama'])) {
                        LaporanKegiatanSdmPeserta::create([
                            'laporan_kegiatan_sdm_id' => $laporan->id,
                            'data_dosen_tendik_id'    => $p['data_dosen_tendik_id'] ?? null,
                            'nama'                    => $p['nama'],
                            'nip_nidn'                => $p['nip_nidn'] ?? null,
                            'unit'                    => $p['unit'] ?? null,
                            'jabatan'                 => $p['jabatan'] ?? null,
                            'kehadiran'               => $p['kehadiran'] ?? 'Hadir',
                        ]);
                    }
                }
            }

            // Upload Tambahan Foto Dokumentasi
            if ($request->hasFile('foto_dokumentasi')) {
                $keteranganFoto = $request->keterangan_foto ?? [];
                foreach ($request->file('foto_dokumentasi') as $idx => $file) {
                    if ($file->isValid()) {
                        $filename = 'foto_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('public/laporan_kegiatan_sdm/foto', $filename);
                        LaporanKegiatanSdmDokumen::create([
                            'laporan_kegiatan_sdm_id' => $laporan->id,
                            'kategori'                => 'foto_dokumentasi',
                            'file_path'               => $path,
                            'nama_file'               => $file->getClientOriginalName(),
                            'keterangan'              => $keteranganFoto[$idx] ?? 'Dokumentasi Pelaksanaan Kegiatan',
                        ]);
                    }
                }
            }

            // Upload Tambahan Berkas Lampiran
            if ($request->hasFile('berkas_lampiran')) {
                $kategoriLampiran = $request->kategori_lampiran ?? [];
                $keteranganLampiran = $request->keterangan_lampiran ?? [];
                foreach ($request->file('berkas_lampiran') as $idx => $file) {
                    if ($file->isValid()) {
                        $filename = 'lampiran_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('public/laporan_kegiatan_sdm/lampiran', $filename);
                        LaporanKegiatanSdmDokumen::create([
                            'laporan_kegiatan_sdm_id' => $laporan->id,
                            'kategori'                => $kategoriLampiran[$idx] ?? 'lampiran_lainnya',
                            'file_path'               => $path,
                            'nama_file'               => $file->getClientOriginalName(),
                            'keterangan'              => $keteranganLampiran[$idx] ?? 'Lampiran Dokumen Kegiatan',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.laporan-kegiatan-sdm.show', $laporan->id)
                ->with('success', 'Laporan Kegiatan SDM berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui laporan kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Laporan Kegiatan SDM (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $laporan = LaporanKegiatanSdm::findOrFail($id);
            $laporan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laporan kegiatan SDM berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus Dokumen / Foto Tertentu
     */
    public function destroyDokumen($dokumenId)
    {
        try {
            $dokumen = LaporanKegiatanSdmDokumen::findOrFail($dokumenId);
            if (Storage::exists($dokumen->file_path)) {
                Storage::delete($dokumen->file_path);
            }
            $dokumen->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX Autocomplete Data Dosen & Tendik
     */
    public function ajaxPegawai(Request $request)
    {
        $q = trim($request->get('q', ''));

        $pegawai = DataDosenTendik::with('unit')
            ->where('is_active', 1)
            ->when($q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%")
                        ->orWhere('nidn', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%");
                });
            })
            ->limit(25)
            ->get();

        $results = $pegawai->map(function ($item) {
            $nip = $item->nip ?: ($item->nidn ?: $item->nik);
            $unit = $item->unit ? $item->unit->nama_unit : '-';
            return [
                'id'       => $item->id,
                'text'     => "{$item->nama} (" . ($nip ?: '-') . ") - {$unit}",
                'nama'     => $item->nama,
                'nip'      => $nip,
                'unit'     => $unit,
                'jabatan'  => $item->posisi ?: ($item->tipe_karyawan ?: '-'),
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Cetak PDF Dokumen Resmi Laporan Kegiatan SDM
     */
    public function cetakPdf($id)
    {
        $laporan = LaporanKegiatanSdm::with(['pesertas', 'dokumens', 'mengetahuiPejabat', 'disusunPejabat'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('admin::laporan-kegiatan-sdm.cetak_pdf', compact('laporan'))
            ->setPaper('A4', 'portrait');

        $cleanNomor = preg_replace('/[^A-Za-z0-9_\-]/', '_', $laporan->nomor_laporan);
        $fileName = 'LAPORAN_KEGIATAN_SDM_' . $cleanNomor . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Helper Parse JSON array from request
     */
    private function parseJsonArray($input): array
    {
        if (is_array($input)) {
            return array_values($input);
        }
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            return is_array($decoded) ? array_values($decoded) : [];
        }
        return [];
    }
}
