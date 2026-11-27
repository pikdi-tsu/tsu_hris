@extends('system::template.admin.header')

@section('title', 'Audit LPM (Lembaga Penjaminan Mutu)')

@section('link_href')
<style>
    .audit-stat-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1.15rem 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .audit-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.06);
    }
    .audit-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .table-audit th {
        background: #f8fafc;
        color: #334155;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
        vertical-align: middle;
        padding: 0.75rem 0.8rem;
    }
    .table-audit td {
        vertical-align: middle;
        font-size: 0.86rem;
        padding: 0.75rem 0.8rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-audit tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .table-audit tbody tr:hover {
        background-color: #f8fafc;
    }
    .table-audit tbody tr.active-row {
        background-color: #f0fdf4 !important;
        font-weight: 500;
    }

    .badge-status-terbuka {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        border-radius: 12px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
    }
    .badge-status-proses {
        background: #fef3c7;
        color: #92400e;
        font-weight: 600;
        border-radius: 12px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
    }
    .badge-status-selesai {
        background: #dcfce7;
        color: #166534;
        font-weight: 600;
        border-radius: 12px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
    }
    .badge-status-overdue {
        background: #f3e8ff;
        color: #6b21a8;
        font-weight: 600;
        border-radius: 12px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
    }

    .timeline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-1" style="font-size: 0.82rem;">
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Audit LPM</a></li>
                        <li class="breadcrumb-item active text-dark font-weight-bold" aria-current="page">Daftar Temuan</li>
                    </ol>
                </nav>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.5rem;">
                    <i class="fas fa-award text-primary mr-2" style="color: #094b54 !important;"></i>Audit LPM
                </h1>
                <p class="text-muted small mb-0">Monitoring hasil audit mutu akademik dan non-akademik serta tindak lanjut perbaikannya.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <div class="d-flex align-items-center bg-white p-2 px-3 border rounded-pill shadow-sm" style="gap: 8px;">
                    <i class="far fa-calendar-alt text-primary" style="color: #094b54;"></i>
                    <small class="text-muted font-weight-bold">Periode Audit:</small>
                    <select class="border-0 bg-transparent font-weight-bold text-dark small" style="outline: none;">
                        <option>Januari – Desember 2026</option>
                        <option>Juli – Desember 2025</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- 5 Stat Cards Top Bar (Mockup Image 12 Part 2) --}}
        <div class="row mb-4">
            <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="audit-stat-card">
                    <div class="audit-stat-icon bg-light text-primary" style="color: #0284c7 !important;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Total Audit</small>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $totalAudit }}</h4>
                        <small class="text-muted">dari 6 unit</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="audit-stat-card">
                    <div class="audit-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Standar Terpenuhi</small>
                        <h4 class="font-weight-bold mb-0 text-success">{{ $standarTerpenuhi }}</h4>
                        <small class="text-muted">{{ $persenTerpenuhi }}%</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="audit-stat-card">
                    <div class="audit-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Temuan</small>
                        <h4 class="font-weight-bold mb-0 text-warning">{{ $totalTemuan }}</h4>
                        <small class="text-muted">(12 item)</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 col-sm-6 mb-3 mb-xl-0">
                <div class="audit-stat-card">
                    <div class="audit-stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #dc2626;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Belum Ditindaklanjuti</small>
                        <h4 class="font-weight-bold mb-0 text-danger">{{ $belumDitindaklanjuti }}</h4>
                        <small class="text-muted">(4 item)</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 col-sm-6 mb-3 mb-xl-0">
                <div class="audit-stat-card">
                    <div class="audit-stat-icon" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Penyelesaian</small>
                        <h4 class="font-weight-bold mb-0" style="color: #7c3aed;">{{ $persenPenyelesaian }}%</h4>
                        <small class="text-muted">dari total temuan</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Layout: Left Table + Right Detail Panel --}}
        <div class="row">
            {{-- Left Column: Table Daftar Temuan Audit LPM --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                            <h6 class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-tasks mr-2" style="color: #094b54;"></i> Daftar Temuan Audit LPM
                            </h6>
                            <button type="button" class="btn btn-sm rounded-pill px-3 font-weight-bold text-white shadow-sm" style="background: #094b54; border-color: #094b54;" data-toggle="modal" data-target="#modalTambahTemuanLpm">
                                <i class="fas fa-plus mr-1"></i> Tambah Temuan
                            </button>
                        </div>

                        {{-- Filters Bar (Mockup Image 12) --}}
                        <form action="{{ route('admin.survey.audit.lpm') }}" method="GET" class="row">
                            <div class="col-md-3 col-6 mb-2">
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Cari temuan...">
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <select name="unit" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="all">Semua Unit</option>
                                    <option value="Fakultas Ekonomi" {{ ($filters['unit'] ?? '') === 'Fakultas Ekonomi' ? 'selected' : '' }}>Fakultas Ekonomi</option>
                                    <option value="FTI" {{ ($filters['unit'] ?? '') === 'FTI' ? 'selected' : '' }}>FTI</option>
                                    <option value="LPPM" {{ ($filters['unit'] ?? '') === 'LPPM' ? 'selected' : '' }}>LPPM</option>
                                    <option value="BAUK" {{ ($filters['unit'] ?? '') === 'BAUK' ? 'selected' : '' }}>BAUK</option>
                                    <option value="Fakultas Teknik" {{ ($filters['unit'] ?? '') === 'Fakultas Teknik' ? 'selected' : '' }}>Fakultas Teknik</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <select name="standar" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="all">Semua Standar</option>
                                    @foreach($standarList as $std)
                                        <option value="{{ $std }}" {{ ($filters['standar'] ?? '') === $std ? 'selected' : '' }}>{{ $std }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="all">Semua Status</option>
                                    <option value="Terbuka" {{ ($filters['status'] ?? '') === 'Terbuka' ? 'selected' : '' }}>Terbuka</option>
                                    <option value="Dalam Proses" {{ ($filters['status'] ?? '') === 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                    <option value="Selesai" {{ ($filters['status'] ?? '') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="Overdue" {{ ($filters['status'] ?? '') === 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                            <table class="table table-hover table-audit mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 35px;" class="text-center">No.</th>
                                        <th style="width: 95px;">Tgl Audit</th>
                                        <th style="width: 100px;">Unit Kerja</th>
                                        <th style="width: 120px;">Standar Mutu</th>
                                        <th style="width: 110px;">Aspek</th>
                                        <th style="width: 120px;">Indikator</th>
                                        <th style="min-width: 160px;">Temuan</th>
                                        <th style="min-width: 150px;">Rekomendasi</th>
                                        <th style="width: 55px;">PIC</th>
                                        <th style="width: 90px;">Deadline</th>
                                        <th style="width: 100px;" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($temuans as $idx => $t)
                                        @php
                                            $isActive = $selectedTemuan && $selectedTemuan->id == $t->id;
                                            $badgeStatus = match($t->status) {
                                                'Terbuka' => 'badge-status-terbuka',
                                                'Dalam Proses' => 'badge-status-proses',
                                                'Selesai' => 'badge-status-selesai',
                                                default => 'badge-status-overdue',
                                            };
                                        @endphp
                                        <tr class="{{ $isActive ? 'active-row' : '' }}" onclick="window.location.href='{{ route('admin.survey.audit.lpm', array_merge($filters, ['selected_id' => $t->id])) }}'">
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>{{ $t->tanggal_audit ? $t->tanggal_audit->translatedFormat('d M Y') : '-' }}</td>
                                            <td><span class="font-weight-bold text-dark">{{ $t->unit_nama }}</span></td>
                                            <td><small class="text-muted font-weight-semibold">{{ $t->standar_mutu }}</small></td>
                                            <td><small class="text-muted">{{ $t->aspek }}</small></td>
                                            <td><small class="text-muted">{{ $t->indikator }}</small></td>
                                            <td>
                                                <div class="text-dark small" style="line-height: 1.35;">{{ Str::limit($t->temuan, 65) }}</div>
                                            </td>
                                            <td>
                                                <div class="text-muted small" style="line-height: 1.35;">{{ Str::limit($t->rekomendasi, 65) }}</div>
                                            </td>
                                            <td><span class="font-weight-bold small text-dark">{{ $t->pic }}</span></td>
                                            <td><small class="text-muted">{{ $t->deadline ? $t->deadline->translatedFormat('d M Y') : '-' }}</small></td>
                                            <td class="text-center">
                                                <span class="{{ $badgeStatus }}">{{ $t->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">
                                                <i class="fas fa-clipboard-check fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                                <p class="mb-0">Tidak ada temuan audit LPM yang sesuai dengan filter.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Detail Temuan Panel (Mockup Image 12 Part 2) --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                @if($selectedTemuan)
                    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="font-weight-bold mb-0 text-dark">
                                <i class="fas fa-eye mr-2" style="color: #094b54;"></i> Detail Temuan
                            </h6>
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalEditTemuanLpm">
                                <i class="fas fa-pencil-alt mr-1"></i> Edit
                            </button>
                        </div>

                        <div class="card-body p-4">
                            {{-- Header Kode & Status --}}
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <i class="fas fa-graduation-cap text-primary fa-lg" style="color: #094b54 !important;"></i>
                                    <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">{{ $selectedTemuan->kode_temuan }}</h5>
                                </div>
                                <span class="badge-status-proses">{{ $selectedTemuan->status }}</span>
                            </div>

                            {{-- Metadata Table --}}
                            <div class="mb-3 small">
                                <div class="row mb-1">
                                    <div class="col-5 text-muted">Unit Kerja</div>
                                    <div class="col-7 font-weight-bold text-dark">{{ $selectedTemuan->unit_nama }}</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-muted">Standar Mutu</div>
                                    <div class="col-7 font-weight-bold text-dark">{{ $selectedTemuan->standar_mutu }}</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-muted">Aspek</div>
                                    <div class="col-7 text-dark">{{ $selectedTemuan->aspek }}</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-muted">Indikator</div>
                                    <div class="col-7 text-dark">{{ $selectedTemuan->indikator }}</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-muted">Target / Standar</div>
                                    <div class="col-7 font-weight-bold text-dark">{{ $selectedTemuan->target_standar ?: '-' }}</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-muted">Kondisi Aktual</div>
                                    <div class="col-7 text-danger font-weight-bold">{{ $selectedTemuan->kondisi_aktual ?: '-' }}</div>
                                </div>
                            </div>

                            <hr>

                            {{-- Temuan & Analisis & Rekomendasi --}}
                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.72rem;">Temuan</label>
                                <p class="small text-dark font-weight-semibold mb-3" style="line-height: 1.4;">{{ $selectedTemuan->temuan }}</p>

                                @if($selectedTemuan->analisis_penyebab)
                                    <label class="small text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.72rem;">Analisis Penyebab</label>
                                    <p class="small text-dark mb-3" style="line-height: 1.4;">{{ $selectedTemuan->analisis_penyebab }}</p>
                                @endif

                                <label class="small text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.72rem;">Rekomendasi</label>
                                <p class="small text-dark mb-3" style="line-height: 1.4;">{{ $selectedTemuan->rekomendasi }}</p>

                                <div class="row small mb-2">
                                    <div class="col-6">
                                        <span class="text-muted">PIC:</span> <strong class="text-dark">{{ $selectedTemuan->pic }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Target:</span> <strong class="text-dark">{{ $selectedTemuan->deadline ? $selectedTemuan->deadline->translatedFormat('d M Y') : '-' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- Bukti Tindak Lanjut --}}
                            <div class="mb-4">
                                <label class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 0.72rem;">Bukti Tindak Lanjut</label>
                                @if($selectedTemuan->bukti_tindak_lanjut)
                                    <div class="p-2 px-3 rounded bg-light border d-flex align-items-center justify-content-between" style="border-radius: 8px;">
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <i class="fas fa-file-pdf text-danger fa-lg"></i>
                                            <div>
                                                <div class="font-weight-bold text-dark small mb-0">{{ basename($selectedTemuan->bukti_tindak_lanjut) }}</div>
                                                <small class="text-muted">1.2 MB</small>
                                            </div>
                                        </div>
                                        <a href="#" class="text-primary"><i class="fas fa-download"></i></a>
                                    </div>
                                @else
                                    <div class="small text-muted font-italic">Belum ada bukti tindak lanjut diunggah.</div>
                                @endif
                            </div>

                            {{-- Riwayat Status Timeline (Mockup Image 12) --}}
                            <div>
                                <label class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 0.72rem;">Riwayat Status</label>
                                <ul class="list-unstyled mb-0 small">
                                    @if(is_array($selectedTemuan->riwayat_status))
                                        @foreach($selectedTemuan->riwayat_status as $rs)
                                            <li class="mb-2 d-flex align-items-center">
                                                <span class="timeline-dot bg-success"></span>
                                                <strong class="text-dark mr-2">{{ $rs['tanggal'] ?? '-' }}</strong>
                                                <span class="text-muted">{{ $rs['status'] ?? '-' }}</span>
                                            </li>
                                        @endforeach
                                    @else
                                        <li class="d-flex align-items-center">
                                            <span class="timeline-dot bg-success"></span>
                                            <strong class="text-dark mr-2">{{ $selectedTemuan->tanggal_audit ? $selectedTemuan->tanggal_audit->translatedFormat('d M Y') : '-' }}</strong>
                                            <span class="text-muted">Temuan dibuat</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 14px;">
                        <div class="card-body text-muted">
                            <i class="fas fa-clipboard-check fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <h6 class="font-weight-bold text-dark">Pilih Temuan</h6>
                            <p class="small mb-0">Klik salah satu baris temuan pada tabel di sebelah kiri untuk melihat rincian.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>

{{-- Modal Tambah Temuan LPM --}}
<div class="modal fade" id="modalTambahTemuanLpm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-plus-circle text-primary mr-2" style="color: #094b54 !important;"></i>Tambah Temuan Audit LPM
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.survey.audit.lpm.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Unit Kerja <span class="text-danger">*</span></label>
                            <input type="text" name="unit_nama" class="form-control form-control-sm" placeholder="Contoh: Fakultas Ekonomi, FTI, LPPM" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Tanggal Audit <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_audit" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Standar Mutu <span class="text-danger">*</span></label>
                            <input type="text" name="standar_mutu" class="form-control form-control-sm" placeholder="Contoh: Standar SDM, Standar Pembelajaran" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Aspek</label>
                            <input type="text" name="aspek" class="form-control form-control-sm" placeholder="Contoh: Kecukupan SDM, Publikasi">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Indikator</label>
                            <input type="text" name="indikator" class="form-control form-control-sm" placeholder="Contoh: Rasio dosen/mahasiswa">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Target / Standar</label>
                            <input type="text" name="target_standar" class="form-control form-control-sm" placeholder="Contoh: 1:30 atau >= 80%">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Kondisi Aktual</label>
                            <input type="text" name="kondisi_aktual" class="form-control form-control-sm" placeholder="Contoh: 1:40 atau 72%">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Uraian Temuan <span class="text-danger">*</span></label>
                        <textarea name="temuan" class="form-control form-control-sm" rows="2" placeholder="Deskripsikan temuan audit LPM..." required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Analisis Penyebab</label>
                        <textarea name="analisis_penyebab" class="form-control form-control-sm" rows="2" placeholder="Faktor akar penyebab ketidaksesuaian..."></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Rekomendasi</label>
                        <textarea name="rekomendasi" class="form-control form-control-sm" rows="2" placeholder="Tindakan korektif yang direkomendasikan..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">PIC</label>
                            <input type="text" name="pic" class="form-control form-control-sm" value="SDM">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Deadline</label>
                            <input type="date" name="deadline" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="Terbuka">Terbuka</option>
                                <option value="Dalam Proses" selected>Dalam Proses</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Overdue">Overdue</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm rounded-pill px-4 font-weight-bold text-white" style="background: #094b54;">Simpan Temuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Temuan LPM --}}
@if($selectedTemuan)
<div class="modal fade" id="modalEditTemuanLpm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-edit text-primary mr-2" style="color: #094b54 !important;"></i>Edit Temuan {{ $selectedTemuan->kode_temuan }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.survey.audit.lpm.update', $selectedTemuan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Unit Kerja <span class="text-danger">*</span></label>
                            <input type="text" name="unit_nama" value="{{ $selectedTemuan->unit_nama }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Tanggal Audit <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_audit" value="{{ $selectedTemuan->tanggal_audit ? $selectedTemuan->tanggal_audit->format('Y-m-d') : '' }}" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Standar Mutu <span class="text-danger">*</span></label>
                            <input type="text" name="standar_mutu" value="{{ $selectedTemuan->standar_mutu }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Aspek</label>
                            <input type="text" name="aspek" value="{{ $selectedTemuan->aspek }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Indikator</label>
                            <input type="text" name="indikator" value="{{ $selectedTemuan->indikator }}" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Target Standar</label>
                            <input type="text" name="target_standar" value="{{ $selectedTemuan->target_standar }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Kondisi Aktual</label>
                            <input type="text" name="kondisi_aktual" value="{{ $selectedTemuan->kondisi_aktual }}" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Uraian Temuan <span class="text-danger">*</span></label>
                        <textarea name="temuan" class="form-control form-control-sm" rows="3" required>{{ $selectedTemuan->temuan }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Analisis Penyebab</label>
                        <textarea name="analisis_penyebab" class="form-control form-control-sm" rows="2">{{ $selectedTemuan->analisis_penyebab }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Rekomendasi LPM</label>
                        <textarea name="rekomendasi" class="form-control form-control-sm" rows="2">{{ $selectedTemuan->rekomendasi }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">PIC Penanggung Jawab</label>
                            <input type="text" name="pic" value="{{ $selectedTemuan->pic }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Tenggat Waktu (Deadline)</label>
                            <input type="date" name="deadline" value="{{ $selectedTemuan->deadline ? $selectedTemuan->deadline->format('Y-m-d') : '' }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Status Temuan <span class="text-danger">*</span></label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="Terbuka" {{ $selectedTemuan->status === 'Terbuka' ? 'selected' : '' }}>Terbuka</option>
                                <option value="Dalam Proses" {{ $selectedTemuan->status === 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                                <option value="Selesai" {{ $selectedTemuan->status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Overdue" {{ $selectedTemuan->status === 'Overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Unggah Bukti Tindak Lanjut</label>
                            <input type="file" name="bukti_file" class="form-control-file form-control-sm border p-1 rounded">
                            @if($selectedTemuan->bukti_tindak_lanjut)
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-file-alt text-primary mr-1"></i> File saat ini: 
                                    <a href="{{ asset($selectedTemuan->bukti_tindak_lanjut) }}" target="_blank" class="font-weight-bold text-primary">{{ basename($selectedTemuan->bukti_tindak_lanjut) }}</a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Catatan / Keterangan Tindak Lanjut</label>
                            <textarea name="catatan_tindak_lanjut" class="form-control form-control-sm" rows="2" placeholder="Catatan respon auditi atau verifikasi auditor...">{{ $selectedTemuan->catatan_tindak_lanjut }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm rounded-pill px-4 font-weight-bold text-white shadow-sm" style="background: #094b54;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    function openEditModal() {
        $('#modalEditTemuanLpm').modal('show');
    }
</script>
@endsection
