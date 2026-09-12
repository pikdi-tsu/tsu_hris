@extends('system::template.admin.header')
@section('title', $title ?? 'Data Master Lembur')

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

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

        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-lembur {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .tsu-stat-grid-lembur {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .tsu-stat-grid-lembur {
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
            opacity: 0.9;
        }

        .tsu-stat-card__value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.3rem;
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

        /* Stat Card Gradient Variations */
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }

        .tsu-stat-card--active {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        .tsu-stat-card--pengajuan {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }

        .tsu-stat-card--bulan-ini {
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

        .tsu-badge-info-clean {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            font-size: 0.78rem;
            padding: 0.35rem 0.75rem;
            font-weight: 600;
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
            border-top: none !important;
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
            background: linear-gradient(135deg, var(--tsu-primary, #094b54) 0%, #0c6170 100%) !important;
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
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
            padding: 0.38rem 0.75rem;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--tsu-primary, #094b54) !important;
            box-shadow: 0 0 0 3px rgba(9, 75, 84, 0.12) !important;
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: var(--tsu-radius, 8px) !important;
            border: 1.5px solid #cbd5e1;
            padding: 0.35rem 1.8rem 0.35rem 0.65rem;
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Data Master Lembur'"
        subtitle="Kelola jenis dan kategori penugasan lembur kerja pegawai TSU"
        icon="fas fa-business-time"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            {{-- Tombol Tambah Master Lembur --}}
            @can('admin:master-lembur:create')
                <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal mr-2" data-url="{{ route('admin.master-lembur.create') }}" title="Tambah Master Lembur">
                    <i class="fas fa-plus mr-1"></i> Tambah Master Lembur
                </button>
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
            <div class="tsu-stat-grid-lembur">
                {{-- Total Jenis Lembur --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div class="tsu-stat-card__title">Jenis Lembur</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Jenis</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Kategori Lembur Terdaftar di Sistem
                    </div>
                </div>

                {{-- Status Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--active">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="tsu-stat-card__title">Status Aktif</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['active'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Jenis</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Dapat Dipilih pada Pengajuan Lembur
                    </div>
                </div>

                {{-- Total Pengajuan --}}
                <div class="tsu-stat-card tsu-stat-card--pengajuan">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="tsu-stat-card__title">Total Pengajuan</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_pengajuan'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Berkas</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Akumulasi Pengajuan Lembur Pegawai
                    </div>
                </div>

                {{-- Pengajuan Bulan Ini --}}
                <div class="tsu-stat-card tsu-stat-card--bulan-ini">
                    <div class="tsu-stat-card__icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="tsu-stat-card__title">Bulan Ini</div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['lembur_bulan_ini'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Pengajuan</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Periode {{ Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </div>
                </div>
            </div>

            {{-- Card Panduan Keterkaitan Master --}}
            <x-tsu-master-guide
                title="Panduan Keterkaitan Master Lembur"
                description="Master Lembur mengatur kategori penugasan kerja lembur pegawai di luar jam operasional standar (lembur hari kerja, hari libur, atau event universitas)."
                :connections="[
                    ['label' => 'Pengajuan Lembur Mandiri', 'route' => 'users.lembur.index', 'icon' => 'fas fa-business-time'],
                    ['label' => 'Approval Lembur Atasan', 'route' => 'users.approval-lembur.index', 'icon' => 'fas fa-check-double'],
                    ['label' => 'Riwayat Lembur (Admin)', 'route' => 'admin.riwayat-lembur.index', 'icon' => 'fas fa-history'],
                    ['label' => 'Kalkulasi Payroll Lembur', 'route' => 'admin.payroll.index', 'icon' => 'fas fa-calculator']
                ]"
                impact="Kategori lembur menentukan pengelompokan penugasan kerja serta parameter pengali perhitungan upah lembur yang akan diakumulasikan ke dalam rekapitulasi gaji bulanan (Payroll)."
            />

            {{-- Main Table Card --}}
            <div class="tsu-card">
                <div class="tsu-card__header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="tsu-card__title">
                            Daftar Kategori &amp; Master Lembur
                        </h5>
                        <div class="text-muted small mt-1">
                            Master data klasifikasi lembur yang terintegrasi dengan persetujuan atasan dan perhitungan payroll
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="tsu-badge-info-clean">
                            Terkoneksi Modul Payroll &amp; Presensi
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table tsu-table-modern table-hover w-100" id="table-lembur">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="32%">Jenis Lembur</th>
                                    <th width="43%">Keterangan</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
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

    {{-- ================= MODAL CONTAINER (AJAX) ================= --}}
    <div class="modal fade" id="modal-lembur" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content" id="modal-lembur-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
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
            // Inisialisasi DataTables
            var table = $('#table-lembur').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('admin.master-lembur.json') }}",
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari jenis lembur, keterangan...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    zeroRecords: "Tidak ada data jenis lembur yang sesuai",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 s/d 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    processing: '<div class="d-flex align-items-center justify-content-center" style="gap: 0.5rem;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <span>Memuat data master lembur...</span></div>'
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'jenislembur', name: 'jenislembur' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'is_active', name: 'is_active', className: 'text-center' },
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

            // Handler Modal Umum (Create)
            $('body').on('click', '.btn-modal', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                $('#modal-lembur').modal('show');
                $('#modal-lembur-content').html(
                    '<div class="text-center p-5">' +
                    '    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>' +
                    '    <p class="mt-3 text-muted font-weight-bold">Memuat Formulir...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-lembur-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-lembur-content').html(
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

            // Handler Modal Edit
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');

                $('#modal-lembur').modal('show');
                $('#modal-lembur-content').html(
                    '<div class="text-center p-5">' +
                    '    <div class="spinner-border" style="color: var(--tsu-primary, #094b54);" role="status"></div>' +
                    '    <p class="mt-3 text-muted font-weight-bold">Mengambil Data Master Lembur...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-lembur-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-lembur-content').html(
                            '<div class="modal-body p-4 text-center">' +
                            '    <div class="alert alert-danger mb-0">Gagal mengambil data. Error: ' + xhr.status + '</div>' +
                            '</div>' +
                            '<div class="modal-footer p-3" style="background: #f8fafc;">' +
                            '    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Tutup</button>' +
                            '</div>'
                        );
                    }
                });
            });

            // Handler Tombol Delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var name = $(this).closest('tr').find('td:eq(1)').text().trim();

                Swal.fire({
                    title: 'Hapus Master Lembur?',
                    html: "Anda akan menghapus jenis lembur: <b>" + name + "</b>.<br><small class='text-danger font-weight-bold'>Data yang dihapus tidak dapat dipulihkan!</small>",
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
                            title: 'Sedang Menghapus Data...',
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
