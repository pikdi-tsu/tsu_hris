@extends('system::template.admin.header')
@section('title', $title ?? 'Detail Periode THR')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

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

        /* === Stepper Styling === */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 0.5rem;
        }
        .stepper-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            text-align: center;
        }
        .stepper-item::before {
            position: absolute;
            content: "";
            border-bottom: 3px solid #e2e8f0;
            width: 100%;
            top: 20px;
            left: -50%;
            z-index: 1;
        }
        .stepper-item:first-child::before {
            content: none;
        }
        .stepper-item .step-counter {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 6px;
            border: 3px solid #e2e8f0;
            transition: all 0.3s;
        }
        .stepper-item.completed .step-counter {
            background-color: #10b981;
            border-color: #10b981;
            color: #ffffff;
        }
        .stepper-item.completed::before {
            border-color: #10b981;
        }
        .stepper-item.active .step-counter {
            background-color: #3b82f6;
            border-color: #93c5fd;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
        }
        .stepper-item.revision .step-counter {
            background-color: #ef4444;
            border-color: #fca5a5;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.25);
        }
        .stepper-item .step-name {
            font-weight: 600;
            font-size: 0.82rem;
            color: #1e293b;
        }
        .stepper-item .step-user {
            font-size: 0.72rem;
            color: #64748b;
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            right: 1.15rem;
            bottom: 0.85rem;
            font-size: 3.2rem;
            opacity: 0.18;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.4rem;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.78rem;
            opacity: 0.88;
            font-weight: 500;
        }

        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--total .tsu-stat-card__title { color: #cce6e9; }

        .tsu-stat-card--penuh {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }
        .tsu-stat-card--penuh .tsu-stat-card__title { color: #a7f3d0; }

        .tsu-stat-card--prorata {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--prorata .tsu-stat-card__title { color: #fef3c7; }

        .tsu-stat-card--anggaran {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .tsu-stat-card--anggaran .tsu-stat-card__title { color: #bae6fd; }

        /* === TSU Modern Card === */
        .tsu-card {
            background: #ffffff;
            border: 1px solid var(--tsu-border-gray, #e2e8f0);
            border-radius: var(--tsu-radius-lg, 12px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
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

        .nav-pills .nav-link {
            border-radius: var(--tsu-radius, 8px);
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            padding: 0.4rem 0.95rem;
            transition: all 0.2s;
        }

        .nav-pills .nav-link.active {
            background-color: var(--tsu-primary, #094b54) !important;
            color: #ffffff !important;
        }
    </style>
@endsection

@section('content')
    <section class="content pt-3">
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Alert Perlu Revisi --}}
            @if($period->status === 'revision_requested')
                <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-circle fa-2x mr-3 mt-1 text-danger"></i>
                        <div>
                            <h6 class="font-weight-bold mb-1">Permintaan Revisi dari: {{ $period->rejection_by_role ?: 'Approver' }}</h6>
                            <p class="mb-1 text-dark" style="font-size: 0.95rem;">"{{ $period->rejection_note }}"</p>
                            <small class="text-muted">Silahkan lakukan koreksi penyesuaian nominal atau hitung ulang, kemudian klik tombol <strong>"Kirim Ulang ke Validator 1"</strong> di atas.</small>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Alert Periode Terbuka Kembali (Unlocked Reason) --}}
            @if($period->unlocked_reason && $period->status === 'draft')
                <div class="alert alert-warning border-0 shadow-sm mb-3" style="border-radius: 8px;">
                    <i class="fas fa-unlock mr-2"></i> <strong>Periode Diberi Akses Koreksi (Unlocked):</strong> {{ $period->unlocked_reason }}
                </div>
            @endif

            {{-- Top Banner Card: Title, Actions & 4-Step Stepper --}}
            <div class="tsu-card mb-3">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <div class="d-flex align-items-center mb-2 mb-lg-0">
                            <a href="{{ route('admin.payroll.thr.index') }}" class="btn btn-outline-secondary btn-sm mr-3" style="border-radius: 8px;" title="Kembali ke Daftar Periode">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <h4 class="m-0 font-weight-bold text-dark mr-2">{{ $period->nama_periode }}</h4>
                                    {!! $period->status_badge !!}
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-calendar-alt mr-1 text-primary"></i> Cut-Off: <strong>{{ \Carbon\Carbon::parse($period->tanggal_cutoff)->format('d M Y') }}</strong> &bull;
                                    <i class="fas fa-file-signature mr-1 text-primary"></i> Surat: <strong>{{ $period->kota_surat }}, {{ \Carbon\Carbon::parse($period->tanggal_surat)->format('d M Y') }}</strong> &bull;
                                    <i class="fas fa-user-edit mr-1 text-primary"></i> Pembuat: <strong>{{ $period->createdUser->name ?? 'Admin' }}</strong>
                                    @if($period->is_locked && $period->lockedUser)
                                        &bull; <i class="fas fa-lock mr-1 text-success"></i> Dikunci oleh: <strong>{{ $period->lockedUser->name }}</strong> ({{ $period->locked_at ? $period->locked_at->format('d/m/Y H:i') : '' }})
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons Header --}}
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            {{-- Riwayat Approval (Semua Pengguna) --}}
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2 font-weight-bold" id="btnShowHistory" title="Lihat Jejak Rekam Approval">
                                <i class="fas fa-history mr-1"></i> Riwayat Approval
                            </button>

                            {{-- AKSI PEMBUAT DRAFT --}}
                            @if($isCreator)
                                @if(in_array($period->status, ['draft', 'revision_requested']))
                                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalEditApproversShow" title="Ganti susunan Validator 1, Validator 2, atau Approval">
                                        <i class="fas fa-user-edit mr-1"></i> Ganti Approval
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold mr-2" id="btnRecalculate" title="Hitung ulang otomatis masa kerja & upah tetap">
                                        <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalSubmitApproval">
                                        <i class="fas fa-paper-plane mr-1"></i> {{ $period->status === 'revision_requested' ? 'Kirim Ulang ke Validator 1' : 'Kirim ke Validator 1' }}
                                    </button>
                                @elseif($period->is_locked)
                                    <button type="button" class="btn btn-warning btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalUnlockPeriod">
                                        <i class="fas fa-unlock mr-1"></i> Buka Kunci (Unlock)
                                    </button>
                                @endif
                            @endif

                            {{-- AKSI VALIDATOR 1 --}}
                            @if($isValidator1 && $period->status === 'pending_val_1')
                                <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                    <i class="fas fa-check-circle mr-1"></i> Setujui (Lanjut ke {{ $period->validator_2_id ? 'Validator 2' : 'Approval Final' }})
                                </button>
                                <button type="button" class="btn btn-danger btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalRejectStep">
                                    <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                </button>
                            @endif

                            {{-- AKSI VALIDATOR 2 --}}
                            @if($isValidator2 && $period->status === 'pending_val_2')
                                <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                    <i class="fas fa-check-circle mr-1"></i> Setujui (Lanjut ke Approval Final)
                                </button>
                                <button type="button" class="btn btn-danger btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalRejectStep">
                                    <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                </button>
                            @endif

                            {{-- AKSI APPROVAL PALING ATAS --}}
                            @if($isApproval && $period->status === 'pending_approval')
                                <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                    <i class="fas fa-lock mr-1"></i> Setujui Final (Kunci Periode)
                                </button>
                                <button type="button" class="btn btn-danger btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalRejectStep">
                                    <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                </button>
                            @endif

                            {{-- Ekspor Dokumen --}}
                            @if(($isCreator && in_array($period->status, ['draft', 'revision_requested'])) || $period->is_locked)
                                <div class="btn-group">
                                    <a href="{{ route('admin.payroll.thr.export-excel', $period->id) }}" class="btn btn-sm btn-success font-weight-bold" title="Export Format Resmi 7 Kolom">
                                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                                    </a>
                                    <a href="{{ route('admin.payroll.thr.download-all-slip', $period->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold" title="Download Semua Slip PDF (ZIP)">
                                        <i class="fas fa-file-archive mr-1"></i> Unduh All Slip (ZIP)
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Visual Stepper 4 Tahap --}}
                    @php
                        $logDraft = $period->approvals->where('action', 'submitted')->sortByDesc('created_at')->first();
                        $logVal1 = $period->approvals->where('step', 'validator_1')->where('action', 'approved')->sortByDesc('created_at')->first();
                        $logVal2 = $period->approvals->where('step', 'validator_2')->where('action', 'approved')->sortByDesc('created_at')->first();
                        $logApproval = $period->approvals->where('step', 'approval')->where('action', 'approved')->sortByDesc('created_at')->first();

                        $step1Class = ($period->status === 'draft' || $period->status === 'revision_requested') ? 'active' : 'completed';
                        if ($period->status === 'revision_requested') $step1Class = 'revision';

                        $step2Class = '';
                        if ($period->status === 'pending_val_1') $step2Class = 'active';
                        elseif (in_array($period->status, ['pending_val_2', 'pending_approval', 'locked'])) $step2Class = 'completed';

                        $step3Class = '';
                        if ($period->status === 'pending_val_2') $step3Class = 'active';
                        elseif (in_array($period->status, ['pending_approval', 'locked'])) $step3Class = 'completed';

                        $step4Class = '';
                        if ($period->status === 'pending_approval') $step4Class = 'active';
                        elseif ($period->status === 'locked') $step4Class = 'completed';
                    @endphp

                    <div class="stepper-wrapper pt-2">
                        {{-- Step 1: Pembuat Draft --}}
                        <div class="stepper-item {{ $step1Class }}">
                            <div class="step-counter">
                                @if($step1Class === 'completed') <i class="fas fa-check"></i>
                                @elseif($step1Class === 'revision') <i class="fas fa-exclamation"></i>
                                @else 1 @endif
                            </div>
                            <div class="step-name">1. Pembuat Draft</div>
                            <div class="step-user font-weight-bold">{{ $period->createdUser->name ?? 'Pembuat' }}</div>
                            @if($logDraft)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-paper-plane mr-1"></i>{{ $logDraft->created_at->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <div class="step-time text-muted mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-clock mr-1"></i>{{ $period->created_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- Step 2: Validator 1 --}}
                        <div class="stepper-item {{ $step2Class }}">
                            <div class="step-counter">
                                @if($step2Class === 'completed') <i class="fas fa-check"></i>
                                @else 2 @endif
                            </div>
                            <div class="step-name">2. Validator 1</div>
                            <div class="step-user font-weight-bold" title="{{ $period->validator1->nama ?? '-' }}">
                                {{ $period->validator1->nama ?? 'Belum Ditentukan' }}
                            </div>
                            @if($logVal1)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-check-circle mr-1"></i>{{ $logVal1->created_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- Step 3: Validator 2 --}}
                        <div class="stepper-item {{ $step3Class }}">
                            <div class="step-counter">
                                @if($step3Class === 'completed') <i class="fas fa-check"></i>
                                @else 3 @endif
                            </div>
                            <div class="step-name">3. Validator 2</div>
                            <div class="step-user font-weight-bold" title="{{ $period->validator2->nama ?? 'Dilewati' }}">
                                {{ $period->validator2 ? $period->validator2->nama : '(Opsional / Lewati)' }}
                            </div>
                            @if($logVal2)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-check-circle mr-1"></i>{{ $logVal2->created_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- Step 4: Approval Paling Atas --}}
                        <div class="stepper-item {{ $step4Class }}">
                            <div class="step-counter">
                                @if($step4Class === 'completed') <i class="fas fa-lock"></i>
                                @else 4 @endif
                            </div>
                            <div class="step-name">4. Approval Final &amp; Lock</div>
                            <div class="step-user font-weight-bold" title="{{ $period->approvalKaryawan->nama ?? '-' }}">
                                {{ $period->approvalKaryawan->nama ?? 'Belum Ditentukan' }}
                            </div>
                            @if($logApproval)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-lock mr-1"></i>{{ $logApproval->created_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- 4 Stat Cards Ringkasan Periode Ini --}}
            <div class="tsu-stat-grid-payroll">
                <!-- Card 1: Total Penerima -->
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Penerima THR</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_pegawai'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Dosen &amp; Tendik Aktif</div>
                </div>

                <!-- Card 2: Penerima Penuh -->
                <div class="tsu-stat-card tsu-stat-card--penuh">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="tsu-stat-card__title">Masa Kerja &ge; 12 Bln (Penuh)</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_penuh'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Upah Penuh 100% (1 Bulan)</div>
                </div>

                <!-- Card 3: Penerima Pro Rata -->
                <div class="tsu-stat-card tsu-stat-card--prorata">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="tsu-stat-card__title">Masa Kerja &lt; 12 Bln (Pro Rata)</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_prorata'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Proporsional (Masa Kerja / 12)</div>
                </div>

                <!-- Card 4: Total Anggaran THR -->
                <div class="tsu-stat-card tsu-stat-card--anggaran">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Anggaran THR</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($stats['total_anggaran'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Total Pengeluaran Bersih</div>
                </div>
            </div>

            {{-- Tabel Data Penerima THR --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <div>
                        <!-- Filter Tab Status -->
                        <ul class="nav nav-pills" id="tabStatusThr">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-status="">Semua ({{ $stats['total_pegawai'] }})</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-status="Penuh">
                                    <i class="fas fa-check-circle mr-1"></i> Penuh ({{ $stats['total_penuh'] }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-status="Pro Rata">
                                    <i class="fas fa-percentage mr-1"></i> Pro Rata ({{ $stats['total_prorata'] }})
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 170px;">
                            <select class="form-control form-control-sm" id="filterTipe" style="border-radius: 6px;">
                                <option value="">-- Semua Tipe --</option>
                                <option value="Dosen">Dosen</option>
                                <option value="Tendik">Tendik</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRefreshTable" title="Muat Ulang Tabel" style="border-radius: 6px;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table class="table tsu-table" id="tableThrKaryawan" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="3%" class="text-center">No</th>
                                    <th width="26%">Nama Pegawai &amp; Unit</th>
                                    <th width="10%" class="text-center">Tgl Awal Kerja</th>
                                    <th width="9%" class="text-center">Masa Kerja</th>
                                    <th width="12%" class="text-right">Upah Tetap</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="11%" class="text-right">Nominal THR</th>
                                    <th width="8%" class="text-right">Penyesuaian</th>
                                    <th width="11%" class="text-right">Total THR</th>
                                    <th width="7%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Modal Kirim Approval (Pembuat Draft -> Validator 1) -->
    <div class="modal fade" id="modalSubmitApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('admin.payroll.thr.submit-approval', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Pengajuan ke Validator 1
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-dark">
                            Apakah Anda yakin seluruh rincian THR untuk periode <strong>{{ $period->nama_periode }}</strong> sudah sesuai dan siap diajukan ke Validator 1 (<strong>{{ $period->validator1->nama ?? '-' }}</strong>)?
                        </p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-muted">Catatan Pengajuan (Opsional):</label>
                            <textarea name="catatan_pengajuan" class="form-control" rows="2" placeholder="Catatan untuk Validator 1..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold px-3">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim ke Validator 1
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Approve Step -->
    <div class="modal fade" id="modalApproveStep" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('admin.payroll.thr.approve-step', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-check-circle mr-2"></i>Konfirmasi Persetujuan
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        @if($period->status === 'pending_approval')
                            <div class="alert alert-warning border-0 small mb-3" style="border-radius: 6px;">
                                <i class="fas fa-lock mr-1"></i> <strong>Persetujuan Final:</strong> Setelah Anda menyetujui, sistem akan <strong>otomatis mengunci (LOCKED)</strong> periode ini secara permanen.
                            </div>
                        @endif
                        <p class="text-dark">
                            Apakah Anda yakin ingin menyetujui berkas THR periode <strong>{{ $period->nama_periode }}</strong> pada tahapan ini?
                        </p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-muted">Catatan Verifikasi (Opsional):</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Disetujui tanpa catatan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold px-3">
                            <i class="fas fa-check mr-1"></i> Berikan Persetujuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reject Step (Minta Revisi) -->
    <div class="modal fade" id="modalRejectStep" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('admin.payroll.thr.reject-step', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-undo-alt mr-2"></i>Minta Revisi Berkas THR
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-dark small">
                            Berkas akan dikembalikan ke Pembuat Draft (<strong>{{ $period->createdUser->name ?? 'Pembuat' }}</strong>) dengan status <strong>"Perlu Revisi"</strong>.
                        </p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Rincian / Catatan Hal yang Harus Diperbaiki <span class="text-danger">*</span>:</label>
                            <textarea name="catatan_revisi" class="form-control" rows="3" required placeholder="Jelaskan alasan pengembalian berkas dan data yang perlu diperbaiki..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3">
                            <i class="fas fa-undo mr-1"></i> Kirim Catatan Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Unlock Period -->
    <div class="modal fade" id="modalUnlockPeriod" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('admin.payroll.thr.unlock', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-unlock mr-2"></i>Buka Kunci Periode THR (Unlock)
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning small border-0 mb-3" style="border-radius: 6px;">
                            <strong>Perhatian:</strong> Membuka kunci akan mengembalikan status periode menjadi <strong>Draft</strong> sehingga penyesuaian nominal dapat dilakukan kembali. Seluruh proses approval harus diulang dari awal.
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Alasan Resmi Pembukaan Kunci <span class="text-danger">*</span>:</label>
                            <textarea name="alasan_buka_kunci" class="form-control" rows="2" required placeholder="Contoh: Ada koreksi masa kerja pegawai baru..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3">
                            <i class="fas fa-unlock mr-1"></i> Buka Kunci Periode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ganti Susunan Approver -->
    <div class="modal fade" id="modalEditApproversShow" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('admin.payroll.thr.update-approvers', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header text-white" style="background-color: #094b54;">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-user-edit mr-2"></i>Perbarui Pejabat Validator &amp; Approval
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark small">Validator 1 <span class="text-danger">*</span></label>
                            <select name="validator_1_id" class="form-control select2-modal" required style="width: 100%;">
                                @foreach($allKaryawan as $k)
                                    <option value="{{ $k->id }}" {{ $period->validator_1_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) &bull; {{ $k->nama_unit ?: $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-dark small">Validator 2 (Opsional)</label>
                            <select name="validator_2_id" class="form-control select2-modal" style="width: 100%;">
                                <option value="">-- Langsung ke Approval Final (Tanpa Validator 2) --</option>
                                @foreach($allKaryawan as $k)
                                    <option value="{{ $k->id }}" {{ $period->validator_2_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) &bull; {{ $k->nama_unit ?: $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Approval Paling Atas <span class="text-danger">*</span></label>
                            <select name="approval_id" class="form-control select2-modal" required style="width: 100%;">
                                @foreach($allKaryawan as $k)
                                    <option value="{{ $k->id }}" {{ $period->approval_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) &bull; {{ $k->nama_unit ?: $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3" style="background-color: #094b54;">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Approval (Timeline History) -->
    <div class="modal fade" id="modalApprovalHistory" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header text-white" style="background-color: #094b54;">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-history mr-2"></i>Rekam Jejak &amp; Riwayat Approval THR
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4" id="historyTimelineContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <div class="small text-muted mt-2">Memuat riwayat jejak audit...</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container Edit Karyawan -->
    <div class="modal fade" id="modalEditKaryawan" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" id="modalContentEditKaryawan" style="border-radius: 12px; overflow: hidden;"></div>
        </div>
    </div>
@endsection

@section('script')
<!-- Select2 -->
<script src="{{ asset('public/assets/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
$(document).ready(function() {
    let currentFilterStatus = '';

    // Inisialisasi Select2 Modal
    $('.select2-modal').select2({
        width: '100%',
        dropdownParent: $('#modalEditApproversShow')
    });

    const table = $('#tableThrKaryawan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.payroll.thr.datatable', $period->id) }}",
            data: function(d) {
                d.filter_status = currentFilterStatus;
                d.filter_tipe = $('#filterTipe').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'pegawai_info', name: 'nama' },
            { data: 'tgl_awal_format', name: 'tgl_awal_kerja', className: 'text-center' },
            { data: 'masa_kerja_formatted', name: 'masa_kerja_bulan', className: 'text-center text-nowrap' },
            { data: 'upah_tetap_format', name: 'upah_tetap', className: 'text-right' },
            { data: 'status_badge', name: 'status_thr', className: 'text-center' },
            { data: 'nominal_thr_format', name: 'nominal_thr', className: 'text-right font-weight-bold' },
            { data: 'penyesuaian_format', name: 'penyesuaian', className: 'text-right' },
            { data: 'total_thr_format', name: 'total_thr', className: 'text-right font-weight-bold text-success' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center text-nowrap' }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...',
            emptyTable: 'Tidak ada data pegawai penerima THR',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data pegawai tidak ditemukan",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pegawai",
            infoEmpty: "Tidak ada data",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Lanjut",
                previous: "Kembali"
            }
        }
    });

    // Tab Filter
    $('#tabStatusThr a').on('click', function(e) {
        e.preventDefault();
        $('#tabStatusThr a').removeClass('active');
        $(this).addClass('active');
        currentFilterStatus = $(this).data('status');
        table.ajax.reload();
    });

    // Tipe Filter
    $('#filterTipe').on('change', function() {
        table.ajax.reload();
    });

    // Refresh Table
    $('#btnRefreshTable').on('click', function() {
        table.ajax.reload();
    });

    // Hitung Ulang (Recalculate)
    $('#btnRecalculate').on('click', function() {
        Swal.fire({
            title: 'Hitung Ulang THR?',
            text: 'Perhitungan upah tetap dan masa kerja seluruh pegawai akan dikalkulasi ulang berdasarkan data master terkini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#094b54',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hitung Ulang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menghitung ulang THR...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.post("{{ route('admin.payroll.thr.recalculate', $period->id) }}", { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Selesai!', text: res.message, timer: 1200, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem', 'error');
                });
            }
        });
    });

    // Riwayat Approval History Modal
    $('#btnShowHistory').on('click', function() {
        $('#modalApprovalHistory').modal('show');
        $('#historyTimelineContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><div class="small text-muted mt-2">Memuat riwayat...</div></div>');

        $.get("{{ route('admin.payroll.thr.approval-history', $period->id) }}", function(res) {
            if (res.success && res.approvals.length > 0) {
                let html = '<div class="timeline">';
                res.approvals.forEach(function(item) {
                    let badgeClass = 'bg-primary';
                    let iconClass = 'fas fa-info';

                    if (item.action === 'submitted') {
                        badgeClass = 'bg-info';
                        iconClass = 'fas fa-paper-plane';
                    } else if (item.action === 'approved') {
                        badgeClass = 'bg-success';
                        iconClass = 'fas fa-check';
                    } else if (item.action === 'revision_requested') {
                        badgeClass = 'bg-danger';
                        iconClass = 'fas fa-undo-alt';
                    } else if (item.action === 'unlocked') {
                        badgeClass = 'bg-warning';
                        iconClass = 'fas fa-unlock';
                    }

                    let dateStr = new Date(item.created_at).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                    });

                    html += `
                        <div>
                            <i class="${iconClass} ${badgeClass}"></i>
                            <div class="timeline-item shadow-sm">
                                <span class="time"><i class="fas fa-clock mr-1"></i>${dateStr}</span>
                                <h3 class="timeline-header font-weight-bold" style="font-size: 0.92rem;">
                                    ${item.role_label} &mdash; <span class="text-primary">${item.karyawan_name}</span>
                                </h3>
                                <div class="timeline-body text-dark" style="font-size: 0.88rem;">
                                    ${item.note || 'Tidak ada catatan.'}
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '<div><i class="fas fa-clock bg-gray"></i></div></div>';
                $('#historyTimelineContent').html(html);
            } else {
                $('#historyTimelineContent').html('<div class="text-center py-4 text-muted">Belum ada riwayat approval tercatat.</div>');
            }
        });
    });

    // Edit Penyesuaian Karyawan Modal
    $(document).on('click', '.btn-edit-karyawan', function() {
        const id = $(this).data('id');
        const url = "{{ route('admin.payroll.thr.edit-karyawan', ':id') }}".replace(':id', id);

        $.get(url, function(res) {
            $('#modalContentEditKaryawan').html(res);
            $('#modalEditKaryawan').modal('show');
        });
    });
});
</script>
@endsection
