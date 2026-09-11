<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\RequestSuratSdm;
use App\Models\DataDosenTendik;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class RequestSuratController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:persuratan-sdm');
    }

    private function getLoggedInKaryawan()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    // =========================================================================
    // SISI PEGAWAI (DOSEN & TENDIK) - SELF-SERVICE
    // =========================================================================

    /**
     * Halaman Layanan Pengajuan Surat Saya
     */
    public function userIndex()
    {
        $karyawan = $this->getLoggedInKaryawan();
        return view('admin::persuratan.user_index', [
            'title'    => 'Pengajuan Surat ke SDM',
            'menuIcon' => 'fas fa-envelope-open-text',
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * DataTables Riwayat Pengajuan Surat Saya
     */
    public function userDataTable()
    {
        $karyawan = $this->getLoggedInKaryawan();
        $query = RequestSuratSdm::query()
            ->where('data_dosen_tendik_id', $karyawan ? $karyawan->id : '')
            ->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('tgl_pengajuan', function ($row) {
                return $row->created_at->format('d/m/Y H:i');
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-detail-surat" data-url="' . route('admin.request-surat.detail', $row->id) . '" title="Lihat Detail & Status"><i class="fas fa-eye mr-1"></i> Detail</button>';
                if ($row->status === 'selesai' && $row->file_hasil_url) {
                    $btn .= '<a href="' . $row->file_hasil_url . '" target="_blank" download class="btn btn-success" title="Unduh Surat Resmi"><i class="fas fa-file-download mr-1"></i> Unduh</a>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Form Buat Pengajuan Surat Baru
     */
    public function userCreateModal()
    {
        $karyawan = $this->getLoggedInKaryawan();
        $masterJenisSurat = \App\Models\MasterJenisSurat::active()
            ->orderBy('urutan', 'asc')
            ->orderBy('nama_surat', 'asc')
            ->get();

        return view('admin::persuratan.user_create_modal', compact('karyawan', 'masterJenisSurat'));
    }

    /**
     * Simpan Pengajuan Surat Baru
     */
    public function userStore(Request $request)
    {
        $karyawan = $this->getLoggedInKaryawan();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil kepegawaian Anda belum terdaftar di sistem HRIS. Silakan hubungi Admin SDM.',
            ], 422);
        }

        $request->validate([
            'jenis_surat'        => 'required|string|max:100',
            'keperluan'          => 'required|string',
            'keterangan_tambahan'=> 'nullable|string',
            'file_lampiran'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Cek jika jenis surat mewajibkan lampiran
        $master = \App\Models\MasterJenisSurat::where('nama_surat', $request->jenis_surat)->first();
        if ($master && $master->perlu_lampiran && !$request->hasFile('file_lampiran')) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis surat "' . $master->nama_surat . '" mewajibkan unggahan berkas lampiran pendukung.',
            ], 422);
        }

        $filePath = null;
        if ($request->hasFile('file_lampiran')) {
            $file = $request->file('file_lampiran');
            $filename = 'Lampiran_' . $karyawan->nik . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('private/surat_sdm/lampiran', $filename);
            $filePath = 'private/surat_sdm/lampiran/' . $filename;
        }

        $tiket = RequestSuratSdm::generateNomorTiket();

        $surat = RequestSuratSdm::create([
            'nomor_tiket'          => $tiket,
            'data_dosen_tendik_id' => $karyawan->id,
            'user_id'              => Auth::id(),
            'jenis_surat'          => $request->jenis_surat,
            'keperluan'            => $request->keperluan,
            'keterangan_tambahan'  => $request->keterangan_tambahan,
            'file_lampiran'        => $filePath,
            'status'               => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Permohonan surat berhasil diajukan dengan Nomor Tiket: {$tiket}. Anda dapat memantau statusnya di dashboard ini.",
            'data'    => $surat,
        ]);
    }

    /**
     * Modal Detail Pengajuan Surat & Tracking Status
     */
    public function detail($id)
    {
        $surat = RequestSuratSdm::with(['pegawai', 'petugas'])->findOrFail($id);
        return view('admin::persuratan.detail_modal', compact('surat'));
    }

    // =========================================================================
    // SISI ADMIN SDM (VERIFIKASI & PEMROSESAN SURAT)
    // =========================================================================

    /**
     * Halaman Kelola Request Surat Masuk (Admin SDM)
     */
    public function adminIndex()
    {
        $counts = [
            'total'    => RequestSuratSdm::count(),
            'menunggu' => RequestSuratSdm::where('status', 'menunggu')->count(),
            'diproses' => RequestSuratSdm::where('status', 'diproses')->count(),
            'selesai'  => RequestSuratSdm::where('status', 'selesai')->count(),
            'ditolak'  => RequestSuratSdm::where('status', 'ditolak')->count(),
        ];

        return view('admin::persuratan.admin_index', [
            'title'    => 'Kelola Permohonan Surat Pegawai',
            'menuIcon' => 'fas fa-tasks',
            'counts'   => $counts,
        ]);
    }

    /**
     * DataTables Permohonan Surat Masuk (Admin SDM)
     */
    public function adminDataTable(Request $request)
    {
        $query = RequestSuratSdm::with(['pegawai.unit', 'petugas', 'penerimaTerusan'])
            ->select('request_surat_sdms.*')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pemohon', function ($row) {
                $nama = $row->pegawai ? $row->pegawai->nama : 'N/A';
                $nik = $row->pegawai ? $row->pegawai->nik : '-';
                $unit = ($row->pegawai && $row->pegawai->unit) ? $row->pegawai->unit->nama_unit : '-';
                return '<div><strong>' . htmlspecialchars($nama) . '</strong></div><small class="text-muted">' . $nik . ' | ' . htmlspecialchars($unit) . '</small>';
            })
            ->addColumn('tgl_pengajuan', function ($row) {
                return $row->created_at->format('d/m/Y H:i');
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-detail-surat" data-url="' . route('admin.request-surat.detail', $row->id) . '" title="Lihat Detail"><i class="fas fa-eye"></i></button>';

                // Trigger status pemrosesan
                if ($row->status === 'menunggu') {
                    $btn .= '<button type="button" class="btn btn-primary btn-proses-surat" data-url="' . route('admin.request-surat.proses', $row->id) . '" title="Mulai Proses Surat"><i class="fas fa-cogs mr-1"></i> Proses</button>';
                    $btn .= '<button type="button" class="btn btn-info btn-modal-teruskan" data-url="' . route('admin.request-surat.modal-teruskan', $row->id) . '" title="Teruskan / Disposisi Surat"><i class="fas fa-share mr-1"></i> Teruskan</button>';
                    $btn .= '<button type="button" class="btn btn-danger btn-modal-tolak" data-url="' . route('admin.request-surat.modal-tolak', $row->id) . '" title="Tolak Permohonan"><i class="fas fa-times"></i></button>';
                } elseif ($row->status === 'diproses') {
                    $btn .= '<button type="button" class="btn btn-info btn-modal-teruskan" data-url="' . route('admin.request-surat.modal-teruskan', $row->id) . '" title="Teruskan / Disposisi Ulang"><i class="fas fa-share mr-1"></i> Teruskan</button>';
                    $btn .= '<button type="button" class="btn btn-success btn-modal-selesai" data-url="' . route('admin.request-surat.modal-selesai', $row->id) . '" title="Selesaikan & Unggah Surat"><i class="fas fa-check mr-1"></i> Selesaikan</button>';
                    $btn .= '<button type="button" class="btn btn-danger btn-modal-tolak" data-url="' . route('admin.request-surat.modal-tolak', $row->id) . '" title="Tolak Permohonan"><i class="fas fa-times"></i></button>';
                } elseif ($row->status === 'selesai' && $row->file_hasil_url) {
                    $btn .= '<a href="' . $row->file_hasil_url . '" target="_blank" download class="btn btn-outline-success" title="Unduh File Surat Hasil"><i class="fas fa-download"></i></a>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['pemohon', 'status_badge', 'aksi'])
            ->make(true);
    }

    /**
     * Trigger: Ubah Status Jadi "Sedang Diproses"
     */
    public function adminProses($id)
    {
        $surat = RequestSuratSdm::findOrFail($id);

        $surat->update([
            'status'       => 'diproses',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Surat tiket {$surat->nomor_tiket} sedang diproses. Pemohon dapat melihat status ini.",
        ]);
    }

    /**
     * Modal Form Teruskan / Disposisi Surat ke Staf/Sekretariat/Pimpinan
     */
    public function adminTeruskanModal($id)
    {
        $surat = RequestSuratSdm::with(['pegawai', 'penerimaTerusan'])->findOrFail($id);
        $staffList = \App\Models\User::where('isactive', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email']);

        return view('admin::persuratan.admin_teruskan_modal', compact('surat', 'staffList'));
    }

    /**
     * Simpan Terusan / Disposisi Surat
     */
    public function adminStoreTeruskan(Request $request, $id)
    {
        $surat = RequestSuratSdm::findOrFail($id);

        $request->validate([
            'diteruskan_ke'   => 'required|exists:system_users,id',
            'catatan_terusan' => 'required|string|min:3',
        ]);

        $surat->update([
            'status'          => 'diproses',
            'diteruskan_ke'   => $request->diteruskan_ke,
            'diteruskan_at'   => now(),
            'catatan_terusan' => $request->catatan_terusan,
            'processed_by'    => Auth::id(),
            'processed_at'    => $surat->processed_at ?? now(),
        ]);

        $penerima = \App\Models\User::find($request->diteruskan_ke);
        $namaPenerima = $penerima ? $penerima->name : 'staf yang dipilih';

        return response()->json([
            'success' => true,
            'message' => "Surat tiket {$surat->nomor_tiket} berhasil diteruskan kepada {$namaPenerima}.",
        ]);
    }

    /**
     * Modal Form Selesaikan Surat & Unggah File PDF Hasil
     */
    public function adminSelesaiModal($id)
    {
        $surat = RequestSuratSdm::with('pegawai')->findOrFail($id);
        return view('admin::persuratan.admin_selesai_modal', compact('surat'));
    }

    /**
     * Simpan Surat Selesai (Upload PDF Surat Resmi)
     */
    public function adminStoreSelesai(Request $request, $id)
    {
        $surat = RequestSuratSdm::findOrFail($id);

        $request->validate([
            'nomor_surat_keluar' => 'required|string|max:100',
            'file_surat_hasil'   => 'required|file|mimes:pdf|max:10240',
            'catatan_petugas'    => 'nullable|string',
        ]);

        $file = $request->file('file_surat_hasil');
        $cleanNomor = str_replace(['/', ' '], '_', $request->nomor_surat_keluar);
        $filename = "Surat_Keluar_{$cleanNomor}_" . time() . ".pdf";
        $file->storeAs('private/surat_sdm/hasil', $filename);
        $filePath = 'private/surat_sdm/hasil/' . $filename;

        $surat->update([
            'status'             => 'selesai',
            'nomor_surat_keluar' => $request->nomor_surat_keluar,
            'file_surat_hasil'   => $filePath,
            'catatan_petugas'    => $request->catatan_petugas,
            'completed_at'       => now(),
            'processed_by'       => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Surat resmi untuk tiket {$surat->nomor_tiket} berhasil diterbitkan dan siap diunduh pemohon.",
        ]);
    }

    /**
     * Modal Form Tolak Permohonan Surat
     */
    public function adminTolakModal($id)
    {
        $surat = RequestSuratSdm::with('pegawai')->findOrFail($id);
        return view('admin::persuratan.admin_tolak_modal', compact('surat'));
    }

    /**
     * Simpan Status Ditolak
     */
    public function adminStoreTolak(Request $request, $id)
    {
        $surat = RequestSuratSdm::findOrFail($id);

        $request->validate([
            'catatan_petugas' => 'required|string|min:5',
        ]);

        $surat->update([
            'status'          => 'ditolak',
            'catatan_petugas' => $request->catatan_petugas,
            'completed_at'    => now(),
            'processed_by'    => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Permohonan surat tiket {$surat->nomor_tiket} telah ditolak dengan catatan yang tersimpan.",
        ]);
    }

    /**
     * Secure Stream Berkas Lampiran Pengajuan Surat (Authorization Gate)
     */
    public function streamLampiran($id)
    {
        if (!Auth::check()) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses berkas lampiran ini.');
        }

        $surat = RequestSuratSdm::with('pegawai')->findOrFail($id);
        $user = Auth::user();

        // 1. Super Admin & Admin SDM
        $isAdmin = $user->isAdmin()
            || $user->hasRole(['super admin hris', 'admin hris testing', 'Super Admin', 'Admin SDM'])
            || $user->can('admin:persuratan-sdm')
            || $user->can('admin:persuratan-sdm:view');

        // 2. Pemilik Surat
        $isOwner = false;
        if ($surat->user_id == $user->id) {
            $isOwner = true;
        } elseif ($surat->pegawai) {
            if ($surat->pegawai->user_id == $user->id || (!empty($surat->pegawai->nik) && $surat->pegawai->nik == $user->nik)) {
                $isOwner = true;
            }
        }

        // 3. Pihak Terusan / Sekretariat
        $isSekretariat = ($surat->diteruskan_ke == $user->id) || ($surat->diselesaikan_oleh_sekretariat == $user->id);

        if (!$isAdmin && !$isOwner && !$isSekretariat) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat lampiran surat ini.');
        }

        $filePath = $surat->file_lampiran;
        if (!$filePath) {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        $candidates = [
            storage_path('app/' . ltrim($filePath, '/')),
            storage_path('app/public/' . str_replace('storage/', '', ltrim($filePath, '/'))),
            public_path(ltrim($filePath, '/')),
            public_path('storage/' . str_replace('storage/', '', ltrim($filePath, '/'))),
        ];

        $fullPath = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            abort(404, 'File fisik lampiran tidak ditemukan di server.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $filename = 'Lampiran_' . ($surat->nomor_tiket) . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Secure Stream Berkas Surat Hasil Terbitan SDM (Authorization Gate)
     */
    public function streamHasil($id)
    {
        if (!Auth::check()) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses dokumen surat ini.');
        }

        $surat = RequestSuratSdm::with('pegawai')->findOrFail($id);
        $user = Auth::user();

        // 1. Super Admin & Admin SDM
        $isAdmin = $user->isAdmin()
            || $user->hasRole(['super admin hris', 'admin hris testing', 'Super Admin', 'Admin SDM'])
            || $user->can('admin:persuratan-sdm')
            || $user->can('admin:persuratan-sdm:view');

        // 2. Pemilik Surat
        $isOwner = false;
        if ($surat->user_id == $user->id) {
            $isOwner = true;
        } elseif ($surat->pegawai) {
            if ($surat->pegawai->user_id == $user->id || (!empty($surat->pegawai->nik) && $surat->pegawai->nik == $user->nik)) {
                $isOwner = true;
            }
        }

        // 3. Pihak Terusan / Sekretariat
        $isSekretariat = ($surat->diteruskan_ke == $user->id) || ($surat->diselesaikan_oleh_sekretariat == $user->id);

        if (!$isAdmin && !$isOwner && !$isSekretariat) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat dokumen surat ini.');
        }

        $filePath = $surat->file_surat_hasil;
        if (!$filePath) {
            abort(404, 'Berkas surat hasil tidak ditemukan.');
        }

        $candidates = [
            storage_path('app/' . ltrim($filePath, '/')),
            storage_path('app/public/' . str_replace('storage/', '', ltrim($filePath, '/'))),
            public_path(ltrim($filePath, '/')),
            public_path('storage/' . str_replace('storage/', '', ltrim($filePath, '/'))),
        ];

        $fullPath = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            abort(404, 'File fisik surat hasil tidak ditemukan di server.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $filename = 'Surat_Resmi_' . ($surat->nomor_tiket) . '.pdf';

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    // =========================================================================
    // SISI SEKRETARIAT & ALUR SK REKTORAT (KASUS INTERNAL SDM - SEKRETARIAT)
    // =========================================================================

    /**
     * Halaman Tugas SK & Permohonan Masuk ke Sekretariat
     */
    public function sekretariatInbox()
    {
        $user = Auth::user();
        $counts = [
            'total'         => RequestSuratSdm::whereNotNull('diteruskan_ke')->count(),
            'perlu_sk'      => RequestSuratSdm::whereNotNull('diteruskan_ke')->where('status', 'diproses')->count(),
            'hardfile_siap' => RequestSuratSdm::where('status_hardfile', 'siap_diambil')->count(),
            'selesai'       => RequestSuratSdm::whereNotNull('diteruskan_ke')->where('status', 'selesai')->count(),
        ];

        return view('admin::persuratan.sekretariat_inbox', [
            'title'    => 'Tugas SK & Alur Sekretariat Rektorat',
            'menuIcon' => 'fas fa-stamp',
            'counts'   => $counts,
        ]);
    }

    /**
     * DataTables Tugas SK Sekretariat
     */
    public function sekretariatDataTable(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin()
            || $user->hasRole(['super admin hris', 'admin hris testing', 'Super Admin', 'Admin SDM'])
            || $user->can('admin:persuratan-sdm');

        $query = RequestSuratSdm::with(['pegawai.unit', 'penerimaTerusan', 'petugasSekretariat', 'petugas'])
            ->whereNotNull('diteruskan_ke')
            ->orderBy('id', 'desc');

        if (!$isAdmin) {
            $query->where('diteruskan_ke', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_hardfile')) {
            $query->where('status_hardfile', $request->status_hardfile);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tiket_info', function ($row) {
                return '<div><strong>' . htmlspecialchars($row->nomor_tiket) . '</strong><br>'
                    . '<small class="text-muted">' . $row->created_at->format('d/m/Y H:i') . '</small></div>';
            })
            ->addColumn('pemohon', function ($row) {
                $nama = $row->pegawai ? $row->pegawai->nama_lengkap : 'N/A';
                $unit = ($row->pegawai && $row->pegawai->unit) ? $row->pegawai->unit->nama_unit : '-';
                return '<strong>' . htmlspecialchars($nama) . '</strong><br><small class="text-muted">' . htmlspecialchars($unit) . '</small>';
            })
            ->addColumn('permohonan', function ($row) {
                $terusan = $row->catatan_terusan ? ('<br><small class="text-primary font-italic"><i class="fas fa-info-circle mr-1"></i> Dari SDM: ' . htmlspecialchars($row->catatan_terusan) . '</small>') : '';
                return '<strong>' . htmlspecialchars($row->jenis_surat) . '</strong><br><small>' . htmlspecialchars($row->keperluan) . '</small>' . $terusan;
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('hardfile_badge', function ($row) {
                return $row->status_hardfile_badge;
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-detail-surat" data-url="' . route('admin.request-surat.detail', $row->id) . '" title="Lihat Detail Tiket"><i class="fas fa-eye mr-1"></i> Detail</button>';

                if ($row->file_lampiran_url) {
                    $btn .= '<a href="' . $row->file_lampiran_url . '" target="_blank" class="btn btn-outline-primary" title="Unduh Lampiran Pengajuan"><i class="fas fa-paperclip"></i></a>';
                }

                if ($row->status !== 'selesai') {
                    $btn .= '<button type="button" class="btn btn-success btn-modal-sekretariat-selesai" data-url="' . route('admin.request-surat.modal-sekretariat-selesai', $row->id) . '" title="Unggah Softfile SK & Update Status Hardfile"><i class="fas fa-upload mr-1"></i> Proses SK</button>';
                } else {
                    if ($row->file_hasil_url) {
                        $btn .= '<a href="' . $row->file_hasil_url . '" target="_blank" class="btn btn-danger" title="Lihat Softfile SK Rektorat"><i class="fas fa-file-pdf mr-1"></i> SK</a>';
                    }
                    if ($row->status_hardfile === 'siap_diambil') {
                        $btn .= '<button type="button" class="btn btn-outline-success btn-update-hardfile" data-url="' . route('admin.request-surat.update-hardfile', $row->id) . '" data-status="telah_diterima_sdm" title="Konfirmasi Hardfile Sudah Diserahkan ke SDM"><i class="fas fa-handshake mr-1"></i> Serahkan Hardfile</button>';
                    }
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['tiket_info', 'pemohon', 'permohonan', 'status_badge', 'hardfile_badge', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Form Selesaikan SK Rektorat oleh Sekretariat
     */
    public function sekretariatSelesaiModal($id)
    {
        $surat = RequestSuratSdm::with(['pegawai.unit', 'penerimaTerusan'])->findOrFail($id);
        return view('admin::persuratan.sekretariat_selesai_modal', compact('surat'));
    }

    /**
     * Simpan Penyelesaian SK Rektorat oleh Sekretariat (Unggah Softfile SK + Set Status Hardfile)
     */
    public function sekretariatStoreSelesai(Request $request, $id)
    {
        $surat = RequestSuratSdm::findOrFail($id);

        $request->validate([
            'nomor_surat_keluar' => 'required|string|max:100',
            'file_surat_hasil'   => 'required|file|mimes:pdf|max:15360',
            'status_hardfile'    => 'required|in:siap_diambil,telah_diterima_sdm',
            'catatan_sekretariat'=> 'nullable|string',
        ]);

        $file = $request->file('file_surat_hasil');
        $cleanNomor = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->nomor_surat_keluar);
        $filename = "SK_Rektor_{$cleanNomor}_" . time() . ".pdf";
        $file->storeAs('private/surat_sdm/sk_rektor', $filename);
        $filePath = 'private/surat_sdm/sk_rektor/' . $filename;

        $surat->update([
            'status'                        => 'selesai',
            'nomor_surat_keluar'            => $request->nomor_surat_keluar,
            'file_surat_hasil'              => $filePath,
            'status_hardfile'               => $request->status_hardfile,
            'catatan_sekretariat'           => $request->catatan_sekretariat,
            'diselesaikan_oleh_sekretariat' => Auth::id(),
            'tgl_diselesaikan_sekretariat'  => now(),
            'completed_at'                  => now(),
        ]);

        $hardfileMsg = $request->status_hardfile === 'siap_diambil' 
            ? 'Hardfile SK bertandatangan/cap basah siap diambil oleh SDM di Sekretariat.'
            : 'Hardfile SK telah diserahkan kepada SDM.';

        return response()->json([
            'success' => true,
            'message' => "SK Rektorat no. {$request->nomor_surat_keluar} untuk tiket {$surat->nomor_tiket} berhasil diunggah dan diselesaikan. {$hardfileMsg}",
        ]);
    }

    /**
     * Update Status Hardfile (Bisa oleh SDM saat ambil berkas fisik atau oleh Sekretariat)
     */
    public function adminUpdateHardfileStatus(Request $request, $id)
    {
        $surat = RequestSuratSdm::findOrFail($id);

        $request->validate([
            'status_hardfile' => 'required|in:belum_tersedia,siap_diambil,telah_diterima_sdm',
        ]);

        $surat->update([
            'status_hardfile' => $request->status_hardfile,
        ]);

        $statusLabel = match($request->status_hardfile) {
            'siap_diambil'      => 'Siap Diambil di Sekretariat',
            'telah_diterima_sdm'=> 'Telah Diterima oleh SDM',
            default             => 'Belum Tersedia',
        };

        return response()->json([
            'success' => true,
            'message' => "Status hardfile fisik tiket {$surat->nomor_tiket} berhasil diubah menjadi: {$statusLabel}.",
        ]);
    }
}
