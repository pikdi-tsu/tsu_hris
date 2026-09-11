<form action="{{ route('admin.jadwal-piket.update', $piket->id) }}" method="POST" id="formEditPiket">
    @csrf
    @method('PUT')
    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; padding: 1.1rem 1.4rem;">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem; color: #ffffff;">
            <i class="fas fa-edit mr-1"></i> Edit Jadwal Piket Sabtu
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4" style="font-size: 9.5pt;">
        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Tanggal Piket <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="date" class="form-control" name="tanggal_piket" id="edit_tanggal_piket" value="{{ Carbon\Carbon::parse($piket->tanggal_piket)->format('Y-m-d') }}" style="border-radius: 6px;" required>
                <small class="form-text mt-1" id="editHariInfo">Memeriksa hari...</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Karyawan <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-control select2-modal" name="data_dosen_tendik_id" style="width:100%" required>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}" {{ $karyawan->id === $piket->data_dosen_tendik_id ? 'selected' : '' }}>
                            {{ $karyawan->nama_lengkap }} (PIN: {{ $karyawan->pin_absensi ?? '-' }}) — {{ $karyawan->unit ? $karyawan->unit->nama_unit : 'Tendik' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Jam Kerja <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light text-muted" style="border-radius: 6px 0 0 6px;"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_mulai" value="{{ substr($piket->jam_mulai, 0, 5) }}" style="border-radius: 0 6px 6px 0;" required>
                </div>
                <small class="text-muted mt-1 d-block">Jam Masuk</small>
            </div>
            <div class="col-sm-1 text-center pt-2 font-weight-bold text-muted">s/d</div>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light text-muted" style="border-radius: 6px 0 0 6px;"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_selesai" value="{{ substr($piket->jam_selesai, 0, 5) }}" style="border-radius: 0 6px 6px 0;" required>
                </div>
                <small class="text-muted mt-1 d-block">Jam Pulang (Target 4 Jam)</small>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Keterangan</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="keterangan" value="{{ $piket->keterangan }}" placeholder="Keterangan piket sabtu" style="border-radius: 6px;">
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3 border-top">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
        <button type="submit" class="btn btn-sm text-white px-4 font-weight-bold" id="btnSubmitEdit" style="background: #094b54; border-radius: 6px;">
            Update Jadwal
        </button>
    </div>
</form>

<script>
    $('.select2-modal').select2({
        dropdownParent: $('#formEditPiket').closest('.modal')
    });

    function checkEditDayName() {
        var val = $('#edit_tanggal_piket').val();
        if (val) {
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var d = new Date(val);
            var dayName = days[d.getDay()];
            if (d.getDay() === 6) {
                $('#editHariInfo').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> ' + dayName + ' (Sesuai dengan ketentuan Piket Sabtu)</span>');
            } else {
                $('#editHariInfo').html('<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> ' + dayName + ' (Bukan Hari Sabtu, pastikan tanggal sudah sesuai)</span>');
            }
        }
    }
    $('#edit_tanggal_piket').on('change', checkEditDayName);
    checkEditDayName();

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
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#094b54'
                    });
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('Update Jadwal');
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memperbarui data.', 'error');
            }
        });
    });
</script>
