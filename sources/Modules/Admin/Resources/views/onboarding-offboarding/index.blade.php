@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Design System Tokens === */
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-hover: #0c6170;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-onoff {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-onoff {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-onoff {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.15rem 1.25rem;
            box-shadow: 0 4px 14px rgba(9, 75, 84, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(9, 75, 84, 0.15);
        }

        .tsu-stat-card__icon {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.95;
            margin-bottom: 0.4rem;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
        }

        .tsu-stat-card__unit {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.85;
        }

        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.88;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Card Color Schemes */
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }
        .tsu-stat-card--onboard {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--tasks {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }
        .tsu-stat-card--offboard {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }

        /* === TSU Container Card & Underline Tabs === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        /* === Underline Nav Tabs (Matching Master Jabatan & Tunjangan) === */
        .card-tabs .card-header {
            background: #ffffff !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 0 1rem !important;
        }
        .tsu-tab-nav {
            border-bottom: none !important;
            margin-bottom: -2px;
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        .card-tabs .card-header .nav-tabs.tsu-tab-nav .nav-link,
        .tsu-tab-nav .nav-link,
        .nav-tabs .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748b !important;
            border: none !important;
            border-bottom: 3px solid transparent !important;
            background: transparent !important;
            border-radius: 0 !important;
            margin-bottom: -2px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: none !important;
            cursor: pointer;
        }
        .card-tabs .card-header .nav-tabs.tsu-tab-nav .nav-link:hover,
        .tsu-tab-nav .nav-link:hover,
        .nav-tabs .nav-link:hover {
            color: var(--tsu-primary, #094b54) !important;
            background: transparent !important;
            border-bottom: 3px solid transparent !important;
        }
        .card-tabs .card-header .nav-tabs.tsu-tab-nav .nav-link.active,
        .tsu-tab-nav .nav-link.active,
        .nav-tabs .nav-link.active {
            color: var(--tsu-primary, #094b54) !important;
            border-bottom: 3px solid var(--tsu-primary, #094b54) !important;
            background: transparent !important;
            font-weight: 700;
        }

        /* === Modern Table Styles === */
        .tsu-table-modern thead th {
            background: #f8fafc !important;
            color: var(--tsu-primary-dark, #094b54) !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            vertical-align: middle !important;
            padding: 0.75rem 1rem !important;
        }
        .tsu-table-modern tbody td {
            vertical-align: middle !important;
            font-size: 0.85rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* === Buttons & Controls === */
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
        .tsu-btn-primary-action {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }
        .tsu-btn-primary-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            color: #ffffff !important;
        }

        /* === DataTables Pagination & Filter === */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Pelaksanaan Onboarding & Offboarding Pegawai"
        subtitle="Pemantauan alur orientasi pegawai baru dan proses pelepasan serah terima tugas pegawai resign (Dosen & Tendik)"
        :icon="$menuIcon ?? 'fas fa-user-check'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.master-onboarding-offboarding.index') }}" class="btn btn-sm tsu-btn-reload mr-2" title="Kelola Master Checklist">
                <i class="fas fa-cog mr-1"></i> Kelola Master Tugas
            </a>
            <button type="button" class="btn tsu-btn-reload btn-sm" id="btn-reload" title="Refresh Data">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards Grid --}}
            <div class="tsu-stat-grid-onoff">
                {{-- Onboarding Berjalan --}}
                <div class="tsu-stat-card tsu-stat-card--onboard">
                    <i class="fas fa-user-clock tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Onboarding Berjalan</div>
                        <div class="tsu-stat-card__value">
                            <span id="stat-count-onb-berjalan">{{ number_format($onboardingBerjalanCount) }}</span>
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Masa Orientasi & Checklist Aktif
                    </div>
                </div>

                {{-- Riwayat Onboarding Selesai --}}
                <div class="tsu-stat-card tsu-stat-card--tasks">
                    <i class="fas fa-user-check tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Riwayat Onboarding</div>
                        <div class="tsu-stat-card__value">
                            <span id="stat-count-onb-riwayat">{{ number_format($riwayatOnboardingCount) }}</span>
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Checklist Tuntas (100%)
                    </div>
                </div>

                {{-- Offboarding Berjalan --}}
                <div class="tsu-stat-card tsu-stat-card--offboard">
                    <i class="fas fa-user-minus tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Offboarding Berjalan</div>
                        <div class="tsu-stat-card__value">
                            <span id="stat-count-off-berjalan">{{ number_format($offboardingBerjalanCount) }}</span>
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Proses Serah Terima & Aset
                    </div>
                </div>

                {{-- Riwayat Offboarding Selesai --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-clipboard-check tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Riwayat Offboarding</div>
                        <div class="tsu-stat-card__value">
                            <span id="stat-count-off-riwayat">{{ number_format($riwayatOffboardingCount) }}</span>
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Serah Terima Tuntas (100%)
                    </div>
                </div>
            </div>

            {{-- Main Underline Tab Card Container (4 Tabs) --}}
            <div class="card card-primary card-outline card-tabs tsu-card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs tsu-tab-nav" id="onoff-tabs" role="tablist">
                        {{-- Tab 1: Onboarding Berjalan --}}
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-onboarding-link" data-toggle="pill" href="#tab-onboarding" role="tab">
                                <i class="fas fa-user-clock mr-2 text-primary"></i> Onboarding (Berjalan)
                                <span class="badge badge-warning text-dark ml-2 px-2" id="badge-count-onb-berjalan">{{ $onboardingBerjalanCount }}</span>
                            </a>
                        </li>
                        {{-- Tab 2: Riwayat Onboarding (Selesai 100%) --}}
                        <li class="nav-item">
                            <a class="nav-link" id="tab-riwayat-onboarding-link" data-toggle="pill" href="#tab-riwayat-onboarding" role="tab">
                                <i class="fas fa-user-check mr-2 text-success"></i> Riwayat Onboarding
                                <span class="badge badge-success ml-2 px-2" id="badge-count-onb-riwayat">{{ $riwayatOnboardingCount }}</span>
                            </a>
                        </li>
                        {{-- Tab 3: Offboarding Berjalan --}}
                        <li class="nav-item">
                            <a class="nav-link" id="tab-offboarding-link" data-toggle="pill" href="#tab-offboarding" role="tab">
                                <i class="fas fa-user-minus mr-2 text-warning"></i> Offboarding (Berjalan)
                                <span class="badge badge-warning text-dark ml-2 px-2" id="badge-count-off-berjalan">{{ $offboardingBerjalanCount }}</span>
                            </a>
                        </li>
                        {{-- Tab 4: Riwayat Offboarding (Selesai 100%) --}}
                        <li class="nav-item">
                            <a class="nav-link" id="tab-riwayat-offboarding-link" data-toggle="pill" href="#tab-riwayat-offboarding" role="tab">
                                <i class="fas fa-clipboard-check mr-2 text-success"></i> Riwayat Offboarding
                                <span class="badge badge-success ml-2 px-2" id="badge-count-off-riwayat">{{ $riwayatOffboardingCount }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content" id="onoff-tabContent">
                        
                        {{-- ========================================================
                             TAB 1: ONBOARDING BERJALAN (< 100%)
                        ======================================================== --}}
                        <div class="tab-pane fade show active" id="tab-onboarding" role="tabpanel">
                            {{-- Filter Dosen vs Tendik --}}
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 bg-light rounded" style="border: 1px solid #e2e8f0;">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons" id="filter-tipe-onboarding">
                                    <label class="btn btn-sm btn-white active shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_onboarding" value="" checked> <i class="fas fa-users mr-1 text-primary"></i> Semua Pegawai Baru
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_onboarding" value="dosen"> <i class="fas fa-chalkboard-teacher mr-1 text-info"></i> Khusus Dosen Baru
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_onboarding" value="tendik"> <i class="fas fa-user-tie mr-1 text-success"></i> Khusus Tendik Baru
                                    </label>
                                </div>
                                <div class="small text-muted mt-2 mt-md-0">
                                    <i class="fas fa-info-circle text-info mr-1"></i> Menampilkan pegawai baru dengan checklist belum tuntas (< 100%)
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="table-onboarding-pegawai" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center text-nowrap">NO</th>
                                            <th width="32%">IDENTITAS PEGAWAI</th>
                                            <th width="14%" class="text-center text-nowrap">TGL BERGABUNG</th>
                                            <th width="24%">PROGRESS KELENGKAPAN</th>
                                            <th width="13%" class="text-center text-nowrap">STATUS</th>
                                            <th width="12%" class="text-center text-nowrap">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ========================================================
                             TAB 2: RIWAYAT ONBOARDING (LENGKAP 100%)
                        ======================================================== --}}
                        <div class="tab-pane fade" id="tab-riwayat-onboarding" role="tabpanel">
                            {{-- Filter Dosen vs Tendik --}}
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 bg-light rounded" style="border: 1px solid #e2e8f0;">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons" id="filter-tipe-riwayat-onboarding">
                                    <label class="btn btn-sm btn-white active shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_riwayat_onboarding" value="" checked> <i class="fas fa-users mr-1 text-success"></i> Semua Riwayat Selesai
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_riwayat_onboarding" value="dosen"> <i class="fas fa-chalkboard-teacher mr-1 text-info"></i> Dosen Tuntas
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_riwayat_onboarding" value="tendik"> <i class="fas fa-user-tie mr-1 text-success"></i> Tendik Tuntas
                                    </label>
                                </div>
                                <div class="small text-muted mt-2 mt-md-0">
                                    <i class="fas fa-check-double text-success mr-1"></i> Seluruh tugas orientasi &amp; berkas checklist telah selesai 100%
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="table-riwayat-onboarding-pegawai" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center text-nowrap">NO</th>
                                            <th width="35%">IDENTITAS PEGAWAI</th>
                                            <th width="15%" class="text-center text-nowrap">TGL BERGABUNG</th>
                                            <th width="15%" class="text-center text-nowrap">STATUS ORIENTASI</th>
                                            <th width="18%" class="text-center text-nowrap">WAKTU TUNTAS</th>
                                            <th width="12%" class="text-center text-nowrap">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ========================================================
                             TAB 3: OFFBOARDING BERJALAN (< 100%)
                        ======================================================== --}}
                        <div class="tab-pane fade" id="tab-offboarding" role="tabpanel">
                            {{-- Filter Dosen vs Tendik --}}
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 bg-light rounded" style="border: 1px solid #e2e8f0;">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons" id="filter-tipe-offboarding">
                                    <label class="btn btn-sm btn-white active shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_offboarding" value="" checked> <i class="fas fa-users mr-1 text-secondary"></i> Semua Pegawai Resign
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_offboarding" value="dosen"> <i class="fas fa-chalkboard-teacher mr-1 text-warning"></i> Offboarding Dosen
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_offboarding" value="tendik"> <i class="fas fa-user-tie mr-1 text-danger"></i> Offboarding Tendik
                                    </label>
                                </div>
                                <div class="small text-muted mt-2 mt-md-0">
                                    <i class="fas fa-info-circle text-warning mr-1"></i> Menampilkan pegawai resign dengan serah terima inventaris &amp; tugas belum selesai
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="table-offboarding-pegawai" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center text-nowrap">NO</th>
                                            <th width="35%">IDENTITAS PEGAWAI</th>
                                            <th width="28%">PROGRESS SERAH TERIMA</th>
                                            <th width="18%" class="text-center text-nowrap">STATUS</th>
                                            <th width="14%" class="text-center text-nowrap">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ========================================================
                             TAB 4: RIWAYAT OFFBOARDING (LENGKAP 100%)
                        ======================================================== --}}
                        <div class="tab-pane fade" id="tab-riwayat-offboarding" role="tabpanel">
                            {{-- Filter Dosen vs Tendik --}}
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 bg-light rounded" style="border: 1px solid #e2e8f0;">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons" id="filter-tipe-riwayat-offboarding">
                                    <label class="btn btn-sm btn-white active shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_riwayat_offboarding" value="" checked> <i class="fas fa-users mr-1 text-success"></i> Semua Riwayat Selesai
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_riwayat_offboarding" value="dosen"> <i class="fas fa-chalkboard-teacher mr-1 text-warning"></i> Dosen Selesai
                                    </label>
                                    <label class="btn btn-sm btn-white shadow-none border" style="font-weight: 600;">
                                        <input type="radio" name="tipe_riwayat_offboarding" value="tendik"> <i class="fas fa-user-tie mr-1 text-danger"></i> Tendik Selesai
                                    </label>
                                </div>
                                <div class="small text-muted mt-2 mt-md-0">
                                    <i class="fas fa-check-double text-success mr-1"></i> Seluruh serah terima inventaris, akun kampus &amp; administrasi telah tuntas 100%
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="table-riwayat-offboarding-pegawai" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center text-nowrap">NO</th>
                                            <th width="40%">IDENTITAS PEGAWAI</th>
                                            <th width="20%" class="text-center text-nowrap">STATUS SERAH TERIMA</th>
                                            <th width="20%" class="text-center text-nowrap">WAKTU TUNTAS</th>
                                            <th width="15%" class="text-center text-nowrap">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL CONTAINER CHECKLIST --}}
    <div class="modal fade" id="modal-checklist" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" id="modal-checklist-content" style="border-radius: 12px; overflow: hidden;">
                {{-- Loaded dynamically via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- DataTables & SweetAlert2 JS -->
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            let currentTipeOnboarding = '';
            let currentTipeRiwayatOnboarding = '';
            let currentTipeOffboarding = '';
            let currentTipeRiwayatOffboarding = '';

            // Common Language settings
            let dtLanguage = {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                search: "Cari Pegawai:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pegawai",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                emptyTable: "Tidak ada data pegawai",
                zeroRecords: "Tidak ditemukan pegawai yang sesuai"
            };

            // 1. Table Onboarding Berjalan
            let dtOnboarding = $('#table-onboarding-pegawai').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-onboarding') }}",
                    data: function(d) {
                        d.tipe_karyawan = currentTipeOnboarding;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'identitas', name: 'nama', className: 'align-middle' },
                    { data: 'tgl_bergabung_fmt', name: 'tgl_bergabung', className: 'text-center align-middle text-nowrap' },
                    { data: 'progress', name: 'progress', orderable: false, searchable: false, className: 'align-middle' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                ],
                language: dtLanguage
            });

            // 2. Table Riwayat Onboarding (Selesai 100%)
            let dtRiwayatOnboarding = null;
            $('#tab-riwayat-onboarding-link').on('shown.bs.tab', function() {
                if (!dtRiwayatOnboarding) {
                    dtRiwayatOnboarding = $('#table-riwayat-onboarding-pegawai').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        pageLength: 25,
                        ajax: {
                            url: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-riwayat-onboarding') }}",
                            data: function(d) {
                                d.tipe_karyawan = currentTipeRiwayatOnboarding;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'identitas', name: 'nama', className: 'align-middle' },
                            { data: 'tgl_bergabung_fmt', name: 'tgl_bergabung', className: 'text-center align-middle text-nowrap' },
                            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                            { data: 'tgl_selesai_fmt', name: 'tgl_selesai_fmt', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: dtLanguage
                    });
                } else {
                    dtRiwayatOnboarding.columns.adjust().responsive.recalc();
                }
            });

            // 3. Table Offboarding Berjalan
            let dtOffboarding = null;
            $('#tab-offboarding-link').on('shown.bs.tab', function() {
                if (!dtOffboarding) {
                    dtOffboarding = $('#table-offboarding-pegawai').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        pageLength: 25,
                        ajax: {
                            url: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-offboarding') }}",
                            data: function(d) {
                                d.tipe_karyawan = currentTipeOffboarding;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'identitas', name: 'nama', className: 'align-middle' },
                            { data: 'progress', name: 'progress', orderable: false, searchable: false, className: 'align-middle' },
                            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: dtLanguage
                    });
                } else {
                    dtOffboarding.columns.adjust().responsive.recalc();
                }
            });

            // 4. Table Riwayat Offboarding (Selesai 100%)
            let dtRiwayatOffboarding = null;
            $('#tab-riwayat-offboarding-link').on('shown.bs.tab', function() {
                if (!dtRiwayatOffboarding) {
                    dtRiwayatOffboarding = $('#table-riwayat-offboarding-pegawai').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        pageLength: 25,
                        ajax: {
                            url: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-riwayat-offboarding') }}",
                            data: function(d) {
                                d.tipe_karyawan = currentTipeRiwayatOffboarding;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'identitas', name: 'nama', className: 'align-middle' },
                            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                            { data: 'tgl_selesai_fmt', name: 'tgl_selesai_fmt', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: dtLanguage
                    });
                } else {
                    dtRiwayatOffboarding.columns.adjust().responsive.recalc();
                }
            });

            // Filter Radio Tipe Karyawan Change Handlers
            $('input[name="tipe_onboarding"]').on('change', function() {
                currentTipeOnboarding = $(this).val();
                if (dtOnboarding) dtOnboarding.ajax.reload();
            });

            $('input[name="tipe_riwayat_onboarding"]').on('change', function() {
                currentTipeRiwayatOnboarding = $(this).val();
                if (dtRiwayatOnboarding) dtRiwayatOnboarding.ajax.reload();
            });

            $('input[name="tipe_offboarding"]').on('change', function() {
                currentTipeOffboarding = $(this).val();
                if (dtOffboarding) dtOffboarding.ajax.reload();
            });

            $('input[name="tipe_riwayat_offboarding"]').on('change', function() {
                currentTipeRiwayatOffboarding = $(this).val();
                if (dtRiwayatOffboarding) dtRiwayatOffboarding.ajax.reload();
            });

            // Adjust columns on any tab switch
            $('a[data-toggle="pill"], a[data-toggle="tab"]').on('shown.bs.tab', function() {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            });

            // Function to reload all active tables
            function reloadAllTables() {
                if (dtOnboarding) dtOnboarding.ajax.reload(null, false);
                if (dtRiwayatOnboarding) dtRiwayatOnboarding.ajax.reload(null, false);
                if (dtOffboarding) dtOffboarding.ajax.reload(null, false);
                if (dtRiwayatOffboarding) dtRiwayatOffboarding.ajax.reload(null, false);
            }

            // Function to apply counter numbers to DOM in real-time with smooth transition
            function applyStats(stats) {
                if (!stats) return;

                function updateElement(selector, val) {
                    let $el = $(selector);
                    if ($el.length && $el.text() != val) {
                        $el.fadeOut(100, function() {
                            $(this).text(val).fadeIn(150);
                        });
                    }
                }

                updateElement('#badge-count-onb-berjalan', stats.onboarding_berjalan);
                updateElement('#stat-count-onb-berjalan', stats.onboarding_berjalan);

                updateElement('#badge-count-onb-riwayat', stats.riwayat_onboarding);
                updateElement('#stat-count-onb-riwayat', stats.riwayat_onboarding);

                updateElement('#badge-count-off-berjalan', stats.offboarding_berjalan);
                updateElement('#stat-count-off-berjalan', stats.offboarding_berjalan);

                updateElement('#badge-count-off-riwayat', stats.riwayat_offboarding);
                updateElement('#stat-count-off-riwayat', stats.riwayat_offboarding);
            }

            // Function to fetch real-time stats via AJAX
            function updateCounters() {
                $.ajax({
                    url: "{{ route('admin.pelaksanaan-onboarding-offboarding.stats') }}",
                    type: "GET",
                    cache: false,
                    success: function(res) {
                        applyStats(res);
                    }
                });
            }

            // Tombol Refresh Data
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                reloadAllTables();
                updateCounters();
                setTimeout(function() {
                    $btn.find('i').removeClass('fa-spin');
                }, 500);
            });

            // Saat Modal Checklist ditutup, pastikan tabel dan angka counter tetap up-to-date
            $('#modal-checklist').on('hidden.bs.modal', function() {
                reloadAllTables();
                updateCounters();
            });

            // Open Checklist Modal
            $('body').on('click', '.btn-modal-checklist', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-checklist').modal('show');
                $('#modal-checklist-content').html(
                    '<div class="text-center p-5">' +
                        '<div class="spinner-border text-primary" style="color: var(--tsu-primary, #094b54) !important;" role="status"></div>' +
                        '<p class="text-muted mt-2 mb-0" style="font-size: 0.88rem;">Memuat checklist pegawai...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-checklist-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-checklist-content').html(
                            '<div class="text-center p-4">' +
                                '<i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>' +
                                '<p class="text-danger font-weight-bold mb-0">Gagal memuat lembar checklist.</p>' +
                                '<small class="text-muted">Error ' + xhr.status + ': ' + (xhr.statusText || 'Terjadi kesalahan sistem') + '</small>' +
                            '</div>'
                        );
                    }
                });
            });

            // Checkbox Toggle Item
            $('body').on('change', '.chk-item-onoff', function() {
                let checkbox = $(this);
                let isChecked = checkbox.is(':checked') ? 1 : 0;
                let karyawanId = checkbox.data('karyawan-id');
                let masterId = checkbox.data('master-id');
                let tr = checkbox.closest('tr');

                $.ajax({
                    url: "{{ route('admin.pelaksanaan-onboarding-offboarding.toggle') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        data_dosen_tendik_id: karyawanId,
                        master_onboarding_offboarding_id: masterId,
                        is_completed: isChecked
                    },
                    success: function(res) {
                        if (res.success) {
                            if (isChecked) {
                                tr.find('.task-title').addClass('text-muted').css('text-decoration', 'line-through');
                            } else {
                                tr.find('.task-title').removeClass('text-muted').css('text-decoration', 'none');
                            }

                            // Reload semua tabel
                            reloadAllTables();

                            // Update angka badge di tab & kartu statistik secara real-time
                            if (res.stats) {
                                applyStats(res.stats);
                            } else {
                                updateCounters();
                            }

                            // Jika baru saja tuntas 100%, beri notifikasi perpindahan tab
                            if (res.is_all_completed && isChecked) {
                                let labelTarget = res.kategori === 'onboarding' ? 'Riwayat Onboarding' : 'Riwayat Offboarding';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Checklist Telah Lengkap (100%)!',
                                    text: 'Seluruh tugas orientasi/serah terima telah tuntas. Data pegawai berhasil dipindahkan ke tab ' + labelTarget + '.',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            }
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan sistem saat memperbarui status.', 'error');
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });
        });
    </script>
@endsection
