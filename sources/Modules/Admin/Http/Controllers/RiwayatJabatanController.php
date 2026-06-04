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

class RiwayatJabatanController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:riwayat-jabatan');
    }

    public function index()
    {
        $karyawans = DataDosenTendik::orderBy('nama', 'asc')->get();
        return view('admin::riwayat-jabatan.index', [
            'title' => 'Manajemen Riwayat Jabatan',
            'karyawans' => $karyawans
        ]);
    }

    public function datatable(Request $request)
    {
        $query = RiwayatJabatan::with(['dataDosenTendik', 'jabatanStruktural', 'jabatanFungsional', 'pangkatGolongan'])
            ->orderBy('created_at', 'desc');

        if ($request->has('karyawan_id') && $request->karyawan_id != '') {
            $query->where('data_dosen_tendik_id', $request->karyawan_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai', function ($row) {
                $nama = $row->dataDosenTendik->nama ?? 'Unknown';
                $nik = $row->dataDosenTendik->nik ?? '-';
                return "<strong>{$nama}</strong><br><small class='text-muted'>NIK: {$nik}</small>";
            })
            ->addColumn('tipe_jabatan', function ($row) {
                if ($row->tipe_jabatan === 'struktural') {
                    return '<span class="badge badge-dark">STRUKTURAL</span>';
                }
                return '<span class="badge badge-info">FUNGSIONAL</span>';
            })
            ->addColumn('jabatan', function ($row) {
                if ($row->tipe_jabatan === 'struktural') {
                    return $row->jabatanStruktural->nama_jabatan ?? '-';
                }
                $nama = $row->jabatanFungsional->nama_jabatan ?? '-';
                if ($row->pangkatGolongan) {
                    $nama .= ' <br><small class="text-muted">(' . $row->pangkatGolongan->nama_pangkat . ' - Gol. ' . $row->pangkatGolongan->golongan . ')</small>';
                }
                return $nama;
            })
            ->addColumn('masa_jabatan', function ($row) {
                $mulai = $row->tgl_mulai ? Carbon::parse($row->tgl_mulai)->format('d M Y') : '-';
                $selesai = $row->tgl_selesai ? Carbon::parse($row->tgl_selesai)->format('d M Y') : 'Sekarang';
                $durasi = $row->lama_menjabat_bulan ? $row->lama_menjabat_bulan . ' Bln' : '< 1 Bln';
                return "{$mulai} &mdash; {$selesai}<br><small class='text-muted'><i class='far fa-clock'></i> {$durasi}</small>";
            })
            ->addColumn('aksi', function ($row) {
                $editUrl = route('admin.riwayat-jabatan.edit', $row->id);
                $deleteUrl = route('admin.riwayat-jabatan.destroy', $row->id);
                $token = csrf_token();

                $btnEdit = '<button type="button" class="btn btn-sm btn-warning btn-edit text-dark mx-1" data-url="'.$editUrl.'" title="Edit Riwayat"><i class="fas fa-pencil-alt"></i></button>';
                
                $btnDelete = '
                    <form action="'.$deleteUrl.'" method="POST" style="display:inline-block; margin: 0;" class="mx-1">
                        <input type="hidden" name="_token" value="'.$token.'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" class="btn btn-sm btn-danger btn-delete-riwayat" title="Hapus Riwayat">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                ';

                return '<div class="d-flex justify-content-center align-items-center">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['pegawai', 'tipe_jabatan', 'jabatan', 'masa_jabatan', 'aksi'])
            ->make(true);
    }

    public function edit($id)
    {
        $this->guard('edit');
        $riwayat = RiwayatJabatan::with(['dataDosenTendik', 'jabatanStruktural', 'jabatanFungsional'])->findOrFail($id);
        return view('admin::data-karyawan.edit_riwayat_modal', compact('riwayat')); // Reusing the modal view we created earlier
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit');
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

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat jabatan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->guard('delete');
        
        try {
            $riwayat = RiwayatJabatan::findOrFail($id);
            $riwayat->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat jabatan berhasil dihapus permanen.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportGlobal(Request $request)
    {
        $this->guard('view');
        $karyawanId = $request->get('karyawan_id', null);
        
        if ($karyawanId) {
            $karyawan = DataDosenTendik::findOrFail($karyawanId);
            $fileName = 'Riwayat_Jabatan_' . str_replace(' ', '_', $karyawan->nama) . '_' . date('Ymd_His') . '.xlsx';
        } else {
            $fileName = 'Rekap_Global_Riwayat_Jabatan_' . date('Ymd_His') . '.xlsx';
        }
        
        return Excel::download(new RiwayatJabatanExport($karyawanId), $fileName);
    }
}
