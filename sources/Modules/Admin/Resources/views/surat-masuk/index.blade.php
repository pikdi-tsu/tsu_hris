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

        .tsu-stat-card--terdaftar {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        }

        .tsu-stat-card--didisposisi {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        }

        .tsu-stat-card--selesai {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
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
        #table-surat-masuk thead th {
            background-color: var(--tsu-primary, #094b54) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.84rem !important;
            border: none !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;
            letter-spacing: 0.3px;
        }

        #table-surat-masuk tbody td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        #table-surat-masuk tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Soft Pill Badges */
        #table-surat-masuk .badge {
            border-radius: 20px !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 5px 10px !important;
            letter-spacing: 0.2px;
        }

        #table-surat-masuk .badge-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        #table-surat-masuk .badge-info {
            background: rgba(2, 132, 199, 0.12) !important;
            color: #0284c7 !important;
            border: 1px solid rgba(2, 132, 199, 0.25) !important;
        }

        #table-surat-masuk .badge-warning {
            background: rgba(217, 119, 6, 0.12) !important;
            color: #b45309 !important;
            border: 1px solid rgba(217, 119, 6, 0.25) !important;
        }

        #table-surat-masuk .badge-success {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.25) !important;
        }

        #table-surat-masuk .badge-danger {
            background: rgba(225, 29, 72, 0.12) !important;
            color: #e11d48 !important;
            border: 1px solid rgba(225, 29, 72, 0.25) !important;
        }

        #table-surat-masuk .badge-primary {
            background: rgba(14, 116, 144, 0.12) !important;
            color: #0e7490 !important;
            border: 1px solid rgba(14, 116, 144, 0.25) !important;
        }

        /* Action Buttons */
        #table-surat-masuk .btn-group .btn {
            border-radius: 6px !important;
            margin: 0 2px;
            font-weight: 600;
            font-size: 0.76rem;
            padding: 4px 9px;
            transition: all 0.2s ease;
        }

        #table-surat-masuk .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
        }

        .card-title {
            float: none !important;
        }

        .card-header::after,
        .card-header::before {
            display: none !important;
        }

        .surat-masuk-filter-wrapper {
            margin-left: auto !important;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap;
        }

        @media (max-width: 992px) {
            .surat-masuk-filter-wrapper {
                width: 100% !important;
                margin-top: 10px !important;
                justify-content: flex-start !important;
                flex-wrap: wrap !important;
            }
            .surat-masuk-filter-wrapper select,
            .surat-masuk-filter-wrapper button {
                flex-grow: 1;
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
        title="Registrasi Surat Masuk & SIKD"
        subtitle="Pusat pencatatan surat masuk dari pihak eksternal, pengarsipan berkas scan, dan lembar disposisi unit kerja"
        :icon="$menuIcon ?? 'fas fa-inbox'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Surat Masuk (4 Signature TSU Stat Cards) --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--total">
                        <i class="fas fa-inbox tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Total Surat Masuk</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['total'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Surat</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-archive mr-1"></i> Arsip Terpusat SIKD
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--terdaftar">
                        <i class="fas fa-clock tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Terdaftar / Antrean</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['terdaftar'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Surat</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-hourglass-half mr-1"></i> Belum Didisposisikan
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--didisposisi">
                        <i class="fas fa-paper-plane tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Dalam Disposisi Unit</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['didisposisi'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Surat</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-cogs mr-1"></i> Tindak Lanjut Unit Kerja
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--selesai">
                        <i class="fas fa-check-circle tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Disposisi Selesai</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['selesai'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Surat</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-check-double mr-1"></i> Berkas Tuntas / Arsip
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card: Daftar Surat Masuk Eksternal --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white px-3 py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 12px;">
                    <div>
                        <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px; float: none !important;">
                            <i class="fas fa-envelope-open-text" style="color: var(--tsu-primary);"></i> Daftar Surat Masuk Eksternal
                        </h5>
                        <small class="d-block text-muted mt-1">Registrasi berkas fisik, tracking nomor agenda SIKD, dan ringkasan disposisi unit kerja</small>
                    </div>
                    <div class="surat-masuk-filter-wrapper">
                        <select id="filter-sifat" class="form-control form-control-sm" style="width: 135px; border-radius: 6px;">
                            <option value="">Semua Sifat</option>
                            <option value="biasa">Biasa</option>
                            <option value="penting">Penting</option>
                            <option value="segera">Segera</option>
                            <option value="rahasia">Rahasia</option>
                        </select>
                        <select id="filter-status" class="form-control form-control-sm" style="width: 145px; border-radius: 6px;">
                            <option value="">Semua Status</option>
                            <option value="terdaftar">Terdaftar</option>
                            <option value="didisposisi">Didisposisi</option>
                            <option value="proses_unit">Proses Unit</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-surat-masuk" title="Muat Ulang Data" style="border-radius: 6px; height: 31px; width: 34px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold" id="btn-create-surat-masuk" style="border-radius: 6px; height: 31px; display: inline-flex; align-items: center; justify-content: center; padding: 0 12px; white-space: nowrap;">
                            <i class="fas fa-plus mr-1"></i> Registrasi Surat Masuk
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-surat-masuk" class="table table-hover table-bordered w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="12%">No. Agenda</th>
                                    <th width="13%">No. Surat Asal</th>
                                    <th width="16%">Pengirim / Instansi</th>
                                    <th width="20%">Perihal</th>
                                    <th width="8%" class="text-center">Sifat</th>
                                    <th width="15%">Disposisi Unit</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="12%" class="text-center">Aksi</th>
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
    <div class="modal fade" id="modal-surat-masuk" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-surat-masuk-content" style="border-radius: 12px; overflow: hidden; border: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Form Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#table-surat-masuk').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.surat-masuk.json') }}",
            data: function(d) {
                d.sifat_surat = $('#filter-sifat').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold' },
            { data: 'no_agenda', name: 'no_agenda', className: 'font-weight-bold text-dark' },
            { data: 'no_surat_asal', name: 'no_surat_asal' },
            { data: 'pengirim_instansi', name: 'pengirim_instansi', className: 'font-weight-600 text-dark' },
            { data: 'perihal', name: 'perihal' },
            { data: 'sifat_badge', name: 'sifat_surat', className: 'text-center' },
            { data: 'ringkasan_disposisi', name: 'ringkasan_disposisi', orderable: false },
            { data: 'status_badge', name: 'status', className: 'text-center' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('#filter-sifat, #filter-status').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-refresh-surat-masuk').on('click', function() {
        table.ajax.reload(null, false);
    });

    // Buka Modal Registrasi Surat Masuk
    $('#btn-create-surat-masuk').on('click', function(e) {
        e.preventDefault();
        $('#modal-surat-masuk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--tsu-primary);"></i><br><span class="text-muted mt-2 d-inline-block">Memuat form registrasi...</span></div>');
        $('#modal-surat-masuk').modal('show');

        $.get("{{ route('admin.surat-masuk.create-modal') }}", function(res) {
            $('#modal-surat-masuk-content').html(res);
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }).fail(function() {
            $('#modal-surat-masuk-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat formulir.</div>');
        });
    });

    // Submit Registrasi Surat Masuk
    $(document).on('submit', '#formSuratMasuk', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = new FormData(this);
        var submitBtn = $('#btnSubmitSuratMasuk');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#modal-surat-masuk').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2500,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Registrasi Surat');
                var errorMsg = 'Gagal menyimpan surat masuk.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMsg
                });
            }
        });
    });

    // Buka Modal Disposisi
    $(document).on('click', '.btn-disposisi-sm', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#modal-surat-masuk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--tsu-primary);"></i><br><span class="text-muted mt-2 d-inline-block">Memuat lembar disposisi...</span></div>');
        $('#modal-surat-masuk').modal('show');

        $.get(url, function(res) {
            $('#modal-surat-masuk-content').html(res);
            $('.select2').select2({ dropdownParent: $('#modal-surat-masuk') });
        }).fail(function() {
            $('#modal-surat-masuk-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat lembar disposisi.</div>');
        });
    });

    // Submit Disposisi
    $(document).on('submit', '#formDisposisiSurat', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var submitBtn = $('#btnSubmitDisposisi');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                $('#modal-surat-masuk').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Disposisi Terkirim!',
                    text: res.message,
                    timer: 2500,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Disposisi ke Unit');
                var errorMsg = 'Gagal mengirim disposisi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMsg
                });
            }
        });
    });

    // Buka Modal Detail Surat Masuk
    $(document).on('click', '.btn-detail-sm', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#modal-surat-masuk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><br><span class="text-muted mt-2 d-inline-block">Memuat detail surat...</span></div>');
        $('#modal-surat-masuk').modal('show');

        $.get(url, function(res) {
            $('#modal-surat-masuk-content').html(res);
        }).fail(function() {
            $('#modal-surat-masuk-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat detail.</div>');
        });
    });
});
</script>
@endsection
