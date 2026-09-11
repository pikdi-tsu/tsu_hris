<form action="{{ route('admin.jadwal-piket.store') }}" method="POST" id="formCreatePiket">
    @csrf
    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; padding: 1.1rem 1.4rem;">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem; color: #ffffff;">
            <i class="fas fa-calendar-plus mr-1"></i> Tambah Jadwal Piket Sabtu
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4" style="font-size: 9.5pt;">
        <div class="p-3 mb-3 rounded" style="background: #e6f3f4; border: 1px solid #c4e4e7; color: #07383f; font-size: 0.82rem;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1 text-primary" style="font-size: 1rem;"></i>
                <div>
                    <strong>Ketentuan Piket Sabtu:</strong> Jadwal piket ini khusus untuk hari <strong>Sabtu (08:00 - 12:00 WIB)</strong> bagi Tenaga Kependidikan (Tendik) dan Sarpras. Jadwal yang disimpan akan otomatis terintegrasi dengan validasi presensi HRIS.
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Tanggal Piket <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                @php
                    $defaultSaturday = Carbon\Carbon::now()->next(Carbon\Carbon::SATURDAY)->format('Y-m-d');
                @endphp
                <input type="date" class="form-control" name="tanggal_piket" id="tanggal_piket" value="{{ $defaultSaturday }}" style="border-radius: 6px;" required>
                <small class="form-text mt-1" id="hariInfo">Memeriksa hari...</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Karyawan Bertugas <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-control select2-modal" name="karyawan_ids[]" id="karyawan_ids" multiple="multiple" style="width:100%" required>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}">
                            {{ $karyawan->nama_lengkap }} (PIN: {{ $karyawan->pin_absensi ?? '-' }}) — {{ $karyawan->unit ? $karyawan->unit->nama_unit : 'Tendik' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted mt-1 d-block">Bisa memilih lebih dari satu karyawan (Tendik & Sarpras) sekaligus.</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Jam Kerja <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light text-muted" style="border-radius: 6px 0 0 6px;"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_mulai" value="08:00" style="border-radius: 0 6px 6px 0;" required>
                </div>
                <small class="text-muted mt-1 d-block">Jam Masuk</small>
            </div>
            <div class="col-sm-1 text-center pt-2 font-weight-bold text-muted">s/d</div>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light text-muted" style="border-radius: 6px 0 0 6px;"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_selesai" value="12:00" style="border-radius: 0 6px 6px 0;" required>
                </div>
                <small class="text-muted mt-1 d-block">Jam Pulang (Target 4 Jam)</small>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-sm-3 col-form-label font-weight-bold text-dark">Keterangan Penugasan</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="keterangan" value="Piket Sabtu Layanan Tendik & Sarpras" placeholder="Contoh: Piket Pelayanan Akademik / Kebersihan / Keamanan" style="border-radius: 6px;">
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3 border-top">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
        <button type="submit" class="btn btn-sm text-white px-4 font-weight-bold" id="btnSubmitCreate" style="background: #094b54; border-radius: 6px;">
            Simpan Jadwal Piket
        </button>
    </div>
</form>

<script>
    $('.select2-modal').select2({
        placeholder: '-- Pilih Karyawan Bertugas --',
        allowClear: true,
        dropdownParent: $('#formCreatePiket').closest('.modal')
    });

    function checkDayName() {
        var val = $('#tanggal_piket').val();
        if (val) {
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var d = new Date(val);
            var dayName = days[d.getDay()];
            if (d.getDay() === 6) {
                $('#hariInfo').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> ' + dayName + ' (Sesuai dengan ketentuan Piket Sabtu)</span>');
            } else {
                $('#hariInfo').html('<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> ' + dayName + ' (Bukan Hari Sabtu, pastikan tanggal sudah sesuai)</span>');
            }
        }
    }
    $('#tanggal_piket').on('change', checkDayName);
    checkDayName();

    $('#formCreatePiket').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSubmitCreate');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('Simpan Jadwal Piket');
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
                btn.prop('disabled', false).html('Simpan Jadwal Piket');
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan data.', 'error');
            }
        });
    });
</script>
