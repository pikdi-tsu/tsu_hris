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
                {{-- Total Pegawai Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <i class="fas fa-users tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Total Pegawai Aktif</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalAktif) }}
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Dosen & Tendik Terdaftar
                    </div>
                </div>

                {{-- Kandidat Onboarding --}}
                <div class="tsu-stat-card tsu-stat-card--onboard">
                    <i class="fas fa-user-plus tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Kandidat Onboarding</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($onboardingCount) }}
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Bergabung dlm 1 Thn Terakhir
                    </div>
                </div>

                {{-- Master Checklist Tugas --}}
                <div class="tsu-stat-card tsu-stat-card--tasks">
                    <i class="fas fa-tasks tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Master Checklist</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalTasks ?? 0) }}
                            <span class="tsu-stat-card__unit">Tugas</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Orientasi & Serah Terima
                    </div>
                </div>

                {{-- Offboarding (Resign) --}}
                <div class="tsu-stat-card tsu-stat-card--offboard">
                    <i class="fas fa-user-slash tsu-stat-card__icon"></i>
                    <div>
                        <div class="tsu-stat-card__title">Offboarding (Resign)</div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($totalResign) }}
                            <span class="tsu-stat-card__unit">Pegawai</span>
                        </div>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Pelepasan Tugas & Aset
                    </div>
                </div>
            </div>

            {{-- Card Panduan (Placed BELOW Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Pelaksanaan Onboarding & Offboarding Pegawai"
                description="Modul Pelaksanaan Onboarding & Offboarding digunakan untuk memantau pemenuhan butir kegiatan orientasi pegawai baru serta protokol serah terima saat terjadi pengunduran diri atau peralihan tugas."
                :connections="[
                    ['label' => 'Kelola Master Tugas', 'route' => 'admin.master-onboarding-offboarding.index', 'icon' => 'fas fa-tasks'],
                    ['label' => 'Data Karyawan', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-users'],
                    ['label' => 'Struktur Organisasi', 'route' => 'admin.struktur-organisasi.index', 'icon' => 'fas fa-sitemap']
                ]"
                impact="Setiap butir checklist yang diverifikasi akan terekam waktu penyelesaiannya dan nama verifikator yang menyetujui, memastikan akuntabilitas proses orientasi dan serah terima aset universitas."
            />

            {{-- Main Underline Tab Card Container --}}
            <div class="card card-primary card-outline card-tabs tsu-card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs tsu-tab-nav" id="onoff-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-onboarding-link" data-toggle="pill" href="#tab-onboarding" role="tab">
                                <i class="fas fa-user-plus mr-2 text-success"></i> Onboarding Pegawai Baru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-offboarding-link" data-toggle="pill" href="#tab-offboarding" role="tab">
                                <i class="fas fa-user-minus mr-2 text-warning"></i> Offboarding Pegawai Resign
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content" id="onoff-tabContent">
                        {{-- Tab 1: Onboarding --}}
                        <div class="tab-pane fade show active" id="tab-onboarding" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-onboarding-pegawai" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">NO</th>
                                            <th width="35%">IDENTITAS PEGAWAI</th>
                                            <th width="16%" class="text-center">TGL BERGABUNG</th>
                                            <th width="30%">PROGRESS KELENGKAPAN</th>
                                            <th width="14%" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 2: Offboarding --}}
                        <div class="tab-pane fade" id="tab-offboarding" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-offboarding-pegawai" class="table tsu-table-modern table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">NO</th>
                                            <th width="38%">IDENTITAS PEGAWAI</th>
                                            <th width="42%">PROGRESS SERAH TERIMA</th>
                                            <th width="15%" class="text-center">AKSI</th>
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
            let dtOnboarding = $('#table-onboarding-pegawai').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 25,
                ajax: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-onboarding') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'identitas', name: 'nama', className: 'align-middle' },
                    { data: 'tgl_bergabung_fmt', name: 'tgl_bergabung', className: 'text-center align-middle' },
                    { data: 'progress', name: 'progress', orderable: false, searchable: false, className: 'align-middle' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                ],
                language: {
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
                    emptyTable: "Tidak ada data pegawai onboarding",
                    zeroRecords: "Tidak ditemukan pegawai yang sesuai"
                }
            });

            let dtOffboarding = null;
            $('#tab-offboarding-link').on('shown.bs.tab', function() {
                if (!dtOffboarding) {
                    dtOffboarding = $('#table-offboarding-pegawai').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        pageLength: 25,
                        ajax: "{{ route('admin.pelaksanaan-onboarding-offboarding.json-offboarding') }}",
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                            { data: 'identitas', name: 'nama', className: 'align-middle' },
                            { data: 'progress', name: 'progress', orderable: false, searchable: false, className: 'align-middle' },
                            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                        ],
                        language: {
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
                            emptyTable: "Tidak ada data pegawai offboarding",
                            zeroRecords: "Tidak ditemukan pegawai yang sesuai"
                        }
                    });
                } else {
                    dtOffboarding.columns.adjust().responsive.recalc();
                }
            });

            // Adjust columns on tab switch
            $('a[data-toggle="pill"], a[data-toggle="tab"]').on('shown.bs.tab', function() {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            });

            // Tombol Refresh Data
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                if (dtOnboarding) dtOnboarding.ajax.reload(null, false);
                if (dtOffboarding) {
                    dtOffboarding.ajax.reload(function() {
                        setTimeout(function() {
                            $btn.find('i').removeClass('fa-spin');
                        }, 400);
                    }, false);
                } else {
                    setTimeout(function() {
                        $btn.find('i').removeClass('fa-spin');
                    }, 400);
                }
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
                            if (dtOnboarding) dtOnboarding.ajax.reload(null, false);
                            if (dtOffboarding) dtOffboarding.ajax.reload(null, false);
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
