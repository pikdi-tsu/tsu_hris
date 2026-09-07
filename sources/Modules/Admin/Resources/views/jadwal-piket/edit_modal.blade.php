<form action="{{ route('admin.jadwal-piket.update', $piket->id) }}" method="POST" id="formEditPiket">
    @csrf
    @method('PUT')
    <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-edit mr-2"></i> Edit Jadwal Piket Sabtu
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body" style="font-size: 9.5pt;">
        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Tanggal Piket <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="date" class="form-control" name="tanggal_piket" value="{{ Carbon\Carbon::parse($piket->tanggal_piket)->format('Y-m-d') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Karyawan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-control select2-modal" name="data_dosen_tendik_id" style="width:100%" required>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}" {{ $karyawan->id === $piket->data_dosen_tendik_id ? 'selected' : '' }}>
                            {{ $karyawan->nama_lengkap }} (PIN: {{ $karyawan->pin_absensi ?? '-' }}) - {{ $karyawan->unit ? $karyawan->unit->nama_unit : 'Tendik' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Jam Kerja <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_mulai" value="{{ substr($piket->jam_mulai, 0, 5) }}" required>
                </div>
            </div>
            <div class="col-sm-1 text-center pt-2 font-weight-bold">s/d</div>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_selesai" value="{{ substr($piket->jam_selesai, 0, 5) }}" required>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Keterangan</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="keterangan" value="{{ $piket->keterangan }}">
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning font-weight-bold px-4" id="btnSubmitEdit">Update Jadwal</button>
    </div>
</form>

<script>
    $('.select2-modal').select2({
        dropdownParent: $('#formEditPiket').closest('.modal')
    });

    $('#formEditPiket').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSubmitEdit');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupdate...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('Update Jadwal');
                if (res.success) {
                    form.closest('.modal').modal('hide');
                    $('#table-piket').DataTable().ajax.reload();
                    Swal.fire('Berhasil!', res.message, 'success');
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('Update Jadwal');
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
            }
        });
    });
</script>
