<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\HrPolicy;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class HrPolicyController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('permission:admin:hr-policy:view');
    }

    /**
     * 1. Halaman Rencana Strategis (Renstra) SDM
     */
    public function renstra()
    {
        return $this->renderView('renstra', [
            'title'        => 'Rencana Strategis (Renstra) SDM',
            'subtitle'     => 'Dokumen haluan, sasaran strategis, dan roadmap pengembangan sumber daya manusia universitas',
            'menu'         => 'hr-policy-renstra',
            'menuIcon'     => 'fas fa-bullseye',
            'badgeColor'   => 'primary',
            'guideDesc'    => 'Master ini memuat dokumen Rencana Strategis (Renstra) SDM TSU, memetakan arah kebijakan jangka panjang, indikator capaian, dan roadmap kepegawaian institusi.',
        ]);
    }

    /**
     * 2. Halaman Peraturan Kepegawaian
     */
    public function peraturan()
    {
        return $this->renderView('peraturan', [
            'title'        => 'Peraturan Kepegawaian',
            'subtitle'     => 'Pedoman, Surat Keputusan (SK) Rektor, dan regulasi internal tata kelola sumber daya manusia',
            'menu'         => 'hr-policy-peraturan',
            'menuIcon'     => 'fas fa-gavel',
            'badgeColor'   => 'danger',
            'guideDesc'    => 'Master ini mendokumentasikan seluruh regulasi kepegawaian resmi, SK Rektor, tata tertib, hak dan kewajiban pegawai, serta kode etik di lingkungan universitas.',
        ]);
    }

    /**
     * 3. Halaman SOP SDM
     */
    public function sop()
    {
        return $this->renderView('sop', [
            'title'        => 'Standar Operasional Prosedur (SOP) SDM',
            'subtitle'     => 'Bagan alur kerja, prosedur baku operasional layanan kepegawaian, dan tata laksana administrasi SDM',
            'menu'         => 'hr-policy-sop',
            'menuIcon'     => 'fas fa-clipboard-list',
            'badgeColor'   => 'info',
            'guideDesc'    => 'Master ini memuat Standar Operasional Prosedur (SOP) baku untuk setiap alur administrasi SDM (perekrutan, cuti, izin, lembur, mutasi, kompensasi, dan terminasi).',
        ]);
    }

    /**
     * 4. Halaman Tugas Pokok dan Fungsi (Tupoksi)
     */
    public function tupoksi()
    {
        return $this->renderView('tupoksi', [
            'title'        => 'Tugas Pokok dan Fungsi (Tupoksi)',
            'subtitle'     => 'Uraian tugas, fungsi unit kerja, dan deskripsi jabatan struktural maupun fungsional SDM',
            'menu'         => 'hr-policy-tupoksi',
            'menuIcon'     => 'fas fa-id-badge',
            'badgeColor'   => 'success',
            'guideDesc'    => 'Master ini mengatur rincian tugas pokok, wewenang, dan fungsi kerja (Job Description) bagi tiap unit kerja dan posisi jabatan di universitas.',
        ]);
    }

    /**
     * Helper untuk render view seragam di 4 menu
     */
    private function renderView(string $kategori, array $data)
    {
        $totalDokumen = HrPolicy::kategori($kategori)->count();

        return view('admin::hr-policy.index', array_merge($data, [
            'kategori'      => $kategori,
            'totalDokumen'  => $totalDokumen,
        ]));
    }

    /**
     * DataTables Server-Side untuk seluruh kategori HR Policy
     */
    public function datatable(Request $request)
    {
        $kategori = $request->input('kategori', 'renstra');

        $query = HrPolicy::kategori($kategori)->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('judul_formatted', function ($row) {
                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.95rem;">' . e($row->judul) . '</div>';
                $html .= '<div class="small text-muted mt-1">';
                $html .= '<i class="far fa-clock mr-1"></i>Diunggah: ' . ($row->created_at ? $row->created_at->translatedFormat('d M Y, H:i') : '-');
                if ($row->created_by) {
                    $html .= ' &bull; <i class="far fa-user mr-1"></i>' . e($row->created_by);
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('file_info', function ($row) {
                if (!$row->file_path) {
                    return '<span class="text-muted small font-italic">- Tidak ada berkas -</span>';
                }

                $url = $row->file_url ?? '#';
                $iconClass = $row->file_icon;
                $size = $row->formatted_file_size;
                $fileName = $row->file_name ?: basename($row->file_path);

                return '
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <i class="' . $iconClass . ' fa-2x"></i>
                        <div>
                            <a href="' . $url . '" target="_blank" class="font-weight-bold text-primary small d-block text-truncate" style="max-width: 220px;" title="' . e($fileName) . '">
                                ' . e($fileName) . '
                            </a>
                            <small class="text-muted">' . $size . '</small>
                        </div>
                        <a href="' . $url . '" target="_blank" class="btn btn-xs btn-outline-primary ml-auto rounded-pill px-2" title="Buka / Unduh Berkas">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                ';
            })
            ->addColumn('keterangan_formatted', function ($row) {
                if (!empty($row->keterangan)) {
                    return '<div class="text-muted small text-wrap" style="max-width: 380px;">' . nl2br(e($row->keterangan)) . '</div>';
                }
                return '<span class="text-muted small font-italic">-</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';
                
                if (auth()->user()->can('admin:hr-policy:edit')) {
                    $btn .= '<button type="button" class="btn btn-outline-info btn-edit-policy" data-id="' . $row->id . '" title="Edit Dokumen"><i class="fas fa-pencil-alt"></i></button>';
                }

                if (auth()->user()->can('admin:hr-policy:delete')) {
                    $btn .= '<button type="button" class="btn btn-outline-danger btn-delete-policy" data-id="' . $row->id . '" data-judul="' . e($row->judul) . '" title="Hapus Dokumen"><i class="fas fa-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['judul_formatted', 'file_info', 'keterangan_formatted', 'action'])
            ->make(true);
    }

    /**
     * Modal Pop-up Form Tambah Dokumen (Judul, File, Keterangan)
     */
    public function create(Request $request)
    {
        $this->middleware('permission:admin:hr-policy:create');

        $kategori = $request->input('kategori', 'renstra');

        $item = new HrPolicy([
            'kategori' => $kategori,
        ]);

        return view('admin::hr-policy.form_modal', [
            'item'      => $item,
            'kategori'  => $kategori,
            'isEdit'    => false,
            'action'    => route('admin.hr-policy.store'),
        ]);
    }

    /**
     * Simpan Dokumen Baru (Judul, File, Keterangan)
     */
    public function store(Request $request)
    {
        $this->middleware('permission:admin:hr-policy:create');

        $request->validate([
            'kategori'   => 'required|in:renstra,peraturan,sop,tupoksi',
            'judul'      => 'required|string|max:255',
            'file'       => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,png|max:25600',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            $creator = $user ? ($user->name ?? $user->email) : 'Admin';

            // Upload berkas dokumen
            $uploadRes = FileUploadService::uploadDocument($request->file('file'), 'public/hr_policy/' . $request->kategori);

            $policy = HrPolicy::create([
                'kategori'   => $request->kategori,
                'judul'      => $request->judul,
                'file_path'  => $uploadRes['file_path'],
                'file_name'  => $uploadRes['original_name'],
                'file_size'  => $uploadRes['original_size'],
                'keterangan' => $request->keterangan,
                'created_by' => $creator,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Dokumen berhasil ditambahkan!',
                    'data'    => $policy,
                ]);
            }

            return back()->with('success', 'Dokumen berhasil ditambahkan!');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan dokumen: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Modal Pop-up Form Edit Dokumen
     */
    public function edit($id)
    {
        $this->middleware('permission:admin:hr-policy:edit');

        $item = HrPolicy::findOrFail($id);

        return view('admin::hr-policy.form_modal', [
            'item'      => $item,
            'kategori'  => $item->kategori,
            'isEdit'    => true,
            'action'    => route('admin.hr-policy.update', $item->id),
        ]);
    }

    /**
     * Update Dokumen (Judul, File (Opsional), Keterangan)
     */
    public function update(Request $request, $id)
    {
        $this->middleware('permission:admin:hr-policy:edit');

        $policy = HrPolicy::findOrFail($id);

        $request->validate([
            'judul'      => 'required|string|max:255',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,png|max:25600',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            $updater = $user ? ($user->name ?? $user->email) : 'Admin';

            $dataUpdate = [
                'judul'      => $request->judul,
                'keterangan' => $request->keterangan,
                'updated_by' => $updater,
            ];

            // Jika mengunggah berkas baru, ganti berkas lama
            if ($request->hasFile('file')) {
                FileUploadService::deleteFile($policy->file_path);
                $uploadRes = FileUploadService::uploadDocument($request->file('file'), 'public/hr_policy/' . $policy->kategori);
                $dataUpdate['file_path'] = $uploadRes['file_path'];
                $dataUpdate['file_name'] = $uploadRes['original_name'];
                $dataUpdate['file_size'] = $uploadRes['original_size'];
            }

            $policy->update($dataUpdate);

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Dokumen berhasil diperbarui!',
                    'data'    => $policy,
                ]);
            }

            return back()->with('success', 'Dokumen berhasil diperbarui!');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal memperbarui dokumen: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Dokumen & Berkas Fisik
     */
    public function destroy($id)
    {
        $this->middleware('permission:admin:hr-policy:delete');

        try {
            $policy = HrPolicy::findOrFail($id);
            
            // Hapus berkas fisik jika ada
            if ($policy->file_path) {
                FileUploadService::deleteFile($policy->file_path);
            }

            $policy->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Dokumen berhasil dihapus!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }
}
