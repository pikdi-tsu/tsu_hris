@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-tasks text-primary mr-2"></i>Persetujuan (Approval) RKAT
                </h1>
                <p class="text-muted small mb-0">Antrean usulan rencana kegiatan dan anggaran yang membutuhkan verifikasi & pengesahan berjenjang.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge badge-warning px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.85rem; border-radius: 20px;">
                    <i class="fas fa-bell mr-1"></i> {{ $pendingApprovals->total() }} Menunggu Tindakan
                </span>
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

        {{-- Filter Box --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.rkat.approval.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Tingkat Jabatan Approval:</label>
                        <select name="level" class="form-control form-control-sm rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Semua Tingkatan --</option>
                            <option value="Kepala Unit" {{ ($filters['level'] ?? '') == 'Kepala Unit' ? 'selected' : '' }}>Kepala Unit Kerja</option>
                            <option value="Bagian Keuangan" {{ ($filters['level'] ?? '') == 'Bagian Keuangan' ? 'selected' : '' }}>Bagian Keuangan</option>
                            <option value="Wakil Rektor" {{ ($filters['level'] ?? '') == 'Wakil Rektor' ? 'selected' : '' }}>Wakil Rektor</option>
                            <option value="Rektor" {{ ($filters['level'] ?? '') == 'Rektor' ? 'selected' : '' }}>Rektor (Final Plafon Besar)</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Cari No Pengajuan / Nama Kegiatan:</label>
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Ketik kata kunci..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-12 text-md-right mt-md-4">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 font-weight-bold w-100">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Pending Approvals Table --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-inbox text-warning mr-2"></i>Antrean Menunggu Persetujuan
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $pendingApprovals->total() }} Pengajuan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 150px;">Nomor Usulan</th>
                                <th>Nama Kegiatan & Unit</th>
                                <th style="width: 150px;">Tingkat Approval</th>
                                <th style="width: 150px;" class="text-right">Nominal Diajukan</th>
                                <th style="width: 130px;" class="text-center">Tgl Masuk</th>
                                <th style="width: 190px;" class="text-center text-nowrap">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingApprovals as $index => $pengajuan)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $pendingApprovals->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">{{ $pengajuan->nomor_pengajuan }}</div>
                                        <small class="text-muted">TA {{ optional($pengajuan->periode)->tahun_anggaran ?? '-' }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary mb-1">
                                            {{ $pengajuan->nama_kegiatan }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-building mr-1"></i>{{ optional($pengajuan->unit)->nama_unit ?? 'Unit Kerja' }} &bull;
                                            <i class="fas fa-user mr-1"></i>{{ optional($pengajuan->pic)->nama ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-warning px-2 py-1 rounded-pill">
                                            <i class="fas fa-user-check mr-1"></i>{{ $pengajuan->current_approval_level ?: 'Pemeriksaan' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-dark">
                                        Rp {{ number_format($pengajuan->total_anggaran_diajukan, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center small text-muted">
                                        {{ $pengajuan->submitted_at ? date('d/m/Y H:i', strtotime($pengajuan->submitted_at)) : '-' }}
                                    </td>
                                    <td class="align-middle text-center text-nowrap">
                                        <a href="{{ route('admin.rkat.approval.show', $pengajuan->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm font-weight-bold text-nowrap" style="height: 31px; display: inline-flex; align-items: center;" title="Review & Putuskan Usulan">
                                            <i class="fas fa-search-dollar mr-1"></i> Review & Putuskan
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3" style="opacity: 0.5;"></i>
                                        <div class="font-weight-bold">Semua Usulan Telah Diproses</div>
                                        <small>Saat ini tidak ada dokumen RKAT yang menunggu tindakan persetujuan Anda.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pendingApprovals->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                    {{ $pendingApprovals->links() }}
                </div>
            @endif
        </div>

        {{-- Processed History Card --}}
        @if($processedHistory->count() > 0)
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="fas fa-history text-secondary mr-2"></i>Riwayat Persetujuan Terakhir yang Anda Proses
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 140px;">Waktu</th>
                                    <th>Nomor & Nama Kegiatan</th>
                                    <th style="width: 140px;">Tindakan</th>
                                    <th style="width: 160px;" class="text-right">Nominal Disetujui</th>
                                    <th>Catatan Rekomendasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedHistory as $h)
                                    <tr>
                                        <td class="small text-muted">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <strong>{{ optional($h->pengajuan)->nomor_pengajuan }}</strong>
                                            <div class="small text-muted">{{ optional($h->pengajuan)->nama_kegiatan }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $h->action == 'Disetujui' ? 'badge-success' : ($h->action == 'Revisi' ? 'badge-warning' : 'badge-danger') }}">
                                                {{ $h->action }}
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">
                                            Rp {{ number_format($h->nominal_disetujui, 0, ',', '.') }}
                                        </td>
                                        <td class="small text-muted">{{ $h->catatan ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</section>
@endsection
