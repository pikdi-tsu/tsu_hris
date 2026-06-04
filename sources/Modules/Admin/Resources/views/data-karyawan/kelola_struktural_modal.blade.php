<div class="modal-header bg-dark">
    <h5 class="modal-title text-white">Kelola Jabatan Struktural: <span class="font-weight-bold text-uppercase">{{ $karyawan->nama }}</span></h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body p-4 bg-light">
    {{-- Form Tambah Struktural --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white font-weight-bold text-dark">
            <i class="fas fa-plus-circle mr-1"></i> Tambah Jabatan Struktural Baru
        </div>
        <div class="card-body">
            <form id="form-tambah-struktural" action="{{ route('admin.data-karyawan.store-struktural', $karyawan->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-7 form-group">
                        <label>Pilih Jabatan Struktural <span class="text-danger">*</span></label>
                        <select name="jabatan_struktural_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Pilih --</option>
                            @foreach($masterStruktural as $ms)
                                <option value="{{ $ms->id }}">{{ $ms->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 form-group">
                        <label>Tgl Mulai Menjabat <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_mulai" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="text-right mt-2">
                    <button type="submit" class="btn btn-dark btn-sm" id="btn-save-str">
                        <i class="fas fa-save mr-1"></i> Simpan Struktural
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- List Struktural Aktif --}}
    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
        Daftar Jabatan Struktural Aktif Saat Ini
    </h6>
    <div id="struktural-list-container">
        @include('admin::data-karyawan._struktural_list', ['strukturals' => $strukturals])
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
</div>

<script>
$(document).ready(function() {
    if($('.select2').length) {
        $('.select2').select2({
            dropdownParent: $('#modal-edit'),
            width: '100%'
        });
    }

    // Submit Add Struktural via AJAX
    $('#form-tambah-struktural').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#btn-save-str');
        let originalText = btn.html();

        btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.html(originalText).prop('disabled', false);
                if(res.status === 'success') {
                    $('#struktural-list-container').html(res.html);
                    form.trigger('reset');
                    form.find('.select2').val('').trigger('change');
                    Swal.fire({
                        icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false
                    });
                    
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

    // Handle Delete Struktural
    $(document).on('click', '.btn-delete-str', function(e) {
        e.preventDefault();
        let btn = $(this);
        let url = btn.data('url');
        
        Swal.fire({
            title: 'Lepas Jabatan Struktural?',
            text: "Data ini akan dinonaktifkan dari daftar struktural aktif.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Lepas!'
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
                            $('#struktural-list-container').html(res.html);
                            if($.fn.DataTable.isDataTable('#table-karyawan')){
                                $('#table-karyawan').DataTable().ajax.reload(null, false);
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal melepas data.', 'error');
                    }
                });
            }
        });
    });
});
</script>
