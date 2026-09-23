@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-chart-pie text-primary mr-2"></i>Executive Dashboard RKAT & Anggaran
                </h1>
                <p class="text-muted small mb-0">Pemantauan perencanaan strategis, alokasi pagu, serta serapan anggaran universitas secara real-time.</p>
            </div>
            <div class="mt-2 mt-md-0 d-flex align-items-center">
                <form action="{{ route('admin.rkat.dashboard') }}" method="GET" class="d-flex align-items-center">
                    <select name="periode_id" class="form-control form-control-sm rounded-pill font-weight-bold mr-2" onchange="this.form.submit()">
                        @foreach($periodes as $p)
                            <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                Tahun Anggaran {{ $p->tahun_anggaran }} {{ $p->status == 'Aktif' ? '• Aktif' : ($p->keterangan ? '• ' . $p->keterangan : '') }}
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.rkat.pengajuan.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold shadow-sm text-nowrap">
                        <i class="fas fa-plus mr-1"></i> Buat Usulan
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- Executive KPI Metrics --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold" style="font-size: 0.72rem;">Total Usulan Masuk</small>
                                <h4 class="font-weight-bold mb-0 mt-1">Rp {{ number_format($totalPengajuan, 0, ',', '.') }}</h4>
                                <small class="text-white-50">{{ array_sum($statusCounts) }} Dokumen Pengajuan</small>
                            </div>
                            <div class="bg-white rounded-circle p-2 text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; opacity: 0.95;">
                                <i class="fas fa-file-invoice fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold" style="font-size: 0.72rem;">Pagu Plafon Disetujui</small>
                                <h4 class="font-weight-bold mb-0 mt-1">Rp {{ number_format($totalDisetujui, 0, ',', '.') }}</h4>
                                <small class="text-white-50">{{ $statusCounts['Disetujui'] }} Usulan Disetujui Pimpinan</small>
                            </div>
                            <div class="bg-white rounded-circle p-2 text-success d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; opacity: 0.95;">
                                <i class="fas fa-check-double fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: #fff;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-white-50 text-uppercase font-weight-bold" style="font-size: 0.72rem;">Realisasi Belanja Riil</small>
                                <h4 class="font-weight-bold mb-0 mt-1">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</h4>
                                <small class="text-white-50">Serapan: <strong>{{ $persenSerapan }}%</strong> dari Pagu</small>
                            </div>
                            <div class="bg-white rounded-circle p-2 text-info d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; opacity: 0.95;">
                                <i class="fas fa-receipt fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem;">Sisa Saldo Pagu</small>
                                <h4 class="font-weight-bold mb-0 mt-1">Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</h4>
                                <small class="text-muted">Siap dialokasikan</small>
                            </div>
                            <div class="bg-white rounded-circle p-2 text-warning d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Breakdown Pills --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body py-3 px-4">
                <div class="row text-center">
                    <div class="col-md-2 col-4 border-right mb-2 mb-md-0">
                        <div class="font-weight-bold text-secondary" style="font-size: 1.25rem;">{{ $statusCounts['Draft'] }}</div>
                        <small class="text-muted">Draft</small>
                    </div>
                    <div class="col-md-2 col-4 border-right mb-2 mb-md-0">
                        <div class="font-weight-bold text-primary" style="font-size: 1.25rem;">{{ $statusCounts['Diajukan'] }}</div>
                        <small class="text-muted">Diajukan</small>
                    </div>
                    <div class="col-md-2 col-4 border-right mb-2 mb-md-0">
                        <div class="font-weight-bold text-info" style="font-size: 1.25rem;">{{ $statusCounts['Review'] }}</div>
                        <small class="text-muted">In Review</small>
                    </div>
                    <div class="col-md-2 col-4 border-right mb-2 mb-md-0">
                        <div class="font-weight-bold text-success" style="font-size: 1.25rem;">{{ $statusCounts['Disetujui'] }}</div>
                        <small class="text-muted">Disetujui</small>
                    </div>
                    <div class="col-md-2 col-4 border-right mb-2 mb-md-0">
                        <div class="font-weight-bold text-warning" style="font-size: 1.25rem;">{{ $statusCounts['Revisi'] }}</div>
                        <small class="text-muted">Revisi</small>
                    </div>
                    <div class="col-md-2 col-4">
                        <div class="font-weight-bold text-danger" style="font-size: 1.25rem;">{{ $statusCounts['Ditolak'] }}</div>
                        <small class="text-muted">Ditolak</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row mb-4">
            {{-- Program Donut Chart --}}
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-chart-pie text-primary mr-2"></i>Komposisi Anggaran per Program
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center" style="min-height: 280px;">
                        @if(!empty($programChart) && count($programChart) > 0)
                            <div style="position: relative; width: 100%; height: 260px;">
                                <canvas id="programChartCanvas"></canvas>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-2 text-secondary" style="opacity: 0.25;"></i>
                                <div class="font-weight-bold text-dark">Belum Ada Data Anggaran</div>
                                <small class="text-muted d-block mt-1">Data grafik akan otomatis tampil setelah usulan kegiatan diajukan pada periode ini.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Unit Comparison Bar Chart --}}
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-chart-bar text-success mr-2"></i>Alokasi vs Realisasi Anggaran per Unit
                        </h6>
                        <span class="badge badge-light border text-muted">Top Units</span>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center" style="min-height: 280px;">
                        @if(!empty($unitChart) && count($unitChart) > 0)
                            <div style="position: relative; width: 100%; height: 260px;">
                                <canvas id="unitChartCanvas"></canvas>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-2 text-secondary" style="opacity: 0.25;"></i>
                                <div class="font-weight-bold text-dark">Belum Ada Data Unit</div>
                                <small class="text-muted d-block mt-1">Data perbandingan alokasi dan realisasi akan tampil setelah usulan kegiatan diajukan / disetujui.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Audit Log Activity Feed --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-history text-secondary mr-2"></i>Log Aktivitas & Audit Trail RKAT Terkini
                </h6>
                <a href="{{ route('admin.rkat.pengajuan.index') }}" class="btn btn-link btn-sm text-primary p-0">Lihat Semua Pengajuan &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 140px;">Waktu</th>
                                <th>Usulan Kegiatan & Unit</th>
                                <th style="width: 140px;">Pelaku</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                                <th>Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $act)
                                <tr>
                                    <td class="small text-muted align-middle">
                                        {{ $act->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">
                                            {{ optional($act->pengajuan)->nama_kegiatan }}
                                        </div>
                                        <small class="text-muted">
                                            {{ optional($act->pengajuan)->nomor_pengajuan }} &bull; {{ optional(optional($act->pengajuan)->unit)->nama_unit }}
                                        </small>
                                    </td>
                                    <td class="align-middle small">
                                        <strong>{{ optional($act->user)->name ?? 'Sistem' }}</strong>
                                        <div class="text-muted">{{ $act->level_jabatan }}</div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge {{ $act->action == 'Disetujui' ? 'badge-success' : ($act->action == 'Diajukan' ? 'badge-primary' : ($act->action == 'Revisi' ? 'badge-warning' : 'badge-danger')) }} px-2 py-1 rounded-pill">
                                            {{ $act->action }}
                                        </span>
                                    </td>
                                    <td class="align-middle small text-muted">
                                        {{ $act->catatan ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada rekaman aktivitas approval untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
$(function() {
    // Data for Program Donut Chart
    var programData = @json($programChart);
    var progLabels = programData.map(function(p) { return p.name; });
    var progValues = programData.map(function(p) { return p.total; });
    var progColors = ['#1e3c72', '#2a5298', '#0ba360', '#3cba92', '#6a11cb', '#2575fc', '#f7971e', '#ffd200', '#e74c3c', '#9b59b6'];

    var ctxProg = document.getElementById('programChartCanvas');
    if (ctxProg && progValues.length > 0 && typeof Chart !== 'undefined') {
        new Chart(ctxProg, {
            type: 'doughnut',
            data: {
                labels: progLabels,
                datasets: [{
                    data: progValues,
                    backgroundColor: progColors.slice(0, progValues.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        fontSize: 11
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var val = data.datasets[0].data[tooltipItem.index] || 0;
                            var label = data.labels[tooltipItem.index] || '';
                            return ' ' + label + ': Rp ' + Number(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        });
    }

    // Data for Unit Comparison Bar Chart
    var unitData = @json($unitChart);
    var unitLabels = unitData.map(function(u) { return u.name; });
    var unitAnggaran = unitData.map(function(u) { return u.anggaran; });
    var unitRealisasi = unitData.map(function(u) { return u.realisasi; });

    var ctxUnit = document.getElementById('unitChartCanvas');
    if (ctxUnit && unitLabels.length > 0 && typeof Chart !== 'undefined') {
        new Chart(ctxUnit, {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [
                    {
                        label: 'Pagu Anggaran',
                        data: unitAnggaran,
                        backgroundColor: 'rgba(54, 162, 235, 0.85)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Realisasi Riil',
                        data: unitRealisasi,
                        backgroundColor: 'rgba(40, 167, 69, 0.85)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' jt';
                                }
                                return 'Rp ' + Number(value).toLocaleString('id-ID');
                            }
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: false
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                            var val = tooltipItem.yLabel || 0;
                            return ' ' + datasetLabel + ': Rp ' + Number(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
