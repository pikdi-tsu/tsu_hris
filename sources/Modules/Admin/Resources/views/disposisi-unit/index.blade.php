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

        .tsu-stat-card--menunggu {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        }

        .tsu-stat-card--diproses {
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
        #table-disposisi-unit thead th {
            background-color: var(--tsu-primary, #094b54) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.84rem !important;
            border: none !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;
            letter-spacing: 0.3px;
        }

        #table-disposisi-unit tbody td {
            vertical-align: middle !important;
            padding: 12px 14px !important;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        #table-disposisi-unit tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Soft Pill Badges */
        #table-disposisi-unit .badge {
            border-radius: 20px !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 5px 10px !important;
            letter-spacing: 0.2px;
        }

        #table-disposisi-unit .badge-secondary {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        #table-disposisi-unit .badge-info {
            background: rgba(2, 132, 199, 0.12) !important;
            color: #0284c7 !important;
            border: 1px solid rgba(2, 132, 199, 0.25) !important;
        }

        #table-disposisi-unit .badge-warning {
            background: rgba(217, 119, 6, 0.12) !important;
            color: #b45309 !important;
            border: 1px solid rgba(217, 119, 6, 0.25) !important;
        }

        #table-disposisi-unit .badge-success {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.25) !important;
        }

        #table-disposisi-unit .badge-danger {
            background: rgba(225, 29, 72, 0.12) !important;
            color: #e11d48 !important;
            border: 1px solid rgba(225, 29, 72, 0.25) !important;
        }

        #table-disposisi-unit .badge-primary {
            background: rgba(14, 116, 144, 0.12) !important;
            color: #0e7490 !important;
            border: 1px solid rgba(14, 116, 144, 0.25) !important;
        }

        /* Action Buttons */
        #table-disposisi-unit .btn-group {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        #table-disposisi-unit .btn-group .btn {
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

        #table-disposisi-unit .btn-group .btn-icon-only {
            width: 31px;
            padding: 0 !important;
        }

        #table-disposisi-unit .btn-group .btn:hover {
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

        .disposisi-filter-wrapper {
            margin-left: auto !important;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap;
        }

        @media (max-width: 992px) {
            .disposisi-filter-wrapper {
                width: 100% !important;
                margin-top: 10px !important;
                justify-content: flex-start !important;
                flex-wrap: wrap !important;
            }
            .disposisi-filter-wrapper select,
            .disposisi-filter-wrapper button {
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
        title="Disposisi Masuk Unit Kerja"
        subtitle="Daftar surat dinas yang didisposisikan untuk ditindaklanjuti oleh unit kerja Anda"
        :icon="$menuIcon ?? 'fas fa-tasks'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            {{-- Info Unit Kerja Pegawai (Elevated Modern Style) --}}
            @if($myUnit)
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%); border-left: 4px solid var(--tsu-primary) !important;">
                <div class="card-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center" style="gap: 14px;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(9, 75, 84, 0.1); color: var(--tsu-primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.5px;">Unit Kerja Terdaftar</span>
                            <h6 class="font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                                {{ $myUnit->nama_unit }}
                                @if(!empty($karyawan->nama_lengkap))
                                    <span class="text-muted font-weight-normal ml-1" style="font-size: 0.88rem;">({{ $karyawan->nama_lengkap }})</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="ml-auto text-right pl-3" style="flex-shrink: 0;">
                        <span class="badge badge-pill px-3 py-2 font-weight-bold" style="background: rgba(9, 75, 84, 0.12); color: var(--tsu-primary); font-size: 0.82rem; white-space: nowrap; display: inline-flex; align-items: center;">
                            <i class="fas fa-inbox mr-1"></i> Kotak Disposisi Aktif
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Statistik Disposisi Masuk (4 Signature TSU Stat Cards) --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--total">
                        <i class="fas fa-inbox tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Total Disposisi Masuk</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['total'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Berkas</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-folder mr-1"></i> Seluruh Instruksi Masuk
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--menunggu">
                        <i class="fas fa-clock tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Menunggu Diterima</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['menunggu'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Berkas</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-exclamation-circle mr-1"></i> Perlu Konfirmasi Unit
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--diproses">
                        <i class="fas fa-cogs tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Sedang Diproses</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['diproses'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Berkas</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-tools mr-1"></i> Tindak Lanjut Berjalan
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12 mb-3">
                    <div class="tsu-stat-card tsu-stat-card--selesai">
                        <i class="fas fa-check-circle tsu-stat-card__watermark"></i>
                        <span class="tsu-stat-card__label">Disposisi Selesai</span>
                        <div class="tsu-stat-card__value mt-1 mb-1">
                            {{ number_format($counts['selesai'] ?? 0) }} <small style="font-size: 1.1rem; opacity: 0.9;">Berkas</small>
                        </div>
                        <div>
                            <span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem;">
                                <i class="fas fa-check-double mr-1"></i> Instruksi Tuntas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card: Daftar Surat Disposisi Masuk --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white px-3 py-3 d-flex flex-wrap justify-content-between align-items-center" style="border-bottom: 1px solid var(--tsu-border); gap: 12px;">
                    <div>
                        <h5 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px; float: none !important;">
                            <i class="fas fa-clipboard-list" style="color: var(--tsu-primary);"></i> Daftar Surat Disposisi Masuk
                        </h5>
                        <small class="d-block text-muted mt-1">Daftar instruksi dan berkas surat dinas yang ditugaskan ke unit kerja</small>
                    </div>
                    <div class="disposisi-filter-wrapper">
                        @if(auth()->user()->isAdmin() || auth()->user()->hasRole(['super admin hris', 'admin hris testing', 'Super Admin', 'Admin SDM']))
                        <select id="filter-unit" class="form-control form-control-sm" style="width: 190px; border-radius: 6px;">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                        @endif
                        <select id="filter-status-disp" class="form-control form-control-sm" style="width: 165px; border-radius: 6px;">
                            <option value="">Semua Status</option>
                            <option value="menunggu">Menunggu Diterima</option>
                            <option value="diterima">Diterima Unit</option>
                            <option value="diproses">Sedang Diproses</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-disposisi" title="Muat Ulang Data" style="border-radius: 6px; height: 31px; width: 34px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="table-disposisi-unit" class="table table-hover table-bordered w-100 mb-0">
                            <thead>
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="28%">Surat Masuk & Pengirim</th>
                                    <th width="18%">Unit & PIC</th>
                                    <th width="24%">Instruksi & Batas Waktu</th>
                                    <th width="12%" class="text-center">Status</th>
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
    <div class="modal fade" id="modal-disposisi-unit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-disposisi-unit-content" style="border-radius: 12px; overflow: hidden; border: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                {{-- Form Loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#table-disposisi-unit').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('admin.disposisi-unit.json') }}",
            data: function(d) {
                d.filter_unit = $('#filter-unit').length ? $('#filter-unit').val() : '';
                d.filter_status = $('#filter-status-disp').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold' },
            { data: 'surat_info', name: 'suratMasuk.perihal' },
            { data: 'unit_info', name: 'unitTujuan.nama_unit' },
            { data: 'instruksi_info', name: 'instruksi' },
            { data: 'status_badge', name: 'status_tindak_lanjut', className: 'text-center' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    $('#filter-unit, #filter-status-disp').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-refresh-disposisi').on('click', function() {
        table.ajax.reload(null, false);
    });

    // Konfirmasi Terima Disposisi
    $(document).on('click', '.btn-terima-disp', function(e) {
        e.preventDefault();
        var url = $(this).data('url');

        Swal.fire({
            title: 'Konfirmasi Terima Disposisi',
            text: 'Apakah unit Anda menyatakan surat ini sudah diterima dan siap diproses?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#094b54',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Terima Surat',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Diterima!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memproses penerimaan disposisi.', 'error');
                    }
                });
            }
        });
    });

    // Buka Modal Tindak Lanjut
    $(document).on('click', '.btn-tindak-lanjut', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#modal-disposisi-unit-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x" style="color: var(--tsu-primary);"></i><br><span class="text-muted mt-2 d-inline-block">Memuat formulir tindak lanjut...</span></div>');
        $('#modal-disposisi-unit').modal('show');

        $.get(url, function(res) {
            $('#modal-disposisi-unit-content').html(res);
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }).fail(function() {
            $('#modal-disposisi-unit-content').html('<div class="p-4 text-center text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat formulir.</div>');
        });
    });

    // Submit Tindak Lanjut
    $(document).on('submit', '#formTindakLanjut', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = new FormData(this);
        var submitBtn = $('#btnSubmitTindakLanjut');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#modal-disposisi-unit').modal('hide');
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
                submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Simpan Tindak Lanjut');
                var errorMsg = 'Gagal menyimpan tindak lanjut.';
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
});
</script>
@endsection
