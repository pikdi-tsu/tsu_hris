@extends('system::template.admin.header')

@section('title', $title ?? 'Manpower Planning')

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #1d4ed8;
            --tsu-radius: 10px;
            --tsu-radius-lg: 14px;
        }

        /* === 4 Stat Cards === */
        .tsu-mpp-stat-card {
            border-radius: var(--tsu-radius-lg, 14px);
            padding: 1.25rem 1.4rem;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 118px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.25s ease;
        }
        .tsu-mpp-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        .tsu-mpp-stat-card__watermark {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.4rem;
            opacity: 0.14;
            pointer-events: none;
            color: #ffffff;
        }
        .tsu-mpp-stat-card__label {
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            opacity: 0.9;
            margin-bottom: 0.35rem;
        }
        .tsu-mpp-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
        }
        .tsu-mpp-stat-card__unit {
            font-size: 0.88rem;
            font-weight: 600;
            opacity: 0.85;
        }
        .tsu-mpp-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.85;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Stat Card Gradients */
        .tsu-card-grad--supply {
            background: linear-gradient(135deg, #094b54 0%, #0e636f 100%);
            color: #ffffff;
        }
        .tsu-card-grad--demand {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
        }
        .tsu-card-grad--deficit {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-card-grad--balanced {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        /* === Main Wrapper Card === */
        .tsu-main-mpp-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 14px);
            border: 1px solid rgba(0, 0, 0, 0.07);
            box-shadow: 0 4px 20px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        /* === Modern Tab Navigation === */
        .tsu-mpp-tabs-nav {
            display: flex;
            align-items: stretch;
            padding: 0.35rem 1rem 0 1rem;
            background: #f8fafc;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9);
            margin: 0;
            list-style: none;
            overflow-x: auto;
            overflow-y: hidden !important;
            white-space: nowrap;
            gap: 0.35rem;
            scrollbar-width: none !important; /* Firefox */
            -ms-overflow-style: none !important; /* IE/Edge */
        }
        .tsu-mpp-tabs-nav::-webkit-scrollbar {
            display: none !important; /* Chrome/Safari/Edge */
            width: 0 !important;
            height: 0 !important;
        }

        /* === Table Scroll Wrapper with Sticky Header === */
        .tsu-table-scroll-wrapper {
            max-height: 540px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            position: relative;
        }
        .tsu-table-scroll-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #f8fafc !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }
        .tsu-table-scroll-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .tsu-table-scroll-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .tsu-table-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .tsu-table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .tsu-mpp-tabs-nav .nav-item {
            margin-bottom: -2px;
        }
        .tsu-mpp-tabs-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.85rem 1.15rem;
            font-size: 0.86rem;
            font-weight: 600;
            color: #64748b;
            border: none;
            border-bottom: 3px solid transparent;
            background: transparent;
            border-radius: 8px 8px 0 0;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .tsu-mpp-tabs-nav .nav-link:hover {
            color: var(--tsu-primary, #094b54);
            background: rgba(9, 75, 84, 0.04);
        }
        .tsu-mpp-tabs-nav .nav-link.active {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border-bottom: 3px solid var(--tsu-primary, #094b54);
            font-weight: 700;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.03);
        }

        /* === Tab Sub-Header Banner === */
        .tsu-tab-banner {
            background: #f8fafc;
            border-radius: var(--tsu-radius, 10px);
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: gap-2;
        }

        /* === Search Box === */
        .tsu-search-input {
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            padding: 0.4rem 0.85rem;
            font-size: 0.85rem;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .tsu-search-input:focus {
            border-color: var(--tsu-primary, #094b54);
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12);
            outline: none;
        }

        /* === Modern Tables === */
        .tsu-table-clean {
            margin-bottom: 0;
        }
        .tsu-table-clean thead th {
            background: #f8fafc !important;
            color: var(--tsu-primary-dark, #07383f) !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            vertical-align: middle !important;
            padding: 0.85rem 1rem !important;
        }
        .tsu-table-clean tbody td {
            vertical-align: middle !important;
            font-size: 0.85rem;
            padding: 0.85rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-clean tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* === Action Button Buka Rekrutmen === */
        .btn-action-rekrutmen {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 7px;
            padding: 0.35rem 0.85rem;
            font-weight: 600;
            font-size: 0.78rem;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-action-rekrutmen:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }

        /* Filter bar approval */
        .tsu-filter-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 0.9rem 1.15rem;
            margin-bottom: 1.25rem;
            border: 1px solid #e2e8f0;
        }

        .tsu-btn-refresh-top {
            background: #ffffff;
            color: var(--tsu-primary, #094b54);
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: 8px;
            font-weight: 600;
            padding: 0.4rem 0.9rem;
            transition: all 0.2s;
        }
        .tsu-btn-refresh-top:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Manpower Planning'"
        :icon="$menuIcon ?? 'fas fa-users-cog'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-refresh-top" id="btn-reload" title="Segarkan Data Halaman">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Section Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Modern Stat Cards (Using Bootstrap Grid col-xl-3 col-md-6) --}}
            <div class="row mb-4">
                {{-- 1. Total Supply --}}
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-mpp-stat-card tsu-card-grad--supply">
                        <i class="fas fa-users tsu-mpp-stat-card__watermark"></i>
                        <div>
                            <div class="tsu-mpp-stat-card__label">Total Supply (Eksisting)</div>
                            <div class="tsu-mpp-stat-card__value">
                                {{ number_format($mppSummary['total_supply'] ?? 0) }}
                                <span class="tsu-mpp-stat-card__unit">Pegawai</span>
                            </div>
                        </div>
                        <div class="tsu-mpp-stat-card__subtext">
                            <i class="fas fa-check-circle mr-1"></i> Headcount Pegawai Aktif Terdaftar
                        </div>
                    </div>
                </div>

                {{-- 2. Total Demand --}}
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-mpp-stat-card tsu-card-grad--demand">
                        <i class="fas fa-bullseye tsu-mpp-stat-card__watermark"></i>
                        <div>
                            <div class="tsu-mpp-stat-card__label">Total Demand (Ideal)</div>
                            <div class="tsu-mpp-stat-card__value">
                                {{ number_format($mppSummary['total_demand'] ?? 0) }}
                                <span class="tsu-mpp-stat-card__unit">Formasi</span>
                            </div>
                        </div>
                        <div class="tsu-mpp-stat-card__subtext">
                            <i class="fas fa-calculator mr-1"></i> Kebutuhan Ideal Berbasis Rasio &amp; Beban
                        </div>
                    </div>
                </div>

                {{-- 3. Defisit Formasi --}}
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-mpp-stat-card tsu-card-grad--deficit">
                        <i class="fas fa-user-plus tsu-mpp-stat-card__watermark"></i>
                        <div>
                            <div class="tsu-mpp-stat-card__label">Unit Butuh Formasi (Defisit)</div>
                            <div class="tsu-mpp-stat-card__value">
                                {{ number_format($mppSummary['units_deficit'] ?? 0) }}
                                <span class="tsu-mpp-stat-card__unit">Unit (+{{ $mppSummary['total_gap_kebutuhan'] ?? 0 }})</span>
                            </div>
                        </div>
                        <div class="tsu-mpp-stat-card__subtext">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Kekurangan Pegawai Prioritas Pengadaan
                        </div>
                    </div>
                </div>

                {{-- 4. Seimbang & Surplus --}}
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-mpp-stat-card tsu-card-grad--balanced">
                        <i class="fas fa-balance-scale tsu-mpp-stat-card__watermark"></i>
                        <div>
                            <div class="tsu-mpp-stat-card__label">Seimbang &amp; Surplus</div>
                            <div class="tsu-mpp-stat-card__value">
                                {{ number_format(($mppSummary['units_balanced'] ?? 0) + ($mppSummary['units_surplus'] ?? 0)) }}
                                <span class="tsu-mpp-stat-card__unit">Unit</span>
                            </div>
                        </div>
                        <div class="tsu-mpp-stat-card__subtext">
                            <i class="fas fa-shield-alt mr-1"></i> {{ $mppSummary['units_balanced'] ?? 0 }} Seimbang | {{ $mppSummary['units_surplus'] ?? 0 }} Surplus
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main MPP Card Container --}}
            <div class="tsu-main-mpp-card">
                {{-- Modern Segmented Tab Navigation --}}
                <ul class="nav tsu-mpp-tabs-nav" id="mpp-tabs-nav" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-btn-supply" data-toggle="tab" href="#tab-pane-supply" role="tab">
                            <i class="fas fa-users mr-1 text-info"></i> 1. Current Supply
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-btn-demand" data-toggle="tab" href="#tab-pane-demand" role="tab">
                            <i class="fas fa-chart-line mr-1 text-primary"></i> 2. Demand Analysis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-btn-gap" data-toggle="tab" href="#tab-pane-gap" role="tab">
                            <i class="fas fa-balance-scale mr-1 text-warning"></i> 3. Gap Analysis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-btn-action" data-toggle="tab" href="#tab-pane-action" role="tab">
                            <i class="fas fa-rocket mr-1 text-danger"></i> 4. Action Plan &amp; Rekrutmen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-btn-approval" data-toggle="tab" href="#tab-pane-approval" role="tab">
                            <i class="fas fa-clipboard-check mr-1 text-success"></i> 5. Usulan Formasi &amp; Persetujuan
                            @if(($stats['count_waiting'] ?? 0) > 0)
                                <span class="badge badge-warning ml-1 font-weight-bold">{{ $stats['count_waiting'] }}</span>
                            @endif
                        </a>
                    </li>
                </ul>

                <div class="card-body p-4 tab-content">

                    {{-- ======================================================== --}}
                    {{-- TAB 1: CURRENT SUPPLY                                    --}}
                    {{-- ======================================================== --}}
                    <div class="tab-pane fade show active" id="tab-pane-supply" role="tabpanel">
                        <div class="tsu-tab-banner">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-users text-info mr-2"></i>Peta Pasokan Pegawai Aktif (Current Supply)
                                </h6>
                                <p class="text-muted text-xs mb-0">
                                    Rekapitulasi kekuatan SDM riil dosen dan tenaga kependidikan aktif berdasarkan penempatan unit kerja.
                                </p>
                            </div>
                            <div>
                                <input type="text" id="search-supply" class="form-control form-control-sm tsu-search-input" placeholder="Cari unit kerja..." style="width: 250px;">
                            </div>
                        </div>

                        <div class="tsu-table-scroll-wrapper">
                            <table class="table tsu-table-clean table-hover w-100" id="table-supply">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">NO</th>
                                        <th>UNIT KERJA / PRODI</th>
                                        <th>TIPE UNIT</th>
                                        <th style="text-align: center;">DOSEN AKTIF</th>
                                        <th style="text-align: center;">TENDIK AKTIF</th>
                                        <th style="text-align: center;">TOTAL HEADCOUNT</th>
                                        <th style="text-align: center;">TARGET MPP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analysisData as $idx => $row)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $row['nama_unit'] }}</div>
                                                <small class="text-muted"><i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i>Induk: {{ $row['parent_name'] }}</small>
                                            </td>
                                            <td>
                                                @if($row['tipe_unit'] === 'akademik')
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-graduation-cap mr-1"></i>Akademik</span>
                                                @else
                                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-briefcase mr-1"></i>Non-Akademik</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border text-dark px-2 py-1 font-weight-600">{{ $row['supply']['dosen'] }} Org</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border text-dark px-2 py-1 font-weight-600">{{ $row['supply']['tendik'] }} Org</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-dark px-3 py-1 font-weight-bold" style="font-size: 0.88rem;">{{ $row['supply']['total_hc'] }} Org</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border text-secondary px-2 py-1 font-weight-600">
                                                    {{ $row['kuota_mpp'] > 0 ? $row['kuota_mpp'] . ' Org' : '∞ (Fleksibel)' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- TAB 2: DEMAND ANALYSIS                                   --}}
                    {{-- ======================================================== --}}
                    <div class="tab-pane fade" id="tab-pane-demand" role="tabpanel">
                        <div class="tsu-tab-banner">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-chart-line text-primary mr-2"></i>Analisis Kebutuhan Ideal (Demand Analysis)
                                </h6>
                                <p class="text-muted text-xs mb-0">
                                    Perhitungan teoritis formasi ideal: Prodi berbasis rasio mahasiswa (1 Dosen : 30 Mhs), Biro/UPT berbasis beban kerja institusi.
                                </p>
                            </div>
                            <div>
                                <input type="text" id="search-demand" class="form-control form-control-sm tsu-search-input" placeholder="Cari unit kerja..." style="width: 250px;">
                            </div>
                        </div>

                        <div class="tsu-table-scroll-wrapper">
                            <table class="table tsu-table-clean table-hover w-100" id="table-demand">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">NO</th>
                                        <th>UNIT KERJA / PRODI</th>
                                        <th>TIPE UNIT</th>
                                        <th>BASIS PENGHITUNGAN</th>
                                        <th style="text-align: center;">PLAFON KUOTA</th>
                                        <th style="text-align: center;">KEBUTUHAN IDEAL (DEMAND)</th>
                                        <th>FORMULA ACUAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analysisData as $idx => $row)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $row['nama_unit'] }}</div>
                                            </td>
                                            <td>
                                                @if($row['tipe_unit'] === 'akademik')
                                                    <span class="badge badge-success px-2 py-1">Akademik</span>
                                                @else
                                                    <span class="badge badge-info px-2 py-1">Non-Akademik</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($row['tipe_unit'] === 'akademik')
                                                    <span class="badge badge-light border text-dark font-weight-600">
                                                        <i class="fas fa-user-graduate text-success mr-1"></i>{{ $row['jumlah_mahasiswa'] ? number_format($row['jumlah_mahasiswa']) . ' Mhs' : 'Belum Diisi' }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-light border text-dark font-weight-600">
                                                        <i class="fas fa-tasks text-info mr-1"></i>Beban: {{ ucfirst($row['beban_kerja']) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border px-2 py-1 font-weight-600">
                                                    {{ $row['kuota_mpp'] > 0 ? $row['kuota_mpp'] . ' Org' : 'Fleksibel' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.88rem;">
                                                    {{ $row['demand'] }} Orang
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    @if($row['tipe_unit'] === 'akademik')
                                                        {{ $row['jumlah_mahasiswa'] > 0 ? '⌈Mahasiswa / 30⌉' : ($row['kuota_mpp'] > 0 ? 'Plafon Kuota Unit' : 'Beban Standar HC') }}
                                                    @else
                                                        {{ $row['kuota_mpp'] > 0 ? 'Plafon Kuota Unit' : 'HC (' . $row['supply']['total_hc'] . ') + Beban ' . ucfirst($row['beban_kerja']) }}
                                                    @endif
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- TAB 3: GAP ANALYSIS                                      --}}
                    {{-- ======================================================== --}}
                    <div class="tab-pane fade" id="tab-pane-gap" role="tabpanel">
                        <div class="tsu-tab-banner">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-balance-scale text-warning mr-2"></i>Peta Kesenjangan Formasi (Gap Analysis)
                                </h6>
                                <p class="text-muted text-xs mb-0">
                                    Perbandingan riil selisih antara Kebutuhan Ideal (Demand) dan Pasokan Staf Saat Ini (Supply).
                                </p>
                            </div>
                            <div>
                                <input type="text" id="search-gap" class="form-control form-control-sm tsu-search-input" placeholder="Cari unit kerja..." style="width: 250px;">
                            </div>
                        </div>

                        <div class="tsu-table-scroll-wrapper">
                            <table class="table tsu-table-clean table-hover w-100" id="table-gap">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">NO</th>
                                        <th>UNIT KERJA / PRODI</th>
                                        <th>TIPE</th>
                                        <th style="text-align: center;">SUPPLY (HC)</th>
                                        <th style="text-align: center;">DEMAND (IDEAL)</th>
                                        <th style="text-align: center;">GAP FORMASI</th>
                                        <th style="text-align: center;">STATUS KESENJANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analysisData as $idx => $row)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $row['nama_unit'] }}</div>
                                            </td>
                                            <td>
                                                <small class="badge badge-light border">{{ ucfirst($row['tipe_unit']) }}</small>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ $row['supply']['total_hc'] }} Org</td>
                                            <td class="text-center font-weight-bold text-primary">{{ $row['demand'] }} Org</td>
                                            <td class="text-center">
                                                @if($row['gap'] > 0)
                                                    <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                                        +{{ $row['gap'] }} Kurang
                                                    </span>
                                                @elseif($row['gap'] == 0)
                                                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                                        0 Seimbang
                                                    </span>
                                                @else
                                                    <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                                        {{ $row['gap'] }} Surplus
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $row['badge'] }} px-2 py-1">{{ $row['label'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- TAB 4: ACTION PLAN & REKRUTMEN                           --}}
                    {{-- ======================================================== --}}
                    <div class="tab-pane fade" id="tab-pane-action" role="tabpanel">
                        <div class="tsu-tab-banner">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-rocket text-danger mr-2"></i>Rencana Tindak Lanjut &amp; Alokasi Rekrutmen (Action Plan)
                                </h6>
                                <p class="text-muted text-xs mb-0">
                                    Rekomendasi strategis tindak lanjut pengadaan pegawai berdasarkan tingkat urgensi kesenjangan formasi (Gap).
                                </p>
                            </div>
                            <div>
                                <input type="text" id="search-action" class="form-control form-control-sm tsu-search-input" placeholder="Cari unit kerja..." style="width: 250px;">
                            </div>
                        </div>

                        <div class="tsu-table-scroll-wrapper">
                            <table class="table tsu-table-clean table-hover w-100" id="table-action">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">NO</th>
                                        <th>UNIT KERJA / PRODI</th>
                                        <th style="text-align: center;">GAP FORMASI</th>
                                        <th>REKOMENDASI TINDAKAN</th>
                                        <th style="text-align: center;">STATUS ALOKASI</th>
                                        <th style="text-align: center; width: 170px;">AKSI STRATEGIS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analysisData as $idx => $row)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $row['nama_unit'] }}</div>
                                                <small class="text-muted">Target Posisi: {{ $row['tipe_unit'] === 'akademik' ? 'Tenaga Pendidik / Dosen' : 'Tenaga Kependidikan' }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($row['gap'] > 0)
                                                    <span class="badge badge-danger px-2 py-1 font-weight-bold font-14">{{ $row['gap'] }} Orang</span>
                                                @elseif($row['gap'] == 0)
                                                    <span class="badge badge-success px-2 py-1 font-weight-600">0 Orang</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1 font-weight-600">{{ abs($row['gap']) }} Berlebih</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-weight-600 text-dark">{{ $row['rekomendasi'] }}</div>
                                                <small class="text-muted">
                                                    @if($row['status'] === 'prioritas_tinggi')
                                                        Prioritas tinggi: Segera buka formasi pengadaan eksternal baru.
                                                    @elseif($row['status'] === 'prioritas_sedang')
                                                        Dapat dipenuhi via mutasi internal atau pembukaan rekrutmen gelombang 2.
                                                    @elseif($row['status'] === 'seimbang')
                                                        Kapasitas staf memadai, pertahankan formasi saat ini.
                                                    @else
                                                        Dapat dipertimbangkan untuk rotasi/redistribusi ke unit yang defisit.
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $row['badge'] }} px-2 py-1">{{ $row['label'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($row['action_type'] === 'rekrutmen')
                                                    <button type="button" class="btn-action-rekrutmen btn-buka-rekrutmen" 
                                                            data-unit="{{ $row['nama_unit'] }}" 
                                                            data-gap="{{ $row['gap'] }}" 
                                                            title="Buka Formasi Rekrutmen untuk Unit Ini">
                                                        <i class="fas fa-bullhorn"></i> Buka Rekrutmen
                                                    </button>
                                                @elseif($row['action_type'] === 'mutasi')
                                                    <span class="badge badge-light border text-secondary px-2 py-1 font-weight-600">Evaluasi Rotasi</span>
                                                @else
                                                    <span class="badge badge-light border text-success px-2 py-1 font-weight-600"><i class="fas fa-check mr-1"></i>Terpenuhi</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- TAB 5: USULAN FORMASI & APPROVAL                         --}}
                    {{-- ======================================================== --}}
                    <div class="tab-pane fade" id="tab-pane-approval" role="tabpanel">
                        <div class="tsu-tab-banner">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-clipboard-check text-success mr-2"></i>Daftar Usulan Formasi &amp; Tinjauan Persetujuan SDM
                                </h6>
                                <p class="text-muted text-xs mb-0">
                                    Verifikasi usulan kebutuhan pegawai yang diajukan oleh unit kerja / fakultas sebelum disetujui.
                                </p>
                            </div>
                        </div>

                        {{-- Sub-tabs Buttons --}}
                        <div class="mb-3 d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary active mr-2 font-weight-bold px-3 py-1" id="btn-sub-waiting" onclick="changeApprovalSubTab('waiting')">
                                <i class="fas fa-clock mr-1"></i> Menunggu Persetujuan
                                <span class="badge badge-warning ml-1">{{ $stats['count_waiting'] ?? 0 }}</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-1" id="btn-sub-history" onclick="changeApprovalSubTab('history')">
                                <i class="fas fa-history mr-1"></i> Riwayat Persetujuan
                                <span class="badge badge-secondary ml-1">{{ $stats['count_history'] ?? 0 }}</span>
                            </button>
                        </div>

                        {{-- Filter Bar --}}
                        <div class="tsu-filter-box">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <label class="text-xs font-weight-bold text-muted uppercase mb-1 d-block"><i class="fas fa-calendar-alt mr-1"></i> Tahun Perencanaan</label>
                                    <select class="form-control form-control-sm font-weight-600" id="filter_tahun" onchange="filterData()">
                                        <option value="">Semua Tahun</option>
                                        @for($i = date('Y') - 1; $i <= date('Y') + 3; $i++)
                                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                                    <label class="text-xs font-weight-bold text-muted uppercase mb-1 d-block"><i class="fas fa-building mr-1"></i> Unit Kerja / Divisi</label>
                                    <select class="form-control select2" id="filter_unit" onchange="filterData()">
                                        <option value="">-- Semua Unit Kerja --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilter()">
                                        <i class="fas fa-undo mr-1"></i> Reset Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Data Table for Approvals --}}
                        <div class="table-responsive">
                            <table id="mpp-table" class="table tsu-table-clean table-hover w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">NO</th>
                                        <th>UNIT / DIVISI</th>
                                        <th>JABATAN</th>
                                        <th style="width: 120px;" class="text-center">KEBUTUHAN</th>
                                        <th style="width: 80px;" class="text-center">TAHUN</th>
                                        <th style="width: 120px;" class="text-center">TIPE</th>
                                        <th style="width: 140px;" class="text-center">STATUS</th>
                                        <th style="width: 100px; text-align: center;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- Modal Container for Approval --}}
    <div id="modal-container-approval"></div>
@endsection

@section('script')
    <script src="{{ asset('public/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('public/assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        var table;
        var currentApprovalSubTab = 'waiting';

        $(function () {
            // Inisialisasi DataTable untuk Tab Approval
            table = $('#mpp-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.mpp.datatables') }}",
                    type: "POST",
                    data: function (d) {
                        d._token = "{{ csrf_token() }}";
                        d.status = currentApprovalSubTab;
                        d.tahun = $('#filter_tahun').val();
                        d.unit_id = $('#filter_unit').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'unit', name: 'unit.nama_unit'},
                    {data: 'jabatan', name: 'jabatan.nama_jabatan'},
                    {data: 'jumlah_kebutuhan', name: 'jumlah_kebutuhan', className: 'text-center'},
                    {data: 'tahun', name: 'tahun', className: 'text-center font-weight-bold'},
                    {data: 'tipe_pengajuan', name: 'tipe_pengajuan', className: 'text-center'},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                ],
                order: [[4, 'desc']],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ usulan",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 usulan",
                    infoFiltered: "(disaring dari _MAX_ total usulan)",
                    zeroRecords: "Tidak ada data usulan yang cocok",
                    emptyTable: "Belum ada pengajuan manpower planning",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                }
            });

            if ($.fn.select2) {
                $('.select2').select2({ width: '100%' });
            }

            // Quick live search filter for static tables
            setupTableSearch('#search-supply', '#table-supply');
            setupTableSearch('#search-demand', '#table-demand');
            setupTableSearch('#search-gap', '#table-gap');
            setupTableSearch('#search-action', '#table-action');

            // Button Buka Rekrutmen Pop Up
            $(document).on('click', '.btn-buka-rekrutmen', function(e) {
                e.preventDefault();
                let unitName = $(this).data('unit');
                let gapQty = $(this).data('gap');

                Swal.fire({
                    title: 'Modul Rekrutmen Terintegrasi',
                    html: `
                        <div class="text-left p-3 rounded" style="background: #f0fdf4; border: 1.5px solid #86efac;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle text-success mr-2" style="font-size: 1.4rem;"></i>
                                <span class="font-weight-bold text-dark" style="font-size: 0.95rem;">Modul Recruitment Sedang Dalam Tahap Pengembangan</span>
                            </div>
                            <p class="text-muted text-sm mb-3" style="line-height: 1.4;">
                                Sistem Manpower Planning telah berhasil mencadangkan kuota lowongan resmi berdasarkan hasil Gap Analysis untuk formasi berikut:
                            </p>
                            <div class="bg-white p-3 rounded border mb-2 shadow-sm">
                                <div class="text-xs text-muted font-weight-bold uppercase">Unit Kerja / Prodi:</div>
                                <div class="font-weight-bold text-dark font-15 mb-2">${unitName}</div>
                                <div class="text-xs text-muted font-weight-bold uppercase">Alokasi Formasi Terbuka:</div>
                                <div class="font-weight-800 text-success font-18">${gapQty} Formasi Pegawai Baru</div>
                            </div>
                            <div class="text-xs text-muted mt-2">
                                <i class="fas fa-shield-alt mr-1 text-primary"></i>Ketika modul Rekrutmen aktif, posisi ini otomatis terbuka dengan pagu formasi terkunci maksimal ${gapQty} kandidat terpilih.
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Mengerti &amp; Tutup',
                    confirmButtonColor: '#094b54'
                });
            });

            // Refresh button
            $('#btn-reload').on('click', function() {
                let $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                location.reload();
            });

            // Adjust table on tab switch
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.id === 'tab-btn-approval') {
                    table.columns.adjust().responsive.recalc();
                }
            });
        });

        function setupTableSearch(inputId, tableId) {
            $(inputId).on('keyup', function() {
                let val = $(this).val().toLowerCase();
                $(tableId + ' tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });
        }

        function changeApprovalSubTab(tabName) {
            currentApprovalSubTab = tabName;
            if (tabName === 'waiting') {
                $('#btn-sub-waiting').addClass('btn-outline-primary active').removeClass('btn-outline-secondary');
                $('#btn-sub-history').addClass('btn-outline-secondary').removeClass('btn-outline-primary active');
            } else {
                $('#btn-sub-history').addClass('btn-outline-primary active').removeClass('btn-outline-secondary');
                $('#btn-sub-waiting').addClass('btn-outline-secondary').removeClass('btn-outline-primary active');
            }
            table.ajax.reload();
        }

        function filterData() {
            let thn = $('#filter_tahun').val();
            if (thn && thn != "{{ $tahun }}") {
                window.location.href = "{{ route('admin.mpp.index') }}?tahun=" + thn;
            } else {
                table.ajax.reload();
            }
        }

        function resetFilter() {
            $('#filter_tahun').val("{{ date('Y') }}");
            $('#filter_unit').val('').trigger('change');
            filterData();
        }

        function detail(id) {
            $.ajax({
                url: "{{ route('admin.mpp.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                },
                success: function(res) {
                    Swal.close();
                    $('#modal-container-approval').html(res.html);
                    $('#modal-approval').modal('show');
                },
                error: function(err) {
                    Swal.fire('Error', 'Gagal memuat detail pengajuan MPP', 'error');
                }
            });
        }
    </script>
@endsection
