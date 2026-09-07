<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold"><i class="fas fa-clock mr-2"></i> Tambah Master Shift & Jam Kerja</h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-shift.store') }}" method="POST" id="formShift">
    @csrf
    <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Nama Shift <span class="text-danger">*</span></label>
                <input type="text" name="nama_shift" class="form-control" placeholder="Contoh: Tendik Pagi, Dosen Asisten Ahli" required>
            </div>
            <div class="col-md-3 form-group">
                <label class="font-weight-bold">Kode Shift</label>
                <input type="text" name="kode_shift" class="form-control" placeholder="Contoh: TNDK-PAGI, DSN-AA">
            </div>
            <div class="col-md-3 form-group">
                <label class="font-weight-bold">Tipe Shift <span class="text-danger">*</span></label>
                <select name="tipe_shift" id="tipe_shift" class="form-control" required>
                    <option value="jadwal">Jadwal Jam Harian (Tendik, Sarpras, Satpam)</option>
                    <option value="durasi">Target Durasi Jam Kerja (Dosen, Struktural)</option>
                </select>
            </div>
        </div>

        <div class="row" id="section-durasi" style="display: none;">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Target Jam Kerja Efektif per Hari (Jam) <span class="text-danger">*</span></label>
                <input type="number" step="0.5" name="target_durasi_jam" id="target_durasi_jam" class="form-control" value="7" min="1" max="24">
                <small class="text-muted">Contoh: 7 Jam (Asisten Ahli/Struktural), 6 Jam (Lektor 200), 5 Jam (Lektor 300), 4 Jam (Lektor Kepala)</small>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan atau aturan khusus terkait shift ini..."></textarea>
        </div>

        <div id="section-jadwal">
            <hr>
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-calendar-week mr-1"></i> Rincian Jadwal Harian</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center">
                    <thead class="bg-light">
                        <tr>
                            <th width="12%">Hari</th>
                            <th width="10%">Libur?</th>
                            <th width="18%">Jam Masuk</th>
                            <th width="18%">Jam Pulang</th>
                            <th width="18%">Istirahat Mulai</th>
                            <th width="18%">Istirahat Selesai</th>
                            <th width="6%">Lintas Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hariNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                        @endphp
                        @foreach ($hariNames as $hariNum => $hariName)
                            <tr>
                                <td class="font-weight-bold align-middle">{{ $hariName }}</td>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-libur" id="libur_{{ $hariNum }}" name="hari[{{ $hariNum }}][is_libur]" value="1" {{ $hariNum == 7 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="libur_{{ $hariNum }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_masuk]" class="form-control form-control-sm time-field" value="{{ $hariNum == 6 ? '08:00' : ($hariNum == 7 ? '' : '08:00') }}">
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_pulang]" class="form-control form-control-sm time-field" value="{{ $hariNum == 6 ? '12:00' : ($hariNum == 7 ? '' : '16:30') }}">
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_istirahat_mulai]" class="form-control form-control-sm time-field" value="{{ $hariNum == 5 ? '11:30' : ($hariNum < 5 ? '12:00' : '') }}">
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_istirahat_selesai]" class="form-control form-control-sm time-field" value="{{ $hariNum <= 5 ? '13:00' : '' }}">
                                </td>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="cross_{{ $hariNum }}" name="hari[{{ $hariNum }}][is_cross_day]" value="1">
                                        <label class="custom-control-label" for="cross_{{ $hariNum }}"></label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-4">Simpan</button>
    </div>
</form>

<script>
    $('#tipe_shift').on('change', function() {
        if ($(this).val() === 'durasi') {
            $('#section-durasi').slideDown();
            $('#section-jadwal').slideUp();
        } else {
            $('#section-durasi').slideUp();
            $('#section-jadwal').slideDown();
        }
    });

    $('.check-libur').on('change', function() {
        var row = $(this).closest('tr');
        var inputs = row.find('.time-field');
        if ($(this).is(':checked')) {
            inputs.prop('disabled', true).val('');
        } else {
            inputs.prop('disabled', false);
        }
    });
    // Trigger initial
    $('.check-libur:checked').each(function() {
        $(this).closest('tr').find('.time-field').prop('disabled', true).val('');
    });
</script>
