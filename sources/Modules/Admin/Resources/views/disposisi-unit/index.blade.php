@extends('system::template.admin.header')

@section('content')
    <x-tsu-page-header
        title="Disposisi Masuk Unit Kerja"
        subtitle="Daftar surat masuk yang didisposisikan untuk ditindaklanjuti oleh unit kerja Anda"
        :icon="$menuIcon ?? 'fas fa-tasks'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">
            {{-- Info Unit Kerja Pegawai --}}
            @if($myUnit)
            <div class="alert alert-info border shadow-sm mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-building mr-2"></i> Unit Kerja Anda: <strong>{{ $myUnit->nama_unit }}</strong>
                        <span class="text-muted ml-2">({{ $karyawan->nama_lengkap ?? '' }})</span>
                    </div>
                    <span class="badge badge-light px-3 py-2 text-primary font-weight-bold">
                        <i class="fas fa-inbox mr-1"></i> Kotak Disposisi Unit
                    </span>
                </div>
            </div>
            @endif

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title font-weight-bold text-dark mb-0">
                                <i class="fas fa-clipboard-list mr-2 text-primary"></i> Daftar Surat Disposisi
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="d-inline-flex align-items-center">
                                @if(auth()->user()->isAdmin() || auth()->user()->hasRole(['super admin hris', 'admin hris testing', 'Super Admin', 'Admin SDM']))
                                <select id="filter-unit" class="form-control form-control-sm mr-2" style="width: 200px;">
                                    <option value="">Semua Unit Kerja</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                                    @endforeach
                                </select>
                                @endif
                                <select id="filter-status-disp" class="form-control form-control-sm" style="width: 170px;">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu">Menunggu Diterima</option>
                                    <option value="diterima">Diterima Unit</option>
                                    <option value="diproses">Sedang Diproses</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table-disposisi-unit" class="table table-bordered table-striped w-100">
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
    </section>

    {{-- MODAL CONTAINER --}}
    <div class="modal fade" id="modal-disposisi-unit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="modal-disposisi-unit-content">
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
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
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

    // Konfirmasi Terima Disposisi
    $(document).on('click', '.btn-terima-disp', function(e) {
        e.preventDefault();
        var url = $(this).data('url');

        Swal.fire({
            title: 'Konfirmasi Terima Disposisi',
            text: 'Apakah unit Anda menyatakan surat ini sudah diterima dan siap diproses?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Terima Surat',
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
        $('#modal-disposisi-unit-content').html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>Memuat formulir tindak lanjut...</div>');
        $('#modal-disposisi-unit').modal('show');

        $.get(url, function(res) {
            $('#modal-disposisi-unit-content').html(res);
            bsCustomFileInput.init();
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
