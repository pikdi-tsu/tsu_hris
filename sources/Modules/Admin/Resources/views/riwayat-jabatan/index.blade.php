@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <style>
        /* === TSU Stat Cards Grid === */
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
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
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
            padding: 0.4rem 0.95rem;
            transition: all 0.2s ease;
        }
        .tsu-btn-reload:hover {
            background: var(--tsu-primary, #094b54);
            color: #ffffff;
            border-color: var(--tsu-primary, #094b54);
        }
        .tsu-btn-export {
            background: linear-gradient(135deg, #166534 0%, #22c55e 100%);
            color: #ffffff !important;
            border: none;
            border-radius: var(--tsu-radius, 8px);
            font-weight: 600;
            padding: 0.4rem 1.1rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(22, 101, 52, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .tsu-btn-export:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(22, 101, 52, 0.3);
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
        :title="$title ?? 'Riwayat Jabatan'"
        :icon="$menuIcon ?? 'fas fa-history'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.riwayat-jabatan.export') }}" id="btn-export" target="_blank" class="btn btn-sm tsu-btn-export" title="Export Excel">
                <i class="fas fa-file-excel mr-1"></i> Export Data
            </a>
            <button type="button" class="btn btn-sm tsu-btn-reload ml-2" id="btn-refresh-table" title="Segarkan Data Tabel">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Section --}}
    <section class="content">
        <div class="container-fluid">

            {{-- Stat Cards: Summary Metrics --}}
            <div class="tsu-stat-grid-jabatan">
                <!-- Card 1: Total Riwayat -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Total Riwayat</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalRiwayat }} <span class="tsu-stat-card__unit">Catatan</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Seluruh riwayat penugasan</div>
                    </div>
                </div>

                <!-- Card 2: Jabatan Struktural -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Jabatan Struktural</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalStruktural }} <span class="tsu-stat-card__unit">Riwayat</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Pimpinan unit &amp; struktural</div>
                    </div>
                </div>

                <!-- Card 3: Jabatan Fungsional -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #166534 0%, #22c55e 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Jabatan Fungsional</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalFungsional }} <span class="tsu-stat-card__unit">Riwayat</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Jenjang fungsional dosen &amp; tendik</div>
                    </div>
                </div>

                <!-- Card 4: Pegawai Tercatat -->
                <div class="tsu-stat-card text-white" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%);">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Pegawai Tercatat</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-id-badge"></i>
                        </div>
                    </div>
                    <div>
                        <div class="tsu-stat-card__value">
                            {{ $totalPegawai }} <span class="tsu-stat-card__unit">Orang</span>
                        </div>
                        <div class="tsu-stat-card__subtext">Pegawai yang memiliki riwayat</div>
                    </div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="card card-primary card-outline tsu-card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="background:transparent;border-bottom:1px solid rgba(0,0,0,.06);padding:1rem 1.25rem;">
                    <div>
                        <h5 class="m-0 font-weight-bold" style="color:var(--tsu-primary-dark, #094b54);font-size:.95rem;display:flex;align-items:center;gap:.5rem;">
                            <i class="fas fa-history" style="color:var(--tsu-primary, #094b54);"></i>
                            Daftar Riwayat Jabatan Pegawai
                        </h5>
                        <small class="text-muted d-block mt-1" style="font-size: 0.76rem;">
                            <i class="fas fa-info-circle mr-1"></i>Data riwayat penugasan struktural dan jenjang fungsional pegawai institusi
                        </small>
                    </div>

                    {{-- Quick Filter Select2 --}}
                    <div class="d-flex align-items-center mt-2 mt-sm-0" style="min-width: 260px;">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background: #f8fafc; border-color: #ced4da; color: var(--tsu-primary, #094b54);">
                                    <i class="fas fa-filter"></i>
                                </span>
                            </div>
                            <select id="filter-karyawan" class="form-control select2" style="width: auto; flex: 1;">
                                <option value="">-- Semua Pegawai --</option>
                                @foreach($karyawans as $karyawan)
                                    <option value="{{ $karyawan->id }}">{{ $karyawan->nama }} ({{ $karyawan->nik ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-riwayat" class="table table-bordered table-striped tsu-table-modern w-100">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="22%">Pegawai</th>
                                    <th width="12%" class="text-center">Tipe</th>
                                    <th width="25%">Nama Jabatan</th>
                                    <th width="18%">Masa Menjabat</th>
                                    <th width="10%">Catatan</th>
                                    <th width="8%" class="text-center">Aksi</th>
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
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content shadow-lg border-0" id="modal-edit-content" style="border-radius: var(--tsu-radius-lg, 12px); overflow: hidden;">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted" style="font-size:0.85rem;">Memuat Form Edit...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('#filter-karyawan').select2({
                theme: 'bootstrap4',
                placeholder: "-- Filter Pegawai --",
                allowClear: true
            });

            // Inisialisasi DataTables
            let table = $('#table-riwayat').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.riwayat-jabatan.json') }}",
                    data: function (d) {
                        d.karyawan_id = $('#filter-karyawan').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle'},
                    {data: 'pegawai', name: 'dataDosenTendik.nama', className: 'align-middle'},
                    {data: 'tipe_jabatan', name: 'tipe_jabatan', className: 'text-center align-middle'},
                    {data: 'jabatan', name: 'jabatan', orderable: false, searchable: false, className: 'align-middle'},
                    {data: 'masa_jabatan', name: 'masa_jabatan', orderable: false, searchable: false, className: 'align-middle'},
                    {data: 'keterangan', name: 'keterangan', defaultContent: '-', className: 'align-middle'},
                    {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle'},
                ],
                order: [[4, 'desc']],
                language: {
                    emptyTable: "Belum ada data riwayat jabatan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data riwayat",
                    infoEmpty: "Data riwayat kosong",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    loadingRecords: "Memuat...",
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memproses...',
                    search: "Cari Pegawai / Jabatan:",
                    zeroRecords: "Tidak ada data riwayat yang cocok"
                }
            });

            // Refresh table
            $('#btn-refresh-table').on('click', function() {
                table.ajax.reload(null, false);
            });

            // Reload table upon filter change
            $('#filter-karyawan').on('change', function() {
                table.ajax.reload();
                
                // Update href for export button to include parameter
                let baseUrl = "{{ route('admin.riwayat-jabatan.export') }}";
                let val = $(this).val();
                if (val) {
                    $('#btn-export').attr('href', baseUrl + '?karyawan_id=' + val);
                } else {
                    $('#btn-export').attr('href', baseUrl);
                }
            });

            // Prevent export when datatable is empty
            $('#btn-export').on('click', function(e) {
                if (table.page.info().recordsTotal === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Kosong',
                        text: 'Tidak ada data riwayat jabatan untuk diekspor!',
                        confirmButtonColor: '#094b54'
                    });
                }
            });

            // Handle Edit Modal
            $(document).on('click', '.btn-edit', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                $('#modal-edit-content').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted" style="font-size:0.85rem;">Memuat Form Edit...</p>
                    </div>
                `);
                $('#modal-edit').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                        
                        // Override tombol "Kembali ke Timeline" karena di sini ga ada timeline
                        $('#modal-edit-content .btn-back-to-riwayat').remove();
                        
                        // Inject ulang handler submit khusus halaman ini
                        $('#modal-edit-content').find('#form-update-riwayat').off('submit').on('submit', function(e) {
                            e.preventDefault();
                            let form = $(this);
                            let btn = $('#btn-submit-update-riwayat');
                            let originalText = btn.html();
                            
                            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);
                            
                            $.ajax({
                                url: form.attr('action'),
                                type: 'POST',
                                data: form.serialize(),
                                success: function(res) {
                                    if(res.status === 'success') {
                                        $('#modal-edit').modal('hide');
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Tersimpan!',
                                            text: res.message || 'Data riwayat jabatan berhasil diperbarui.',
                                            confirmButtonColor: '#094b54'
                                        });
                                        table.ajax.reload(null, false);
                                    }
                                },
                                error: function(xhr) {
                                    btn.html(originalText).prop('disabled', false);
                                    if(xhr.status === 422) {
                                        let errors = xhr.responseJSON.errors;
                                        let msg = '';
                                        for(let k in errors) msg += errors[k][0] + '<br>';
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Validasi Gagal',
                                            html: msg,
                                            confirmButtonColor: '#094b54'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: xhr.responseJSON?.message || 'Gagal memperbarui data.',
                                            confirmButtonColor: '#094b54'
                                        });
                                    }
                                }
                            });
                        });
                    },
                    error: function() {
                        $('#modal-edit-content').html(`
                            <div class="text-center text-danger p-5">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0">Gagal memuat formulir edit.</p>
                            </div>
                        `);
                    }
                });
            });

            // Handle Delete
            $(document).on('click', '.btn-delete-riwayat', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Hapus Riwayat Jabatan?',
                    text: "Data riwayat ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message || 'Data riwayat jabatan berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal menghapus riwayat jabatan.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
