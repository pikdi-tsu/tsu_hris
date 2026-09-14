@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-user {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-user {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-user {
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
        .tsu-stat-card--tendik {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--dosen {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }
        .tsu-stat-card--admin {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
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

        /* Callout / Notice */
        .tsu-callout {
            border-radius: var(--tsu-radius, 8px);
            padding: 0.85rem 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.86rem;
            line-height: 1.45;
        }
        .tsu-callout--info {
            background: #f0f9ff;
            border: 1.5px solid #bae6fd;
            color: #0369a1;
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
        .tsu-btn-sync {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);
        }
        .tsu-btn-sync:hover {
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
        :title="$title ?? 'Data Pengguna Modul'"
        :icon="$menuIcon ?? 'fas fa-users'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @can('users:user:create')
                <form action="{{ route('users.user.sync') }}" method="POST" style="display:inline;" id="form-sync-user">
                    @csrf
                    <button type="submit" class="btn btn-sm tsu-btn-sync btn-sync" title="Tarik data user terbaru dari Homebase">
                        <i class="fas fa-sync-alt mr-1"></i> Update Users Lokal
                    </button>
                </form>
            @endcan
            <button type="button" class="btn btn-sm tsu-btn-reload ml-2" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-user">
                {{-- Total Pengguna --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Pengguna</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Akun</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Akun Modul HRIS Terdaftar
                    </div>
                </div>

                {{-- Tendik --}}
                <div class="tsu-stat-card tsu-stat-card--tendik">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Tenaga Kependidikan</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['tendik'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Pengguna Berstatus Tendik
                    </div>
                </div>

                {{-- Dosen --}}
                <div class="tsu-stat-card tsu-stat-card--dosen">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Tenaga Pendidik</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['dosen'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Orang</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Dosen / Pengajar Kampus
                    </div>
                </div>

                {{-- Administrator --}}
                <div class="tsu-stat-card tsu-stat-card--admin">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Administrator Modul</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['admin'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Akun</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Super Admin & Admin HRIS
                    </div>
                </div>
            </div>

            {{-- Notice Callout --}}
            <div class="tsu-callout tsu-callout--info mb-3">
                <i class="fas fa-info-circle fa-lg" style="color: #0284c7; flex-shrink: 0;"></i>
                <div>
                    Halaman ini digunakan untuk <strong>Monitoring & Manajemen Role Lokal</strong>. Penambahan User & sinkronisasi data profil dilakukan secara otomatis melalui SSO atau sinkronisasi dengan sistem Homebase.
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="tsu-card">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table tsu-table-modern table-hover w-100" id="table-user-modul">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">NO</th>
                                    <th style="width: 70px;" class="text-center">AVATAR</th>
                                    <th>NAMA USER</th>
                                    <th>EMAIL</th>
                                    <th>ROLE DI MODUL INI</th>
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

    {{-- ================= MODAL EDIT ROLE CONTAINER ================= --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="modal-edit-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                {{-- Loading State --}}
                <div class="text-center p-5">
                    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>
                    <p class="mt-2 text-muted" style="font-size: 0.85rem;">Sedang mengambil data role user...</p>
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
            var table = $('#table-user-modul').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('users.user.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'avatar', name: 'avatar', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'roles', name: 'roles' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[2, 'asc']],
                language: {
                    search: "Cari Pengguna:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengguna",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 pengguna",
                    infoFiltered: "(disaring dari _MAX_ total pengguna)",
                    zeroRecords: "Tidak ada data pengguna yang cocok",
                    emptyTable: "Belum ada data pengguna",
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
                    title: 'Sinkronisasi Pengguna?',
                    html: "Sistem akan memperbarui data pengguna & role dari <b>Homebase</b>.<br><small class='text-muted'>Pastikan server Homebase sedang aktif.</small>",
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

            // Event Listener Tombol Edit Role
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).attr('href') || $(this).data('url');

                if (!url) {
                    console.error('URL Edit tidak ditemukan!');
                    return;
                }

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>
                        <p class="mt-2 text-muted" style="font-size: 0.85rem;">Sedang mengambil data role...</p>
                    </div>
                `);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#modal-edit-content').html(response);
                    },
                    error: function() {
                        $('#modal-edit-content').html('<div class="alert alert-danger m-3">Gagal mengambil data role pengguna.</div>');
                    }
                });
            });

            // Modal Delete/Kick
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var userName = $(this).data('name') || 'User ini';

                Swal.fire({
                    title: 'Keluarkan Pengguna?',
                    html: `Apakah Anda yakin ingin mengeluarkan <strong>${userName}</strong> dari modul HRIS?<br><small class="text-muted">User harus login ulang untuk masuk ke modul ini.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-sign-out-alt mr-1"></i> Ya, Keluarkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
