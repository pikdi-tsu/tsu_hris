@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-mpp {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-mpp {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-mpp {
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
        .tsu-stat-card--waiting {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--approved {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }
        .tsu-stat-card--rejected {
            background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%);
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

        /* === TSU Tab Navigation (Underline Style) === */
        .tsu-tab-nav {
            display: flex;
            align-items: stretch;
            padding: 0 1.25rem;
            gap: 0.5rem;
            border-bottom: 2px solid var(--tsu-primary-light, #cce6e9) !important;
            margin: 0;
            background: #ffffff !important;
            list-style: none;
        }
        .tsu-tab-nav .nav-item {
            display: flex;
            margin-bottom: -2px;
        }
        .tsu-tab-nav .nav-link,
        .tsu-tab-nav a.nav-link {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.85rem 1.25rem;
            font-size: 0.86rem;
            font-weight: 600;
            color: #64748b !important;
            border: none !important;
            border-bottom: 3px solid transparent !important;
            background: transparent !important;
            background-color: transparent !important;
            border-radius: 0 !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
            box-shadow: none !important;
            cursor: pointer;
        }
        .tsu-tab-nav .nav-link:hover,
        .tsu-tab-nav a.nav-link:hover {
            color: var(--tsu-primary, #094b54) !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            border-bottom: 3px solid transparent !important;
        }
        .tsu-tab-nav .nav-link.active,
        .tsu-tab-nav a.nav-link.active {
            color: var(--tsu-primary-dark, #094b54) !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            border-bottom: 3px solid var(--tsu-primary, #094b54) !important;
            border-radius: 0 !important;
            font-weight: 700;
            box-shadow: none !important;
        }
        .tsu-tab-nav .nav-link .badge {
            font-size: 0.72rem;
            padding: 0.2rem 0.55rem;
            border-radius: 12px;
            font-weight: 700;
        }
        .tsu-tab-nav .nav-link.active .badge-warning {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            border: 1px solid #fde68a !important;
        }
        .tsu-tab-nav .nav-link:not(.active) .badge-warning {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }
        .tsu-tab-nav .nav-link.active .badge-secondary {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            border: 1px solid #bae6fd !important;
        }
        .tsu-tab-nav .nav-link:not(.active) .badge-secondary {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* === Filter Bar === */
        .tsu-filter-bar {
            background: #f8fafc;
            border-radius: var(--tsu-radius, 8px);
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid #e2e8f0;
        }
        .tsu-filter-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 0.35rem;
            display: block;
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
            font-size: 0.84rem;
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
        :title="$title ?? 'Manpower Planning'"
        :icon="$menuIcon ?? 'fas fa-users-cog'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-mpp">
                {{-- Total Pengajuan --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Usulan {{ $tahun }}</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Akumulasi Formasi Seluruh Unit
                    </div>
                </div>

                {{-- Menunggu Persetujuan --}}
                <div class="tsu-stat-card tsu-stat-card--waiting">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Menunggu Persetujuan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['waiting'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        {{ $stats['count_waiting'] ?? 0 }} Usulan Butuh Verifikasi SDM
                    </div>
                </div>

                {{-- Disetujui --}}
                <div class="tsu-stat-card tsu-stat-card--approved">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Disetujui SDM</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['approved'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Formasi Usulan Diterima
                    </div>
                </div>

                {{-- Ditolak --}}
                <div class="tsu-stat-card tsu-stat-card--rejected">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Ditolak SDM</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['rejected'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Formasi Usulan Tidak Disetujui
                    </div>
                </div>
            </div>

            {{-- Main Card: Tabs & Data Table --}}
            <div class="tsu-card">
                {{-- Underline Tab Navigation --}}
                <ul class="nav tsu-tab-nav" id="mpp-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-waiting" href="javascript:void(0)" onclick="changeTab('waiting')">
                            <i class="fas fa-clock mr-1"></i> Menunggu Persetujuan
                            <span class="badge badge-warning ml-1 font-weight-bold" id="badge-waiting">{{ $stats['count_waiting'] ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-history" href="javascript:void(0)" onclick="changeTab('history')">
                            <i class="fas fa-history mr-1"></i> Riwayat Persetujuan
                            <span class="badge badge-secondary ml-1 font-weight-bold" id="badge-history">{{ $stats['count_history'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>

                <div class="card-body p-4">
                    {{-- Filter Bar --}}
                    <div class="tsu-filter-bar">
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                <label class="tsu-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Tahun Perencanaan</label>
                                <select class="form-control form-control-sm font-weight-600" id="filter_tahun" onchange="filterData()">
                                    <option value="">Semua Tahun</option>
                                    @for($i = date('Y') - 1; $i <= date('Y') + 3; $i++)
                                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                                <label class="tsu-filter-label"><i class="fas fa-building mr-1"></i> Unit Kerja / Divisi</label>
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

                    {{-- Data Table --}}
                    <div class="table-responsive">
                        <table id="mpp-table" class="table tsu-table-modern table-hover w-100">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">NO</th>
                                    <th>UNIT / DIVISI</th>
                                    <th>JABATAN</th>
                                    <th style="width: 120px;">KEBUTUHAN</th>
                                    <th style="width: 80px;">TAHUN</th>
                                    <th style="width: 120px;">TIPE</th>
                                    <th style="width: 140px;">STATUS</th>
                                    <th style="width: 100px; text-align: center;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Modal Container for Approval --}}
    <div id="modal-container-approval"></div>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        var table;
        var currentTab = 'waiting';

        $(function() {
            table = $('#mpp-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('admin.mpp.datatables') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.tahun = $('#filter_tahun').val();
                        d.unit_id = $('#filter_unit').val();
                        d.status = currentTab;
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

            $('.select2').select2({ width: '100%' });

            $('#btn-reload').on('click', function() {
                let $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                table.ajax.reload(function() {
                    $btn.find('i').removeClass('fa-spin');
                });
            });
        });

        function changeTab(tabName) {
            currentTab = tabName;
            $('#mpp-tabs .nav-link').removeClass('active');
            if (tabName === 'waiting') {
                $('#tab-waiting').addClass('active');
            } else {
                $('#tab-history').addClass('active');
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
