@extends('system::template.admin.header')
@section('title', $title ?? 'Rekap Riwayat Presensi & Payroll Transport')

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
            --tsu-accent-red: #b91c1c;
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
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 112px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tsu-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 0.35rem;
        }

        .tsu-stat-card__subtext {
            font-size: 0.74rem;
            opacity: 0.88;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Card Color Schemes */
        .tsu-stat-card--pegawai {
            background: linear-gradient(135deg, #094b54 0%, #0f6875 100%);
            color: #ffffff;
        }
        .tsu-stat-card--pegawai .tsu-stat-card__title { color: #a5d8dd; }

        .tsu-stat-card--tarif {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--tarif .tsu-stat-card__title { color: #fef3c7; }

        .tsu-stat-card--hadir {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }
        .tsu-stat-card--hadir .tsu-stat-card__title { color: #bae6fd; }

        .tsu-stat-card--payroll {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            color: #ffffff;
        }
        .tsu-stat-card--payroll .tsu-stat-card__title { color: #a7f3d0; }

        /* === TSU Modern Card === */
        .tsu-card {
            background: #ffffff;
            border: 1px solid var(--tsu-border-gray, #e2e8f0);
            border-radius: var(--tsu-radius-lg, 12px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.75rem;
            overflow: hidden;
        }

        .tsu-sub-bar {
            background: #f8fafc;
            border-bottom: 1px solid var(--tsu-border-gray, #e2e8f0);
            padding: 0.85rem 1.35rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .tsu-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #e6f3f4;
            color: var(--tsu-primary, #094b54);
            border: 1px solid #c4e4e7;
            border-radius: 6px;
            padding: 0.4rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .tsu-tariff-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 0.4rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
        }

        /* Action Buttons */
        .tsu-btn-outline-action {
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1rem;
            border: 1px solid #cbd5e1;
            color: #475569;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .tsu-btn-outline-action:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .tsu-btn-success-action {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            border: none;
            color: #ffffff;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.2);
        }

        .tsu-btn-success-action:hover {
            box-shadow: 0 4px 10px rgba(4, 120, 87, 0.3);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .tsu-btn-danger-action {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            border: none;
            color: #ffffff;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(185, 28, 28, 0.2);
        }

        .tsu-btn-danger-action:hover {
            box-shadow: 0 4px 10px rgba(185, 28, 28, 0.3);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* === Table Styling === */
        .tsu-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.86rem;
        }

        .tsu-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
            padding: 0.85rem 0.75rem;
            border-bottom: 2px solid var(--tsu-border-gray, #e2e8f0) !important;
            border-top: none !important;
        }

        .tsu-table tbody td {
            vertical-align: middle;
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .tsu-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Table Badges strictly WITHOUT icons */
        .tsu-badge-nik {
            display: inline-block;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.2rem 0.55rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .tsu-badge-hadir {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 0.25rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .tsu-badge-ineligible {
            display: inline-block;
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.25rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* Action Buttons inside Datatable */
        .tsu-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.76rem;
            padding: 0.3rem 0.65rem;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .tsu-btn-action--detail {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .tsu-btn-action--detail:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }

        .tsu-btn-action--pdf {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .tsu-btn-action--pdf:hover {
            background: #fca5a5;
            color: #7f1d1d;
        }

        /* Modal Gradient Header */
        .tsu-modal-header {
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%);
            color: #ffffff;
            border-top-left-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            border-top-right-radius: calc(var(--tsu-radius-lg, 12px) - 1px);
            padding: 1.1rem 1.4rem;
        }

        .tsu-modal-header .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tsu-modal-header .close {
            color: #ffffff;
            opacity: 0.85;
            text-shadow: none;
            transition: opacity 0.2s ease;
        }
        .tsu-modal-header .close:hover {
            opacity: 1;
        }

        /* Modal Detail Table Badges */
        .tsu-badge-valid {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: 4px;
            padding: 0.2rem 0.55rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .tsu-badge-invalid {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 4px;
            padding: 0.2rem 0.55rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--tsu-primary, #094b54) !important;
            border-color: var(--tsu-primary, #094b54) !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
            padding: 0.35rem 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1px solid var(--tsu-border-gray, #e2e8f0) !important;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header Component --}}
    <x-tsu-page-header
        title="Rekap Riwayat Presensi & Payroll Transport"
        subtitle="Laporan akumulasi kehadiran valid dan perhitungan payroll tunjangan transport kerja pegawai"
        icon="fas fa-file-invoice-dollar"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-outline-action mr-2" id="btnFilter" title="Filter Periode / Tanggal">
                <i class="fas fa-filter mr-1"></i> Filter Periode
            </button>

            <button type="button" class="btn btn-sm tsu-btn-success-action mr-2" id="btnExportExcel" title="Export Rekap Presensi & Payroll Transport (Excel)">
                <i class="fas fa-file-excel mr-1"></i> Export Rekap Excel
            </button>

            <button type="button" class="btn btn-sm tsu-btn-danger-action" id="btnDownloadAllSlip" title="Download Slip Presensi Semua Pegawai (ZIP)">
                <i class="fas fa-file-archive mr-1"></i> Download Semua Slip (ZIP)
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- 4 Stat Cards Grid --}}
            <div class="tsu-stat-grid-rekap">
                {{-- Card 1: Total Pegawai Terdata --}}
                <div class="tsu-stat-card tsu-stat-card--pegawai">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Pegawai Terdata</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_pegawai'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Pegawai</span></div>
                    <div class="tsu-stat-card__subtext">Dosen & Tendik terdaftar HRIS</div>
                </div>

                {{-- Card 2: Standar Tarif Transport --}}
                <div class="tsu-stat-card tsu-stat-card--tarif">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="tsu-stat-card__title">Standar Tarif Transport</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">Rp {{ number_format($stats['default_nominal'] ?? 20000, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Tarif per hari kehadiran valid</div>
                </div>

                {{-- Card 3: Kehadiran Valid Bulan Ini --}}
                <div class="tsu-stat-card tsu-stat-card--hadir">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="tsu-stat-card__title">Kehadiran Valid Bulan Ini</div>
                    <div class="tsu-stat-card__value">{{ number_format($stats['total_hadir_bulan_ini'] ?? 0, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">Hari</span></div>
                    <div class="tsu-stat-card__subtext">Periode {{ $bulan[$defaultBulan] ?? 'Bulan Ini' }} {{ $defaultTahun }}</div>
                </div>

                {{-- Card 4: Estimasi Payroll Transport --}}
                <div class="tsu-stat-card tsu-stat-card--payroll">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="tsu-stat-card__title">Estimasi Payroll Transport</div>
                    <div class="tsu-stat-card__value" style="font-size: 1.55rem;">Rp {{ number_format($stats['total_payroll_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                    <div class="tsu-stat-card__subtext">Akumulasi tunjangan transport</div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="tsu-card">
                {{-- Filter & Period Sub-bar --}}
                <div class="tsu-sub-bar">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted small font-weight-bold mr-1">
                            <i class="fas fa-calendar-alt mr-1 text-primary"></i> Periode Terpilih:
                        </span>
                        <span class="tsu-period-badge" id="labelPeriodeAktif">
                            Memuat Periode...
                        </span>
                    </div>
                    <div>
                        <span class="tsu-tariff-pill">
                            <i class="fas fa-tag mr-1"></i> Tarif Uang Transport: <strong class="ml-1" id="labelTarif">Rp {{ number_format($defaultNominal, 0, ',', '.') }}</strong> / hari hadir
                        </span>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table id="table-rekap" class="table tsu-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="12%">NIK</th>
                                    <th width="28%">Nama Pegawai</th>
                                    <th width="14%" class="text-center">Kehadiran Valid</th>
                                    <th width="14%" class="text-center">Tarif Transport</th>
                                    <th width="16%" class="text-right">Total Transport</th>
                                    <th width="12%" class="text-center text-nowrap">Aksi</th>
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
    <div class="modal fade" id="modal-filter" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Data Rekap Presensi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark small text-uppercase">Tipe Filter</label>
                        <select class="form-control" id="filter_tipe" style="border-radius: 8px;">
                            <option value="bulan">Berdasarkan Bulan &amp; Tahun</option>
                            <option value="custom">Rentang Tanggal Khusus (Custom)</option>
                        </select>
                    </div>

                    <div id="filter_bulan_section">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Bulan</label>
                                <select class="form-control select2" id="filter_bulan">
                                    @foreach ($bulan as $key => $item)
                                        <option value="{{ $key }}" {{ $key == $defaultBulan ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Tahun</label>
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
                                <label class="small text-muted font-weight-bold">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="filter_start_date" style="border-radius: 8px;">
                            </div>
                            <div class="col-6 form-group">
                                <label class="small text-muted font-weight-bold">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="filter_end_date" style="border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Tutup</button>
                    <button type="button" class="btn btn-sm text-white px-4 font-weight-bold" id="btnApplyFilter" style="background: #094b54; border-radius: 6px;">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL PRESENSI INDIVIDU --}}
    <div class="modal fade" id="modal-detail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <div class="modal-header tsu-modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-clock mr-1"></i> Rincian Harian Presensi: <span id="detailNamaKaryawan" class="ml-1"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="detailContent">
                    {{-- Loaded dynamically via AJAX --}}
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal" style="border-radius: 6px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('.select2').select2({ width: '100%' });

        var currentFilter = {
            periode_bulan: '{{ $defaultBulan }}',
            periode_tahun: '{{ $defaultTahun }}',
            start_date: '',
            end_date: '',
            nominal: '{{ $defaultNominal }}'
        };

        function updateLabelPeriode() {
            if (currentFilter.start_date && currentFilter.end_date) {
                $('#labelPeriodeAktif').text(currentFilter.start_date + ' s/d ' + currentFilter.end_date);
            } else {
                var bulanText = $('#filter_bulan option[value="' + currentFilter.periode_bulan + '"]').text();
                $('#labelPeriodeAktif').text((bulanText || 'Bulan ' + currentFilter.periode_bulan) + ' ' + currentFilter.periode_tahun);
            }
            $('#labelTarif').text('Rp ' + parseInt(currentFilter.nominal).toLocaleString('id-ID'));
        }
        updateLabelPeriode();

        // Inisialisasi DataTables
        var oTable = $('#table-rekap').DataTable({
            processing: true,
            serverSide: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json",
                emptyTable: "Belum ada rekap presensi pada periode yang dipilih",
                zeroRecords: "Tidak ada data presensi yang sesuai pencarian"
            },
            ajax: {
                url: "{{ route('admin.riwayatabsensi.datatablesabsensi') }}",
                data: function(d) {
                    d.periode_bulan = currentFilter.periode_bulan;
                    d.periode_tahun = currentFilter.periode_tahun;
                    d.start_date = currentFilter.start_date;
                    d.end_date = currentFilter.end_date;
                    d.nominal = currentFilter.nominal;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                { data: 'nik', name: 'users.nik' },
                { data: 'nama_lengkap', name: 'nama' },
                { data: 'hadir', name: 'total_hadir', className: 'text-center' },
                { data: 'nominal_transport', name: 'nominal_transport', orderable: false, searchable: false, className: 'text-center' },
                { data: 'total_transport', name: 'total_transport', orderable: false, searchable: false, className: 'text-right' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center text-nowrap' },
            ]
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

        // Detail Presensi Modal
        $('body').on('click', '.btn-detail-rekap', function(e) {
            e.preventDefault();
            var pin = $(this).data('pin');
            var nama = $(this).data('nama');

            $('#detailNamaKaryawan').text(nama + ' (PIN: ' + pin + ')');
            $('#modal-detail').modal('show');
            $('#detailContent').html(
                `<div class="text-center p-5"><div class="spinner-border" style="color: var(--tsu-primary, #094b54);"></div><p class="mt-3 font-weight-bold text-muted">Memuat rincian presensi...</p></div>`
            );

            $.ajax({
                url: "{{ route('admin.riwayatabsensi.detail') }}",
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
                    if (res.records && res.records.length > 0) {
                        var html = `
                            <div class="table-responsive">
                                <table class="table tsu-table" style="font-size: 0.84rem;">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="15%" class="text-center">Tanggal</th>
                                            <th width="12%" class="text-center">Scan 1</th>
                                            <th width="12%" class="text-center">Scan 2</th>
                                            <th width="12%" class="text-center">Scan 3</th>
                                            <th width="12%" class="text-center">Scan 4</th>
                                            <th width="16%" class="text-center">Durasi Jam</th>
                                            <th width="16%" class="text-center">Validitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        $.each(res.records, function(idx, item) {
                            var isValid = parseFloat(item.akumulasi_validasi || 0) > 0;
                            var badgeValid = isValid
                                ? '<span class="tsu-badge-valid">1.0 (Valid)</span>'
                                : '<span class="tsu-badge-invalid">0.0 (Tidak Hadir)</span>';

                            html += `
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
                                    <td class="text-center font-weight-bold text-dark">${item.tanggal_absen}</td>
                                    <td class="text-center">${item.scan_1 || '-'}</td>
                                    <td class="text-center">${item.scan_2 || '-'}</td>
                                    <td class="text-center">${item.scan_3 || '-'}</td>
                                    <td class="text-center">${item.scan_4 || '-'}</td>
                                    <td class="text-center font-weight-bold" style="color: var(--tsu-accent-blue, #0284c7);">${item.durasi_kerja || '-'}</td>
                                    <td class="text-center">${badgeValid}</td>
                                </tr>
                            `;
                        });
                        html += `
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="7" class="text-right text-dark text-uppercase small">Total Hari Hadir Valid:</td>
                                            <td class="text-center text-success" style="font-size: 1.05rem;">${res.total_valid} Hari</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        `;
                        $('#detailContent').html(html);
                    } else {
                        $('#detailContent').html('<div class="text-center text-muted p-5"><i class="fas fa-calendar-times fa-3x mb-3 text-secondary"></i><p class="font-weight-bold">Tidak ada catatan presensi pada periode ini.</p></div>');
                    }
                },
                error: function(xhr) {
                    $('#detailContent').html('<div class="text-center text-danger p-5"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p class="font-weight-bold">Gagal memuat rincian presensi.</p></div>');
                }
            });
        });
    </script>
@endsection
