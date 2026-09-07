@extends('system::template.admin.header')

@section('content')
    <style>
        #table-honorarium-dosen td.text-nowrap, #table-honorarium-dosen th.text-nowrap {
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
        {{-- Card Header & Ringkasan Periode --}}
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3">
                    <div>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                            <a href="{{ route('admin.honorarium.index') }}" class="btn btn-outline-secondary btn-sm mr-2" title="Kembali ke Daftar Periode">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <span class="badge badge-secondary font-monospace mr-1" style="font-size: 0.85rem;">{{ $period->kode_periode }}</span>
                            {!! $period->status_badge !!}
                            <span class="badge badge-primary px-2 py-1 ml-1"><i class="fas fa-layer-group mr-1"></i> Honorarium Terpadu</span>
                        </div>
                        <h4 class="font-weight-bold mb-1 text-dark">{{ $period->nama_periode }}</h4>
                        <div class="text-muted small">
                            <i class="far fa-calendar-alt mr-1 text-primary"></i> Cut-Off: <strong>{{ $period->cutoff_label }}</strong>
                            <span class="mx-2">•</span>
                            <i class="fas fa-user-edit mr-1 text-info"></i> Pembuat: <strong>{{ $period->createdUser->name ?? '-' }}</strong>
                            @if($period->tahun_akademik)
                                <span class="mx-2">•</span>
                                <i class="fas fa-graduation-cap mr-1 text-success"></i> TA: <strong>{{ $period->tahun_akademik }} ({{ $period->semester }})</strong>
                                @if($period->bulan_honor)
                                    <span class="mx-1">•</span> Bulan: <strong>{{ $period->bulan_honor }}</strong>
                                @endif
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
                                <a href="{{ route('admin.honorarium.export-excel', $period->id) }}" class="btn btn-outline-success btn-sm font-weight-bold">
                                    <i class="fas fa-file-excel mr-1"></i> Rekap Excel
                                </a>
                                <a href="{{ route('admin.honorarium.export-bank', $period->id) }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                                    <i class="fas fa-university mr-1"></i> Transfer Bank
                                </a>
                                <a href="{{ route('admin.honorarium.download-all-slip', $period->id) }}" class="btn btn-outline-danger btn-sm font-weight-bold">
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

                <div class="stepper-wrapper pt-3">
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

                {{-- Alert Banner Khusus Action --}}
                @if($isValidator1 && $period->status === 'pending_val_1')
                    <div class="alert alert-success border-0 shadow-sm mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-user-check mr-2"></i> Tahap Validasi Dokumen: Validator 1 ({{ $period->validator1->nama ?? '' }})</h6>
                            <p class="mb-0 small text-dark">Silakan periksa lembar honorarium dosen di bawah. Anda dapat menyetujui berkas untuk diteruskan ke Validator 2, atau meminta revisi jika ada data yang perlu dibenahi.</p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <button type="button" class="btn btn-success font-weight-bold btn-sm mr-1" data-toggle="modal" data-target="#modalApproveStep"><i class="fas fa-check-circle mr-1"></i> Setujui Berkas</button>
                            <button type="button" class="btn btn-danger font-weight-bold btn-sm" data-toggle="modal" data-target="#modalRejectStep"><i class="fas fa-undo-alt mr-1"></i> Minta Revisi</button>
                        </div>
                    </div>
                @elseif($isValidator2 && $period->status === 'pending_val_2')
                    <div class="alert alert-primary border-0 shadow-sm mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-user-check mr-2"></i> Tahap Validasi Dokumen: Validator 2 ({{ $period->validator2->nama ?? '' }})</h6>
                            <p class="mb-0 small text-dark">Validator 1 telah menyetujui berkas. Silakan periksa berkas sebelum diteruskan ke Approval Final.</p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <button type="button" class="btn btn-primary font-weight-bold btn-sm mr-1" data-toggle="modal" data-target="#modalApproveStep"><i class="fas fa-check-circle mr-1"></i> Setujui Berkas</button>
                            <button type="button" class="btn btn-danger font-weight-bold btn-sm" data-toggle="modal" data-target="#modalRejectStep"><i class="fas fa-undo-alt mr-1"></i> Minta Revisi</button>
                        </div>
                    </div>
                @elseif($isApproval && $period->status === 'pending_approval')
                    <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-shield-alt mr-2"></i> Persetujuan Akhir & Penguncian: {{ $period->approvalKaryawan->nama ?? '' }}</h6>
                            <p class="mb-0 small text-dark">Validator 1 & 2 telah menyetujui berkas ini. Persetujuan Anda akan <strong>mengunci berkas secara permanen (LOCKED)</strong>.</p>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <button type="button" class="btn btn-success font-weight-bold btn-sm mr-1" data-toggle="modal" data-target="#modalApproveStep"><i class="fas fa-lock mr-1"></i> Setujui Final & Kunci</button>
                            <button type="button" class="btn btn-danger font-weight-bold btn-sm" data-toggle="modal" data-target="#modalRejectStep"><i class="fas fa-undo-alt mr-1"></i> Minta Revisi</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 4 Stat Cards --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3 id="sumPegawai">{{ number_format($period->total_pegawai, 0) }}</h3>
                        <p class="mb-0 font-weight-bold">Total Dosen Penerima</p>
                    </div>
                    <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary shadow-sm">
                    <div class="inner">
                        <h3 id="sumKotor">Rp {{ number_format($period->total_gaji_kotor, 0, ',', '.') }}</h3>
                        <p class="mb-0 font-weight-bold">Total Honor Kotor</p>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger shadow-sm">
                    <div class="inner">
                        <h3 id="sumPotongan">Rp {{ number_format($period->total_potongan, 0, ',', '.') }}</h3>
                        <p class="mb-0 font-weight-bold">Total Potongan / Pajak</p>
                    </div>
                    <div class="icon"><i class="fas fa-cut"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3 id="sumBersih">Rp {{ number_format($period->total_gaji_bersih, 0, ',', '.') }}</h3>
                        <p class="mb-0 font-weight-bold">Total Transfer Bersih (Net)</p>
                    </div>
                    <div class="icon"><i class="fas fa-money-check-alt"></i></div>
                </div>
            </div>
        </div>

        {{-- Tabel Data Honorarium Dosen --}}
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between">
                <h5 class="card-title font-weight-bold mb-0 text-primary">
                    <i class="fas fa-list-alt mr-2"></i> Daftar Dosen Penerima Honorarium
                </h5>

                @php
                    $canEdit = $isCreator && in_array($period->status, ['draft', 'revision_requested']);
                @endphp

                @if($canEdit)
                    <div>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" data-toggle="modal" data-target="#modalAddDosen">
                            <i class="fas fa-user-plus mr-1"></i> Tambah Dosen Penerima Honor
                        </button>
                    </div>
                @endif
            </div>

            <div class="card-body p-3" style="font-size: 9pt;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="table-honorarium-dosen" style="width: 100%;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="3%">No</th>
                                <th width="22%">Dosen & Unit</th>
                                <th width="10%">Jabatan Fungsional</th>
                                <th width="24%">Komponen Honorarium Terhitung</th>
                                <th width="12%">Honor Kotor</th>
                                <th width="9%">Potongan</th>
                                <th width="12%">Total Transfer (Net)</th>
                                <th width="8%" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: TAMBAH DOSEN PENERIMA HONORARIUM --}}
    @if($canEdit)
        <div class="modal fade" id="modalAddDosen" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content border-0 shadow">
                    <form id="formAddDosen" action="{{ route('admin.honorarium.store-dosen', $period->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title font-weight-bold">
                                <i class="fas fa-user-plus mr-2"></i> Tambah Dosen Penerima Honorarium
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4">
                            {{-- Step 1: Pilih Dosen --}}
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">1. Cari & Pilih Dosen <span class="text-danger">*</span></label>
                                <select name="data_dosen_tendik_id" id="selectAddDosenId" class="form-control select2-dosen" style="width: 100%;" required>
                                    <option value="">-- Ketik Nama atau NIP Dosen --</option>
                                    @foreach($dosenTendiks as $d)
                                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }}) - {{ $d->unit->nama_unit ?? 'Dosen' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Step 2: Info Profil & Tarif Master (Muncul setelah dosen dipilih) --}}
                            <div id="addDosenProfileCard" class="card bg-light border p-3 mb-3" style="display: none;">
                                <div class="row align-items-center">
                                    <div class="col-md-6 border-right">
                                        <h6 class="font-weight-bold text-dark mb-1" id="addDosenNamaText">-</h6>
                                        <div class="small text-muted mb-1">
                                            <span id="addDosenUnitText">-</span> • <span id="addDosenStrukturalText">-</span>
                                        </div>
                                        <div class="small text-secondary">
                                            <i class="fas fa-university mr-1"></i> Rekening: <strong id="addDosenBankText">-</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-muted small mr-2">Jabatan Fungsional:</span>
                                            <span class="badge badge-primary px-2 py-1 font-weight-bold" id="addDosenJafungBadge">-</span>
                                        </div>
                                        <div class="small text-muted" id="addDosenTarifSummary">
                                            {{-- Tariffs preview --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3: Tab Komponen Honorarium --}}
                            <div id="addDosenFormSection" style="display: none;">
                                <h6 class="font-weight-bold text-primary mb-2">
                                    <i class="fas fa-calculator mr-1"></i> 2. Masukkan Komponen Honorarium Dosen
                                </h6>
                                <ul class="nav nav-pills mb-3 bg-light p-2 rounded border" id="addHonorTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active font-weight-bold py-1 px-3" id="tab-add-8a" data-toggle="pill" href="#pane-add-8a" role="tab">
                                            <i class="fas fa-chalkboard-teacher mr-1"></i> 8A. Kelebihan SKS
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link font-weight-bold py-1 px-3" id="tab-add-8b" data-toggle="pill" href="#pane-add-8b" role="tab">
                                            <i class="fas fa-user-graduate mr-1"></i> 8B. Bimbingan & Uji TA/KP
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link font-weight-bold py-1 px-3" id="tab-add-8c" data-toggle="pill" href="#pane-add-8c" role="tab">
                                            <i class="fas fa-file-alt mr-1"></i> 8C. Ujian (UTS & UAS)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link font-weight-bold py-1 px-3" id="tab-add-potongan" data-toggle="pill" href="#pane-add-potongan" role="tab">
                                            <i class="fas fa-cut mr-1"></i> Potongan & Catatan
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content border rounded p-3 bg-white" id="addHonorTabContent">
                                    {{-- TAB 8A: SKS --}}
                                    <div class="tab-pane fade show active" id="pane-add-8a" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">SKS Struktural</label>
                                                    <input type="number" step="0.5" name="sks_struktural" id="add_sks_struktural" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">SKS Riil Mengajar</label>
                                                    <input type="number" step="0.5" name="sks_mengajar" id="add_sks_mengajar" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small text-muted">Total SKS (Wajib: 12)</label>
                                                    <input type="text" id="add_total_sks" class="form-control form-control-sm bg-light" value="0" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small text-success">SKS Lebih (Terhitung)</label>
                                                    <input type="text" id="add_sks_lebih" class="form-control form-control-sm bg-light font-weight-bold text-success" value="0" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Tarif per SKS (Rp)</label>
                                                    <input type="number" name="tarif_sks" id="add_tarif_sks" class="form-control form-control-sm calc-add" value="25000">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Alokasi Jumlah Pertemuan</label>
                                                    <input type="number" name="jumlah_pertemuan" id="add_jumlah_pertemuan" class="form-control form-control-sm calc-add" value="{{ $period->jumlah_pertemuan ?: 3 }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light border p-2 mb-0 mt-3 text-right">
                                                    <small class="text-muted font-weight-bold">Subtotal Honor 8A (SKS):</small>
                                                    <h6 class="font-weight-bold text-success mb-0" id="add_preview_8a">Rp 0</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 8B: Bimbingan & Penguji --}}
                                    <div class="tab-pane fade" id="pane-add-8b" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Jml Bimbingan TA/Skripsi (Mhs)</label>
                                                    <input type="number" name="jml_bimbingan_ta" id="add_jml_bimbingan_ta" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small text-muted">Tarif Bimbingan (Rp/mhs)</label>
                                                    <input type="number" name="tarif_bimbingan_ta" id="add_tarif_bimbingan_ta" class="form-control form-control-sm calc-add" value="200000">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Jml Penguji Sidang TA (Mhs)</label>
                                                    <input type="number" name="jml_penguji_ta" id="add_jml_penguji_ta" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small text-muted">Tarif Penguji (Rp/mhs)</label>
                                                    <input type="number" name="tarif_penguji_ta" id="add_tarif_penguji_ta" class="form-control form-control-sm calc-add" value="75000">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Jml Kerja Praktek / KP (Mhs)</label>
                                                    <input type="number" name="jml_kerja_praktek" id="add_jml_kerja_praktek" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small text-muted">Tarif KP (Rp/mhs)</label>
                                                    <input type="number" name="tarif_kerja_praktek" id="add_tarif_kerja_praktek" class="form-control form-control-sm calc-add" value="150000">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card bg-light border p-2 mb-0 mt-2 text-right">
                                            <small class="text-muted font-weight-bold">Subtotal Honor 8B (Bimbing & Uji TA/KP):</small>
                                            <h6 class="font-weight-bold text-primary mb-0" id="add_preview_8b">Rp 0</h6>
                                        </div>
                                    </div>

                                    {{-- TAB 8C: Ujian --}}
                                    <div class="tab-pane fade" id="pane-add-8c" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Mata Kuliah Diampu</label>
                                                    <input type="text" name="mata_kuliah" id="add_mata_kuliah" class="form-control form-control-sm" placeholder="Contoh: Pemrograman Web Lanjut">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small">Tipe Kelas</label>
                                                    <select name="tipe_kelas" id="add_tipe_kelas" class="form-control form-control-sm">
                                                        <option value="T">Teori (T)</option>
                                                        <option value="T/P">Teori & Praktik (T/P)</option>
                                                        <option value="P">Praktik (P)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row">
                                            <div class="col-md-6 border-right">
                                                <h6 class="font-weight-bold text-secondary small mb-2"><i class="fas fa-file-signature mr-1"></i> Pembuatan Naskah Soal</h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label class="small">Kelas UTS</label>
                                                        <input type="number" name="jml_kelas_uts" id="add_jml_kelas_uts" class="form-control form-control-sm calc-add" value="0">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small">Kelas UAS</label>
                                                        <input type="number" name="jml_kelas_uas" id="add_jml_kelas_uas" class="form-control form-control-sm calc-add" value="0">
                                                    </div>
                                                </div>
                                                <div class="form-group mt-2 mb-1">
                                                    <label class="small text-muted">Tarif Buat Soal (Rp/kelas)</label>
                                                    <input type="number" name="tarif_soal" id="add_tarif_soal" class="form-control form-control-sm calc-add" value="25000">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="font-weight-bold text-secondary small mb-2"><i class="fas fa-check-double mr-1"></i> Koreksi Hasil Ujian Mahasiswa</h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label class="small">Peserta UTS (Mhs)</label>
                                                        <input type="number" name="jml_peserta_uts" id="add_jml_peserta_uts" class="form-control form-control-sm calc-add" value="0">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small">Peserta UAS (Mhs)</label>
                                                        <input type="number" name="jml_peserta_uas" id="add_jml_peserta_uas" class="form-control form-control-sm calc-add" value="0">
                                                    </div>
                                                </div>
                                                <div class="form-group mt-2 mb-1">
                                                    <label class="small text-muted">Tarif Koreksi (Rp/mhs)</label>
                                                    <input type="number" name="tarif_koreksi" id="add_tarif_koreksi" class="form-control form-control-sm calc-add" value="2000">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card bg-light border p-2 mb-0 mt-2 text-right">
                                            <small class="text-muted font-weight-bold">Subtotal Honor 8C (Ujian):</small>
                                            <h6 class="font-weight-bold text-info mb-0" id="add_preview_8c">Rp 0</h6>
                                        </div>
                                    </div>

                                    {{-- TAB Potongan & Catatan --}}
                                    <div class="tab-pane fade" id="pane-add-potongan" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small text-danger">Potongan Pajak PPh (Rp)</label>
                                                    <input type="number" name="potongan_pajak" id="add_potongan_pajak" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold small text-danger">Potongan Lainnya (Rp)</label>
                                                    <input type="number" name="potongan_lainnya" id="add_potongan_lainnya" class="form-control form-control-sm calc-add" value="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Keterangan Potongan</label>
                                            <input type="text" name="keterangan_potongan" id="add_keterangan_potongan" class="form-control form-control-sm" placeholder="Contoh: Pajak PPh 21 / Potongan Kas">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold small">Catatan Koreksi (Opsional)</label>
                                            <textarea name="catatan_koreksi" id="add_catatan_koreksi" class="form-control form-control-sm" rows="2" placeholder="Catatan internal pengajuan..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- Live Grand Total Preview Bar --}}
                                <div class="card bg-dark text-white p-3 shadow-sm border-0 mt-3 mb-0">
                                    <div class="row text-center align-items-center">
                                        <div class="col-4 border-right">
                                            <small class="text-uppercase text-muted font-weight-bold">Total Honor Kotor</small>
                                            <h5 class="font-weight-bold text-warning mb-0" id="add_preview_kotor">Rp 0</h5>
                                        </div>
                                        <div class="col-4 border-right">
                                            <small class="text-uppercase text-muted font-weight-bold">Total Potongan</small>
                                            <h5 class="font-weight-bold text-danger mb-0" id="add_preview_potongan">Rp 0</h5>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-uppercase text-muted font-weight-bold">Total Transfer (Net)</small>
                                            <h4 class="font-weight-bold text-success mb-0" id="add_preview_transfer">Rp 0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success btn-sm font-weight-bold" id="btnSubmitAddDosen" disabled>
                                <i class="fas fa-save mr-1"></i> Simpan Dosen Penerima
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: EDIT KOMPONEN HONORARIUM DOSEN --}}
    <div class="modal fade" id="modalEditHonor" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow">
                <form id="formEditHonor" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-edit mr-2"></i> Edit Komponen Honorarium: <span id="editModalDosenTitle">-</span>
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <ul class="nav nav-pills mb-3 bg-light p-2 rounded border" id="editHonorTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold py-1 px-3" id="tab-edit-8a" data-toggle="pill" href="#pane-edit-8a" role="tab">
                                    <i class="fas fa-chalkboard-teacher mr-1"></i> 8A. Kelebihan SKS
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold py-1 px-3" id="tab-edit-8b" data-toggle="pill" href="#pane-edit-8b" role="tab">
                                    <i class="fas fa-user-graduate mr-1"></i> 8B. Bimbingan & Uji TA/KP
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold py-1 px-3" id="tab-edit-8c" data-toggle="pill" href="#pane-edit-8c" role="tab">
                                    <i class="fas fa-file-alt mr-1"></i> 8C. Ujian (UTS & UAS)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold py-1 px-3" id="tab-edit-potongan" data-toggle="pill" href="#pane-edit-potongan" role="tab">
                                    <i class="fas fa-cut mr-1"></i> Potongan & Catatan
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content border rounded p-3 bg-white" id="editHonorTabContent">
                            {{-- TAB 8A: SKS --}}
                            <div class="tab-pane fade show active" id="pane-edit-8a" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">SKS Struktural</label>
                                            <input type="number" step="0.5" name="sks_struktural" id="edit_sks_struktural" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">SKS Riil Mengajar</label>
                                            <input type="number" step="0.5" name="sks_mengajar" id="edit_sks_mengajar" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small text-muted">Total SKS (Wajib: 12)</label>
                                            <input type="text" id="edit_total_sks" class="form-control form-control-sm bg-light" value="0" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small text-success">SKS Lebih (Terhitung)</label>
                                            <input type="text" id="edit_sks_lebih" class="form-control form-control-sm bg-light font-weight-bold text-success" value="0" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Tarif per SKS (Rp)</label>
                                            <input type="number" name="tarif_sks" id="edit_tarif_sks" class="form-control form-control-sm calc-edit" value="25000">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Alokasi Jumlah Pertemuan</label>
                                            <input type="number" name="jumlah_pertemuan" id="edit_jumlah_pertemuan" class="form-control form-control-sm calc-edit" value="3">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light border p-2 mb-0 mt-3 text-right">
                                            <small class="text-muted font-weight-bold">Subtotal Honor 8A (SKS):</small>
                                            <h6 class="font-weight-bold text-success mb-0" id="edit_preview_8a">Rp 0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB 8B: Bimbingan & Penguji --}}
                            <div class="tab-pane fade" id="pane-edit-8b" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Jml Bimbingan TA/Skripsi (Mhs)</label>
                                            <input type="number" name="jml_bimbingan_ta" id="edit_jml_bimbingan_ta" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small text-muted">Tarif Bimbingan (Rp/mhs)</label>
                                            <input type="number" name="tarif_bimbingan_ta" id="edit_tarif_bimbingan_ta" class="form-control form-control-sm calc-edit" value="200000">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Jml Penguji Sidang TA (Mhs)</label>
                                            <input type="number" name="jml_penguji_ta" id="edit_jml_penguji_ta" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small text-muted">Tarif Penguji (Rp/mhs)</label>
                                            <input type="number" name="tarif_penguji_ta" id="edit_tarif_penguji_ta" class="form-control form-control-sm calc-edit" value="75000">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Jml Kerja Praktek / KP (Mhs)</label>
                                            <input type="number" name="jml_kerja_praktek" id="edit_jml_kerja_praktek" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small text-muted">Tarif KP (Rp/mhs)</label>
                                            <input type="number" name="tarif_kerja_praktek" id="edit_tarif_kerja_praktek" class="form-control form-control-sm calc-edit" value="150000">
                                        </div>
                                    </div>
                                </div>
                                <div class="card bg-light border p-2 mb-0 mt-2 text-right">
                                    <small class="text-muted font-weight-bold">Subtotal Honor 8B (Bimbing & Uji TA/KP):</small>
                                    <h6 class="font-weight-bold text-primary mb-0" id="edit_preview_8b">Rp 0</h6>
                                </div>
                            </div>

                            {{-- TAB 8C: Ujian --}}
                            <div class="tab-pane fade" id="pane-edit-8c" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Mata Kuliah Diampu</label>
                                            <input type="text" name="mata_kuliah" id="edit_mata_kuliah" class="form-control form-control-sm" placeholder="Contoh: Pemrograman Web Lanjut">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small">Tipe Kelas</label>
                                            <select name="tipe_kelas" id="edit_tipe_kelas" class="form-control form-control-sm">
                                                <option value="T">Teori (T)</option>
                                                <option value="T/P">Teori & Praktik (T/P)</option>
                                                <option value="P">Praktik (P)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-md-6 border-right">
                                        <h6 class="font-weight-bold text-secondary small mb-2"><i class="fas fa-file-signature mr-1"></i> Pembuatan Naskah Soal</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="small">Kelas UTS</label>
                                                <input type="number" name="jml_kelas_uts" id="edit_jml_kelas_uts" class="form-control form-control-sm calc-edit" value="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="small">Kelas UAS</label>
                                                <input type="number" name="jml_kelas_uas" id="edit_jml_kelas_uas" class="form-control form-control-sm calc-edit" value="0">
                                            </div>
                                        </div>
                                        <div class="form-group mt-2 mb-1">
                                            <label class="small text-muted">Tarif Buat Soal (Rp/kelas)</label>
                                            <input type="number" name="tarif_soal" id="edit_tarif_soal" class="form-control form-control-sm calc-edit" value="25000">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="font-weight-bold text-secondary small mb-2"><i class="fas fa-check-double mr-1"></i> Koreksi Hasil Ujian Mahasiswa</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="small">Peserta UTS (Mhs)</label>
                                                <input type="number" name="jml_peserta_uts" id="edit_jml_peserta_uts" class="form-control form-control-sm calc-edit" value="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="small">Peserta UAS (Mhs)</label>
                                                <input type="number" name="jml_peserta_uas" id="edit_jml_peserta_uas" class="form-control form-control-sm calc-edit" value="0">
                                            </div>
                                        </div>
                                        <div class="form-group mt-2 mb-1">
                                            <label class="small text-muted">Tarif Koreksi (Rp/mhs)</label>
                                            <input type="number" name="tarif_koreksi" id="edit_tarif_koreksi" class="form-control form-control-sm calc-edit" value="2000">
                                        </div>
                                    </div>
                                </div>
                                <div class="card bg-light border p-2 mb-0 mt-2 text-right">
                                    <small class="text-muted font-weight-bold">Subtotal Honor 8C (Ujian):</small>
                                    <h6 class="font-weight-bold text-info mb-0" id="edit_preview_8c">Rp 0</h6>
                                </div>
                            </div>

                            {{-- TAB Potongan & Catatan --}}
                            <div class="tab-pane fade" id="pane-edit-potongan" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small text-danger">Potongan Pajak PPh (Rp)</label>
                                            <input type="number" name="potongan_pajak" id="edit_potongan_pajak" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold small text-danger">Potongan Lainnya (Rp)</label>
                                            <input type="number" name="potongan_lainnya" id="edit_potongan_lainnya" class="form-control form-control-sm calc-edit" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold small">Keterangan Potongan</label>
                                    <input type="text" name="keterangan_potongan" id="edit_keterangan_potongan" class="form-control form-control-sm" placeholder="Contoh: Pajak PPh 21 / Potongan Kas">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Catatan Koreksi (Opsional)</label>
                                    <textarea name="catatan_koreksi" id="edit_catatan_koreksi" class="form-control form-control-sm" rows="2" placeholder="Catatan internal pengajuan..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Live Grand Total Preview Bar --}}
                        <div class="card bg-dark text-white p-3 shadow-sm border-0 mt-3 mb-0">
                            <div class="row text-center align-items-center">
                                <div class="col-4 border-right">
                                    <small class="text-uppercase text-muted font-weight-bold">Total Honor Kotor</small>
                                    <h5 class="font-weight-bold text-warning mb-0" id="edit_preview_kotor">Rp 0</h5>
                                </div>
                                <div class="col-4 border-right">
                                    <small class="text-uppercase text-muted font-weight-bold">Total Potongan</small>
                                    <h5 class="font-weight-bold text-danger mb-0" id="edit_preview_potongan">Rp 0</h5>
                                </div>
                                <div class="col-4">
                                    <small class="text-uppercase text-muted font-weight-bold">Total Transfer (Net)</small>
                                    <h4 class="font-weight-bold text-success mb-0" id="edit_preview_transfer">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold" id="btnSubmitEditHonor">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Submit Approval ke Validator 1 --}}
    <div class="modal fade" id="modalSubmitApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.honorarium.submit-approval', $period->id) }}" method="POST">
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
                            Apakah Anda yakin seluruh data honorarium pada periode <strong>{{ $period->nama_periode }}</strong> sudah dikroscek dengan benar?
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
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold">
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
                <form action="{{ route('admin.honorarium.approve-step', $period->id) }}" method="POST">
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
                            Berikan persetujuan untuk berkas honorarium dosen <strong>{{ $period->nama_periode }}</strong>.
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
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                            <i class="fas fa-check mr-1"></i> Setujui Berkas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Reject Step (Minta Revisi) --}}
    <div class="modal fade" id="modalRejectStep" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.honorarium.reject-step', $period->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-undo-alt mr-2"></i> Minta Revisi Berkas
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2 text-danger font-weight-bold">
                            Kembalikan berkas honorarium ke Pembuat Draft untuk diperbaiki.
                        </p>
                        <div class="form-group">
                            <label class="font-weight-bold small">Catatan Revisi / Hal yang Harus Dibenahi <span class="text-danger">*</span></label>
                            <textarea name="catatan_revisi" class="form-control form-control-sm" rows="4" placeholder="Tuliskan secara jelas poin-poin yang perlu diperbaiki oleh pembuat draft..." required minlength="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
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
                <form action="{{ route('admin.honorarium.unlock', $period->id) }}" method="POST">
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
                            Sebagai <strong>Pembuat Draft</strong>, Anda berwenang membuka kunci periode honorarium ini agar data dosen dapat dibenahi kembali jika terjadi kekeliruan.
                        </p>
                        <div class="form-group">
                            <label class="font-weight-bold small">Alasan Pembukaan Kunci <span class="text-danger">*</span></label>
                            <textarea name="alasan_buka_kunci" class="form-control" rows="3" required placeholder="Sertakan alasan pembukaan kunci untuk keperluan audit log..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold">
                            <i class="fas fa-unlock mr-1"></i> Ya, Buka Kunci
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Riwayat Approval --}}
    <div class="modal fade" id="modalHistoryApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-history mr-2"></i> Riwayat & Rekam Jejak Approval: {{ $period->nama_periode }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    @if($period->approvals->count() == 0)
                        <div class="text-center py-4 text-muted">Belum ada riwayat aktivitas.</div>
                    @else
                        <div class="timeline timeline-inverse">
                            @foreach($period->approvals as $app)
                                <div>
                                    @php
                                        $iconClass = 'fas fa-info bg-primary';
                                        if ($app->action === 'submitted') $iconClass = 'fas fa-paper-plane bg-info';
                                        if ($app->action === 'approved') $iconClass = 'fas fa-check bg-success';
                                        if ($app->action === 'revision_requested') $iconClass = 'fas fa-undo bg-danger';
                                        if ($app->action === 'unlocked') $iconClass = 'fas fa-unlock bg-warning';
                                    @endphp
                                    <i class="{{ $iconClass }}"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock mr-1"></i> {{ $app->created_at->format('d M Y, H:i') }}</span>
                                        <h3 class="timeline-header font-weight-bold">
                                            {{ $app->role_label }} ({{ $app->karyawan->nama ?? ($app->user->name ?? 'User') }})
                                        </h3>
                                        @if($app->note)
                                            <div class="timeline-body small text-secondary">
                                                {{ $app->note }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div><i class="far fa-clock bg-gray"></i></div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL (READ ONLY) --}}
    <div class="modal fade" id="modalDetailHonor" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Rincian Honorarium: <span id="detailDosenName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="detailContentBody">
                    {{-- Dynamic details filled via JS --}}
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <a href="#" id="detailBtnSlipPdf" target="_blank" class="btn btn-danger btn-sm font-weight-bold">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Slip PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var oTable = $('#table-honorarium-dosen').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.honorarium.karyawan-json', $period->id) }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'dosen_info', name: 'nama_dosen', className: 'align-middle' },
                    { data: 'jafung_badge', name: 'kode_jafung', className: 'align-middle' },
                    { data: 'komponen_info', name: 'komponen_info', orderable: false, searchable: false, className: 'align-middle' },
                    { data: 'honor_kotor_formatted', name: 'total_honor_kotor', className: 'text-right align-middle' },
                    { data: 'potongan_info', name: 'total_potongan', className: 'align-middle text-center' },
                    { data: 'total_transfer_formatted', name: 'total_transfer', className: 'text-right align-middle font-weight-bold' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                ],
                pageLength: 50,
                language: {
                    search: "Cari Dosen:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Belum ada dosen yang ditambahkan pada periode honorarium ini.",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ dosen",
                    infoEmpty: "Belum ada dosen yang ditambahkan",
                    paginate: { first: "Pertama", last: "Terakhir", next: "Lanjut", previous: "Kembali" }
                }
            });

            $('#btnShowHistory').click(function() {
                $('#modalHistoryApproval').modal('show');
            });

            // Initialize Select2 in Modal Add Dosen
            if ($('#selectAddDosenId').length) {
                $('#selectAddDosenId').select2({
                    dropdownParent: $('#modalAddDosen'),
                    placeholder: '-- Ketik Nama atau NIP Dosen --',
                    allowClear: true
                });
            }

            // --- FUNGSI KALKULASI LIVE (MODAL TAMBAH) ---
            function calcAddLive() {
                var sksStruktur = parseFloat($('#add_sks_struktural').val()) || 0;
                var sksAjar     = parseFloat($('#add_sks_mengajar').val()) || 0;
                var totalSks    = sksStruktur + sksAjar;
                var sksLebih    = Math.max(0, totalSks - 12);
                var tarifSks    = parseFloat($('#add_tarif_sks').val()) || 0;
                var ptm         = parseInt($('#add_jumlah_pertemuan').val()) || 3;
                var tot8a       = sksLebih * tarifSks * ptm;

                $('#add_total_sks').val(totalSks);
                $('#add_sks_lebih').val(sksLebih);
                $('#add_preview_8a').text('Rp ' + tot8a.toLocaleString('id-ID'));

                // 8B
                var jmlBimbingan = parseInt($('#add_jml_bimbingan_ta').val()) || 0;
                var tarBimbingan = parseFloat($('#add_tarif_bimbingan_ta').val()) || 0;
                var totBimbingan = jmlBimbingan * tarBimbingan;

                var jmlPenguji = parseInt($('#add_jml_penguji_ta').val()) || 0;
                var tarPenguji = parseFloat($('#add_tarif_penguji_ta').val()) || 0;
                var totPenguji = jmlPenguji * tarPenguji;

                var jmlKp = parseInt($('#add_jml_kerja_praktek').val()) || 0;
                var tarKp = parseFloat($('#add_tarif_kerja_praktek').val()) || 0;
                var totKp = jmlKp * tarKp;

                var tot8b = totBimbingan + totPenguji + totKp;
                $('#add_preview_8b').text('Rp ' + tot8b.toLocaleString('id-ID'));

                // 8C
                var klsUts = parseInt($('#add_jml_kelas_uts').val()) || 0;
                var klsUas = parseInt($('#add_jml_kelas_uas').val()) || 0;
                var tarSoal = parseFloat($('#add_tarif_soal').val()) || 0;
                var totSoal = (klsUts + klsUas) * tarSoal;

                var mhsUts = parseInt($('#add_jml_peserta_uts').val()) || 0;
                var mhsUas = parseInt($('#add_jml_peserta_uas').val()) || 0;
                var tarKoreksi = parseFloat($('#add_tarif_koreksi').val()) || 0;
                var totKoreksi = (mhsUts + mhsUas) * tarKoreksi;

                var tot8c = totSoal + totKoreksi;
                $('#add_preview_8c').text('Rp ' + tot8c.toLocaleString('id-ID'));

                // Totals
                var totKotor = tot8a + tot8b + tot8c;
                var potPajak = parseFloat($('#add_potongan_pajak').val()) || 0;
                var potLain  = parseFloat($('#add_potongan_lainnya').val()) || 0;
                var totPot   = potPajak + potLain;
                var totNet   = Math.max(0, totKotor - totPot);

                $('#add_preview_kotor').text('Rp ' + totKotor.toLocaleString('id-ID'));
                $('#add_preview_potongan').text('Rp ' + totPot.toLocaleString('id-ID'));
                $('#add_preview_transfer').text('Rp ' + totNet.toLocaleString('id-ID'));
            }

            $('.calc-add').on('input change', function() {
                calcAddLive();
            });

            // Live Tarif Otomatis saat Ganti Tipe Kelas (Modal Add)
            var currentAddTarif = null;
            function updateAddTarifUjian() {
                if (!currentAddTarif) return;
                var tipe = $('#add_tipe_kelas').val();
                if (tipe === 'T/P' || tipe === 'P') {
                    $('#add_tarif_soal').val(currentAddTarif.tarif_soal_teori_praktik || 30000);
                    $('#add_tarif_koreksi').val(currentAddTarif.tarif_koreksi_teori_praktik || 2500);
                } else {
                    $('#add_tarif_soal').val(currentAddTarif.tarif_soal_teori || 25000);
                    $('#add_tarif_koreksi').val(currentAddTarif.tarif_koreksi_teori || 2000);
                }
                calcAddLive();
            }

            $('#add_tipe_kelas').on('change', function() {
                updateAddTarifUjian();
            });

            // ON CHANGE: Pilih Dosen di Modal Add
            $('#selectAddDosenId').on('change', function() {
                var dosenId = $(this).val();
                if (!dosenId) {
                    currentAddTarif = null;
                    $('#addDosenProfileCard').hide();
                    $('#addDosenFormSection').hide();
                    $('#btnSubmitAddDosen').prop('disabled', true);
                    return;
                }

                $.ajax({
                    url: "{{ url('admin/honorarium/dosen-tarif') }}/" + dosenId,
                    type: 'GET',
                    success: function(res) {
                        if (res.success) {
                            var d = res.dosen;
                            var t = res.tarif;
                            currentAddTarif = t;

                            $('#addDosenNamaText').text(d.nama + (d.nik_nip ? ' (' + d.nik_nip + ')' : ''));
                            $('#addDosenUnitText').text(d.nama_unit);
                            $('#addDosenStrukturalText').text(d.struktural);
                            $('#addDosenBankText').text((d.rekening_bank || 'BSI') + ' - ' + (d.nomor_rekening || '-') + ' a.n. ' + (d.nama_rekening || d.nama));
                            $('#addDosenJafungBadge').text((d.kode_jafung || 'TP') + ' - ' + (d.nama_jafung || 'Tenaga Pengajar'));

                            if (t) {
                                $('#addDosenTarifSummary').html(
                                    `Tarif SKS: <strong>Rp ${parseFloat(t.tarif_sks_hadir || 0).toLocaleString('id-ID')}</strong> | Bimbing TA: <strong>Rp ${parseFloat(t.tarif_bimbingan_ta || 0).toLocaleString('id-ID')}</strong> | Penguji: <strong>Rp ${parseFloat(t.tarif_penguji_ta || 0).toLocaleString('id-ID')}</strong> | Soal (T / TP): <strong>Rp ${parseFloat(t.tarif_soal_teori || 0).toLocaleString('id-ID')} / Rp ${parseFloat(t.tarif_soal_teori_praktik || 0).toLocaleString('id-ID')}</strong>`
                                );
                                $('#add_tarif_sks').val(t.tarif_sks_hadir || 25000);
                                $('#add_tarif_bimbingan_ta').val(t.tarif_bimbingan_ta || 200000);
                                $('#add_tarif_penguji_ta').val(t.tarif_penguji_ta || 75000);
                                $('#add_tarif_kerja_praktek').val(t.tarif_kerja_praktek || 150000);
                                updateAddTarifUjian();
                            }

                            $('#add_sks_struktural').val(d.sks_struktural || 0);

                            $('#addDosenProfileCard').slideDown();
                            $('#addDosenFormSection').slideDown();
                            $('#btnSubmitAddDosen').prop('disabled', false);

                            calcAddLive();
                        }
                    },
                    error: function() {
                        alert('Gagal mengambil data profil dan tarif dosen.');
                    }
                });
            });

            // SUBMIT: Form Tambah Dosen
            $('#formAddDosen').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = $('#btnSubmitAddDosen');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Dosen Penerima');
                        if (res.success) {
                            $('#modalAddDosen').modal('hide');
                            form[0].reset();
                            $('#selectAddDosenId').val('').trigger('change');
                            oTable.ajax.reload();

                            if (res.period) {
                                $('#sumPegawai').text(res.period.total_pegawai);
                                $('#sumKotor').text('Rp ' + parseFloat(res.period.total_gaji_kotor || 0).toLocaleString('id-ID'));
                                $('#sumPotongan').text('Rp ' + parseFloat(res.period.total_potongan || 0).toLocaleString('id-ID'));
                                $('#sumBersih').text('Rp ' + parseFloat(res.period.total_gaji_bersih || 0).toLocaleString('id-ID'));
                            }
                            alert(res.message);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Dosen Penerima');
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menambahkan dosen.';
                        alert(msg);
                    }
                });
            });

            // --- FUNGSI KALKULASI LIVE (MODAL EDIT) ---
            function calcEditLive() {
                var sksStruktur = parseFloat($('#edit_sks_struktural').val()) || 0;
                var sksAjar     = parseFloat($('#edit_sks_mengajar').val()) || 0;
                var totalSks    = sksStruktur + sksAjar;
                var sksLebih    = Math.max(0, totalSks - 12);
                var tarifSks    = parseFloat($('#edit_tarif_sks').val()) || 0;
                var ptm         = parseInt($('#edit_jumlah_pertemuan').val()) || 3;
                var tot8a       = sksLebih * tarifSks * ptm;

                $('#edit_total_sks').val(totalSks);
                $('#edit_sks_lebih').val(sksLebih);
                $('#edit_preview_8a').text('Rp ' + tot8a.toLocaleString('id-ID'));

                // 8B
                var jmlBimbingan = parseInt($('#edit_jml_bimbingan_ta').val()) || 0;
                var tarBimbingan = parseFloat($('#edit_tarif_bimbingan_ta').val()) || 0;
                var totBimbingan = jmlBimbingan * tarBimbingan;

                var jmlPenguji = parseInt($('#edit_jml_penguji_ta').val()) || 0;
                var tarPenguji = parseFloat($('#edit_tarif_penguji_ta').val()) || 0;
                var totPenguji = jmlPenguji * tarPenguji;

                var jmlKp = parseInt($('#edit_jml_kerja_praktek').val()) || 0;
                var tarKp = parseFloat($('#edit_tarif_kerja_praktek').val()) || 0;
                var totKp = jmlKp * tarKp;

                var tot8b = totBimbingan + totPenguji + totKp;
                $('#edit_preview_8b').text('Rp ' + tot8b.toLocaleString('id-ID'));

                // 8C
                var klsUts = parseInt($('#edit_jml_kelas_uts').val()) || 0;
                var klsUas = parseInt($('#edit_jml_kelas_uas').val()) || 0;
                var tarSoal = parseFloat($('#edit_tarif_soal').val()) || 0;
                var totSoal = (klsUts + klsUas) * tarSoal;

                var mhsUts = parseInt($('#edit_jml_peserta_uts').val()) || 0;
                var mhsUas = parseInt($('#edit_jml_peserta_uas').val()) || 0;
                var tarKoreksi = parseFloat($('#edit_tarif_koreksi').val()) || 0;
                var totKoreksi = (mhsUts + mhsUas) * tarKoreksi;

                var tot8c = totSoal + totKoreksi;
                $('#edit_preview_8c').text('Rp ' + tot8c.toLocaleString('id-ID'));

                // Totals
                var totKotor = tot8a + tot8b + tot8c;
                var potPajak = parseFloat($('#edit_potongan_pajak').val()) || 0;
                var potLain  = parseFloat($('#edit_potongan_lainnya').val()) || 0;
                var totPot   = potPajak + potLain;
                var totNet   = Math.max(0, totKotor - totPot);

                $('#edit_preview_kotor').text('Rp ' + totKotor.toLocaleString('id-ID'));
                $('#edit_preview_potongan').text('Rp ' + totPot.toLocaleString('id-ID'));
                $('#edit_preview_transfer').text('Rp ' + totNet.toLocaleString('id-ID'));
            }

            $('.calc-edit').on('input change', function() {
                calcEditLive();
            });

            // Live Tarif Otomatis saat Ganti Tipe Kelas (Modal Edit)
            var currentEditTarif = null;
            function updateEditTarifUjian() {
                if (!currentEditTarif) return;
                var tipe = $('#edit_tipe_kelas').val();
                if (tipe === 'T/P' || tipe === 'P') {
                    $('#edit_tarif_soal').val(currentEditTarif.tarif_soal_teori_praktik || 30000);
                    $('#edit_tarif_koreksi').val(currentEditTarif.tarif_koreksi_teori_praktik || 2500);
                } else {
                    $('#edit_tarif_soal').val(currentEditTarif.tarif_soal_teori || 25000);
                    $('#edit_tarif_koreksi').val(currentEditTarif.tarif_koreksi_teori || 2000);
                }
                calcEditLive();
            }

            $('#edit_tipe_kelas').on('change', function() {
                updateEditTarifUjian();
            });

            // ON CLICK: Edit Dosen Modal Trigger
            $('body').on('click', '.btn-edit-honor', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/honorarium/data') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            var d = res.data;
                            currentEditTarif = res.tarif || null;

                            $('#formEditHonor').attr('action', "{{ url('admin/honorarium/update-data') }}/" + d.id);
                            $('#editModalDosenTitle').text(d.nama_dosen + ' (' + (d.kode_jafung || 'TP') + ')');

                            // 8A
                            $('#edit_sks_struktural').val(d.sks_struktural || 0);
                            $('#edit_sks_mengajar').val(d.sks_mengajar || 0);
                            $('#edit_tarif_sks').val(d.tarif_sks || 25000);
                            $('#edit_jumlah_pertemuan').val(d.jumlah_pertemuan || 3);

                            // 8B
                            $('#edit_jml_bimbingan_ta').val(d.jml_bimbingan_ta || 0);
                            $('#edit_tarif_bimbingan_ta').val(d.tarif_bimbingan_ta || 200000);
                            $('#edit_jml_penguji_ta').val(d.jml_penguji_ta || 0);
                            $('#edit_tarif_penguji_ta').val(d.tarif_penguji_ta || 75000);
                            $('#edit_jml_kerja_praktek').val(d.jml_kerja_praktek || 0);
                            $('#edit_tarif_kerja_praktek').val(d.tarif_kerja_praktek || 150000);

                            // 8C
                            $('#edit_mata_kuliah').val(d.mata_kuliah || '');
                            $('#edit_tipe_kelas').val(d.tipe_kelas || 'T');
                            $('#edit_jml_kelas_uts').val(d.jml_kelas_uts || 0);
                            $('#edit_jml_kelas_uas').val(d.jml_kelas_uas || 0);
                            $('#edit_tarif_soal').val(d.tarif_soal || (currentEditTarif ? currentEditTarif.tarif_soal_teori : 25000));
                            $('#edit_jml_peserta_uts').val(d.jml_peserta_uts || 0);
                            $('#edit_jml_peserta_uas').val(d.jml_peserta_uas || 0);
                            $('#edit_tarif_koreksi').val(d.tarif_koreksi || (currentEditTarif ? currentEditTarif.tarif_koreksi_teori : 2000));

                            // Potongan
                            $('#edit_potongan_pajak').val(d.potongan_pajak || 0);
                            $('#edit_potongan_lainnya').val(d.potongan_lainnya || 0);
                            $('#edit_keterangan_potongan').val(d.keterangan_potongan || '');
                            $('#edit_catatan_koreksi').val(d.catatan_koreksi || '');

                            calcEditLive();
                            $('#modalEditHonor').modal('show');
                        }
                    }
                });
            });

            // SUBMIT: Form Edit Dosen
            $('#formEditHonor').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = $('#btnSubmitEditHonor');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                        if (res.success) {
                            $('#modalEditHonor').modal('hide');
                            oTable.ajax.reload();

                            if (res.period) {
                                $('#sumPegawai').text(res.period.total_pegawai);
                                $('#sumKotor').text('Rp ' + parseFloat(res.period.total_gaji_kotor || 0).toLocaleString('id-ID'));
                                $('#sumPotongan').text('Rp ' + parseFloat(res.period.total_potongan || 0).toLocaleString('id-ID'));
                                $('#sumBersih').text('Rp ' + parseFloat(res.period.total_gaji_bersih || 0).toLocaleString('id-ID'));
                            }
                            alert(res.message);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal memperbarui data.';
                        alert(msg);
                    }
                });
            });

            // ON CLICK: Delete Dosen from Period
            $('body').on('click', '.btn-delete-dosen', function() {
                var id = $(this).data('id');
                var name = $(this).data('name') || 'dosen ini';

                if (confirm('Apakah Anda yakin ingin menghapus ' + name + ' dari daftar penerima honorarium periode ini?')) {
                    $.ajax({
                        url: "{{ url('admin/honorarium/delete-dosen') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success) {
                                oTable.ajax.reload();
                                if (res.period) {
                                    $('#sumPegawai').text(res.period.total_pegawai);
                                    $('#sumKotor').text('Rp ' + parseFloat(res.period.total_gaji_kotor || 0).toLocaleString('id-ID'));
                                    $('#sumPotongan').text('Rp ' + parseFloat(res.period.total_potongan || 0).toLocaleString('id-ID'));
                                    $('#sumBersih').text('Rp ' + parseFloat(res.period.total_gaji_bersih || 0).toLocaleString('id-ID'));
                                }
                                alert(res.message);
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus dosen.';
                            alert(msg);
                        }
                    });
                }
            });

            // ON CLICK: Detail Modal
            $('body').on('click', '.btn-detail-honor', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/honorarium/data') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            var d = res.data;
                            $('#detailDosenName').text(d.nama_dosen + (d.nik_nip ? ' (' + d.nik_nip + ')' : ''));
                            
                            var html = `
                                <div class="card bg-light border p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="small text-muted">Jabatan Fungsional:</div>
                                            <strong>${d.kode_jafung || 'TP'} - ${d.nama_jafung || 'Tenaga Pengajar'}</strong>
                                            <div class="small text-muted mt-1">Fakultas/Unit:</div>
                                            <strong>${d.nama_unit || '-'}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted">Jabatan Struktural:</div>
                                            <strong>${d.struktural || 'Dosen'}</strong>
                                            <div class="small text-muted mt-1">Rekening Bank:</div>
                                            <strong>${d.rekening_bank || 'BSI'} - ${d.nomor_rekening || '-'}</strong>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // 8A Breakdown
                            if (parseFloat(d.total_honor_sks || 0) > 0 || parseFloat(d.sks_mengajar || 0) > 0) {
                                html += `
                                    <div class="card border-success mb-3">
                                        <div class="card-header bg-success text-white py-1 px-3">
                                            <strong style="font-size: 8.5pt;"><i class="fas fa-chalkboard-teacher mr-1"></i> 8A. Kelebihan SKS & Dosen Tidak Tetap</strong>
                                        </div>
                                        <div class="card-body p-2">
                                            <table class="table table-sm table-borderless mb-0" style="font-size: 8.5pt;">
                                                <tr><td width="60%">SKS Struktural:</td><td class="text-right">${d.sks_struktural} SKS</td></tr>
                                                <tr><td>SKS Riil Mengajar:</td><td class="text-right">${d.sks_mengajar} SKS</td></tr>
                                                <tr><td>Total SKS (Wajib: 12):</td><td class="text-right font-weight-bold">${d.total_sks} SKS</td></tr>
                                                <tr class="text-success font-weight-bold"><td>SKS Lebih Dihitung:</td><td class="text-right">${d.sks_lebih} SKS</td></tr>
                                                <tr><td>Tarif per SKS:</td><td class="text-right">Rp ${parseFloat(d.tarif_sks || 0).toLocaleString('id-ID')}</td></tr>
                                                <tr><td>Alokasi Pertemuan:</td><td class="text-right">${d.jumlah_pertemuan} Pertemuan</td></tr>
                                                <tr class="border-top font-weight-bold text-success"><td>Subtotal Honor SKS:</td><td class="text-right">Rp ${parseFloat(d.total_honor_sks || 0).toLocaleString('id-ID')}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            }

                            // 8B Breakdown
                            var tot8b = parseFloat(d.total_bimbingan_ta || 0) + parseFloat(d.total_penguji_ta || 0) + parseFloat(d.total_kerja_praktek || 0);
                            if (tot8b > 0) {
                                html += `
                                    <div class="card border-primary mb-3">
                                        <div class="card-header bg-primary text-white py-1 px-3">
                                            <strong style="font-size: 8.5pt;"><i class="fas fa-user-graduate mr-1"></i> 8B. Bimbingan & Penguji TA / KP</strong>
                                        </div>
                                        <div class="card-body p-2">
                                            <table class="table table-sm table-borderless mb-0" style="font-size: 8.5pt;">
                                                <tr><td width="60%">Bimbingan Skripsi/TA:</td><td class="text-right">${d.jml_bimbingan_ta} mhs × Rp ${parseFloat(d.tarif_bimbingan_ta || 0).toLocaleString('id-ID')} = <strong>Rp ${parseFloat(d.total_bimbingan_ta || 0).toLocaleString('id-ID')}</strong></td></tr>
                                                <tr><td>Penguji Sidang TA:</td><td class="text-right">${d.jml_penguji_ta} mhs × Rp ${parseFloat(d.tarif_penguji_ta || 0).toLocaleString('id-ID')} = <strong>Rp ${parseFloat(d.total_penguji_ta || 0).toLocaleString('id-ID')}</strong></td></tr>
                                                <tr><td>Kerja Praktek (KP):</td><td class="text-right">${d.jml_kerja_praktek} mhs × Rp ${parseFloat(d.tarif_kerja_praktek || 0).toLocaleString('id-ID')} = <strong>Rp ${parseFloat(d.total_kerja_praktek || 0).toLocaleString('id-ID')}</strong></td></tr>
                                                <tr class="border-top font-weight-bold text-primary"><td>Subtotal Honor Bimbing & Uji:</td><td class="text-right">Rp ${tot8b.toLocaleString('id-ID')}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            }

                            // 8C Breakdown
                            var tot8c = parseFloat(d.total_honor_soal || 0) + parseFloat(d.total_honor_koreksi || 0);
                            if (tot8c > 0) {
                                html += `
                                    <div class="card border-info mb-3">
                                        <div class="card-header bg-info text-white py-1 px-3">
                                            <strong style="font-size: 8.5pt;"><i class="fas fa-file-alt mr-1"></i> 8C. Honorarium Ujian (UTS & UAS)</strong>
                                        </div>
                                        <div class="card-body p-2">
                                            <table class="table table-sm table-borderless mb-0" style="font-size: 8.5pt;">
                                                <tr><td width="60%">Mata Kuliah:</td><td class="text-right font-weight-bold">${d.mata_kuliah || '-'} (${d.tipe_kelas || 'T'})</td></tr>
                                                <tr><td>Pembuatan Soal (UTS: ${d.jml_kelas_uts}, UAS: ${d.jml_kelas_uas}):</td><td class="text-right">${d.total_kelas_soal} kls × Rp ${parseFloat(d.tarif_soal || 0).toLocaleString('id-ID')} = <strong>Rp ${parseFloat(d.total_honor_soal || 0).toLocaleString('id-ID')}</strong></td></tr>
                                                <tr><td>Koreksi Jawaban (UTS: ${d.jml_peserta_uts}, UAS: ${d.jml_peserta_uas}):</td><td class="text-right">${d.total_peserta_koreksi} mhs × Rp ${parseFloat(d.tarif_koreksi || 0).toLocaleString('id-ID')} = <strong>Rp ${parseFloat(d.total_honor_koreksi || 0).toLocaleString('id-ID')}</strong></td></tr>
                                                <tr class="border-top font-weight-bold text-info"><td>Subtotal Honor Ujian:</td><td class="text-right">Rp ${tot8c.toLocaleString('id-ID')}</td></tr>
                                            </table>
                                        </div>
                                    </div>
                                `;
                            }

                            html += `
                                <div class="card bg-dark text-white p-3 shadow-sm border-0">
                                    <div class="row text-center">
                                        <div class="col-4 border-right">
                                            <small class="text-uppercase text-muted font-weight-bold">Total Honor Kotor</small>
                                            <h5 class="font-weight-bold text-warning mb-0">Rp ${parseFloat(d.total_honor_kotor || 0).toLocaleString('id-ID')}</h5>
                                        </div>
                                        <div class="col-4 border-right">
                                            <small class="text-uppercase text-muted font-weight-bold">Total Potongan</small>
                                            <h5 class="font-weight-bold text-danger mb-0">Rp ${parseFloat(d.total_potongan || 0).toLocaleString('id-ID')}</h5>
                                            ${d.keterangan_potongan ? '<small class="text-muted d-block mt-1">' + d.keterangan_potongan + '</small>' : ''}
                                        </div>
                                        <div class="col-4">
                                            <small class="text-uppercase text-muted font-weight-bold">Transfer Bersih</small>
                                            <h4 class="font-weight-bold text-success mb-0">Rp ${parseFloat(d.total_transfer || 0).toLocaleString('id-ID')}</h4>
                                        </div>
                                    </div>
                                </div>
                            `;

                            $('#detailContentBody').html(html);
                            $('#detailBtnSlipPdf').attr('href', "{{ url('admin/honorarium/slip-pdf') }}/" + d.id);
                            $('#modalDetailHonor').modal('show');
                        }
                    }
                });
            });
        });
    </script>
@endsection
