@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Registrasi Surat Masuk & SIKD"
        subtitle="Pusat pencatatan surat masuk dari pihak eksternal, pengarsipan berkas scan, dan lembar disposisi unit kerja"
        :icon="$menuIcon ?? 'fas fa-inbox'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-envelope mr-2 text-primary"></i> Daftar Surat Masuk Eksternal
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="d-inline-flex align-items-center">
                                <select id="filter-sifat" class="form-control form-control-sm mr-2" style="width: 140px;">
                                    <option value="">Semua Sifat</option>
                                    <option value="biasa">Biasa</option>
                                    <option value="penting">Penting</option>
                                    <option value="segera">Segera</option>
                                    <option value="rahasia">Rahasia</option>
                                </select>
                                <select id="filter-status" class="form-control form-control-sm mr-3" style="width: 150px;">
                                    <option value="">Semua Status</option>
                                    <option value="terdaftar">Terdaftar</option>
                                    <option value="didisposisi">Didisposisi</option>
                                    <option value="proses_unit">Proses Unit</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm font-weight-bold" id="btn-create-surat-masuk">
                                    <i class="fas fa-plus mr-1"></i> Registrasi Surat Masuk
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table-surat-masuk" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th width="12%">No. Agenda</th>
                                <th width="14%">No. Surat Asal</th>
                                <th width="16%">Pengirim / Instansi</th>
                                <th width="20%">Perihal</th>
                                <th width="8%" class="text-center">Sifat</th>
                                <th width="14%">Disposisi Unit</th>
                                <th width="8%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Aksi</th>
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
    <div class="modal fade" id="modal-surat-masuk" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-surat-masuk-content">
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
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'no_agenda', name: 'no_agenda' },
            { data: 'no_surat_asal', name: 'no_surat_asal' },
            { data: 'pengirim_instansi', name: 'pengirim_instansi' },
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

    // Buka Modal Registrasi Surat Masuk
    $('#btn-create-surat-masuk').on('click', function(e) {
        e.preventDefault();
        $('#modal-surat-masuk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>Memuat form registrasi...</div>');
        $('#modal-surat-masuk').modal('show');

        $.get("{{ route('admin.surat-masuk.create-modal') }}", function(res) {
            $('#modal-surat-masuk-content').html(res);
            bsCustomFileInput.init();
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
        $('#modal-surat-masuk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>Memuat lembar disposisi...</div>');
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
        $('#modal-surat-masuk-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><br>Memuat detail surat...</div>');
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
