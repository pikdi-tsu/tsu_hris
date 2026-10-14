@extends('system::template.admin.header')
@section('title', $title ?? 'Detail Payroll Lembur')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('css')
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-accent-red: #b91c1c;
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
            letter-spacing: -0.02em;
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

        .tsu-btn-primary {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .tsu-btn-primary:hover {
            background: var(--tsu-primary-dark, #07383f);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            transform: translateY(-1px);
        }

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
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
            padding: 0.75rem 0.8rem;
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

        /* Stepper Approval 4 Tahap */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 1rem;
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
    </style>
@endsection

@section('content')
    {{-- TSU Standard Page Header Component --}}
    <x-tsu-page-header
        title="{{ $period->nama_periode }}"
        icon="fas fa-business-time"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.payroll.lembur.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Periode
            </a>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Approval Workflow Card & 4-Stage Stepper --}}
            <div class="card card-outline shadow-sm mb-4" style="border-top: 3px solid var(--tsu-primary, #094b54); border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom" style="gap: 1rem;">
                        <div>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 0.6rem;">
                                <h4 class="font-weight-bold text-dark mb-0">{{ $period->nama_periode }}</h4>
                                {!! $period->status_badge !!}
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-calendar-alt text-primary mr-1"></i> Cut-Off Lembur: <strong>{{ $period->cutoff_label }}</strong> &bull;
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i> Kota: <strong>{{ $period->kota_surat }}</strong> &bull;
                                <i class="fas fa-user-edit mr-1"></i> Pembuat: <strong>{{ $period->creator->name ?? 'Admin' }}</strong>
                                @if($period->is_locked && $period->lockedUser)
                                    &bull; <i class="fas fa-lock text-success mr-1"></i> Dikunci: <strong>{{ $period->lockedUser->name }}</strong> ({{ $period->locked_at ? $period->locked_at->format('d/m/Y H:i') : '' }})
                                @endif
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="btnShowHistory" title="Lihat Rekam Jejak Approval">
                                <i class="fas fa-history mr-1"></i> Riwayat Approval
                            </button>

                            {{-- AKSI KHUSUS PEMBUAT DRAFT / ADMIN --}}
                            @if($isCreator)
                                @if(in_array($period->status, ['draft', 'revision_requested']))
                                    <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalEditApproversShow" title="Ganti susunan Validator 1, Validator 2, atau Approval">
                                        <i class="fas fa-user-edit mr-1"></i> Ganti Approval
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm font-weight-bold mr-2" id="btn-recalculate" title="Hitung ulang otomatis kehadiran & jam lembur">
                                        <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalSubmitApproval">
                                        <i class="fas fa-paper-plane mr-1"></i> {{ $period->status === 'revision_requested' ? 'Kirim Ulang ke Validator 1' : 'Kirim ke Validator 1' }}
                                    </button>
                                @elseif($period->is_locked)
                                    <button type="button" class="btn btn-warning btn-sm font-weight-bold mr-2 text-dark" data-toggle="modal" data-target="#modalUnlockPeriod">
                                        <i class="fas fa-unlock mr-1"></i> Buka Kunci (Unlock)
                                    </button>
                                @endif
                            @endif

                            {{-- AKSI VALIDATOR 1 --}}
                            @if($isValidator1 && $period->status === 'pending_val_1')
                                <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                    <i class="fas fa-check-circle mr-1"></i> Setujui (Lanjut ke Tahap Berikutnya)
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
                                    <i class="fas fa-lock mr-1"></i> Setujui Final &amp; Kunci Periode
                                </button>
                                <button type="button" class="btn btn-danger btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalRejectStep">
                                    <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                </button>
                            @endif

                            {{-- EKSPOR DOKUMEN --}}
                            @if(($isCreator && in_array($period->status, ['draft', 'revision_requested'])) || $period->is_locked)
                                <div class="btn-group">
                                    <a href="{{ route('admin.payroll.lembur.export-excel', $period->id) }}" class="btn btn-outline-success btn-sm font-weight-bold">
                                        <i class="fas fa-file-excel mr-1"></i> Rekap Excel
                                    </a>
                                    <a href="{{ route('admin.payroll.lembur.download-all-slip', $period->id) }}" class="btn tsu-btn-primary btn-sm font-weight-bold">
                                        <i class="fas fa-file-archive mr-1"></i> Semua Slip (ZIP)
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
                        <div class="stepper-item {{ $step1Class }}">
                            <div class="step-counter">
                                @if($step1Class === 'completed') <i class="fas fa-check"></i>
                                @elseif($step1Class === 'revision') <i class="fas fa-exclamation"></i>
                                @else 1 @endif
                            </div>
                            <div class="step-name">1. Pembuat Draft</div>
                            <div class="step-user font-weight-bold">{{ $period->creator->name ?? 'Pembuat' }}</div>
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
                        <div class="stepper-item {{ $step2Class }}">
                            <div class="step-counter">
                                @if($step2Class === 'completed') <i class="fas fa-check"></i>
                                @else 2 @endif
                            </div>
                            <div class="step-name">2. Validator 1</div>
                            <div class="step-user font-weight-bold">{{ $period->validator1->nama ?? 'Validator 1' }}</div>
                            @if($logVal1)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-check-circle mr-1"></i>{{ $logVal1->created_at->format('d/m/Y H:i') }}
                                </div>
                            @elseif($period->status === 'pending_val_1')
                                <div class="step-time text-primary mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Menunggu
                                </div>
                            @elseif($period->status === 'revision_requested' && str_contains($period->rejection_by_role ?? '', 'Validator 1'))
                                <div class="step-time text-danger mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-undo-alt mr-1"></i>Minta Revisi
                                </div>
                            @else
                                <div class="step-time text-muted mt-1" style="font-size: 7.5pt;">-</div>
                            @endif
                        </div>
                        <div class="stepper-item {{ $step3Class }}">
                            <div class="step-counter">
                                @if($step3Class === 'completed') <i class="fas fa-check"></i>
                                @else 3 @endif
                            </div>
                            <div class="step-name">3. Validator 2</div>
                            <div class="step-user font-weight-bold">{{ $period->validator2->nama ?? ($period->validator_2_id ? 'Validator 2' : '(Dilewati)') }}</div>
                            @if($logVal2)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-check-circle mr-1"></i>{{ $logVal2->created_at->format('d/m/Y H:i') }}
                                </div>
                            @elseif($period->status === 'pending_val_2')
                                <div class="step-time text-primary mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Menunggu
                                </div>
                            @elseif($period->status === 'revision_requested' && str_contains($period->rejection_by_role ?? '', 'Validator 2'))
                                <div class="step-time text-danger mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-undo-alt mr-1"></i>Minta Revisi
                                </div>
                            @else
                                <div class="step-time text-muted mt-1" style="font-size: 7.5pt;">-</div>
                            @endif
                        </div>
                        <div class="stepper-item {{ $step4Class }}">
                            <div class="step-counter">
                                @if($step4Class === 'completed') <i class="fas fa-lock"></i>
                                @else 4 @endif
                            </div>
                            <div class="step-name">4. Approval Final (Lock)</div>
                            <div class="step-user font-weight-bold">{{ $period->approvalKaryawan->nama ?? 'Approval Paling Atas' }}</div>
                            @if($logApproval)
                                <div class="step-time text-success mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-lock mr-1"></i>{{ $logApproval->created_at->format('d/m/Y H:i') }}
                                </div>
                            @elseif($period->status === 'pending_approval')
                                <div class="step-time text-primary mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Menunggu
                                </div>
                            @elseif($period->status === 'revision_requested' && str_contains($period->rejection_by_role ?? '', 'Approval'))
                                <div class="step-time text-danger mt-1" style="font-size: 7.5pt;">
                                    <i class="fas fa-undo-alt mr-1"></i>Minta Revisi
                                </div>
                            @else
                                <div class="step-time text-muted mt-1" style="font-size: 7.5pt;">-</div>
                            @endif
                        </div>
                    </div>

                    {{-- Alert Catatan Revisi --}}
                    @if($period->status === 'revision_requested' && $period->rejection_note)
                        <div class="alert alert-danger mb-0 mt-3 shadow-sm border-0" style="background-color: #fee2e2; border-left: 4px solid #ef4444 !important;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-circle text-danger mr-2 mt-1" style="font-size: 1.1rem;"></i>
                                <div>
                                    <strong class="text-danger d-block">Perlu Revisi dari {{ $period->rejection_by_role }}:</strong>
                                    <span class="text-dark">{{ $period->rejection_note }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4 Stat Cards --}}
            <div class="tsu-stat-grid-payroll">
                <div class="tsu-stat-card tsu-stat-card--periods">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Pegawai</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($summary['total_pegawai'], 0, ',', '.') }} 
                        <span style="font-size: 1rem; font-weight: 600;">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Pegawai memiliki jam lembur approved</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--latest">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Jam Lembur</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($summary['total_jam_lembur'], 1) }} 
                        <span style="font-size: 1rem; font-weight: 600;">Jam</span>
                    </div>
                    <div class="tsu-stat-card__subtext">Kerja: {{ number_format($summary['jam_kerja_biasa'], 1) }} Jam | Libur: {{ number_format($summary['jam_hari_libur'], 1) }} Jam</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--locked">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Anggaran Lembur</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($summary['total_anggaran_lembur'], 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Total pengeluaran lembur periode ini</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--draft">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="tsu-stat-card__title">Rata-Rata per Orang</div>
                    @php
                        $avg = ($summary['total_pegawai'] > 0) ? ($summary['total_anggaran_lembur'] / $summary['total_pegawai']) : 0;
                    @endphp
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">
                        Rp {{ number_format($avg, 0, ',', '.') }}
                    </div>
                    <div class="tsu-stat-card__subtext">Rata-rata nominal per pegawai</div>
                </div>
            </div>

            {{-- Main Table of Employees Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header">
                    <h5 class="tsu-card__title">
                        <i class="fas fa-user-check"></i> Daftar Karyawan Penerima Uang Lembur
                    </h5>

                    {{-- Filters --}}
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <select id="filter-tipe" class="form-control form-control-sm border" style="width: 150px; border-radius: 8px;">
                            <option value="all">Semua Tipe</option>
                            <option value="Tendik">Tendik Saja</option>
                            <option value="Dosen">Dosen Saja</option>
                        </select>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table id="table-payroll-lembur" class="table tsu-table w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center align-middle">No</th>
                                    <th width="24%" class="align-middle">Karyawan &amp; Posisi</th>
                                    <th width="11%" class="text-right align-middle">Gaji Tetap</th>
                                    <th width="10%" class="text-right align-middle">Upah / Jam</th>
                                    <th width="13%" class="text-center align-middle">Jam Lembur</th>
                                    <th width="11%" class="text-right align-middle">Sistem (Rp)</th>
                                    <th width="9%" class="text-right align-middle">Koreksi (Rp)</th>
                                    <th width="11%" class="text-right align-middle">Total Akhir</th>
                                    <th width="12%" class="align-middle">Rekening Bank</th>
                                    <th width="9%" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-ajax-lembur" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-ajax-lembur-content" style="border-radius: 14px; overflow: hidden;">
            </div>
        </div>
    </div>

    <!-- Modal Submit Approval -->
    <div class="modal fade" id="modalSubmitApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('admin.payroll.lembur.submit-approval', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header text-white" style="background-color: #094b54;">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-paper-plane mr-2"></i>Pengajuan Approval Lembur
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-dark">
                            Apakah Anda yakin seluruh rincian lembur untuk periode <strong>{{ $period->nama_periode }}</strong> sudah sesuai dan siap diajukan ke Validator 1 (<strong>{{ $period->validator1->nama ?? '-' }}</strong>)?
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
                <form action="{{ route('admin.payroll.lembur.approve-step', $period->id) }}" method="POST">
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
                                <i class="fas fa-lock mr-1"></i> <strong>Persetujuan Final:</strong> Setelah Anda menyetujui, sistem akan <strong>otomatis mengunci (LOCKED)</strong> periode lembur ini secara resmi.
                            </div>
                        @endif
                        <p class="text-dark">
                            Apakah Anda yakin ingin menyetujui berkas lembur periode <strong>{{ $period->nama_periode }}</strong> pada tahapan ini?
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
                <form action="{{ route('admin.payroll.lembur.reject-step', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-undo-alt mr-2"></i>Minta Revisi Berkas Lembur
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-dark small">
                            Berkas akan dikembalikan ke Pembuat Draft (<strong>{{ $period->creator->name ?? 'Pembuat' }}</strong>) dengan status <strong>"Perlu Revisi"</strong>.
                        </p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Rincian / Catatan Hal yang Harus Diperbaiki <span class="text-danger">*</span>:</label>
                            <textarea name="catatan_revisi" class="form-control" rows="3" required placeholder="Jelaskan alasan pengembalian berkas dan data jam lembur/koreksi yang perlu diperbaiki..."></textarea>
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
                <form action="{{ route('admin.payroll.lembur.unlock', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-unlock mr-2"></i>Buka Kunci Periode Lembur (Unlock)
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning small border-0 mb-3" style="border-radius: 6px;">
                            <strong>Perhatian:</strong> Membuka kunci akan mengembalikan status periode menjadi <strong>Draft</strong> sehingga penyesuaian nominal dan kalkulasi ulang dapat dilakukan kembali. Seluruh proses approval harus diulang dari awal.
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark small">Alasan Resmi Pembukaan Kunci <span class="text-danger">*</span>:</label>
                            <textarea name="alasan_buka_kunci" class="form-control" rows="2" required placeholder="Contoh: Ada koreksi lembur susulan bagi staf laboratorium..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3 text-dark">
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
                <form action="{{ route('admin.payroll.lembur.update-approvers', $period->id) }}" method="POST">
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
                        <i class="fas fa-history mr-2"></i>Rekam Jejak &amp; Riwayat Approval Lembur
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
@endsection

@section('script')
    <!-- DataTables -->
    <script src="{{ asset('public/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('public/assets/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 di Modal Edit Approver
            $('#modalEditApproversShow').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#modalEditApproversShow')
                });
            });

            let table = $('#table-payroll-lembur').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.payroll.lembur.datatable', $period->id) }}",
                    data: function(d) {
                        d.filter_tipe = $('#filter-tipe').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'pegawai_info', name: 'nama', className: 'align-middle' },
                    { data: 'gaji_tetap_format', name: 'gaji_tetap', className: 'align-middle' },
                    { data: 'upah_jam_format', name: 'upah_per_jam', className: 'align-middle' },
                    { data: 'jam_lembur_info', name: 'total_jam_lembur', className: 'align-middle' },
                    { data: 'nominal_sistem_format', name: 'nominal_lembur_sistem', className: 'align-middle' },
                    { data: 'penyesuaian_format', name: 'penyesuaian', className: 'align-middle' },
                    { data: 'total_final_format', name: 'total_lembur_final', className: 'align-middle font-weight-bold' },
                    { data: 'bank_info', name: 'no_rekening', className: 'align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "Cari Karyawan:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data lembur",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            $('#filter-tipe').on('change', function() {
                table.draw();
            });

            // Modal Detail Hari Lembur
            $(document).on('click', '.btn-detail-lembur', function() {
                let id = $(this).data('id');
                let modal = $('#modal-ajax-lembur');
                let content = $('#modal-ajax-lembur-content');

                content.html('<div class="p-5 text-center text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>Memuat rincian hari lembur...</div>');
                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/payroll/lembur/detail-modal') }}/" + id,
                    type: "GET",
                    success: function(html) {
                        content.html(html);
                    },
                    error: function() {
                        content.html('<div class="p-4 text-center text-danger">Gagal memuat rincian hari lembur.</div>');
                    }
                });
            });

            // Modal Edit Penyesuaian
            $(document).on('click', '.btn-edit-karyawan', function() {
                let id = $(this).data('id');
                let modal = $('#modal-ajax-lembur');
                let content = $('#modal-ajax-lembur-content');

                content.html('<div class="p-5 text-center text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>Memuat form penyesuaian...</div>');
                modal.modal('show');

                $.ajax({
                    url: "{{ url('admin/payroll/lembur/edit-karyawan') }}/" + id,
                    type: "GET",
                    success: function(html) {
                        content.html(html);
                    },
                    error: function() {
                        content.html('<div class="p-4 text-center text-danger">Gagal memuat form penyesuaian.</div>');
                    }
                });
            });

            // Recalculate Period
            $('#btn-recalculate').on('click', function() {
                Swal.fire({
                    title: 'Hitung Ulang Seluruh Lembur?',
                    text: 'Sistem akan menarik ulang seluruh jam lembur yang telah approved dalam rentang cut-off. Nilai koreksi penyesuaian manual akan tetap dipertahankan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#094b54',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hitung Ulang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghitung Ulang...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: "{{ route('admin.payroll.lembur.recalculate', $period->id) }}",
                            type: "POST",
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selesai!',
                                    text: res.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                setTimeout(() => window.location.reload(), 1400);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Gagal menghitung ulang.'
                                });
                            }
                        });
                    }
                });
            });

            // Riwayat Approval History Modal
            $('#btnShowHistory').on('click', function() {
                $('#modalApprovalHistory').modal('show');
                $('#historyTimelineContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><div class="small text-muted mt-2">Memuat riwayat...</div></div>');

                $.get("{{ route('admin.payroll.lembur.approval-history', $period->id) }}", function(res) {
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

            // Toggle Lock (Backup / alternatif cepat jika diperlukan)
            $('#btn-toggle-lock').on('click', function() {
                let isLocked = {{ $period->is_locked ? 'true' : 'false' }};
                let actionText = isLocked ? 'Buka Kunci' : 'Kunci (Lock)';
                let msgText = isLocked 
                    ? 'Membuka kunci akan memungkinkan dilakukannya koreksi dan kalkulasi ulang kembali.'
                    : 'Mengunci periode akan membatasi perubahan data agar siap untuk proses pencairan dan pencetakan slip.';

                Swal.fire({
                    title: actionText + ' Periode Lembur?',
                    text: msgText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isLocked ? '#f59e0b' : '#047857',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, ' + actionText,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.payroll.lembur.toggle-lock', $period->id) }}",
                            type: "POST",
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                setTimeout(() => window.location.reload(), 1300);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Gagal mengubah status kunci.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
