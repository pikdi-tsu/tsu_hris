@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables & Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-tunjangan {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-tunjangan {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-tunjangan {
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
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(9, 75, 84, 0.15);
        }
        .tsu-stat-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }
        .tsu-stat-card__label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.95;
            margin: 0;
        }
        .tsu-stat-card__icon-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
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
            opacity: 0.85;
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
        .tsu-stat-card--struktural {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--fungsional {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--keluarga {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
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

        /* === Underline Nav Tabs (Matching Riwayat Cuti, Jabatan, & MPP) === */
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
        :title="$title ?? 'Master Data Tunjangan Pegawai'"
        subtitle="Kelola matriks tarif tunjangan struktural, fungsional, dan ketentuan tunjangan keluarga (Tersambung Otomatis ke Payroll)"
        icon="fas fa-hand-holding-usd"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-reload btn-sm" id="btn-reload" title="Refresh Data">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards Grid (4 Columns) --}}
            <div class="tsu-stat-grid-tunjangan">
                {{-- Total Pos Tunjangan --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-coins tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Total Pos Tunjangan</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['total'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Pos</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Struktural & Fungsional Terdaftar
                    </div>
                </div>

                {{-- Tunjangan Struktural --}}
                <div class="tsu-stat-card tsu-stat-card--struktural">
                    <i class="fas fa-sitemap tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Tunjangan Struktural</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['struktural'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Jabatan</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Jabatan Struktural & Tugas Tambahan
                    </div>
                </div>

                {{-- Tunjangan Fungsional --}}
                <div class="tsu-stat-card tsu-stat-card--fungsional">
                    <i class="fas fa-graduation-cap tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Tunjangan Fungsional</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['fungsional'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Jenjang</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Jenjang Akademik Jafung Dosen
                    </div>
                </div>

                {{-- Tunjangan Keluarga --}}
                <div class="tsu-stat-card tsu-stat-card--keluarga">
                    <i class="fas fa-users tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Tunjangan Keluarga</div>
                        <div class="tsu-stat-card__value" style="font-size: 1.45rem;">
                            {{ $settingKeluarga->persen_suami_istri }}% + {{ $settingKeluarga->persen_anak }}%
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Suami/Istri & Maks. {{ $settingKeluarga->maksimal_anak }} Anak
                    </div>
                </div>
            </div>

            {{-- Card Panduan (Placed BELOW Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Tunjangan Pegawai"
                description="Master Tunjangan mengatur komponen pendapatan tetap dan variabel pegawai (Tunjangan Jabatan Struktural, Fungsional Dosen, Tunjangan Beras/Keluarga, serta Tunjangan Khusus)."
                :connections="[
                    ['label' => 'Penggajian (Payroll)', 'route' => 'admin.payroll.index', 'icon' => 'fas fa-money-check-alt'],
                    ['label' => 'Penetapan di Data Pegawai', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-user-tag'],
                    ['label' => 'Master Jabatan Terkait', 'route' => 'admin.master-jabatan.index', 'icon' => 'fas fa-sitemap']
                ]"
                impact="Besaran nominal tunjangan di sini otomatis masuk ke rincian penghasilan kotor (Gross Earnings) pada slip gaji bulanan pegawai yang memenuhi kriteria jabatan atau fungsionalnya."
            />

            {{-- Card Container with Underline Tabs (Matching Master Jabatan) --}}
            <div class="card card-primary card-outline card-tabs tsu-card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs tsu-tab-nav" id="tunjanganTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="tab-struktural" data-toggle="tab" href="#content-struktural" role="tab" aria-controls="content-struktural" aria-selected="true">
                                <i class="fas fa-sitemap mr-2"></i> Tunjangan Struktural
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tab-fungsional" data-toggle="tab" href="#content-fungsional" role="tab" aria-controls="content-fungsional" aria-selected="false">
                                <i class="fas fa-graduation-cap mr-2"></i> Tunjangan Fungsional
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tab-keluarga" data-toggle="tab" href="#content-keluarga" role="tab" aria-controls="content-keluarga" aria-selected="false">
                                <i class="fas fa-users mr-2"></i> Tunjangan Keluarga & Anak
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-3">
                    <div class="tab-content" id="tunjanganTabsContent">
                        
                        {{-- 1. TAB TUNJANGAN STRUKTURAL --}}
                        <div class="tab-pane fade show active" id="content-struktural" role="tabpanel" aria-labelledby="tab-struktural">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.92rem;">
                                        Matriks Tunjangan Struktural & Tugas Tambahan
                                    </h6>
                                    <small class="text-muted">Nominal tersimpan terpisah di tabel finansial <code>master_pengaturan_tunjangans</code>.</small>
                                </div>
                                <div>
                                    @can('admin:master-tunjangan:create')
                                        <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal" data-url="{{ route('admin.master-tunjangan.struktural.create') }}">
                                            <i class="fas fa-plus mr-1"></i> Tambah Tunjangan Struktural
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="table-tunjangan-struktural" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">NO</th>
                                            <th>NAMA JABATAN STRUKTURAL / TUGAS TAMBAHAN</th>
                                            <th width="16%" class="text-right">NOMINAL DASAR (100%)</th>
                                            <th width="10%" class="text-center">PERSEN</th>
                                            <th width="18%" class="text-right">TUNJANGAN DIBAYAR / BULAN</th>
                                            <th width="20%">KETERANGAN</th>
                                            <th width="12%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 2. TAB TUNJANGAN FUNGSIONAL --}}
                        <div class="tab-pane fade" id="content-fungsional" role="tabpanel" aria-labelledby="tab-fungsional">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.92rem;">
                                        Matriks Tunjangan Jabatan Fungsional Dosen
                                    </h6>
                                    <small class="text-muted">Nominal tunjangan per jenjang jabatan fungsional (Tenaga Pengajar, Asisten Ahli, Lektor, Lektor Kepala, Guru Besar).</small>
                                </div>
                                <div>
                                    @can('admin:master-tunjangan:create')
                                        <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal" data-url="{{ route('admin.master-tunjangan.fungsional.create') }}">
                                            <i class="fas fa-plus mr-1"></i> Tambah Jenjang Fungsional
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="table-tunjangan-fungsional" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">NO</th>
                                            <th width="10%" class="text-center">KODE</th>
                                            <th>JENJANG JABATAN FUNGSIONAL</th>
                                            <th width="20%" class="text-right">NOMINAL TUNJANGAN / BULAN</th>
                                            <th width="25%">KETERANGAN</th>
                                            <th width="12%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 3. TAB TUNJANGAN KELUARGA & ANAK --}}
                        <div class="tab-pane fade" id="content-keluarga" role="tabpanel" aria-labelledby="tab-keluarga">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.92rem;">
                                        Ketentuan & Rumus Perhitungan Tunjangan Keluarga (Suami/Istri & Anak)
                                    </h6>
                                    <small class="text-muted">Parameter persentase dan batasan kuota anak yang otomatis diterapkan pada formula penggajian (Payroll).</small>
                                </div>
                                <div>
                                    <span class="badge badge-info px-3 py-2" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                                        <i class="fas fa-calculator mr-1"></i> Terhubung ke Payroll Otomatis
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                {{-- KOLOM KIRI: FORM PENGATURAN RUMUS --}}
                                <div class="col-lg-7 mb-3">
                                    <div class="p-4 bg-white border" style="border-radius: 12px; border-color: #e2e8f0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                        <div class="d-flex align-items-center pb-3 mb-3 border-bottom">
                                            <div class="d-flex align-items-center justify-content-center text-white mr-3" style="width: 38px; height: 38px; border-radius: 8px; background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                                                <i class="fas fa-sliders-h" style="font-size: 1rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.95rem;">
                                                    Parameter Rumus Tunjangan
                                                </h6>
                                                <small class="text-muted">Atur persentase hak tunjangan pasangan dan kuota tanggungan anak.</small>
                                            </div>
                                        </div>

                                        <form action="{{ route('admin.master-tunjangan.keluarga.update') }}" method="POST" id="form-tunjangan-keluarga">
                                            @csrf
                                            
                                            {{-- Persentase Pasangan --}}
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                                                    <span>Persentase Tunjangan Pasangan (Suami/Istri) <span class="text-danger">*</span></span>
                                                    <small class="text-muted font-weight-normal">Status perkawinan: Menikah</small>
                                                </label>
                                                <div class="input-group" style="max-width: 200px;">
                                                    <input type="number" step="0.1" min="0" max="100" id="input_persen_suami_istri" name="persen_suami_istri" class="form-control font-weight-bold text-dark text-right" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-color: #cbd5e1;" value="{{ $settingKeluarga->persen_suami_istri }}" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text font-weight-bold" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1; background: #f8fafc; color: #094b54;">%</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted" style="font-size: 0.75rem;">Persentase dari basis gaji yang diberikan untuk suami/istri sah.</small>
                                            </div>

                                            {{-- Persentase Anak --}}
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                                                    <span>Persentase Tunjangan Anak (Per Anak) <span class="text-danger">*</span></span>
                                                    <small class="text-muted font-weight-normal">Anak sah yang terdaftar</small>
                                                </label>
                                                <div class="input-group" style="max-width: 200px;">
                                                    <input type="number" step="0.1" min="0" max="100" id="input_persen_anak" name="persen_anak" class="form-control font-weight-bold text-dark text-right" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-color: #cbd5e1;" value="{{ $settingKeluarga->persen_anak }}" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text font-weight-bold" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1; background: #f8fafc; color: #094b54;">%</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted" style="font-size: 0.75rem;">Persentase dari basis gaji yang diberikan untuk setiap 1 orang anak.</small>
                                            </div>

                                            {{-- Maksimal Anak --}}
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                                                    <span>Maksimal Jumlah Anak Ditanggung <span class="text-danger">*</span></span>
                                                    <small class="text-muted font-weight-normal">Batas kuota anak</small>
                                                </label>
                                                <div class="input-group" style="max-width: 200px;">
                                                    <input type="number" min="0" max="10" id="input_maksimal_anak" name="maksimal_anak" class="form-control font-weight-bold text-dark text-right" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-color: #cbd5e1;" value="{{ $settingKeluarga->maksimal_anak }}" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text font-weight-bold" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1; background: #f8fafc; color: #094b54;">Anak</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted" style="font-size: 0.75rem;">Batas maksimum anak sah yang dihitung dalam tunjangan payroll.</small>
                                            </div>

                                            {{-- Basis Perhitungan --}}
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                    Basis / Dasar Perhitungan Tunjangan <span class="text-danger">*</span>
                                                </label>
                                                <select name="basis_perhitungan" id="input_basis_perhitungan" class="form-control font-weight-bold text-dark" style="border-radius: 8px; border-color: #cbd5e1;" required>
                                                    <option value="gaji_tetap" {{ $settingKeluarga->basis_perhitungan === 'gaji_tetap' ? 'selected' : '' }}>
                                                        Gaji Tetap Dasar (Gaji Pokok + Tunjangan Fungsional + Tunjangan Struktural)
                                                    </option>
                                                    <option value="gaji_pokok" {{ $settingKeluarga->basis_perhitungan === 'gaji_pokok' ? 'selected' : '' }}>
                                                        Gaji Pokok Saja
                                                    </option>
                                                </select>
                                                <small class="form-text text-muted" style="font-size: 0.75rem;">Nilai nominal yang dijadikan acuan dasar pengali persentase.</small>
                                            </div>

                                            {{-- Catatan / Keterangan --}}
                                            <div class="form-group mb-4">
                                                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                                    Catatan / Keterangan Kebijakan SK
                                                </label>
                                                <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Contoh: Berdasarkan SK Rektor No... Ketentuan Tunjangan Keluarga Universitas TSU">{{ $settingKeluarga->keterangan }}</textarea>
                                            </div>

                                            <div class="pt-3 border-top d-flex justify-content-end">
                                                <button type="submit" class="btn tsu-btn-primary-action px-4 font-weight-bold shadow-sm" style="border-radius: 8px;">
                                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan Tunjangan Keluarga
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN: RINGKASAN FORMULA & SIMULASI INTERAKTIF --}}
                                <div class="col-lg-5 mb-3">
                                    {{-- Panel Formula --}}
                                    <div class="p-4 bg-white border mb-3" style="border-radius: 12px; border-color: #e2e8f0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                        <div class="d-flex align-items-center pb-3 mb-3 border-bottom">
                                            <div class="d-flex align-items-center justify-content-center text-white mr-3" style="width: 38px; height: 38px; border-radius: 8px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);">
                                                <i class="fas fa-calculator" style="font-size: 1rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 0.95rem;">
                                                    Formula Perhitungan Payroll
                                                </h6>
                                                <small class="text-muted">Rumus baku yang diterapkan pada payroll.</small>
                                            </div>
                                        </div>

                                        <div class="mb-3 p-3" style="background: #f8fafc; border-radius: 8px; border-left: 3px solid #0284c7;">
                                            <div class="font-weight-bold text-dark mb-1" style="font-size: 0.84rem;">1. Tunjangan Pasangan (Suami/Istri)</div>
                                            <code class="d-block p-2 text-dark font-weight-bold" style="background: #e2e8f0; border-radius: 6px; font-size: 0.8rem;">
                                                = <span id="formula_pasangan_pct">{{ $settingKeluarga->persen_suami_istri }}</span>% × Basis Gaji
                                            </code>
                                        </div>

                                        <div class="mb-3 p-3" style="background: #f8fafc; border-radius: 8px; border-left: 3px solid #047857;">
                                            <div class="font-weight-bold text-dark mb-1" style="font-size: 0.84rem;">2. Tunjangan Anak (Maks. <span id="formula_maks_anak">{{ $settingKeluarga->maksimal_anak }}</span> Anak)</div>
                                            <code class="d-block p-2 text-dark font-weight-bold" style="background: #e2e8f0; border-radius: 6px; font-size: 0.8rem;">
                                                = Jml Anak × <span id="formula_anak_pct">{{ $settingKeluarga->persen_anak }}</span>% × Basis Gaji
                                            </code>
                                        </div>

                                        <div class="p-3" style="background: #f8fafc; border-radius: 8px; border-left: 3px solid #094b54;">
                                            <div class="font-weight-bold text-dark mb-1" style="font-size: 0.84rem;">3. Total Tunjangan Keluarga</div>
                                            <code class="d-block p-2 text-dark font-weight-bold" style="background: #e2e8f0; border-radius: 6px; font-size: 0.8rem;">
                                                = Tunjangan Pasangan + Tunjangan Anak
                                            </code>
                                        </div>
                                    </div>

                                    {{-- Panel Simulasi Payroll Live --}}
                                    <div class="p-4 border text-dark" style="border-radius: 12px; background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%); border-color: #99f6e4 !important;">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="fas fa-coins mr-2" style="font-size: 1.1rem; color: #0f766e;"></i>
                                            <h6 class="font-weight-bold mb-0" style="color: #0f766e; font-size: 0.95rem;">
                                                Simulasi Otomatis Payroll
                                            </h6>
                                        </div>
                                        <p class="small text-muted mb-3">
                                            Contoh estimasi tunjangan jika seorang karyawan memiliki <strong>1 Istri</strong> dan <strong>2 Anak</strong> dengan asumsi <strong>Basis Gaji Rp 5.000.000</strong>:
                                        </p>

                                        <div class="bg-white p-3 mb-2 border" style="border-radius: 8px; border-color: #ccfbf1 !important;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small font-weight-bold text-muted">Tunjangan Pasangan (<span id="sim_pct_pasangan">{{ $settingKeluarga->persen_suami_istri }}</span>%):</span>
                                                <strong class="text-dark" id="sim_val_pasangan">Rp {{ number_format(5000000 * ($settingKeluarga->persen_suami_istri / 100), 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small font-weight-bold text-muted">Tunjangan Anak (2 × <span id="sim_pct_anak">{{ $settingKeluarga->persen_anak }}</span>%):</span>
                                                <strong class="text-dark" id="sim_val_anak">Rp {{ number_format(5000000 * (min(2, $settingKeluarga->maksimal_anak) * $settingKeluarga->persen_anak / 100), 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="border-top mt-2 pt-2 d-flex justify-content-between align-items-center">
                                                <span class="font-weight-bold" style="color: #094b54; font-size: 0.88rem;">Total Tunjangan / Bulan:</span>
                                                <span class="font-weight-bold" id="sim_val_total" style="color: #047857; font-size: 1.05rem;">
                                                    Rp {{ number_format((5000000 * ($settingKeluarga->persen_suami_istri / 100)) + (5000000 * (min(2, $settingKeluarga->maksimal_anak) * $settingKeluarga->persen_anak / 100)), 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <small class="d-block text-muted" style="font-size: 0.72rem; line-height: 1.35;">
                                            <i class="fas fa-info-circle mr-1 text-info"></i> Perhitungan riil tiap karyawan menggunakan data keluarga aktif dan basis gaji masing-masing pada modul Payroll.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-tunjangan" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" id="modal-tunjangan-content" style="border-radius: 12px; overflow: hidden;">
                {{-- Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- DataTables, Select2 & SweetAlert2 JS -->
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // 1. DataTables Struktural
            var tableStruktural = $('#table-tunjangan-struktural').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 25,
                ajax: "{{ route('admin.master-tunjangan.struktural.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle'},
                    {data: 'nama_display', name: 'nama_tunjangan', className: 'align-middle'},
                    {data: 'nominal_dasar_formatted', name: 'nominal_dasar', className: 'text-right align-middle'},
                    {data: 'persen_bayar_badge', name: 'persen_bayar', className: 'text-center align-middle'},
                    {data: 'nominal_formatted', name: 'nominal_tunjangan', className: 'text-right align-middle'},
                    {data: 'keterangan', name: 'keterangan', className: 'align-middle small text-muted'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap'},
                ],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                    search: "Cari Struktural:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tunjangan",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data tunjangan struktural yang tersedia",
                    zeroRecords: "Tidak ditemukan data yang sesuai"
                }
            });

            // 2. DataTables Fungsional
            var tableFungsional = $('#table-tunjangan-fungsional').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.master-tunjangan.fungsional.json') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle'},
                    {data: 'kode_badge', name: 'kode', className: 'text-center align-middle'},
                    {data: 'nama_display', name: 'nama_tunjangan', className: 'align-middle'},
                    {data: 'nominal_formatted', name: 'nominal_tunjangan', className: 'text-right align-middle'},
                    {data: 'keterangan', name: 'keterangan', className: 'align-middle small text-muted'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap'},
                ],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                    search: "Cari Fungsional:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ jenjang",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data tunjangan fungsional yang tersedia",
                    zeroRecords: "Tidak ditemukan data yang sesuai"
                }
            });

            // Adjust table columns on tab shown
            $('a[data-toggle="tab"], a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            // Tombol Refresh Data
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                tableStruktural.ajax.reload(null, false);
                tableFungsional.ajax.reload(function() {
                    setTimeout(function() {
                        $btn.find('i').removeClass('fa-spin');
                    }, 400);
                }, false);
            });

            // Show Modal Create / Edit
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-tunjangan').modal('show');
                $('#modal-tunjangan-content').html(
                    '<div class="text-center p-5">' +
                        '<div class="spinner-border text-primary" style="color: var(--tsu-primary, #094b54) !important;" role="status"></div>' +
                        '<p class="text-muted mt-2 mb-0" style="font-size: 0.88rem;">Memuat formulir...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-tunjangan-content').html(res);
                        if ($.fn.select2) {
                            $('#modal-tunjangan-content select.select2').select2({
                                dropdownParent: $('#modal-tunjangan'),
                                width: '100%'
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#modal-tunjangan-content').html(
                            '<div class="text-center p-4">' +
                                '<i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>' +
                                '<p class="text-danger font-weight-bold mb-0">Gagal memuat formulir.</p>' +
                                '<small class="text-muted">Error ' + xhr.status + ': ' + (xhr.statusText || 'Terjadi kesalahan sistem') + '</small>' +
                            '</div>'
                        );
                    }
                });
            });

            // Submit Form via AJAX (Modal Create & Edit)
            $('body').on('submit', '#modal-tunjangan form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method') || 'POST';
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        $('#modal-tunjangan').modal('hide');
                        tableStruktural.ajax.reload(null, false);
                        tableFungsional.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Data tunjangan berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        var errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            html: errorMsg
                        });
                    }
                });
            });

            // Delete Action via AJAX
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name') || 'item ini';

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    html: `Apakah Anda yakin ingin menghapus data tarif: <br><strong>${name}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu beberapa saat.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(res) {
                                tableStruktural.ajax.reload(null, false);
                                tableFungsional.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus',
                                    text: res.message || 'Data tarif berhasil dihapus.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                var errorMsg = xhr.responseJSON?.message || 'Gagal menghapus data.';
                                Swal.fire('Gagal', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // Submit Form Tunjangan Keluarga via AJAX
            $('#form-tunjangan-keluarga').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Pengaturan tunjangan keluarga berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        var errorMsg = xhr.responseJSON?.message || 'Gagal menyimpan pengaturan.';
                        Swal.fire('Gagal', errorMsg, 'error');
                    }
                });
            });

            // Live Update Simulasi & Formula Tunjangan Keluarga
            function updateSimulasiKeluarga() {
                var pIstri = parseFloat($('#input_persen_suami_istri').val()) || 0;
                var pAnak = parseFloat($('#input_persen_anak').val()) || 0;
                var maxAnak = parseInt($('#input_maksimal_anak').val()) || 0;

                // Update formula display
                $('#formula_pasangan_pct').text(pIstri);
                $('#formula_anak_pct').text(pAnak);
                $('#formula_maks_anak').text(maxAnak);
                $('#sim_pct_pasangan').text(pIstri);
                $('#sim_pct_anak').text(pAnak);

                // Hitung simulasi (Basis: Rp 5.000.000, 1 istri, 2 anak)
                var basis = 5000000;
                var valIstri = Math.round(basis * (pIstri / 100));
                var hitungAnak = Math.min(2, maxAnak);
                var valAnak = Math.round(basis * (hitungAnak * pAnak / 100));
                var total = valIstri + valAnak;

                function formatRupiah(n) {
                    return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }

                $('#sim_val_pasangan').text(formatRupiah(valIstri));
                $('#sim_val_anak').text(formatRupiah(valAnak));
                $('#sim_val_total').text(formatRupiah(total));
            }

            $('#input_persen_suami_istri, #input_persen_anak, #input_maksimal_anak').on('input', updateSimulasiKeluarga);
        });
    </script>
@endsection
