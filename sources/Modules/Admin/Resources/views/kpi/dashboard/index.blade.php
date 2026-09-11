@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Dashboard KPI & Balanced Scorecard"
        subtitle="Executive Dashboard pemantauan target kinerja universitas, 4 perspektif Balanced Scorecard (BSC), dan capaian unit kerja"
        :icon="$menuIcon ?? 'fas fa-tachometer-alt'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Bar: Periode Selection -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.kpi.dashboard.index') }}" class="form-inline justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <label class="font-weight-bold mr-3 text-dark"><i class="fas fa-calendar-alt text-primary mr-1"></i> Periode Penilaian:</label>
                            <select name="periode_id" class="form-control form-control-sm select2" onchange="this.form.submit()" style="min-width: 220px;">
                                @foreach($periodes as $p)
                                    <option value="{{ $p->id }}" {{ $currentPeriode && $currentPeriode->id == $p->id ? 'selected' : '' }}>
                                        Tahun {{ $p->tahun }} - {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            @if($currentPeriode)
                                {!! $currentPeriode->status_badge !!}
                                <span class="ml-2">{!! $currentPeriode->kunci_badge !!}</span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Top Level Scorecard Summary -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 5px solid #4e73df !important; border-radius: 8px;">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Indikator Terdaftar</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $totalAllIndikator }}</div>
                                    <div class="text-xs text-muted mt-1"><i class="fas fa-sitemap mr-1"></i>Terdistribusi ke unit kerja</div>
                                </div>
                                <div class="col-auto">
                                    <div class="p-3 rounded-circle bg-light text-primary">
                                        <i class="fas fa-tasks fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 5px solid #1cc88a !important; border-radius: 8px;">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Rata-Rata Capaian (%)</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $overallAvgCapaian }}%</div>
                                    <div class="progress progress-sm mt-2">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($overallAvgCapaian, 100) }}%"></div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="p-3 rounded-circle bg-light text-success">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 5px solid #36b9cc !important; border-radius: 8px;">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Skor Kinerja</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $totalSkorTercapai }}</div>
                                    <div class="text-xs text-muted mt-1"><i class="fas fa-calculator mr-1"></i>$\sum(\text{Capaian} \times \text{Bobot})$</div>
                                </div>
                                <div class="col-auto">
                                    <div class="p-3 rounded-circle bg-light text-info">
                                        <i class="fas fa-star fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 5px solid #f6c23e !important; border-radius: 8px;">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unit Kerja Terlibat</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ count($unitKpiStats) }}</div>
                                    <div class="text-xs text-muted mt-1"><i class="fas fa-building mr-1"></i>Biro, Lembaga, & Sub-Unit</div>
                                </div>
                                <div class="col-auto">
                                    <div class="p-3 rounded-circle bg-light text-warning">
                                        <i class="fas fa-university fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Balanced Scorecard Perspectives -->
            <h5 class="font-weight-bold text-dark mb-3">
                <i class="fas fa-layer-group text-primary mr-2"></i> 4 Pilar Balanced Scorecard (BSC)
            </h5>
            <div class="row mb-4">
                @php
                    $perspColors = [
                        'FIN' => ['border' => '#10B981', 'bg' => '#E6F4EA', 'text' => '#137333', 'icon' => 'fas fa-hand-holding-usd'],
                        'CUS' => ['border' => '#0284C7', 'bg' => '#E0F2FE', 'text' => '#0369A1', 'icon' => 'fas fa-users'],
                        'INT' => ['border' => '#6366F1', 'bg' => '#EEF2FF', 'text' => '#4338CA', 'icon' => 'fas fa-cogs'],
                        'LRN' => ['border' => '#F59E0B', 'bg' => '#FEF3C7', 'text' => '#B45309', 'icon' => 'fas fa-graduation-cap'],
                    ];
                @endphp

                @foreach($perspektifStats as $pStat)
                    @php
                        $code = $pStat['perspektif']->kode;
                        $theme = $perspColors[$code] ?? ['border' => '#6c757d', 'bg' => '#f8f9fa', 'text' => '#343a40', 'icon' => 'fas fa-chart-pie'];
                    @endphp
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden" style="border-radius: 10px; border-top: 4px solid {{ $theme['border'] }} !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge px-2 py-1" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }}; font-weight: 700; font-size: 12px;">
                                        {{ $code }}
                                    </span>
                                    <i class="{{ $theme['icon'] }} fa-lg" style="color: {{ $theme['border'] }};"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">{{ $pStat['perspektif']->nama_perspektif }}</h6>
                                <p class="text-muted small mb-3" style="min-height: 38px;">{{ $pStat['perspektif']->deskripsi ?? '-' }}</p>

                                <div class="p-2 rounded bg-light mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Indikator Terdaftar:</span>
                                        <span class="font-weight-bold text-dark">{{ $pStat['total_indikator'] }} Kinerja</span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Total Bobot (%):</span>
                                        <span class="font-weight-bold text-primary">{{ $pStat['total_bobot'] }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>Rata-rata Skor:</span>
                                        <span class="font-weight-bold text-success">{{ $pStat['avg_skor'] }}</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center small font-weight-bold">
                                    <span>Capaian Perspektif:</span>
                                    <span style="color: {{ $theme['text'] }};">{{ $pStat['avg_capaian'] }}%</span>
                                </div>
                                <div class="progress progress-xs mt-1">
                                    <div class="progress-bar" role="progressbar" style="width: {{ min($pStat['avg_capaian'], 100) }}%; background-color: {{ $theme['border'] }};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Unit Performance Breakdown Table & Highlights -->
            <div class="row">
                <!-- Unit Scorecard Recap -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-dark">
                                <i class="fas fa-poll text-primary mr-2"></i> Rekap Capaian Kinerja Unit Kerja
                            </h6>
                            <a href="{{ route('admin.kpi.cascading.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sitemap mr-1"></i> Kelola Cascading
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 px-3">Unit Kerja</th>
                                            <th class="border-0 text-center">Jml Indikator</th>
                                            <th class="border-0 text-center">Total Bobot</th>
                                            <th class="border-0">Rata-rata Capaian</th>
                                            <th class="border-0 text-center">Total Skor</th>
                                            <th class="border-0 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($unitKpiStats as $u)
                                            <tr>
                                                <td class="px-3">
                                                    <div class="font-weight-bold text-dark">{{ $u['unit']->nama_unit }}</div>
                                                    <small class="text-muted">{{ $u['unit']->kode_unit ?? '-' }}</small>
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $u['total_kpi'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-light border {{ $u['total_bobot'] == 100 ? 'border-success text-success' : 'border-warning text-warning' }}">
                                                        {{ $u['total_bobot'] }}%
                                                    </span>
                                                </td>
                                                <td style="min-width: 170px;">
                                                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                                        <span>{{ $u['avg_capaian'] }}%</span>
                                                    </div>
                                                    <div class="progress progress-xs">
                                                        @php
                                                            $pColor = $u['avg_capaian'] >= 100 ? 'bg-success' : ($u['avg_capaian'] >= 80 ? 'bg-info' : ($u['avg_capaian'] >= 60 ? 'bg-warning' : 'bg-danger'));
                                                        @endphp
                                                        <div class="progress-bar {{ $pColor }}" role="progressbar" style="width: {{ min($u['avg_capaian'], 100) }}%"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center font-weight-bold text-primary">{{ $u['total_skor'] }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.kpi.monitoring.index', ['unit_id' => $u['unit']->id, 'periode_id' => $currentPeriode->id ?? null]) }}" class="btn btn-xs btn-outline-info" title="Lihat Monev Unit">
                                                        <i class="fas fa-eye"></i> Monev
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i> Belum ada data indikator KPI unit kerja untuk periode ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- High Achievers & Needing Attention -->
                <div class="col-lg-4 mb-4">
                    <!-- High Achievers -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-award mr-2"></i> Indikator Terbaik ( $\ge 100\%$ )
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            @forelse($topAchievers as $top)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-2">
                                            <div class="font-weight-bold text-dark small" style="line-height: 1.3;">
                                                [{{ optional($top->masterIndikator)->kode_indikator }}] {{ optional($top->masterIndikator)->nama_indikator }}
                                            </div>
                                            <small class="text-muted"><i class="fas fa-building mr-1"></i>{{ optional($top->unit)->nama_unit }}</small>
                                        </div>
                                        <span class="badge badge-success font-weight-bold px-2 py-1">{{ number_format($top->capaian_persen, 1) }}%</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-3">Belum ada data capaian $\ge 100\%$</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Needing Attention -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Perlu Perhatian ( $< 70\%$ )
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            @forelse($needAttention as $low)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-2">
                                            <div class="font-weight-bold text-dark small" style="line-height: 1.3;">
                                                [{{ optional($low->masterIndikator)->kode_indikator }}] {{ optional($low->masterIndikator)->nama_indikator }}
                                            </div>
                                            <small class="text-muted"><i class="fas fa-building mr-1"></i>{{ optional($low->unit)->nama_unit }}</small>
                                        </div>
                                        <span class="badge badge-danger font-weight-bold px-2 py-1">{{ number_format($low->capaian_persen, 1) }}%</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-3">Tidak ada indikator di bawah 70%</div>
                            @endforelse
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
    $('.select2').select2({
        theme: 'bootstrap4'
    });
});
</script>
@endsection
