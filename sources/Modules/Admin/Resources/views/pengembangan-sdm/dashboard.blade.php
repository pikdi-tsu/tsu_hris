@extends('system::template.admin.header')
@section('title', $title)

@section('css')
    <style>
        .kpi-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform .2s ease, box-shadow .2s ease;
            overflow: hidden;
            border: none;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .kpi-icon {
            font-size: 2.5rem;
            opacity: 0.85;
        }
        .badge-ss {
            background-color: #28a745;
            color: #fff;
            font-size: 85%;
            font-weight: 600;
        }
        .badge-tss {
            background-color: #6c757d;
            color: #fff;
            font-size: 85%;
            font-weight: 600;
        }
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }
        .table-prodi thead th {
            background-color: #1e293b;
            color: #f8fafc;
            font-weight: 600;
            vertical-align: middle;
            text-align: center;
            border: none;
        }
        .table-prodi tbody tr:hover {
            background-color: #f1f5f9;
        }
        .target-pill {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-chart-line text-primary mr-2"></i>Dashboard Pengembangan SDM
                </h1>
                <p class="text-muted mb-0">Road Map Pengembangan Kualifikasi &amp; Kompetensi Dosen &amp; Tendik (2026 - 2030)</p>
            </div>
            <div class="col-sm-5 text-right">
                <form method="GET" action="{{ route('admin.pengembangan-sdm.dashboard') }}" class="form-inline d-inline-block">
                    <label class="mr-2 font-weight-bold text-secondary">Periode:</label>
                    <select name="periode_id" class="form-control form-control-sm select2 d-inline-block" style="width: 200px;" onchange="this.form.submit()">
                        @foreach($periodeList as $p)
                            <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.pengembangan-sdm.dosen') }}" class="btn btn-sm btn-primary ml-2 shadow-sm">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Road Map Dosen
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Top KPI Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="card kpi-card bg-gradient-primary text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-uppercase font-weight-bold" style="letter-spacing: 0.5px; opacity: 0.9;">Total Dosen Tetap</span>
                            <h2 class="font-weight-bolder mt-2 mb-1">{{ $kpi['total_dosen'] ?? 0 }} <small style="font-size: 14px;">Dosen</small></h2>
                            <span class="badge badge-light text-primary font-weight-bold">
                                {{ $kpi['total_prodi'] ?? 10 }} Program Studi
                            </span>
                        </div>
                        <div class="kpi-icon"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="card kpi-card bg-gradient-success text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-uppercase font-weight-bold" style="letter-spacing: 0.5px; opacity: 0.9;">Target Doktor (S3) 2030</span>
                            <h2 class="font-weight-bolder mt-2 mb-1">{{ $kpi['dosen_s3_2030'] ?? 0 }} <small style="font-size: 14px;">({{ $kpi['persen_s3_2030'] ?? 0 }}%)</small></h2>
                            <small style="opacity: 0.9;"><i class="fas fa-arrow-up mr-1"></i> Saat Ini (2026): {{ $kpi['dosen_s3_2026'] ?? 0 }} ({{ $kpi['persen_s3_2026'] ?? 0 }}%)</small>
                        </div>
                        <div class="kpi-icon"><i class="fas fa-medal"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="card kpi-card bg-gradient-info text-white h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-uppercase font-weight-bold" style="letter-spacing: 0.5px; opacity: 0.9;">Total Tendik</span>
                            <h2 class="font-weight-bolder mt-2 mb-1">{{ $kpi['total_tendik'] ?? 0 }} <small style="font-size: 14px;">Pegawai</small></h2>
                            <span class="badge badge-light text-info font-weight-bold">
                                {{ count($tendik_breakdown) }} Unit Kerja
                            </span>
                        </div>
                        <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="card kpi-card bg-gradient-warning text-dark h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-uppercase font-weight-bold" style="letter-spacing: 0.5px; opacity: 0.85;">Dosen Sedang Studi (SS)</span>
                            <h2 class="font-weight-bolder mt-2 mb-1">{{ $kpi['dosen_ss_2026'] ?? 0 }} <small style="font-size: 14px;">Dosen</small></h2>
                            <span class="badge badge-dark text-white font-weight-bold">
                                {{ $kpi['total_dn'] ?? 0 }} DN / {{ $kpi['total_ln'] ?? 0 }} LN
                            </span>
                        </div>
                        <div class="kpi-icon text-dark"><i class="fas fa-book-reader"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Status Aktif Studi Bar Chart -->
            <div class="col-lg-7 col-12 mb-4">
                <div class="card shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-balance-scale text-primary mr-2"></i>Status Studi Lanjut Dosen (SS vs TSS) per Prodi (2026)
                        </h5>
                        <span class="badge badge-primary px-2 py-1">{{ count($prodi_breakdown) }} Program Studi</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartKesesuaian"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proyeksi S3 Area Chart -->
            <div class="col-lg-5 col-12 mb-4">
                <div class="card shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-chart-area text-success mr-2"></i>Proyeksi Kualifikasi S3 (2026 - 2030)
                        </h5>
                        <span class="badge badge-success px-2 py-1">Target Renstra</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartProyeksiS3"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10 Program Studi Breakdown Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-university text-primary mr-2"></i>Rekapitulasi Road Map 10 Program Studi (Dosen)
                        </h5>
                        <div>
                            <a href="{{ route('admin.pengembangan-sdm.dosen') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka Lembar Kerja Detail
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-prodi mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 40px;">No</th>
                                    <th rowspan="2">Program Studi</th>
                                    <th rowspan="2" style="width: 70px;">Total Dosen</th>
                                    <th colspan="5">Proyeksi Dosen Bergelar Doktor (S3) per Tahun</th>
                                    <th colspan="3">Status Studi 2026</th>
                                    <th rowspan="2" style="width: 100px;">Aksi</th>
                                </tr>
                                <tr>
                                    <th style="background-color: #334155;">2026</th>
                                    <th style="background-color: #334155;">2027</th>
                                    <th style="background-color: #334155;">2028</th>
                                    <th style="background-color: #334155;">2029</th>
                                    <th style="background-color: #0284c7;">2030 (Target)</th>
                                    <th style="background-color: #166534;">SS</th>
                                    <th style="background-color: #475569;">TSS</th>
                                    <th style="background-color: #1e293b;">% SS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totDosen = 0;
                                    $tot2026 = 0; $tot2027 = 0; $tot2028 = 0; $tot2029 = 0; $tot2030 = 0;
                                    $totSS = 0; $totTSS = 0;
                                @endphp
                                @forelse($prodi_breakdown as $index => $row)
                                    @php
                                        $totDosen += $row['total_dosen'];
                                        $y26 = $row['yearly'][2026] ?? [];
                                        $y27 = $row['yearly'][2027] ?? [];
                                        $y28 = $row['yearly'][2028] ?? [];
                                        $y29 = $row['yearly'][2029] ?? [];
                                        $y30 = $row['yearly'][2030] ?? [];

                                        $tot2026 += ($y26['s3'] ?? 0);
                                        $tot2027 += ($y27['s3'] ?? 0);
                                        $tot2028 += ($y28['s3'] ?? 0);
                                        $tot2029 += ($y29['s3'] ?? 0);
                                        $tot2030 += ($y30['s3'] ?? 0);

                                        $totSS += ($y26['ss'] ?? 0);
                                        $totTSS += ($y26['tss'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('admin.pengembangan-sdm.dosen', ['unit_id' => $row['unit_id']]) }}" class="font-weight-bold text-primary">
                                                {{ $row['nama_prodi'] }}
                                            </a>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ $row['total_dosen'] }}</td>
                                        <td class="text-center">{{ $y26['s3'] ?? 0 }} <small class="text-muted">({{ $y26['persen_s3'] ?? 0 }}%)</small></td>
                                        <td class="text-center">{{ $y27['s3'] ?? 0 }}</td>
                                        <td class="text-center">{{ $y28['s3'] ?? 0 }}</td>
                                        <td class="text-center">{{ $y29['s3'] ?? 0 }}</td>
                                        <td class="text-center font-weight-bolder target-pill">
                                            {{ $y30['s3'] ?? 0 }} <small>({{ $y30['persen_s3'] ?? 0 }}%)</small>
                                        </td>
                                        <td class="text-center"><span class="badge badge-ss px-2">{{ $y26['ss'] ?? 0 }}</span></td>
                                        <td class="text-center"><span class="badge badge-tss px-2">{{ $y26['tss'] ?? 0 }}</span></td>
                                        <td class="text-center font-weight-bold text-success">{{ $y26['persen_ss'] ?? 0 }}%</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.pengembangan-sdm.dosen', ['unit_id' => $row['unit_id']]) }}" class="btn btn-xs btn-outline-primary">
                                                <i class="fas fa-eye mr-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">Belum ada data road map dosen.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($prodi_breakdown) > 0)
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="2" class="text-center text-uppercase">Total Universitas</td>
                                    <td class="text-center">{{ $totDosen }}</td>
                                    <td class="text-center">{{ $tot2026 }}</td>
                                    <td class="text-center">{{ $tot2027 }}</td>
                                    <td class="text-center">{{ $tot2028 }}</td>
                                    <td class="text-center">{{ $tot2029 }}</td>
                                    <td class="text-center text-primary font-weight-bolder">{{ $tot2030 }} ({{ $totDosen > 0 ? round(($tot2030 / $totDosen) * 100, 1) : 0 }}%)</td>
                                    <td class="text-center text-success">{{ $totSS }}</td>
                                    <td class="text-center text-secondary">{{ $totTSS }}</td>
                                    <td class="text-center text-success">{{ ($totSS + $totTSS) > 0 ? round(($totSS / ($totSS + $totTSS)) * 100, 1) : 0 }}%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9 Unit Tendik Summary Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-users-cog text-info mr-2"></i>Rekapitulasi Pengembangan Tenaga Kependidikan ({{ count($tendik_breakdown) }} Unit Kerja)
                        </h5>
                        <a href="{{ route('admin.pengembangan-sdm.tendik') }}" class="btn btn-sm btn-outline-info font-weight-bold">
                            <i class="fas fa-external-link-alt mr-1"></i> Buka Lembar Kerja Tendik
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($tendik_breakdown as $tUnit)
                                <div class="col-lg-4 col-md-6 col-12 mb-3">
                                    <div class="card border border-light shadow-none bg-light p-3 h-100" style="border-radius: 10px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="font-weight-bold text-dark mb-0">{{ $tUnit['nama_unit'] }}</h6>
                                            <span class="badge badge-info">{{ $tUnit['total_tendik'] }} Pegawai</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small mt-2">
                                            <span>Sedang Studi (SS):</span>
                                            <span class="font-weight-bold text-dark">{{ $tUnit['yearly'][2026]['ss'] ?? 0 }} Pegawai</span>
                                        </div>
                                        <div class="mt-2 text-right">
                                            <a href="{{ route('admin.pengembangan-sdm.tendik', ['unit_id' => $tUnit['unit_id']]) }}" class="btn btn-xs btn-primary">
                                                Lihat Rincian <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        @php
            $prodiNames = array_map(function($p) {
                return str_replace(['S1 - ', 'D3 - '], '', $p['nama_prodi']);
            }, $prodi_breakdown);

            $ssList = array_map(function($p) {
                return $p['yearly'][2026]['ss'] ?? 0;
            }, $prodi_breakdown);

            $tssList = array_map(function($p) {
                return $p['yearly'][2026]['tss'] ?? 0;
            }, $prodi_breakdown);

            $years = array_keys($dosen_yearly_stats);
            $persenS3List = array_column($dosen_yearly_stats, 'persen_s3');
            $countS3List = array_column($dosen_yearly_stats, 's3');
        @endphp

        var prodiLabels = {!! json_encode($prodiNames) !!};
        var ssData = {!! json_encode($ssList) !!};
        var tssData = {!! json_encode($tssList) !!};

        // Render Bar Chart: SS vs TSS
        var ctxKesesuaian = document.getElementById('chartKesesuaian').getContext('2d');
        new Chart(ctxKesesuaian, {
            type: 'bar',
            data: {
                labels: prodiLabels,
                datasets: [
                    {
                        label: 'Sedang Studi (SS)',
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1,
                        data: ssData
                    },
                    {
                        label: 'Tidak Sedang Studi (TSS)',
                        backgroundColor: '#94a3b8',
                        borderColor: '#64748b',
                        borderWidth: 1,
                        data: tssData
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        stacked: false,
                        gridLines: { display: false }
                    }],
                    yAxes: [{
                        ticks: { beginAtZero: true, stepSize: 2 }
                    }]
                }
            }
        });

        // Render Area Chart: Proyeksi S3
        var years = {!! json_encode($years) !!};
        var persenS3 = {!! json_encode($persenS3List) !!};
        var totalS3 = {!! json_encode($countS3List) !!};

        var ctxProyeksi = document.getElementById('chartProyeksiS3').getContext('2d');
        new Chart(ctxProyeksi, {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: '% Dosen Bergelar Doktor (S3)',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: '#10b981',
                    pointBackgroundColor: '#047857',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    pointRadius: 5,
                    data: persenS3,
                    fill: true,
                    lineTension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var index = tooltipItem.index;
                            return ' ' + tooltipItem.yLabel + '% (' + totalS3[index] + ' Orang S3)';
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            max: 70,
                            callback: function(value) { return value + '%'; }
                        }
                    }]
                }
            }
        });
    });
</script>
@endsection
