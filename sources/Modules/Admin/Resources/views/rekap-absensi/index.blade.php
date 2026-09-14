@extends('system::template.admin.header')
@section('title', $title ?? 'Rekap Data Absensi')

@section('css')
    <style>
        /* === TSU Color Tokens === */
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #07383f;
            --tsu-primary-light: #cce6e9;
            --tsu-accent-green: #047857;
            --tsu-accent-amber: #b45309;
            --tsu-accent-blue: #0284c7;
            --tsu-bg-gray: #f8fafc;
            --tsu-border-gray: #e2e8f0;
            --tsu-radius: 8px;
            --tsu-radius-lg: 12px;
        }

        /* === Stat Cards Grid === */
        .tsu-stat-grid-rekap {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-rekap {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-rekap {
                grid-template-columns: 1fr;
            }
        }

        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .tsu-stat-card__icon {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.2rem;
            opacity: 0.15;
            pointer-events: none;
        }

        .tsu-stat-card__title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
            opacity: 0.92;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.3rem;
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

        /* Stat Card Variations */
        .tsu-stat-card--pegawai {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }

        .tsu-stat-card--transport {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        .tsu-stat-card--periode {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }

        .tsu-stat-card--log {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }

        /* === Container Card === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .tsu-card__header {
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            padding: 1.1rem 1.4rem;
        }

        .tsu-card__title {
            color: var(--tsu-primary-dark, #07383f);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            margin: 0;
        }

        /* === Modern Sub-Bar Info === */
        .tsu-subbar-info {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.4rem;
        }

        /* === Table Styling === */
        .tsu-rekap-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .tsu-rekap-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            padding: 0.85rem 0.65rem;
            border: 1px solid #e2e8f0;
            border-top: none;
        }

        .tsu-rekap-table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            padding: 0.8rem 0.65rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: background 0.15s ease;
        }

        .tsu-rekap-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* === Badges without Icons === */
        .tsu-badge-soft {
            display: inline-block;
            padding: 0.28rem 0.65rem;
            font-size: 0.74rem;
            font-weight: 600;
            border-radius: 6px;
            line-height: 1.2;
        }

        .tsu-badge-valid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            font-weight: 700;
        }

        .tsu-badge-cuti {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-weight: 700;
        }

        .tsu-badge-izin {
            background: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #d8b4fe;
            font-weight: 700;
        }

        .tsu-badge-alpha {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            font-weight: 700;
        }

        /* === Action Buttons === */
        .tsu-btn-detail {
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border: 1px solid #0284c7;
            color: #0284c7;
            background: #f0f9ff;
            transition: all 0.15s ease;
        }

        .tsu-btn-detail:hover {
            background: #0284c7;
            color: #ffffff;
        }

        .tsu-btn-pdf {
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border: 1px solid #dc2626;
            color: #dc2626;
            background: #fef2f2;
            transition: all 0.15s ease;
        }

        .tsu-btn-pdf:hover {
            background: #dc2626;
            color: #ffffff;
        }

        /* === Header Buttons === */
        .tsu-btn-filter {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%) !important;
            border: none !important;
            color: #ffffff !important;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }

        .tsu-btn-filter:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(9, 75, 84, 0.3);
            color: #ffffff !important;
        }

        .tsu-btn-hitung {
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #0284c7;
            color: #0284c7;
            background: #f0f9ff;
            transition: all 0.2s ease;
        }

        .tsu-btn-hitung:hover {
            background: #0284c7;
            color: #ffffff;
        }

        .tsu-btn-export {
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #047857;
            color: #047857;
            background: #f0fdf4;
            transition: all 0.2s ease;
        }

        .tsu-btn-export:hover {
            background: #047857;
            color: #ffffff;
        }

        .tsu-btn-zip {
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #dc2626;
            color: #dc2626;
            background: #fef2f2;
            transition: all 0.2s ease;
        }

        .tsu-btn-zip:hover {
            background: #dc2626;
            color: #ffffff;
        }

        .tsu-btn-update-periode {
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #b45309;
            color: #b45309;
            background: #fffbeb;
            transition: all 0.2s ease;
        }

        .tsu-btn-update-periode:hover {
            background: #b45309;
            color: #ffffff;
        }

        /* === Modal Polish === */
        .tsu-modal-header {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%);
            color: #ffffff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1rem 1.4rem;
        }

        .tsu-modal-header .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
        }

        .tsu-modal-header .close:hover {
            opacity: 1;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Rekap Data Absensi'"
        subtitle="Baseline Presensi, Rekapitulasi Kehadiran &amp; Kalkulasi Insentif Transport"
        icon="fas fa-calendar-check"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Filter Periode --}}
            <button type="button" class="btn btn-sm tsu-btn-filter mr-1" id="btnFilter" title="Filter Periode / Tanggal Cut-off">
                <i class="fas fa-filter mr-1"></i> Filter Periode
            </button>

            {{-- Hitung Ulang Validitas --}}
            <button type="button" class="btn btn-sm tsu-btn-hitung mr-1" id="btnKalkulasi" title="Hitung Ulang Durasi &amp; Validitas Presensi">
                <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang Validitas
            </button>

            {{-- Export Rekap Excel --}}
            <button type="button" class="btn btn-sm tsu-btn-export mr-1" id="btnExportExcel" title="Export Rekap Presensi &amp; Payroll Transport (Excel)">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>

            {{-- Download Semua Slip ZIP --}}
            <button type="button" class="btn btn-sm tsu-btn-zip mr-1" id="btnDownloadAllSlip" title="Download Slip Presensi Semua Pegawai (ZIP)">
                <i class="fas fa-file-archive mr-1"></i> Download Slip (ZIP)
            </button>

            {{-- Update Periode --}}
            <button type="button" class="btn btn-sm tsu-btn-update-periode" id="updateperiode" title="Update Periode Absensi">
                <i class="fas fa-calendar-alt mr-1"></i> Update Periode
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards: Metrics Ringkasan Rekap Presensi --}}
            <div class="tsu-stat-grid-rekap">
                {{-- Total Pegawai Ber-PIN --}}
                <div class="tsu-stat-card tsu-stat-card--pegawai">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="tsu-stat-card__title">Pegawai Terdaftar PIN</div>
                    <div class="tsu-stat-card__value">{{ $stats['total_karyawan_pin'] ?? 0 }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Terhubung dengan mesin absensi</div>
                </div>

                {{-- Tarif Transport Kehadiran --}}
                <div class="tsu-stat-card tsu-stat-card--transport">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="tsu-stat-card__title">Tarif Transport</div>
                    <div class="tsu-stat-card__value">Rp {{ number_format($defaultNominal, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Per hari kehadiran valid (1.0)</div>
                </div>

                {{-- Periode Terpilih --}}
                <div class="tsu-stat-card tsu-stat-card--periode">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Periode Terpilih</div>
                    <div class="tsu-stat-card__value" id="statCardPeriode" style="font-size: 1.45rem;">
                        {{ $bulan[$defaultBulan] ?? 'Bulan' }} {{ $defaultTahun }}
                    </div>
                    <div class="tsu-stat-card__subtext">Rentang aktif kalkulasi presensi</div>
                </div>

                {{-- Total Arsip Log --}}
                <div class="tsu-stat-card tsu-stat-card--log">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="tsu-stat-card__title">Riwayat Log Mesin</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_logs'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Log</span></div>
                    <div class="tsu-stat-card__subtext">{{ $stats['total_shifts'] ?? 0 }} master pola shift aktif</div>
                </div>
            </div>

            {{-- Table Rekapitulasi Presensi Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title">
                            Daftar Rekapitulasi Presensi Pegawai
                        </h5>
                        <div class="text-muted small mt-1">
                            Akumulasi hari kehadiran valid, perizinan, cuti tahunan, dan ketidakhadiran per pegawai
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="tsu-badge-soft tsu-badge-valid">
                            Transport: Rp {{ number_format($defaultNominal, 0, ',', '.') }} / hari valid
                        </span>
                    </div>
                </div>

                {{-- Sub-bar Filter Status --}}
                <div class="tsu-subbar-info d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small mr-2">Periode Aktif:</span>
                        <strong class="text-dark font-weight-bold" id="labelPeriodeAktif" style="font-size: 0.9rem;">
                            {{ $bulan[$defaultBulan] ?? 'Bulan' }} {{ $defaultTahun }}
                        </strong>
                    </div>
                    <div class="text-muted small mt-1 mt-md-0">
                        Klik <strong>Detail</strong> pada baris pegawai untuk memeriksa catatan jam scan harian
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-rekap-absensi" class="table tsu-rekap-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">No</th>
                                    <th style="width: 80px; text-align: center;">PIN</th>
                                    <th style="min-width: 240px; text-align: left;">Nama Karyawan</th>
                                    <th style="width: 140px; text-align: center;">Akumulasi Validasi</th>
                                    <th style="width: 100px; text-align: center;">Cuti (CT)</th>
                                    <th style="width: 100px; text-align: center;">Izin (I)</th>
                                    <th style="width: 100px; text-align: center;">Alpha (A)</th>
                                    <th style="width: 140px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL FILTER PERIODE --}}
    <div class="modal fade" id="modal-filter">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-filter mr-2"></i> Filter Data Presensi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-dark">Tipe Filter Periode</label>
                        <select class="form-control" id="filter_tipe">
                            <option value="bulan">Berdasarkan Bulan &amp; Tahun</option>
                            <option value="custom">Rentang Tanggal Cut-off (Custom)</option>
                        </select>
                    </div>

                    <div id="filter_bulan_section">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label class="font-weight-bold small text-dark">Bulan <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="filter_bulan">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 form-group">
                                <label class="font-weight-bold small text-dark">Tahun <span class="text-danger">*</span></label>
                                @php $tahun = date('Y'); @endphp
                                <select class="form-control select2" id="filter_tahun">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $defaultTahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="filter_custom_section" style="display: none;">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label class="font-weight-bold small text-dark">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="filter_start_date">
                            </div>
                            <div class="col-6 form-group">
                                <label class="font-weight-bold small text-dark">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="filter_end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn tsu-btn-filter px-4" id="btnApplyFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL LOG HARIAN PRESENSI --}}
    <div class="modal fade" id="modal-detail-presensi" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-calendar-alt mr-2"></i> Rincian Harian Presensi: <span id="detailNamaKaryawan"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="detailContent">
                    {{-- Loaded dynamically via AJAX --}}
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KOREKSI JAM KERJA HARIAN --}}
    <div class="modal fade" id="modal-edit-harian" tabindex="-1" role="dialog" style="z-index: 1060;">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-edit mr-2"></i> Koreksi Jam Kerja Harian
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditHarian">
                    @csrf
                    <input type="hidden" name="absensi_id" id="edit_harian_absensi_id">
                    <input type="hidden" name="pin" id="edit_harian_pin">
                    <input type="hidden" name="tanggal_absen" id="edit_harian_tanggal">

                    <div class="modal-body p-4">
                        <div class="p-3 rounded mb-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                            <div class="small font-weight-bold" style="color: var(--tsu-primary, #094b54);" id="edit_harian_info_karyawan">-</div>
                            <div class="font-weight-bold text-dark h6 mb-0 mt-1" id="edit_harian_info_tanggal">-</div>
                        </div>

                        {{-- Panel Bantuan Quick Action Pindahkan Scan 3 / Scan 4 --}}
                        <div id="panelQuickMoveSection" style="display: none;" class="mb-3">
                            <div class="card border-info mb-0" style="background-color: #f0f7fd; border: 1px solid #b8daff !important;">
                                <div class="card-header bg-info text-white py-1 px-2 small font-weight-bold">
                                    <i class="fas fa-magic mr-1"></i> Bantuan Pindah Jam Cepat
                                </div>
                                <div class="card-body p-2" id="quickMoveButtonsContainer">
                                    {{-- Dynamically populated --}}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-success">
                                        Scan 1 (Jam Masuk)
                                    </label>
                                    <input type="text" name="scan_1" id="edit_harian_scan_1" class="form-control text-center font-weight-bold text-dark" placeholder="00:00:00" style="font-size: 1rem;">
                                    <small class="text-muted font-weight-semibold">Format: JJ:MM:DD</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-danger">
                                        Scan 2 (Jam Pulang)
                                    </label>
                                    <input type="text" name="scan_2" id="edit_harian_scan_2" class="form-control text-center font-weight-bold text-dark" placeholder="00:00:00" style="font-size: 1rem;">
                                    <small class="text-muted font-weight-semibold">Format: JJ:MM:DD</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-dark">Scan 3</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="scan_3" id="edit_harian_scan_3" class="form-control text-center text-dark font-weight-bold" placeholder="-">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-clear-scan" type="button" data-target="#edit_harian_scan_3" title="Kosongkan"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-dark">Scan 4</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="scan_4" id="edit_harian_scan_4" class="form-control text-center text-dark font-weight-bold" placeholder="-">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary btn-clear-scan" type="button" data-target="#edit_harian_scan_4" title="Kosongkan"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert py-2 px-3 mt-3 mb-0" style="background-color: #f0fdfa !important; border: 1px solid #ccfbf1 !important; border-radius: 6px;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-lg mr-2" style="color: var(--tsu-primary, #094b54);"></i>
                                <div class="small font-weight-semibold" style="color: #0f766e !important; line-height: 1.4;">
                                    Jika Scan 3/4 dipindahkan ke Scan 1/2, sistem otomatis mengosongkannya agar kalkulasi presensi akurat.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm tsu-btn-filter px-3" id="btnSubmitEditHarian">
                            <i class="fas fa-save mr-1"></i> Simpan &amp; Kalkulasi Ulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL UPDATE PERIODE --}}
    <div class="modal fade" id="modal-update">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-calendar-alt mr-2"></i> Update Periode Absensi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.rekap-absensi.updateperiode') }}" method="POST" id="formUpdate">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group row mb-3">
                            <label class="col-sm-4 col-form-label font-weight-bold small text-dark">Periode Lama</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodebulanold" id="periodebulanold">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodetahunold" id="periodetahunold">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $defaultTahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-sm-4 col-form-label font-weight-bold small text-dark">Periode Baru</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodebulannew" id="periodebulannew">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="periodetahunnew" id="periodetahunnew">
                                    @for ($i = $tahun - 2; $i <= $tahun + 1; $i++)
                                        <option value="{{ $i }}" {{ $i == $tahun ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn tsu-btn-filter px-4" id="btnUpdate">Update Periode</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });

        var currentFilter = {
            periode_bulan: '{{ $defaultBulan }}',
            periode_tahun: '{{ $defaultTahun }}',
            start_date: '',
            end_date: '',
            nominal: '{{ $defaultNominal }}'
        };

        function updateLabelPeriode() {
            if (currentFilter.start_date && currentFilter.end_date) {
                var rangeText = currentFilter.start_date + ' s/d ' + currentFilter.end_date;
                $('#labelPeriodeAktif').text(rangeText);
                $('#statCardPeriode').text(rangeText);
            } else if (currentFilter.periode_bulan && currentFilter.periode_tahun) {
                var bulanText = $('#filter_bulan option[value="' + currentFilter.periode_bulan + '"]').text();
                var fullText = (bulanText || 'Bulan ' + currentFilter.periode_bulan) + ' ' + currentFilter.periode_tahun;
                $('#labelPeriodeAktif').text(fullText);
                $('#statCardPeriode').text(fullText);
            } else {
                $('#labelPeriodeAktif').text('Silahkan Pilih Periode Filter');
            }
        }
        updateLabelPeriode();

        // Inisialisasi Yajra DataTables Summary per Orang
        var oTable = $('#table-rekap-absensi').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.rekap-absensi.json') }}",
                data: function(d) {
                    d.periode_bulan = currentFilter.periode_bulan;
                    d.periode_tahun = currentFilter.periode_tahun;
                    d.start_date = currentFilter.start_date;
                    d.end_date = currentFilter.end_date;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                { data: 'pin', name: 'pin', className: 'text-center font-weight-bold text-dark' },
                { data: 'nama_karyawan', name: 'nama' },
                { data: 'validasi_badge', name: 'akumulasi_validasi', className: 'text-center' },
                { data: 'cuti_badge', name: 'cuti', className: 'text-center' },
                { data: 'izin_badge', name: 'izin', className: 'text-center' },
                { data: 'alpha_badge', name: 'alpha', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                search: "Cari Pegawai:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pegawai",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total pegawai)",
                zeroRecords: "Tidak ada data rekap presensi yang sesuai",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });

        // Filter modal handling
        $('#btnFilter').click(function() {
            $('#modal-filter').modal('show');
        });

        $('#filter_tipe').change(function() {
            if ($(this).val() === 'custom') {
                $('#filter_bulan_section').hide();
                $('#filter_custom_section').show();
            } else {
                $('#filter_bulan_section').show();
                $('#filter_custom_section').hide();
            }
        });

        $('#btnApplyFilter').click(function() {
            var tipe = $('#filter_tipe').val();
            if (tipe === 'custom') {
                currentFilter.start_date = $('#filter_start_date').val();
                currentFilter.end_date = $('#filter_end_date').val();
                currentFilter.periode_bulan = '';
                currentFilter.periode_tahun = '';
            } else {
                currentFilter.periode_bulan = $('#filter_bulan').val();
                currentFilter.periode_tahun = $('#filter_tahun').val();
                currentFilter.start_date = '';
                currentFilter.end_date = '';
            }

            updateLabelPeriode();
            oTable.ajax.reload();
            $('#modal-filter').modal('hide');
        });

        var currentDetailPin = '';
        var currentDetailNama = '';

        function loadDetailPresensi(pin, nama) {
            currentDetailPin = pin;
            currentDetailNama = nama;
            $('#detailNamaKaryawan').text(nama + ' (PIN: ' + pin + ')');
            $('#detailContent').html('<div class="text-center p-5"><div class="spinner-border text-info"></div><p class="mt-2 font-weight-bold text-muted">Menyinkronkan dan memuat rincian harian presensi...</p></div>');

            $.ajax({
                url: "{{ route('admin.rekap-absensi.detail') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    pin: pin,
                    periode_bulan: currentFilter.periode_bulan,
                    periode_tahun: currentFilter.periode_tahun,
                    start_date: currentFilter.start_date,
                    end_date: currentFilter.end_date,
                },
                success: function(res) {
                    if (res.success && res.logs && res.logs.length > 0) {
                        var sum = res.summary;
                        var html = `
                            <!-- WIDGET RINGKASAN DETAIL -->
                            <div class="row text-center mb-3">
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block font-weight-bold">Hadir Valid</small>
                                        <strong class="text-success h5 font-weight-bold">${sum.total_valid} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block font-weight-bold">Cuti (CT)</small>
                                        <strong class="text-primary h5 font-weight-bold">${sum.total_cuti} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block font-weight-bold">Izin (I)</small>
                                        <strong class="text-purple h5 font-weight-bold" style="color:#6f42c1;">${sum.total_izin} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block font-weight-bold">Alpha (A)</small>
                                        <strong class="text-danger h5 font-weight-bold">${sum.total_alpha} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block font-weight-bold">Libur / OFF</small>
                                        <strong class="text-secondary h5 font-weight-bold">${sum.total_libur} Hari</strong>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-2">
                                    <div class="bg-light p-2 rounded border">
                                        <small class="text-muted d-block font-weight-bold">Kurang Durasi</small>
                                        <strong class="text-warning h5 font-weight-bold">${sum.total_kurang_durasi} Hari</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="small text-muted">
                                    <span class="tsu-badge-soft tsu-badge-alpha mr-2">
                                        Jam Berwarna Merah
                                    </span>
                                    <span>: Menandakan <strong>Terlambat Masuk</strong> atau <strong>Durasi Jam Harian Belum Memenuhi Syarat</strong>.</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover" style="font-size: 8.5pt;">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="16%">Hari &amp; Tanggal</th>
                                            <th width="8%">Scan 1</th>
                                            <th width="8%">Scan 2</th>
                                            <th width="8%">Scan 3</th>
                                            <th width="8%">Scan 4</th>
                                            <th width="9%">Durasi</th>
                                            <th width="14%">Status Presensi</th>
                                            <th width="18%">Keterangan</th>
                                            <th width="8%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        function formatScan(val, isRed, tooltip) {
                            if (!val || val === '-') return '<span class="text-muted">-</span>';
                            if (isRed) {
                                return `<span class="text-danger font-weight-bold" style="background-color: #fee2e2; padding: 2px 6px; border-radius: 4px; border: 1px solid #fca5a5;" title="${tooltip || 'Terlambat / Kurang Durasi'}">${val}</span>`;
                            }
                            return `<span class="text-dark font-weight-bold">${val}</span>`;
                        }

                        $.each(res.logs, function(idx, item) {
                            var badge = `<span class="tsu-badge-soft ${item.badge_class} px-2 py-1">${item.status_label}</span>`;
                            var rowBg = item.status_type === 'ALPHA' ? 'style="background-color: #fff5f5;"' : (item.status_type === 'CUTI' ? 'style="background-color: #f0f8ff;"' : '');

                            var scan1Html = formatScan(item.scan_1, item.scan_1_red, item.is_late_in ? 'Terlambat Masuk (Melebihi Jam Shift)' : 'Akumulasi Durasi Harian Tidak Memenuhi Target');
                            var scan2Html = formatScan(item.scan_2, item.scan_2_red, 'Akumulasi Durasi Harian Tidak Memenuhi Target');
                            var scan3Html = formatScan(item.scan_3, item.scan_3_red, 'Akumulasi Durasi Harian Tidak Memenuhi Target');
                            var scan4Html = formatScan(item.scan_4, item.scan_4_red, 'Akumulasi Durasi Harian Tidak Memenuhi Target');

                            var btnEdit = `<button type="button" class="btn btn-xs tsu-btn-detail btn-edit-harian" 
                                data-pin="${res.karyawan.pin}" 
                                data-nama="${res.karyawan.nama}" 
                                data-absensi-id="${item.absensi_id || ''}" 
                                data-tanggal="${item.tanggal}" 
                                data-tanggal-formatted="${item.tanggal_formatted}" 
                                data-scan1="${item.scan_1 !== '-' ? item.scan_1 : ''}" 
                                data-scan2="${item.scan_2 !== '-' ? item.scan_2 : ''}" 
                                data-scan3="${item.scan_3 !== '-' ? item.scan_3 : ''}" 
                                data-scan4="${item.scan_4 !== '-' ? item.scan_4 : ''}" 
                                title="Koreksi Jam Kerja">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>`;

                            html += `
                                <tr ${rowBg}>
                                    <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
                                    <td><strong>${item.tanggal_formatted}</strong></td>
                                    <td class="text-center">${scan1Html}</td>
                                    <td class="text-center">${scan2Html}</td>
                                    <td class="text-center">${scan3Html}</td>
                                    <td class="text-center">${scan4Html}</td>
                                    <td class="text-center font-weight-bold">${item.durasi}</td>
                                    <td class="text-center">${badge}</td>
                                    <td><small class="text-muted">${item.keterangan || '-'}</small></td>
                                    <td class="text-center">${btnEdit}</td>
                                </tr>
                            `;
                        });

                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;

                        $('#detailContent').html(html);
                    } else {
                        $('#detailContent').html('<div class="text-center text-muted p-5"><i class="fas fa-folder-open mb-2" style="font-size: 2rem; opacity: 0.3; display: block;"></i>Tidak ada catatan presensi pada periode ini.</div>');
                    }
                },
                error: function(xhr) {
                    $('#detailContent').html('<div class="text-center text-danger p-4">Gagal memuat rincian presensi: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan.') + '</div>');
                }
            });
        }

        // Modal Detail Log Harian Trigger
        $('body').on('click', '.btn-detail-rekap', function(e) {
            e.preventDefault();
            var pin = $(this).data('pin');
            var nama = $(this).data('nama');
            $('#modal-detail-presensi').modal('show');
            loadDetailPresensi(pin, nama);
        });

        // Trigger Modal Edit Jam Kerja Harian
        $('body').on('click', '.btn-edit-harian', function(e) {
            e.preventDefault();
            var pin = $(this).data('pin');
            var nama = $(this).data('nama');
            var absensiId = $(this).data('absensi-id');
            var tanggal = $(this).data('tanggal');
            var tanggalFormatted = $(this).data('tanggal-formatted');
            var scan1 = $(this).data('scan1') || '';
            var scan2 = $(this).data('scan2') || '';
            var scan3 = $(this).data('scan3') || '';
            var scan4 = $(this).data('scan4') || '';

            $('#edit_harian_absensi_id').val(absensiId);
            $('#edit_harian_pin').val(pin);
            $('#edit_harian_tanggal').val(tanggal);
            $('#edit_harian_info_karyawan').text(nama + ' (PIN: ' + pin + ')');
            $('#edit_harian_info_tanggal').text(tanggalFormatted);

            $('#edit_harian_scan_1').val(scan1);
            $('#edit_harian_scan_2').val(scan2);
            $('#edit_harian_scan_3').val(scan3);
            $('#edit_harian_scan_4').val(scan4);

            // Bantuan Quick Move jika ada Scan 3 atau Scan 4
            var quickButtons = '';
            if (scan3) {
                quickButtons += `
                    <div class="mb-2 p-2 bg-white rounded border shadow-sm" style="border-color: #bae6fd !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small font-weight-bold text-dark"><i class="fas fa-history text-info mr-1"></i> Terdeteksi Scan 3: <code class="font-weight-bold h6 text-primary bg-light px-2 py-1 rounded border">${scan3}</code></span>
                        </div>
                        <div class="btn-group btn-group-sm w-100 mb-1">
                            <button type="button" class="btn btn-outline-danger btn-quick-action font-weight-bold" data-from="3" data-to="2" data-val="${scan3}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Pulang (Scan 2)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-quick-action font-weight-bold" data-from="3" data-to="1" data-val="${scan3}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Masuk (Scan 1)
                            </button>
                        </div>
                        <small class="d-block mt-1 font-italic font-weight-bold text-muted">* Scan 3 otomatis dikosongkan setelah dipindahkan.</small>
                    </div>
                `;
            }

            if (scan4) {
                quickButtons += `
                    <div class="mb-1 p-2 bg-white rounded border shadow-sm" style="border-color: #bae6fd !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small font-weight-bold text-dark"><i class="fas fa-history text-info mr-1"></i> Terdeteksi Scan 4: <code class="font-weight-bold h6 text-primary bg-light px-2 py-1 rounded border">${scan4}</code></span>
                        </div>
                        <div class="btn-group btn-group-sm w-100 mb-1">
                            <button type="button" class="btn btn-outline-danger btn-quick-action font-weight-bold" data-from="4" data-to="2" data-val="${scan4}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Pulang (Scan 2)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-quick-action font-weight-bold" data-from="4" data-to="1" data-val="${scan4}">
                                <i class="fas fa-arrow-left mr-1"></i> Pindah ke Jam Masuk (Scan 1)
                            </button>
                        </div>
                        <small class="d-block mt-1 font-italic font-weight-bold text-muted">* Scan 4 otomatis dikosongkan setelah dipindahkan.</small>
                    </div>
                `;
            }

            if (quickButtons) {
                $('#quickMoveButtonsContainer').html(quickButtons);
                $('#panelQuickMoveSection').slideDown();
            } else {
                $('#quickMoveButtonsContainer').empty();
                $('#panelQuickMoveSection').hide();
            }

            $('#modal-edit-harian').modal('show');
        });

        // Quick action click handler (Pindah Jam & Kosongkan Scan 3/4)
        $('body').on('click', '.btn-quick-action', function(e) {
            e.preventDefault();
            var from = $(this).data('from');
            var to = $(this).data('to');
            var val = $(this).data('val');

            if (to == 2) {
                $('#edit_harian_scan_2').val(val).addClass('is-valid');
                setTimeout(() => $('#edit_harian_scan_2').removeClass('is-valid'), 1500);
            } else if (to == 1) {
                $('#edit_harian_scan_1').val(val).addClass('is-valid');
                setTimeout(() => $('#edit_harian_scan_1').removeClass('is-valid'), 1500);
            }

            // Otomatis kosongkan scan sumber (Scan 3 atau Scan 4)
            $('#edit_harian_scan_' + from).val('');
            $(this).closest('.p-2').fadeOut();
        });

        // Clear button
        $('body').on('click', '.btn-clear-scan', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $(target).val('');
        });

        // Submit form edit harian
        $('#formEditHarian').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btnSubmitEditHarian');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: "{{ route('admin.rekap-absensi.update-daily') }}",
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan &amp; Kalkulasi Ulang');
                    if (res.success) {
                        $('#modal-edit-harian').modal('hide');
                        // Reload detail modal
                        loadDetailPresensi(currentDetailPin, currentDetailNama);
                        // Reload main summary table
                        oTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan &amp; Kalkulasi Ulang');
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan server.', 'error');
                }
            });
        });

        // Multi-modal fix for Bootstrap
        $('#modal-edit-harian').on('hidden.bs.modal', function () {
            if ($('#modal-detail-presensi').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });

        // Hitung Ulang Validitas
        $('#btnKalkulasi').click(function() {
            Swal.fire({
                title: 'Hitung Ulang Validitas Presensi?',
                text: 'Sistem akan mengkalkulasi ulang durasi kerja dan status validasi (1.0) untuk data periode terpilih.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#094b54',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hitung Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Mengkalkulasi...',
                        text: 'Mohon tunggu beberapa saat...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ route('admin.rekap-absensi.kalkulasiulang') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            periode_bulan: currentFilter.periode_bulan,
                            periode_tahun: currentFilter.periode_tahun,
                            start_date: currentFilter.start_date,
                            end_date: currentFilter.end_date,
                        },
                        success: function(res) {
                            oTable.ajax.reload();
                            Swal.fire('Berhasil!', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });

        // Export Rekap Excel
        $('#btnExportExcel').click(function() {
            var params = $.param({
                periode_bulan: currentFilter.periode_bulan,
                periode_tahun: currentFilter.periode_tahun,
                start_date: currentFilter.start_date,
                end_date: currentFilter.end_date,
                nominal: currentFilter.nominal
            });
            window.location.href = "{{ route('admin.rekap-absensi.exportrekap') }}?" + params;
        });

        // Download All Slip PDF ZIP
        $('#btnDownloadAllSlip').click(function() {
            var params = $.param({
                periode_bulan: currentFilter.periode_bulan,
                periode_tahun: currentFilter.periode_tahun,
                start_date: currentFilter.start_date,
                end_date: currentFilter.end_date,
            });
            window.location.href = "{{ route('admin.rekap-absensi.downloadallslip') }}?" + params;
        });

        $('#updateperiode').click(function(e) {
            $('#modal-update').modal({ show: true, backdrop: 'static' });
        });

        $('#formUpdate').on('submit', function() {
            $('#btnUpdate').prop('disabled', true);
            Swal.fire({
                title: 'Mengupdate Periode Absensi ...',
                text: 'Mohon tunggu, jangan menutup browser sampai proses selesai.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
        });
    </script>
@endsection
