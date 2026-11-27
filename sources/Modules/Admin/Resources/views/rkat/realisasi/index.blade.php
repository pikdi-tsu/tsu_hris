@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-receipt text-success mr-2"></i>Realisasi Anggaran RKAT
                </h1>
                <p class="text-muted small mb-0">Pencatatan pengeluaran riil, penyerapan belanja per kegiatan, dan pengunggahan berkas bukti SPJ/kwitansi.</p>
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

        {{-- Metric Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #007bff !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Total Pagu Disetujui</small>
                        <h4 class="font-weight-bold text-dark mb-0 mt-1">Rp {{ number_format($stats['total_anggaran'], 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $stats['total_kegiatan'] }} Kegiatan Aktif Disetujui</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #28a745 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Total Realisasi (SPJ)</small>
                        <h4 class="font-weight-bold text-success mb-0 mt-1">Rp {{ number_format($stats['total_realisasi'], 0, ',', '.') }}</h4>
                        <small class="text-muted">Terserap Riil</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #ffc107 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Sisa Anggaran Belum Terserap</small>
                        <h4 class="font-weight-bold text-warning mb-0 mt-1">Rp {{ number_format($stats['sisa_anggaran'], 0, ',', '.') }}</h4>
                        <small class="text-muted">Saldo Tersedia</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #17a2b8 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Rata-rata Persentase Serapan</small>
                        <h4 class="font-weight-bold text-info mb-0 mt-1">{{ $stats['persen_serapan'] }}%</h4>
                        <div class="progress mt-1" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $stats['persen_serapan']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Box --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.rkat.realisasi.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
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
                    <div class="col-md-6 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Pencarian Kegiatan:</label>
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Cari nomor pengajuan atau nama kegiatan..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-12 text-md-right mt-md-4">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 font-weight-bold w-100">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table Approved Activities --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-list-check text-success mr-2"></i>Daftar Kegiatan Siap Realisasi
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $kegiatans->total() }} Kegiatan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 150px;">Nomor Pengajuan</th>
                                <th>Nama Kegiatan & Unit</th>
                                <th style="width: 150px;" class="text-right">Pagu Disetujui</th>
                                <th style="width: 150px;" class="text-right">Realisasi (SPJ)</th>
                                <th style="width: 140px;" class="text-right">Sisa Pagu</th>
                                <th style="width: 130px;" class="text-center">Serapan (%)</th>
                                <th style="width: 130px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kegiatans as $index => $kegiatan)
                                @php
                                    $pagu = $kegiatan->total_anggaran_disetujui;
                                    $realisasi = $kegiatan->total_realisasi;
                                    $sisa = max(0, $pagu - $realisasi);
                                    $persen = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $kegiatans->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">{{ $kegiatan->nomor_pengajuan }}</div>
                                        <small class="text-muted">TA {{ optional($kegiatan->periode)->tahun_anggaran ?? '-' }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary mb-1">
                                            <a href="{{ route('admin.rkat.realisasi.show', $kegiatan->id) }}" class="text-primary text-decoration-none">
                                                {{ $kegiatan->nama_kegiatan }}
                                            </a>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-building mr-1"></i>{{ optional($kegiatan->unit)->nama_unit }} &bull;
                                            <i class="fas fa-user mr-1"></i>{{ optional($kegiatan->pic)->nama }}
                                        </div>
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-dark">
                                        Rp {{ number_format($pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-success">
                                        Rp {{ number_format($realisasi, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-secondary">
                                        Rp {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="font-weight-bold small mb-1 {{ $persen > 90 ? 'text-success' : ($persen < 30 ? 'text-muted' : 'text-info') }}">
                                            {{ $persen }}%
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px;">
                                            <div class="progress-bar {{ $persen > 90 ? 'bg-success' : ($persen < 30 ? 'bg-warning' : 'bg-primary') }}" role="progressbar" style="width: {{ min(100, $persen) }}%"></div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.rkat.realisasi.show', $kegiatan->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 font-weight-bold">
                                            <i class="fas fa-receipt mr-1"></i> Transaksi
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-3x text-secondary mb-3" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold">Belum Ada Kegiatan Disetujui</div>
                                        <small>Kegiatan RKAT yang berstatus "Disetujui" akan tampil di sini untuk dicatat bukti realisasinya.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($kegiatans->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                    {{ $kegiatans->links() }}
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
