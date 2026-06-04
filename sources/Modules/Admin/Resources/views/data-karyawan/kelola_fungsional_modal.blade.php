<div class="modal-header bg-secondary">
    <h5 class="modal-title text-white">Kelola Jabatan Fungsional: <span class="font-weight-bold text-uppercase">{{ $karyawan->nama }}</span></h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body p-4 bg-light">
    {{-- Form Tambah Fungsional --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white font-weight-bold text-primary">
            <i class="fas fa-plus-circle mr-1"></i> Tambah Jabatan Fungsional Baru
        </div>
        <div class="card-body">
            <form id="form-tambah-fungsional" action="{{ route('admin.data-karyawan.store-fungsional', $karyawan->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Pilih Jabatan <span class="text-danger">*</span></label>
                        <select name="jabatan_fungsional_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Pilih --</option>
                            @foreach($masterFungsional as $mf)
                                <option value="{{ $mf->id }}">{{ $mf->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Pangkat/Golongan</label>
                        <select name="pangkat_golongan_id" class="form-control select2" style="width: 100%;">
                            <option value="">-- Kosongkan Jika Tidak Ada --</option>
                            @foreach($masterPangkat as $mp)
                                <option value="{{ $mp->id }}">{{ $mp->nama_pangkat_golongan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Tgl Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_mulai" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Nomor SK</label>
                        <input type="text" name="sk_jabatan" class="form-control" placeholder="Opsional">
                    </div>
                </div>
                <div class="text-right mt-2">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-fung">
                        <i class="fas fa-save mr-1"></i> Simpan Fungsional
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- List Fungsional Aktif --}}
    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
        Daftar Jabatan Fungsional Aktif Saat Ini
    </h6>
    <div id="fungsional-list-container">
        @include('admin::data-karyawan._fungsional_list', ['fungsionals' => $fungsionals])
    </div>
</div>
<div class="modal-footer bg-light px-4 py-3" style="border-top: 1px solid #dee2e6;">
    <button type="button" class="btn btn-outline-secondary font-weight-bold mr-auto shadow-sm btn-back-to-edit" data-url="{{ route('admin.data-karyawan.edit', $karyawan->id) }}">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Edit Profil
    </button>
    <button type="button" class="btn btn-secondary font-weight-bold shadow-sm" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>

<script>
$(document).ready(function() {
    if($('.select2').length) {
        $('.select2').select2({
            dropdownParent: $('#modal-edit'),
            width: '100%'
        });
    }

    // Handle Kembali ke Edit Profil
    $('.btn-back-to-edit').on('click', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-info"></div><p>Memuat Form Edit...</p></div>`);
        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                $('#modal-edit-content').html(res);
                setTimeout(function() {
                    $('#dynamic-tabs a[href="#tab-tab_kepangkatan"]').tab('show');
                }, 400);
            },
            error: function() {
                $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form.</div>`);
            }
        });
    });

    // Submit Add Fungsional via AJAX
    $('#form-tambah-fungsional').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#btn-save-fung');
        let originalText = btn.html();

        btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.html(originalText).prop('disabled', false);
                if(res.status === 'success') {
                    $('#fungsional-list-container').html(res.html);
                    form.trigger('reset');
                    form.find('.select2').val('').trigger('change');
                    Swal.fire({
                        icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false
                    });
                    
                    // Reload main datatable if exists
                    if($.fn.DataTable.isDataTable('#table-karyawan')){
                        $('#table-karyawan').DataTable().ajax.reload(null, false);
                    }
                }
            },
            error: function(xhr) {
                btn.html(originalText).prop('disabled', false);
                Swal.fire('Error', 'Gagal menyimpan data.', 'error');
            }
        });
    });

    // Handle Delete Fungsional
    $(document).on('click', '.btn-delete-fung', function(e) {
        e.preventDefault();
        let btn = $(this);
        let url = btn.data('url');
        
        Swal.fire({
            title: 'Hapus Jabatan Fungsional?',
            text: "Data ini akan dinonaktifkan dari daftar fungsional aktif.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.status === 'success') {
                            $('#fungsional-list-container').html(res.html);
                            // Reload main datatable if exists
                            if($.fn.DataTable.isDataTable('#table-karyawan')){
                                $('#table-karyawan').DataTable().ajax.reload(null, false);
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal menghapus data.', 'error');
                    }
                });
            }
        });
    });
});
</script>
