@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Pengajuan RKAT
                </h1>
                <p class="text-muted small mb-0">Daftar usulan kegiatan dan rencana anggaran kerja tahunan unit/fakultas/lembaga.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.rkat.pengajuan.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm font-weight-bold">
                    <i class="fas fa-plus mr-1"></i> Buat Pengajuan Baru
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Metric Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold" style="font-size: 0.75rem;">Total Usulan</small>
                                <h3 class="font-weight-bold mb-0 mt-1">{{ $stats['total_pengajuan'] }}</h3>
                                <small class="text-white-50">Pengajuan RKAT</small>
                            </div>
                            <div class="bg-white rounded-circle p-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; opacity: 0.9;">
                                <i class="fas fa-folder-open fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%); color: white;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold" style="font-size: 0.75rem;">Total Diajukan</small>
                                <h4 class="font-weight-bold mb-0 mt-1">Rp {{ number_format($stats['total_nominal_diajukan'], 0, ',', '.') }}</h4>
                                <small class="text-white-50">{{ $stats['count_diajukan'] + $stats['count_review'] }} Menunggu Approval</small>
                            </div>
                            <div class="bg-white rounded-circle p-3 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; opacity: 0.9;">
                                <i class="fas fa-hand-holding-usd fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold" style="font-size: 0.75rem;">Total Disetujui</small>
                                <h4 class="font-weight-bold mb-0 mt-1">Rp {{ number_format($stats['total_nominal_disetujui'], 0, ',', '.') }}</h4>
                                <small class="text-white-50">{{ $stats['count_disetujui'] }} Usulan Disetujui</small>
                            </div>
                            <div class="bg-white rounded-circle p-3 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; opacity: 0.9;">
                                <i class="fas fa-check-double fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.75rem;">Status Review</small>
                                <div class="d-flex align-items-center mt-1">
                                    <span class="badge badge-warning mr-1">{{ $stats['count_revisi'] }} Revisi</span>
                                    <span class="badge badge-danger mr-1">{{ $stats['count_ditolak'] }} Ditolak</span>
                                    <span class="badge badge-secondary">{{ $stats['count_draft'] }} Draft</span>
                                </div>
                                <small class="text-muted">Perlu perhatian</small>
                            </div>
                            <div class="bg-white rounded-circle p-3 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Box --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.rkat.pengajuan.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Tahun Anggaran:</label>
                        <select name="periode_id" class="form-control form-control-sm rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Semua Periode --</option>
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ ($filters['periode_id'] ?? '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->tahun_anggaran }} ({{ $p->nama_periode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Unit / Fakultas:</label>
                        <select name="unit_id" class="form-control form-control-sm rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Semua Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ ($filters['unit_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Status:</label>
                        <select name="status" class="form-control form-control-sm rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            @foreach(['Draft', 'Diajukan', 'Review', 'Disetujui', 'Revisi', 'Ditolak'] as $st)
                                <option value="{{ $st }}" {{ ($filters['status'] ?? '') == $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Pencarian Kegiatan:</label>
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="No. Pengajuan / Kegiatan..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-1 col-sm-12 text-md-right mt-md-4">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-list-alt text-primary mr-2"></i>Daftar Rencana Kegiatan Anggaran
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $pengajuans->total() }} Data</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 150px;">Nomor Pengajuan</th>
                                <th>Nama Kegiatan & Unit</th>
                                <th style="width: 140px;">Program</th>
                                <th style="width: 150px;" class="text-right">Nominal Diajukan</th>
                                <th style="width: 150px;" class="text-right">Disetujui</th>
                                <th style="width: 120px;" class="text-center">Status</th>
                                <th style="width: 120px;" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $pengajuans->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">{{ $pengajuan->nomor_pengajuan }}</div>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt mr-1"></i>{{ optional($pengajuan->periode)->tahun_anggaran ?? '-' }}
                                        </small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary mb-1">
                                            <a href="{{ route('admin.rkat.pengajuan.show', $pengajuan->id) }}" class="text-primary text-decoration-none">
                                                {{ $pengajuan->nama_kegiatan }}
                                            </a>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-building mr-1"></i>{{ optional($pengajuan->unit)->nama_unit ?? 'Unit Umum' }} &bull;
                                            <i class="fas fa-user mr-1"></i>PIC: {{ optional($pengajuan->pic)->nama ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($pengajuan->program)
                                            <span class="badge badge-light border text-dark font-weight-normal px-2 py-1">
                                                <i class="fas fa-tag text-info mr-1"></i>{{ $pengajuan->program->kode_program }}
                                            </span>
                                            <div class="small text-muted text-truncate" style="max-width: 140px;">
                                                {{ $pengajuan->program->nama_program }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-dark">
                                        Rp {{ number_format($pengajuan->total_anggaran_diajukan, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-success">
                                        @if($pengajuan->status == 'Disetujui')
                                            Rp {{ number_format($pengajuan->total_anggaran_disetujui, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted font-weight-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($pengajuan->status == 'Disetujui')
                                            <span class="badge badge-success px-2 py-1 rounded-pill"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                                        @elseif($pengajuan->status == 'Diajukan')
                                            <span class="badge badge-primary px-2 py-1 rounded-pill"><i class="fas fa-paper-plane mr-1"></i>Diajukan</span>
                                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">{{ $pengajuan->current_approval_level }}</div>
                                        @elseif($pengajuan->status == 'Review')
                                            <span class="badge badge-info px-2 py-1 rounded-pill"><i class="fas fa-search mr-1"></i>Review</span>
                                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">{{ $pengajuan->current_approval_level }}</div>
                                        @elseif($pengajuan->status == 'Revisi')
                                            <span class="badge badge-warning px-2 py-1 rounded-pill"><i class="fas fa-undo mr-1"></i>Revisi</span>
                                        @elseif($pengajuan->status == 'Ditolak')
                                            <span class="badge badge-danger px-2 py-1 rounded-pill"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1 rounded-pill"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center text-nowrap">
                                        <div class="d-inline-flex align-items-center justify-content-center">
                                            <a href="{{ route('admin.rkat.pengajuan.show', $pengajuan->id) }}" class="btn btn-sm btn-outline-info rounded-circle mr-1" style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Detail Pengajuan">
                                                <i class="fas fa-eye" style="font-size: 0.8rem;"></i>
                                            </a>
                                            @if(in_array($pengajuan->status, ['Draft', 'Revisi']))
                                                <a href="{{ route('admin.rkat.pengajuan.edit', $pengajuan->id) }}" class="btn btn-sm btn-outline-warning rounded-circle mr-1" style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Edit / Revisi Usulan RKAT">
                                                    <i class="fas fa-edit" style="font-size: 0.8rem;"></i>
                                                </a>
                                                <form action="{{ route('admin.rkat.pengajuan.submit', $pengajuan->id) }}" method="POST" class="d-inline mb-0 mr-1" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan usulan RKAT ini untuk proses approval?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-circle" style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Ajukan Usulan RKAT">
                                                        <i class="fas fa-paper-plane" style="font-size: 0.8rem;"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.rkat.pengajuan.destroy', $pengajuan->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan draft ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Hapus Pengajuan">
                                                        <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold">Belum ada pengajuan RKAT ditemukan</div>
                                        <small>Klik tombol "Buat Pengajuan Baru" untuk memulai usulan rencana kegiatan.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pengajuans->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                    {{ $pengajuans->links() }}
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
