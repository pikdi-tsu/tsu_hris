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

        .tsu-stat-card--perlu-sk {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        }

        .tsu-stat-card--hardfile {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
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
        #table-sekretariat-sk thead th {
            background-color: var(--tsu-primary, #094b54) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.84rem !important;
            border: none !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;
            letter-spacing: 0.3px;
        }

        #table-sekretariat-sk tbody td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        #table-sekretariat-sk tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Soft Pill Badges */
        #table-sekretariat-sk .badge {
            border-radius: 20px !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 5px 10px !important;
            letter-spacing: 0.2px;
        }

        #table-sekretariat-sk .badge-warning {
            background: rgba(217, 119, 6, 0.12) !important;
            color: #b45309 !important;
            border: 1px solid rgba(217, 119, 6, 0.25) !important;
        }

        #table-sekretariat-sk .badge-primary {
            background: rgba(2, 132, 199, 0.12) !important;
            color: #0284c7 !important;
            border: 1px solid rgba(2, 132, 199, 0.25) !important;
        }

        #table-sekretariat-sk .badge-success {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.25) !important;
        }

        #table-sekretariat-sk .badge-danger {
            background: rgba(225, 29, 72, 0.12) !important;
            color: #e11d48 !important;
            border: 1px solid rgba(225, 29, 72, 0.25) !important;
        }

        #table-sekretariat-sk .badge-info {
            background: rgba(6, 182, 212, 0.12) !important;
            color: #0891b2 !important;
            border: 1px solid rgba(6, 182, 212, 0.25) !important;
        }

        #table-sekretariat-sk .badge-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Action Buttons */
        #table-sekretariat-sk .btn-group .btn {
            border-radius: 6px !important;
            margin: 0 2px;
            font-weight: 600;
            font-size: 0.76rem;
            padding: 4px 9px;
            transition: all 0.2s ease;
        }

        #table-sekretariat-sk .btn-group .btn:hover {
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

        .sekretariat-filter-wrapper {
            margin-left: auto !important;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap;
        }

        @media (max-width: 768px) {
            .sekretariat-filter-wrapper {
                width: 100% !important;
                margin-top: 10px !important;
                justify-content: flex-start !important;
                flex-wrap: wrap !important;
            }
            .sekretariat-filter-wrapper select {
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
            height: 31px !important;
            padding: 2px 8px !important;
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
        title="Tugas SK & Alur Sekretariat Rektorat"
        subtitle="Kelola permintaan SK Rektorat dari SDM, penerbitan softfile surat resmi, dan tracking hardfile bertandatangan basah"
        :icon="$menuIcon ?? 'fas fa-stamp'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.request-surat.admin-index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-tasks mr-1"></i> Kelola Permohonan Surat SDM
                </a>
                <a href="{{ route('admin.surat-edaran.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-bullhorn mr-1"></i> Pusat Surat Edaran & SK
                </a>
            </div>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Permohonan SK Sekretariat (4 Signature TSU Stat Cards) --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--total">
                        <i class="fas fa-inbox tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Total Diteruskan SDM</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['total']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Berkas</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-share mr-1"></i> Disposisi Masuk SDM
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--perlu-sk">
                        <i class="fas fa-file-signature tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Perlu Proses SK</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['perlu_sk']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Berkas</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-edit mr-1"></i> Menunggu Draf & TTE
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--hardfile">
                        <i class="fas fa-hand-holding tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Hardfile Siap Diambil</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['hardfile_siap']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Fisik</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-map-marker-alt mr-1"></i> Fisik di Sekretariat
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--selesai">
                        <i class="fas fa-check-double tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">SK Selesai & Terbit</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['selesai']) }} <small style="font-size: 1.1rem; opacity: 0.9;">Terbit</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-certificate mr-1"></i> SK Resmi Telah Terbit
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Panduan (Placed Below Stat Cards) --}}
            <x-tsu-master-guide
                title="Panduan Tugas SK & Alur Sekretariat Rektorat"
                description="Halaman kerja Sekretariat Rektorat untuk memproses permohonan dinas yang diteruskan oleh SDM terkait penerbitan Surat Keputusan (SK) Rektor, penomoran resmi surat keluar, pengunggahan dokumen PDF bertandatangan/stempel, serta pelacakan tanda terima berkas fisik asli (hardfile)."
                :connections="[
                    ['label' => 'Kelola Permohonan Surat SDM', 'route' => 'admin.request-surat.admin-index', 'icon' => 'fas fa-tasks'],
                    ['label' => 'Surat Masuk & SIKD', 'route' => 'admin.surat-masuk.index', 'icon' => 'fas fa-inbox'],
                    ['label' => 'Disposisi Masuk Unit', 'route' => 'admin.disposisi-unit.index', 'icon' => 'fas fa-paper-plane'],
                    ['label' => 'Pusat Surat Edaran & SK', 'route' => 'admin.surat-edaran.index', 'icon' => 'fas fa-bullhorn']
                ]"
                impact="Dokumen SK yang telah selesai dan diunggah akan otomatis dapat diunduh pemohon pada portal mandiri pegawai, dan Admin SDM dapat memantau serah terima berkas fisik asli secara transparan."
            />

            {{-- Table Card: Daftar Permintaan SK Rektorat Masuk --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white px-3 py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 12px;">
                    <div>
                        <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px; float: none !important;">
                            <i class="fas fa-stamp" style="color: var(--tsu-primary);"></i> Daftar Permintaan SK Rektorat Masuk
                        </h5>
                        <small class="d-block text-muted mt-1">Permintaan penerbitan SK dinas universitas dan tracking hardfile dokumen fisik</small>
                    </div>
                    <div class="sekretariat-filter-wrapper">
                        <select id="filter-status-sk" class="form-control form-control-sm" style="width: 165px; border-radius: 6px;">
                            <option value="">Semua Status SK</option>
                            <option value="diproses">Sedang Diproses</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        <select id="filter-hardfile-sk" class="form-control form-control-sm" style="width: 185px; border-radius: 6px;">
                            <option value="">Semua Status Hardfile</option>
                            <option value="belum_tersedia">Belum Tersedia</option>
                            <option value="siap_diambil">Siap Diambil</option>
                            <option value="telah_diterima_sdm">Telah Diterima SDM</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-sekretariat" title="Muat Ulang Data" style="border-radius: 6px; height: 31px; width: 34px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-sekretariat-sk" class="table table-hover table-bordered w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="14%">No. Tiket</th>
                                    <th width="20%">Pemohon & Unit</th>
                                    <th width="24%">Permintaan Surat & Catatan SDM</th>
                                    <th width="12%" class="text-center">Status SK</th>
                                    <th width="12%" class="text-center">Hardfile Fisik</th>
                                    <th width="14%" class="text-center">Aksi / Tindakan</th>
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
    <div class="modal fade" id="modal-sekretariat-sk" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-sekretariat-sk-content" style="border-radius: 12px; overflow: hidden; border: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Form Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#table-sekretariat-sk').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.request-surat.sekretariat-json') }}",
            data: function(d) {
                d.status = $('#filter-status-sk').val();
                d.status_hardfile = $('#filter-hardfile-sk').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold' },
            { data: 'tiket_info', name: 'nomor_tiket' },
            { data: 'pemohon', name: 'pegawai.nama_lengkap' },
            { data: 'permohonan', name: 'jenis_surat' },
            { data: 'status_badge', name: 'status', className: 'text-center' },
            { data: 'hardfile_badge', name: 'status_hardfile', className: 'text-center' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('#filter-status-sk, #filter-hardfile-sk').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-refresh-sekretariat').on('click', function() {
        table.ajax.reload(null, false);
    });

    // Buka Modal Proses SK & Upload Softfile
    $(document).on('click', '.btn-modal-sekretariat-selesai', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#modal-sekretariat-sk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><br>Memuat formulir...</div>');
        $('#modal-sekretariat-sk').modal('show');

        $.get(url, function(res) {
            $('#modal-sekretariat-sk-content').html(res);
            // Custom file input label update
            bsCustomFileInput.init();
        }).fail(function() {
            $('#modal-sekretariat-sk-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat formulir.</div>');
        });
    });

    // Buka Modal Detail Tiket
    $(document).on('click', '.btn-detail-surat', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#modal-sekretariat-sk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><br>Memuat data...</div>');
        $('#modal-sekretariat-sk').modal('show');

        $.get(url, function(res) {
            $('#modal-sekretariat-sk-content').html(res);
        }).fail(function() {
            $('#modal-sekretariat-sk-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat detail surat.</div>');
        });
    });

    // Submit Proses SK
    $(document).on('submit', '#formSekretariatSelesai', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = new FormData(this);
        var submitBtn = $('#btnSubmitSekretariat');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#modal-sekretariat-sk').modal('hide');
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
                submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Terbitkan SK & Selesaikan');
                var errorMsg = 'Terjadi kesalahan sistem.';
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

    // Update Hardfile Status
    $(document).on('click', '.btn-update-hardfile, .btn-confirm-hardfile', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var targetStatus = $(this).data('status') || 'telah_diterima_sdm';

        Swal.fire({
            title: 'Konfirmasi Serah Terima Hardfile',
            text: 'Apakah berkas fisik SK asli bertandatangan basah sudah diserahkan / diterima?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Konfirmasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        status_hardfile: targetStatus
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Diperbarui',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#modal-sekretariat-sk').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memperbarui status berkas fisik.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
