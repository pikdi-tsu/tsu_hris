<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\RkatPeriode;
use Illuminate\Http\Request;

class RkatPeriodeController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Tampilan Daftar Periode Tahun Anggaran RKAT
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $query = RkatPeriode::withCount('pengajuans')->orderBy('tahun_anggaran', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tahun_anggaran', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        $periodes = $query->paginate(10)->withQueryString();

        return view('admin::rkat.periode.index', [
            'title' => 'Periode Anggaran RKAT',
            'periodes' => $periodes,
            'search' => $request->search ?? '',
        ]);
    }

    /**
     * Simpan Periode Anggaran Baru
     */
    public function store(Request $request)
    {
        $this->guard('create', 'admin:rkat');

        $request->validate([
            'tahun_anggaran' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:Aktif,Draft,Arsip',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            RkatPeriode::create([
                'tahun_anggaran' => $request->tahun_anggaran,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return back()->with('success', 'Periode Anggaran ' . $request->tahun_anggaran . ' berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menambahkan periode: ' . $e->getMessage());
        }
    }

    /**
     * Update Periode Anggaran
     */
    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');

        $periode = RkatPeriode::findOrFail($id);

        $request->validate([
            'tahun_anggaran' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:Aktif,Draft,Arsip',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $periode->update([
                'tahun_anggaran' => $request->tahun_anggaran,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return back()->with('success', 'Periode Anggaran berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui periode: ' . $e->getMessage());
        }
    }

    /**
     * Ubah Status Periode (Aktif / Arsip / Draft)
     */
    public function toggleStatus(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');

        try {
            $periode = RkatPeriode::findOrFail($id);
            $newStatus = $request->input('status', ($periode->status === 'Aktif' ? 'Arsip' : 'Aktif'));
            $periode->update(['status' => $newStatus]);

            return back()->with('success', 'Status Periode ' . $periode->tahun_anggaran . ' berhasil diubah menjadi ' . $newStatus . '.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Periode (jika belum ada pengajuan)
     */
    public function destroy($id)
    {
        $this->guard('delete', 'admin:rkat');

        try {
            $periode = RkatPeriode::withCount('pengajuans')->findOrFail($id);
            if ($periode->pengajuans_count > 0) {
                return back()->with('error', 'Periode ini tidak dapat dihapus karena sudah memiliki data pengajuan RKAT.');
            }

            $periode->delete();
            return back()->with('success', 'Periode anggaran berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus periode: ' . $e->getMessage());
        }
    }
}
