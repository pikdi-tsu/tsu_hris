@extends('system::template.admin.header')
@section('title', $title ?? 'Payroll Karyawan (Gaji Bulanan)')

@section('css')
    <style>
        /* === TSU Color Tokens === */
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-accent-red: #b91c1c;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === Stat Cards Grid === */
        .tsu-stat-grid-payroll {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-payroll {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-payroll {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card__icon {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.74rem;
            opacity: 0.88;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Color variations */
        .tsu-stat-card--periods {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--periods .tsu-stat-card__title { color: #a5d8dd; }

        .tsu-stat-card--locked {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }
        .tsu-stat-card--locked .tsu-stat-card__title { color: #a7f3d0; }

        .tsu-stat-card--draft {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--draft .tsu-stat-card__title { color: #fef3c7; }

        .tsu-stat-card--latest {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .tsu-stat-card--latest .tsu-stat-card__title { color: #bae6fd; }

        /* === TSU Modern Card === */
        .tsu-card {
            background: #ffffff;
            border: 1px solid var(--tsu-border-gray, #e2e8f0);
            border-radius: var(--tsu-radius-lg, 12px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.75rem;
            overflow: hidden;
        }

        .tsu-card__header {
            padding: 1.1rem 1.4rem;
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .tsu-card__title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .tsu-card__title i {
            color: var(--tsu-primary, #094b54);
            font-size: 1.15rem;
        }

        /* Buttons */
        .tsu-btn-primary {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }

        .tsu-btn-primary:hover {
            background: var(--tsu-primary-dark, #07383f);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            transform: translateY(-1px);
        }

        .tsu-btn-info-action {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px 0 0 6px;
            transition: all 0.2s ease;
        }

        .tsu-btn-info-action:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }

        .tsu-btn-info-split {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-left: none;
            border-radius: 0 6px 6px 0;
            padding: 0.35rem 0.55rem;
            transition: all 0.2s ease;
        }

        .tsu-btn-info-split:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.86rem;
        }

        .tsu-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            text-align: center;
            padding: 0.85rem 0.75rem;
            border-bottom: 2px solid var(--tsu-border-gray, #e2e8f0) !important;
            border-top: none !important;
        }

        .tsu-table tbody td {
            vertical-align: middle;
            padding: 0.8rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Table Badges without icons */
        .tsu-badge-kode {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.15rem 0.45rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .tsu-badge-cutoff {
            display: inline-block;
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.25rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .tsu-badge-pegawai {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Modal Gradient Header */
        .tsu-modal-header {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%);
            color: #ffffff;
            border-top-left-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            border-top-right-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            padding: 1.1rem 1.4rem;
        }

        .tsu-modal-header .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tsu-modal-header .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
            transition: opacity 0.2s ease;
        }
        .tsu-modal-header .close:hover {
            opacity: 1;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
            padding: 0.35rem 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header Component --}}
    <x-tsu-page-header
        title="Payroll Karyawan (Gaji Bulanan)"
        subtitle="Manajemen periode penggajian bulanan, perhitungan THP, dan alur approval bertingkat"
        icon="fas fa-file-invoice-dollar"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-primary font-weight-bold" data-toggle="modal" data-target="#modalCreatePeriod">
                <i class="fas fa-plus mr-1"></i> Buat Periode Payroll Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- 4 Stat Cards Grid --}}
            <div class="tsu-stat-grid-payroll">
                {{-- Card 1: Total Periode Payroll --}}
                <div class="tsu-stat-card tsu-stat-card--periods">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Periode Payroll</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_periods'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Seluruh riwayat periode penggajian</div>
                </div>

                {{-- Card 2: Terkunci (Final) --}}
                <div class="tsu-stat-card tsu-stat-card--locked">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Terkunci (Final)</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_locked'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Payroll disetujui &amp; siap bayar</div>
                </div>

                {{-- Card 3: Draft / Perlu Revisi --}}
                <div class="tsu-stat-card tsu-stat-card--draft">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="tsu-stat-card__title">Draft / Perlu Revisi</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_draft'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Periode</span></div>
                    <div class="tsu-stat-card__subtext">Dalam proses penyusunan &amp; validasi</div>
                </div>

                {{-- Card 4: Total Gaji Bersih Terakhir --}}
                <div class="tsu-stat-card tsu-stat-card--latest">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="tsu-stat-card__title">Gaji Bersih Terakhir</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">Rp {{ number_format($stats['latest_payout'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Total THP periode payroll terbaru</div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <h5 class="tsu-card__title">
                        <i class="fas fa-file-invoice-dollar"></i> Data Periode Payroll Karyawan
                    </h5>
                    <div class="text-muted small">
                        Daftar seluruh periode payroll bulanan beserta status approval
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table class="table tsu-table" id="table-periods">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="18%">Kode &amp; Periode</th>
                                    <th width="16%" class="text-center">Rentang Cut-Off Presensi</th>
                                    <th width="18%">Struktur Approval</th>
                                    <th width="8%" class="text-center">Pegawai</th>
                                    <th width="14%" class="text-right">Gaji Bersih (THP)</th>
                                    <th width="12%" class="text-center">Status Approval</th>
                                    <th width="10%" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periods as $idx => $p)
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <a href="{{ route('admin.payroll.show', $p->id) }}" class="font-weight-bold" style="color: var(--tsu-primary, #094b54); font-size: 0.95rem;">
                                                {{ $p->nama_periode }}
                                            </a>
                                            <br>
                                            <span class="tsu-badge-kode mt-1">{{ $p->kode_periode }}</span>
                                            @if($p->createdUser)
                                                <small class="text-muted d-block mt-1">Dibuat: {{ $p->createdUser->name }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($p->start_date_cutoff && $p->end_date_cutoff)
                                                <span class="tsu-badge-cutoff">
                                                    {{ \Carbon\Carbon::parse($p->start_date_cutoff)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($p->end_date_cutoff)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted font-italic small">Bulan {{ $p->bulan_nama }} {{ $p->tahun }}</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 8.5pt;">
                                            <div><span class="text-muted">Val 1:</span> <strong class="text-dark">{{ $p->validator1->nama ?? '-' }}</strong></div>
                                            <div><span class="text-muted">Val 2:</span> <strong class="text-dark">{{ $p->validator2->nama ?? '-' }}</strong></div>
                                            <div><span class="text-muted">Appr:</span> <strong class="text-dark">{{ $p->approvalKaryawan->nama ?? '-' }}</strong></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="tsu-badge-pegawai">{{ $p->total_pegawai }} Org</span>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-success" style="font-size: 0.95rem;">
                                                Rp {{ number_format($p->total_gaji_bersih, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            {!! $p->status_badge !!}
                                            @if($p->status === 'revision_requested' && $p->rejection_note)
                                                <br><small class="text-danger font-italic text-truncate d-inline-block mt-1" style="max-width: 160px;" title="{{ $p->rejection_note }}">"{{ $p->rejection_note }}"</small>
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.payroll.show', $p->id) }}" class="btn btn-sm tsu-btn-info-action" title="Buka / Kroscek Detail">
                                                    <i class="fas fa-search mr-1"></i> Buka
                                                </a>
                                                <button type="button" class="btn btn-sm tsu-btn-info-split dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 8px;">
                                                    <a class="dropdown-item py-2" href="{{ route('admin.payroll.export-excel', $p->id) }}">
                                                        <i class="fas fa-file-excel mr-2 text-success"></i> Export Rekap Excel
                                                    </a>
                                                    <a class="dropdown-item py-2" href="{{ route('admin.payroll.export-bank', $p->id) }}">
                                                        <i class="fas fa-university mr-2 text-primary"></i> Export Transfer Bank
                                                    </a>
                                                    <a class="dropdown-item py-2" href="{{ route('admin.payroll.download-all-slip', $p->id) }}">
                                                        <i class="fas fa-file-archive mr-2 text-danger"></i> Download Semua Slip (ZIP)
                                                    </a>
                                                    <div class="dropdown-divider"></div>

                                                    {{-- Khusus Pembuat Draft --}}
                                                    @if($p->created_by == Auth::id())
                                                        @if($p->is_locked)
                                                            <a href="{{ route('admin.payroll.show', $p->id) }}" class="dropdown-item py-2 text-warning font-weight-bold">
                                                                <i class="fas fa-unlock mr-2"></i> Buka Kunci (Unlock)
                                                            </a>
                                                        @else
                                                            <form action="{{ route('admin.payroll.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode ini beserta seluruh datanya?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item py-2 text-danger">
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
                                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
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
    </section>

    {{-- Modal Create Period --}}
    <div class="modal fade" id="modalCreatePeriod" role="dialog" aria-labelledby="modalCreatePeriodLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <form action="{{ route('admin.payroll.store') }}" method="POST">
                    @csrf
                    <div class="modal-header tsu-modal-header">
                        <h5 class="modal-title font-weight-bold" id="modalCreatePeriodLabel">
                            <i class="fas fa-calendar-plus mr-1"></i> Buat Periode Payroll &amp; Tentukan Approval
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        {{-- Section 1: Periode & Cutoff --}}
                        <h6 class="font-weight-bold border-bottom pb-2 mb-3" style="color: var(--tsu-primary, #094b54);">
                            <i class="fas fa-calendar-alt mr-1"></i> 1. Periode &amp; Cut-Off Presensi
                        </h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Bulan Penggajian <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-control select2" required style="width: 100%;">
                                    @php $curMonth = date('n'); @endphp
                                    @foreach($bulanList as $num => $nama)
                                        <option value="{{ $num }}" {{ $num == $curMonth ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Tahun <span class="text-danger">*</span></label>
                                <select name="tahun" class="form-control select2" required style="width: 100%;">
                                    @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="p-3 my-2 rounded" style="background: #f8fafc; border: 1px solid var(--tsu-border-gray, #e2e8f0);">
                            <label class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-clock text-primary mr-1"></i> Rentang Tanggal Cut-Off Presensi
                            </label>
                            <small class="text-muted mb-2 d-block" style="font-size: 8pt;">
                                Digunakan untuk menghitung kehadiran valid (Uang Transport Rp 20.000/hari), Jam Lembur, dan Potongan Unpaid Leave secara otomatis.
                            </small>
                            <div class="row">
                                <div class="col-6 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Mulai Cut-Off</label>
                                    <input type="date" name="start_date_cutoff" class="form-control form-control-sm" style="border-radius: 6px;">
                                </div>
                                <div class="col-6 form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Selesai Cut-Off</label>
                                    <input type="date" name="end_date_cutoff" class="form-control form-control-sm" style="border-radius: 6px;">
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Penugasan Validator & Approval --}}
                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4" style="color: var(--tsu-primary, #094b54);">
                            <i class="fas fa-users-cog mr-1"></i> 2. Penugasan Validator &amp; Approval Bertingkat
                        </h6>
                        <p class="text-muted small mb-3" style="font-size: 8.5pt;">
                            Pilih nama pejabat/karyawan yang bertugas memvalidasi dan menyetujui payroll periode ini.
                        </p>

                        <div class="form-group">
                            <label class="font-weight-bold small text-dark">
                                <i class="fas fa-user-check text-info mr-1"></i> Validator 1 (Pemeriksa Tingkat 1) <span class="text-danger">*</span>
                            </label>
                            <select name="validator_1_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Validator 1 --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) — {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small text-dark">
                                <i class="fas fa-user-check text-primary mr-1"></i> Validator 2 (Pemeriksa Tingkat 2) <span class="text-danger">*</span>
                            </label>
                            <select name="validator_2_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Validator 2 --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) — {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small text-dark">
                                <i class="fas fa-crown text-warning mr-1"></i> Approval Paling Atas (Persetujuan Final &amp; Auto-Lock) <span class="text-danger">*</span>
                            </label>
                            <select name="approval_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Approval Paling Atas --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) — {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-0 mt-3">
                            <label class="font-weight-bold small text-dark">Catatan Pengajuan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan internal pengelola payroll..." style="border-radius: 6px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3 border-top">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
                        <button type="submit" class="btn btn-sm text-white px-4 font-weight-bold" style="background: #094b54; border-radius: 6px;">
                            <i class="fas fa-magic mr-1"></i> Simpan &amp; Generate Data Awal
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
