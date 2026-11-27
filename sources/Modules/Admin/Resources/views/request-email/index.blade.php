@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<style>
    .mail-stat-card {
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .mail-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.07);
    }
    .mail-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .badge-bpo-diajukan {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        white-space: nowrap !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .badge-bpo-sdm {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        white-space: nowrap !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .badge-bpo-selesai {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        white-space: nowrap !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .badge-bpo-ditolak {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        white-space: nowrap !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
</style>

<x-tsu-page-header 
    :title="$title" 
    subtitle="Alur BPO: Permintaan Email Masuk &bull; Divalidasi oleh SDM &bull; Akun Diterbitkan oleh PIKDI &bull; Permintaan Selesai"
    icon="fas fa-envelope-open-text"
/>

<section class="content">
    <div class="container-fluid">

        {{-- BPO Workflow Information Banner --}}
        <div class="alert alert-light border shadow-sm mb-4" style="border-radius: 12px; border-left: 5px solid #094b54 !important; background: #f8fafc;">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex align-items-center" style="gap: 14px;">
                    <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #094b54;">
                        <i class="fas fa-project-diagram fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1 text-dark" style="color: #094b54 !important;">
                            Standard Operating Procedure (BPO) Penerbitan Email Resmi (@tsu.ac.id)
                        </h6>
                        <p class="mb-0 small text-muted">
                            <span class="badge badge-light border font-weight-bold mr-1">1. Permintaan Masuk</span> &rarr; 
                            <span class="badge badge-light border font-weight-bold mr-1">2. Validasi & Koreksi Alamat oleh SDM</span> &rarr; 
                            <span class="badge badge-light border font-weight-bold mr-1">3. Pembuatan Akun oleh PIKDI</span> &rarr; 
                            <span class="badge badge-light border font-weight-bold text-success">4. Selesai & Penyerahan Akun</span>
                        </p>
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    <button type="button" class="btn btn-sm text-white font-weight-bold rounded-pill px-3 shadow-sm" style="background: #094b54;" data-toggle="modal" data-target="#modalTambahRequestEmail">
                        <i class="fas fa-plus mr-1"></i> Ajukan Permintaan Email Baru
                    </button>
                </div>
            </div>
        </div>

        {{-- 5 Stat Cards --}}
        <div class="row mb-4">
            <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="mail-stat-card">
                    <div class="mail-stat-icon" style="background: rgba(9, 75, 84, 0.12); color: #094b54;">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Total Permohonan</small>
                        <h4 class="font-weight-bold mb-0 text-dark">{{ $totalRequest }}</h4>
                        <small class="text-muted">Semua Request</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="mail-stat-card">
                    <div class="mail-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Validasi SDM</small>
                        <h4 class="font-weight-bold mb-0 text-warning">{{ $menungguSdm }}</h4>
                        <small class="text-muted">Menunggu Review</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6 mb-3 mb-xl-0">
                <div class="mail-stat-card">
                    <div class="mail-stat-icon" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                        <i class="fas fa-server"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Eksekusi PIKDI</small>
                        <h4 class="font-weight-bold mb-0" style="color: #0284c7;">{{ $menungguPikdi }}</h4>
                        <small class="text-muted">Siap Dibuat Akun</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 col-sm-6 mb-3 mb-xl-0">
                <div class="mail-stat-card">
                    <div class="mail-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Akun Aktif / Selesai</small>
                        <h4 class="font-weight-bold mb-0 text-success">{{ $selesai }}</h4>
                        <small class="text-muted">Telah Diterbitkan</small>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 col-sm-6 mb-3 mb-xl-0">
                <div class="mail-stat-card">
                    <div class="mail-stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #dc2626;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem;">Ditolak / Terkendala</small>
                        <h4 class="font-weight-bold mb-0 text-danger">{{ $ditolak }}</h4>
                        <small class="text-muted">Perlu Revisi</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB NAVIGASI ROLE-BASED WORKFLOW (SDM & PIKDI) --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; overflow: hidden;">
            <div class="card-body p-2 bg-white">
                <ul class="nav nav-pills d-flex flex-wrap" style="gap: 8px;">
                    {{-- TAB 1: VERIFIKASI SDM --}}
                    <li class="nav-item flex-fill">
                        <a class="nav-link text-center {{ $activeTab === 'sdm' ? 'active font-weight-bold shadow-sm' : 'bg-light text-dark' }}" 
                           href="{{ route('admin.request-email.index', ['tab' => 'sdm', 'sdm_filter' => 'pending']) }}"
                           style="{{ $activeTab === 'sdm' ? 'background: #094b54 !important;' : '' }}; border-radius: 10px; padding: 12px 16px;">
                            <i class="fas fa-user-check mr-2 {{ $activeTab === 'sdm' ? 'text-warning' : 'text-primary' }}"></i> 
                            1. Antrean Verifikasi SDM
                            <span class="badge {{ $activeTab === 'sdm' ? 'badge-warning text-dark font-weight-bold' : 'badge-secondary' }} ml-2 px-2 py-1">
                                {{ $menungguSdm }} Perlu Review
                            </span>
                        </a>
                    </li>

                    {{-- TAB 2: EKSEKUSI PIKDI --}}
                    <li class="nav-item flex-fill">
                        <a class="nav-link text-center {{ $activeTab === 'pikdi' ? 'active font-weight-bold shadow-sm' : 'bg-light text-dark' }}" 
                           href="{{ route('admin.request-email.index', ['tab' => 'pikdi', 'pikdi_filter' => 'pending']) }}"
                           style="{{ $activeTab === 'pikdi' ? 'background: #0284c7 !important;' : '' }}; border-radius: 10px; padding: 12px 16px;">
                            <i class="fas fa-server mr-2 {{ $activeTab === 'pikdi' ? 'text-white' : 'text-info' }}"></i> 
                            2. Antrean Eksekusi PIKDI
                            <span class="badge {{ $activeTab === 'pikdi' ? 'badge-light text-primary font-weight-bold' : 'badge-secondary' }} ml-2 px-2 py-1">
                                {{ $menungguPikdi }} Siap Terbit
                            </span>
                        </a>
                    </li>

                    {{-- TAB 3: SEMUA RIWAYAT / ARSIP --}}
                    <li class="nav-item">
                        <a class="nav-link text-center {{ $activeTab === 'semua' ? 'active font-weight-bold shadow-sm' : 'bg-light text-secondary' }}" 
                           href="{{ route('admin.request-email.index', ['tab' => 'semua']) }}"
                           style="{{ $activeTab === 'semua' ? 'background: #475569 !important;' : '' }}; border-radius: 10px; padding: 12px 16px;">
                            <i class="fas fa-archive mr-1"></i> Semua Riwayat ({{ $totalRequest }})
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- BANNER INFORMASI ALUR BPO SESUAI TAB AKTIF --}}
        @if($activeTab === 'sdm')
            <div class="alert border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" style="border-radius: 12px; background: rgba(9, 75, 84, 0.08); border-left: 5px solid #094b54 !important;">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mr-3 shadow-sm" style="width: 40px; height: 40px; background: #094b54;">
                        <i class="fas fa-user-check"></i>
                    </span>
                    <div>
                        <strong class="text-dark">Tahap 1 BPO: Verifikasi & Rekomendasi Format Alamat oleh SDM</strong>
                        <p class="mb-0 text-muted small">
                            Tim SDM memvalidasi identitas kepegawaian pemohon dan menyesuaikan format nama alamat resmi universitas. 
                            <strong>Saat di-ACC:</strong> Data akan langsung keluar dari antrean SDM dan otomatis berpindah ke Tab PIKDI. 
                            <strong>Jika ditolak:</strong> Tiket berhenti di sini dan tidak akan masuk ke PIKDI.
                        </p>
                    </div>
                </div>
                <div class="d-none d-md-block text-right">
                    <span class="badge badge-warning px-3 py-2 text-dark font-weight-bold shadow-sm" style="font-size: 0.85rem;">
                        <i class="fas fa-clock mr-1"></i> {{ $menungguSdm }} Menunggu Keputusan
                    </span>
                </div>
            </div>
        @elseif($activeTab === 'pikdi')
            <div class="alert border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" style="border-radius: 12px; background: rgba(2, 132, 199, 0.08); border-left: 5px solid #0284c7 !important;">
                <div class="d-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mr-3 shadow-sm" style="width: 40px; height: 40px; background: #0284c7;">
                        <i class="fas fa-server"></i>
                    </span>
                    <div>
                        <strong class="text-dark">Tahap 2 BPO: Eksekusi Penerbitan Akun Google Workspace oleh PIKDI</strong>
                        <p class="mb-0 text-muted small">
                            Seluruh data di tab ini telah <strong>sah divalidasi oleh Tim SDM</strong>. Tim PIKDI bertugas mengaktifkan akun resmi di konsol Google Workspace universitas dan menginput password awal sementara untuk pemohon.
                        </p>
                    </div>
                </div>
                <div class="d-none d-md-block text-right">
                    <span class="badge badge-info px-3 py-2 text-white font-weight-bold shadow-sm" style="background: #0284c7; font-size: 0.85rem;">
                        <i class="fas fa-bolt mr-1"></i> {{ $menungguPikdi }} Siap Diaktifkan
                    </span>
                </div>
            </div>
        @endif

        {{-- Filter & Table Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="font-weight-bold mb-0 text-dark">
                            @if($activeTab === 'sdm')
                                <i class="fas fa-user-check mr-2 text-warning"></i> Antrean Verifikasi Alamat Email (Tim SDM)
                            @elseif($activeTab === 'pikdi')
                                <i class="fas fa-server mr-2 text-info"></i> Antrean Penerbitan Akun (Tim PIKDI)
                            @else
                                <i class="fas fa-list-ul mr-2" style="color: #094b54;"></i> Semua Arsip Permohonan Akun Email
                            @endif
                        </h6>
                        <small class="text-muted">Total {{ $requests->total() }} permohonan dalam tampilan ini</small>
                    </div>

                    {{-- SUB-FILTER PILLS SESUAI TAB --}}
                    @if($activeTab === 'sdm')
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.request-email.index', ['tab' => 'sdm', 'sdm_filter' => 'pending', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($sdmFilter ?? 'pending') === 'pending' ? 'btn-warning font-weight-bold text-dark' : 'btn-outline-secondary' }}">
                                <i class="fas fa-clock mr-1"></i> Perlu Verifikasi ({{ $menungguSdm }})
                            </a>
                            <a href="{{ route('admin.request-email.index', ['tab' => 'sdm', 'sdm_filter' => 'ditolak', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($sdmFilter ?? '') === 'ditolak' ? 'btn-danger font-weight-bold text-white' : 'btn-outline-secondary' }}">
                                <i class="fas fa-times-circle mr-1"></i> Ditolak SDM ({{ $ditolakSdm }})
                            </a>
                            <a href="{{ route('admin.request-email.index', ['tab' => 'sdm', 'sdm_filter' => 'all', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($sdmFilter ?? '') === 'all' ? 'btn-dark font-weight-bold' : 'btn-outline-secondary' }}">
                                Semua Riwayat SDM
                            </a>
                        </div>
                    @elseif($activeTab === 'pikdi')
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.request-email.index', ['tab' => 'pikdi', 'pikdi_filter' => 'pending', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($pikdiFilter ?? 'pending') === 'pending' ? 'btn-primary font-weight-bold text-white' : 'btn-outline-secondary' }}" style="{{ ($pikdiFilter ?? 'pending') === 'pending' ? 'background: #0284c7; border-color: #0284c7;' : '' }}">
                                <i class="fas fa-cogs mr-1"></i> Siap Dibuat ({{ $menungguPikdi }})
                            </a>
                            <a href="{{ route('admin.request-email.index', ['tab' => 'pikdi', 'pikdi_filter' => 'selesai', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($pikdiFilter ?? '') === 'selesai' ? 'btn-success font-weight-bold text-white' : 'btn-outline-secondary' }}">
                                <i class="fas fa-check-circle mr-1"></i> Telah Terbit ({{ $selesai }})
                            </a>
                            <a href="{{ route('admin.request-email.index', ['tab' => 'pikdi', 'pikdi_filter' => 'kendala', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($pikdiFilter ?? '') === 'kendala' ? 'btn-danger font-weight-bold text-white' : 'btn-outline-secondary' }}">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Kendala ({{ $ditolakPikdi }})
                            </a>
                            <a href="{{ route('admin.request-email.index', ['tab' => 'pikdi', 'pikdi_filter' => 'all', 'search' => $filters['search'] ?? '']) }}" 
                               class="btn {{ ($pikdiFilter ?? '') === 'all' ? 'btn-dark font-weight-bold' : 'btn-outline-secondary' }}">
                                Semua Riwayat PIKDI
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Filters Search & Tipe --}}
                <form action="{{ route('admin.request-email.index') }}" method="GET" class="row">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    @if($activeTab === 'sdm')
                        <input type="hidden" name="sdm_filter" value="{{ $sdmFilter }}">
                    @elseif($activeTab === 'pikdi')
                        <input type="hidden" name="pikdi_filter" value="{{ $pikdiFilter }}">
                    @endif

                    <div class="col-md-5 mb-2 mb-md-0">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control rounded-left" placeholder="Cari no tiket, nama pemohon, usulan email, unit..." value="{{ $filters['search'] ?? '' }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary font-weight-bold" style="background: #094b54; border-color: #094b54;">
                                    <i class="fas fa-search mr-1"></i> Cari
                                </button>
                            </div>
                        </div>
                    </div>
                    @if($activeTab === 'semua')
                        <div class="col-md-3 col-6">
                            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="all" {{ ($filters['status'] ?? '') == 'all' ? 'selected' : '' }}>-- Semua Status --</option>
                                <option value="Diajukan" {{ ($filters['status'] ?? '') == 'Diajukan' ? 'selected' : '' }}>Diajukan (Menunggu SDM)</option>
                                <option value="Divalidasi SDM" {{ ($filters['status'] ?? '') == 'Divalidasi SDM' ? 'selected' : '' }}>Divalidasi SDM (Menunggu PIKDI)</option>
                                <option value="Selesai" {{ ($filters['status'] ?? '') == 'Selesai' ? 'selected' : '' }}>Selesai (Akun Aktif)</option>
                                <option value="Ditolak SDM" {{ ($filters['status'] ?? '') == 'Ditolak SDM' ? 'selected' : '' }}>Ditolak SDM</option>
                                <option value="Ditolak PIKDI" {{ ($filters['status'] ?? '') == 'Ditolak PIKDI' ? 'selected' : '' }}>Ditolak PIKDI</option>
                            </select>
                        </div>
                    @else
                        <div class="col-md-3 col-6"></div>
                    @endif
                    <div class="col-md-3 col-6">
                        <select name="tipe" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="all" {{ ($filters['tipe'] ?? '') == 'all' ? 'selected' : '' }}>-- Semua Tipe Pemohon --</option>
                            <option value="Dosen" {{ ($filters['tipe'] ?? '') == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="Tendik" {{ ($filters['tipe'] ?? '') == 'Tendik' ? 'selected' : '' }}>Tendik</option>
                            <option value="Unit Kerja" {{ ($filters['tipe'] ?? '') == 'Unit Kerja' ? 'selected' : '' }}>Unit Kerja</option>
                            <option value="Organisasi / Kepanitiaan" {{ ($filters['tipe'] ?? '') == 'Organisasi / Kepanitiaan' ? 'selected' : '' }}>Organisasi / Kepanitiaan</option>
                        </select>
                    </div>
                    <div class="col-md-1 text-right">
                        @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['tipe']))
                            <a href="{{ route('admin.request-email.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-outline-secondary btn-block" title="Reset Filter">
                                <i class="fas fa-undo"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <tr>
                                <th style="width: 140px;">No. Tiket</th>
                                <th style="min-width: 180px;">Pemohon & Unit</th>
                                <th style="width: 120px;">Tipe</th>
                                <th style="min-width: 170px;">Usulan Pemohon</th>
                                <th style="min-width: 180px;">Rekomendasi SDM</th>
                                <th style="min-width: 180px;">Akun Final PIKDI</th>
                                <th style="min-width: 140px; width: 150px;" class="text-center text-nowrap">Status</th>
                                <th style="width: 130px;" class="text-center text-nowrap">Aksi BPO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $r)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $r->nomor_tiket }}</div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ $r->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $r->nama_pemohon }}</div>
                                        <small class="text-muted">{{ $r->unit_nama }}</small>
                                        @if($r->kontak_hp)
                                            <div>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $r->kontak_hp) }}" target="_blank" class="badge badge-light border text-success">
                                                    <i class="fab fa-whatsapp mr-1"></i>{{ $r->kontak_hp }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary px-2 py-1">{{ $r->tipe_pemohon }}</span>
                                        <div class="small text-muted mt-1" style="line-height: 1.2;">{{ Str::limit($r->keperluan, 40) }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-semibold text-dark">{{ $r->email_usulan }}</div>
                                        @if($r->email_alternatif)
                                            <small class="text-muted font-italic">Alt: {{ $r->email_alternatif }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($r->email_rekomendasi_sdm)
                                            <div class="font-weight-bold text-primary">
                                                <i class="fas fa-check-circle text-info mr-1"></i>{{ $r->email_rekomendasi_sdm }}
                                            </div>
                                            @if($r->email_rekomendasi_sdm !== $r->email_usulan)
                                                <small class="badge badge-warning px-1 py-0" style="font-size: 0.65rem;">Alamat Disesuaikan SDM</small>
                                            @endif
                                        @else
                                            <span class="text-muted font-italic">Menunggu Validasi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($r->email_final_dibuat)
                                            <div class="font-weight-bold text-success">
                                                <i class="fas fa-certificate text-success mr-1"></i>{{ $r->email_final_dibuat }}
                                            </div>
                                            <small class="text-muted">Password: <code>{{ $r->password_sementara ?? 'Tersedia' }}</code></small>
                                        @else
                                            <span class="text-muted font-italic">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap align-middle">
                                        {!! $r->status_badge !!}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Tombol Detail --}}
                                            <button type="button" class="btn btn-outline-info" title="Lihat Riwayat & Detail" onclick="showDetailModal({{ json_encode($r) }})">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            {{-- Tombol Step 2: Validasi SDM (bisa edit email) --}}
                                            @if($r->status === 'Diajukan')
                                                <button type="button" class="btn btn-outline-warning" title="Validasi & Edit Alamat oleh SDM" onclick="openValidasiSdmModal({{ json_encode($r) }})">
                                                    <i class="fas fa-user-edit"></i>
                                                </button>
                                            @endif

                                            {{-- Tombol Step 3: Eksekusi PIKDI (buat akun) --}}
                                            @if(in_array($r->status, ['Divalidasi SDM', 'Diproses PIKDI']))
                                                <button type="button" class="btn btn-outline-primary" title="Proses Pembuatan Akun oleh PIKDI" onclick="openProcessPikdiModal({{ json_encode($r) }})">
                                                    <i class="fas fa-cogs"></i>
                                                </button>
                                            @endif

                                            {{-- Tombol WhatsApp Cepat (Kirim Kredensial jika Selesai / Hubungi Pemohon) --}}
                                            @if($r->kontak_hp)
                                                @php
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $r->kontak_hp);
                                                    if (str_starts_with($cleanPhone, '0')) {
                                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                                    }
                                                    if ($r->status === 'Selesai') {
                                                        $waText = urlencode("Halo Bapak/Ibu {$r->nama_pemohon},\n\nAkun email resmi Universitas Tiga Serangkai Anda telah aktif:\n• Email Resmi: {$r->email_final_dibuat}\n• Password Sementara: " . ($r->password_sementara ?? 'TigaSerangkai#' . date('Y')) . "\n• Login Portal: https://mail.google.com\n\nSilakan segera login dan lakukan pergantian password saat pertama login. Terima kasih.\n- Tim PIKDI & SDM TSU");
                                                        $waTitle = "Kirim Kredensial Akun via WhatsApp";
                                                        $waBtnClass = "btn-success text-white font-weight-bold";
                                                    } else {
                                                        $waText = urlencode("Halo Bapak/Ibu {$r->nama_pemohon},\n\nKonfirmasi permohonan akun email resmi [{$r->nomor_tiket}] ({$r->email_usulan}) dengan status terkini: {$r->status}.\n- Tim Administrasi HRIS TSU");
                                                        $waTitle = "Hubungi Pemohon via WhatsApp";
                                                        $waBtnClass = "btn-outline-success";
                                                    }
                                                @endphp
                                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="btn {{ $waBtnClass }}" title="{{ $waTitle }}">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            @endif

                                            {{-- Tombol Resend Notifikasi Sistem --}}
                                            <form action="{{ route('admin.request-email.resend-notif', $r->id) }}" method="POST" class="d-inline" title="Kirim Ulang Notifikasi Sistem">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary" onclick="return confirm('Kirim ulang notifikasi sistem untuk tiket {{ $r->nomor_tiket }}?');">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                            </form>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('admin.request-email.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan email ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-envelope-open fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                        <p class="mb-0">Belum ada permohonan akun email resmi.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($requests->hasPages())
                <div class="card-footer bg-white border-top py-2">
                    <div class="d-flex justify-content-end">
                        {{ $requests->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</section>

{{-- MODAL 1: Tambah Request Email Baru (Pemohon / Admin Input) --}}
<div class="modal fade" id="modalTambahRequestEmail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-envelope-open-text text-primary mr-2" style="color: #094b54 !important;"></i>Ajukan Permintaan Akun Email Resmi (@tsu.ac.id)
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.request-email.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-3 small" style="border-radius: 8px;">
                        <i class="fas fa-info-circle mr-1"></i> Permintaan yang dikirimkan akan divalidasi oleh tim <strong>SDM</strong> untuk kesesuaian identitas dan format penamaan, kemudian diterbitkan oleh <strong>PIKDI</strong>.
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Tipe Pemohon <span class="text-danger">*</span></label>
                            <select name="tipe_pemohon" id="add_tipe_pemohon" class="form-control form-control-sm" required onchange="togglePegawaiSelect()">
                                <option value="Dosen">Dosen</option>
                                <option value="Tendik" selected>Tendik</option>
                                <option value="Unit Kerja">Unit Kerja / Biro / Bagian</option>
                                <option value="Organisasi / Kepanitiaan">Organisasi Mahasiswa / Kepanitiaan</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group mb-3" id="group_pegawai_select">
                            <label class="font-weight-bold small text-dark">Pilih Pegawai (Opsional)</label>
                            <select name="pegawai_id" id="add_pegawai_id" class="form-control form-control-sm" onchange="autoFillPegawai()">
                                <option value="">-- Pilih dari Master Pegawai --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->id }}" data-nama="{{ $p->nama }}" data-nip="{{ $p->nip ?? $p->nik }}" data-unit="{{ $p->unit_id }}" data-unitnama="{{ $p->unit->nama_unit ?? '' }}" data-jabatan="{{ $p->tipe_karyawan }}" data-hp="{{ $p->no_hp }}">
                                        {{ $p->nama }} ({{ $p->nip ?? $p->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Nama Pemohon / Akun <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pemohon" id="add_nama_pemohon" class="form-control form-control-sm" placeholder="Nama lengkap atau nama kepanitiaan" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">NIP / NIK / Kode Unit</label>
                            <input type="text" name="nik_nip" id="add_nik_nip" class="form-control form-control-sm" placeholder="Contoh: 19890101...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Unit Kerja / Bagian <span class="text-danger">*</span></label>
                            <input type="text" name="unit_nama" id="add_unit_nama" class="form-control form-control-sm" placeholder="Contoh: Biro Sistem Informasi, LPPM, dll" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Jabatan / Posisi</label>
                            <input type="text" name="jabatan" id="add_jabatan" class="form-control form-control-sm" placeholder="Contoh: Staf Pengajar, Anggota Tim">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Usulan Alamat Email (@tsu.ac.id) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="email_usulan" id="add_email_usulan" class="form-control" placeholder="Contoh: budi atau budi.santoso" required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold bg-light">@tsu.ac.id</span>
                                </div>
                            </div>
                            <small class="text-muted">Gunakan huruf kecil tanpa spasi.</small>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">No. WhatsApp / HP Pemohon <span class="text-danger">*</span></label>
                            <input type="text" name="kontak_hp" id="add_kontak_hp" class="form-control form-control-sm" placeholder="08xxxxxxxxxx (untuk pengiriman password)" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Keperluan Pembuatan Email <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" class="form-control form-control-sm" placeholder="Contoh: Korespondensi dinas, submit jurnal ilmiah, pendaftaran hibah, dll" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Email Alternatif / Pribadi (Opsional)</label>
                            <input type="email" name="email_alternatif" class="form-control form-control-sm" placeholder="email@gmail.com">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-dark">Catatan Tambahan</label>
                            <input type="text" name="catatan_pemohon" class="form-control form-control-sm" placeholder="Keterangan khusus jika ada">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm rounded-pill px-4 font-weight-bold text-white shadow-sm" style="background: #094b54;">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: Validasi oleh SDM (SDM Bisa Edit / Koreksi Alamat Rekomendasi) --}}
<div class="modal fade" id="modalValidasiSdm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-user-check text-warning mr-2"></i>Validasi & Koreksi Alamat (SDM)
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formValidasiSdm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 bg-light rounded border">
                        <div class="small text-muted mb-1 font-weight-bold text-uppercase">Data Pemohon:</div>
                        <div class="font-weight-bold text-dark" id="val_sdm_nama">-</div>
                        <div class="small text-muted" id="val_sdm_unit">-</div>
                        <div class="small mt-2">
                            <span class="text-muted">Usulan Alamat Awal:</span>
                            <span class="font-weight-bold text-primary" id="val_sdm_usulan">-</span>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">
                            <i class="fas fa-pencil-alt text-primary mr-1"></i> Alamat Email Rekomendasi SDM (@tsu.ac.id) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="email_rekomendasi_sdm" id="val_sdm_input_email" class="form-control form-control-sm font-weight-bold" required>
                        <small class="text-info d-block mt-1">
                            <i class="fas fa-info-circle mr-1"></i> <strong>Fitur Revisi SDM:</strong> Tim SDM dapat mengedit dan menyesuaikan format nama alamat resmi sesuai ketentuan universitas (misal: <code>nama.belakang@tsu.ac.id</code>).
                        </small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Tindakan Validasi <span class="text-danger">*</span></label>
                        <div class="d-flex" style="gap: 15px;">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="action_approve" name="action_type" value="approve" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-bold text-success" for="action_approve">Setujui & Teruskan ke PIKDI</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="action_reject" name="action_type" value="reject" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold text-danger" for="action_reject">Tolak Permohonan</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Catatan Verifikasi SDM</label>
                        <textarea name="catatan_sdm" class="form-control form-control-sm" rows="3" placeholder="Tuliskan catatan verifikasi identitas atau alasan penyesuaian alamat email..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm rounded-pill px-4 font-weight-bold text-white shadow-sm" style="background: #094b54;">
                        <i class="fas fa-check mr-1"></i> Simpan Validasi SDM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 3: Proses Pembuatan Akun oleh PIKDI (Req Selesai) --}}
<div class="modal fade" id="modalProcessPikdi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-server text-primary mr-2" style="color: #0284c7 !important;"></i>Penerbitan Akun oleh PIKDI
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formProcessPikdi" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 bg-light rounded border">
                        <div class="small text-muted mb-1 font-weight-bold text-uppercase">Rekomendasi dari SDM:</div>
                        <div class="font-weight-bold text-dark" id="pikdi_nama">-</div>
                        <div class="small mt-1 text-primary font-weight-bold" id="pikdi_rekomendasi_sdm">-</div>
                        <div class="small text-muted mt-1" id="pikdi_catatan_sdm">-</div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Alamat Email Final yang Diterbitkan <span class="text-danger">*</span></label>
                        <input type="text" name="email_final_dibuat" id="pikdi_input_email" class="form-control form-control-sm font-weight-bold" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Password Awal / Sementara</label>
                        <input type="text" name="password_sementara" class="form-control form-control-sm" value="TigaSerangkai#2026">
                        <small class="text-muted">Password sementara yang wajib diganti pemohon saat pertama login.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Status Penerbitan <span class="text-danger">*</span></label>
                        <div class="d-flex" style="gap: 15px;">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="pikdi_complete" name="action_type" value="complete" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-bold text-success" for="pikdi_complete">Akun Selesai Dibuat (Aktif)</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="pikdi_reject" name="action_type" value="reject" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold text-danger" for="pikdi_reject">Kendala Teknis / Batal</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-dark">Catatan Konfirmasi PIKDI</label>
                        <textarea name="catatan_pikdi" class="form-control form-control-sm" rows="3" placeholder="Informasi kuota penyimpanan, aktivasi Google Workspace, tautan panduan, dll..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm rounded-pill px-4 font-weight-bold text-white shadow-sm" style="background: #0284c7;">
                        <i class="fas fa-check-double mr-1"></i> Selesaikan & Terbitkan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 4: Detail Permohonan & Timeline Status BPO --}}
<div class="modal fade" id="modalDetailRequest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 14px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title font-weight-bold text-dark">
                    <i class="fas fa-ticket-alt text-primary mr-2" style="color: #094b54 !important;"></i>Detail Permohonan <span id="dtl_tiket"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-muted" style="width: 140px;">Nama Pemohon</td>
                                <td class="font-weight-bold text-dark" id="dtl_nama">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">NIP / NIK</td>
                                <td class="font-weight-bold text-dark" id="dtl_nip">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Unit Kerja</td>
                                <td class="font-weight-bold text-dark" id="dtl_unit">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. WhatsApp</td>
                                <td class="text-dark" id="dtl_hp">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 border-left">
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-muted" style="width: 140px;">Usulan Pemohon</td>
                                <td class="font-weight-bold text-dark" id="dtl_usulan">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Rekomendasi SDM</td>
                                <td class="font-weight-bold text-primary" id="dtl_rekomendasi">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Akun Dibuat PIKDI</td>
                                <td class="font-weight-bold text-success" id="dtl_final">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Terkini</td>
                                <td id="dtl_status">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.72rem;">Keperluan Pengajuan</label>
                    <p class="small text-dark p-2 rounded bg-light border mb-0" id="dtl_keperluan">-</p>
                </div>

                {{-- Saluran Notifikasi & WhatsApp Gateway --}}
                <div class="p-3 mb-3 rounded border" style="background: #f8fafc;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small font-weight-bold text-dark text-uppercase">
                            <i class="fas fa-satellite-dish text-primary mr-1"></i> Status Saluran Notifikasi & Komunikasi
                        </span>
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Realtime Active</span>
                    </div>
                    <p class="small text-muted mb-2" style="font-size: 0.8rem;">
                        Notifikasi sistem (Database In-App + Broadcast WebSocket) dikirimkan otomatis ke <strong>Tim SDM</strong>, <strong>Tim PIKDI</strong>, dan <strong>Pemohon</strong> di setiap tahapan BPO.
                    </p>
                    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;" id="dtl_action_communication">
                        {{-- Injected dynamically --}}
                    </div>
                </div>

                {{-- Vertical Timeline Riwayat Status --}}
                <div class="mt-4">
                    <label class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 0.72rem;">
                        <i class="fas fa-history mr-1"></i> Timeline Perjalanan Status (BPO Flow)
                    </label>
                    <div class="p-3 rounded bg-light border" id="dtl_timeline_container">
                        {{-- Injected dynamically --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePegawaiSelect() {
        var tipe = $('#add_tipe_pemohon').val();
        if (tipe === 'Dosen' || tipe === 'Tendik') {
            $('#group_pegawai_select').show();
        } else {
            $('#group_pegawai_select').hide();
            $('#add_pegawai_id').val('');
        }
    }

    function autoFillPegawai() {
        var opt = $('#add_pegawai_id option:selected');
        if (opt.val()) {
            $('#add_nama_pemohon').val(opt.data('nama'));
            $('#add_nik_nip').val(opt.data('nip'));
            $('#add_unit_nama').val(opt.data('unitnama'));
            $('#add_jabatan').val(opt.data('jabatan'));
            if (opt.data('hp')) {
                $('#add_kontak_hp').val(opt.data('hp'));
            }
            var nameFirst = opt.data('nama').toLowerCase().replace(/[^a-z]/g, ' ').trim().split(' ')[0];
            if (nameFirst && !$('#add_email_usulan').val()) {
                $('#add_email_usulan').val(nameFirst);
            }
        }
    }

    function openValidasiSdmModal(data) {
        var url = "{{ route('admin.request-email.validate-sdm', ':id') }}".replace(':id', data.id);
        $('#formValidasiSdm').attr('action', url);
        $('#val_sdm_nama').text(data.nama_pemohon);
        $('#val_sdm_unit').text(data.unit_nama + ' (' + data.tipe_pemohon + ')');
        $('#val_sdm_usulan').text(data.email_usulan);
        $('#val_sdm_input_email').val(data.email_rekomendasi_sdm || data.email_usulan);
        $('#modalValidasiSdm').modal('show');
    }

    function openProcessPikdiModal(data) {
        var url = "{{ route('admin.request-email.process-pikdi', ':id') }}".replace(':id', data.id);
        $('#formProcessPikdi').attr('action', url);
        $('#pikdi_nama').text(data.nama_pemohon + ' (' + data.unit_nama + ')');
        $('#pikdi_rekomendasi_sdm').text('Rekomendasi SDM: ' + (data.email_rekomendasi_sdm || data.email_usulan));
        $('#pikdi_catatan_sdm').text(data.catatan_sdm ? 'Catatan SDM: ' + data.catatan_sdm : 'Tidak ada catatan khusus dari SDM.');
        $('#pikdi_input_email').val(data.email_rekomendasi_sdm || data.email_usulan);
        $('#modalProcessPikdi').modal('show');
    }

    function showDetailModal(data) {
        $('#dtl_tiket').text(data.nomor_tiket);
        $('#dtl_nama').text(data.nama_pemohon);
        $('#dtl_nip').text(data.nik_nip || '-');
        $('#dtl_unit').text(data.unit_nama + ' (' + data.tipe_pemohon + ')');
        $('#dtl_hp').text(data.kontak_hp || '-');
        $('#dtl_usulan').text(data.email_usulan);
        $('#dtl_rekomendasi').text(data.email_rekomendasi_sdm || 'Belum divalidasi');
        $('#dtl_final').text(data.email_final_dibuat || 'Belum diterbitkan');
        $('#dtl_status').html(data.status_badge || ('<span class="badge badge-info px-2 py-1 text-nowrap" style="white-space: nowrap;">' + data.status + '</span>'));
        $('#dtl_keperluan').text(data.keperluan);

        // Setup WhatsApp and Resend Notification buttons
        var phoneClean = (data.kontak_hp || '').replace(/[^0-9]/g, '');
        if (phoneClean.startsWith('0')) {
            phoneClean = '62' + phoneClean.substring(1);
        }

        var commHtml = '';
        if (phoneClean) {
            var waMsg = '';
            if (data.status === 'Selesai') {
                waMsg = encodeURIComponent("Halo Bapak/Ibu " + data.nama_pemohon + ",\n\nAkun email resmi Universitas Tiga Serangkai Anda telah aktif:\n• Email Resmi: " + (data.email_final_dibuat || data.email_usulan) + "\n• Password Sementara: " + (data.password_sementara || 'TigaSerangkai#' + new Date().getFullYear()) + "\n• Login Portal: https://mail.google.com\n\nSilakan segera login dan lakukan pergantian password saat pertama login. Terima kasih.\n- Tim PIKDI & SDM TSU");
                commHtml += '<a href="https://wa.me/' + phoneClean + '?text=' + waMsg + '" target="_blank" class="btn btn-sm btn-success rounded-pill font-weight-bold px-3 shadow-sm">' +
                    '<i class="fab fa-whatsapp mr-1"></i> Kirim Kredensial via WhatsApp' +
                '</a>';
            } else {
                waMsg = encodeURIComponent("Halo Bapak/Ibu " + data.nama_pemohon + ",\n\nKonfirmasi pengajuan akun email resmi [" + data.nomor_tiket + "] saat ini berstatus: " + data.status + ".\n- Tim Administrasi HRIS TSU");
                commHtml += '<a href="https://wa.me/' + phoneClean + '?text=' + waMsg + '" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">' +
                    '<i class="fab fa-whatsapp mr-1"></i> Hubungi WhatsApp Pemohon' +
                '</a>';
            }
        }

        var resendUrl = "{{ route('admin.request-email.resend-notif', ':id') }}".replace(':id', data.id);
        commHtml += '<form action="' + resendUrl + '" method="POST" class="d-inline">' +
            '@csrf' +
            '<button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold" onclick="return confirm(\'Kirim ulang notifikasi sistem untuk tiket ' + data.nomor_tiket + '?\');">' +
                '<i class="fas fa-bell mr-1"></i> Kirim Ulang Notifikasi Sistem' +
            '</button>' +
        '</form>';

        $('#dtl_action_communication').html(commHtml);

        var timelineHtml = '<ul class="list-unstyled mb-0 small">';
        if (data.riwayat_status && data.riwayat_status.length > 0) {
            data.riwayat_status.forEach(function(rs) {
                timelineHtml += '<li class="mb-2 d-flex align-items-start">' +
                    '<span class="timeline-dot bg-success mt-1"></span>' +
                    '<div><strong class="text-dark mr-2">' + rs.tanggal + '</strong>' +
                    '<span class="text-muted">' + rs.status + '</span></div>' +
                '</li>';
            });
        } else {
            timelineHtml += '<li class="text-muted font-italic">Belum ada riwayat aktivitas.</li>';
        }
        timelineHtml += '</ul>';

        $('#dtl_timeline_container').html(timelineHtml);
        $('#modalDetailRequest').modal('show');
    }
</script>
@endsection
