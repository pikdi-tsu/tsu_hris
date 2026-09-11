@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <style>
        /* TSU Tab Navigation (Underline style matching Lembur Karyawan) */
        .card-tabs .card-header .nav-tabs.tsu-tab-nav,
        .tsu-tab-nav {
            display: flex;
            align-items: stretch;
            padding: 0 1rem;
            gap: 0.25rem;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            margin: 0;
            background: #ffffff;
            list-style: none;
        }
        .tsu-tab-nav .nav-item {
            display: flex;
            margin-bottom: -2px;
        }
        .card-tabs .card-header .nav-tabs.tsu-tab-nav .nav-link,
        .tsu-tab-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.1rem;
            font-size: 0.83rem;
            font-weight: 600;
            color: #6c757d !important;
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

        /* Modern Table */
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
            font-size: 0.84rem;
            padding: 0.75rem 1rem !important;
            border-color: #f1f5f9 !important;
        }
        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* Reload button */
        .tsu-btn-reload {
            color: var(--tsu-primary, #094b54);
            background: #ffffff;
            border: 1.5px solid var(--tsu-primary-light, #cce6e9);
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }

        /* Pagination & Search tweaks */
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
        title="Data Riwayat Izin & Cuti"
        :icon="$menuIcon ?? 'fas fa-history'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline card-tabs tsu-card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs tsu-tab-nav" id="riwayatTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="tab-cuti-btn" data-toggle="tab" href="#content-cuti" role="tab" aria-controls="content-cuti" aria-selected="true">
                                <i class="fas fa-calendar-minus mr-2"></i> Riwayat Cuti
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tab-izin-btn" data-toggle="tab" href="#content-izin" role="tab" aria-controls="content-izin" aria-selected="false">
                                <i class="fas fa-envelope-open-text mr-2"></i> Riwayat Izin
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content" id="riwayatTabsContent">
                        {{-- Tab Cuti --}}
                        <div class="tab-pane fade show active p-3" id="content-cuti" role="tabpanel" aria-labelledby="tab-cuti-btn">
                            <div class="table-responsive">
                                <table id="table-cuti" class="table table-bordered table-hover tsu-table-modern w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="18%">Nama Pegawai</th>
                                            <th width="13%">Jenis Cuti</th>
                                            <th width="15%">Tanggal Cuti</th>
                                            <th width="16%">Keterangan</th>
                                            <th width="11%" class="text-center">Berkas Bukti</th>
                                            <th width="11%">Approval Atasan</th>
                                            <th width="11%">Approval SDM</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab Izin --}}
                        <div class="tab-pane fade p-3" id="content-izin" role="tabpanel" aria-labelledby="tab-izin-btn">
                            <div class="table-responsive">
                                <table id="table-izin" class="table table-bordered table-hover tsu-table-modern w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="18%">Nama Pegawai</th>
                                            <th width="13%">Jenis Izin</th>
                                            <th width="15%">Tanggal Izin</th>
                                            <th width="16%">Keterangan</th>
                                            <th width="11%" class="text-center">Berkas Bukti</th>
                                            <th width="11%">Approval Atasan</th>
                                            <th width="11%">Approval SDM</th>
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

    {{-- Container Modal (for future details/actions) --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Dynamic via Ajax --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var dtLanguage = {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-3x mb-2" style="opacity:0.35;"></i><p class="mb-0 font-weight-500">Belum ada data riwayat tercatat.</p></div>',
                zeroRecords: '<div class="text-center py-4 text-muted"><i class="fas fa-search fa-2x mb-2" style="opacity:0.35;"></i><p class="mb-0">Tidak ditemukan data yang sesuai.</p></div>',
                search: "",
                searchPlaceholder: "Cari riwayat...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                processing: '<div class="text-center p-3 text-primary"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 mb-0 font-weight-bold" style="font-size:.85rem;">Memuat data...</p></div>',
                paginate: {
                    first: '<i class="fas fa-angle-double-left"></i>',
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>',
                    last: '<i class="fas fa-angle-double-right"></i>'
                }
            };

            // Inisialisasi DataTable Cuti
            var tableCuti = $('#table-cuti').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.riwayat-izincuti.jsoncuti') }}",
                language: dtLanguage,
                order: [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama', name: 'user.nama' },
                    { data: 'jeniscuti', name: 'masterCuti.jeniscuti' },
                    { data: 'tanggalcuti', name: 'tanggalmulai' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'file_bukti', name: 'file_bukti', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'approvalatasan', name: 'statusatasan' },
                    { data: 'approvalsdm', name: 'statushrd' }
                ]
            });

            // Inisialisasi DataTable Izin
            var tableIzin = $('#table-izin').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.riwayat-izincuti.jsonizin') }}",
                language: dtLanguage,
                order: [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama', name: 'user.nama' },
                    { data: 'jenisizin', name: 'masterIzin.jenisizin' },
                    { data: 'tanggalizin', name: 'tanggalmulai' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'file_bukti', name: 'file_bukti', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'approvalatasan', name: 'statusatasan' },
                    { data: 'approvalsdm', name: 'statushrd' }
                ]
            });

            // Tab switch click handler
            $('#riwayatTabs a[data-toggle="tab"]').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Adjust columns on tab switch
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });

            // Reload button listener
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                var $icon = $btn.find('i');
                $icon.addClass('fa-spin');
                $btn.prop('disabled', true);

                tableCuti.ajax.reload(function() {
                    tableIzin.ajax.reload(function() {
                        $icon.removeClass('fa-spin');
                        $btn.prop('disabled', false);
                    });
                });
            });
        });
    </script>
@endsection
