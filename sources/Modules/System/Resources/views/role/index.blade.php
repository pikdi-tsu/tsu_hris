@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-role {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-role {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-role {
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
        .tsu-stat-card--permissions {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--core {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--users {
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

        /* === Guide Card === */
        .tsu-guide-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
            border-left: 4px solid var(--tsu-primary, #094b54);
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
        :title="$title ?? 'Role Matrix'"
        :icon="$menuIcon ?? 'fas fa-user-shield'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Panduan --}}
            <button type="button" class="btn btn-sm btn-outline-info mr-2" data-toggle="collapse" data-target="#panduanRole" title="Lihat Panduan Matrix Role" style="border-radius: 8px; font-weight: 600;">
                <i class="fas fa-question-circle mr-1"></i> Panduan
            </button>

            {{-- TOMBOL CREATE --}}
            @can('system:role:create')
                <a href="{{ route('system.role.create') }}" class="btn btn-sm tsu-btn-primary-action btn-create mr-2" title="Buat Role Lokal Baru">
                    <i class="fas fa-plus mr-1"></i> Tambah Role
                </a>
            @endcan

            {{-- TOMBOL SYNC --}}
            @can('system:role:create')
                <form action="{{ route('system.role.sync') }}" method="POST" style="display:inline;" id="form-sync-role">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary btn-sync mr-2" style="border-radius: 8px; font-weight: 600;" title="Tarik data role dari Homebase">
                        <i class="fas fa-sync-alt mr-1"></i> Sync Roles dari Homebase
                    </button>
                </form>
            @endcan

            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-role">
                {{-- Total Role --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Role Modul</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_roles'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Role</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Role Terdaftar di Sistem
                    </div>
                </div>

                {{-- Total Permissions --}}
                <div class="tsu-stat-card tsu-stat-card--permissions">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Hak Akses Modul</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_permissions'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Izin</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Master Permission Terkonfigurasi
                    </div>
                </div>

                {{-- Role Inti --}}
                <div class="tsu-stat-card tsu-stat-card--core">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Role Inti Sistem</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['core_roles'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Role</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Role Bawaan (Protected)
                    </div>
                </div>

                {{-- Pengguna Diberi Role --}}
                <div class="tsu-stat-card tsu-stat-card--users">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Pengguna Terdaftar</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users-cog"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['assigned_users'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Akun</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Pengguna dengan Role Aktif
                    </div>
                </div>
            </div>

            {{-- Panduan Manajemen Roles (Collapsible) --}}
            <div class="collapse" id="panduanRole">
                <div class="tsu-guide-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                            <i class="fas fa-lightbulb text-warning mr-2"></i> Panduan Manajemen Matrix Role
                        </h6>
                        <button type="button" class="close" data-toggle="collapse" data-target="#panduanRole" style="outline: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="row" style="font-size: 0.85rem;">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="font-weight-bold text-secondary text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Tipe Role:</div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge mr-2 font-weight-600" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; width: 120px; padding: 0.35rem 0.5rem;">
                                        <i class="fas fa-globe mr-1"></i> Global
                                    </span>
                                    <span>Role dari Homebase (Pusat). <span class="text-muted">Nama & Hapus terkunci.</span></span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge mr-2 font-weight-600" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; width: 120px; padding: 0.35rem 0.5rem;">
                                        <i class="fas fa-lock mr-1"></i> Lokal Inti
                                    </span>
                                    <span>Role bawaan sistem (Admin/Dosen/Tendik). <span class="text-muted">Izin dapat diatur.</span></span>
                                </li>
                                <li class="mb-0 d-flex align-items-center">
                                    <span class="badge badge-light border mr-2 font-weight-600 text-secondary" style="width: 120px; padding: 0.35rem 0.5rem;">
                                        <i class="fas fa-user-tag mr-1 text-primary"></i> Lokal Custom
                                    </span>
                                    <span>Role buatan sendiri. <span class="text-muted">Bebas edit nama, izin, &amp; hapus.</span></span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <div class="font-weight-bold text-secondary text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Fitur & Hak Akses:</div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge mr-2 font-weight-700" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; width: 120px; padding: 0.35rem 0.5rem;">
                                        <i class="fas fa-crown mr-1"></i> Full Access
                                    </span>
                                    <span>Hak akses mutlak (Super Admin). <span class="text-muted">Mengabaikan pembatasan permission.</span></span>
                                </li>
                                <li class="mb-0 d-flex align-items-center">
                                    <span class="badge badge-light border text-primary mr-2 font-weight-600" style="width: 120px; padding: 0.35rem 0.5rem;">
                                        <i class="fas fa-sync-alt mr-1"></i> Sync Roles
                                    </span>
                                    <span>Gunakan tombol ini jika terdapat role baru yang ditambahkan di Homebase.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="tsu-card">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table tsu-table-modern table-hover w-100" id="table-role">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">NO</th>
                                    <th>NAMA ROLE</th>
                                    <th style="width: 220px;">JUMLAH IZIN (PERMISSION)</th>
                                    <th style="width: 200px;">TIPE ROLE</th>
                                    <th style="width: 120px;" class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- MODAL EDIT CONTAINER --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content" id="modal-edit-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                {{-- Loading State --}}
                <div class="text-center p-5">
                    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>
                    <p class="mt-2 text-muted" style="font-size: 0.85rem;">Sedang memuat form role...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#table-role').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('system.role.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'permissions_count', name: 'permissions_count', searchable: false },
                    { data: 'is_identity_badge', name: 'is_identity_badge' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "Cari Role:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ role",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 role",
                    infoFiltered: "(disaring dari _MAX_ total role)",
                    zeroRecords: "Tidak ada data role yang cocok",
                    emptyTable: "Belum ada data role",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                }
            });

            $('#btn-reload').on('click', function() {
                let $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                table.ajax.reload(function() {
                    $btn.find('i').removeClass('fa-spin');
                });
            });

            // Logic Sync Roles Homebase
            $('body').on('click', '.btn-sync', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Sinkronisasi Role?',
                    html: "Sistem akan mengambil data role terbaru dari <b>Homebase</b>.<br><small class='text-muted'>Pastikan server Homebase sedang aktif.</small>",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-sync fa-spin mr-1"></i> Ya, Sync Sekarang!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#094b54',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Sedang Menghubungkan...',
                            html: 'Mohon tunggu, sedang meminta data ke Homebase...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });

            // Logic Modal Create
            $('body').on('click', '.btn-create', function(e) {
                e.preventDefault();
                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>
                        <p class="mt-2 text-muted" style="font-size: 0.85rem;">Memuat Form Role Baru...</p>
                    </div>
                `);

                $.ajax({
                    url: $(this).attr('href'),
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form role.</div>`);
                    }
                });
            });

            // Logic Modal Edit
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();

                var url = $(this).data('url') || $(this).attr('href');
                if (!url) {
                    console.error('URL Edit tidak ditemukan!');
                    return;
                }

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>
                        <p class="mt-2 text-muted" style="font-size: 0.85rem;">Mengambil Data Role...</p>
                    </div>
                `);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal mengambil data role. Error: ${xhr.status}</div>`);
                    }
                });
            });

            // Logic delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();

                var form = $(this).closest('form');
                var name = $(this).closest('tr').find('td:eq(1)').text().trim();

                Swal.fire({
                    title: 'Hapus Role Lokal?',
                    html: `Apakah Anda yakin ingin menghapus role: <b>${name}</b>?<br><small class='text-danger'>Data yang dihapus tidak dapat dikembalikan!</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang menghapus role...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
