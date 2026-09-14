<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\RiwayatJabatan;
use App\Models\DataDosenTendik;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RiwayatJabatanExport;
use App\Traits\ApiResponseTrait;
use App\Services\TsuErrorHandlerService;
use Modules\System\Models\MenuSidebar;

class RiwayatJabatanController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:riwayat-jabatan');
    }

    public function index()
    {
        $this->guard('view', 'admin:riwayat-jabatan');

        $karyawans = DataDosenTendik::orderBy('nama', 'asc')->get();
        
        $menuData = MenuSidebar::where('route', 'admin.riwayat-jabatan.index')->first();
        $menuIcon = $menuData->icon ?? 'fas fa-history';
        $title = $menuData->name ?? 'Riwayat Jabatan';

        $totalRiwayat = RiwayatJabatan::count();
        $totalStruktural = RiwayatJabatan::where('tipe_jabatan', 'struktural')->count();
        $totalFungsional = RiwayatJabatan::where('tipe_jabatan', 'fungsional')->count();
        $totalPegawai = RiwayatJabatan::distinct('data_dosen_tendik_id')->count('data_dosen_tendik_id');

        return view('admin::riwayat-jabatan.index', [
            'title'           => $title,
            'menuIcon'        => $menuIcon,
            'karyawans'       => $karyawans,
            'totalRiwayat'    => $totalRiwayat,
            'totalStruktural' => $totalStruktural,
            'totalFungsional' => $totalFungsional,
            'totalPegawai'    => $totalPegawai
        ]);
    }

    public function datatable(Request $request)
    {
        $this->guard('view', 'admin:riwayat-jabatan');

        $query = RiwayatJabatan::with(['dataDosenTendik', 'jabatanStruktural', 'jabatanFungsional', 'pangkatGolongan'])
            ->orderBy('created_at', 'desc');

        if ($request->has('karyawan_id') && $request->karyawan_id != '') {
            $query->where('data_dosen_tendik_id', $request->karyawan_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai', function ($row) {
                $nama = $row->dataDosenTendik ? htmlspecialchars($row->dataDosenTendik->nama) : 'Unknown';
                $nik = $row->dataDosenTendik && $row->dataDosenTendik->nik 
                    ? '<small class="text-muted d-block mt-1"><i class="fas fa-id-card mr-1"></i>' . htmlspecialchars($row->dataDosenTendik->nik) . '</small>' 
                    : '';
                return "<div><strong class='text-dark' style='font-size:0.9rem;'>{$nama}</strong>{$nik}</div>";
            })
            ->addColumn('tipe_jabatan', function ($row) {
                if ($row->tipe_jabatan === 'struktural') {
                    return '<span class="badge badge-dark" style="font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.76rem;"><i class="fas fa-sitemap mr-1"></i>STRUKTURAL</span>';
                }
                return '<span class="badge badge-info" style="font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.76rem;"><i class="fas fa-user-tie mr-1"></i>FUNGSIONAL</span>';
            })
            ->addColumn('jabatan', function ($row) {
                if ($row->tipe_jabatan === 'struktural') {
                    return '<strong style="font-size:0.88rem;">' . htmlspecialchars($row->jabatanStruktural->nama_jabatan ?? '-') . '</strong>';
                }
                $nama = '<strong style="font-size:0.88rem;">' . htmlspecialchars($row->jabatanFungsional->nama_jabatan ?? '-') . '</strong>';
                if ($row->pangkatGolongan) {
                    $nama .= '<br><small class="text-muted"><i class="fas fa-medal mr-1" style="color:var(--tsu-primary,#094b54);"></i>' . htmlspecialchars($row->pangkatGolongan->nama_pangkat . ' - Gol. ' . $row->pangkatGolongan->golongan) . '</small>';
                }
                return $nama;
            })
            ->addColumn('masa_jabatan', function ($row) {
                $mulai = $row->tgl_mulai ? Carbon::parse($row->tgl_mulai)->translatedFormat('d M Y') : '-';
                $selesai = $row->tgl_selesai ? Carbon::parse($row->tgl_selesai)->translatedFormat('d M Y') : 'Sekarang';
                $durasi = $row->lama_menjabat_bulan ? $row->lama_menjabat_bulan . ' Bln' : '< 1 Bln';
                return "<div><span style='font-weight:600;font-size:0.85rem;'>{$mulai}</span> &mdash; <span style='font-weight:600;font-size:0.85rem;'>{$selesai}</span></div><div class='mt-1'><span class='badge badge-light border text-muted' style='font-size:0.75rem;'><i class='fas fa-clock mr-1' style='color:var(--tsu-primary,#094b54);'></i>{$durasi}</span></div>";
            })
            ->addColumn('keterangan', function ($row) {
                return $row->keterangan ? htmlspecialchars($row->keterangan) : '<span class="text-muted font-italic">-</span>';
            })
            ->addColumn('aksi', function ($row) {
                $editUrl = route('admin.riwayat-jabatan.edit', $row->id);
                $deleteUrl = route('admin.riwayat-jabatan.destroy', $row->id);
                $token = csrf_token();

                $btnEdit = '<button type="button" class="btn btn-sm btn-warning btn-edit text-dark mr-1" data-url="'.$editUrl.'" title="Edit Riwayat" style="border-radius: 6px; font-size: 0.78rem; padding: 0.25rem 0.55rem;"><i class="fas fa-pencil-alt"></i></button>';
                
                $btnDelete = '
                    <form action="'.$deleteUrl.'" method="POST" style="display:inline-block; margin: 0;">
                        <input type="hidden" name="_token" value="'.$token.'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" class="btn btn-sm btn-danger btn-delete-riwayat" title="Hapus Riwayat" style="border-radius: 6px; font-size: 0.78rem; padding: 0.25rem 0.55rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                ';

                return '<div class="d-flex justify-content-center align-items-center">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['pegawai', 'tipe_jabatan', 'jabatan', 'masa_jabatan', 'keterangan', 'aksi'])
            ->make(true);
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:riwayat-jabatan');
        $riwayat = RiwayatJabatan::with(['dataDosenTendik', 'jabatanStruktural', 'jabatanFungsional'])->findOrFail($id);
        return view('admin::data-karyawan.edit_riwayat_modal', compact('riwayat'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:riwayat-jabatan');
        $riwayat = RiwayatJabatan::findOrFail($id);

        $request->validate([
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'nullable|date|after_or_equal:tgl_mulai',
            'keterangan' => 'nullable|string'
        ]);

        try {
            $lamaBulan = 0;
            if ($request->tgl_selesai) {
                $tglMulai = Carbon::parse($request->tgl_mulai);
                $tglSelesai = Carbon::parse($request->tgl_selesai);
                $lamaBulan = $tglMulai->diffInMonths($tglSelesai);
            }

            $riwayat->update([
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'lama_menjabat_bulan' => $lamaBulan,
                'keterangan' => $request->keterangan
            ]);

            return $this->sendSuccess('Data riwayat jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_RIWAYAT_UPD_FAIL]', 
                'Gagal memperbarui riwayat.', 
                "Update Riwayat ID: $id."
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:riwayat-jabatan');
        
        try {
            $riwayat = RiwayatJabatan::findOrFail($id);
            $riwayat->delete();
            
            return $this->sendSuccess('Data riwayat jabatan berhasil dihapus permanen.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_RIWAYAT_DEL_FAIL]', 
                'Gagal menghapus riwayat.', 
                "Delete Riwayat ID: $id."
            );
        }
    }

    public function exportGlobal(Request $request)
    {
        $this->guard('view', 'admin:riwayat-jabatan');
        $karyawanId = $request->get('karyawan_id', null);
        
        $query = RiwayatJabatan::query();
        if ($karyawanId) {
            $query->where('data_dosen_tendik_id', $karyawanId);
        }
        
        if ($query->count() === 0) {
            return response('<script>alert("Gagal: Tidak ada data riwayat jabatan untuk diekspor!"); window.close();</script>');
        }

        if ($karyawanId) {
            $karyawan = DataDosenTendik::findOrFail($karyawanId);
            $fileName = 'Riwayat_Jabatan_' . str_replace(' ', '_', $karyawan->nama) . '_' . date('Ymd_His') . '.xlsx';
        } else {
            $fileName = 'Rekap_Global_Riwayat_Jabatan_' . date('Ymd_His') . '.xlsx';
        }
        
        return Excel::download(new RiwayatJabatanExport($karyawanId), $fileName);
    }
}
