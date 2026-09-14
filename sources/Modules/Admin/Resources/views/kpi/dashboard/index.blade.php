@extends('system::template.admin.header')

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #063339;
            --tsu-primary-light: #cce6e9;
            --tsu-teal-accent: #0ea5e9;
            --tsu-surface: #ffffff;
            --tsu-bg-subtle: #f8fafc;
            --tsu-border: #e2e8f0;
            --tsu-text-main: #0f172a;
            --tsu-text-muted: #64748b;
        }

        /* STAT CARDS */
        .tsu-stat-grid-dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-dashboard {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-dashboard {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 105px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .tsu-stat-card--indikator {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--capaian {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--skor {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--unit {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .tsu-stat-card__watermark {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
            color: #ffffff;
        }

        .tsu-stat-card__value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }

        .tsu-stat-card__label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 500;
            color: #ffffff;
        }

        /* BUTTONS */
        .tsu-btn-create {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            border: none;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.45rem 1rem;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.25);
            transition: all 0.2s ease;
        }

        .tsu-btn-create:hover {
            background: linear-gradient(135deg, #063339 0%, #094b54 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.35);
            transform: translateY(-1px);
        }

        /* BSC PERSPECTIVES CARDS */
        .tsu-bsc-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .tsu-bsc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .tsu-bsc-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-accent, #094b54);
        }

        /* TABLE STYLING */
        .tsu-table-modern thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .tsu-table-modern tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            border: 1px solid #ced4da;
            border-radius: 0.35rem;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Dashboard KPI & Balanced Scorecard"
        subtitle="Executive Dashboard pemantauan target kinerja universitas, 4 perspektif Balanced Scorecard (BSC), dan capaian unit kerja"
        :icon="$menuIcon ?? 'fas fa-tachometer-alt'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.kpi.cascading.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-sitemap mr-1"></i> Cascading KPI
                </a>
                <a href="{{ route('admin.kpi.monitoring.index') }}" class="btn btn-sm tsu-btn-create">
                    <i class="fas fa-clipboard-check mr-1"></i> Monitoring Realisasi
                </a>
            </div>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            <!-- Ringkasan Statistik 4 Kartu Context-Aware -->
            <div class="tsu-stat-grid-dashboard">
                <div class="tsu-stat-card tsu-stat-card--indikator">
                    <i class="fas fa-tasks tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalAllIndikator }}</div>
                    <div class="tsu-stat-card__label">Total Indikator Terdaftar</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--capaian">
                    <i class="fas fa-chart-line tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $overallAvgCapaian }}%</div>
                    <div class="tsu-stat-card__label">Rata-Rata Capaian Agregat</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--skor">
                    <i class="fas fa-star tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ $totalSkorTercapai }}</div>
                    <div class="tsu-stat-card__label">Total Skor Kinerja BSC</div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--unit">
                    <i class="fas fa-building tsu-stat-card__watermark"></i>
                    <div class="tsu-stat-card__value">{{ count($unitKpiStats) }}</div>
                    <div class="tsu-stat-card__label">Unit Kerja Terlibat</div>
                </div>
            </div>

            <!-- Filter Bar: Periode Selection (Di bawah Panduan) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.kpi.dashboard.index') }}" class="row align-items-center">
                        <div class="col-md-7 d-flex align-items-center flex-wrap">
                            <label class="font-weight-bold text-dark small mb-0 mr-3">
                                <i class="fas fa-calendar-alt mr-1" style="color: var(--tsu-primary);"></i> Periode Penilaian Acuan:
                            </label>
                            <select name="periode_id" class="form-control form-control-sm select2" onchange="this.form.submit()" style="min-width: 260px;">
                                @foreach($periodes as $p)
                                    <option value="{{ $p->id }}" {{ $currentPeriode && $currentPeriode->id == $p->id ? 'selected' : '' }}>
                                        Tahun {{ $p->tahun }} - {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 text-md-right mt-2 mt-md-0 d-flex align-items-center justify-content-md-end" style="gap: 8px;">
                            @if($currentPeriode)
                                {!! $currentPeriode->status_badge !!}
                                {!! $currentPeriode->kunci_badge !!}
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- 4 Balanced Scorecard Perspectives -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-layer-group" style="color: var(--tsu-primary);"></i> 4 Pilar Balanced Scorecard (BSC)
                </h5>
                <span class="badge badge-light border text-muted" style="border-radius: 6px; padding: 5px 10px; font-weight: 600;">
                    <i class="fas fa-chart-pie mr-1 text-info"></i> Matriks Perspektif Universitas
                </span>
            </div>

            <div class="row mb-4">
                @php
                    $perspColors = [
                        'FIN' => [
                            'accent' => '#059669',
                            'badge_bg' => 'rgba(16, 185, 129, 0.1)',
                            'badge_border' => 'rgba(16, 185, 129, 0.25)',
                            'badge_text' => '#059669',
                            'icon' => 'fas fa-hand-holding-usd'
                        ],
                        'CUS' => [
                            'accent' => '#0284c7',
                            'badge_bg' => 'rgba(2, 132, 199, 0.1)',
                            'badge_border' => 'rgba(2, 132, 199, 0.25)',
                            'badge_text' => '#0284c7',
                            'icon' => 'fas fa-users'
                        ],
                        'INT' => [
                            'accent' => '#7c3aed',
                            'badge_bg' => 'rgba(139, 92, 246, 0.1)',
                            'badge_border' => 'rgba(139, 92, 246, 0.25)',
                            'badge_text' => '#7c3aed',
                            'icon' => 'fas fa-cogs'
                        ],
                        'LRN' => [
                            'accent' => '#b45309',
                            'badge_bg' => 'rgba(217, 119, 6, 0.1)',
                            'badge_border' => 'rgba(217, 119, 6, 0.25)',
                            'badge_text' => '#b45309',
                            'icon' => 'fas fa-graduation-cap'
                        ],
                    ];
                @endphp

                @foreach($perspektifStats as $pStat)
                    @php
                        $code = strtoupper($pStat['perspektif']->kode ?? '');
                        $theme = $perspColors[$code] ?? [
                            'accent' => '#094b54',
                            'badge_bg' => 'rgba(9, 75, 84, 0.1)',
                            'badge_border' => 'rgba(9, 75, 84, 0.25)',
                            'badge_text' => '#094b54',
                            'icon' => 'fas fa-chart-pie'
                        ];
                    @endphp
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100 tsu-bsc-card" style="--card-accent: {{ $theme['accent'] }};">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: {{ $theme['badge_bg'] }}; color: {{ $theme['badge_text'] }}; border: 1px solid {{ $theme['badge_border'] }}; font-size: 0.78rem;">
                                            {{ $code }}
                                        </span>
                                        <i class="{{ $theme['icon'] }}" style="color: {{ $theme['accent'] }}; font-size: 1.25rem;"></i>
                                    </div>
                                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">{{ $pStat['perspektif']->nama_perspektif }}</h6>
                                    <p class="text-muted small mb-3" style="min-height: 38px; line-height: 1.4; font-size: 0.8rem;">
                                        {{ $pStat['perspektif']->deskripsi ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <div class="p-2 mb-3" style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Indikator:</span>
                                            <span class="font-weight-bold text-dark">{{ $pStat['total_indikator'] }} Kinerja</span>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Total Bobot:</span>
                                            <span class="font-weight-bold" style="color: var(--tsu-primary);">{{ $pStat['total_bobot'] }}%</span>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <span class="text-muted">Rata-rata Skor:</span>
                                            <span class="font-weight-bold text-dark">{{ $pStat['avg_skor'] }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center small font-weight-bold mb-1">
                                        <span class="text-muted">Capaian:</span>
                                        <span style="color: {{ $theme['badge_text'] }};">{{ $pStat['avg_capaian'] }}%</span>
                                    </div>
                                    <div class="progress progress-xs" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ min($pStat['avg_capaian'], 100) }}%; background-color: {{ $theme['accent'] }};"></div>
                                    </div>
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
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <div>
                                <h6 class="m-0 font-weight-bold text-dark" style="display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-poll" style="color: var(--tsu-primary);"></i> Rekap Capaian Kinerja Unit Kerja
                                </h6>
                                <small class="text-muted">Daftar agregat pencapaian scorecard per fakultas, prodi, biro, dan lembaga</small>
                            </div>
                            <a href="{{ route('admin.kpi.cascading.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-sitemap mr-1"></i> Kelola Cascading
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover tsu-table-modern mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th class="px-3">Unit Kerja</th>
                                            <th class="text-center">Jml Indikator</th>
                                            <th class="text-center">Total Bobot</th>
                                            <th>Rata-rata Capaian</th>
                                            <th class="text-center">Total Skor</th>
                                            <th class="text-center">Aksi</th>
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
                                                    @if($u['total_bobot'] == 100)
                                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.78rem;">
                                                            {{ $u['total_bobot'] }}%
                                                        </span>
                                                    @else
                                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); font-size: 0.78rem;">
                                                            {{ $u['total_bobot'] }}%
                                                        </span>
                                                    @endif
                                                </td>
                                                <td style="min-width: 170px;">
                                                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                                        <span>{{ $u['avg_capaian'] }}%</span>
                                                    </div>
                                                    <div class="progress progress-xs" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                                        @php
                                                            $pColor = $u['avg_capaian'] >= 100 ? '#059669' : ($u['avg_capaian'] >= 80 ? '#0284c7' : ($u['avg_capaian'] >= 60 ? '#b45309' : '#dc2626'));
                                                        @endphp
                                                        <div class="progress-bar" role="progressbar" style="width: {{ min($u['avg_capaian'], 100) }}%; background-color: {{ $pColor }};"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center font-weight-bold" style="color: var(--tsu-primary); font-size: 0.95rem;">
                                                    {{ $u['total_skor'] }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.kpi.monitoring.index', ['unit_id' => $u['unit']->id, 'periode_id' => $currentPeriode->id ?? null]) }}" class="btn btn-sm btn-outline-primary" title="Lihat Monev Unit" style="border-radius: 6px; padding: 0.25rem 0.55rem; font-size: 0.78rem;">
                                                        <i class="fas fa-eye mr-1"></i> Monev
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
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                            <h6 class="m-0 font-weight-bold" style="color: #059669; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-award"></i> Indikator Terbaik (≥ 100%)
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            @forelse($topAchievers as $top)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-2">
                                            <div class="font-weight-bold text-dark small" style="line-height: 1.3;">
                                                <span style="color: var(--tsu-primary); font-family: monospace;">[{{ optional($top->masterIndikator)->kode_indikator }}]</span> 
                                                {{ optional($top->masterIndikator)->nama_indikator }}
                                            </div>
                                            <small class="text-muted"><i class="fas fa-building mr-1"></i>{{ optional($top->unit)->nama_unit }}</small>
                                        </div>
                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.78rem;">
                                            {{ number_format($top->capaian_persen, 1) }}%
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-3">Belum ada data capaian ≥ 100%</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Needing Attention -->
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3" style="border-bottom: 1px solid var(--tsu-border);">
                            <h6 class="m-0 font-weight-bold text-danger" style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-exclamation-triangle"></i> Perlu Perhatian (< 70%)
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            @forelse($needAttention as $low)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-2">
                                            <div class="font-weight-bold text-dark small" style="line-height: 1.3;">
                                                <span style="color: #dc2626; font-family: monospace;">[{{ optional($low->masterIndikator)->kode_indikator }}]</span> 
                                                {{ optional($low->masterIndikator)->nama_indikator }}
                                            </div>
                                            <small class="text-muted"><i class="fas fa-building mr-1"></i>{{ optional($low->unit)->nama_unit }}</small>
                                        </div>
                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.78rem;">
                                            {{ number_format($low->capaian_persen, 1) }}%
                                        </span>
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
