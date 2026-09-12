<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\DataDosenTendik;
use App\Models\MasterCuti;
use App\Models\CutiKaryawan;
use App\Models\IzinKaryawan;
use Modules\System\Models\MenuSidebar;
use Illuminate\Support\Str;

class RiwayatIzinCutiController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-cuti');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $menuData = MenuSidebar::where('route', 'admin.riwayat-izincuti.index')->first();
        return view('admin::riwayat-izincuti.index', [
            'title'    => 'Data Riwayat Izin & Cuti',
            'menuIcon' => $menuData->icon ?? 'fas fa-history',
        ]);
    }

    /**
     * Helper untuk memformat badge status approval atasan / SDM
     */
    private function formatApprovalBadge($status, $approver, $alasan = null): string
    {
        $approverName = $approver ? htmlspecialchars($approver->nama) : '-';

        if ($status === 'approved') {
            $badge = '<span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#dcfce7;color:#166534;"><i class="fas fa-check-circle" style="font-size:.65rem;"></i> Disetujui</span>';
        } elseif ($status === 'rejected') {
            $badge = '<span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#fee2e2;color:#991b1b;"><i class="fas fa-times-circle" style="font-size:.65rem;"></i> Ditolak</span>';
        } else {
            $badge = '<span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#fef9c3;color:#854d0e;"><i class="fas fa-hourglass-half" style="font-size:.65rem;"></i> Menunggu</span>';
        }

        $html = '<div class="d-flex flex-column align-items-start" style="gap:.25rem;">';
        $html .= $badge;
        if ($approverName !== '-') {
            $html .= '<div style="font-size:.74rem;color:#64748b;display:inline-flex;align-items:center;gap:.25rem;"><i class="fas fa-user-check" style="font-size:.65rem;color:#094b54;"></i> ' . $approverName . '</div>';
        }
        if ($status === 'rejected' && !empty($alasan)) {
            $cleanAlasan = htmlspecialchars(strip_tags($alasan));
            $html .= '<div style="font-size:.72rem;color:#b91c1c;background:#fff1f2;padding:.15rem .45rem;border-radius:4px;border:1px solid #fecdd3;max-width:200px;white-space:normal;line-height:1.2;" title="' . $cleanAlasan . '"><i class="fas fa-exclamation-circle mr-1"></i>' . Str::limit($cleanAlasan, 45) . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    public function datatablecuti()
    {
        $data = CutiKaryawan::with(['masterCuti', 'user', 'atasan', 'hrd'])
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', function ($data) {
                if (!$data->user) {
                    return '<span class="text-muted">-</span>';
                }
                $nama = htmlspecialchars($data->user->nama);
                $nik = htmlspecialchars($data->user->nik ?? '-');
                return '<div class="d-flex flex-column" style="gap:.15rem;">'
                     . '<span style="font-weight:700;color:#0f172a;font-size:.85rem;">' . $nama . '</span>'
                     . '<span style="font-size:.74rem;color:#64748b;"><i class="fas fa-id-badge mr-1" style="color:#094b54;"></i>' . $nik . '</span>'
                     . '</div>';
            })
            ->addColumn('jeniscuti', function ($data) {
                $jenis = $data->masterCuti ? htmlspecialchars($data->masterCuti->jeniscuti) : '-';
                return '<span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .6rem;border-radius:6px;font-size:.75rem;font-weight:600;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;">'
                     . '<i class="fas fa-calendar-check" style="font-size:.65rem;"></i> ' . $jenis . '</span>';
            })
            ->addColumn('tanggalcuti', function ($data) {
                $mulai = Carbon::parse($data->tanggalmulai);
                $selesai = Carbon::parse($data->tanggalselesai);

                if ($mulai->format('Y-m') == $selesai->format('Y-m')) {
                    $tanggal = $mulai->translatedFormat('d') . '–' . $selesai->translatedFormat('d M Y');
                } else {
                    $tanggal = $mulai->translatedFormat('d M Y') . ' - ' . $selesai->translatedFormat('d M Y');
                }

                $hari = CutiKaryawan::hitungHariEfektif($data->tanggalmulai, $data->tanggalselesai);
                $hariText = $hari > 0 ? (' <span class="badge badge-light" style="font-size:.72rem;color:#094b54;background:#e6f4f6;border:1px solid #b2dfdb;">' . $hari . ' hari</span>') : '';

                return '<div style="font-size:.82rem;font-weight:600;color:#1e293b;white-space:nowrap;"><i class="far fa-calendar-alt mr-1" style="color:#094b54;"></i>' . $tanggal . $hariText . '</div>';
            })
            ->addColumn('keterangan', function ($data) {
                $ket = $data->keterangan ? htmlspecialchars($data->keterangan) : '-';
                return '<div style="max-width:240px;font-size:.8rem;color:#475569;line-height:1.35;white-space:normal;" title="' . $ket . '">'
                     . Str::limit($ket, 70) . '</div>';
            })
            ->addColumn('approvalatasan', function ($data) {
                return $this->formatApprovalBadge($data->statusatasan, $data->atasan, $data->alasanatasan);
            })
            ->addColumn('approvalsdm', function ($data) {
                return $this->formatApprovalBadge($data->statushrd, $data->hrd, $data->alasanhrd);
            })
            ->addColumn('file_bukti', function ($data) {
                if ($data->file_bukti && $data->file_bukti_url) {
                    return '<a href="' . $data->file_bukti_url . '" target="_blank" download class="btn btn-xs btn-outline-info rounded-pill px-2" title="Unduh / Buka Bukti"><i class="fas fa-paperclip mr-1"></i> Bukti</a>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->rawColumns(['nama', 'jeniscuti', 'tanggalcuti', 'keterangan', 'approvalatasan', 'approvalsdm', 'file_bukti'])
            ->make(true);
    }

    public function datatableizin()
    {
        $data = IzinKaryawan::with(['masterIzin', 'user', 'atasan', 'hrd'])
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama', function ($data) {
                if (!$data->user) {
                    return '<span class="text-muted">-</span>';
                }
                $nama = htmlspecialchars($data->user->nama);
                $nik = htmlspecialchars($data->user->nik ?? '-');
                return '<div class="d-flex flex-column" style="gap:.15rem;">'
                     . '<span style="font-weight:700;color:#0f172a;font-size:.85rem;">' . $nama . '</span>'
                     . '<span style="font-size:.74rem;color:#64748b;"><i class="fas fa-id-badge mr-1" style="color:#094b54;"></i>' . $nik . '</span>'
                     . '</div>';
            })
            ->addColumn('jenisizin', function ($data) {
                $jenis = $data->masterIzin ? htmlspecialchars($data->masterIzin->jenisizin) : '-';
                return '<span style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .6rem;border-radius:6px;font-size:.75rem;font-weight:600;background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;">'
                     . '<i class="fas fa-tag" style="font-size:.65rem;"></i> ' . $jenis . '</span>';
            })
            ->addColumn('tanggalizin', function ($data) {
                $mulai = Carbon::parse($data->tanggalmulai);
                $selesai = Carbon::parse($data->tanggalselesai);

                if ($mulai->format('Y-m') == $selesai->format('Y-m')) {
                    $tanggal = $mulai->translatedFormat('d') . '–' . $selesai->translatedFormat('d M Y');
                } else {
                    $tanggal = $mulai->translatedFormat('d M Y') . ' - ' . $selesai->translatedFormat('d M Y');
                }

                $hari = IzinKaryawan::hitungHariEfektif($data->tanggalmulai, $data->tanggalselesai);
                $hariText = $hari > 0 ? (' <span class="badge badge-light" style="font-size:.72rem;color:#094b54;background:#e6f4f6;border:1px solid #b2dfdb;">' . $hari . ' hari</span>') : '';

                return '<div style="font-size:.82rem;font-weight:600;color:#1e293b;white-space:nowrap;"><i class="far fa-calendar-alt mr-1" style="color:#094b54;"></i>' . $tanggal . $hariText . '</div>';
            })
            ->addColumn('keterangan', function ($data) {
                $ket = $data->keterangan ? htmlspecialchars($data->keterangan) : '-';
                return '<div style="max-width:240px;font-size:.8rem;color:#475569;line-height:1.35;white-space:normal;" title="' . $ket . '">'
                     . Str::limit($ket, 70) . '</div>';
            })
            ->addColumn('approvalatasan', function ($data) {
                return $this->formatApprovalBadge($data->statusatasan, $data->atasan, $data->alasanatasan);
            })
            ->addColumn('approvalsdm', function ($data) {
                return $this->formatApprovalBadge($data->statushrd, $data->hrd, $data->alasanhrd);
            })
            ->addColumn('file_bukti', function ($data) {
                if ($data->file_bukti && $data->file_bukti_url) {
                    return '<a href="' . $data->file_bukti_url . '" target="_blank" download class="btn btn-xs btn-outline-info rounded-pill px-2" title="Unduh / Buka Bukti"><i class="fas fa-paperclip mr-1"></i> Bukti</a>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->rawColumns(['nama', 'jenisizin', 'tanggalizin', 'keterangan', 'approvalatasan', 'approvalsdm', 'file_bukti'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-cuti');
        return view('admin::master-data.cuti.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-cuti');

        $request->validate([
            'jeniscuti'     => 'required|string|max:255',
            'durasicuti'    => 'required|integer',
            'minimalhari'   => 'required|integer',
        ]);

        try {
            MasterCuti::create([
                'jeniscuti'   => $request->jeniscuti,
                'durasicuti'  => $request->durasicuti,
                'minimalhari' => $request->minimalhari,
                'is_active'   => '1',
                'created_at'  => date("Y-m-d H:i:s"),
                'created_by'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
                // 'updated_by'  => auth()->check() ? auth()->user()->name : 'System',
            ]);

            return back()->with('success', 'Master Cuti Berhasil Ditambahkan.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_STORE_FAIL]',
                'Gagal menyimpan master cuti.',
                'Create Master Cuti.',
                $request
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);
        return view('admin::master-data.cuti.edit_modal', compact('cuti'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);

        $request->validate([
            'jeniscuti'   => 'required|string|max:255',
            'durasicuti'  => 'required|integer',
            'minimalhari' => 'required|integer',
            'is_active'   => 'required|in:0,1'
        ]);

        try {
            $cuti->update([
                'jeniscuti'   => $request->jeniscuti,
                'durasicuti'  => $request->durasicuti,
                'minimalhari' => $request->minimalhari,
                'is_active'   => $request->is_active,
                'updated_at'  => date("Y-m-d H:i:s"),
                'updated_by'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
            ]);

            return back()->with('success', 'Master Cuti Berhasil Diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_UPD_FAIL]',
                'Gagal menyimpan perubahan master cuti.',
                "Update Master Cuti ID: $id.",
                $request
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);

        try {
            $cuti->delete();
            return back()->with('success', 'Master Cuti Berhasil Dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_DEL_FAIL]',
                'Gagal menghapus master cuti karena masih digunakan atau kesalahan sistem.',
                "Delete Master Cuti ID: $id."
            );
        }
    }
}
