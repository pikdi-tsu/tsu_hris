<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\MasterPeriodeSurvey;
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
        $stats = [
            'total'              => $totalCount,
            'avg_total'          => $totalCount > 0 
                ? round($allResponses->avg(function($r) {
                    return ($r->skor_responsiveness + $r->skor_keramahan + $r->skor_kecepatan + $r->skor_fasilitas) / 4;
                }), 2) 
                : 0,
            'avg_responsiveness' => $totalCount > 0 ? round($allResponses->avg('skor_responsiveness'), 2) : 0,
            'avg_keramahan'      => $totalCount > 0 ? round($allResponses->avg('skor_keramahan'), 2) : 0,
            'avg_kecepatan'      => $totalCount > 0 ? round($allResponses->avg('skor_kecepatan'), 2) : 0,
            'avg_fasilitas'      => $totalCount > 0 ? round($allResponses->avg('skor_fasilitas'), 2) : 0,
        ];

        return view('admin::survey.periode_show', [
            'title'     => 'Detail Respon Survei: ' . $periode->nama_periode,
            'periode'   => $periode,
            'responses' => $responses,
            'stats'     => $stats,
            'search'    => $request->search ?? '',
        ]);
    }

    /**
     * Submit Kuesioner Kepuasan Layanan SDM (dari Modal Pop-Up di Dashboard)
     */
    public function submitLayanan(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|uuid|exists:master_periode_surveys,id',
            'skor_responsiveness' => 'required|integer|min:1|max:5',
            'skor_keramahan' => 'required|integer|min:1|max:5',
            'skor_kecepatan' => 'required|integer|min:1|max:5',
            'skor_fasilitas' => 'required|integer|min:1|max:5',
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

            // Simpan respon atau update jika sudah ada
            SurveyLayananResponse::updateOrCreate(
                [
                    'periode_id' => $request->periode_id,
                    'karyawan_id' => $karyawan->id,
                ],
                [
                    'user_id' => $user->id,
                    'skor_responsiveness' => $request->skor_responsiveness,
                    'skor_keramahan' => $request->skor_keramahan,
                    'skor_kecepatan' => $request->skor_kecepatan,
                    'skor_fasilitas' => $request->skor_fasilitas,
                    'kritik_saran' => $request->kritik_saran,
                ]
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
     */
    public function pelatihanIndex()
    {
        $user = Auth::user();
        $karyawan = $this->getKaryawanProfile($user);

        $pesertaList = collect();
        if ($karyawan) {
            $pesertaList = TrainingPeserta::with('training')
                ->where('karyawan_id', $karyawan->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin::survey.pelatihan_index', [
            'title' => 'Survey Kepuasan Training',
            'pesertaList' => $pesertaList,
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Form Pengisian Survey Pelatihan + Upload Sertifikat (1x Input)
     */
    public function pelatihanForm($pesertaId)
    {
        $peserta = TrainingPeserta::with(['training', 'karyawan', 'surveyResponse'])->findOrFail($pesertaId);

        return view('admin::survey.form_pelatihan', [
            'title' => 'Survey & Unggah Sertifikat: ' . $peserta->training->nama_training,
            'peserta' => $peserta,
            'training' => $peserta->training,
        ]);
    }

    /**
     * Proses Simpan Survey Pelatihan + File Sertifikat + Auto-Sync ke Roadmap
     */
    public function pelatihanSubmit(Request $request, $pesertaId)
    {
        $peserta = TrainingPeserta::with(['training', 'karyawan'])->findOrFail($pesertaId);

        $request->validate([
            'skor_materi' => 'required|integer|min:1|max:5',
            'skor_narasumber' => 'required|integer|min:1|max:5',
            'skor_fasilitas' => 'required|integer|min:1|max:5',
            'skor_relevansi' => 'required|integer|min:1|max:5',
            'feedback_manfaat' => 'nullable|string|max:1000',
            'sertifikat_nomor' => 'nullable|string|max:100',
            'sertifikat_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Respon Survey Pelatihan
            SurveyPelatihanResponse::updateOrCreate(
                [
                    'training_peserta_id' => $peserta->id,
                ],
                [
                    'skor_materi' => $request->skor_materi,
                    'skor_narasumber' => $request->skor_narasumber,
                    'skor_fasilitas' => $request->skor_fasilitas,
                    'skor_relevansi' => $request->skor_relevansi,
                    'feedback_manfaat' => $request->feedback_manfaat,
                ]
            );

            // 2. Upload file sertifikat jika dilampirkan
            $sertifikatPath = $peserta->sertifikat_file;
            if ($request->hasFile('sertifikat_file')) {
                $file = $request->file('sertifikat_file');
                $ext = $file->getClientOriginalExtension();
                $cleanName = Str::slug($peserta->training->nama_training);
                $filename = 'sertifikat_' . $cleanName . '_' . time() . '.' . $ext;
                $file->storeAs('public/sertifikat', $filename);
                $sertifikatPath = 'storage/sertifikat/' . $filename;
            }

            // 3. Update status TrainingPeserta
            $peserta->update([
                'is_survey_filled' => true,
                'survey_filled_at' => now(),
                'sertifikat_file' => $sertifikatPath,
                'sertifikat_nomor' => $request->sertifikat_nomor ?? $peserta->sertifikat_nomor,
                'sertifikat_uploaded_at' => $sertifikatPath ? now() : $peserta->sertifikat_uploaded_at,
            ]);

            // 4. OTOMATIS SINKRONISASI KE ROADMAP PENGEMBANGAN SDM (DOSEN / TENDIK)
            TrainingSyncRoadmapService::syncPesertaToRoadmap($peserta);

            DB::commit();

            return redirect()->route('admin.survey.pelatihan.index')
                ->with('success', 'Survey kepuasan pelatihan & sertifikat berhasil disimpan dan otomatis terdata pada Roadmap Pengembangan SDM Anda!');
        } catch (\Throwable $e) {
            DB::rollBack();
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
    public function auditSpi()
    {
        $this->guard('view', 'admin:survey');

        return view('admin::survey.audit_spi', [
            'title' => 'Audit SPI (Satuan Pengawas Internal)',
        ]);
    }

    public function auditLpm()
    {
        $this->guard('view', 'admin:survey');

        return view('admin::survey.audit_lpm', [
            'title' => 'Audit LPM (Lembaga Penjaminan Mutu)',
        ]);
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
