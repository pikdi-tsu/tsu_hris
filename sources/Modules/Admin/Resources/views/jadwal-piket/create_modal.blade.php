<form action="{{ route('admin.jadwal-piket.store') }}" method="POST" id="formCreatePiket">
    @csrf
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-calendar-plus mr-2"></i> Tambah Jadwal Piket Sabtu
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body" style="font-size: 9.5pt;">
        <div class="alert alert-info py-2 px-3 small">
            <i class="fas fa-info-circle mr-1"></i> Jadwal piket ini khusus untuk hari <strong>Sabtu (08:00 - 12:00)</strong> bagi tenaga kependidikan (Tendik) dan Sarpras. Data ini akan otomatis disinkronkan ke perhitungan presensi.
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Tanggal Piket <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                @php
                    // Default hari Sabtu terdekat
                    $defaultSaturday = Carbon\Carbon::now()->next(Carbon\Carbon::SATURDAY)->format('Y-m-d');
                @endphp
                <input type="date" class="form-control" name="tanggal_piket" id="tanggal_piket" value="{{ $defaultSaturday }}" required>
                <small class="text-muted" id="hariInfo">Pastikan memilih hari Sabtu</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Karyawan Bertugas <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-control select2-modal" name="karyawan_ids[]" id="karyawan_ids" multiple="multiple" style="width:100%" required>
                    @foreach ($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}">
                            {{ $karyawan->nama_lengkap }} (PIN: {{ $karyawan->pin_absensi ?? '-' }}) - {{ $karyawan->unit ? $karyawan->unit->nama_unit : 'Tendik' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Bisa memilih lebih dari satu karyawan (Tendik & Sarpras)</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Jam Kerja <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_mulai" value="08:00" required>
                </div>
                <small class="text-muted">Jam Masuk</small>
            </div>
            <div class="col-sm-1 text-center pt-2 font-weight-bold">s/d</div>
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="time" class="form-control" name="jam_selesai" value="12:00" required>
                </div>
                <small class="text-muted">Jam Pulang (Target 4 Jam)</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label font-weight-bold">Keterangan Penugasan</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="keterangan" value="Piket Sabtu Layanan Tendik & Sarpras" placeholder="Contoh: Piket Pelayanan Akademik / Kebersihan">
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSubmitCreate">Simpan Jadwal Piket</button>
    </div>
</form>

<script>
    $('.select2-modal').select2({
        placeholder: '-- Pilih Karyawan --',
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
                $('#hariInfo').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> ' + dayName + ' (Sesuai Ketentuan Piket Sabtu)</span>');
            } else {
                $('#hariInfo').html('<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> ' + dayName + ' (Bukan Hari Sabtu, pastikan tanggal sudah tepat)</span>');
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
                    Swal.fire('Berhasil!', res.message, 'success');
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('Simpan Jadwal Piket');
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
            }
        });
    });
</script>
