@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Tugas SK & Alur Sekretariat Rektorat"
        subtitle="Kelola permintaan SK Rektorat dari SDM, penerbitan softfile surat resmi, dan tracking hardfile bertandatangan basah"
        :icon="$menuIcon ?? 'fas fa-stamp'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            {{-- Statistik Permohonan SK Sekretariat --}}
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #17a2b8;">
                        <span class="info-box-icon bg-info text-white"><i class="fas fa-inbox"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Diteruskan SDM</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['total']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #ffc107;">
                        <span class="info-box-icon bg-warning text-white"><i class="fas fa-file-signature"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Perlu Proses SK</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['perlu_sk']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #fd7e14;">
                        <span class="info-box-icon bg-orange text-white"><i class="fas fa-hand-holding"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Hardfile Siap Diambil</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['hardfile_siap']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm" style="border-left: 4px solid #28a745;">
                        <span class="info-box-icon bg-success text-white"><i class="fas fa-check-double"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">SK Selesai & Terbit</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 1.4rem;">{{ number_format($counts['selesai']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-stamp mr-2 text-info"></i> Daftar Permintaan SK Rektorat Masuk
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="d-inline-flex align-items-center">
                                <select id="filter-status-sk" class="form-control form-control-sm mr-2" style="width: 170px;">
                                    <option value="">Semua Status SK</option>
                                    <option value="diproses">Sedang Diproses</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                                <select id="filter-hardfile-sk" class="form-control form-control-sm" style="width: 190px;">
                                    <option value="">Semua Status Hardfile</option>
                                    <option value="belum_tersedia">Belum Tersedia</option>
                                    <option value="siap_diambil">Siap Diambil</option>
                                    <option value="telah_diterima_sdm">Telah Diterima SDM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table-sekretariat-sk" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th width="14%">No. Tiket</th>
                                <th width="18%">Pemohon & Unit</th>
                                <th width="24%">Permintaan Surat & Catatan SDM</th>
                                <th width="13%" class="text-center">Status SK</th>
                                <th width="13%" class="text-center">Hardfile Fisik</th>
                                <th width="14%" class="text-center">Aksi / Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-sekretariat-sk" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-sekretariat-sk-content">
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
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
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
