<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\Training;
use App\Models\TrainingPeserta;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:training');
    }

    /**
     * Tampilan Utama: Riwayat & Daftar Agenda Pelatihan
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:training');

        $query = Training::with(['pesertas.karyawan.unit'])
            ->orderBy('tanggal_mulai', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_training', 'LIKE', "%{$search}%")
                  ->orWhere('penyelenggara', 'LIKE', "%{$search}%")
                  ->orWhere('lokasi', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('penyelenggara')) {
            $query->where('penyelenggara', $request->penyelenggara);
        }

        $trainings = $query->paginate(15)->withQueryString();

        // Daftar seluruh karyawan aktif untuk modal pemilihan peserta
        $karyawanList = DataDosenTendik::where('is_active', 1)
            ->with('unit')
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin::training.index', [
            'title' => 'Input Training',
            'trainings' => $trainings,
            'karyawanList' => $karyawanList,
        ]);
    }

    /**
     * Simpan Agenda Training Baru beserta Peserta Awal
     */
    public function store(Request $request)
    {
        $this->guard('create', 'admin:training');

        $request->validate([
            'nama_training' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'deskripsi' => 'nullable|string',
            'peserta_ids' => 'nullable|array',
            'peserta_ids.*' => 'uuid|exists:data_dosen_tendiks,id',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $creator = $user ? ($user->name ?? $user->email) : 'Admin';

            $training = Training::create([
                'nama_training' => $request->nama_training,
                'penyelenggara' => $request->penyelenggara,
                'lokasi' => $request->lokasi,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'deskripsi' => $request->deskripsi,
                'status' => 'Selesai',
                'created_by' => $creator,
            ]);

            // Daftarkan peserta
            if ($request->has('peserta_ids') && is_array($request->peserta_ids)) {
                foreach ($request->peserta_ids as $karyawanId) {
                    TrainingPeserta::create([
                        'training_id' => $training->id,
                        'karyawan_id' => $karyawanId,
                        'status_kehadiran' => 'Hadir',
                        'is_survey_filled' => false,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.training.show', $training->id)
                ->with('success', 'Agenda Pelatihan berhasil ditambahkan beserta daftar pesertanya.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_TRAINING_STORE_FAIL]',
                'Gagal menyimpan agenda pelatihan.',
                'Training Store',
                $request
            );
        }
    }

    /**
     * Tampilan Detail Training & Catatan Absensi / Status Survei Peserta
     */
    public function show($id)
    {
        $this->guard('view', 'admin:training');

        $training = Training::with([
            'pesertas.karyawan.unit',
            'pesertas.surveyResponse'
        ])->findOrFail($id);

        // Karyawan yang belum terdaftar di training ini
        $registeredIds = $training->pesertas->pluck('karyawan_id')->toArray();
        $availableKaryawan = DataDosenTendik::where('is_active', 1)
            ->whereNotIn('id', $registeredIds)
            ->with('unit')
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin::training.show', [
            'title' => 'Detail Pelatihan: ' . $training->nama_training,
            'training' => $training,
            'availableKaryawan' => $availableKaryawan,
        ]);
    }

    /**
     * Tambah Peserta ke Training yang Sudah Ada
     */
    public function addPeserta(Request $request, $id)
    {
        $this->guard('edit', 'admin:training');

        $training = Training::findOrFail($id);

        $request->validate([
            'peserta_ids' => 'required|array',
            'peserta_ids.*' => 'uuid|exists:data_dosen_tendiks,id',
        ]);

        try {
            foreach ($request->peserta_ids as $karyawanId) {
                TrainingPeserta::firstOrCreate([
                    'training_id' => $training->id,
                    'karyawan_id' => $karyawanId,
                ], [
                    'status_kehadiran' => 'Hadir',
                    'is_survey_filled' => false,
                ]);
            }

            return back()->with('success', 'Peserta berhasil ditambahkan ke agenda pelatihan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menambahkan peserta: ' . $e->getMessage());
        }
    }

    /**
     * Update Catatan Absensi / Kehadiran Peserta
     */
    public function updateAbsensi(Request $request, $pesertaId)
    {
        $this->guard('edit', 'admin:training');

        $request->validate([
            'status_kehadiran' => 'required|in:Hadir,Izin,Tidak Hadir',
            'catatan_kehadiran' => 'nullable|string|max:255',
        ]);

        try {
            $peserta = TrainingPeserta::findOrFail($pesertaId);
            $peserta->update([
                'status_kehadiran' => $request->status_kehadiran,
                'catatan_kehadiran' => $request->catatan_kehadiran,
            ]);

            return back()->with('success', 'Catatan absensi peserta berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui absensi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Peserta dari Training
     */
    public function removePeserta($pesertaId)
    {
        $this->guard('delete', 'admin:training');

        try {
            $peserta = TrainingPeserta::findOrFail($pesertaId);
            $peserta->delete();

            return back()->with('success', 'Peserta berhasil dihapus dari daftar pelatihan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus peserta: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Agenda Training
     */
    public function destroy($id)
    {
        $this->guard('delete', 'admin:training');

        try {
            $training = Training::findOrFail($id);
            $training->delete();

            return redirect()->route('admin.training.index')
                ->with('success', 'Data pelatihan berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus data pelatihan: ' . $e->getMessage());
        }
    }
}
