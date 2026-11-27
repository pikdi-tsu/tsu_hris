<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\AuditSpiTemuan;
use App\Models\AuditLpmTemuan;
use App\Models\MasterUnit;
use App\Models\DataDosenTendik;
use App\Models\MasterPeriodeSurvey;
use App\Models\MasterAspekSurvey;
use App\Models\SurveyLayananResponse;
use App\Models\SurveyPelatihanResponse;
use App\Models\TrainingPeserta;
use App\Services\TrainingSyncRoadmapService;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SurveyController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:survey');
    }

    /**
     * ========================================================
     * 1.a. SURVEY KEPUASAN LAYANAN SDM: KELOLA PERIODE (ADMIN HR)
     * ========================================================
     */
    public function periodeIndex()
    {
        $this->guard('view', 'admin:survey');

        $periodes = MasterPeriodeSurvey::withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin::survey.periode_index', [
            'title' => 'Survey Kepuasan Layanan SDM',
            'periodes' => $periodes,
        ]);
    }

    /**
     * Simpan Periode Baru Survey Kepuasan Layanan SDM
     */
    public function periodeStore(Request $request)
    {
        $this->guard('create', 'admin:survey');

        $request->validate([
            'nama_periode' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
            'tahun_ajaran' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $creator = $user ? ($user->name ?? $user->email) : 'Admin';

            // Jika periode baru ini aktif, nonaktifkan periode lain jika diinginkan, atau biarkan multirange
            MasterPeriodeSurvey::create([
                'nama_periode' => $request->nama_periode,
                'semester' => $request->semester,
                'tahun_ajaran' => $request->tahun_ajaran,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_active' => true,
                'keterangan' => $request->keterangan,
                'created_by' => $creator,
            ]);

            return back()->with('success', 'Periode Survei Kepuasan Layanan SDM berhasil dibuka!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuka periode survei: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Status Aktif Periode
     */
    public function periodeToggle($id)
    {
        $this->guard('edit', 'admin:survey');

        try {
            $periode = MasterPeriodeSurvey::findOrFail($id);
            $periode->is_active = !$periode->is_active;
            $periode->save();

            return back()->with('success', 'Status periode survei berhasil diubah.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Detail Hasil & Respon Karyawan pada Suatu Periode Survei Layanan
     */
    public function periodeShow($id, Request $request)
    {
        $this->guard('view', 'admin:survey');

        $periode = MasterPeriodeSurvey::withCount('responses')->findOrFail($id);
        $aspekList = MasterAspekSurvey::layananSdm()->active()->ordered()->get();

        $query = SurveyLayananResponse::with(['karyawan.unit'])
            ->where('periode_id', $id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%");
            });
        }

        $responses = $query->paginate(20)->withQueryString();

        // Hitung statistik rata-rata kepuasan
        $allResponses = SurveyLayananResponse::where('periode_id', $id)->get();
        $totalCount = $allResponses->count();

        // Statistik dinamis per aspek
        $aspekStats = [];
        $totalSumAll = 0;
        $totalScoreCount = 0;

        foreach ($aspekList as $aspek) {
            $scores = [];
            foreach ($allResponses as $r) {
                $val = null;
                if (!empty($r->jawaban_detail) && is_array($r->jawaban_detail)) {
                    foreach ($r->jawaban_detail as $jd) {
                        if (($jd['aspek_id'] ?? null) == $aspek->id || ($jd['kode'] ?? null) == $aspek->kode) {
                            $val = (float)($jd['skor'] ?? 0);
                            break;
                        }
                    }
                }
                if ($val === null) {
                    $col = 'skor_' . $aspek->kode;
                    if (isset($r->{$col})) {
                        $val = (float)$r->{$col};
                    }
                }
                if ($val !== null && $val > 0) {
                    $scores[] = $val;
                    $totalSumAll += $val;
                    $totalScoreCount++;
                }
            }
            $aspekStats[$aspek->id] = [
                'aspek' => $aspek,
                'avg'   => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0,
                'count' => count($scores),
            ];
        }

        $avgTotal = $totalScoreCount > 0 ? round($totalSumAll / $totalScoreCount, 2) : 0;
        if ($avgTotal == 0 && $totalCount > 0) {
            $avgTotal = round($allResponses->avg(function($r) {
                return ($r->skor_responsiveness + $r->skor_keramahan + $r->skor_kecepatan + $r->skor_fasilitas) / 4;
            }), 2);
        }

        $stats = [
            'total'              => $totalCount,
            'avg_total'          => $avgTotal,
            'avg_responsiveness' => $totalCount > 0 ? round($allResponses->avg('skor_responsiveness'), 2) : 0,
            'avg_keramahan'      => $totalCount > 0 ? round($allResponses->avg('skor_keramahan'), 2) : 0,
            'avg_kecepatan'      => $totalCount > 0 ? round($allResponses->avg('skor_kecepatan'), 2) : 0,
            'avg_fasilitas'      => $totalCount > 0 ? round($allResponses->avg('skor_fasilitas'), 2) : 0,
            'aspek_stats'        => $aspekStats,
        ];

        return view('admin::survey.periode_show', [
            'title'      => 'Detail Respon Survei: ' . $periode->nama_periode,
            'periode'    => $periode,
            'responses'  => $responses,
            'stats'      => $stats,
            'aspekList'  => $aspekList,
            'search'     => $request->search ?? '',
        ]);
    }

    /**
     * Submit Kuesioner Kepuasan Layanan SDM (dari Modal Pop-Up di Dashboard)
     */
    public function submitLayanan(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|uuid|exists:master_periode_surveys,id',
            'kritik_saran' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Temukan profil pegawai
            $karyawan = $this->getKaryawanProfile($user);

            if (!$karyawan) {
                return response()->json(['success' => false, 'message' => 'Profil pegawai tidak ditemukan.'], 404);
            }

            $aspekList = MasterAspekSurvey::layananSdm()->active()->ordered()->get();
            $jawabanDetail = [];
            $fallbackScores = [
                'skor_responsiveness' => (int)$request->input('skor_responsiveness', 5),
                'skor_keramahan'      => (int)$request->input('skor_keramahan', 5),
                'skor_kecepatan'      => (int)$request->input('skor_kecepatan', 5),
                'skor_fasilitas'      => (int)$request->input('skor_fasilitas', 5),
            ];

            foreach ($aspekList as $asp) {
                $val = $request->input('aspek_' . $asp->id, $request->input('skor_' . $asp->kode, $request->input($asp->kode, 5)));
                $val = max(1, min(5, (int)$val));
                $jawabanDetail[] = [
                    'aspek_id'   => $asp->id,
                    'kode'       => $asp->kode,
                    'nama_aspek' => $asp->nama_aspek,
                    'skor'       => $val,
                ];

                if (array_key_exists('skor_' . $asp->kode, $fallbackScores)) {
                    $fallbackScores['skor_' . $asp->kode] = $val;
                }
            }

            $saveData = array_merge($fallbackScores, [
                'user_id'        => $user->id,
                'jawaban_detail' => $jawabanDetail,
                'kritik_saran'   => $request->kritik_saran,
            ]);

            // Simpan respon atau update jika sudah ada
            SurveyLayananResponse::updateOrCreate(
                [
                    'periode_id'  => $request->periode_id,
                    'karyawan_id' => $karyawan->id,
                ],
                $saveData
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Terima kasih! Survei Kepuasan Layanan SDM Anda telah berhasil disimpan.',
                ]);
            }

            return back()->with('success', 'Terima kasih atas partisipasi Anda dalam survei kepuasan layanan SDM.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menyimpan respon: ' . $e->getMessage());
        }
    }

    /**
     * ========================================================
     * 1.b. SURVEY KEPUASAN PELATIHAN & UPLOAD SERTIFIKAT
     * ========================================================
     */

    /**
     * Daftar Pelatihan Saya yang perlu/telah diisi survei kepuasannya
    /**
     * Daftar Pelatihan Saya yang perlu/telah diisi survei kepuasannya (Split Layout Mockup 11)
     */
    public function pelatihanIndex(Request $request)
    {
        $user = Auth::user();
        $karyawan = $this->getKaryawanProfile($user);

        // Hanya tampilkan pelatihan yang diikuti oleh pegawai yang sedang login
        $karyawanId = $karyawan ? $karyawan->id : null;

        $pesertaList = TrainingPeserta::with(['training', 'surveyResponse', 'karyawan'])
            ->where('karyawan_id', $karyawanId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPelatihan = $pesertaList->count();
        $telahDisurvei = $pesertaList->where('is_survey_filled', true)->count();
        $belumDisurvei = $totalPelatihan - $telahDisurvei;
        $persenTelah = $totalPelatihan > 0 ? round(($telahDisurvei / $totalPelatihan) * 100) : 0;
        $persenBelum = $totalPelatihan > 0 ? round(($belumDisurvei / $totalPelatihan) * 100) : 0;

        // Ambil pelatihan aktif terpilih untuk panel detail (kanan)
        $selectedId = $request->input('selected_id', optional($pesertaList->first())->id);
        $selectedPeserta = $pesertaList->firstWhere('id', $selectedId) ?? $pesertaList->first();

        $aspekList = MasterAspekSurvey::training()->active()->ordered()->get();

        return view('admin::survey.pelatihan_index', [
            'title'            => 'Survey Kepuasan Training',
            'pesertaList'      => $pesertaList,
            'selectedPeserta'  => $selectedPeserta,
            'karyawan'         => $karyawan,
            'aspekList'        => $aspekList,
            'totalPelatihan'   => $totalPelatihan,
            'telahDisurvei'    => $telahDisurvei,
            'belumDisurvei'    => $belumDisurvei,
            'persenTelah'      => $persenTelah,
            'persenBelum'      => $persenBelum,
        ]);
    }

    /**
     * Form Pengisian Survey Pelatihan + Upload Sertifikat (1x Input)
     */
    public function pelatihanForm($pesertaId)
    {
        $peserta = TrainingPeserta::with(['training', 'karyawan', 'surveyResponse'])->findOrFail($pesertaId);
        $aspekList = MasterAspekSurvey::training()->active()->ordered()->get();

        return view('admin::survey.form_pelatihan', [
            'title'     => 'Survey & Unggah Sertifikat: ' . $peserta->training->nama_training,
            'peserta'   => $peserta,
            'training'  => $peserta->training,
            'aspekList' => $aspekList,
        ]);
    }

    /**
     * Proses Simpan Survey Pelatihan + File Sertifikat + Auto-Sync ke Roadmap (Mendukung Aspek Dinamis & Draft)
     */
    public function pelatihanSubmit(Request $request, $pesertaId)
    {
        $peserta = TrainingPeserta::with(['training', 'karyawan'])->findOrFail($pesertaId);

        $isDraft = $request->input('action_type') === 'draft';

        if (!$isDraft) {
            $request->validate([
                'saran_masukan'    => 'nullable|string|max:1000',
                'bukti_file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ]);
        }

        DB::beginTransaction();
        try {
            // Upload file bukti jika ada
            $buktiPath = optional($peserta->surveyResponse)->bukti_file;
            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $ext = $file->getClientOriginalExtension();
                $cleanName = Str::slug($peserta->training->nama_training ?? 'training');
                $filename = 'survey_' . $cleanName . '_' . time() . '.' . $ext;
                $file->storeAs('public/survey_training', $filename);
                $buktiPath = 'storage/survey_training/' . $filename;
            }

            $aspekList = MasterAspekSurvey::training()->active()->ordered()->get();
            $jawabanDetail = [];
            $fallbackScores = [
                'skor_relevansi'   => (int)$request->input('skor_relevansi', 5),
                'skor_narasumber'  => (int)$request->input('skor_narasumber', 5),
                'skor_metode'      => (int)$request->input('skor_metode', 5),
                'skor_fasilitas'   => (int)$request->input('skor_fasilitas', 5),
                'skor_manfaat'     => (int)$request->input('skor_manfaat', 5),
            ];

            foreach ($aspekList as $asp) {
                $val = $request->input('aspek_' . $asp->id, $request->input('skor_' . $asp->kode, $request->input($asp->kode, 5)));
                $val = max(1, min(5, (int)$val));
                $jawabanDetail[] = [
                    'aspek_id'   => $asp->id,
                    'kode'       => $asp->kode,
                    'nama_aspek' => $asp->nama_aspek,
                    'skor'       => $val,
                ];

                if (array_key_exists('skor_' . $asp->kode, $fallbackScores)) {
                    $fallbackScores['skor_' . $asp->kode] = $val;
                }
            }

            // 1. Simpan Respon Survey Pelatihan
            $response = SurveyPelatihanResponse::updateOrCreate(
                [
                    'training_peserta_id' => $peserta->id,
                ],
                array_merge($fallbackScores, [
                    'jawaban_detail'   => $jawabanDetail,
                    'feedback_manfaat' => $request->input('saran_masukan', $request->input('feedback_manfaat')),
                    'saran_masukan'    => $request->input('saran_masukan', $request->input('feedback_manfaat')),
                    'bukti_file'       => $buktiPath,
                    'is_draft'         => $isDraft,
                ])
            );

            // 2. Update status TrainingPeserta
            $peserta->update([
                'is_survey_filled'        => !$isDraft,
                'survey_filled_at'        => !$isDraft ? now() : null,
                'sertifikat_file'         => $buktiPath ?: $peserta->sertifikat_file,
                'sertifikat_uploaded_at'  => $buktiPath ? now() : $peserta->sertifikat_uploaded_at,
            ]);

            // 3. Otomatis sinkronisasi ke Roadmap SDM jika bukan draft
            if (!$isDraft) {
                TrainingSyncRoadmapService::syncPesertaToRoadmap($peserta);
            }

            DB::commit();

            $msg = $isDraft
                ? 'Draft survei kepuasan pelatihan berhasil disimpan sementara.'
                : 'Survei kepuasan pelatihan berhasil dikirim dan tersinkronisasi ke Roadmap SDM Anda!';

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('admin.survey.pelatihan.index', ['selected_id' => $peserta->id])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Gagal menyimpan survei: ' . $e->getMessage());
        }
    }

    /**
     * ========================================================
     * 1.c. HASIL & ANALISIS SURVEI (TAMPILAN RESMI RENSTRA STYLE)
     * ========================================================
     */
    public function hasilIndex()
    {
        $this->guard('view', 'admin:survey');

        return view('admin::survey.hasil_analisis', [
            'title' => 'Hasil & Analisis Survei',
        ]);
    }

    /**
     * ========================================================
     * 1.d. AUDIT SDM (AUDIT SPI & AUDIT LPM - RENSTRA STYLE)
     * ========================================================
     */
    public function auditSpi(Request $request)
    {
        $this->guard('view', 'admin:survey');

        $query = AuditSpiTemuan::with('unit');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('temuan', 'like', "%{$s}%")
                  ->orWhere('kode_temuan', 'like', "%{$s}%")
                  ->orWhere('area_audit', 'like', "%{$s}%")
                  ->orWhere('kategori', 'like', "%{$s}%")
                  ->orWhere('pic', 'like', "%{$s}%");
            });
        }
        if ($request->filled('unit') && $request->unit !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('unit_nama', $request->unit)
                  ->orWhere('unit_id', $request->unit);
            });
        }
        if ($request->filled('area_audit') && $request->area_audit !== 'all') {
            $query->where('area_audit', $request->area_audit);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $allTemuan = AuditSpiTemuan::all();
        $totalTemuan = $allTemuan->count();
        $terbuka = $allTemuan->where('status', 'Terbuka')->count();
        $dalamProses = $allTemuan->where('status', 'Dalam Proses')->count();
        $selesai = $allTemuan->where('status', 'Selesai')->count();
        $overdue = $allTemuan->where('status', 'Overdue')->count();

        $persenTerbuka = $totalTemuan > 0 ? round(($terbuka / $totalTemuan) * 100, 1) : 0;
        $persenDalamProses = $totalTemuan > 0 ? round(($dalamProses / $totalTemuan) * 100, 1) : 0;
        $persenSelesai = $totalTemuan > 0 ? round(($selesai / $totalTemuan) * 100, 1) : 0;
        $persenOverdue = $totalTemuan > 0 ? round(($overdue / $totalTemuan) * 100, 1) : 0;

        $temuans = $query->orderBy('tanggal_audit', 'desc')->get();

        // Selected temuan for right panel
        $selectedId = $request->input('selected_id', optional($temuans->first())->id);
        $selectedTemuan = $temuans->firstWhere('id', $selectedId) ?? $temuans->first();

        $units = MasterUnit::orderBy('nama_unit')->get();
        $areaAudits = AuditSpiTemuan::distinct()->pluck('area_audit')->filter();

        return view('admin::survey.audit_spi', [
            'title'             => 'Audit SPI (Satuan Pengawas Internal)',
            'temuans'           => $temuans,
            'selectedTemuan'    => $selectedTemuan,
            'units'             => $units,
            'areaAudits'        => $areaAudits,
            'totalTemuan'       => $totalTemuan,
            'terbuka'           => $terbuka,
            'dalamProses'       => $dalamProses,
            'selesai'           => $selesai,
            'overdue'           => $overdue,
            'persenTerbuka'     => $persenTerbuka,
            'persenDalamProses' => $persenDalamProses,
            'persenSelesai'     => $persenSelesai,
            'persenOverdue'     => $persenOverdue,
            'filters'           => $request->only(['search', 'unit', 'area_audit', 'status']),
        ]);
    }

    public function auditSpiStore(Request $request)
    {
        $this->guard('create', 'admin:survey');

        $request->validate([
            'unit_nama'      => 'required|string|max:100',
            'tanggal_audit'  => 'required|date',
            'area_audit'     => 'required|string|max:150',
            'temuan'         => 'required|string',
            'kategori'       => 'nullable|string|max:100',
            'tingkat_risiko' => 'required|in:Tinggi,Sedang,Rendah',
            'rekomendasi'    => 'nullable|string',
            'pic'            => 'nullable|string|max:100',
            'deadline'       => 'nullable|date',
            'status'         => 'required|in:Terbuka,Dalam Proses,Selesai,Overdue',
        ]);

        $count = AuditSpiTemuan::count() + 1;
        $kode = 'SPI-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $unit = MasterUnit::where('nama_unit', 'like', "%{$request->unit_nama}%")->first();

        AuditSpiTemuan::create([
            'kode_temuan'    => $kode,
            'unit_id'        => $unit ? $unit->id : null,
            'unit_nama'      => $request->unit_nama,
            'tanggal_audit'  => $request->tanggal_audit,
            'area_audit'     => $request->area_audit,
            'temuan'         => $request->temuan,
            'kategori'       => $request->kategori,
            'tingkat_risiko' => $request->tingkat_risiko,
            'rekomendasi'    => $request->rekomendasi,
            'pic'            => $request->pic,
            'deadline'       => $request->deadline,
            'status'         => $request->status,
            'riwayat_status' => [
                ['tanggal' => date('d M Y'), 'status' => 'Temuan dibuat'],
            ],
        ]);

        return back()->with('success', "Temuan audit SPI baru [{$kode}] berhasil ditambahkan!");
    }

    public function auditSpiUpdate(Request $request, $id)
    {
        $this->guard('edit', 'admin:survey');
        $item = AuditSpiTemuan::findOrFail($id);

        $data = $request->only([
            'unit_nama', 'tanggal_audit', 'area_audit', 'temuan', 'kategori',
            'tingkat_risiko', 'rekomendasi', 'pic', 'deadline', 'status',
            'catatan_tindak_lanjut'
        ]);

        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $filename = 'spi_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('audit_bukti', $filename, 'public');
            $data['bukti_tindak_lanjut'] = 'storage/' . $path;
        }

        if ($request->filled('status') && $request->status !== $item->status) {
            $riwayat = is_array($item->riwayat_status) ? $item->riwayat_status : [];
            $riwayat[] = [
                'tanggal' => date('d M Y'),
                'status'  => 'Status diubah ke ' . $request->status . ($request->catatan_tindak_lanjut ? ' (' . Str::limit($request->catatan_tindak_lanjut, 50) . ')' : '')
            ];
            $data['riwayat_status'] = $riwayat;
        }

        $item->update($data);

        return back()->with('success', "Temuan audit SPI [{$item->kode_temuan}] berhasil diperbarui.");
    }

    public function auditSpiDestroy($id)
    {
        $this->guard('delete', 'admin:survey');
        $item = AuditSpiTemuan::findOrFail($id);
        $item->delete();

        return back()->with('success', "Temuan audit SPI [{$item->kode_temuan}] berhasil dihapus.");
    }

    public function auditLpm(Request $request)
    {
        $this->guard('view', 'admin:survey');

        $query = AuditLpmTemuan::with('unit');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('temuan', 'like', "%{$s}%")
                  ->orWhere('kode_temuan', 'like', "%{$s}%")
                  ->orWhere('standar_mutu', 'like', "%{$s}%")
                  ->orWhere('aspek', 'like', "%{$s}%")
                  ->orWhere('indikator', 'like', "%{$s}%")
                  ->orWhere('pic', 'like', "%{$s}%");
            });
        }
        if ($request->filled('unit') && $request->unit !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('unit_nama', $request->unit)
                  ->orWhere('unit_id', $request->unit);
            });
        }
        if ($request->filled('standar') && $request->standar !== 'all') {
            $query->where('standar_mutu', $request->standar);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $allTemuan = AuditLpmTemuan::all();
        $totalTemuan = $allTemuan->count();
        $totalAudit = 10;
        $standarTerpenuhi = 7;
        $persenTerpenuhi = 70;
        $belumDitindaklanjuti = $allTemuan->whereIn('status', ['Terbuka', 'Overdue'])->count();
        $selesai = $allTemuan->where('status', 'Selesai')->count();
        $persenPenyelesaian = $totalTemuan > 0 ? round(($selesai / $totalTemuan) * 100) : 60;

        $temuans = $query->orderBy('tanggal_audit', 'desc')->get();

        $selectedId = $request->input('selected_id', optional($temuans->first())->id);
        $selectedTemuan = $temuans->firstWhere('id', $selectedId) ?? $temuans->first();

        $units = MasterUnit::orderBy('nama_unit')->get();
        $standarList = AuditLpmTemuan::distinct()->pluck('standar_mutu')->filter();

        return view('admin::survey.audit_lpm', [
            'title'                 => 'Audit LPM (Lembaga Penjaminan Mutu)',
            'temuans'               => $temuans,
            'selectedTemuan'        => $selectedTemuan,
            'units'                 => $units,
            'standarList'           => $standarList,
            'totalAudit'            => $totalAudit,
            'standarTerpenuhi'      => $standarTerpenuhi,
            'persenTerpenuhi'       => $persenTerpenuhi,
            'totalTemuan'           => $totalTemuan,
            'belumDitindaklanjuti'  => $belumDitindaklanjuti,
            'persenPenyelesaian'    => $persenPenyelesaian,
            'filters'               => $request->only(['search', 'unit', 'standar', 'status']),
        ]);
    }

    public function auditLpmStore(Request $request)
    {
        $this->guard('create', 'admin:survey');

        $request->validate([
            'unit_nama'      => 'required|string|max:100',
            'tanggal_audit'  => 'required|date',
            'standar_mutu'   => 'required|string|max:150',
            'aspek'          => 'nullable|string|max:150',
            'indikator'      => 'nullable|string|max:150',
            'target_standar' => 'nullable|string|max:100',
            'kondisi_aktual' => 'nullable|string',
            'temuan'         => 'required|string',
            'rekomendasi'    => 'nullable|string',
            'pic'            => 'nullable|string|max:100',
            'deadline'       => 'nullable|date',
            'status'         => 'required|in:Terbuka,Dalam Proses,Selesai,Overdue',
        ]);

        $count = AuditLpmTemuan::count() + 1;
        $kode = 'LPM-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $unit = MasterUnit::where('nama_unit', 'like', "%{$request->unit_nama}%")->first();

        AuditLpmTemuan::create([
            'kode_temuan'       => $kode,
            'unit_id'           => $unit ? $unit->id : null,
            'unit_nama'         => $request->unit_nama,
            'tanggal_audit'     => $request->tanggal_audit,
            'standar_mutu'      => $request->standar_mutu,
            'aspek'             => $request->aspek,
            'indikator'         => $request->indikator,
            'target_standar'    => $request->target_standar,
            'kondisi_aktual'    => $request->kondisi_aktual,
            'temuan'            => $request->temuan,
            'analisis_penyebab' => $request->analisis_penyebab,
            'rekomendasi'       => $request->rekomendasi,
            'pic'               => $request->pic,
            'deadline'          => $request->deadline,
            'status'            => $request->status,
            'riwayat_status'    => [
                ['tanggal' => date('d M Y'), 'status' => 'Temuan dibuat'],
            ],
        ]);

        return back()->with('success', "Temuan audit LPM baru [{$kode}] berhasil ditambahkan!");
    }

    public function auditLpmUpdate(Request $request, $id)
    {
        $this->guard('edit', 'admin:survey');
        $item = AuditLpmTemuan::findOrFail($id);

        $data = $request->only([
            'unit_nama', 'tanggal_audit', 'standar_mutu', 'aspek', 'indikator',
            'target_standar', 'kondisi_aktual', 'temuan', 'analisis_penyebab',
            'rekomendasi', 'pic', 'deadline', 'status', 'catatan_tindak_lanjut'
        ]);

        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $filename = 'lpm_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('audit_bukti', $filename, 'public');
            $data['bukti_tindak_lanjut'] = 'storage/' . $path;
        }

        if ($request->filled('status') && $request->status !== $item->status) {
            $riwayat = is_array($item->riwayat_status) ? $item->riwayat_status : [];
            $riwayat[] = [
                'tanggal' => date('d M Y'),
                'status'  => 'Status diubah ke ' . $request->status . ($request->catatan_tindak_lanjut ? ' (' . Str::limit($request->catatan_tindak_lanjut, 50) . ')' : '')
            ];
            $data['riwayat_status'] = $riwayat;
        }

        $item->update($data);

        return back()->with('success', "Temuan audit LPM [{$item->kode_temuan}] berhasil diperbarui.");
    }

    public function auditLpmDestroy($id)
    {
        $this->guard('delete', 'admin:survey');
        $item = AuditLpmTemuan::findOrFail($id);
        $item->delete();

        return back()->with('success', "Temuan audit LPM [{$item->kode_temuan}] berhasil dihapus.");
    }

    /**
     * Helper untuk mendapatkan profil DataDosenTendik dari user yang sedang login
     */
    protected function getKaryawanProfile($user)
    {
        if (!$user) {
            return null;
        }

        $karyawan = DataDosenTendik::where('user_id', $user->id)->first();
        if (!$karyawan) {
            $karyawan = DataDosenTendik::where('nama', $user->name)
                ->orWhere('nik', $user->username ?? '')
                ->orWhere('nip', $user->username ?? '')
                ->first();
        }

        return $karyawan;
    }
}
