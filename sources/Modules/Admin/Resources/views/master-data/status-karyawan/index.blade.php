@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* === TSU Stat Cards Grid (4 Columns) === */
        .tsu-stat-grid-status {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-status {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-status {
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
        .tsu-stat-card--aktif {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }
        .tsu-stat-card--nonaktif {
            background: linear-gradient(135deg, #475569 0%, #64748b 100%);
            color: #ffffff;
        }
        .tsu-stat-card--pegawai {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
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
        :title="$title ?? 'Master Data Status Karyawan'"
        :icon="$menuIcon ?? 'fas fa-id-badge'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @can('admin:master-status-karyawan:create')
                <button type="button" class="btn btn-sm tsu-btn-primary-action btn-modal mr-2" data-url="{{ route('admin.master-status-karyawan.create') }}" title="Tambah Status Karyawan Baru">
                    <i class="fas fa-plus mr-1"></i> Tambah Status
                </button>
            @endcan

            <button type="button" class="btn btn-sm tsu-btn-reload" id="btn-reload" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards Grid --}}
            <div class="tsu-stat-grid-status">
                {{-- Total Status --}}
                <div class="tsu-stat-card tsu-stat-card--total">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Status</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-id-badge"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Status</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Kategori Hubungan Kerja di TSU
                    </div>
                </div>

                {{-- Status Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--aktif">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Status Aktif</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['aktif'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Status</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Dapat Digunakan pada Profil Pegawai
                    </div>
                </div>

                {{-- Status Non-Aktif --}}
                <div class="tsu-stat-card tsu-stat-card--nonaktif">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Non-Aktif</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['non_aktif'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Status</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Kategori Hubungan Kerja Diarsipkan
                    </div>
                </div>

                {{-- Pegawai Terdistribusi --}}
                <div class="tsu-stat-card tsu-stat-card--pegawai">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Pegawai Terdaftar</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_pegawai'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Pegawai</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Total SDM dengan Status Terikat
                    </div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="tsu-card">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-status" class="table tsu-table-modern table-hover w-100">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">NO</th>
                                    <th>NAMA STATUS</th>
                                    <th>KETERANGAN</th>
                                    <th width="12%" class="text-center">STATUS</th>
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
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-status" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-status-content" style="border-radius: 12px; overflow: hidden;">
                {{-- Loaded via AJAX --}}
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
            var table = $('#table-status').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.master-status-karyawan.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nama_status', name: 'nama_status' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data status karyawan yang tersedia",
                    zeroRecords: "Tidak ditemukan data yang sesuai"
                }
            });

            // Tombol Refresh Data
            $('#btn-reload').on('click', function() {
                var $btn = $(this);
                $btn.find('i').addClass('fa-spin');
                table.ajax.reload(function() {
                    setTimeout(function() {
                        $btn.find('i').removeClass('fa-spin');
                    }, 400);
                }, false);
            });

            // Open Modal (Create / Edit)
            $(document).on('click', '.btn-modal, .btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!url) url = $(this).attr('href');

                $('#modal-status').modal('show');
                $('#modal-status-content').html(
                    '<div class="text-center p-5">' +
                        '<div class="spinner-border text-primary" style="color: var(--tsu-primary, #094b54) !important;" role="status"></div>' +
                        '<p class="text-muted mt-2 mb-0" style="font-size: 0.88rem;">Memuat form...</p>' +
                    '</div>'
                );

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-status-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-status-content').html(
                            '<div class="text-center p-4">' +
                                '<i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>' +
                                '<p class="text-danger font-weight-bold mb-0">Gagal memuat formulir.</p>' +
                                '<small class="text-muted">Error ' + xhr.status + ': ' + (xhr.statusText || 'Terjadi kesalahan sistem') + '</small>' +
                            '</div>'
                        );
                    }
                });
            });

            // Submit Form Modal via AJAX
            $(document).on('submit', '#modal-status form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btnSubmit = $form.find('button[type="submit"]');
                var originalHtml = $btnSubmit.html();

                $btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: $form.attr('action'),
                    type: $form.attr('method') || 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        $('#modal-status').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Status karyawan berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        $btnSubmit.prop('disabled', false).html(originalHtml);
                        var errMsg = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            errMsg = Object.values(errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            html: errMsg
                        });
                    }
                });
            });

            // Handle Delete / Toggle with SweetAlert2 Confirmation
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var $form = $(this).closest('form');
                var actionUrl = $form.attr('action');
                var rowName = $(this).closest('tr').find('td:eq(1)').find('.font-weight-bold').text().trim() || $(this).closest('tr').find('td:eq(1)').text().trim();

                Swal.fire({
                    title: 'Kelola Status Karyawan?',
                    html: "Tindakan ini akan memperbarui status aktif/non-aktif atau menghapus kategori: <br><strong>" + rowName + "</strong>.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#094b54',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Lanjutkan!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu beberapa saat.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: actionUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message || 'Status karyawan berhasil diperbarui.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                var msg = 'Gagal memproses data.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: msg
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
