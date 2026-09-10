@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-menu {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-menu {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-menu {
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
        .tsu-stat-card--root {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--sub {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--active {
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
        tr.tsu-row-root {
            background-color: rgba(9, 75, 84, 0.02) !important;
        }
        tr.tsu-row-root:hover {
            background-color: rgba(9, 75, 84, 0.05) !important;
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
        :title="$title ?? 'Manajemen Menu Sidebar'"
        :icon="$menuIcon ?? 'fas fa-bars'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Panduan --}}
            <button type="button" class="btn btn-sm btn-outline-info mr-2" data-toggle="collapse" data-target="#panduanMenu" title="Lihat Panduan Struktur Menu" style="border-radius: 8px; font-weight: 600;">
                <i class="fas fa-question-circle mr-1"></i> Bantuan: Cara Baca Tabel
            </button>

            {{-- Tombol Tambah Menu --}}
            @can('system:menu:create')
                <button type="button" class="btn btn-sm tsu-btn-primary-action mr-2" data-toggle="modal" data-target="#modal-create" title="Tambah Menu Baru">
                    <i class="fas fa-plus mr-1"></i> Tambah Menu
                </button>
            @else
                <span class="badge badge-secondary p-2 mr-2 shadow-sm" style="cursor: not-allowed; opacity: 0.7; border-radius: 8px;" title="Anda tidak memiliki akses ke action ini">
                    <i class="fas fa-lock mr-1"></i> Tambah Menu (No Access)
                </span>
            @endcan

            {{-- Tombol Refresh Data --}}
            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-menu">
                {{-- Total Menu --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Menu</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Menu</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Item Navigasi Terdaftar di Sistem
                    </div>
                </div>

                {{-- Menu Utama / Root --}}
                <div class="tsu-stat-card tsu-stat-card--root">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Menu Utama (Root)</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-folder-open"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['root'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Induk</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Modul Navigasi Utama Level 0
                    </div>
                </div>

                {{-- Sub-Menu --}}
                <div class="tsu-stat-card tsu-stat-card--sub">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Sub-Menu</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['sub'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Item</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Cabang Navigasi Bertingkat (Level 1+)
                    </div>
                </div>

                {{-- Status Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--active">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Menu Aktif</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['active'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Aktif</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Ditampilkan pada Sidebar Pengguna
                    </div>
                </div>
            </div>

            {{-- Collapsible Panduan Hierarki Menu --}}
            <div class="collapse mb-3" id="panduanMenu">
                <div class="tsu-guide-card">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="font-weight-bold mb-0" style="color: var(--tsu-primary, #094b54);">
                            <i class="fas fa-info-circle mr-2 text-info"></i> Panduan Hierarki & Pengelolaan Menu Sidebar
                        </h6>
                        <button type="button" class="close text-muted" data-toggle="collapse" data-target="#panduanMenu" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge mr-2" style="background: #094b54; color: #fff; font-size: 0.72rem;">Level 0</span>
                                    <b style="color: #094b54; font-size: 0.85rem;">Menu Utama (Root)</b>
                                </div>
                                <small class="text-muted d-block">Baris menu level teratas. Dapat berupa modul langsung (jika terhubung route) atau folder dropdown (jika route <code>#</code>).</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge mr-2" style="background: #0284c7; color: #fff; font-size: 0.72rem;">Level 1</span>
                                    <b style="color: #0284c7; font-size: 0.85rem;">Sub-Menu Langsung</b>
                                </div>
                                <small class="text-muted d-block">Menu turunan langsung di bawah menu utama dengan tanda cabang <code>↳</code> yang terarah ke route fitur aplikasi.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge mr-2" style="background: #64748b; color: #fff; font-size: 0.72rem;">Level 2+</span>
                                    <b style="color: #475569; font-size: 0.85rem;">Sub-Menu Bertingkat</b>
                                </div>
                                <small class="text-muted d-block">Turunan lebih dalam dengan lekukan indentasi khusus untuk pengelompokan fungsi modul yang lebih mendalam.</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center text-xs text-muted" style="gap: 1.5rem;">
                        <span><i class="fas fa-lock mr-1 text-secondary"></i> <b>Menu Inti (Core):</b> Menu vital seperti Dashboard dikunci otomatis dari aksi hapus demi integritas navigasi.</span>
                        <span><i class="fas fa-key mr-1 text-primary"></i> <b>Permission:</b> Menentukan visibilitas menu sesuai hak akses role pengguna yang terdaftar.</span>
                    </div>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="tsu-card">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table tsu-table-modern table-hover w-100" id="table-menu">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="28%">Nama Menu</th>
                                    <th width="8%" class="text-center">Icon</th>
                                    <th width="22%">Route</th>
                                    <th width="20%">Permission</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="8%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loaded via DataTables AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ================= MODAL CREATE ================= --}}
    <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
                    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
                        <i class="fas fa-plus-circle mr-2 text-warning"></i> Tambah Menu Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('system.menu.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-sm text-dark">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Data Karyawan" style="border-radius: 8px;">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-sm text-dark">Icon Class (FontAwesome)</label>
                                    <div class="input-group">
                                        <input type="text" name="icon" class="form-control" placeholder="Default: fas fa-box" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; background: #f8fafc;"><i class="fas fa-icons text-muted"></i></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">
                                            Default: <code>fas fa-box</code>
                                        </small>
                                        <small>
                                            <a href="https://fontawesome.com/search?ic=free-collection" target="_blank" class="text-info font-weight-bold">
                                                <i class="fas fa-external-link-alt mr-1"></i> Referensi Icon
                                            </a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-sm text-dark">Route Laravel</label>
                                    <input type="text" name="route" id="input_route" class="form-control" placeholder="Contoh: users.users.index" style="border-radius: 8px;">
                                    <small class="text-muted d-block mt-1">Isi <code>#</code> atau kosongkan jika berupa Menu Induk (Dropdown).</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-sm text-dark">Permission Kunci</label>
                                    <select name="permission_name" id="input_permission" class="form-control select2" style="width: 100%;">
                                        <option value="">-- Public (Bebas Akses) --</option>
                                        @if(isset($permissions))
                                            @foreach($permissions as $perm)
                                                <option value="{{ $perm }}">{{ $perm }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small id="help_permission" class="text-danger mt-1 font-weight-bold" style="display: none;">
                                        <i class="fas fa-ban mr-1"></i> Permission dinonaktifkan untuk Menu Folder / Induk.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-sm text-dark">Parent Menu (Induk)</label>
                                    <select name="parent_id" id="input_parent" class="form-control select2" style="width: 100%;">
                                        <option value="">-- Jadikan Menu Utama (Root) --</option>
                                        @if(isset($parents))
                                            @foreach($parents as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-sm text-dark">Urutan Tampil (Order)</label>
                                    <input type="number" name="order" class="form-control" value="0" style="border-radius: 8px;">
                                    <small class="text-muted">Angka lebih kecil tampil lebih atas.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-sm text-dark">Status Visibilitas</label>
                                    <div class="custom-control custom-switch pt-2">
                                        <input type="checkbox" class="custom-control-input" id="create_isactive" name="isactive" value="1" checked>
                                        <label class="custom-control-label font-weight-bold text-sm text-success" for="create_isactive">Aktif (Tampil di Sidebar)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn tsu-btn-primary-action px-4">
                            <i class="fas fa-check mr-1"></i> Simpan Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL EDIT (AJAX CONTAINER) ================= --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" id="modal-edit-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Loading State --}}
                <div class="text-center p-5">
                    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted font-weight-bold">Sedang mengambil rincian data menu...</p>
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
            // Inisialisasi Select2 di modal create
            if ($.fn.select2) {
                $('#modal-create .select2').select2({
                    dropdownParent: $('#modal-create'),
                    width: '100%'
                });
            }

            // Hapus instance lama jika ada
            if ($.fn.DataTable.isDataTable('#table-menu')) {
                $('#table-menu').DataTable().destroy();
            }

            // Init DataTables Baru
            var table = $('#table-menu').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('system.menu.json') }}",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari nama menu, route, permission...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    zeroRecords: "Tidak ada menu yang sesuai",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ menu",
                    infoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total menu)",
                    processing: '<div class="d-flex align-items-center justify-content-center" style="gap: 0.5rem;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <span>Memuat data menu...</span></div>'
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'icon', name: 'icon', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'route', name: 'route' },
                    { data: 'permission', name: 'permission_name' },
                    { data: 'status', name: 'isactive', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: []
            });

            // Reload table button
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                table.ajax.reload(function() {
                    $btn.find('i').removeClass('fa-spin');
                }, false);
            });

            // AUTO DISABLE PERMISSION PADA MODAL CREATE
            function adjustCreatePermission() {
                var routeVal  = $('#input_route').val();
                var parentVal = $('#input_parent').val();
                var permInput = $('#input_permission');
                var helpText  = $('#help_permission');

                if ( (parentVal === '' || parentVal == null) && (routeVal === '' || routeVal === '#') ) {
                    permInput.val('').trigger('change');
                    permInput.prop('disabled', true);
                    helpText.show();
                } else {
                    permInput.prop('disabled', false);
                    helpText.hide();
                }
            }

            $('#input_route').on('keyup change', function() {
                adjustCreatePermission();
            });

            $('#input_parent').on('change', function() {
                adjustCreatePermission();
            });

            adjustCreatePermission();

            $('#modal-create form').on('submit', function() {
                $('#input_permission').prop('disabled', false);
            });

            // Event Listener Tombol Edit (AJAX Modal)
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(
                    '<div class="text-center p-5">' +
                    '    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>' +
                    '    <p class="mt-3 text-muted font-weight-bold">Sedang mengambil data menu...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#modal-edit-content').html(response);
                    },
                    error: function() {
                        $('#modal-edit-content').html(
                            '<div class="modal-body p-4 text-center">' +
                            '    <div class="alert alert-danger mb-0">Gagal mengambil data menu. Silakan coba kembali.</div>' +
                            '</div>' +
                            '<div class="modal-footer p-3" style="background: #f8fafc;">' +
                            '    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Tutup</button>' +
                            '</div>'
                        );
                    }
                });
            });

            // Event Listener Tombol Delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Hapus Menu Ini?',
                    text: "Data menu yang dihapus tidak dapat dipulihkan kembali!",
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
                        Swal.fire({
                            title: 'Sedang Menghapus Menu...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
