@extends('system::template.admin.header')

@section('content')
    <style>
        #table-karyawan td.text-nowrap, #table-karyawan th.text-nowrap {
            white-space: nowrap !important;
        }
        .btn-xs {
            padding: 0.2rem 0.5rem;
            font-size: 0.78rem;
            line-height: 1.4;
            border-radius: 0.25rem;
            display: inline-flex;
            align-items: center;
        }
        /* Stepper Styling */
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
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Banner Approval Stepper & Status --}}
        <div class="card shadow-sm border-0 mb-3 bg-white">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-3">
                    <div>
                        <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline-secondary btn-sm mr-2" title="Kembali ke Daftar Periode">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <span class="h4 font-weight-bold text-dark align-middle mr-2">{{ $period->nama_periode }}</span>
                        {!! $period->status_badge !!}

                        <div class="text-muted small mt-1">
                            <i class="fas fa-barcode mr-1"></i> {{ $period->kode_periode }} &bull;
                            <i class="fas fa-calendar-alt mr-1"></i> Cut-Off: <strong>{{ $period->cutoff_label }}</strong> &bull;
                            <i class="fas fa-user-edit mr-1"></i> Pembuat: <strong>{{ $period->createdUser->name ?? 'Admin' }}</strong>
                            @if($period->is_locked && $period->lockedUser)
                                &bull; <i class="fas fa-lock mr-1 text-success"></i> Dikunci oleh: <strong>{{ $period->lockedUser->name }}</strong> ({{ $period->locked_at->format('d/m/Y H:i') }})
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons Header --}}
                    <div class="d-flex flex-wrap gap-2 mt-3 mt-lg-0 align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="btnShowHistory" title="Lihat Rekam Jejak Approval">
                            <i class="fas fa-history mr-1"></i> Riwayat Approval
                        </button>

                        {{-- AKSI KHUSUS PEMBUAT DRAFT --}}
                        @if($isCreator)
                            @if(in_array($period->status, ['draft', 'revision_requested']))
                                <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#modalEditApproversShow" title="Ganti susunan Validator 1, Validator 2, atau Approval">
                                    <i class="fas fa-user-edit mr-1"></i> Ganti Approval
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm font-weight-bold mr-2" id="btnRecalculate" title="Hitung ulang otomatis kehadiran, lembur, dan tunjangan">
                                    <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang (Recalculate)
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
                                <i class="fas fa-check-circle mr-1"></i> Setujui (Lanjut ke Validator 2)
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

                        {{-- Ekspor Dokumen: HANYA untuk Pembuat Draft (saat draft/revisi) ATAU ketika periode sudah resmi Terkunci (Locked) --}}
                        @if(($isCreator && in_array($period->status, ['draft', 'revision_requested'])) || $period->is_locked)
                            <div class="btn-group">
                                <a href="{{ route('admin.payroll.export-excel', $period->id) }}" class="btn btn-outline-success btn-sm font-weight-bold">
                                    <i class="fas fa-file-excel mr-1"></i> Rekap Excel
                                </a>
                                <a href="{{ route('admin.payroll.export-bank', $period->id) }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                                    <i class="fas fa-university mr-1"></i> Transfer Bank
                                </a>
                                <a href="{{ route('admin.payroll.download-all-slip', $period->id) }}" class="btn btn-outline-danger btn-sm font-weight-bold">
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
                        <div class="step-user font-weight-bold">{{ $period->validator2->nama ?? 'Validator 2' }}</div>
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
                            <i class="fas fa-exclamation-circle text-danger mr-2 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h6 class="font-weight-bold text-danger mb-1">
                                    Catatan Revisi dari {{ $period->rejection_by_role }}:
                                </h6>
                                <p class="mb-1 text-dark">"{{ $period->rejection_note }}"</p>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Silahkan pembuat draft melakukan koreksi data melalui tombol <strong>Edit</strong> pada tabel di bawah, kemudian ajukan kembali melalui tombol <strong>Kirim Ulang ke Validator 1</strong>.
                                </small>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Alert Catatan Buka Kunci --}}
                @if($period->status === 'draft' && $period->unlocked_reason)
                    <div class="alert alert-warning mb-0 mt-3 shadow-sm border-0" style="background-color: #fef3c7; border-left: 4px solid #f59e0b !important;">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-unlock text-warning mr-2 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1">
                                    Periode Telah Dibuka Kuncinya oleh Pembuat Draft:
                                </h6>
                                <p class="mb-0 text-muted small">Alasan pembukaan: <em>"{{ $period->unlocked_reason }}"</em>. Anda dapat membenahi data kembali sebelum diajukan ulang.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Banner Khusus Tahap Validator 1 --}}
                @if($period->status === 'pending_val_1')
                    <div class="alert alert-info mb-0 mt-3 shadow-sm border-0" style="background-color: #f0fdf4; border-left: 4px solid #16a34a !important;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-user-check text-success mr-2 mt-1" style="font-size: 1.3rem;"></i>
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-1">
                                        Tahap Validasi Dokumen: <span class="text-success">Validator 1 ({{ $period->validator1->nama ?? '-' }})</span>
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        Silakan kroscek lembar penggajian di bawah. Validator 1 dapat menyetujui berkas untuk diteruskan ke Validator 2, atau memberikan catatan revisi jika ada data yang perlu diperbaiki.
                                    </p>
                                </div>
                            </div>
                            @if($isValidator1)
                                <div class="mt-2 mt-md-0 d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                        <i class="fas fa-check-circle mr-1"></i> Setujui Berkas
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalRejectStep">
                                        <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Banner Khusus Tahap Validator 2 --}}
                @if($period->status === 'pending_val_2')
                    <div class="alert alert-info mb-0 mt-3 shadow-sm border-0" style="background-color: #eff6ff; border-left: 4px solid #2563eb !important;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-user-check text-primary mr-2 mt-1" style="font-size: 1.3rem;"></i>
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-1">
                                        Tahap Verifikasi Dokumen: <span class="text-primary">Validator 2 ({{ $period->validator2->nama ?? '-' }})</span>
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        Berkas telah lolos validasi dari Validator 1. Silakan lakukan kroscek tahap 2 sebelum diteruskan ke Approval Paling Atas.
                                    </p>
                                </div>
                            </div>
                            @if($isValidator2)
                                <div class="mt-2 mt-md-0 d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                        <i class="fas fa-check-circle mr-1"></i> Setujui Berkas
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalRejectStep">
                                        <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Banner Khusus Tahap Approval Paling Atas --}}
                @if($period->status === 'pending_approval')
                    <div class="alert alert-warning mb-0 mt-3 shadow-sm border-0" style="background-color: #fffbeb; border-left: 4px solid #f59e0b !important;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-crown text-warning mr-2 mt-1" style="font-size: 1.3rem;"></i>
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-1">
                                        Tahap Persetujuan Final & Penguncian: <span class="text-warning">Approval ({{ $period->approvalKaryawan->nama ?? '-' }})</span>
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        Berkas telah diverifikasi oleh Validator 1 & 2. Memberikan persetujuan final akan <strong>OTOMATIS MENGUNCI (LOCKED)</strong> periode ini.
                                    </p>
                                </div>
                            </div>
                            @if($isApproval)
                                <div class="mt-2 mt-md-0 d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2" data-toggle="modal" data-target="#modalApproveStep">
                                        <i class="fas fa-lock mr-1"></i> Setujui Final & Kunci
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalRejectStep">
                                        <i class="fas fa-undo-alt mr-1"></i> Minta Revisi
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-3">
            <div class="col-xl-2 col-md-4 col-6 mb-2">
                <div class="card shadow-sm border-0 bg-white p-2">
                    <span class="text-xs text-muted text-uppercase font-weight-bold">Total Pegawai</span>
                    <h5 class="font-weight-bold mb-0 text-dark" id="sumPegawai">{{ $summary['total_pegawai'] }} Orang</h5>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-2">
                <div class="card shadow-sm border-0 bg-white p-2">
                    <span class="text-xs text-muted text-uppercase font-weight-bold">Total Gaji Pokok</span>
                    <h5 class="font-weight-bold mb-0 text-secondary" id="sumGapok">Rp {{ number_format($summary['total_gapok'], 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-2">
                <div class="card shadow-sm border-0 bg-white p-2">
                    <span class="text-xs text-muted text-uppercase font-weight-bold">Total Transport</span>
                    <h5 class="font-weight-bold mb-0 text-info" id="sumTransport">Rp {{ number_format($summary['total_transport'], 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-2">
                <div class="card shadow-sm border-0 bg-white p-2">
                    <span class="text-xs text-muted text-uppercase font-weight-bold">Total Lembur</span>
                    <h5 class="font-weight-bold mb-0 text-primary" id="sumLembur">Rp {{ number_format($summary['total_lembur'], 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-2">
                <div class="card shadow-sm border-0 bg-white p-2">
                    <span class="text-xs text-muted text-uppercase font-weight-bold">Total Potongan</span>
                    <h5 class="font-weight-bold mb-0 text-danger" id="sumPotongan">Rp {{ number_format($summary['total_potongan'], 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-2">
                <div class="card shadow-sm border-0 bg-white p-2" style="background-color: #ecfdf5 !important;">
                    <span class="text-xs text-success text-uppercase font-weight-bold">Take Home Pay</span>
                    <h5 class="font-weight-bold mb-0 text-success" id="sumBersih">Rp {{ number_format($summary['total_gaji_bersih'], 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-2">
                <div class="card-title font-weight-bold" style="font-size: 1rem;">
                    <i class="fas fa-list-alt mr-2 text-primary"></i> Lembar Tabsheet Kroscek Penggajian Pegawai
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <label class="mr-2 mb-0 small font-weight-bold">Filter Tipe:</label>
                    <select id="filterTipeKaryawan" class="form-control form-control-sm" style="width: 140px;">
                        <option value="">Semua Pegawai</option>
                        <option value="Dosen">Dosen</option>
                        <option value="Tendik">Tendik</option>
                    </select>
                </div>
            </div>

            <div class="card-body p-3" style="font-size: 9pt;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="table-karyawan" style="width: 100%;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="3%">No</th>
                                <th width="19%">Pegawai & Unit</th>
                                <th width="14%">Pangkat & Jabatan</th>
                                <th width="9%">Gaji Pokok</th>
                                <th width="9%">Gaji Tetap</th>
                                <th width="9%">Transport Presensi</th>
                                <th width="7%">Lembur</th>
                                <th width="8%">Potongan</th>
                                <th width="10%">Gaji Bersih (THP)</th>
                                <th width="10%">Keterangan</th>
                                <th width="11%" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Submit Approval ke Validator 1 --}}
    <div class="modal fade" id="modalSubmitApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.payroll.submit-approval', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-paper-plane mr-2"></i> Ajukan ke Validator 1
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            Apakah Anda yakin seluruh data penggajian pada periode <strong>{{ $period->nama_periode }}</strong> sudah dikroscek dengan benar?
                        </p>
                        <div class="card bg-light border p-2 mb-3">
                            <small class="text-muted d-block">Validator 1 yang ditugaskan:</small>
                            <strong class="text-dark"><i class="fas fa-user-check text-info mr-1"></i> {{ $period->validator1->nama ?? '-' }}</strong>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small">Catatan Pengajuan (Opsional)</label>
                            <textarea name="catatan_pengajuan" class="form-control form-control-sm" rows="3" placeholder="Catatan informasi untuk pemeriksa Validator 1..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-paper-plane mr-1"></i> Ya, Ajukan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Approve Step (Persetujuan) --}}
    <div class="modal fade" id="modalApproveStep" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.payroll.approve-step', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-check-circle mr-2"></i> Konfirmasi Persetujuan
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            Berikan persetujuan untuk periode penggajian <strong>{{ $period->nama_periode }}</strong>.
                        </p>
                        @if($period->status === 'pending_approval')
                            <div class="alert alert-warning small mb-3">
                                <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Perhatian:</strong> Ini adalah persetujuan paling atas. Begitu disetujui, sistem akan <strong>OTOMATIS MENGUNCI (LOCKED)</strong> periode ini menjadi final.
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="font-weight-bold small">Catatan Persetujuan (Opsional)</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-check mr-1"></i> Setujui Berkas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Minta Revisi (Tolak) --}}
    <div class="modal fade" id="modalRejectStep" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.payroll.reject-step', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-undo-alt mr-2"></i> Minta Revisi ke Pembuat Draft
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2 text-muted small">
                            Berkas akan dikembalikan ke status <strong>Perlu Revisi</strong> sehingga Pembuat Draft dapat membenahi data sesuai catatan yang Anda berikan.
                        </p>
                        <div class="form-group">
                            <label class="font-weight-bold small text-danger">Catatan Revisi / Hal yang Harus Dibenahi <span class="text-danger">*</span></label>
                            <textarea name="catatan_revisi" class="form-control" rows="4" required placeholder="Contoh: Mohon kroscek kembali uang transport untuk Bpk/Ibu X karena ada penugasan luar kota..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger font-weight-bold">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Catatan Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Buka Kunci (Unlock) Khusus Pembuat --}}
    <div class="modal fade" id="modalUnlockPeriod" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.payroll.unlock', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-unlock mr-2"></i> Buka Kunci Periode (Unlock)
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2 text-dark small">
                            Sebagai <strong>Pembuat Draft</strong>, Anda berwenang membuka kunci periode ini agar data dapat dibenahi kembali jika terjadi kekeliruan.
                        </p>
                        <div class="form-group">
                            <label class="font-weight-bold small">Alasan Pembukaan Kunci <span class="text-danger">*</span></label>
                            <textarea name="alasan_buka_kunci" class="form-control" rows="3" required placeholder="Sertakan alasan pembukaan kunci untuk keperluan audit log..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning font-weight-bold">
                            <i class="fas fa-unlock mr-1"></i> Ya, Buka Kunci
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Riwayat Approval Log --}}
    <div class="modal fade" id="modalHistoryApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-history mr-2"></i> Rekam Jejak & Riwayat Approval
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tableHistoryApproval" style="font-size: 8.5pt;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Waktu</th>
                                    <th width="20%">Aktor & Peran</th>
                                    <th width="15%">Aksi</th>
                                    <th width="40%">Catatan / Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($period->approvals as $idx => $app)
                                    <tr>
                                        <td class="text-center align-middle">{{ $idx + 1 }}</td>
                                        <td class="align-middle text-muted">{{ $app->created_at->format('d M Y H:i') }}</td>
                                        <td class="align-middle">
                                            <strong>{{ $app->karyawan_name }}</strong><br>
                                            <small class="badge badge-light border">{{ $app->role_label }}</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($app->action === 'approved')
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Approved</span>
                                            @elseif($app->action === 'revision_requested')
                                                <span class="badge badge-danger"><i class="fas fa-undo-alt mr-1"></i> Minta Revisi</span>
                                            @elseif($app->action === 'unlocked')
                                                <span class="badge badge-warning text-dark"><i class="fas fa-unlock mr-1"></i> Buka Kunci</span>
                                            @else
                                                <span class="badge badge-info"><i class="fas fa-paper-plane mr-1"></i> Submitted</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-dark">
                                            {{ $app->note ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat aktivitas approval.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Penyesuaian Manual Karyawan --}}
    <div class="modal fade" id="modalEditKaryawan" tabindex="-1" role="dialog" aria-labelledby="modalEditKaryawanLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold" id="modalEditKaryawanLabel">
                        <i class="fas fa-edit mr-2"></i> Koreksi & Penyesuaian Manual: <span id="modalPegawaiName"></span>
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editKaryawanId">

                    <div class="row">
                        {{-- Kolom Kiri: Komponen Gaji Tetap & Tunjangan --}}
                        <div class="col-md-6 border-right">
                            <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-2">
                                <i class="fas fa-money-check-alt mr-1"></i> 1. Gaji Pokok & Tunjangan Tetap
                            </h6>

                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label font-weight-bold small">Gaji Pokok (Rp)</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editGajiPokok" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tunjangan Fungsional</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTunjFungsional" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tunjangan Struktural</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTunjStruktural" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tunjangan Khusus</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTunjKhusus" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tunjangan Keluarga</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTunjKeluarga" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tunjangan Anak</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTunjAnak" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tunjangan BPJS Kes (4%)</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTunjKesehatan" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>

                            <div class="card bg-light border p-2 mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold small">Subtotal Gaji Tetap:</span>
                                    <input type="text" id="previewGajiTetap" class="form-control form-control-sm text-right font-weight-bold bg-white" readonly style="width: 140px;">
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Presensi, Lembur & Potongan --}}
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-info border-bottom pb-1 mb-2">
                                <i class="fas fa-calendar-check mr-1"></i> 2. Presensi & Uang Transport
                            </h6>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Hari Hadir Valid</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editHariHadir" class="form-control form-control-sm calc-trigger" min="0" step="1">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Tarif Transport / Hari</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTarifTransport" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label font-weight-bold small">Total Transport</label>
                                <div class="col-sm-7">
                                    <input type="text" id="previewTotalTransport" class="form-control form-control-sm text-right bg-light font-weight-bold" readonly>
                                </div>
                            </div>

                            <h6 class="font-weight-bold text-primary border-bottom pb-1 mb-2 mt-3">
                                <i class="fas fa-business-time mr-1"></i> 3. Uang Lembur
                            </h6>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Total Uang Lembur (Rp)</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editTotalLembur" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>

                            <h6 class="font-weight-bold text-danger border-bottom pb-1 mb-2 mt-3">
                                <i class="fas fa-minus-circle mr-1"></i> 4. Komponen Potongan
                            </h6>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Hari Unpaid Leave</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editHariUnpaid" class="form-control form-control-sm calc-trigger" min="0" step="1" placeholder="Rate = Gapok / 25">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Potongan BPJS Kes (4%)</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editPotonganBpjs" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Potongan Lainnya (Rp)</label>
                                <div class="col-sm-7">
                                    <input type="number" id="editPotonganLainnya" class="form-control form-control-sm calc-trigger" min="0" step="1000">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label small">Ket. Potongan Lain</label>
                                <div class="col-sm-7">
                                    <input type="text" id="editKeteranganPotongan" class="form-control form-control-sm" placeholder="Misal: Kasbon, Koperasi, dll">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label font-weight-bold small text-danger">Total Potongan</label>
                                <div class="col-sm-7">
                                    <input type="text" id="previewTotalPotongan" class="form-control form-control-sm text-right bg-light text-danger font-weight-bold" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3 border-top pt-2">
                        <label class="font-weight-bold small">Catatan Alasan Koreksi Manual</label>
                        <textarea id="editCatatanKoreksi" class="form-control form-control-sm" rows="2" placeholder="Catatan internal pengelola jika ada angka yang disesuaikan..."></textarea>
                    </div>

                    {{-- Live Summary Result Box --}}
                    <div class="card bg-dark text-white p-3 mt-3 shadow-sm border-0">
                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <small class="text-uppercase text-muted font-weight-bold">Estimasi Gaji Kotor</small>
                                <h4 class="font-weight-bold text-warning mb-0" id="previewGajiKotor">Rp 0</h4>
                            </div>
                            <div class="col-6">
                                <small class="text-uppercase text-muted font-weight-bold">Take Home Pay (Gaji Bersih)</small>
                                <h4 class="font-weight-bold text-success mb-0" id="previewGajiBersih">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary font-weight-bold px-4" id="btnSaveKaryawan">
                        <i class="fas fa-save mr-1"></i> Simpan Penyesuaian
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Rincian Penggajian Karyawan (Khusus Review Validator 1, 2, Approval & Pembuat) --}}
    <div class="modal fade" id="modalDetailKaryawan" tabindex="-1" role="dialog" aria-labelledby="modalDetailKaryawanLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold" id="modalDetailKaryawanLabel">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Rincian Penggajian: <span id="detailPegawaiName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        {{-- Kolom Kiri: Komponen Gaji Pokok & Tunjangan Tetap --}}
                        <div class="col-md-6 border-right">
                            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-money-check-alt mr-1"></i> 1. Gaji Pokok & Tunjangan Tetap
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="font-weight-bold text-muted" width="55%">Gaji Pokok</td>
                                    <td class="text-right font-weight-bold" id="detailGajiPokok">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tunjangan Fungsional</td>
                                    <td class="text-right font-weight-bold" id="detailTunjFungsional">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tunjangan Struktural</td>
                                    <td class="text-right font-weight-bold" id="detailTunjStruktural">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tunjangan Khusus</td>
                                    <td class="text-right font-weight-bold" id="detailTunjKhusus">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tunjangan Keluarga</td>
                                    <td class="text-right font-weight-bold" id="detailTunjKeluarga">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tunjangan Anak</td>
                                    <td class="text-right font-weight-bold" id="detailTunjAnak">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tunjangan BPJS Kes (4%)</td>
                                    <td class="text-right font-weight-bold" id="detailTunjKesehatan">Rp 0</td>
                                </tr>
                            </table>

                            <div class="card bg-light border p-2 mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold small text-dark">Subtotal Gaji Tetap:</span>
                                    <span class="font-weight-bold text-primary h6 mb-0" id="detailGajiTetap">Rp 0</span>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Presensi, Lembur & Potongan --}}
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-info border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-check mr-1"></i> 2. Presensi & Uang Transport
                            </h6>
                            <table class="table table-sm table-borderless mb-2">
                                <tr>
                                    <td class="text-muted" width="55%">Hari Hadir Valid</td>
                                    <td class="text-right font-weight-bold" id="detailHariHadir">0 Hari</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tarif Transport / Hari</td>
                                    <td class="text-right font-weight-bold" id="detailTarifTransport">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Total Uang Transport</td>
                                    <td class="text-right font-weight-bold text-info" id="detailTotalTransport">Rp 0</td>
                                </tr>
                            </table>

                            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3 mt-3">
                                <i class="fas fa-business-time mr-1"></i> 3. Uang Lembur
                            </h6>
                            <table class="table table-sm table-borderless mb-2">
                                <tr>
                                    <td class="text-muted" width="55%">Total Jam Lembur</td>
                                    <td class="text-right font-weight-bold" id="detailTotalJamLembur">0 Jam</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-dark">Total Uang Lembur</td>
                                    <td class="text-right font-weight-bold text-primary" id="detailTotalLembur">Rp 0</td>
                                </tr>
                            </table>

                            <h6 class="font-weight-bold text-danger border-bottom pb-2 mb-3 mt-3">
                                <i class="fas fa-minus-circle mr-1"></i> 4. Komponen Potongan
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="55%">Hari Unpaid Leave</td>
                                    <td class="text-right font-weight-bold" id="detailHariUnpaid">0 Hari</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Potongan BPJS Kes (4%)</td>
                                    <td class="text-right font-weight-bold" id="detailPotonganBpjs">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Potongan Lainnya</td>
                                    <td class="text-right font-weight-bold" id="detailPotonganLainnya">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold text-danger">Total Potongan</td>
                                    <td class="text-right font-weight-bold text-danger h6 mb-0" id="detailTotalPotongan">Rp 0</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- 5. Catatan Koreksi / Penyesuaian Manual --}}
                    <div class="card border mt-3 shadow-sm" id="detailCatatanKoreksiCard">
                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark small">
                                <i class="fas fa-clipboard-list text-warning mr-1"></i> Catatan Koreksi & Penyesuaian Manual
                            </span>
                            <span id="detailCatatanBadge" class="badge badge-secondary px-2 py-1">Otomatis Sistem</span>
                        </div>
                        <div class="card-body p-3 bg-white" id="detailCatatanKoreksiBody">
                            <span class="text-muted small font-italic">
                                <i class="fas fa-check-circle text-success mr-1"></i> Tidak ada catatan koreksi manual. Komponen dihitung otomatis oleh sistem.
                            </span>
                        </div>
                    </div>

                    {{-- Live Summary Result Box --}}
                    <div class="card bg-dark text-white p-3 mt-3 shadow-sm border-0">
                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <small class="text-uppercase text-muted font-weight-bold">Estimasi Gaji Kotor</small>
                                <h4 class="font-weight-bold text-warning mb-0" id="detailGajiKotor">Rp 0</h4>
                            </div>
                            <div class="col-6">
                                <small class="text-uppercase text-muted font-weight-bold">Take Home Pay (Gaji Bersih)</small>
                                <h4 class="font-weight-bold text-success mb-0" id="detailGajiBersih">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a href="#" id="detailBtnSlipPdf" target="_blank" class="btn btn-danger font-weight-bold">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Slip PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Ganti Susunan Approval (Show View) --}}
    @if($isCreator && !$period->is_locked)
    <div class="modal fade" id="modalEditApproversShow" role="dialog" aria-labelledby="modalEditApproversShowLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.payroll.update-approvers', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="modalEditApproversShowLabel">
                            <i class="fas fa-user-edit mr-2"></i> Ganti Susunan Approval: {{ $period->nama_periode }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Pilih kembali nama pejabat/karyawan untuk mengganti susunan Validator 1, Validator 2, atau Approval Paling Atas.
                        </p>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-user-check text-info mr-1"></i> Validator 1 (Pemeriksa Tingkat 1) <span class="text-danger">*</span>
                            </label>
                            <select name="validator_1_id" class="form-control select2-modal-show" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Validator 1 --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}" {{ $period->validator_1_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) - {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-user-check text-primary mr-1"></i> Validator 2 (Pemeriksa Tingkat 2) <span class="text-danger">*</span>
                            </label>
                            <select name="validator_2_id" class="form-control select2-modal-show" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Validator 2 --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}" {{ $period->validator_2_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) - {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-crown text-warning mr-1"></i> Approval Paling Atas (Persetujuan Final & Auto-Lock) <span class="text-danger">*</span>
                            </label>
                            <select name="approval_id" class="form-control select2-modal-show" required style="width: 100%;">
                                <option value="">-- Cari Nama Karyawan Approval Paling Atas --</option>
                                @foreach($allKaryawans as $k)
                                    <option value="{{ $k->id }}" {{ $period->approval_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) - {{ $k->unit ? $k->unit->nama_unit : $k->posisi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Hindari focus trap Bootstrap agar input search Select2 bisa diketik
            if ($.fn.modal && $.fn.modal.Constructor) {
                $.fn.modal.Constructor.prototype._enforceFocus = function() {};
            }

            $('#modalEditApproversShow').on('shown.bs.modal', function () {
                $('#modalEditApproversShow .select2-modal-show').select2({
                    dropdownParent: $('#modalEditApproversShow'),
                    width: '100%'
                });
            });

            var oTable = $('#table-karyawan').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.payroll.datatable', $period->id) }}",
                    data: function(d) {
                        d.tipe_karyawan = $('#filterTipeKaryawan').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'pegawai_info', name: 'nama' },
                    { data: 'gol_jabatan', name: 'golongan_pangkat' },
                    { data: 'gapok_formatted', name: 'gaji_pokok', className: 'text-right' },
                    { data: 'gaji_tetap_formatted', name: 'gaji_tetap', className: 'text-right' },
                    { data: 'transport_info', name: 'total_transport' },
                    { data: 'lembur_info', name: 'total_lembur' },
                    { data: 'potongan_info', name: 'total_potongan' },
                    { data: 'gaji_bersih_formatted', name: 'gaji_bersih' },
                    { data: 'keterangan', name: 'catatan_koreksi', className: 'align-middle' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center text-nowrap align-middle' },
                ],
                pageLength: 25,
                language: {
                    search: "Cari Pegawai:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    zeroRecords: "Pegawai tidak ditemukan",
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

            $('#filterTipeKaryawan').change(function() {
                oTable.ajax.reload();
            });

            // Recalculate Period Button
            $('#btnRecalculate').click(function() {
                if (!confirm('Tarik dan hitung ulang seluruh data presensi, lembur, dan tunjangan untuk periode ini? Data koreksi manual dapat tertimpa.')) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menghitung Ulang...');

                $.ajax({
                    url: "{{ route('admin.payroll.recalculate', $period->id) }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Hitung Ulang (Recalculate)');
                        if (res.success) {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message || 'Terjadi kesalahan');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Hitung Ulang (Recalculate)');
                        alert('Gagal menghitung ulang: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error server'));
                    }
                });
            });

            // Buka Modal Riwayat Approval
            $('#btnShowHistory').click(function() {
                $('#modalHistoryApproval').modal('show');
            });

            // Buka Modal Detail Rincian Penggajian Karyawan (Read-Only)
            $('body').on('click', '.btn-detail-karyawan', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/payroll/karyawan') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            var d = res.data;
                            $('#detailPegawaiName').text(d.nama + ' (' + (d.nik || '-') + ')');
                            $('#detailGajiPokok').text('Rp ' + parseFloat(d.gaji_pokok || 0).toLocaleString('id-ID'));
                            $('#detailTunjFungsional').text('Rp ' + parseFloat(d.tunjangan_fungsional || 0).toLocaleString('id-ID'));
                            $('#detailTunjStruktural').text('Rp ' + parseFloat(d.tunjangan_struktural || 0).toLocaleString('id-ID'));
                            $('#detailTunjKhusus').text('Rp ' + parseFloat(d.tunjangan_khusus || 0).toLocaleString('id-ID'));
                            $('#detailTunjKeluarga').text('Rp ' + parseFloat(d.tunjangan_keluarga || 0).toLocaleString('id-ID'));
                            $('#detailTunjAnak').text('Rp ' + parseFloat(d.tunjangan_anak || 0).toLocaleString('id-ID'));
                            $('#detailTunjKesehatan').text('Rp ' + parseFloat(d.tunjangan_kesehatan || 0).toLocaleString('id-ID'));
                            $('#detailGajiTetap').text('Rp ' + parseFloat(d.gaji_tetap || 0).toLocaleString('id-ID'));

                            $('#detailHariHadir').text((d.hari_hadir_valid || 0) + ' Hari');
                            $('#detailTarifTransport').text('Rp ' + parseFloat(d.tarif_transport || 0).toLocaleString('id-ID'));
                            $('#detailTotalTransport').text('Rp ' + parseFloat(d.total_transport || 0).toLocaleString('id-ID'));

                            $('#detailTotalJamLembur').text((d.total_jam_lembur || 0) + ' Jam');
                            $('#detailTotalLembur').text('Rp ' + parseFloat(d.total_lembur || 0).toLocaleString('id-ID'));

                            $('#detailHariUnpaid').text((d.hari_unpaid_leave || 0) + ' Hari');
                            $('#detailPotonganBpjs').text('Rp ' + parseFloat(d.potongan_bpjs_kes || 0).toLocaleString('id-ID'));
                            $('#detailPotonganLainnya').text('Rp ' + parseFloat(d.potongan_lainnya || 0).toLocaleString('id-ID') + (d.keterangan_potongan ? ' (' + d.keterangan_potongan + ')' : ''));
                            $('#detailTotalPotongan').text('Rp ' + parseFloat(d.total_potongan || 0).toLocaleString('id-ID'));

                            $('#detailGajiKotor').text('Rp ' + parseFloat(d.gaji_kotor || 0).toLocaleString('id-ID'));
                            $('#detailGajiBersih').text('Rp ' + parseFloat(d.gaji_bersih || 0).toLocaleString('id-ID'));

                            // Tampilkan Catatan Koreksi Manual di Modal Detail
                            if (d.catatan_koreksi && d.catatan_koreksi.trim() !== '') {
                                $('#detailCatatanBadge').removeClass('badge-secondary').addClass('badge-warning text-dark').html('<i class="fas fa-edit mr-1"></i> Ada Koreksi Manual');
                                $('#detailCatatanKoreksiBody').html('<div class="alert alert-warning mb-0 border py-2 px-3 font-weight-bold text-dark" style="background-color: #fffbeb;"><i class="fas fa-comment-alt text-warning mr-2"></i>' + $('<div>').text(d.catatan_koreksi).html() + '</div>');
                            } else {
                                $('#detailCatatanBadge').removeClass('badge-warning text-dark').addClass('badge-secondary').text('Otomatis Sistem');
                                $('#detailCatatanKoreksiBody').html('<span class="text-muted small font-italic"><i class="fas fa-check-circle text-success mr-1"></i> Tidak ada catatan koreksi manual. Komponen dihitung otomatis oleh sistem.</span>');
                            }

                            $('#detailBtnSlipPdf').attr('href', "{{ url('admin/payroll/slip-pdf') }}/" + d.id);
                            $('#modalDetailKaryawan').modal('show');
                        }
                    }
                });
            });

            // Buka Modal Edit Koreksi Manual
            $('body').on('click', '.btn-edit-karyawan', function() {
                var id = $(this).data('id');
                $('#editKaryawanId').val(id);

                $.ajax({
                    url: "{{ url('admin/payroll/karyawan') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            var d = res.data;
                            $('#modalPegawaiName').text(d.nama + ' (' + (d.nik || '-') + ')');
                            $('#editGajiPokok').val(d.gaji_pokok);
                            $('#editTunjFungsional').val(d.tunjangan_fungsional);
                            $('#editTunjStruktural').val(d.tunjangan_struktural);
                            $('#editTunjKhusus').val(d.tunjangan_khusus);
                            $('#editTunjKeluarga').val(d.tunjangan_keluarga);
                            $('#editTunjAnak').val(d.tunjangan_anak);
                            $('#editTunjKesehatan').val(d.tunjangan_kesehatan);
                            $('#editHariHadir').val(d.hari_hadir_valid);
                            $('#editTarifTransport').val(d.tarif_transport);
                            $('#editTotalLembur').val(d.total_lembur);
                            $('#editHariUnpaid').val(d.hari_unpaid_leave);
                            $('#editPotonganBpjs').val(d.potongan_bpjs_kes);
                            $('#editPotonganLainnya').val(d.potongan_lainnya);
                            $('#editKeteranganPotongan').val(d.keterangan_potongan);
                            $('#editCatatanKoreksi').val(d.catatan_koreksi);

                            liveCalculateModal();
                            $('#modalEditKaryawan').modal('show');
                        }
                    }
                });
            });

            // Live Calculation in Modal
            function liveCalculateModal() {
                var gapok = parseFloat($('#editGajiPokok').val()) || 0;
                var tFung = parseFloat($('#editTunjFungsional').val()) || 0;
                var tStruk = parseFloat($('#editTunjStruktural').val()) || 0;
                var tKhusus = parseFloat($('#editTunjKhusus').val()) || 0;
                var tKel = parseFloat($('#editTunjKeluarga').val()) || 0;
                var tAnak = parseFloat($('#editTunjAnak').val()) || 0;
                var tKes = parseFloat($('#editTunjKesehatan').val()) || 0;

                var gTetap = gapok + tFung + tStruk + tKhusus + tKel + tAnak + tKes;
                $('#previewGajiTetap').val('Rp ' + gTetap.toLocaleString('id-ID'));

                var hadir = parseFloat($('#editHariHadir').val()) || 0;
                var tarif = parseFloat($('#editTarifTransport').val()) || 0;
                var transport = hadir * tarif;
                $('#previewTotalTransport').val('Rp ' + transport.toLocaleString('id-ID'));

                var lembur = parseFloat($('#editTotalLembur').val()) || 0;

                var hariUnpaid = parseFloat($('#editHariUnpaid').val()) || 0;
                var rateUnpaid = gapok > 0 ? (gapok / 25) : 0;
                var potUnpaid = hariUnpaid * rateUnpaid;

                var potBpjs = parseFloat($('#editPotonganBpjs').val()) || 0;
                var potLain = parseFloat($('#editPotonganLainnya').val()) || 0;
                var totalPotongan = potUnpaid + potBpjs + potLain;
                $('#previewTotalPotongan').val('Rp ' + totalPotongan.toLocaleString('id-ID'));

                var gKotor = gTetap + transport + lembur;
                var gBersih = Math.max(0, gKotor - totalPotongan);

                $('#previewGajiKotor').text('Rp ' + gKotor.toLocaleString('id-ID'));
                $('#previewGajiBersih').text('Rp ' + gBersih.toLocaleString('id-ID'));
            }

            $('.calc-trigger').on('input', function() {
                liveCalculateModal();
            });

            // Simpan Penyesuaian Manual
            $('#btnSaveKaryawan').click(function() {
                var id = $('#editKaryawanId').val();
                var btn = $(this);

                var payload = {
                    _token: '{{ csrf_token() }}',
                    gaji_pokok: $('#editGajiPokok').val(),
                    tunjangan_fungsional: $('#editTunjFungsional').val(),
                    tunjangan_struktural: $('#editTunjStruktural').val(),
                    tunjangan_khusus: $('#editTunjKhusus').val(),
                    tunjangan_keluarga: $('#editTunjKeluarga').val(),
                    tunjangan_anak: $('#editTunjAnak').val(),
                    tunjangan_kesehatan: $('#editTunjKesehatan').val(),
                    hari_hadir_valid: $('#editHariHadir').val(),
                    tarif_transport: $('#editTarifTransport').val(),
                    total_lembur: $('#editTotalLembur').val(),
                    hari_unpaid_leave: $('#editHariUnpaid').val(),
                    potongan_bpjs_kes: $('#editPotonganBpjs').val(),
                    potongan_lainnya: $('#editPotonganLainnya').val(),
                    keterangan_potongan: $('#editKeteranganPotongan').val(),
                    catatan_koreksi: $('#editCatatanKoreksi').val(),
                };

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: "{{ url('admin/payroll/update-karyawan') }}/" + id,
                    type: 'POST',
                    data: payload,
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penyesuaian');
                        if (res.success) {
                            $('#modalEditKaryawan').modal('hide');
                            oTable.ajax.reload(null, false);

                            // Update top summary cards
                            if (res.period) {
                                $('#sumPegawai').text(res.period.total_pegawai + ' Orang');
                                $('#sumPotongan').text('Rp ' + parseFloat(res.period.total_potongan).toLocaleString('id-ID'));
                                $('#sumBersih').text('Rp ' + parseFloat(res.period.total_gaji_bersih).toLocaleString('id-ID'));
                            }
                        } else {
                            alert(res.message || 'Gagal menyimpan penyesuaian');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penyesuaian');
                        alert('Terjadi kesalahan: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error'));
                    }
                });
            });
        });
    </script>
@endsection
