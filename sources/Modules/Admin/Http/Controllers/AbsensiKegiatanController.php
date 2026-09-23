<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\Kegiatan;
use App\Models\KegiatanPresensi;
use App\Models\MasterUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AbsensiKegiatanController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:absensi-kegiatan');
        $this->middleware('permission:admin:absensi-kegiatan:view')->only(['checkActive', 'printDaftarHadir']);
    }

    /**
     * Halaman Utama Absensi Kegiatan (Daftar Acara & Monitoring)
     */
    public function index(Request $request)
    {
        $query = Kegiatan::with(['penyelenggaraUnit', 'penanggungJawab'])
            ->withCount([
                'presensis as hadir_count' => function ($q) {
                    $q->where('status_kehadiran', 'Ya');
                },
                'presensis as tidak_count' => function ($q) {
                    $q->where('status_kehadiran', 'Tidak');
                },
                'presensis as terlambat_count' => function ($q) {
                    $q->where('status_kehadiran', 'Terlambat');
                },
            ]);

        // Filter pencarian
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($sq) use ($search) {
                $sq->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_kegiatan', $request->tanggal);
        }

        $kegiatans = $query->orderBy('tanggal_kegiatan', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Statistik Utama
        $totalKegiatan = Kegiatan::count();
        $kegiatanAktif = Kegiatan::activeNow()->count();
        $totalPresensi = KegiatanPresensi::count();
        $totalHadir = KegiatanPresensi::where('status_kehadiran', 'Ya')->count();

        $units = MasterUnit::orderBy('nama_unit')->get();
        $pegawais = DataDosenTendik::orderBy('nama')->get();

        return view('admin::absensi-kegiatan.index', [
            'title'          => 'Absensi Kegiatan',
            'menu'           => 'absensi-kegiatan',
            'menuIcon'       => 'fas fa-calendar-check',
            'kegiatans'      => $kegiatans,
            'totalKegiatan'  => $totalKegiatan,
            'kegiatanAktif'  => $kegiatanAktif,
            'totalPresensi'  => $totalPresensi,
            'totalHadir'     => $totalHadir,
            'units'          => $units,
            'pegawais'       => $pegawais,
        ]);
    }

    /**
     * Simpan Kegiatan Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan'         => 'required|string|max:255',
            'kategori'              => 'required|string|max:100',
            'tanggal_kegiatan'      => 'required|date',
            'jam_mulai'             => 'required',
            'jam_selesai'           => 'required',
            'lokasi'                => 'required|string|max:255',
            'target_peserta'        => 'required|string|max:100',
            'penyelenggara_unit_id' => 'nullable|exists:master_units,id',
            'penanggung_jawab_id'   => 'nullable|exists:data_dosen_tendiks,id',
            'keterangan'            => 'nullable|string',
        ]);

        Kegiatan::create([
            'nama_kegiatan'         => $request->nama_kegiatan,
            'kategori'              => $request->kategori,
            'tanggal_kegiatan'      => $request->tanggal_kegiatan,
            'jam_mulai'             => $request->jam_mulai,
            'jam_selesai'           => $request->jam_selesai,
            'lokasi'                => $request->lokasi,
            'target_peserta'        => $request->target_peserta,
            'penyelenggara_unit_id' => $request->penyelenggara_unit_id ?: null,
            'penanggung_jawab_id'   => $request->penanggung_jawab_id ?: null,
            'status'                => 'Dibuka',
            'keterangan'            => $request->keterangan,
        ]);

        return redirect()->route('admin.absensi-kegiatan.index')
            ->with('success', 'Agenda kegiatan berhasil dijadwalkan dan dibuka untuk absensi!');
    }

    /**
     * Detail Kegiatan & Monitoring Kehadiran
     */
    public function show($id)
    {
        $kegiatan = Kegiatan::with([
            'penyelenggaraUnit',
            'penanggungJawab',
            'presensis.pegawai.unit',
            'presensis.user',
        ])->findOrFail($id);

        $presensis = $kegiatan->presensis()->orderBy('waktu_presensi', 'desc')->get();

        $countYa = $presensis->where('status_kehadiran', 'Ya')->count();
        $countTidak = $presensis->where('status_kehadiran', 'Tidak')->count();
        $countTerlambat = $presensis->where('status_kehadiran', 'Terlambat')->count();
        $totalRespon = $presensis->count();

        $units = MasterUnit::orderBy('nama_unit')->get();
        $pegawais = DataDosenTendik::orderBy('nama')->get();

        return view('admin::absensi-kegiatan.show', [
            'title'          => 'Detail Absensi Kegiatan: ' . $kegiatan->nama_kegiatan,
            'menu'           => 'absensi-kegiatan',
            'menuIcon'       => 'fas fa-calendar-check',
            'kegiatan'       => $kegiatan,
            'presensis'      => $presensis,
            'countYa'        => $countYa,
            'countTidak'     => $countTidak,
            'countTerlambat' => $countTerlambat,
            'totalRespon'    => $totalRespon,
            'units'          => $units,
            'pegawais'       => $pegawais,
        ]);
    }

    /**
     * Update Data Kegiatan
     */
    public function update(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        $request->validate([
            'nama_kegiatan'         => 'required|string|max:255',
            'kategori'              => 'required|string|max:100',
            'tanggal_kegiatan'      => 'required|date',
            'jam_mulai'             => 'required',
            'jam_selesai'           => 'required',
            'lokasi'                => 'required|string|max:255',
            'target_peserta'        => 'required|string|max:100',
            'status'                => 'required|in:Draft,Dibuka,Selesai,Dibatalkan',
            'penyelenggara_unit_id' => 'nullable|exists:master_units,id',
            'penanggung_jawab_id'   => 'nullable|exists:data_dosen_tendiks,id',
            'keterangan'            => 'nullable|string',
        ]);

        $kegiatan->update([
            'nama_kegiatan'         => $request->nama_kegiatan,
            'kategori'              => $request->kategori,
            'tanggal_kegiatan'      => $request->tanggal_kegiatan,
            'jam_mulai'             => $request->jam_mulai,
            'jam_selesai'           => $request->jam_selesai,
            'lokasi'                => $request->lokasi,
            'target_peserta'        => $request->target_peserta,
            'status'                => $request->status,
            'penyelenggara_unit_id' => $request->penyelenggara_unit_id ?: null,
            'penanggung_jawab_id'   => $request->penanggung_jawab_id ?: null,
            'keterangan'            => $request->keterangan,
        ]);

        return redirect()->route('admin.absensi-kegiatan.show', $kegiatan->id)
            ->with('success', 'Data kegiatan berhasil diperbarui!');
    }

    /**
     * Hapus Kegiatan Beserta Data Presensinya
     */
    public function destroy($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        // Hapus foto-foto selfie jika ada
        foreach ($kegiatan->presensis as $p) {
            if ($p->foto_selfie && Storage::disk('public')->exists($p->foto_selfie)) {
                Storage::disk('public')->delete($p->foto_selfie);
            }
        }

        $kegiatan->delete();

        return redirect()->route('admin.absensi-kegiatan.index')
            ->with('success', 'Kegiatan beserta rekaman absensi berhasil dihapus.');
    }

    /**
     * Endpoint Pengecekan Kegiatan Aktif Saat Ini (Untuk Pop-Up Otomatis)
     */
    public function checkActive(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['has_active' => false]);
        }

        $user = auth()->user();
        $karyawan = $user->karyawan ?? DataDosenTendik::where('user_id', $user->id)->first();

        // Cari kegiatan yang berstatus 'Dibuka', tanggal hari ini, dan jam saat ini berada di rentang waktu kegiatan
        $activeEvents = Kegiatan::activeNow()->get();

        foreach ($activeEvents as $event) {
            // Evaluasi target peserta
            $target = $event->target_peserta;
            $match = false;

            if ($target === 'Semua Pegawai' || empty($target)) {
                $match = true;
            } elseif ($target === 'Dosen Saja' && $karyawan && ($karyawan->tipe_karyawan === 'Dosen' || !empty($karyawan->nidn))) {
                $match = true;
            } elseif ($target === 'Tendik Saja' && $karyawan && $karyawan->tipe_karyawan === 'Tendik') {
                $match = true;
            } elseif ($target === 'Unit Tertentu' && $karyawan && $karyawan->unit_id == $event->penyelenggara_unit_id) {
                $match = true;
            } elseif ($karyawan && $event->penanggung_jawab_id == $karyawan->id) {
                $match = true;
            } else {
                $match = false;
            }

            if (!$match) {
                continue;
            }

            // Periksa apakah user ini sudah pernah submit absensi
            $alreadyFilled = KegiatanPresensi::where('kegiatan_id', $event->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$alreadyFilled) {
                return response()->json([
                    'has_active' => true,
                    'kegiatan'   => [
                        'id'            => $event->id,
                        'nama_kegiatan' => $event->nama_kegiatan,
                        'kategori'      => $event->kategori,
                        'tanggal'       => $event->tanggal_kegiatan->format('d/m/Y'),
                        'waktu'         => substr($event->jam_mulai, 0, 5) . ' - ' . substr($event->jam_selesai, 0, 5) . ' WIB',
                        'lokasi'        => $event->lokasi,
                        'keterangan'    => $event->keterangan,
                    ],
                    'user_name'  => $karyawan->nama ?? $user->name,
                ]);
            }
        }

        return response()->json(['has_active' => false]);
    }

    /**
     * Endpoint Submit Absensi dari Pop-Up
     */
    public function submitPresensi(Request $request)
    {
        $request->validate([
            'kegiatan_id'      => 'required|exists:kegiatans,id',
            'status_kehadiran' => 'required|in:Ya,Tidak,Terlambat',
            'foto_selfie'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'keterangan'       => 'nullable|string|max:500',
        ], [
            'status_kehadiran.required' => 'Pilih status kehadiran Anda (Ya / Tidak / Terlambat).',
            'foto_selfie.image'         => 'File foto selfie harus berupa gambar yang valid.',
            'foto_selfie.max'           => 'Ukuran foto selfie maksimal adalah 5MB.',
        ]);

        $user = auth()->user();
        $karyawan = $user->karyawan ?? DataDosenTendik::where('user_id', $user->id)->first();

        // Simpan foto selfie jika diunggah
        $fotoPath = null;
        if ($request->hasFile('foto_selfie')) {
            $file = $request->file('foto_selfie');
            $filename = 'selfie_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $fotoPath = $file->storeAs('kegiatan_selfie', $filename, 'public');
        }

        $presensi = KegiatanPresensi::updateOrCreate(
            [
                'kegiatan_id' => $request->kegiatan_id,
                'user_id'     => $user->id,
            ],
            [
                'pegawai_id'       => $karyawan->id ?? null,
                'status_kehadiran' => $request->status_kehadiran,
                'foto_selfie'      => $fotoPath,
                'keterangan'       => $request->keterangan,
                'waktu_presensi'   => now(),
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Terima kasih, absensi kehadiran kegiatan Anda telah berhasil dicatat!',
                'data'    => $presensi,
            ]);
        }

        return back()->with('success', 'Terima kasih, absensi kehadiran kegiatan Anda telah berhasil dicatat!');
    }

    /**
     * Cetak Berita Acara / Rekap Daftar Hadir Kegiatan (Print Preview)
     */
    public function printDaftarHadir($id)
    {
        $kegiatan = Kegiatan::with([
            'penyelenggaraUnit',
            'penanggungJawab',
            'presensis.pegawai.unit',
            'presensis.user',
        ])->findOrFail($id);

        $presensis = $kegiatan->presensis()->orderBy('waktu_presensi', 'asc')->get();

        return view('admin::absensi-kegiatan.print', [
            'kegiatan'  => $kegiatan,
            'presensis' => $presensis,
        ]);
    }
}
