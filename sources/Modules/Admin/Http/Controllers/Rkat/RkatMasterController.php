<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\RkatAkun;
use App\Models\RkatIndikator;
use App\Models\RkatKegiatanMaster;
use App\Models\RkatProgram;
use App\Models\RkatSumberDana;
use Illuminate\Http\Request;

class RkatMasterController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Tampilan Terpadu Master Data RKAT (Tabbed UI)
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $activeTab = $request->input('tab', 'program');

        $programs = RkatProgram::withCount('kegiatans')->orderBy('kode_program')->get();
        $akuns = RkatAkun::orderBy('kode_akun')->get();
        $sumberDanas = RkatSumberDana::orderBy('kode')->get();
        $indikators = RkatIndikator::orderBy('nama_indikator')->get();
        $kegiatans = RkatKegiatanMaster::with('program')->orderBy('nama_kegiatan')->get();

        return view('admin::rkat.master.index', [
            'title' => 'Master Data RKAT',
            'activeTab' => $activeTab,
            'programs' => $programs,
            'akuns' => $akuns,
            'sumberDanas' => $sumberDanas,
            'indikators' => $indikators,
            'kegiatans' => $kegiatans,
        ]);
    }

    // ========================================================
    // 1. Program Universitas
    // ========================================================
    public function storeProgram(Request $request)
    {
        $this->guard('create', 'admin:rkat');
        $request->validate([
            'kode_program' => 'required|string|max:50|unique:rkat_programs,kode_program',
            'nama_program' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            RkatProgram::create($request->only('kode_program', 'nama_program', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'program'])->with('success', 'Program berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menambahkan program: ' . $e->getMessage());
        }
    }

    public function updateProgram(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');
        $program = RkatProgram::findOrFail($id);
        $request->validate([
            'kode_program' => 'required|string|max:50|unique:rkat_programs,kode_program,' . $id,
            'nama_program' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $program->update($request->only('kode_program', 'nama_program', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'program'])->with('success', 'Program berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui program: ' . $e->getMessage());
        }
    }

    public function destroyProgram($id)
    {
        $this->guard('delete', 'admin:rkat');
        try {
            $program = RkatProgram::withCount('pengajuans')->findOrFail($id);
            if ($program->pengajuans_count > 0) {
                return back()->with('error', 'Program ini tidak dapat dihapus karena sudah dipakai dalam pengajuan RKAT.');
            }
            $program->delete();
            return redirect()->route('admin.rkat.master.index', ['tab' => 'program'])->with('success', 'Program berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ========================================================
    // 2. Akun Anggaran (COA)
    // ========================================================
    public function storeAkun(Request $request)
    {
        $this->guard('create', 'admin:rkat');
        $request->validate([
            'kode_akun' => 'required|string|max:50|unique:rkat_akuns,kode_akun',
            'nama_akun' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            RkatAkun::create($request->only('kode_akun', 'nama_akun', 'kategori', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'akun'])->with('success', 'Akun Anggaran berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function updateAkun(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');
        $akun = RkatAkun::findOrFail($id);
        $request->validate([
            'kode_akun' => 'required|string|max:50|unique:rkat_akuns,kode_akun,' . $id,
            'nama_akun' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $akun->update($request->only('kode_akun', 'nama_akun', 'kategori', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'akun'])->with('success', 'Akun Anggaran berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroyAkun($id)
    {
        $this->guard('delete', 'admin:rkat');
        try {
            $akun = RkatAkun::findOrFail($id);
            $akun->delete();
            return redirect()->route('admin.rkat.master.index', ['tab' => 'akun'])->with('success', 'Akun Anggaran berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ========================================================
    // 3. Sumber Dana
    // ========================================================
    public function storeSumberDana(Request $request)
    {
        $this->guard('create', 'admin:rkat');
        $request->validate([
            'kode' => 'required|string|max:30|unique:rkat_sumber_danas,kode',
            'nama_sumber_dana' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            RkatSumberDana::create($request->only('kode', 'nama_sumber_dana', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'sumber-dana'])->with('success', 'Sumber Dana berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function updateSumberDana(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');
        $sumber = RkatSumberDana::findOrFail($id);
        $request->validate([
            'kode' => 'required|string|max:30|unique:rkat_sumber_danas,kode,' . $id,
            'nama_sumber_dana' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $sumber->update($request->only('kode', 'nama_sumber_dana', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'sumber-dana'])->with('success', 'Sumber Dana berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroySumberDana($id)
    {
        $this->guard('delete', 'admin:rkat');
        try {
            $sumber = RkatSumberDana::findOrFail($id);
            $sumber->delete();
            return redirect()->route('admin.rkat.master.index', ['tab' => 'sumber-dana'])->with('success', 'Sumber Dana berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ========================================================
    // 4. Indikator Kinerja
    // ========================================================
    public function storeIndikator(Request $request)
    {
        $this->guard('create', 'admin:rkat');
        $request->validate([
            'nama_indikator' => 'required|string|max:255',
            'satuan_target' => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            RkatIndikator::create($request->only('nama_indikator', 'satuan_target', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'indikator'])->with('success', 'Indikator Kinerja berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function updateIndikator(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');
        $ind = RkatIndikator::findOrFail($id);
        $request->validate([
            'nama_indikator' => 'required|string|max:255',
            'satuan_target' => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $ind->update($request->only('nama_indikator', 'satuan_target', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'indikator'])->with('success', 'Indikator Kinerja berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroyIndikator($id)
    {
        $this->guard('delete', 'admin:rkat');
        try {
            $ind = RkatIndikator::findOrFail($id);
            $ind->delete();
            return redirect()->route('admin.rkat.master.index', ['tab' => 'indikator'])->with('success', 'Indikator berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ========================================================
    // 5. Jenis Kegiatan
    // ========================================================
    public function storeKegiatan(Request $request)
    {
        $this->guard('create', 'admin:rkat');
        $request->validate([
            'program_id' => 'required|uuid|exists:rkat_programs,id',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            RkatKegiatanMaster::create($request->only('program_id', 'nama_kegiatan', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'kegiatan'])->with('success', 'Jenis Kegiatan berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function updateKegiatan(Request $request, $id)
    {
        $this->guard('edit', 'admin:rkat');
        $kegiatan = RkatKegiatanMaster::findOrFail($id);
        $request->validate([
            'program_id' => 'required|uuid|exists:rkat_programs,id',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $kegiatan->update($request->only('program_id', 'nama_kegiatan', 'deskripsi'));
            return redirect()->route('admin.rkat.master.index', ['tab' => 'kegiatan'])->with('success', 'Jenis Kegiatan berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroyKegiatan($id)
    {
        $this->guard('delete', 'admin:rkat');
        try {
            $kegiatan = RkatKegiatanMaster::findOrFail($id);
            $kegiatan->delete();
            return redirect()->route('admin.rkat.master.index', ['tab' => 'kegiatan'])->with('success', 'Kegiatan berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
