<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\DisposisiSuratMasuk;
use App\Models\SuratMasuk;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class DisposisiUnitController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        // Allowed for all authenticated users who have role or permissions
    }

    private function getLoggedInKaryawan()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    /**
     * Halaman Kotak Masuk Disposisi Unit Kerja
     */
    public function index()
    {
        $karyawan = $this->getLoggedInKaryawan();
        $myUnit = $karyawan && $karyawan->unit ? $karyawan->unit : null;
        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();

        return view('admin::disposisi-unit.index', [
            'title'    => 'Disposisi Masuk Unit Kerja',
            'menuIcon' => 'fas fa-tasks',
            'karyawan' => $karyawan,
            'myUnit'   => $myUnit,
            'units'    => $units,
        ]);
    }

    /**
     * DataTables Disposisi Unit
     */
    public function dataTable(Request $request)
    {
        $user = Auth::user();
        $karyawan = $this->getLoggedInKaryawan();

        $isAdmin = $user->isAdmin()
            || $user->hasRole(['super admin hris', 'admin hris testing', 'Super Admin', 'Admin SDM'])
            || $user->can('admin:persuratan-sdm');

        $query = DisposisiSuratMasuk::with([
            'suratMasuk',
            'unitTujuan',
            'pegawaiTujuan',
            'dariUser',
            'userPenyelesai'
        ])->orderBy('id', 'desc');

        if (!$isAdmin) {
            // Filter hanya untuk unit kerja pegawai login atau yang ditugaskan ke pegawai login
            $query->where(function ($q) use ($karyawan) {
                if ($karyawan) {
                    $q->where('ke_unit_id', $karyawan->unit_id)
                      ->orWhere('ke_pegawai_id', $karyawan->id);
                } else {
                    $q->where('ke_unit_id', 'none');
                }
            });
        } elseif ($request->filled('filter_unit')) {
            $query->where('ke_unit_id', $request->filter_unit);
        }

        if ($request->filled('filter_status')) {
            $query->where('status_tindak_lanjut', $request->filter_status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('surat_info', function ($row) {
                $sm = $row->suratMasuk;
                if (!$sm) return '-';
                return '<div><strong>' . htmlspecialchars($sm->no_agenda ?? '-') . '</strong><br>'
                    . '<small class="text-muted">No Asal: ' . htmlspecialchars($sm->no_surat_asal) . '</small><br>'
                    . '<span class="font-weight-bold text-primary">' . htmlspecialchars($sm->pengirim_instansi) . '</span><br>'
                    . '<small>' . htmlspecialchars($sm->perihal) . '</small>'
                    . '</div>';
            })
            ->addColumn('unit_info', function ($row) {
                $unit = $row->unitTujuan ? $row->unitTujuan->nama_unit : 'Semua Unit';
                $pegawai = $row->pegawaiTujuan ? ('<br><small class="text-muted">PIC: ' . htmlspecialchars($row->pegawaiTujuan->nama_lengkap) . '</small>') : '';
                return '<strong>' . htmlspecialchars($unit) . '</strong>' . $pegawai;
            })
            ->addColumn('instruksi_info', function ($row) {
                $batas = $row->batas_waktu ? ('<br><small class="text-danger"><i class="fas fa-calendar-alt mr-1"></i> Batas: ' . $row->batas_waktu->format('d/m/Y') . '</small>') : '';
                $catatan = $row->catatan_disposisi ? ('<br><small class="text-muted font-italic">"' . htmlspecialchars($row->catatan_disposisi) . '"</small>') : '';
                return '<strong>' . htmlspecialchars($row->instruksi) . '</strong>' . $batas . $catatan;
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';

                // Tombol Lihat Surat Scan
                if ($row->suratMasuk && $row->suratMasuk->file_url) {
                    $btn .= '<a href="' . $row->suratMasuk->file_url . '" target="_blank" class="btn btn-outline-danger" title="Lihat Surat Masuk PDF"><i class="fas fa-file-pdf mr-1"></i> Surat</a>';
                }

                // Tombol Terima Disposisi (jika masih status menunggu)
                if ($row->status_tindak_lanjut === 'menunggu') {
                    $btn .= '<button type="button" class="btn btn-warning btn-terima-disp" data-url="' . route('admin.disposisi-unit.terima', $row->id) . '" title="Konfirmasi Terima Disposisi"><i class="fas fa-check mr-1"></i> Terima</button>';
                }

                // Tombol Tindak Lanjut / Penyelesaian
                if ($row->status_tindak_lanjut !== 'selesai') {
                    $btn .= '<button type="button" class="btn btn-primary btn-tindak-lanjut" data-url="' . route('admin.disposisi-unit.tindak-lanjut-modal', $row->id) . '" title="Tindak Lanjuti / Selesaikan"><i class="fas fa-edit mr-1"></i> Tindak Lanjut</button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-success btn-tindak-lanjut" data-url="' . route('admin.disposisi-unit.tindak-lanjut-modal', $row->id) . '" title="Lihat Hasil Tindak Lanjut"><i class="fas fa-check-double mr-1"></i> Selesai</button>';
                }

                if ($row->file_tindak_lanjut_url) {
                    $btn .= '<a href="' . $row->file_tindak_lanjut_url . '" target="_blank" class="btn btn-outline-info" title="Unduh Bukti Tindak Lanjut"><i class="fas fa-paperclip mr-1"></i> Bukti</a>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['surat_info', 'unit_info', 'instruksi_info', 'status_badge', 'aksi'])
            ->make(true);
    }

    /**
     * Terima Disposisi oleh Unit
     */
    public function terimaDisposisi($id)
    {
        $disp = DisposisiSuratMasuk::with('suratMasuk')->findOrFail($id);
        
        $disp->update([
            'status_tindak_lanjut' => 'diterima',
        ]);

        if ($disp->suratMasuk && in_array($disp->suratMasuk->status, ['terdaftar', 'didisposisi'])) {
            $disp->suratMasuk->update(['status' => 'proses_unit']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Disposisi berhasil diterima. Silakan proses dan tindak lanjuti surat ini.',
        ]);
    }

    /**
     * Modal Form Tindak Lanjut Disposisi
     */
    public function tindakLanjutModal($id)
    {
        $disp = DisposisiSuratMasuk::with([
            'suratMasuk',
            'unitTujuan',
            'pegawaiTujuan',
            'dariUser',
            'userPenyelesai'
        ])->findOrFail($id);

        return view('admin::disposisi-unit.tindak_lanjut_modal', compact('disp'));
    }

    /**
     * Simpan Tindak Lanjut & Penyelesaian oleh Unit Kerja
     */
    public function storeTindakLanjut(Request $request, $id)
    {
        $disp = DisposisiSuratMasuk::with('suratMasuk')->findOrFail($id);

        $request->validate([
            'status_tindak_lanjut'  => 'required|in:diproses,selesai',
            'catatan_tindak_lanjut' => 'required|string|min:5',
            'file_tindak_lanjut'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip,rar|max:15360',
        ]);

        $filePath = $disp->file_tindak_lanjut;
        if ($request->hasFile('file_tindak_lanjut')) {
            $file = $request->file('file_tindak_lanjut');
            $ext = $file->getClientOriginalExtension();
            $filename = "TindakLanjut_Disp{$disp->id}_" . time() . ".{$ext}";
            $file->storeAs('private/disposisi_tindak_lanjut', $filename);
            $filePath = 'private/disposisi_tindak_lanjut/' . $filename;
        }

        $isSelesai = $request->status_tindak_lanjut === 'selesai';

        $disp->update([
            'status_tindak_lanjut'  => $request->status_tindak_lanjut,
            'catatan_tindak_lanjut' => $request->catatan_tindak_lanjut,
            'file_tindak_lanjut'    => $filePath,
            'diselesaikan_oleh'     => Auth::id(),
            'tgl_selesai'           => $isSelesai ? now() : null,
        ]);

        // Cek status keseluruhan surat masuk
        if ($disp->suratMasuk) {
            $sm = $disp->suratMasuk;
            if ($isSelesai) {
                // Periksa apakah seluruh disposisi pada surat masuk ini sudah selesai
                $unfinishedCount = DisposisiSuratMasuk::where('surat_masuk_id', $sm->id)
                    ->where('status_tindak_lanjut', '!=', 'selesai')
                    ->count();

                if ($unfinishedCount === 0) {
                    $sm->update(['status' => 'selesai']);
                } else {
                    $sm->update(['status' => 'proses_unit']);
                }
            } else {
                $sm->update(['status' => 'proses_unit']);
            }
        }

        $msg = $isSelesai 
            ? 'Disposisi telah berhasil diselesaikan oleh unit kerja.' 
            : 'Tindak lanjut disposisi berhasil disimpan dengan status sedang diproses.';

        return response()->json([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Secure Stream File Bukti Tindak Lanjut
     */
    public function streamTindakLanjut($id)
    {
        if (!Auth::check()) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $disp = DisposisiSuratMasuk::findOrFail($id);
        $filePath = $disp->file_tindak_lanjut;

        if (!$filePath) {
            abort(404, 'Berkas tindak lanjut tidak ditemukan.');
        }

        $candidates = [
            storage_path('app/' . ltrim($filePath, '/')),
            storage_path('app/public/' . str_replace('storage/', '', ltrim($filePath, '/'))),
            public_path(ltrim($filePath, '/')),
        ];

        $fullPath = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            abort(404, 'File bukti fisik tidak ditemukan di server.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $filename = 'Bukti_TindakLanjut_Disp' . $disp->id . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
