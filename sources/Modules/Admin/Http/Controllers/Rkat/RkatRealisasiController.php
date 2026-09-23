<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\RkatAnggaranItem;
use App\Models\RkatPengajuan;
use App\Models\RkatPeriode;
use App\Models\RkatRealisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RkatRealisasiController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Daftar Kegiatan Disetujui untuk Pencatatan Realisasi
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $query = RkatPengajuan::with(['periode', 'unit', 'program', 'pic'])
            ->where('status', 'Disetujui')
            ->orderBy('approved_at', 'desc');

        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'LIKE', "%{$search}%");
            });
        }

        $kegiatans = $query->paginate(15)->withQueryString();

        $allDisetujui = RkatPengajuan::where('status', 'Disetujui')->get();
        $totalAnggaranDisetujui = $allDisetujui->sum('total_anggaran_disetujui');
        $totalRealisasi = $allDisetujui->sum('total_realisasi');
        $sisaAnggaran = max(0, $totalAnggaranDisetujui - $totalRealisasi);
        $avgSerapan = $totalAnggaranDisetujui > 0 ? round(($totalRealisasi / $totalAnggaranDisetujui) * 100, 1) : 0;

        $stats = [
            'total_kegiatan' => $allDisetujui->count(),
            'total_anggaran' => $totalAnggaranDisetujui,
            'total_realisasi' => $totalRealisasi,
            'sisa_anggaran' => $sisaAnggaran,
            'persen_serapan' => $avgSerapan,
        ];

        $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();

        return view('admin::rkat.realisasi.index', [
            'title' => 'Realisasi Anggaran RKAT',
            'kegiatans' => $kegiatans,
            'periodes' => $periodes,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Halaman Detail Transaksi Realisasi Suatu Kegiatan
     */
    public function show($pengajuanId)
    {
        $this->guard('view', 'admin:rkat');

        $pengajuan = RkatPengajuan::with([
            'periode', 
            'unit', 
            'program', 
            'pic.unit', 
            'anggaranItems.akun', 
            'realisasis.anggaranItem'
        ])->findOrFail($pengajuanId);

        return view('admin::rkat.realisasi.show', [
            'title' => 'Pencatatan Realisasi: ' . $pengajuan->nama_kegiatan,
            'pengajuan' => $pengajuan,
        ]);
    }

    /**
     * Simpan Transaksi Realisasi Pengeluaran Baru + Upload Bukti SPJ
     */
    public function store(Request $request, $pengajuanId)
    {
        $this->guard('create', 'admin:rkat');

        $pengajuan = RkatPengajuan::findOrFail($pengajuanId);

        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'uraian_pengeluaran' => 'required|string|max:500',
            'jumlah_realisasi' => 'required|numeric|min:1000',
            'jenis_bukti' => 'required|string|max:100',
            'nomor_bukti' => 'nullable|string|max:100',
            'anggaran_item_id' => 'nullable|uuid|exists:rkat_anggaran_items,id',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'keterangan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($request->hasFile('file_bukti')) {
                $file = $request->file('file_bukti');
                $filename = 'SPJ_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('public/rkat_bukti', $filename);
            }

            RkatRealisasi::create([
                'pengajuan_id' => $pengajuan->id,
                'anggaran_item_id' => $request->anggaran_item_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'uraian_pengeluaran' => $request->uraian_pengeluaran,
                'jumlah_realisasi' => $request->jumlah_realisasi,
                'jenis_bukti' => $request->jenis_bukti,
                'nomor_bukti' => $request->nomor_bukti,
                'file_bukti' => $filePath,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::user()->name ?? 'User',
            ]);

            // Hitung ulang total_realisasi pada pengajuan
            $newTotalRealisasi = RkatRealisasi::where('pengajuan_id', $pengajuan->id)->sum('jumlah_realisasi');
            $pengajuan->update(['total_realisasi' => $newTotalRealisasi]);

            DB::commit();
            return back()->with('success', 'Transaksi realisasi berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan realisasi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Transaksi Realisasi
     */
    public function destroy($realisasiId)
    {
        $this->guard('delete', 'admin:rkat');

        DB::beginTransaction();
        try {
            $realisasi = RkatRealisasi::findOrFail($realisasiId);
            $pengajuanId = $realisasi->pengajuan_id;

            if ($realisasi->file_bukti && Storage::exists($realisasi->file_bukti)) {
                Storage::delete($realisasi->file_bukti);
            }

            $realisasi->delete();

            // Hitung ulang total_realisasi
            $newTotalRealisasi = RkatRealisasi::where('pengajuan_id', $pengajuanId)->sum('jumlah_realisasi');
            RkatPengajuan::where('id', $pengajuanId)->update(['total_realisasi' => $newTotalRealisasi]);

            DB::commit();
            return back()->with('success', 'Catatan realisasi berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus realisasi: ' . $e->getMessage());
        }
    }
}
