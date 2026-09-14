@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-perm {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-perm {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-perm {
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
        .tsu-stat-card--admin {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--users {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }
        .tsu-stat-card--system {
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
        :title="$title ?? 'Role Permissions'"
        :icon="$menuIcon ?? 'fas fa-file-shield'"
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
            <div class="tsu-stat-grid-perm">
                {{-- Total Hak Akses --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Hak Akses</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Izin</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Master Permission Terdaftar
                    </div>
                </div>

                {{-- Modul Admin --}}
                <div class="tsu-stat-card tsu-stat-card--admin">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Modul Admin</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['admin'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Izin</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Akses Fitur Manajemen SDM
                    </div>
                </div>

                {{-- Modul Users --}}
                <div class="tsu-stat-card tsu-stat-card--users">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Modul Users</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['users'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Izin</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Akses Self-Service Pegawai
                    </div>
                </div>

                {{-- Modul System --}}
                <div class="tsu-stat-card tsu-stat-card--system">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Modul System</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-cogs"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['system'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Izin</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Akses Pengaturan &amp; Role
                    </div>
                </div>
            </div>

            {{-- 2 Columns Layout: Table & Form --}}
            <div class="row">
                {{-- TABEL PERMISSION (KIRI) --}}
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="tsu-card h-100">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="font-weight-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                                <i class="fas fa-list-alt mr-2 text-primary"></i> Daftar Hak Akses (Permission)
                            </h6>
                            <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 0.8rem;">
                                Total: {{ number_format($stats['total'] ?? 0) }} Key
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table tsu-table-modern table-hover w-100" id="table-permission">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;" class="text-center">NO</th>
                                            <th>NAMA PERMISSION (KEY)</th>
                                            <th style="width: 100px;" class="text-center">GUARD</th>
                                            <th style="width: 110px;" class="text-center">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FORM TAMBAH / EDIT PERMISSION (KANAN) --}}
                <div class="col-lg-4">
                    <div class="tsu-card">
                        <div class="card-header py-3 px-4" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff;">
                            <h6 class="font-weight-bold mb-0 d-flex align-items-center" id="form-title" style="font-size: 0.95rem;">
                                <i class="fas fa-plus-circle mr-2"></i> Tambah Permission Baru
                            </h6>
                        </div>
                        <form action="{{ route('system.permission.store') }}" method="POST" id="form-permission">
                            @csrf
                            <div id="method-put"></div>

                            <div class="card-body p-4">
                                <div class="form-group mb-3">
                                    <label for="input-name" class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                        Nama Permission (Key) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-key text-muted"></i></span>
                                        </div>
                                        <input type="text" name="name" id="input-name" class="form-control border-left-0" placeholder="contoh: admin:karyawan:view" autocomplete="off" required style="border-radius: 0 8px 8px 0; font-size: 0.86rem;">
                                    </div>
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.76rem;">
                                        Format saran: <code>modul:fitur:aksi</code> atau <code>aplikasi:fitur:aksi</code>
                                    </small>
                                </div>

                                {{-- Info Box --}}
                                <div class="p-3 rounded border mb-0" style="background: #f8fafc; border-color: #e2e8f0 !important; font-size: 0.82rem; line-height: 1.45;">
                                    <div class="d-flex align-items-center font-weight-bold text-dark mb-1">
                                        <i class="fas fa-info-circle text-info mr-1"></i> Format Penamaan Standar:
                                    </div>
                                    <div class="text-muted mb-2">
                                        Gunakan pola <code>[Modul]:[Fitur]:[Aksi]</code> agar izin otomatis terkelompokkan dalam matriks role.
                                    </div>
                                    <div class="small">
                                        <strong>Contoh:</strong><br>
                                        &bull; <code>admin:cuti:approve</code><br>
                                        &bull; <code>users:lembur:create</code><br>
                                        &bull; <code>system:role:view</code>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-light px-4 py-3 border-top d-flex justify-content-end align-items-center" style="border-color: #e2e8f0 !important; gap: 0.5rem;">
                                @can('system:permission:create')
                                    <button type="submit" class="btn btn-sm px-3" id="btn-submit" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 6px rgba(9, 75, 84, 0.2);">
                                        <i class="fas fa-save mr-1"></i> Simpan Permission
                                    </button>
                                @else
                                    <span id="span-submit" class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.7;" title="Anda tidak memiliki akses ke action ini">
                                        <i class="fas fa-lock mr-1"></i> Simpan (No Access)
                                    </span>
                                @endcan

                                @can('system:permission:edit')
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 btn-reset d-none" style="border-radius: 8px; font-weight: 600;">
                                        Batal Edit
                                    </button>
                                    <button type="submit" class="btn btn-sm px-3 btn-warning d-none font-weight-bold" id="btn-edit" style="border-radius: 8px; color: #92400e; background-color: #fef3c7; border: 1px solid #fde68a;">
                                        <i class="fas fa-check-circle mr-1"></i> Perbarui
                                    </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#table-permission').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('system.permission.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'guard_name', name: 'guard_name', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                language: {
                    search: "Cari Permission:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ izin",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 izin",
                    infoFiltered: "(disaring dari _MAX_ total izin)",
                    zeroRecords: "Tidak ada data permission yang cocok",
                    emptyTable: "Belum ada data permission",
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

            // LOGIC EDIT
            $('body').on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var updateUrl = "{{ route('system.permission.update', ':id') }}".replace(':id', id);

                // Ubah Judul & Form
                $('#form-title').html('<i class="fas fa-pencil-alt mr-2"></i> Edit Permission');
                $('#form-permission').attr('action', updateUrl);
                $('#input-name').val(name).focus();

                // Tambahkan Method PUT
                $('#method-put').html('<input type="hidden" name="_method" value="PUT">');

                // Ganti Tombol
                $('#btn-submit').addClass('d-none');
                $('#span-submit').addClass('d-none');
                $('#btn-edit').removeClass('d-none');
                $('.btn-reset').removeClass('d-none');

                // Smooth scroll to form on mobile
                if ($(window).width() < 992) {
                    $('html, body').animate({
                        scrollTop: $("#form-permission").offset().top - 100
                    }, 400);
                }
            });

            // LOGIC BATAL EDIT
            $('.btn-reset').click(function() {
                // Reset ke Mode Create
                $('#form-title').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Permission Baru');
                $('#form-permission').attr('action', "{{ route('system.permission.store') }}");
                $('#input-name').val('');
                $('#method-put').html('');

                $(this).addClass('d-none');
                $('#btn-edit').addClass('d-none');
                $('#btn-submit').removeClass('d-none');
                $('#span-submit').removeClass('d-none');
            });

            // LOGIC DELETE
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var name = $(this).data('name') || $(this).closest('tr').find('code').text().trim() || 'Permission ini';

                Swal.fire({
                    title: 'Hapus Permission?',
                    html: `Apakah Anda yakin ingin menghapus permission: <b>${name}</b>?<br><small class='text-danger'>Pastikan permission ini tidak sedang dipakai dalam kodingan!</small>`,
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
                            text: 'Sedang menghapus permission...',
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
