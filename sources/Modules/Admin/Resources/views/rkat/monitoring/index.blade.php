@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-desktop text-primary mr-2"></i>Monitoring RKAT & Capaian Kinerja
                </h1>
                <p class="text-muted small mb-0">Pemantauan efektivitas belanja, deteksi anomali serapan anggaran, dan progres capaian target fisik.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.rkat.monitoring.evaluasi') }}" class="btn btn-outline-info rounded-pill px-3 font-weight-bold shadow-sm mr-2">
                    <i class="fas fa-clipboard-check mr-1"></i> Evaluasi Akhir Tahun
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- Alert / Anomaly Indicators --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #dc3545 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-danger font-weight-bold text-uppercase">Over-Budget</small>
                                <h3 class="font-weight-bold text-danger mb-0 mt-1">{{ $alerts['over_budget']->count() }}</h3>
                                <small class="text-muted">Realisasi > Pagu</small>
                            </div>
                            <div class="bg-light rounded-circle p-2 text-danger">
                                <i class="fas fa-exclamation-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #ffc107 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-warning font-weight-bold text-uppercase">Serapan Rendah (<30%)</small>
                                <h3 class="font-weight-bold text-warning mb-0 mt-1">{{ $alerts['low_spending']->count() }}</h3>
                                <small class="text-muted">Perlu percepatan</small>
                            </div>
                            <div class="bg-light rounded-circle p-2 text-warning">
                                <i class="fas fa-hourglass-half fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #28a745 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-success font-weight-bold text-uppercase">Serapan Tinggi (>=90%)</small>
                                <h3 class="font-weight-bold text-success mb-0 mt-1">{{ $alerts['high_spending']->count() }}</h3>
                                <small class="text-muted">Mendekati rampung</small>
                            </div>
                            <div class="bg-light rounded-circle p-2 text-success">
                                <i class="fas fa-tachometer-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #6c757d !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary font-weight-bold text-uppercase">Belum Ada Belanja</small>
                                <h3 class="font-weight-bold text-secondary mb-0 mt-1">{{ $alerts['no_spending']->count() }}</h3>
                                <small class="text-muted">Realisasi 0 Rupiah</small>
                            </div>
                            <div class="bg-light rounded-circle p-2 text-secondary">
                                <i class="fas fa-pause-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Box --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.rkat.monitoring.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Tahun Anggaran:</label>
                        <select name="periode_id" class="form-control form-control-sm rounded-pill" onchange="this.form.submit()">
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                    {{ $p->tahun_anggaran }} ({{ $p->nama_periode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Unit / Lembaga:</label>
                        <select name="unit_id" class="form-control form-control-sm rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Semua Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ ($filters['unit_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Pencarian Kegiatan:</label>
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Cari kegiatan..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-1 col-sm-6 text-md-right mt-md-4">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Monitoring Performance Table --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-chart-line text-primary mr-2"></i>Matriks Serapan Anggaran & Capaian Kinerja
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $kegiatans->total() }} Kegiatan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Nama Kegiatan & Unit</th>
                                <th style="width: 140px;">Target Capaian</th>
                                <th style="width: 140px;" class="text-right">Pagu Disetujui</th>
                                <th style="width: 140px;" class="text-right">Realisasi Belanja</th>
                                <th style="width: 180px;">Serapan Anggaran</th>
                                <th style="width: 120px;" class="text-center">Status Fisik</th>
                                <th style="width: 90px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kegiatans as $index => $kegiatan)
                                @php
                                    $pagu = $kegiatan->total_anggaran_disetujui;
                                    $realisasi = $kegiatan->total_realisasi;
                                    $persen = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $kegiatans->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary mb-1">
                                            {{ $kegiatan->nama_kegiatan }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-building mr-1"></i>{{ optional($kegiatan->unit)->nama_unit }} &bull;
                                            <i class="fas fa-user mr-1"></i>{{ optional($kegiatan->pic)->nama }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info px-2 py-1 font-weight-bold">
                                            {{ $kegiatan->target_kuantitas }} {{ $kegiatan->satuan_target }}
                                        </span>
                                        @if($kegiatan->output)
                                            <small class="text-muted d-block text-truncate" style="max-width: 140px;">
                                                {{ $kegiatan->output }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-dark">
                                        Rp {{ number_format($pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-success">
                                        Rp {{ number_format($realisasi, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="font-weight-bold {{ $persen > 100 ? 'text-danger' : ($persen > 85 ? 'text-success' : ($persen < 30 ? 'text-warning' : 'text-primary')) }}">
                                                {{ $persen }}%
                                            </small>
                                            <small class="text-muted">
                                                Sisa: Rp {{ number_format(max(0, $pagu - $realisasi), 0, ',', '.') }}
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px;">
                                            <div class="progress-bar {{ $persen > 100 ? 'bg-danger' : ($persen > 85 ? 'bg-success' : ($persen < 30 ? 'bg-warning' : 'bg-primary')) }}" role="progressbar" style="width: {{ min(100, $persen) }}%"></div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($persen >= 90)
                                            <span class="badge badge-success px-2 py-1 rounded-pill"><i class="fas fa-check-circle mr-1"></i>Tercapai</span>
                                        @elseif($persen > 0)
                                            <span class="badge badge-primary px-2 py-1 rounded-pill"><i class="fas fa-spinner fa-spin mr-1"></i>Berjalan</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1 rounded-pill"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.rkat.pengajuan.show', $kegiatan->id) }}" class="btn btn-sm btn-outline-info rounded-circle" title="Detail Pengajuan">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-chart-line fa-3x text-secondary mb-3" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold">Tidak Ada Data Monitoring</div>
                                        <small>Pastikan kegiatan telah disetujui untuk mulai dipantau progresnya.</small>
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
