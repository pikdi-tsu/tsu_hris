<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\DataDosenTendik;
use App\Models\DataJadwalPiket;
use App\Models\MasterUnit;

class JadwalPiketController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:jadwal-piket');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        $bulan = $this->getBulan();
        $currentMonth = date('n');
        $currentYear = date('Y');

        $totalPiket = DataJadwalPiket::count();
        $piketBulanIni = DataJadwalPiket::whereMonth('tanggal_piket', $currentMonth)
            ->whereYear('tanggal_piket', $currentYear)
            ->count();
        $petugasTerjadwal = DataJadwalPiket::distinct('data_dosen_tendik_id')->count('data_dosen_tendik_id');
        $totalTendikSarpras = DataDosenTendik::where('tipe_karyawan', 'Tendik')
            ->orWhereNull('tipe_karyawan')
            ->count();

        $stats = [
            'total_piket' => $totalPiket,
            'piket_bulan_ini' => $piketBulanIni,
            'petugas_terjadwal' => $petugasTerjadwal,
            'total_tendik' => $totalTendikSarpras,
        ];

        return view('admin::jadwal-piket.index', [
            'title' => 'Jadwal Piket Sabtu (Tendik & Sarpras)',
            'bulan' => $bulan,
            'defaultBulan' => $currentMonth,
            'defaultTahun' => $currentYear,
            'stats' => $stats,
        ]);
    }

    public function datatable(Request $request)
    {
        $bulan = $request->input('periode_bulan');
        $tahun = $request->input('periode_tahun');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DataJadwalPiket::with(['karyawan.unit']);

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('tanggal_piket', [$startDate, $endDate]);
        } elseif (!empty($bulan) && !empty($tahun)) {
            $query->whereMonth('tanggal_piket', $bulan)->whereYear('tanggal_piket', $tahun);
        }

        $query->orderBy('tanggal_piket', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_karyawan', function ($row) {
                $nama = $row->karyawan ? e($row->karyawan->nama_lengkap) : '-';
                $unit = $row->karyawan && $row->karyawan->unit ? '<br><small class="text-muted font-weight-normal">' . e($row->karyawan->unit->nama_unit) . '</small>' : '';
                return '<strong class="text-dark">' . $nama . '</strong>' . $unit;
            })
            ->addColumn('pin', function ($row) {
                $pinVal = $row->pin ?? ($row->karyawan ? $row->karyawan->pin_absensi : '-');
                return '<span class="tsu-badge-pin">' . e($pinVal) . '</span>';
            })
            ->addColumn('tanggal_formatted', function ($row) {
                $dt = Carbon::parse($row->tanggal_piket);
                return '<strong class="text-dark">' . $dt->translatedFormat('l') . '</strong><br><span class="text-muted small">' . $dt->format('d/m/Y') . '</span>';
            })
            ->addColumn('jam_kerja', function ($row) {
                $start = substr($row->jam_mulai, 0, 5);
                $end = substr($row->jam_selesai, 0, 5);
                return '<span class="tsu-badge-jam">' . $start . ' - ' . $end . '</span>';
            })
            ->addColumn('durasi', function ($row) {
                $hours = round($row->target_durasi_menit / 60, 1);
                return '<span class="tsu-badge-durasi">' . $hours . ' Jam (' . $row->target_durasi_menit . ' mnt)</span>';
            })
            ->addColumn('keterangan_badge', function ($row) {
                return $row->keterangan ? '<span class="text-dark">' . e($row->keterangan) . '</span>' : '<span class="text-muted">Piket Sabtu</span>';
            })
            ->addColumn('aksi', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs tsu-btn-action tsu-btn-action--edit btn-modal mr-1" data-url="' . route('admin.jadwal-piket.edit', $row->id) . '" title="Edit Piket"><i class="fas fa-pencil-alt"></i></button>';
                $btnDelete = '<button type="button" class="btn btn-xs tsu-btn-action tsu-btn-action--delete btn-delete" data-url="' . route('admin.jadwal-piket.destroy', $row->id) . '" data-nama="' . e($row->karyawan ? $row->karyawan->nama_lengkap : 'Karyawan') . '" title="Hapus Jadwal"><i class="fas fa-trash-alt"></i></button>';
                return '<div class="text-center text-nowrap">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['nama_karyawan', 'pin', 'tanggal_formatted', 'jam_kerja', 'durasi', 'keterangan_badge', 'aksi'])
            ->make(true);
    }

    public function create()
    {
        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();
        // Ambil data Tendik dan Sarpras yang aktif
        $karyawans = DataDosenTendik::with('unit')
            ->where(function ($q) {
                $q->where('tipe_karyawan', 'Tendik')
                  ->orWhereNull('tipe_karyawan');
            })
            ->where('is_active', true)
            ->orderBy('nama', 'asc')
            ->get();

        $bulan = $this->getBulan();
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        $saturdays = $this->generateSaturdaysForMonth($currentMonth, $currentYear);

        return view('admin::jadwal-piket.create_modal', [
            'karyawans'    => $karyawans,
            'units'        => $units,
            'bulan'        => $bulan,
            'currentMonth' => $currentMonth,
            'currentYear'  => $currentYear,
            'saturdays'    => $saturdays,
        ]);
    }

    /**
     * Helper untuk menghasilkan daftar hari Sabtu dalam bulan tertentu
     */
    private function generateSaturdaysForMonth(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $saturdays = [];

        while ($startDate->lte($endDate)) {
            if ($startDate->isSaturday()) {
                $saturdays[] = [
                    'date'     => $startDate->format('Y-m-d'),
                    'label'    => $startDate->translatedFormat('l, d F Y'),
                    'short'    => $startDate->format('d M'),
                    'day_num'  => $startDate->day,
                ];
            }
            $startDate->addDay();
        }

        return $saturdays;
    }

    /**
     * AJAX endpoint untuk mengambil daftar hari Sabtu berdasarkan Bulan & Tahun
     */
    public function getSaturdays(Request $request)
    {
        $month = (int) ($request->month ?: date('n'));
        $year = (int) ($request->year ?: date('Y'));
        $saturdays = $this->generateSaturdaysForMonth($month, $year);

        return response()->json([
            'success'   => true,
            'saturdays' => $saturdays,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_ids' => 'required|array|min:1',
            'jam_mulai'    => 'required',
            'jam_selesai'  => 'required',
        ], [
            'karyawan_ids.required' => 'Pilih minimal satu karyawan bertugas.',
            'jam_mulai.required'    => 'Jam mulai wajib diisi.',
            'jam_selesai.required'  => 'Jam selesai wajib diisi.',
        ]);

        $dates = $request->input('tanggal_pikets');
        if (empty($dates) && $request->filled('tanggal_piket')) {
            $dates = [$request->input('tanggal_piket')];
        }

        if (empty($dates) || !is_array($dates)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih minimal satu tanggal piket (Hari Sabtu).'
            ], 422);
        }

        try {
            $userNik = Auth::check() && $this->getCurrentProfile() ? $this->getCurrentProfile()->nik : 'System';
            $inTime = Carbon::parse($request->jam_mulai);
            $outTime = Carbon::parse($request->jam_selesai);
            $targetMinutes = intval(abs($outTime->diffInMinutes($inTime)));
            if ($targetMinutes <= 0) $targetMinutes = 240;

            $inserted = 0;
            foreach ($dates as $tgl) {
                foreach ($request->karyawan_ids as $karyawanId) {
                    $karyawan = DataDosenTendik::find($karyawanId);
                    if (!$karyawan) continue;

                    DataJadwalPiket::updateOrCreate([
                        'data_dosen_tendik_id' => $karyawan->id,
                        'tanggal_piket'        => $tgl,
                    ], [
                        'pin'                 => $karyawan->pin_absensi,
                        'jam_mulai'           => $request->jam_mulai,
                        'jam_selesai'         => $request->jam_selesai,
                        'target_durasi_menit' => $targetMinutes,
                        'keterangan'          => $request->keterangan ?? 'Piket Sabtu Pelayanan',
                        'is_active'           => true,
                        'created_by'          => $userNik,
                        'updated_by'          => $userNik,
                    ]);

                    $inserted++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menyimpan ' . $inserted . ' jadwal penugasan piket Sabtu.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jadwal piket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $piket = DataJadwalPiket::with('karyawan.unit')->findOrFail($id);
        $karyawans = DataDosenTendik::with('unit')->orderBy('nama', 'asc')->get();

        return view('admin::jadwal-piket.edit_modal', [
            'piket' => $piket,
            'karyawans' => $karyawans,
        ]);
    }

    public function update(Request $request, $id)
    {
        $piket = DataJadwalPiket::findOrFail($id);

        $request->validate([
            'tanggal_piket' => 'required|date',
            'data_dosen_tendik_id' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        try {
            $userNik = Auth::check() && $this->getCurrentProfile() ? $this->getCurrentProfile()->nik : 'System';
            $karyawan = DataDosenTendik::find($request->data_dosen_tendik_id);

            $inTime = Carbon::parse($request->jam_mulai);
            $outTime = Carbon::parse($request->jam_selesai);
            $targetMinutes = intval(abs($outTime->diffInMinutes($inTime)));

            $piket->update([
                'data_dosen_tendik_id' => $request->data_dosen_tendik_id,
                'pin' => $karyawan ? $karyawan->pin_absensi : $piket->pin,
                'tanggal_piket' => $request->tanggal_piket,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'target_durasi_menit' => $targetMinutes > 0 ? $targetMinutes : 240,
                'keterangan' => $request->keterangan,
                'updated_by' => $userNik,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal piket berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jadwal piket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $piket = DataJadwalPiket::findOrFail($id);
            $piket->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal piket berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jadwal piket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBulan()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
