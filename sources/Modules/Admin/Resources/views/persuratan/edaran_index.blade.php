@extends('system::template.admin.header')

@section('css')
    <style>
        .tsu-stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 14px;
            padding: 22px 20px;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transition: all 0.25s ease-in-out;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .tsu-stat-card__watermark {
            position: absolute;
            right: -8px;
            bottom: -8px;
            font-size: 4.8rem;
            opacity: 0.18;
            pointer-events: none;
            color: #ffffff;
        }

        .tsu-stat-card--total {
            background: linear-gradient(135deg, #0d9488 0%, #094b54 100%) !important;
        }

        .tsu-stat-card--libur {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        }

        .tsu-stat-card--sk {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        }

        .tsu-stat-card--kebijakan {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        }

        .tsu-stat-card__label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 4px;
        }

        .tsu-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
        }

        /* Table styling */
        #table-edaran thead th {
            background-color: var(--tsu-primary, #094b54) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.84rem !important;
            border: none !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;
            letter-spacing: 0.3px;
        }

        #table-edaran tbody td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        #table-edaran tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Soft Pill Badges */
        #table-edaran .badge {
            border-radius: 20px !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 5px 10px !important;
            letter-spacing: 0.2px;
        }

        #table-edaran .badge-info {
            background: rgba(2, 132, 199, 0.12) !important;
            color: #0284c7 !important;
            border: 1px solid rgba(2, 132, 199, 0.25) !important;
        }

        #table-edaran .badge-primary {
            background: rgba(14, 116, 144, 0.12) !important;
            color: #0e7490 !important;
            border: 1px solid rgba(14, 116, 144, 0.25) !important;
        }

        #table-edaran .badge-danger {
            background: rgba(225, 29, 72, 0.12) !important;
            color: #e11d48 !important;
            border: 1px solid rgba(225, 29, 72, 0.25) !important;
        }

        #table-edaran .badge-success {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.25) !important;
        }

        #table-edaran .badge-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Action Buttons */
        #table-edaran .btn-group {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        #table-edaran .btn-group .btn {
            border-radius: 6px !important;
            font-weight: 600;
            font-size: 0.76rem;
            height: 31px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 9px;
            transition: all 0.2s ease;
        }

        #table-edaran .btn-group .btn-icon-only {
            width: 31px;
            padding: 0 !important;
        }

        #table-edaran .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }

        .card-title {
            float: none !important;
        }

        .card-header::after,
        .card-header::before {
            display: none !important;
        }

        .edaran-filter-wrapper {
            margin-left: auto !important;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap;
        }

        @media (max-width: 768px) {
            .edaran-filter-wrapper {
                width: 100% !important;
                margin-top: 10px !important;
                justify-content: flex-start !important;
                flex-wrap: wrap !important;
            }
            .edaran-filter-wrapper select {
                width: 100% !important;
            }
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 14px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            height: 32px !important;
            min-width: 80px !important;
            padding: 2px 28px 2px 10px !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            height: 31px !important;
            padding: 4px 10px !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 14px;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Pusat Surat Edaran & SK Rektorat"
        subtitle="Repositori resmi penerbitan Surat Edaran, Surat Keputusan, dan kebijakan SDM Universitas"
        :icon="$menuIcon ?? 'fas fa-newspaper'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @if(auth()->user()->can('admin:persuratan-sdm:create') || auth()->user()->hasRole(['Developer', 'Super Admin', 'Admin SDM', 'SDM']))
            <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold btn-modal-tambah"
                data-url="{{ route('admin.surat-edaran.create') }}" title="Terbitkan Dokumen" style="border-radius: 8px; height: 36px; display: inline-flex; align-items: center; padding: 0 14px;">
                <i class="fas fa-plus mr-1"></i> Terbitkan Dokumen Baru
            </button>
            @endif
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Surat Edaran (4 Signature TSU Stat Cards) --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--total">
                        <i class="fas fa-newspaper tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Total Dokumen Aktif</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['total'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Dokumen</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-archive mr-1"></i> Arsip Resmi Berlaku
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--libur">
                        <i class="fas fa-calendar-check tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Edaran Libur & Cuti</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['edaran_libur'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Edaran</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-calendar-alt mr-1"></i> Sinkron Kalender Kerja
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--sk">
                        <i class="fas fa-stamp tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">SK Rektorat / Yayasan</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['sk_rektor'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Surat SK</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-file-contract mr-1"></i> Ketetapan Resmi Pimpinan
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--kebijakan">
                        <i class="fas fa-book-open tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Kebijakan & Jam Kerja</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format(($counts['kebijakan_sdm'] ?? 0) + ($counts['edaran_jam_kerja'] ?? 0)) }} <small style="font-size: 1.1rem; opacity: 0.9;">Pedoman</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-clock mr-1"></i> Pedoman Operasional SDM
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card: Daftar Surat Edaran & SK Resmi --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white px-3 py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 12px;">
                    <div>
                        <h5 class="card-title font-weight-bold mb-0 text-dark" style="font-size: 1rem; display: flex; align-items: center; gap: 8px; float: none !important;">
                            <i class="fas fa-archive" style="color: var(--tsu-primary);"></i> Daftar Surat Edaran &amp; SK Resmi
                        </h5>
                        <small class="d-block text-muted mt-1">Publikasi ketetapan dinas, nomor SK rektorat, dan ketentuan operasional SDM</small>
                    </div>
                    <div class="edaran-filter-wrapper">
                        <label class="mr-1 mb-0 small font-weight-bold text-muted text-nowrap"><i class="fas fa-filter mr-1" style="color: var(--tsu-primary);"></i> Kategori:</label>
                        <select id="filter-kategori-edaran" class="form-control form-control-sm" style="width: 210px; border-radius: 6px;">
                            <option value="">Semua Kategori</option>
                            <option value="edaran_libur">Edaran Libur &amp; Cuti</option>
                            <option value="edaran_jam_kerja">Edaran Jam Kerja</option>
                            <option value="sk_rektor">SK Rektorat / Yayasan</option>
                            <option value="kebijakan_sdm">Pedoman &amp; Kebijakan SDM</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-edaran" title="Muat Ulang Data" style="border-radius: 6px; height: 31px; width: 34px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-edaran" class="table table-hover table-bordered w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="17%">Nomor Surat / SK</th>
                                    <th width="24%">Perihal Dokumen</th>
                                    <th width="13%">Kategori</th>
                                    <th width="10%">Tgl Terbit</th>
                                    <th width="11%" class="text-center">Kalender</th>
                                    <th width="9%">Sasaran</th>
                                    <th width="4%" class="text-center">Unduh</th>
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

    {{-- MODAL FORM CONTAINER --}}
    <div class="modal fade" id="modal-edaran" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-edaran-content" style="border-radius: 12px; overflow: hidden; border: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Form loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#table-edaran').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.surat-edaran.datatable') }}",
            data: function(d) {
                d.kategori = $('#filter-kategori-edaran').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold' },
            { data: 'nomor_surat', name: 'nomor_surat', className: 'font-weight-bold text-dark' },
            { data: 'perihal', name: 'perihal' },
            { data: 'kategori_badge', name: 'kategori' },
            { data: 'tgl_format', name: 'tanggal_surat' },
            { data: 'kalender_badge', name: 'tampilkan_di_kalender', className: 'text-center' },
            { data: 'target_badge', name: 'target_audience' },
            { data: 'unduhan', name: 'download_count', className: 'text-center' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[4, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('#filter-kategori-edaran').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-refresh-edaran').on('click', function() {
        table.ajax.reload(null, false);
    });

    // Open Modal Tambah
    $('body').on('click', '.btn-modal-tambah', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var modal = $('#modal-edaran');
        var container = $('#modal-edaran-content');

        container.html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--tsu-primary);"></i><p class="mt-2 text-muted">Memuat formulir...</p></div>');
        modal.modal('show');

        $.get(url, function(res) {
            container.html(res);
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }).fail(function() {
            container.html('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat form dokumen.</div>');
        });
    });

    // Open Modal Edit
    $('body').on('click', '.btn-modal-edit', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var modal = $('#modal-edaran');
        var container = $('#modal-edaran-content');

        container.html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--tsu-primary);"></i><p class="mt-2 text-muted">Memuat data dokumen...</p></div>');
        modal.modal('show');

        $.get(url, function(res) {
            container.html(res);
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }).fail(function() {
            container.html('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data dokumen.</div>');
        });
    });

    // Submit Form Tambah/Edit via AJAX
    $('body').on('submit', '#form-edaran', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var originalText = btn.html();

        var formData = new FormData(this);

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    $('#modal-edaran').modal('hide');
                    table.ajax.reload(null, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                var msg = 'Terjadi kesalahan sistem.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: msg
                    });
                } else {
                    alert(msg);
                }
            }
        });
    });

    // Hapus Dokumen
    $('body').on('click', '.btn-delete-edaran', function(e) {
        e.preventDefault();
        var url = $(this).data('url');

        var doDelete = function() {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    table.ajax.reload(null, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr) {
                    var msg = 'Gagal menghapus dokumen.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Dokumen Resmi?',
                text: 'Dokumen ini akan dihapus dari arsip pusat persuratan universitas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus dokumen ini?')) {
                doDelete();
            }
        }
    });
});
</script>
@endsection
