<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use App\Models\PayrollPeriod;
use App\Models\HonorariumDosen;
use App\Models\MasterTarifHonorarium;
use App\Models\PayrollPeriodApproval;
use App\Models\DataDosenTendik;
use App\Models\User;
use App\Services\HonorariumCalculationService;
use App\Services\TsuErrorHandlerService;
use App\Notifications\PayrollApprovalNotification;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapHonorariumExport;
use App\Exports\RekapBankTransferHonorariumExport;
use ZipArchive;

class HonorariumController extends MiddlewareController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:honorarium');
        $this->middleware('permission:admin:honorarium:view')->only(['datatable', 'datatableKaryawan', 'getDosenTarif', 'getHonorariumData', 'slipPdf', 'downloadAllSlip', 'exportExcel', 'exportBank']);
        $this->middleware('permission:admin:honorarium:create')->only(['storeDosen']);
        $this->middleware('permission:admin:honorarium:edit')->only(['updateHonorariumData', 'submitApproval', 'approveStep', 'rejectStep', 'unlock']);
        $this->middleware('permission:admin:honorarium:delete')->only(['deleteDosen']);
    }

    private function getCurrentProfile()
    {
        $user = Auth::user();
        if (!$user) return null;
        return DataDosenTendik::where('user_id', $user->id)
            ->orWhere('nama', $user->name)
            ->first();
    }

    private function notifyUserById($userId, $notification)
    {
        if (!$userId) return;
        $user = User::find($userId);
        if ($user) {
            try {
                $user->notify($notification);
            } catch (\Exception $e) {
                // Ignore safe notification failure
            }
        }
    }

    private function notifyKaryawanUser($karyawan, $notification)
    {
        if (!$karyawan) return;
        if (is_string($karyawan)) {
            $karyawan = DataDosenTendik::find($karyawan);
        }
        if (!$karyawan) return;

        $user = null;
        if (!empty($karyawan->user_id)) {
            $user = User::find($karyawan->user_id);
        }
        if (!$user && !empty($karyawan->nik)) {
            $user = User::where('nik', $karyawan->nik)->first();
        }
        if (!$user && !empty($karyawan->nama)) {
            $user = User::where('name', $karyawan->nama)->first();
        }

        if ($user) {
            try {
                $user->notify($notification);
            } catch (\Exception $e) {
                // Ignore safe notification failure
            }
        }
    }

    public function index()
    {
        return view('admin::honorarium.index', [
            'title' => 'Honorarium Dosen & Tenaga Pengajar',
        ]);
    }

    public function datatable(Request $request)
    {
        $query = PayrollPeriod::where('tipe', 'honorarium')
            ->with(['validator1', 'validator2', 'approvalKaryawan', 'createdUser'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kategori_badge', function ($row) {
                return '<span class="badge badge-primary px-2 py-1"><i class="fas fa-layer-group mr-1"></i> Honorarium Terpadu</span>';
            })
            ->addColumn('periode_info', function ($row) {
                $code = '<small class="text-muted d-block font-monospace">' . e($row->kode_periode) . '</small>';
                $nama = '<strong>' . e($row->nama_periode) . '</strong>';
                $extra = '';
                if ($row->tahun_akademik) {
                    $extra .= '<br><small class="text-info font-weight-bold">TA: ' . e($row->tahun_akademik) . ' (' . e($row->semester) . ')' . ($row->bulan_honor ? ' • ' . e($row->bulan_honor) : '') . '</small>';
                }
                return '<div>' . $nama . $code . $extra . '</div>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('approvers_info', function ($row) {
                $v1 = $row->validator1->nama ?? '-';
                $v2 = $row->validator2->nama ?? '-';
                $ap = $row->approvalKaryawan->nama ?? '-';
                return '<div style="font-size: 8pt;" class="text-muted">' .
                       '<div><strong>Val 1:</strong> ' . e($v1) . '</div>' .
                       '<div><strong>Val 2:</strong> ' . e($v2) . '</div>' .
                       '<div><strong>Approval:</strong> ' . e($ap) . '</div>' .
                       '</div>';
            })
            ->addColumn('total_pegawai_formatted', function ($row) {
                return '<div class="text-center font-weight-bold">' . number_format($row->total_pegawai, 0) . ' Dosen</div>';
            })
            ->addColumn('total_gaji_bersih_formatted', function ($row) {
                return '<div class="text-right font-weight-bold text-success" style="font-size: 0.95rem;">Rp ' . number_format($row->total_gaji_bersih, 0, ',', '.') . '</div>';
            })
            ->addColumn('action', function ($row) {
                $btnShow = '<a href="' . route('admin.honorarium.show', $row->id) . '" class="btn btn-xs btn-primary mr-1" title="Buka Lembar Kroscek"><i class="fas fa-eye mr-1"></i> Buka Kroscek</a>';
                
                $dropdown = '';
                if ($row->is_locked || ($row->created_by == Auth::id() && in_array($row->status, ['draft', 'revision_requested']))) {
                    $dropdown = '
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-outline-secondary dropdown-toggle" data-toggle="dropdown" title="Download Laporan">
                            <i class="fas fa-download"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('admin.honorarium.export-excel', $row->id) . '"><i class="fas fa-file-excel mr-2 text-success"></i> Rekap Excel</a>
                            <a class="dropdown-item" href="' . route('admin.honorarium.export-bank', $row->id) . '"><i class="fas fa-university mr-2 text-primary"></i> Transfer Bank</a>
                            <a class="dropdown-item" href="' . route('admin.honorarium.download-all-slip', $row->id) . '"><i class="fas fa-file-archive mr-2 text-danger"></i> Semua Slip (ZIP)</a>
                        </div>
                    </div>';
                }

                $btnDel = ($row->status === 'draft' && $row->created_by == Auth::id())
                    ? '<button type="button" class="btn btn-xs btn-danger btn-delete-period ml-1" data-url="' . route('admin.honorarium.destroy', $row->id) . '" data-name="' . e($row->nama_periode) . '" title="Hapus Draft"><i class="fas fa-trash"></i></button>'
                    : '';
                return '<div class="text-center text-nowrap">' . $btnShow . $dropdown . $btnDel . '</div>';
            })
            ->rawColumns(['kategori_badge', 'periode_info', 'status_badge', 'approvers_info', 'total_pegawai_formatted', 'total_gaji_bersih_formatted', 'action'])
            ->make(true);
    }

    public function create()
    {
        $dosenTendiks = DataDosenTendik::where('is_active', 1)->orderBy('nama', 'asc')->get();
        return view('admin::honorarium.create_modal', compact('dosenTendiks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_periode'     => 'required|string|max:150',
            'tahun_akademik'   => 'required|string|max:20',
            'semester'         => 'required|in:Ganjil,Genap',
            'bulan_honor'      => 'nullable|string|max:20',
            'jumlah_pertemuan' => 'nullable|numeric|min:1',
            'start_date_cutoff'=> 'required|date',
            'end_date_cutoff'  => 'required|date|after_or_equal:start_date_cutoff',
            'validator_1_id'   => 'required|exists:data_dosen_tendiks,id',
            'validator_2_id'   => 'required|exists:data_dosen_tendiks,id',
            'approval_id'      => 'required|exists:data_dosen_tendiks,id',
        ], [
            'nama_periode.required'   => 'Nama periode honorarium wajib diisi.',
            'validator_1_id.required' => 'Validator 1 wajib dipilih.',
            'validator_2_id.required' => 'Validator 2 wajib dipilih.',
            'approval_id.required'    => 'Pimpinan Approval Final wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $month = Carbon::parse($request->start_date_cutoff)->format('m');
            $year  = Carbon::parse($request->start_date_cutoff)->format('Y');
            $kodePeriode = sprintf('HON-%s%s-%04d', $year, $month, rand(100, 999));

            $period = PayrollPeriod::create([
                'id'                => (string) Str::uuid(),
                'kode_periode'      => $kodePeriode,
                'nama_periode'      => $request->nama_periode,
                'bulan'             => (int)$month,
                'tahun'             => (int)$year,
                'start_date_cutoff' => $request->start_date_cutoff,
                'end_date_cutoff'   => $request->end_date_cutoff,
                'tipe'              => 'honorarium',
                'kategori_honor'    => 'terpadu',
                'tahun_akademik'    => $request->tahun_akademik,
                'semester'          => $request->semester,
                'bulan_honor'       => $request->bulan_honor,
                'jumlah_pertemuan'  => $request->jumlah_pertemuan ?: 3,
                'status'            => 'draft',
                'validator_1_id'    => $request->validator_1_id,
                'validator_2_id'    => $request->validator_2_id,
                'approval_id'       => $request->approval_id,
                'created_by'        => Auth::id(),
                'total_pegawai'     => 0,
                'total_gaji_kotor'  => 0,
                'total_potongan'    => 0,
                'total_gaji_bersih' => 0,
            ]);

            // Catat log approval awal
            $myProfile = $this->getCurrentProfile();
            PayrollPeriodApproval::create([
                'payroll_period_id' => $period->id,
                'step'              => 'draft',
                'role_label'        => 'Pembuat Draft',
                'user_id'           => Auth::id(),
                'karyawan_id'       => $myProfile ? $myProfile->id : null,
                'karyawan_name'     => $myProfile ? $myProfile->nama : Auth::user()->name,
                'action'            => 'created',
                'note'              => 'Membuat draft periode honorarium dosen: ' . $period->nama_periode,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Periode Honorarium Dosen berhasil dibuat.',
                'redirect'=> route('admin.honorarium.show', $period->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat periode honorarium dosen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $period = PayrollPeriod::with(['validator1', 'validator2', 'approvalKaryawan', 'createdUser', 'approvals.user', 'approvals.karyawan'])
            ->where('tipe', 'honorarium')
            ->findOrFail($id);

        $myProfile = $this->getCurrentProfile();
        $myKaryawanId = $myProfile ? $myProfile->id : null;
        $isCreator = ($period->created_by == Auth::id());

        $isValidator1 = ($period->validator_1_id == $myKaryawanId);
        $isValidator2 = ($period->validator_2_id == $myKaryawanId);
        $isApproval   = ($period->approval_id == $myKaryawanId);

        // Auto mark as read database notification untuk periode ini
        try {
            Auth::user()->unreadNotifications()
                ->where('data->payroll_period_id', $period->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            // Ignore
        }

        $title = 'Kroscek Honorarium: ' . $period->nama_periode;
        $dosenTendiks = DataDosenTendik::where('tipe_karyawan', 'Dosen')
            ->where('is_active', 1)
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin::honorarium.show', compact(
            'title',
            'period',
            'isCreator',
            'isValidator1',
            'isValidator2',
            'isApproval',
            'dosenTendiks'
        ));
    }

    public function getDosenTarif($dosenId)
    {
        $dosen = DataDosenTendik::with([
            'unit',
            'jabatanFungsionals' => function ($q) {
                $q->where('is_active', 'Y')->with('masterFungsional');
            },
            'jabatanStrukturals' => function ($q) {
                $q->where('is_active', 'Y')->with('masterStruktural');
            }
        ])->findOrFail($dosenId);

        $jafungInfo = HonorariumCalculationService::detectKodeJafung($dosen);
        $matrixTarif = HonorariumCalculationService::getTarifMatrix();
        $tarif = $matrixTarif[$jafungInfo['kode']] ?? ($matrixTarif['TP'] ?? null);

        $struktural = $dosen->jabatanStrukturals ? $dosen->jabatanStrukturals->where('is_active', 'Y')->first() : null;
        $namaStruktural = $struktural ? ($struktural->masterStruktural->nama ?? 'Dosen') : 'Dosen';
        $sksStruktur = $struktural ? 3 : 0;

        return response()->json([
            'success'        => true,
            'dosen'          => [
                'id'             => $dosen->id,
                'nama'           => $dosen->nama,
                'nik_nip'        => $dosen->nip ?? $dosen->nik,
                'nama_unit'      => $dosen->unit->nama_unit ?? '-',
                'struktural'     => $namaStruktural,
                'sks_struktural' => $sksStruktur,
                'kode_jafung'    => $jafungInfo['kode'],
                'nama_jafung'    => $jafungInfo['nama'],
                'rekening_bank'  => $dosen->nama_bank ?? 'BSI',
                'nomor_rekening' => $dosen->no_rekening,
                'nama_rekening'  => $dosen->atas_nama_rekening ?? $dosen->nama,
            ],
            'tarif'          => $tarif,
        ]);
    }

    public function storeDosen(Request $request, $periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);

        if ($period->is_locked || !in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json(['success' => false, 'message' => 'Periode tidak dapat diubah karena sedang dalam proses approval atau terkunci.'], 403);
        }

        if ($period->created_by != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Hanya pembuat draft yang berwenang menambahkan dosen penerima honor.'], 403);
        }

        $request->validate([
            'data_dosen_tendik_id' => 'required|exists:data_dosen_tendiks,id',
        ], [
            'data_dosen_tendik_id.required' => 'Pilih dosen penerima honorarium.',
        ]);

        // Cek duplikasi dosen di periode yang sama
        $exists = HonorariumDosen::where('payroll_period_id', $period->id)
            ->where('data_dosen_tendik_id', $request->data_dosen_tendik_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Dosen tersebut sudah ada dalam daftar penerima honorarium periode ini. Silakan gunakan tombol Edit untuk mengubah data.'], 422);
        }

        $dosen = DataDosenTendik::with([
            'unit',
            'jabatanFungsionals' => function ($q) {
                $q->where('is_active', 'Y')->with('masterFungsional');
            },
            'jabatanStrukturals' => function ($q) {
                $q->where('is_active', 'Y')->with('masterStruktural');
            }
        ])->findOrFail($request->data_dosen_tendik_id);

        $jafungInfo = HonorariumCalculationService::detectKodeJafung($dosen);
        $matrixTarif = HonorariumCalculationService::getTarifMatrix();
        $tarif = $matrixTarif[$jafungInfo['kode']] ?? ($matrixTarif['TP'] ?? null);

        $struktural = $dosen->jabatanStrukturals ? $dosen->jabatanStrukturals->where('is_active', 'Y')->first() : null;
        $namaStruktural = $struktural ? ($struktural->masterStruktural->nama ?? 'Dosen') : 'Dosen';

        $calc = HonorariumCalculationService::calculateRowData($request->all(), $tarif);

        DB::beginTransaction();
        try {
            $honorItem = new HonorariumDosen();
            $honorItem->id                   = (string) Str::uuid();
            $honorItem->payroll_period_id    = $period->id;
            $honorItem->data_dosen_tendik_id = $dosen->id;
            $honorItem->kategori_honor       = 'terpadu';
            $honorItem->nama_dosen           = $dosen->nama;
            $honorItem->nik_nip              = $dosen->nip ?? $dosen->nik;
            $honorItem->nama_unit            = $dosen->unit->nama_unit ?? '-';
            $honorItem->struktural           = $namaStruktural;
            $honorItem->kode_jafung          = $jafungInfo['kode'];
            $honorItem->nama_jafung          = $jafungInfo['nama'];
            $honorItem->rekening_bank        = $dosen->nama_bank ?? 'BSI';
            $honorItem->nomor_rekening       = $dosen->no_rekening;
            $honorItem->nama_rekening        = $dosen->atas_nama_rekening ?? $dosen->nama;

            $honorItem->fill($calc);
            $honorItem->save();

            HonorariumCalculationService::syncPeriodSummary($period);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Dosen penerima honorarium berhasil ditambahkan.',
                'period'  => $period->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan dosen: ' . $e->getMessage()], 500);
        }
    }

    public function datatableKaryawan(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $query = HonorariumDosen::where('payroll_period_id', $period->id)->orderBy('nama_dosen', 'asc');

        $isCreator = ($period->created_by == Auth::id());
        $canEdit = $isCreator && in_array($period->status, ['draft', 'revision_requested']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('dosen_info', function ($row) {
                $unit = $row->nama_unit ? '<br><small class="text-muted"><i class="fas fa-university mr-1"></i>' . e($row->nama_unit) . '</small>' : '';
                $struk = $row->struktural && $row->struktural !== 'Dosen' ? '<br><small class="text-info"><i class="fas fa-sitemap mr-1"></i>' . e($row->struktural) . '</small>' : '';
                $nip = $row->nik_nip ? '<small class="text-muted font-monospace ml-1">(' . e($row->nik_nip) . ')</small>' : '';
                return '<div><strong>' . e($row->nama_dosen) . '</strong>' . $nip . $unit . $struk . '</div>';
            })
            ->addColumn('jafung_badge', function ($row) {
                return '<div class="text-center"><span class="badge badge-primary px-2 py-1 font-weight-bold">' . e($row->kode_jafung ?: 'TP') . '</span><br><small class="text-muted">' . e($row->nama_jafung ?: 'Tenaga Pengajar') . '</small></div>';
            })
            ->addColumn('komponen_info', function ($row) {
                $html = '<div style="font-size: 8pt;">';
                $hasKomponen = false;

                if ($row->total_honor_sks > 0) {
                    $hasKomponen = true;
                    $html .= '<div class="mb-1"><span class="badge badge-success px-1 py-0"><i class="fas fa-chalkboard mr-1"></i>SKS Lebih (' . $row->sks_lebih . ' sks)</span> <strong class="text-dark">Rp ' . number_format($row->total_honor_sks, 0, ',', '.') . '</strong></div>';
                }
                $totBimbingUji = $row->total_bimbingan_ta + $row->total_penguji_ta + $row->total_kerja_praktek;
                if ($totBimbingUji > 0) {
                    $hasKomponen = true;
                    $html .= '<div class="mb-1"><span class="badge badge-primary px-1 py-0"><i class="fas fa-user-graduate mr-1"></i>Bimbing/Uji TA & KP</span> <strong class="text-dark">Rp ' . number_format($totBimbingUji, 0, ',', '.') . '</strong></div>';
                }
                $totUjian = $row->total_honor_soal + $row->total_honor_koreksi;
                if ($totUjian > 0) {
                    $hasKomponen = true;
                    $html .= '<div class="mb-1"><span class="badge badge-info px-1 py-0"><i class="fas fa-file-alt mr-1"></i>Ujian (Soal/Koreksi)</span> <strong class="text-dark">Rp ' . number_format($totUjian, 0, ',', '.') . '</strong></div>';
                }

                if (!$hasKomponen) {
                    $html .= '<span class="text-muted font-italic">Belum ada komponen honor</span>';
                }

                $html .= '</div>';
                return $html;
            })
            ->addColumn('honor_kotor_formatted', function ($row) {
                return '<div class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">Rp ' . number_format($row->total_honor_kotor, 0, ',', '.') . '</div>';
            })
            ->addColumn('potongan_info', function ($row) {
                if ($row->total_potongan <= 0) return '<div class="text-center text-muted">-</div>';
                return '<div class="text-center text-danger font-weight-bold">Rp ' . number_format($row->total_potongan, 0, ',', '.') . '</div>';
            })
            ->addColumn('total_transfer_formatted', function ($row) {
                return '<div class="text-right font-weight-bold text-success" style="font-size: 1rem;">Rp ' . number_format($row->total_transfer, 0, ',', '.') . '</div>';
            })
            ->addColumn('aksi', function ($row) use ($canEdit) {
                $btnDetail = '<button type="button" class="btn btn-xs btn-info btn-detail-honor mr-1" data-id="' . $row->id . '" title="Lihat Rincian"><i class="fas fa-eye mr-1"></i>Detail</button>';
                $btnEdit = $canEdit
                    ? '<button type="button" class="btn btn-xs btn-warning btn-edit-honor mr-1" data-id="' . $row->id . '" title="Edit Komponen"><i class="fas fa-edit mr-1"></i>Edit</button>'
                    : '';
                $btnDel = $canEdit
                    ? '<button type="button" class="btn btn-xs btn-danger btn-delete-dosen mr-1" data-id="' . $row->id . '" data-name="' . e($row->nama_dosen) . '" title="Hapus dari Periode"><i class="fas fa-trash"></i></button>'
                    : '';
                $btnPdf = '<a href="' . route('admin.honorarium.slip-pdf', $row->id) . '" target="_blank" class="btn btn-xs btn-secondary" title="Cetak Slip PDF"><i class="fas fa-file-pdf mr-1"></i>Slip</a>';

                return '<div class="d-inline-flex align-items-center justify-content-center" style="white-space: nowrap;">' . $btnDetail . $btnEdit . $btnDel . $btnPdf . '</div>';
            })
            ->rawColumns(['dosen_info', 'jafung_badge', 'komponen_info', 'honor_kotor_formatted', 'potongan_info', 'total_transfer_formatted', 'aksi'])
            ->make(true);
    }

    public function getHonorariumData($id)
    {
        $item = HonorariumDosen::with('period')->findOrFail($id);
        $matrixTarif = HonorariumCalculationService::getTarifMatrix();
        $tarif = $matrixTarif[$item->kode_jafung] ?? ($matrixTarif['TP'] ?? null);

        return response()->json([
            'success' => true,
            'data'    => $item,
            'tarif'   => $tarif,
        ]);
    }

    public function updateHonorariumData(Request $request, $id)
    {
        $item = HonorariumDosen::with('period')->findOrFail($id);
        $period = $item->period;

        if ($period->is_locked || !in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json(['success' => false, 'message' => 'Periode tidak dapat diubah karena sedang dalam proses approval atau terkunci.'], 403);
        }

        if ($period->created_by != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Hanya pembuat draft yang berwenang melakukan koreksi data.'], 403);
        }

        DB::beginTransaction();
        try {
            $matrixTarif = HonorariumCalculationService::getTarifMatrix();
            $tarif = $matrixTarif[$item->kode_jafung] ?? ($matrixTarif['TP'] ?? null);

            $calc = HonorariumCalculationService::calculateRowData($request->all(), $tarif);
            $item->fill($calc);
            $item->save();

            HonorariumCalculationService::syncPeriodSummary($period);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data honorarium dosen berhasil diperbarui.',
                'period'  => $period->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan penyesuaian: ' . $e->getMessage()], 500);
        }
    }

    public function deleteDosen($id)
    {
        $item = HonorariumDosen::with('period')->findOrFail($id);
        $period = $item->period;

        if ($period->is_locked || !in_array($period->status, ['draft', 'revision_requested'])) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus dosen saat periode terkunci atau sedang dalam proses approval.'], 403);
        }

        if ($period->created_by != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Hanya pembuat draft yang berwenang menghapus dosen penerima honor.'], 403);
        }

        DB::beginTransaction();
        try {
            $item->delete();
            HonorariumCalculationService::syncPeriodSummary($period);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Dosen berhasil dihapus dari daftar penerima honorarium periode ini.',
                'period'  => $period->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus dosen: ' . $e->getMessage()], 500);
        }
    }

    public function submitApproval(Request $request, $id)
    {
        $period = PayrollPeriod::with(['validator1', 'createdUser'])->findOrFail($id);

        if ($period->created_by != Auth::id()) {
            return back()->with('error', 'Hanya pembuat draft yang berwenang mengajukan periode honorarium ini.');
        }

        if (!in_array($period->status, ['draft', 'revision_requested'])) {
            return back()->with('error', 'Status periode tidak valid untuk diajukan approval.');
        }

        $period->update([
            'status'            => 'pending_val_1',
            'rejection_note'    => null,
            'rejection_by_role' => null,
        ]);

        $myProfile = $this->getCurrentProfile();
        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => 'validator_1',
            'role_label'        => 'Pembuat Draft',
            'user_id'           => Auth::id(),
            'karyawan_id'       => $myProfile ? $myProfile->id : null,
            'karyawan_name'     => $myProfile ? $myProfile->nama : Auth::user()->name,
            'action'            => 'submitted',
            'note'              => $request->catatan_pengajuan ?: 'Mengajukan berkas honorarium dosen ke Validator 1',
        ]);

        // Dispatch Notifikasi ke Validator 1
        $this->notifyKaryawanUser(
            $period->validator_1_id,
            new PayrollApprovalNotification(
                $period,
                "Periode honorarium dosen {$period->nama_periode} telah diajukan oleh " . Auth::user()->name . ". Mohon lakukan pemeriksaan dan validasi berkas.",
                'submitted',
                'Kroscek Honorarium Dosen'
            )
        );

        return back()->with('success', "Berkas honorarium dosen berhasil diajukan ke Validator 1 (" . ($period->validator1->nama ?? '-') . ").");
    }

    public function approveStep(Request $request, $id)
    {
        $period = PayrollPeriod::with(['validator1', 'validator2', 'approvalKaryawan'])->findOrFail($id);
        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;
        $karyawanId = $myProfile ? $myProfile->id : null;

        $nextStatus = '';
        $stepName = '';
        $roleLabel = '';
        $pesanSukses = '';

        if ($period->status === 'pending_val_1') {
            $nextStatus = 'pending_val_2';
            $stepName = 'validator_1';
            $roleLabel = 'Validator 1 (' . ($period->validator1->nama ?? $userName) . ')';
            $pesanSukses = "Berkas honorarium berhasil diverifikasi oleh Validator 1 dan diteruskan ke Validator 2 (" . ($period->validator2->nama ?? '-') . ").";
        } elseif ($period->status === 'pending_val_2') {
            $nextStatus = 'pending_approval';
            $stepName = 'validator_2';
            $roleLabel = 'Validator 2 (' . ($period->validator2->nama ?? $userName) . ')';
            $pesanSukses = "Berkas honorarium berhasil diverifikasi oleh Validator 2 dan diteruskan ke Approval Paling Atas (" . ($period->approvalKaryawan->nama ?? '-') . ").";
        } elseif ($period->status === 'pending_approval') {
            $nextStatus = 'locked';
            $stepName = 'approval';
            $roleLabel = 'Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? $userName) . ')';
            $pesanSukses = "Selamat! Berkas honorarium telah disetujui secara final dan resmi TERKUNCI (LOCKED).";
        } else {
            return back()->with('error', 'Status periode saat ini tidak membutuhkan aksi persetujuan.');
        }

        $updateData = [
            'status'            => $nextStatus,
            'rejection_note'    => null,
            'rejection_by_role' => null,
        ];

        if ($nextStatus === 'locked') {
            $updateData['is_locked'] = true;
            $updateData['locked_at'] = now();
            $updateData['locked_by'] = Auth::id();
        }

        $period->update($updateData);

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => $stepName,
            'role_label'        => $roleLabel,
            'user_id'           => Auth::id(),
            'karyawan_id'       => $karyawanId,
            'karyawan_name'     => $userName,
            'action'            => 'approved',
            'note'              => $request->note ?: 'Menyetujui berkas honorarium tahapan ' . $roleLabel,
        ]);

        // Dispatch Notifikasi ke tahap selanjutnya
        if ($nextStatus === 'pending_val_2') {
            $this->notifyKaryawanUser(
                $period->validator_2_id,
                new PayrollApprovalNotification(
                    $period,
                    "Berkas honorarium dosen {$period->nama_periode} telah disetujui oleh Validator 1. Menunggu validasi tahap 2 dari Anda.",
                    'pending_val_2',
                    'Validasi Tahap 2'
                )
            );
        } elseif ($nextStatus === 'pending_approval') {
            $this->notifyKaryawanUser(
                $period->approval_id,
                new PayrollApprovalNotification(
                    $period,
                    "Berkas honorarium dosen {$period->nama_periode} telah diverifikasi oleh Validator 1 & 2. Menunggu persetujuan final & penguncian Anda.",
                    'approval',
                    'Persetujuan Final & Lock'
                )
            );
        } elseif ($nextStatus === 'locked') {
            $this->notifyUserById(
                $period->created_by,
                new PayrollApprovalNotification(
                    $period,
                    "Selamat! Periode honorarium dosen {$period->nama_periode} telah disetujui secara final oleh " . ($period->approvalKaryawan->nama ?? 'Pimpinan') . " dan resmi TERKUNCI.",
                    'locked',
                    'Lihat Rekap Final'
                )
            );
        }

        // Auto mark as read unread notifications
        try {
            Auth::user()->unreadNotifications()
                ->where('data->payroll_period_id', $period->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            // Ignore
        }

        return back()->with('success', $pesanSukses);
    }

    public function rejectStep(Request $request, $id)
    {
        $request->validate([
            'catatan_revisi' => 'required|string|min:5',
        ], [
            'catatan_revisi.required' => 'Mohon isi catatan revisi agar pembuat draft mengetahui hal yang perlu diperbaiki.',
            'catatan_revisi.min'      => 'Catatan revisi minimal 5 karakter.',
        ]);

        $period = PayrollPeriod::with(['validator1', 'validator2', 'approvalKaryawan'])->findOrFail($id);
        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;
        $karyawanId = $myProfile ? $myProfile->id : null;

        $roleLabel = 'Approver';
        $stepName = $period->status;

        if ($period->status === 'pending_val_1') {
            $roleLabel = 'Validator 1 (' . ($period->validator1->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_val_2') {
            $roleLabel = 'Validator 2 (' . ($period->validator2->nama ?? $userName) . ')';
        } elseif ($period->status === 'pending_approval') {
            $roleLabel = 'Approval Paling Atas (' . ($period->approvalKaryawan->nama ?? $userName) . ')';
        } else {
            return back()->with('error', 'Periode saat ini tidak sedang dalam tahapan approval.');
        }

        $period->update([
            'status'            => 'revision_requested',
            'rejection_by_role' => $roleLabel,
            'rejection_note'    => $request->catatan_revisi,
        ]);

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => $stepName,
            'role_label'        => $roleLabel,
            'user_id'           => Auth::id(),
            'karyawan_id'       => $karyawanId,
            'karyawan_name'     => $userName,
            'action'            => 'revision_requested',
            'note'              => $request->catatan_revisi,
        ]);

        // Kirim Notifikasi Lonceng ke Pembuat Draft
        $this->notifyUserById(
            $period->created_by,
            new PayrollApprovalNotification(
                $period,
                "{$roleLabel} meminta revisi untuk honorarium dosen {$period->nama_periode}. Catatan: \"{$request->catatan_revisi}\".",
                'revision',
                'Benahi Data Honorarium'
            )
        );

        // Auto mark as read
        try {
            Auth::user()->unreadNotifications()
                ->where('data->payroll_period_id', $period->id)
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            // Ignore
        }

        return back()->with('warning', "Catatan revisi berhasil dikirim ke Pembuat Draft. Status periode kini: PERLU REVISI.");
    }

    public function destroy($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status !== 'draft' || $period->created_by != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Hanya draft periode milik sendiri yang dapat dihapus.'], 403);
        }

        DB::beginTransaction();
        try {
            HonorariumDosen::where('payroll_period_id', $period->id)->delete();
            PayrollPeriodApproval::where('payroll_period_id', $period->id)->delete();
            $period->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Draft periode honorarium berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus periode: ' . $e->getMessage()], 500);
        }
    }

    public function slipPdf($id)
    {
        $item = HonorariumDosen::with(['period', 'pegawai'])->findOrFail($id);
        $pdf = Pdf::loadView('admin::honorarium.slip_pdf', compact('item'))
            ->setPaper('a4', 'portrait');

        $filename = 'Slip_Honorarium_' . Str::slug($item->nama_dosen) . '_' . Str::slug($item->period->nama_periode) . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Buka Kunci (Unlock) Khusus oleh Pembuat Draft
     */
    public function unlock(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->created_by != Auth::id()) {
            return back()->with('error', 'Akses ditolak! Hanya Pembuat Draft yang berhak membuka kunci periode ini.');
        }

        if (!$period->is_locked) {
            return back()->with('info', 'Periode ini belum dalam keadaan terkunci.');
        }

        $request->validate([
            'alasan_buka_kunci' => 'required|string|min:5',
        ], [
            'alasan_buka_kunci.required' => 'Mohon sertakan alasan pembukaan kunci.',
        ]);

        $myProfile = $this->getCurrentProfile();
        $userName = $myProfile ? $myProfile->nama : Auth::user()->name;

        $period->update([
            'status'          => 'draft',
            'locked_at'       => null,
            'locked_by'       => null,
            'unlocked_reason' => $request->alasan_buka_kunci,
        ]);

        PayrollPeriodApproval::create([
            'payroll_period_id' => $period->id,
            'step'              => 'unlocked',
            'role_label'        => 'Pembuat Draft',
            'user_id'           => Auth::id(),
            'karyawan_id'       => $myProfile ? $myProfile->id : null,
            'karyawan_name'     => $userName,
            'action'            => 'unlocked',
            'note'              => $request->alasan_buka_kunci,
        ]);

        return back()->with('warning', "Kunci pada periode honorarium {$period->nama_periode} telah berhasil DIBUKA. Anda dapat membenahi data kembali.");
    }

    /**
     * Ekspor Rekap Honorarium Excel
     */
    public function exportExcel($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $fileName = 'REKAP_HONORARIUM_' . Str::slug($period->nama_periode) . '.xlsx';
        return Excel::download(new RekapHonorariumExport($period), $fileName);
    }

    /**
     * Ekspor Rekap Transfer Bank Honorarium Excel
     */
    public function exportBank($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $fileName = 'REKAP_TRANSFER_BANK_HONORARIUM_' . Str::slug($period->nama_periode) . '.xlsx';
        return Excel::download(new RekapBankTransferHonorariumExport($period), $fileName);
    }

    /**
     * Download Semua Slip Honorarium Perorangan dalam Format ZIP
     */
    public function downloadAllSlip($id)
    {
        $period = PayrollPeriod::with('honorariums')->findOrFail($id);
        $dosens = $period->honorariums;

        if ($dosens->isEmpty()) {
            return back()->with('error', 'Tidak ada data dosen penerima honor pada periode ini.');
        }

        $zipFileName = 'SLIP_HONORARIUM_ALL_' . Str::slug($period->nama_periode) . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($dosens as $item) {
                $pdf = Pdf::loadView('admin::honorarium.slip_pdf', compact('item'))
                    ->setPaper('a4', 'portrait');

                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $item->nama_dosen);
                $pdfFileName = 'SLIP_HONOR_' . ($item->nik_nip ?: 'DOSEN') . '_' . $cleanName . '.pdf';

                $zip->addFromString($pdfFileName, $pdf->output());
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file arsip ZIP.');
    }
}
