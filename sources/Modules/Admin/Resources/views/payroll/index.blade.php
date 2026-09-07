@extends('system::template.admin.header')

@section('content')
    <div class="container-fluid">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Metric Cards --}}
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2 border-0 bg-white" style="border-left: 4px solid #3b82f6 !important;">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Periode Payroll</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_periods'] }} Periode</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300 text-primary" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2 border-0 bg-white" style="border-left: 4px solid #10b981 !important;">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Terkunci (Final)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_locked'] }} Periode</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-lock fa-2x text-gray-300 text-success" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2 border-0 bg-white" style="border-left: 4px solid #f59e0b !important;">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Draft / Perlu Revisi</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_draft'] }} Periode</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-edit fa-2x text-gray-300 text-warning" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 py-2 border-0 bg-white" style="border-left: 4px solid #06b6d4 !important;">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Gaji Bersih Terakhir</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($stats['latest_payout'], 0, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300 text-info" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                <h3 class="card-title font-weight-bold m-0" style="font-size: 1.15rem;">
                    <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i> Data Periode Payroll Karyawan
                </h3>
                <button type="button" class="btn btn-primary font-weight-bold shadow-sm mt-2 mt-md-0" data-toggle="modal" data-target="#modalCreatePeriod">
                    <i class="fas fa-plus mr-1"></i> Buat Periode Payroll Baru
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-sm" id="table-periods">
                        <thead class="bg-light">
                            <tr class="text-center align-middle">
                                <th width="4%">No</th>
                                <th width="16%">Kode & Periode</th>
                                <th width="16%">Rentang Cut-Off Presensi</th>
                                <th width="16%">Struktur Approval</th>
                                <th width="7%">Pegawai</th>
                                <th width="12%">Gaji Bersih (THP)</th>
                                <th width="14%">Status Approval</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periods as $idx => $p)
                                <tr>
                                    <td class="text-center align-middle">{{ $idx + 1 }}</td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.payroll.show', $p->id) }}" class="font-weight-bold text-primary" style="font-size: 1rem;">
                                            {{ $p->nama_periode }}
                                        </a>
                                        <br>
                                        <small class="text-muted"><i class="fas fa-barcode mr-1"></i>{{ $p->kode_periode }}</small>
                                        @if($p->createdUser)
                                            <br><small class="text-muted"><i class="fas fa-user-edit mr-1"></i>Dibuat: {{ $p->createdUser->name }}</small>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($p->start_date_cutoff && $p->end_date_cutoff)
                                            <span class="badge badge-light border px-2 py-1">
                                                <i class="fas fa-calendar-check mr-1 text-primary"></i>
                                                {{ \Carbon\Carbon::parse($p->start_date_cutoff)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($p->end_date_cutoff)->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted font-italic">Bulan {{ $p->bulan_nama }} {{ $p->tahun }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" style="font-size: 8pt;">
                                        <div><strong class="text-secondary">Val 1:</strong> {{ $p->validator1->nama ?? '-' }}</div>
                                        <div><strong class="text-secondary">Val 2:</strong> {{ $p->validator2->nama ?? '-' }}</div>
                                        <div><strong class="text-secondary">Appr:</strong> {{ $p->approvalKaryawan->nama ?? '-' }}</div>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">
                                        {{ $p->total_pegawai }} Org
                                    </td>
                                    <td class="text-right align-middle text-success font-weight-bold" style="font-size: 0.95rem;">
                                        Rp {{ number_format($p->total_gaji_bersih, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        {!! $p->status_badge !!}
                                        @if($p->status === 'revision_requested' && $p->rejection_note)
                                            <br><small class="text-danger font-italic text-truncate d-inline-block" style="max-width: 160px;" title="{{ $p->rejection_note }}">"{{ $p->rejection_note }}"</small>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.payroll.show', $p->id) }}" class="btn btn-sm btn-info" title="Buka / Kroscek Detail">
                                                <i class="fas fa-search mr-1"></i> Buka
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow">
                                                <a class="dropdown-item" href="{{ route('admin.payroll.export-excel', $p->id) }}">
                                                    <i class="fas fa-file-excel mr-2 text-success"></i> Export Rekap Excel
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.payroll.export-bank', $p->id) }}">
                                                    <i class="fas fa-university mr-2 text-primary"></i> Export Transfer Bank
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.payroll.download-all-slip', $p->id) }}">
                                                    <i class="fas fa-file-archive mr-2 text-danger"></i> Download Semua Slip (ZIP)
                                                </a>
                                                <div class="dropdown-divider"></div>

                                                {{-- Khusus Pembuat Draft --}}
                                                @if($p->created_by == Auth::id())
                                                    @if($p->is_locked)
                                                        <a href="{{ route('admin.payroll.show', $p->id) }}" class="dropdown-item text-warning font-weight-bold">
                                                            <i class="fas fa-unlock mr-2"></i> Buka Kunci (Unlock)
                                                        </a>
                                                    @else
                                                        <form action="{{ route('admin.payroll.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode ini beserta seluruh datanya?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash-alt mr-2"></i> Hapus Periode
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                                        <p class="font-weight-bold mb-1">Belum ada periode payroll yang dibuat.</p>
                                        <p class="small">Klik tombol <strong>"Buat Periode Payroll Baru"</strong> di atas untuk memulai penarikan dan perhitungan gaji bulanan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create Period --}}
    <div class="modal fade" id="modalCreatePeriod" role="dialog" aria-labelledby="modalCreatePeriodLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.payroll.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="modalCreatePeriodLabel">
                            <i class="fas fa-calendar-plus mr-2"></i> Buat Periode Payroll & Tentukan Approval
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Section 1: Periode & Cutoff --}}
                        <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i> 1. Periode & Cut-Off Presensi
                        </h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small">Bulan Penggajian <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-control select2" required style="width: 100%;">
                                    @php $curMonth = date('n'); @endphp
                                    @foreach($bulanList as $num => $nama)
                                        <option value="{{ $num }}" {{ $num == $curMonth ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small">Tahun <span class="text-danger">*</span></label>
                                <select name="tahun" class="form-control select2" required style="width: 100%;">
                                    @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="card bg-light border p-2 my-2">
                            <label class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-clock text-primary mr-1"></i> Rentang Tanggal Cut-Off Presensi
                            </label>
                            <small class="text-muted mb-2 d-block" style="font-size: 8pt;">
                                Digunakan untuk menghitung kehadiran valid (Uang Transport Rp 20.000/hari), Jam Lembur, dan Potongan Unpaid Leave secara otomatis.
                            </small>
                            <div class="row">
                                <div class="col-6 form-group mb-0">
                                    <label class="small font-weight-bold">Mulai Cut-Off</label>
                                    <input type="date" name="start_date_cutoff" class="form-control form-control-sm">
                                </div>
                                <div class="col-6 form-group mb-0">
                                    <label class="small font-weight-bold">Selesai Cut-Off</label>
                                    <input type="date" name="end_date_cutoff" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Penugasan Validator & Approval --}}
                        <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-2 mt-3">
                            <i class="fas fa-users-cog mr-1"></i> 2. Penugasan Validator & Approval Bertingkat
                        </h6>
                        <p class="text-muted small mb-2" style="font-size: 8pt;">
                            Pilih nama pejabat/karyawan yang bertugas memvalidasi dan menyetujui payroll periode ini.
                        </p>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-user-check text-info mr-1"></i> Validator 1 (Pemeriksa Tingkat 1) <span class="text-danger">*</span>
                            </label>
                            <select name="validator_1_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Validator 1 --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) - {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-user-check text-primary mr-1"></i> Validator 2 (Pemeriksa Tingkat 2) <span class="text-danger">*</span>
                            </label>
                            <select name="validator_2_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Validator 2 --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) - {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-crown text-warning mr-1"></i> Approval Paling Atas (Persetujuan Final & Auto-Lock) <span class="text-danger">*</span>
                            </label>
                            <select name="approval_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Approval Paling Atas --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) - {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-2">
                            <label class="font-weight-bold small">Catatan Pengajuan (Opsional)</label>
                            <textarea name="catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan internal pengelola payroll..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4">
                            <i class="fas fa-magic mr-1"></i> Simpan & Generate Data Awal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Hindari focus trap Bootstrap agar input search Select2 bisa diketik
            if ($.fn.modal && $.fn.modal.Constructor) {
                $.fn.modal.Constructor.prototype._enforceFocus = function() {};
            }

            // Inisialisasi Select2 di dalam modal dengan dropdownParent
            $('#modalCreatePeriod .select2').select2({
                dropdownParent: $('#modalCreatePeriod'),
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || '-- Pilih --';
                },
                allowClear: false
            });

            $('#modalCreatePeriod').on('shown.bs.modal', function () {
                $('#modalCreatePeriod .select2').select2({
                    dropdownParent: $('#modalCreatePeriod'),
                    width: '100%'
                });
            });

            $('#table-periods').DataTable({
                paging: true,
                searching: true,
                ordering: false,
                info: true,
                autoWidth: false,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ periode",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });
        });
    </script>
@endsection
