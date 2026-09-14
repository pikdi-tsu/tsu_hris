@extends('system::template.admin.header')
@section('title', $title)

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
        .tsu-stat-grid-sdm {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-sdm {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-sdm {
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

        .tsu-stat-card--dosen {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
        }

        .tsu-stat-card--target-s3 {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
        }

        .tsu-stat-card--tendik {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .tsu-stat-card--studi {
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
            font-size: 1.75rem;
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

        /* CHART CONTAINERS */
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }

        /* TABLE STYLING */
        .table-prodi thead th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 0.5rem;
        }

        .table-prodi thead tr.table-prodi-subheader th {
            font-size: 0.75rem;
            padding: 0.45rem 0.35rem;
            color: #ffffff;
            border: none;
        }

        .table-prodi tbody td {
            padding: 0.75rem 0.75rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-prodi tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge-ss {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .badge-tss {
            background: rgba(100, 116, 139, 0.1);
            color: #475569;
            border: 1px solid rgba(100, 116, 139, 0.25);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .target-pill {
            background: rgba(9, 75, 84, 0.1);
            color: #094b54;
            border: 1px solid rgba(9, 75, 84, 0.25);
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
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
        title="Dashboard & Rekapitulasi Pengembangan SDM"
        subtitle="Road Map Pengembangan Kualifikasi & Kompetensi Dosen & Tendik (2026 - 2030)"
        icon="fas fa-chart-line"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.pengembangan-sdm.dosen') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Road Map Dosen
                </a>
                <a href="{{ route('admin.pengembangan-sdm.tendik') }}" class="btn btn-sm tsu-btn-create">
                    <i class="fas fa-users-cog mr-1"></i> Road Map Tendik
                </a>
            </div>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            <!-- Top KPI Cards (4 Signature TSU Stat Cards) -->
            <div class="tsu-stat-grid-sdm">
                <div class="tsu-stat-card tsu-stat-card--dosen">
                    <i class="fas fa-user-graduate tsu-stat-card__watermark"></i>
                    <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Total Dosen Tetap</span>
                    <div class="tsu-stat-card__value mt-1 mb-1">
                        {{ $kpi['total_dosen'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Dosen</small>
                    </div>
                    <div>
                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                            {{ $kpi['total_prodi'] ?? 10 }} Program Studi
                        </span>
                    </div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--target-s3">
                    <i class="fas fa-award tsu-stat-card__watermark"></i>
                    <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Target Doktor (S3) 2030</span>
                    <div class="tsu-stat-card__value mt-1 mb-1">
                        {{ $kpi['dosen_s3_2030'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">({{ $kpi['persen_s3_2030'] ?? 0 }}%)</small>
                    </div>
                    <small style="opacity: 0.95; font-size: 0.8rem;">
                        <i class="fas fa-arrow-up mr-1"></i> Saat Ini (2026): {{ $kpi['dosen_s3_2026'] ?? 0 }} ({{ $kpi['persen_s3_2026'] ?? 0 }}%)
                    </small>
                </div>

                <div class="tsu-stat-card tsu-stat-card--tendik">
                    <i class="fas fa-user-tie tsu-stat-card__watermark"></i>
                    <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Total Tendik</span>
                    <div class="tsu-stat-card__value mt-1 mb-1">
                        {{ $kpi['total_tendik'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Pegawai</small>
                    </div>
                    <div>
                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                            {{ count($tendik_breakdown) }} Unit Kerja
                        </span>
                    </div>
                </div>

                <div class="tsu-stat-card tsu-stat-card--studi">
                    <i class="fas fa-book-reader tsu-stat-card__watermark"></i>
                    <span class="tsu-stat-card__label text-uppercase font-weight-bold" style="letter-spacing: 0.5px;">Dosen Sedang Studi (SS)</span>
                    <div class="tsu-stat-card__value mt-1 mb-1">
                        {{ $kpi['dosen_ss_2026'] ?? 0 }} <small style="font-size: 1.1rem; opacity: 0.9;">Dosen</small>
                    </div>
                    <div>
                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                            {{ $kpi['total_dn'] ?? 0 }} DN / {{ $kpi['total_ln'] ?? 0 }} LN
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card Panduan (Placed BELOW Stat Cards) -->
            <x-tsu-master-guide
                title="Panduan Dashboard & Road Map Pengembangan SDM"
                description="Dashboard ini memantau peta jalan (road map) akselerasi kualifikasi pendidikan Dosen (pencapaian gelar Doktor S3) serta perencanaan studi lanjut dan sertifikasi kompetensi Tenaga Kependidikan (Tendik) hingga tahun 2030 di lingkungan universitas."
                :connections="[
                    ['label' => 'Road Map Dosen per Prodi', 'route' => 'admin.pengembangan-sdm.dosen', 'icon' => 'fas fa-chalkboard-teacher'],
                    ['label' => 'Road Map Tendik per Unit', 'route' => 'admin.pengembangan-sdm.tendik', 'icon' => 'fas fa-users-cog'],
                    ['label' => 'Monitoring Usia Pensiun', 'route' => 'admin.pengembangan-sdm.pensiun', 'icon' => 'fas fa-hourglass-half'],
                    ['label' => 'Master Periode Renstra', 'route' => 'admin.master-pengembangan.periode.index', 'icon' => 'fas fa-calendar-alt']
                ]"
                impact="Data proyeksi kualifikasi menjadi acuan strategis pimpinan dalam penyusunan anggaran beasiswa studi lanjut, alokasi formasi dosen, serta pemenuhan syarat akreditasi program studi."
            />

            <!-- Filter Bar: Periode Selection (Di bawah Panduan) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.pengembangan-sdm.dashboard') }}" class="row align-items-center justify-content-between">
                        <div class="col-md-7 d-flex align-items-center flex-wrap">
                            <label class="font-weight-bold text-dark small mb-0 mr-3">
                                <i class="fas fa-calendar-alt mr-1" style="color: var(--tsu-primary);"></i> Periode Renstra Pengembangan:
                            </label>
                            <select name="periode_id" class="form-control form-control-sm select2" onchange="this.form.submit()" style="min-width: 260px;">
                                @foreach($periodeList as $p)
                                    <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_periode }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 text-md-right mt-2 mt-md-0 d-flex align-items-center justify-content-md-end" style="gap: 8px;">
                            <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.82rem;">
                                <i class="fas fa-university mr-1"></i> 10 Prodi Dosen & 9 Unit Tendik
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Charts Row: Dosen -->
            <div class="row">
                <!-- Status Aktif Studi Bar Chart -->
                <div class="col-lg-7 col-12 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-balance-scale" style="color: var(--tsu-primary);"></i> Status Studi Lanjut Dosen (SS vs TSS) per Prodi (2026)
                            </h5>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.78rem;">
                                {{ count($prodi_breakdown) }} Program Studi
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart-container">
                                <canvas id="chartKesesuaian"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proyeksi S3 Area Chart -->
                <div class="col-lg-5 col-12 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-chart-area text-success"></i> Proyeksi Kualifikasi S3 (2026 - 2030)
                            </h5>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.78rem;">
                                Target Renstra
                            </span>
                        </div>
                        <div class="card-body p-3">
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
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <div>
                                <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-university" style="color: var(--tsu-primary);"></i> Rekapitulasi Road Map 10 Program Studi (Dosen)
                                </h5>
                                <small class="text-muted">Proyeksi kualifikasi doktor (S3) multi-tahun dan status aktif studi lanjut dosen</small>
                            </div>
                            <div>
                                <a href="{{ route('admin.pengembangan-sdm.dosen') }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 8px;">
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
                                        <th rowspan="2" style="width: 80px;">Total Dosen</th>
                                        <th colspan="5">Proyeksi Dosen Bergelar Doktor (S3) per Tahun</th>
                                        <th colspan="3">Status Studi 2026</th>
                                        <th rowspan="2" style="width: 90px;">Aksi</th>
                                    </tr>
                                    <tr class="table-prodi-subheader">
                                        <th style="background-color: #334155;">2026</th>
                                        <th style="background-color: #334155;">2027</th>
                                        <th style="background-color: #334155;">2028</th>
                                        <th style="background-color: #334155;">2029</th>
                                        <th style="background-color: #094b54;">2030 (Target)</th>
                                        <th style="background-color: #047857;">SS</th>
                                        <th style="background-color: #64748b;">TSS</th>
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
                                                <a href="{{ route('admin.pengembangan-sdm.dosen', ['unit_id' => $row['unit_id']]) }}" class="font-weight-bold" style="color: var(--tsu-primary);">
                                                    {{ $row['nama_prodi'] }}
                                                </a>
                                            </td>
                                            <td class="text-center font-weight-bold text-dark">{{ $row['total_dosen'] }}</td>
                                            <td class="text-center">{{ $y26['s3'] ?? 0 }} <small class="text-muted">({{ $y26['persen_s3'] ?? 0 }}%)</small></td>
                                            <td class="text-center">{{ $y27['s3'] ?? 0 }}</td>
                                            <td class="text-center">{{ $y28['s3'] ?? 0 }}</td>
                                            <td class="text-center">{{ $y29['s3'] ?? 0 }}</td>
                                            <td class="text-center font-weight-bold target-pill">
                                                {{ $y30['s3'] ?? 0 }} <small>({{ $y30['persen_s3'] ?? 0 }}%)</small>
                                            </td>
                                            <td class="text-center"><span class="badge badge-ss px-2 py-1">{{ $y26['ss'] ?? 0 }}</span></td>
                                            <td class="text-center"><span class="badge badge-tss px-2 py-1">{{ $y26['tss'] ?? 0 }}</span></td>
                                            <td class="text-center font-weight-bold" style="color: #059669;">{{ $y26['persen_ss'] ?? 0 }}%</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.pengembangan-sdm.dosen', ['unit_id' => $row['unit_id']]) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; padding: 0.2rem 0.5rem; font-size: 0.78rem;">
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
                                        <td class="text-center font-weight-bold" style="color: var(--tsu-primary);">{{ $tot2030 }} ({{ $totDosen > 0 ? round(($tot2030 / $totDosen) * 100, 1) : 0 }}%)</td>
                                        <td class="text-center" style="color: #059669;">{{ $totSS }}</td>
                                        <td class="text-center text-secondary">{{ $totTSS }}</td>
                                        <td class="text-center" style="color: #059669;">{{ ($totSS + $totTSS) > 0 ? round(($totSS / ($totSS + $totTSS)) * 100, 1) : 0 }}%</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: ROAD MAP PENGEMBANGAN TENAGA KEPENDIDIKAN (TENDIK) -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <div>
                    <h4 class="font-weight-bold text-dark mb-1" style="font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-users-cog" style="color: var(--tsu-teal-accent);"></i> Road Map Pengembangan Tenaga Kependidikan (Tendik)
                    </h4>
                    <p class="text-muted small mb-0">Visualisasi status studi lanjut dan proyeksi kualifikasi pendidikan Tendik (9 Unit Kerja, 2026–2030)</p>
                </div>
                <div>
                    <a href="{{ route('admin.pengembangan-sdm.tendik') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 8px;">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka Lembar Kerja Tendik
                    </a>
                </div>
            </div>

            <!-- Tendik Charts Row -->
            <div class="row">
                <!-- Tendik Status Aktif Studi Bar Chart -->
                <div class="col-lg-7 col-12 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-user-clock text-info"></i> Status Studi Lanjut Tendik (SS vs TSS) per Unit Kerja (2026)
                            </h5>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-size: 0.78rem;">
                                {{ count($tendik_breakdown) }} Unit Kerja
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart-container">
                                <canvas id="chartTendikUnit"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tendik Proyeksi Kualifikasi S1 & S2 Line Chart -->
                <div class="col-lg-5 col-12 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-chart-line" style="color: var(--tsu-primary);"></i> Proyeksi Kualifikasi S1 & S2 Tendik (2026 - 2030)
                            </h5>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.78rem;">
                                Renstra Tendik
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart-container">
                                <canvas id="chartTendikProyeksi"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rekapitulasi 9 Unit Kerja Tendik Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border);">
                            <div>
                                <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-table" style="color: var(--tsu-teal-accent);"></i> Rekapitulasi Road Map 9 Unit Kerja (Tenaga Kependidikan)
                                </h5>
                                <small class="text-muted">Peta kualifikasi jenjang pendidikan dan status studi lanjut tenaga kependidikan</small>
                            </div>
                            <div>
                                <a href="{{ route('admin.pengembangan-sdm.tendik') }}" class="btn btn-sm btn-outline-info font-weight-bold" style="border-radius: 8px;">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Lembar Kerja Detail
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-prodi mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width: 40px;">No</th>
                                        <th rowspan="2">Unit Kerja</th>
                                        <th rowspan="2" style="width: 80px;">Total Tendik</th>
                                        <th colspan="3">Kualifikasi 2026</th>
                                        <th colspan="5">Proyeksi Tendik Magister (S2) per Tahun</th>
                                        <th colspan="3">Status Studi 2026</th>
                                        <th rowspan="2" style="width: 90px;">Aksi</th>
                                    </tr>
                                    <tr class="table-prodi-subheader">
                                        <th style="background-color: #334155;">D3</th>
                                        <th style="background-color: #334155;">S1</th>
                                        <th style="background-color: #0f766e;">S2</th>
                                        <th style="background-color: #334155;">2026</th>
                                        <th style="background-color: #334155;">2027</th>
                                        <th style="background-color: #334155;">2028</th>
                                        <th style="background-color: #334155;">2029</th>
                                        <th style="background-color: #0284c7;">2030 (Target)</th>
                                        <th style="background-color: #047857;">SS</th>
                                        <th style="background-color: #64748b;">TSS</th>
                                        <th style="background-color: #1e293b;">% SS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totTendik = 0;
                                        $totD3_26 = 0; $totS1_26 = 0; $totS2_26 = 0;
                                        $tTot26 = 0; $tTot27 = 0; $tTot28 = 0; $tTot29 = 0; $tTot30 = 0;
                                        $tTotSS = 0; $tTotTSS = 0;
                                    @endphp
                                    @forelse($tendik_breakdown as $tIdx => $tRow)
                                        @php
                                            $totTendik += $tRow['total_tendik'];
                                            $ty26 = $tRow['yearly'][2026] ?? [];
                                            $ty27 = $tRow['yearly'][2027] ?? [];
                                            $ty28 = $tRow['yearly'][2028] ?? [];
                                            $ty29 = $tRow['yearly'][2029] ?? [];
                                            $ty30 = $tRow['yearly'][2030] ?? [];

                                            $totD3_26 += ($ty26['d3'] ?? 0);
                                            $totS1_26 += ($ty26['s1'] ?? 0);
                                            $totS2_26 += ($ty26['s2'] ?? 0);

                                            $tTot26 += ($ty26['s2'] ?? 0);
                                            $tTot27 += ($ty27['s2'] ?? 0);
                                            $tTot28 += ($ty28['s2'] ?? 0);
                                            $tTot29 += ($ty29['s2'] ?? 0);
                                            $tTot30 += ($ty30['s2'] ?? 0);

                                            $tTotSS += ($ty26['ss'] ?? 0);
                                            $tTotTSS += ($ty26['tss'] ?? 0);

                                            $tPersenSS = ($tRow['total_tendik'] > 0) ? round((($ty26['ss'] ?? 0) / $tRow['total_tendik']) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold">{{ $tIdx + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.pengembangan-sdm.tendik', ['unit_id' => $tRow['unit_id']]) }}" class="font-weight-bold text-dark">
                                                    {{ $tRow['nama_unit'] }}
                                                </a>
                                            </td>
                                            <td class="text-center font-weight-bold text-dark">{{ $tRow['total_tendik'] }}</td>
                                            <td class="text-center">{{ $ty26['d3'] ?? 0 }}</td>
                                            <td class="text-center">{{ $ty26['s1'] ?? 0 }}</td>
                                            <td class="text-center font-weight-bold text-success">{{ $ty26['s2'] ?? 0 }}</td>
                                            <td class="text-center">{{ $ty26['s2'] ?? 0 }}</td>
                                            <td class="text-center">{{ $ty27['s2'] ?? 0 }}</td>
                                            <td class="text-center">{{ $ty28['s2'] ?? 0 }}</td>
                                            <td class="text-center">{{ $ty29['s2'] ?? 0 }}</td>
                                            <td class="text-center font-weight-bold target-pill">
                                                {{ $ty30['s2'] ?? 0 }}
                                            </td>
                                            <td class="text-center"><span class="badge badge-ss px-2 py-1">{{ $ty26['ss'] ?? 0 }}</span></td>
                                            <td class="text-center"><span class="badge badge-tss px-2 py-1">{{ $ty26['tss'] ?? 0 }}</span></td>
                                            <td class="text-center font-weight-bold" style="color: #059669;">{{ $tPersenSS }}%</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.pengembangan-sdm.tendik', ['unit_id' => $tRow['unit_id']]) }}" class="btn btn-sm btn-outline-info" style="border-radius: 6px; padding: 0.2rem 0.5rem; font-size: 0.78rem;">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="text-center text-muted py-4">Belum ada data road map tenaga kependidikan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($tendik_breakdown) > 0)
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-center text-uppercase">Total Seluruh Unit Tendik</td>
                                        <td class="text-center">{{ $totTendik }}</td>
                                        <td class="text-center">{{ $totD3_26 }}</td>
                                        <td class="text-center">{{ $totS1_26 }}</td>
                                        <td class="text-center text-success">{{ $totS2_26 }}</td>
                                        <td class="text-center">{{ $tTot26 }}</td>
                                        <td class="text-center">{{ $tTot27 }}</td>
                                        <td class="text-center">{{ $tTot28 }}</td>
                                        <td class="text-center">{{ $tTot29 }}</td>
                                        <td class="text-center font-weight-bold text-primary">{{ $tTot30 }} ({{ $totTendik > 0 ? round(($tTot30 / $totTendik) * 100, 1) : 0 }}%)</td>
                                        <td class="text-center" style="color: #059669;">{{ $tTotSS }}</td>
                                        <td class="text-center text-secondary">{{ $tTotTSS }}</td>
                                        <td class="text-center" style="color: #059669;">{{ ($tTotSS + $tTotTSS) > 0 ? round(($tTotSS / ($tTotSS + $tTotTSS)) * 100, 1) : 0 }}%</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9 Unit Tendik Quick Cards (Elevated & Informative) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 10px;">
                            <div>
                                <h6 class="card-title font-weight-bold text-dark mb-0" style="font-size: 0.98rem; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-th-large" style="color: var(--tsu-primary);"></i> Rincian Distribusi & Status 9 Unit Kerja Tendik
                                </h6>
                                <small class="text-muted">Pemantauan progres kualifikasi pendidikan, studi lanjut, dan target Renstra per unit operasional</small>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.8rem;">
                                    <i class="fas fa-building mr-1"></i> 9 Unit Pelaksana (Biro, Lembaga, Fak. & UPT)
                                </span>
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-size: 0.8rem;">
                                    <i class="fas fa-users mr-1"></i> {{ $kpi['total_tendik'] ?? $totTendik }} Total Pegawai
                                </span>
                                <span class="badge badge-pill font-weight-bold px-3 py-2" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.8rem;">
                                    <i class="fas fa-user-graduate mr-1"></i> {{ $tTotSS }} Sedang Studi ({{ ($kpi['total_tendik'] ?? $totTendik) > 0 ? round(($tTotSS / ($kpi['total_tendik'] ?? $totTendik)) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3" style="background: #f8fafc;">
                            <div class="row">
                                @foreach($tendik_breakdown as $tUnit)
                                    @php
                                        $uTotal = $tUnit['total_tendik'] ?? 0;
                                        $uSS = $tUnit['yearly'][2026]['ss'] ?? 0;
                                        $uTSS = $tUnit['yearly'][2026]['tss'] ?? 0;
                                        $uS2 = $tUnit['yearly'][2026]['s2'] ?? 0;
                                        $uS2_30 = $tUnit['yearly'][2030]['s2'] ?? 0;
                                        $pctSS = $uTotal > 0 ? round(($uSS / $uTotal) * 100, 1) : 0;
                                        $pctS2 = $uTotal > 0 ? round(($uS2 / $uTotal) * 100, 1) : 0;
                                        
                                        // Unit icon category
                                        $uName = $tUnit['nama_unit'];
                                        $uIcon = 'fas fa-building';
                                        if (str_contains($uName, 'Akademik') || str_contains($uName, 'BAAK')) $uIcon = 'fas fa-graduation-cap';
                                        elseif (str_contains($uName, 'Keuangan') || str_contains($uName, 'BAUK')) $uIcon = 'fas fa-file-invoice-dollar';
                                        elseif (str_contains($uName, 'Mutu') || str_contains($uName, 'LPM')) $uIcon = 'fas fa-clipboard-check';
                                        elseif (str_contains($uName, 'LPPM') || str_contains($uName, 'Penelitian')) $uIcon = 'fas fa-microscope';
                                        elseif (str_contains($uName, 'Fakultas')) $uIcon = 'fas fa-university';
                                        elseif (str_contains($uName, 'Vokasi')) $uIcon = 'fas fa-tools';
                                        elseif (str_contains($uName, 'Sekretariat')) $uIcon = 'fas fa-user-shield';
                                        elseif (str_contains($uName, 'Perpustakaan')) $uIcon = 'fas fa-book';
                                        elseif (str_contains($uName, 'Sistem Informasi')) $uIcon = 'fas fa-laptop-code';
                                    @endphp
                                    <div class="col-lg-4 col-md-6 col-12 mb-3">
                                        <div class="card h-100 border shadow-none" style="border-radius: 12px; background: #ffffff; border-color: #e2e8f0 !important; transition: all 0.2s ease-in-out; overflow: hidden;">
                                            <div class="p-3" style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                                                <div class="d-flex justify-content-between align-items-start" style="gap: 8px;">
                                                    <div class="d-flex align-items-center" style="gap: 10px;">
                                                        <div class="d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(9, 75, 84, 0.08); color: var(--tsu-primary); font-size: 1rem; flex-shrink: 0;">
                                                            <i class="{{ $uIcon }}"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.88rem; line-height: 1.3;" title="{{ $tUnit['nama_unit'] }}">
                                                                {{ $tUnit['nama_unit'] }}
                                                            </h6>
                                                            <span class="text-muted small" style="font-size: 0.73rem;">Unit Kerja Pelaksana</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge badge-pill font-weight-bold px-2 py-1 flex-shrink-0" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-size: 0.75rem;">
                                                        <i class="fas fa-users mr-1"></i> {{ $uTotal }} Pegawai
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="p-3">
                                                <!-- Status Sedang Studi (SS) -->
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small" style="font-size: 0.8rem;">
                                                        <i class="fas fa-user-graduate mr-1" style="color: #059669;"></i> Sedang Studi (SS):
                                                    </span>
                                                    @if($uSS > 0)
                                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.75rem;">
                                                            {{ $uSS }} Pegawai ({{ $pctSS }}%)
                                                        </span>
                                                    @else
                                                        <span class="badge badge-pill font-weight-normal text-muted px-2 py-1" style="background: #f1f5f9; border: 1px solid #e2e8f0; font-size: 0.75rem;">
                                                            0 Pegawai (0%)
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Kualifikasi S2 Saat Ini -->
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small" style="font-size: 0.8rem;">
                                                        <i class="fas fa-award mr-1" style="color: var(--tsu-primary);"></i> Kualifikasi S2 (2026):
                                                    </span>
                                                    @if($uS2 > 0)
                                                        <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.75rem;">
                                                            {{ $uS2 }} Magister ({{ $pctS2 }}%)
                                                        </span>
                                                    @else
                                                        <span class="badge badge-pill font-weight-normal text-muted px-2 py-1" style="background: #f1f5f9; border: 1px solid #e2e8f0; font-size: 0.75rem;">
                                                            0 Magister
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Target 2030 Row -->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="text-muted small" style="font-size: 0.8rem;">
                                                        <i class="fas fa-bullseye mr-1" style="color: #d97706;"></i> Target S2 (2030):
                                                    </span>
                                                    <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.75rem;">
                                                        {{ $uS2_30 }} Magister
                                                    </span>
                                                </div>

                                                <!-- Progress Mini Bar -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem; margin-bottom: 4px;">
                                                        <span>Rasio Kualifikasi S2 & SS</span>
                                                        <span class="font-weight-bold text-dark">{{ $uTotal > 0 ? round((($uS2 + $uSS) / $uTotal) * 100) : 0 }}%</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $uTotal > 0 ? min(100, round((($uS2 + $uSS) / $uTotal) * 100)) : 0 }}%; background: linear-gradient(90deg, #094b54, #0d9488); border-radius: 4px;"></div>
                                                    </div>
                                                </div>

                                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small" style="font-size: 0.74rem;">
                                                        <i class="fas fa-briefcase mr-1"></i> Tendik Unit
                                                    </span>
                                                    <a href="{{ route('admin.pengembangan-sdm.tendik', ['unit_id' => $tUnit['unit_id']]) }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 0.75rem; padding: 3px 10px;">
                                                        Buka Rincian <i class="fas fa-arrow-right ml-1"></i>
                                                    </a>
                                                </div>
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
        $('.select2').select2({ theme: 'bootstrap4' });

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

            // Tendik Data
            $tendikUnitNames = array_map(function($t) {
                $name = $t['nama_unit'];
                $name = str_replace([
                    'Biro Administrasi Akademik dan Kemahasiswaan (BAAK)',
                    'Biro Administrasi Umum dan Keuangan (BAUK)',
                    'Lembaga Penjaminan Mutu (LPM)',
                    'Lembaga Penelitian dan Pengabdian Masyarakat (LPPM)',
                    'Unit Pelaksana Teknis Sistem Informasi (UPT SI)',
                    'Unit Pelaksana Teknis Perpustakaan',
                    'Unit Pelaksana Teknis Laboratorium Terpadu',
                    'Biro Humas, Kerjasama, dan Pemasaran',
                    'Satuan Pengawas Internal (SPI)'
                ], [
                    'BAAK', 'BAUK', 'LPM', 'LPPM', 'UPT SI', 'UPT Perpus', 'UPT Lab', 'Humas', 'SPI'
                ], $name);
                return $name;
            }, $tendik_breakdown);

            $tendikSSList = array_map(function($t) {
                return $t['yearly'][2026]['ss'] ?? 0;
            }, $tendik_breakdown);

            $tendikTSSList = array_map(function($t) {
                return $t['yearly'][2026]['tss'] ?? 0;
            }, $tendik_breakdown);

            $tendikYears = array_keys($tendik_yearly_stats);
            $tendikS2List = array_column($tendik_yearly_stats, 's2');
            $tendikS1List = array_column($tendik_yearly_stats, 's1');
            $tendikPersenS1S2 = array_column($tendik_yearly_stats, 'persen_s1_s2');
        @endphp

        var prodiLabels = {!! json_encode($prodiNames) !!};
        var ssData = {!! json_encode($ssList) !!};
        var tssData = {!! json_encode($tssList) !!};

        // ==========================================
        // 1. Bar Chart Dosen: SS vs TSS per Prodi
        // ==========================================
        // Filosofi Warna:
        // - Sedang Studi (SS): TSU Deep Teal (#094b54) melambangkan akselerasi kualifikasi dosen yang aktif menempuh studi lanjut.
        // - Tidak Sedang Studi (TSS): Refined Neutral Slate (rgba(148, 163, 184, 0.45)) melambangkan stabilitas operasional pengajaran aktif di kampus.
        var ctxKesesuaian = document.getElementById('chartKesesuaian').getContext('2d');
        new Chart(ctxKesesuaian, {
            type: 'bar',
            data: {
                labels: prodiLabels,
                datasets: [
                    {
                        label: 'Sedang Studi (SS)',
                        backgroundColor: '#094b54',
                        borderColor: '#042f35',
                        borderWidth: 1.5,
                        hoverBackgroundColor: '#0d9488',
                        data: ssData
                    },
                    {
                        label: 'Tidak Sedang Studi (TSS)',
                        backgroundColor: 'rgba(148, 163, 184, 0.45)',
                        borderColor: '#94a3b8',
                        borderWidth: 1,
                        hoverBackgroundColor: 'rgba(148, 163, 184, 0.75)',
                        data: tssData
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        fontColor: '#334155',
                        fontStyle: '600',
                        fontSize: 11
                    }
                },
                scales: {
                    xAxes: [{
                        stacked: false,
                        gridLines: { display: false },
                        ticks: { autoSkip: false, maxRotation: 35, minRotation: 0, fontColor: '#64748b', fontSize: 10 }
                    }],
                    yAxes: [{
                        gridLines: { color: '#f1f5f9', zeroLineColor: '#e2e8f0' },
                        ticks: { beginAtZero: true, stepSize: 2, fontColor: '#64748b' }
                    }]
                }
            }
        });

        // ==========================================
        // 2. Area Chart Dosen: Proyeksi Kualifikasi S3 (2026 - 2030)
        // ==========================================
        // Filosofi Warna:
        // - Garis kurva TSU Deep Teal (#094b54) dengan gradient fill halus merefleksikan roadmap akselerasi keunggulan institusi.
        // - Titik Capaian Amber Gold (#f59e0b) dengan border putih melambangkan medali emas gelar Doktor (S3) sebagai standar tertinggi.
        var years = {!! json_encode($years) !!};
        var persenS3 = {!! json_encode($persenS3List) !!};
        var totalS3 = {!! json_encode($countS3List) !!};

        var ctxProyeksi = document.getElementById('chartProyeksiS3').getContext('2d');
        var gradientS3 = ctxProyeksi.createLinearGradient(0, 0, 0, 260);
        gradientS3.addColorStop(0, 'rgba(9, 75, 84, 0.28)');
        gradientS3.addColorStop(1, 'rgba(9, 75, 84, 0.01)');

        new Chart(ctxProyeksi, {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: '% Dosen Bergelar Doktor (S3)',
                    backgroundColor: gradientS3,
                    borderColor: '#094b54',
                    borderWidth: 3,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#d97706',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                    pointRadius: 6,
                    data: persenS3,
                    fill: true,
                    lineTension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        fontColor: '#334155',
                        fontStyle: '600',
                        fontSize: 11
                    }
                },
                tooltips: {
                    backgroundColor: '#1e293b',
                    titleFontSize: 12,
                    bodyFontSize: 12,
                    cornerRadius: 6,
                    xPadding: 10,
                    yPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var index = tooltipItem.index;
                            return ' Proyeksi Doktor S3: ' + tooltipItem.yLabel + '% (' + totalS3[index] + ' Orang S3)';
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#64748b' }
                    }],
                    yAxes: [{
                        gridLines: { color: '#f1f5f9', zeroLineColor: '#e2e8f0' },
                        ticks: {
                            beginAtZero: true,
                            max: 70,
                            fontColor: '#64748b',
                            callback: function(value) { return value + '%'; }
                        }
                    }]
                }
            }
        });

        // ==========================================
        // 3. Bar Chart Tendik: SS vs TSS per Unit Kerja (2026)
        // ==========================================
        // Filosofi Warna:
        // - Sedang Studi (SS): Oceanic Blue (#0284c7) melambangkan transformasi inovatif dan peningkatan kompetensi profesional tendik.
        // - Tidak Sedang Studi (TSS): Refined Neutral Slate (rgba(148, 163, 184, 0.45)) melambangkan kontinuitas pelayanan prima harian unit kerja.
        var tendikUnitLabels = {!! json_encode($tendikUnitNames) !!};
        var tendikSSData = {!! json_encode($tendikSSList) !!};
        var tendikTSSData = {!! json_encode($tendikTSSList) !!};

        var ctxTendikUnit = document.getElementById('chartTendikUnit').getContext('2d');
        new Chart(ctxTendikUnit, {
            type: 'bar',
            data: {
                labels: tendikUnitLabels,
                datasets: [
                    {
                        label: 'Sedang Studi (SS)',
                        backgroundColor: '#0284c7',
                        borderColor: '#0369a1',
                        borderWidth: 1.5,
                        hoverBackgroundColor: '#0369a1',
                        data: tendikSSData
                    },
                    {
                        label: 'Tidak Sedang Studi (TSS)',
                        backgroundColor: 'rgba(148, 163, 184, 0.45)',
                        borderColor: '#94a3b8',
                        borderWidth: 1,
                        hoverBackgroundColor: 'rgba(148, 163, 184, 0.75)',
                        data: tendikTSSData
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        fontColor: '#334155',
                        fontStyle: '600',
                        fontSize: 11
                    }
                },
                scales: {
                    xAxes: [{
                        stacked: false,
                        gridLines: { display: false },
                        ticks: { autoSkip: false, maxRotation: 30, minRotation: 0, fontColor: '#64748b', fontSize: 10 }
                    }],
                    yAxes: [{
                        gridLines: { color: '#f1f5f9', zeroLineColor: '#e2e8f0' },
                        ticks: { beginAtZero: true, stepSize: 1, fontColor: '#64748b' }
                    }]
                }
            }
        });

        // ==========================================
        // 4. Line Chart Tendik: Proyeksi Kualifikasi S1 & S2 (2026 - 2030)
        // ==========================================
        // Filosofi Warna:
        // - Tendik S2 (Magister): TSU Deep Teal (#094b54) solid line dengan gradient fill melambangkan akselerasi kualifikasi magister sebagai target utama.
        // - Tendik S1 (Sarjana): Warm Amber Gold (#f59e0b) garis putus-putus melambangkan transformasi staf sarjana menuju kualifikasi magister.
        var ctxTendikProyeksi = document.getElementById('chartTendikProyeksi').getContext('2d');
        var gradientTendikS2 = ctxTendikProyeksi.createLinearGradient(0, 0, 0, 260);
        gradientTendikS2.addColorStop(0, 'rgba(9, 75, 84, 0.22)');
        gradientTendikS2.addColorStop(1, 'rgba(9, 75, 84, 0.01)');

        new Chart(ctxTendikProyeksi, {
            type: 'line',
            data: {
                labels: {!! json_encode($tendikYears) !!},
                datasets: [
                    {
                        label: 'Tendik S2 (Magister)',
                        backgroundColor: gradientTendikS2,
                        borderColor: '#094b54',
                        borderWidth: 3,
                        pointBackgroundColor: '#094b54',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#0d9488',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2,
                        pointRadius: 6,
                        data: {!! json_encode($tendikS2List) !!},
                        fill: true,
                        lineTension: 0.35
                    },
                    {
                        label: 'Tendik S1 (Sarjana)',
                        backgroundColor: 'transparent',
                        borderColor: '#f59e0b',
                        borderWidth: 2.5,
                        borderDash: [6, 4],
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#d97706',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2,
                        pointRadius: 5,
                        data: {!! json_encode($tendikS1List) !!},
                        fill: false,
                        lineTension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        fontColor: '#334155',
                        fontStyle: '600',
                        fontSize: 11
                    }
                },
                tooltips: {
                    backgroundColor: '#1e293b',
                    titleFontSize: 12,
                    bodyFontSize: 12,
                    cornerRadius: 6,
                    xPadding: 10,
                    yPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dsLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                            return ' ' + dsLabel + ': ' + tooltipItem.yLabel + ' Pegawai';
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#64748b' }
                    }],
                    yAxes: [{
                        gridLines: { color: '#f1f5f9', zeroLineColor: '#e2e8f0' },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 5,
                            fontColor: '#64748b'
                        }
                    }]
                }
            }
        });
    });
</script>
@endsection
