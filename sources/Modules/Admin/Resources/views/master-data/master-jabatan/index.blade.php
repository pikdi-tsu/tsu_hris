@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-jabatan {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-jabatan {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-jabatan {
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
            font-size: 1.85rem;
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
        .tsu-stat-card--pangkat {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        /* === TSU Container Card === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        /* === Underline Nav Tabs (Matching Riwayat Cuti & MPP) === */
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
        .tsu-tab-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748b !important;
            border: none !important;
            border-bottom: 3px solid transparent !important;
            background: transparent !important;
            background-color: transparent !important;
            border-radius: 0 !important;
            margin-bottom: -2px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: none !important;
            cursor: pointer;
        }
        .card-tabs .card-header .nav-tabs.tsu-tab-nav .nav-link:hover,
        .tsu-tab-nav .nav-link:hover {
            color: var(--tsu-primary, #094b54) !important;
            background: transparent !important;
            background-color: transparent !important;
            border-bottom: 3px solid transparent !important;
        }
        .card-tabs .card-header .nav-tabs.tsu-tab-nav .nav-link.active,
        .tsu-tab-nav .nav-link.active {
            color: var(--tsu-primary-dark, #094b54) !important;
            border-bottom: 3px solid var(--tsu-primary, #094b54) !important;
            background: transparent !important;
            background-color: transparent !important;
            font-weight: 700;
            box-shadow: none !important;
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
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Master Data Jabatan'"
        :icon="$menuIcon ?? 'fas fa-sitemap'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Refresh Data --}}
            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-jabatan">
                {{-- Total Jabatan --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Jabatan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_jabatan'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Posisi</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Total Struktural & Fungsional Kampus
                    </div>
                </div>

                {{-- Jabatan Struktural --}}
                <div class="tsu-stat-card tsu-stat-card--struktural">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Jabatan Struktural</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['struktural'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Jabatan</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Posisi Pimpinan & Manajerial Unit
                    </div>
                </div>

                {{-- Jabatan Fungsional --}}
                <div class="tsu-stat-card tsu-stat-card--fungsional">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Jabatan Fungsional</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['fungsional'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Jabatan</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Jenjang Akademik & Keahlian Khusus
                    </div>
                </div>

                {{-- Pangkat & Golongan --}}
                <div class="tsu-stat-card tsu-stat-card--pangkat">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Pangkat & Golongan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['pangkat'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Jenjang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Tingkatan Kepangkatan Pegawai
                    </div>
                </div>
            </div>

            {{-- Card Container with Underline Tabs --}}
            <div class="card card-primary card-outline card-tabs tsu-card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs tsu-tab-nav" id="jabatanTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="tab-struktural" data-toggle="tab" href="#content-struktural" role="tab" aria-controls="content-struktural" aria-selected="true">
                                Jabatan Struktural
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tab-fungsional" data-toggle="tab" href="#content-fungsional" role="tab" aria-controls="content-fungsional" aria-selected="false">
                                Jabatan Fungsional
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tab-pangkat" data-toggle="tab" href="#content-pangkat" role="tab" aria-controls="content-pangkat" aria-selected="false">
                                Pangkat & Golongan
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-3">
                    <div class="tab-content" id="jabatanTabsContent">
                        
                        {{-- TAB 1: STRUKTURAL --}}
                        <div class="tab-pane fade show active" id="content-struktural" role="tabpanel" aria-labelledby="tab-struktural">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted text-sm font-weight-500">
                                    Daftar master posisi jabatan struktural dan manajerial pada unit organisasi.
                                </span>
                                @can('admin:master-jabatan:create')
                                    <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal" data-url="{{ route('admin.master-jabatan.struktural.create') }}">
                                        <i class="fas fa-plus mr-1"></i> Tambah Struktural
                                    </button>
                                @endcan
                            </div>
                            <div class="table-responsive">
                                <table id="table-struktural" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">No</th>
                                            <th width="36%">Nama Jabatan</th>
                                            <th width="15%">Periode</th>
                                            <th width="25%">Keterangan</th>
                                            <th width="12%">Jumlah Pegawai</th>
                                            <th width="8%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 2: FUNGSIONAL --}}
                        <div class="tab-pane fade" id="content-fungsional" role="tabpanel" aria-labelledby="tab-fungsional">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted text-sm font-weight-500">
                                    Daftar jenjang jabatan fungsional akademik dosen dan keahlian tendik.
                                </span>
                                @can('admin:master-jabatan:create')
                                    <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal" data-url="{{ route('admin.master-jabatan.fungsional.create') }}">
                                        <i class="fas fa-plus mr-1"></i> Tambah Fungsional
                                    </button>
                                @endcan
                            </div>
                            <div class="table-responsive">
                                <table id="table-fungsional" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">No</th>
                                            <th width="36%">Nama Jabatan</th>
                                            <th width="15%">Periode</th>
                                            <th width="25%">Keterangan</th>
                                            <th width="12%">Jumlah Pegawai</th>
                                            <th width="8%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 3: PANGKAT & GOLONGAN --}}
                        <div class="tab-pane fade" id="content-pangkat" role="tabpanel" aria-labelledby="tab-pangkat">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted text-sm font-weight-500">
                                    Daftar tingkatan pangkat dan golongan ruang kepegawaian.
                                </span>
                                @can('admin:master-jabatan:create')
                                    <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal" data-url="{{ route('admin.master-jabatan.pangkat.create') }}">
                                        <i class="fas fa-plus mr-1"></i> Tambah Pangkat/Golongan
                                    </button>
                                @endcan
                            </div>
                            <div class="table-responsive">
                                <table id="table-pangkat" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="4%" class="text-center">No</th>
                                            <th width="40%">Pangkat / Golongan</th>
                                            <th width="36%">Keterangan</th>
                                            <th width="12%">Jumlah Pegawai</th>
                                            <th width="8%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ================= MODAL CONTAINER (AJAX) ================= --}}
    <div class="modal fade" id="modal-jabatan" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content" id="modal-jabatan-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Loading State --}}
                <div class="text-center p-5">
                    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted font-weight-bold">Memuat Formulir...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var dtLanguage = {
                search: "_INPUT_",
                searchPlaceholder: "Cari nama jabatan, keterangan...",
                lengthMenu: "Tampilkan _MENU_ baris",
                zeroRecords: "Tidak ada data yang sesuai",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                processing: '<div class="d-flex align-items-center justify-content-center" style="gap: 0.5rem;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <span>Memuat data...</span></div>'
            };

            // Init Datatable 1: Struktural
            var tableStruktural = $('#table-struktural').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('admin.master-jabatan.struktural.json') }}",
                language: dtLanguage,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama_jabatan', name: 'nama_jabatan' },
                    { data: 'periode', name: 'periode' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'jumlah_karyawan', name: 'jumlah_karyawan', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            // Init Datatable 2: Fungsional
            var tableFungsional = $('#table-fungsional').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('admin.master-jabatan.fungsional.json') }}",
                language: dtLanguage,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama_jabatan', name: 'nama_jabatan' },
                    { data: 'periode', name: 'periode' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'jumlah_karyawan', name: 'jumlah_karyawan', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            // Init Datatable 3: Pangkat
            var tablePangkat = $('#table-pangkat').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('admin.master-jabatan.pangkat.json') }}",
                language: dtLanguage,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama_pangkat_golongan', name: 'nama_pangkat_golongan' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'jumlah_karyawan', name: 'jumlah_karyawan', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ]
            });

            // Adjust table columns on tab change
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            });

            // Reload all tables button
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                tableStruktural.ajax.reload(null, false);
                tableFungsional.ajax.reload(null, false);
                tablePangkat.ajax.reload(function() {
                    $btn.find('i').removeClass('fa-spin');
                }, false);
            });

            // Handler Modal Umum (Create & Edit)
            $('body').on('click', '.btn-modal, .btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');

                $('#modal-jabatan').modal('show');
                $('#modal-jabatan-content').html(
                    '<div class="text-center p-5">' +
                    '    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>' +
                    '    <p class="mt-3 text-muted font-weight-bold">Memuat Formulir...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-jabatan-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-jabatan-content').html(
                            '<div class="modal-body p-4 text-center">' +
                            '    <div class="alert alert-danger mb-0">Gagal memuat formulir. Error: ' + xhr.status + '</div>' +
                            '</div>' +
                            '<div class="modal-footer p-3" style="background: #f8fafc;">' +
                            '    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Tutup</button>' +
                            '</div>'
                        );
                    }
                });
            });

            // Handler Submit Form AJAX via pikdiAjax
            $('body').on('submit', '#modal-jabatan form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();
                var btnSubmit = form.find('button[type="submit"]');
                var originalBtnText = btnSubmit.html();

                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

                pikdiAjax({
                    url: url,
                    type: method,
                    data: formData,
                    onSuccess: function(res) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                        $('#modal-jabatan').modal('hide');
                        // Reload all 3 tables
                        tableStruktural.ajax.reload(null, false);
                        tableFungsional.ajax.reload(null, false);
                        tablePangkat.ajax.reload(null, false);
                    },
                    onError: function(xhr) {
                        btnSubmit.html(originalBtnText).prop('disabled', false);
                    }
                });
            });

            // Handler Delete Data AJAX via pikdiAjax
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var btn = $(this);
                var url = btn.attr('href') || btn.data('url') || btn.closest('form').attr('action');
                var name = btn.closest('tr').find('td:eq(1)').text().trim();

                Swal.fire({
                    title: 'Hapus Data Jabatan?',
                    html: "Anda akan menghapus data: <b>" + name + "</b>.<br><small class='text-danger font-weight-bold'>Data yang dihapus tidak dapat dipulihkan!</small>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-danger btn-md px-3 mr-2',
                        cancelButton: 'btn btn-secondary btn-md px-3'
                    },
                    backdrop: `rgba(9, 75, 84, 0.25)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        pikdiAjax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            onSuccess: function(res) {
                                tableStruktural.ajax.reload(null, false);
                                tableFungsional.ajax.reload(null, false);
                                tablePangkat.ajax.reload(null, false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
