<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SuratMasuk;
use App\Models\DisposisiSuratMasuk;
use App\Models\MasterUnit;
use App\Models\DataDosenTendik;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class SuratMasukController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:persuratan-sdm');
    }

    /**
     * Halaman Utama Registrasi Surat Masuk Eksternal (SIKD)
     */
    public function index()
    {
        $masterUnits = MasterUnit::orderBy('nama_unit', 'asc')->get();
        return view('admin::surat-masuk.index', [
            'title'       => 'Registrasi Surat Masuk & SIKD',
            'menuIcon'    => 'fas fa-inbox',
            'masterUnits' => $masterUnits,
        ]);
    }

    /**
     * DataTables Surat Masuk
     */
    public function dataTable(Request $request)
    {
        $query = SuratMasuk::with(['disposisis.unitTujuan', 'creator'])
            ->orderBy('id', 'desc');

        if ($request->filled('sifat_surat')) {
            $query->where('sifat_surat', $request->sifat_surat);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('sifat_badge', function ($row) {
                return $row->sifat_badge;
            })
            ->addColumn('tgl_surat_formatted', function ($row) {
                return $row->tgl_surat ? $row->tgl_surat->format('d/m/Y') : '-';
            })
            ->addColumn('tgl_diterima_formatted', function ($row) {
                return $row->tgl_diterima ? $row->tgl_diterima->format('d/m/Y') : '-';
            })
            ->addColumn('ringkasan_disposisi', function ($row) {
                if ($row->disposisis->isEmpty()) {
                    return '<span class="text-muted font-italic"><i class="fas fa-minus-circle mr-1"></i> Belum didisposisikan</span>';
                }
                $list = '<ul class="list-unstyled mb-0 small">';
                foreach ($row->disposisis as $disp) {
                    $unitName = $disp->unitTujuan->nama_unit ?? 'Unit Umum';
                    $statusBadge = match($disp->status_tindak_lanjut) {
                        'menunggu' => '<span class="badge badge-secondary">Menunggu</span>',
                        'diterima' => '<span class="badge badge-info">Diterima</span>',
                        'diproses' => '<span class="badge badge-warning">Diproses</span>',
                        'selesai'  => '<span class="badge badge-success">Selesai</span>',
                        default    => '<span class="badge badge-light">' . $disp->status_tindak_lanjut . '</span>',
                    };
                    $list .= "<li><strong>{$unitName}</strong>: {$disp->instruksi} ({$statusBadge})</li>";
                }
                $list .= '</ul>';
                return $list;
            })
            ->addColumn('aksi', function ($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<button type="button" class="btn btn-outline-info btn-detail-sm" data-url="' . route('admin.surat-masuk.detail', $row->id) . '" title="Lihat Detail & Timeline Disposisi"><i class="fas fa-eye mr-1"></i> Detail</button>';
                
                if ($row->status !== 'selesai') {
                    $btn .= '<button type="button" class="btn btn-primary btn-disposisi-sm" data-url="' . route('admin.surat-masuk.disposisi-modal', $row->id) . '" title="Teruskan Disposisi ke Unit"><i class="fas fa-share mr-1"></i> Disposisi</button>';
                }

                if ($row->file_url) {
                    $btn .= '<a href="' . $row->file_url . '" target="_blank" class="btn btn-outline-danger" title="Lihat Scan Surat PDF"><i class="fas fa-file-pdf mr-1"></i> File</a>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'sifat_badge', 'ringkasan_disposisi', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Form Registrasi Surat Masuk Baru
     */
    public function createModal()
    {
        $noAgendaOtomatis = SuratMasuk::generateNoAgenda();
        return view('admin::surat-masuk.form_modal', compact('noAgendaOtomatis'));
    }

    /**
     * Simpan Registrasi Surat Masuk Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_surat_asal'     => 'required|string|max:100',
            'pengirim_instansi' => 'required|string|max:255',
            'tgl_surat'         => 'required|date',
            'tgl_diterima'      => 'required|date',
            'perihal'           => 'required|string|max:255',
            'sifat_surat'       => 'required|in:biasa,penting,segera,rahasia',
            'file_surat'        => 'required|file|mimes:pdf|max:15360',
            'ringkasan_isi'     => 'nullable|string',
        ]);

        $file = $request->file('file_surat');
        $cleanPerihal = substr(preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->perihal), 0, 30);
        $filename = "SuratMasuk_" . time() . "_{$cleanPerihal}.pdf";
        $file->storeAs('private/surat_masuk', $filename);
        $filePath = 'private/surat_masuk/' . $filename;

        $surat = SuratMasuk::create([
            'no_agenda'         => SuratMasuk::generateNoAgenda(),
            'no_surat_asal'     => $request->no_surat_asal,
            'pengirim_instansi' => $request->pengirim_instansi,
            'tgl_surat'         => $request->tgl_surat,
            'tgl_diterima'      => $request->tgl_diterima,
            'perihal'           => $request->perihal,
            'ringkasan_isi'     => $request->ringkasan_isi,
            'sifat_surat'       => $request->sifat_surat,
            'file_surat'        => $filePath,
            'file_size'         => $file->getSize(),
            'status'            => 'terdaftar',
            'created_by'        => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Surat masuk no. agenda {$surat->no_agenda} dari {$surat->pengirim_instansi} berhasil didaftarkan.",
        ]);
    }

    /**
     * Modal Detail Surat Masuk & Riwayat Disposisi
     */
    public function detailModal($id)
    {
        $surat = SuratMasuk::with([
            'disposisis.unitTujuan',
            'disposisis.pegawaiTujuan',
            'disposisis.dariUser',
            'disposisis.userPenyelesai',
            'creator'
        ])->findOrFail($id);

        return view('admin::surat-masuk.detail_modal', compact('surat'));
    }

    /**
     * Modal Disposisi Surat Masuk ke Unit Kerja
     */
    public function disposisiModal($id)
    {
        $surat = SuratMasuk::findOrFail($id);
        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();
        $pegawais = DataDosenTendik::with('unit')
            ->where('is_active', 1)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nik', 'unit_id']);

        return view('admin::surat-masuk.disposisi_modal', compact('surat', 'units', 'pegawais'));
    }

    /**
     * Simpan Disposisi Surat Masuk
     */
    public function storeDisposisi(Request $request, $id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $request->validate([
            'ke_unit_id'        => 'required|exists:master_units,id',
            'ke_pegawai_id'     => 'nullable|exists:data_dosen_tendiks,id',
            'instruksi'         => 'required|string|max:255',
            'catatan_disposisi' => 'nullable|string',
            'batas_waktu'       => 'nullable|date',
        ]);

        $disposisi = DisposisiSuratMasuk::create([
            'surat_masuk_id'       => $surat->id,
            'dari_user_id'         => Auth::id(),
            'ke_unit_id'           => $request->ke_unit_id,
            'ke_pegawai_id'        => $request->ke_pegawai_id,
            'instruksi'            => $request->instruksi,
            'catatan_disposisi'    => $request->catatan_disposisi,
            'tgl_disposisi'        => now(),
            'batas_waktu'          => $request->batas_waktu,
            'status_tindak_lanjut' => 'menunggu',
        ]);

        // Update status surat masuk ke 'didisposisi' jika masih 'terdaftar'
        if ($surat->status === 'terdaftar') {
            $surat->update(['status' => 'didisposisi']);
        }

        $unit = MasterUnit::find($request->ke_unit_id);
        $namaUnit = $unit ? $unit->nama_unit : 'unit kerja terkait';

        return response()->json([
            'success' => true,
            'message' => "Surat berhasil didisposisikan ke {$namaUnit} dengan instruksi: {$request->instruksi}.",
        ]);
    }

    /**
     * Secure Stream Scan Surat Masuk
     */
    public function streamFile($id)
    {
        if (!Auth::check()) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $surat = SuratMasuk::findOrFail($id);
        $filePath = $surat->file_surat;

        if (!$filePath) {
            abort(404, 'Berkas fisik tidak terdaftar.');
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
            abort(404, 'File scan fisik surat tidak ditemukan di server.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/pdf';
        $filename = 'Scan_' . ($surat->no_agenda ?: $surat->id) . '.pdf';

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
