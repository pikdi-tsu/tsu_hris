<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use App\Models\SuratTugas;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SuratTugasController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $karyawans = DataDosenTendik::with('unit')
            ->whereNotNull('nama')
            ->orderBy('nama', 'asc')
            ->get(['id', 'nik', 'nama', 'unit_id', 'posisi']);

        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();
        $title = 'Surat Tugas & Dinas Luar';

        return view('admin::surat-tugas.index', compact('karyawans', 'units', 'title'));
    }

    public function datatable(Request $request)
    {
        $query = SuratTugas::with(['pegawai.unit']);

        if ($request->filled('unit_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('tanggal_mulai', [$request->start_date, $request->end_date])
                  ->orWhereBetween('tanggal_selesai', [$request->start_date, $request->end_date]);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $pegawai = $row->pegawai;
                $nama = $pegawai ? $pegawai->nama : ($row->nama_pegawai ?? '-');
                $nik = $pegawai && $pegawai->nik ? $pegawai->nik : '-';
                $unit = $pegawai && $pegawai->unit ? $pegawai->unit->nama_unit : '-';

                return '<div>
                    <strong class="text-dark">' . e($nama) . '</strong><br>
                    <small class="text-muted"><i class="fas fa-id-badge mr-1"></i>NIK: ' . e($nik) . ' | <i class="fas fa-building mr-1"></i>' . e($unit) . '</small>
                </div>';
            })
            ->addColumn('rentang_tanggal', function ($row) {
                $mulai = Carbon::parse($row->tanggal_mulai)->translatedFormat('d M Y');
                $selesai = Carbon::parse($row->tanggal_selesai)->translatedFormat('d M Y');
                $durasi = Carbon::parse($row->tanggal_mulai)->diffInDays(Carbon::parse($row->tanggal_selesai)) + 1;

                return '<div>
                    <span class="font-weight-bold text-primary">' . $mulai . ' s.d ' . $selesai . '</span><br>
                    <small class="badge badge-light border text-muted"><i class="far fa-clock mr-1"></i>' . $durasi . ' Hari</small>
                </div>';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'Disetujui'  => 'badge-success',
                    'Menunggu'   => 'badge-warning',
                    'Dibatalkan' => 'badge-danger',
                    'Selesai'    => 'badge-secondary',
                ];
                $cls = $badges[$row->status] ?? 'badge-info';
                return '<span class="badge ' . $cls . ' px-2 py-1"><i class="fas fa-check-circle mr-1"></i>' . e($row->status) . '</span>';
            })
            ->addColumn('berkas_btn', function ($row) {
                if ($row->file_surat_tugas) {
                    return '<a href="' . route('admin.surat-tugas.download-berkas', $row->id) . '" target="_blank" class="btn btn-xs btn-outline-primary" title="Unduh Berkas"><i class="fas fa-file-pdf mr-1"></i> Unduh File</a>';
                }
                return '<span class="text-muted font-italic" style="font-size: 8pt;">Tidak ada file</span>';
            })
            ->addColumn('aksi', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-info mr-1 btn-edit-tugas" data-id="' . $row->id . '" title="Edit Penugasan"><i class="fas fa-edit"></i> Edit</button>';
                $btnDelete = '<button type="button" class="btn btn-xs btn-danger btn-delete-tugas" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_surat) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                return '<div class="text-center text-nowrap">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['pegawai_info', 'rentang_tanggal', 'status_badge', 'berkas_btn', 'aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat'          => 'required|string|max:150',
            'data_dosen_tendik_id' => 'required|exists:data_dosen_tendiks,id',
            'tanggal_mulai'        => 'required|date',
            'tanggal_selesai'      => 'required|date|after_or_equal:tanggal_mulai',
            'keperluan'            => 'required|string',
            'lokasi_tugas'         => 'nullable|string|max:255',
            'pejabat_pemberi_tugas'=> 'nullable|string|max:255',
            'status'               => 'required|in:Disetujui,Menunggu,Dibatalkan,Selesai',
            'file_surat_tugas'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'nomor_surat.required'          => 'Nomor Surat Tugas wajib diisi.',
            'data_dosen_tendik_id.required' => 'Pilih pegawai yang ditugaskan.',
            'tanggal_mulai.required'        => 'Tanggal mulai tugas wajib diisi.',
            'tanggal_selesai.required'      => 'Tanggal selesai tugas wajib diisi.',
            'tanggal_selesai.after_or_equal'=> 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'keperluan.required'            => 'Keperluan / agenda dinas luar wajib diisi.',
        ]);

        $pegawai = DataDosenTendik::find($request->data_dosen_tendik_id);

        $filePath = null;
        if ($request->hasFile('file_surat_tugas')) {
            $file = $request->file('file_surat_tugas');
            $fileName = 'ST_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('private/surat_tugas', $fileName);
        }

        SuratTugas::create([
            'nomor_surat'           => $request->nomor_surat,
            'data_dosen_tendik_id'  => $request->data_dosen_tendik_id,
            'nama_pegawai'          => $pegawai ? $pegawai->nama : null,
            'tanggal_mulai'         => $request->tanggal_mulai,
            'tanggal_selesai'       => $request->tanggal_selesai,
            'keperluan'             => $request->keperluan,
            'lokasi_tugas'          => $request->lokasi_tugas,
            'pejabat_pemberi_tugas' => $request->pejabat_pemberi_tugas,
            'status'                => $request->status,
            'file_surat_tugas'      => $filePath,
            'catatan'               => $request->catatan,
            'created_by'            => Auth::user() ? Auth::user()->name : 'Admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat Tugas dinas luar berhasil diterbitkan dan otomatis tersinkron ke log presensi pegawai.',
        ]);
    }

    public function edit($id)
    {
        $suratTugas = SuratTugas::findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $suratTugas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $suratTugas = SuratTugas::findOrFail($id);

        $request->validate([
            'nomor_surat'          => 'required|string|max:150',
            'data_dosen_tendik_id' => 'required|exists:data_dosen_tendiks,id',
            'tanggal_mulai'        => 'required|date',
            'tanggal_selesai'      => 'required|date|after_or_equal:tanggal_mulai',
            'keperluan'            => 'required|string',
            'lokasi_tugas'         => 'nullable|string|max:255',
            'pejabat_pemberi_tugas'=> 'nullable|string|max:255',
            'status'               => 'required|in:Disetujui,Menunggu,Dibatalkan,Selesai',
            'file_surat_tugas'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $pegawai = DataDosenTendik::find($request->data_dosen_tendik_id);

        $data = [
            'nomor_surat'           => $request->nomor_surat,
            'data_dosen_tendik_id'  => $request->data_dosen_tendik_id,
            'nama_pegawai'          => $pegawai ? $pegawai->nama : null,
            'tanggal_mulai'         => $request->tanggal_mulai,
            'tanggal_selesai'       => $request->tanggal_selesai,
            'keperluan'             => $request->keperluan,
            'lokasi_tugas'          => $request->lokasi_tugas,
            'pejabat_pemberi_tugas' => $request->pejabat_pemberi_tugas,
            'status'                => $request->status,
            'catatan'               => $request->catatan,
            'updated_by'            => Auth::user() ? Auth::user()->name : 'Admin',
        ];

        if ($request->hasFile('file_surat_tugas')) {
            $file = $request->file('file_surat_tugas');
            $fileName = 'ST_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('private/surat_tugas', $fileName);
            $data['file_surat_tugas'] = $filePath;
        }

        $suratTugas->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data Surat Tugas berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $suratTugas = SuratTugas::findOrFail($id);

        if ($suratTugas->file_surat_tugas && Storage::exists($suratTugas->file_surat_tugas)) {
            Storage::delete($suratTugas->file_surat_tugas);
        }

        $suratTugas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surat Tugas berhasil dihapus.',
        ]);
    }

    public function downloadBerkas($id)
    {
        $suratTugas = SuratTugas::findOrFail($id);

        if (!$suratTugas->file_surat_tugas || !Storage::exists($suratTugas->file_surat_tugas)) {
            abort(404, 'Berkas file surat tugas tidak ditemukan.');
        }

        return Storage::download($suratTugas->file_surat_tugas);
    }
}
