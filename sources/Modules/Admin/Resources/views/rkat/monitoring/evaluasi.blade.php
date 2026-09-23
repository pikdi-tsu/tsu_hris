@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-clipboard-check text-primary mr-2"></i>Evaluasi Akhir Tahun Anggaran RKAT
                </h1>
                <p class="text-muted small mb-0">Laporan pertanggungjawaban tahunan, kalkulasi SiPA (Sisa Lebih Perhitungan Anggaran), dan efisiensi serapan.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.rkat.monitoring.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-weight-bold mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Monitoring
                </a>
                <button type="button" class="btn btn-primary rounded-pill px-3 font-weight-bold shadow-sm" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak Dokumen Evaluasi
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- Filter Periode Evaluasi --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.rkat.monitoring.evaluasi') }}" method="GET" class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Tahun Anggaran Evaluasi:</label>
                        <select name="periode_id" class="form-control form-control-sm rounded-pill font-weight-bold" onchange="this.form.submit()">
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                    Tahun Anggaran {{ $p->tahun_anggaran }} ({{ $p->nama_periode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary Metrics Evaluasi --}}
        @php
            $totalPagu = $kegiatans->sum('total_anggaran_disetujui');
            $totalRealisasi = $kegiatans->sum('total_realisasi');
            $sipa = max(0, $totalPagu - $totalRealisasi);
            $avgSerapan = $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 1) : 0;
            $efisiensi = max(0, 100 - $avgSerapan);
        @endphp
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #007bff !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Total Pagu RKAT Disahkan</small>
                        <h4 class="font-weight-bold text-dark mt-1 mb-0">Rp {{ number_format($totalPagu, 0, ',', '.') }}</h4>
                        <small class="text-muted">Tahun Anggaran {{ optional($periodes->firstWhere('id', $selectedPeriodeId))->tahun_anggaran }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #28a745 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Total Belanja Riil</small>
                        <h4 class="font-weight-bold text-success mt-1 mb-0">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</h4>
                        <small class="text-muted">Terserap ({{ $avgSerapan }}%)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #ffc107 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Saldo Sisa / SiPA</small>
                        <h4 class="font-weight-bold text-warning mt-1 mb-0">Rp {{ number_format($sipa, 0, ',', '.') }}</h4>
                        <small class="text-muted">Sisa Lebih Perhitungan Anggaran</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #17a2b8 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Efisiensi Anggaran</small>
                        <h4 class="font-weight-bold text-info mt-1 mb-0">{{ $efisiensi }}%</h4>
                        <small class="text-muted">Penghematan Pengeluaran</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Evaluasi Detail --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-table text-primary mr-2"></i>Rincian Capaian Kinerja & Deviasi Belanja per Usulan
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $kegiatans->total() }} Usulan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 140px;">No. Usulan</th>
                                <th>Nama Kegiatan & Unit</th>
                                <th style="width: 130px;">Target Output</th>
                                <th style="width: 140px;" class="text-right">Pagu Disetujui</th>
                                <th style="width: 140px;" class="text-right">Realisasi (SPJ)</th>
                                <th style="width: 130px;" class="text-right">Deviasi / Sisa</th>
                                <th style="width: 110px;" class="text-center">Tingkat Serapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kegiatans as $index => $kegiatan)
                                @php
                                    $pagu = $kegiatan->total_anggaran_disetujui;
                                    $real = $kegiatan->total_realisasi;
                                    $sisa = max(0, $pagu - $real);
                                    $persen = $pagu > 0 ? round(($real / $pagu) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $kegiatans->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark">
                                        {{ $kegiatan->nomor_pengajuan }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">{{ $kegiatan->nama_kegiatan }}</div>
                                        <small class="text-muted">{{ optional($kegiatan->unit)->nama_unit }} &bull; PIC: {{ optional($kegiatan->pic)->nama }}</small>
                                    </td>
                                    <td class="align-middle small">
                                        <strong>{{ $kegiatan->target_kuantitas }} {{ $kegiatan->satuan_target }}</strong>
                                        <div class="text-muted text-truncate" style="max-width: 130px;">{{ $kegiatan->output ?: '-' }}</div>
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-dark">
                                        Rp {{ number_format($pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-success">
                                        Rp {{ number_format($real, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-secondary">
                                        Rp {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge {{ $persen >= 90 ? 'badge-success' : ($persen < 50 ? 'badge-warning' : 'badge-primary') }} px-2 py-1 rounded-pill">
                                            {{ $persen }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Tidak ada data kegiatan disetujui untuk dievaluasi pada periode anggaran ini.
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
