<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Master Shift & Jam Kerja</h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-shift.update', $shift->id) }}" method="POST" id="formShift">
    @csrf
    @method('PUT')
    <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Nama Shift <span class="text-danger">*</span></label>
                <input type="text" name="nama_shift" class="form-control" value="{{ $shift->nama_shift }}" required>
            </div>
            <div class="col-md-3 form-group">
                <label class="font-weight-bold">Kode Shift</label>
                <input type="text" name="kode_shift" class="form-control" value="{{ $shift->kode_shift }}">
            </div>
            <div class="col-md-3 form-group">
                <label class="font-weight-bold">Tipe Shift <span class="text-danger">*</span></label>
                <select name="tipe_shift" id="tipe_shift" class="form-control" required>
                    <option value="jadwal" {{ $shift->tipe_shift == 'jadwal' ? 'selected' : '' }}>Jadwal Jam Harian (Tendik, Sarpras, Satpam)</option>
                    <option value="durasi" {{ $shift->tipe_shift == 'durasi' ? 'selected' : '' }}>Target Durasi Jam Kerja (Dosen, Struktural)</option>
                </select>
            </div>
        </div>

        <div class="row" id="section-durasi" style="{{ $shift->tipe_shift == 'durasi' ? '' : 'display: none;' }}">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Target Jam Kerja Efektif per Hari (Jam) <span class="text-danger">*</span></label>
                <input type="number" step="0.5" name="target_durasi_jam" id="target_durasi_jam" class="form-control" value="{{ round($shift->target_durasi_menit / 60, 1) }}" min="1" max="24">
                <small class="text-muted">Contoh: 7 Jam (Asisten Ahli/Struktural), 6 Jam (Lektor 200), 5 Jam (Lektor 300), 4 Jam (Lektor Kepala)</small>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control" rows="2">{{ $shift->keterangan }}</textarea>
        </div>

        <div id="section-jadwal" style="{{ $shift->tipe_shift == 'jadwal' ? '' : 'display: none;' }}">
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
                            $detailMap = $shift->details->keyBy('hari');
                        @endphp
                        @foreach ($hariNames as $hariNum => $hariName)
                            @php
                                $d = $detailMap->get($hariNum);
                                $isLibur = $d ? $d->is_libur : ($hariNum == 7);
                                $jamIn = $d && $d->jam_masuk ? substr($d->jam_masuk, 0, 5) : '';
                                $jamOut = $d && $d->jam_pulang ? substr($d->jam_pulang, 0, 5) : '';
                                $istStart = $d && $d->jam_istirahat_mulai ? substr($d->jam_istirahat_mulai, 0, 5) : '';
                                $istEnd = $d && $d->jam_istirahat_selesai ? substr($d->jam_istirahat_selesai, 0, 5) : '';
                                $isCross = $d ? $d->is_cross_day : false;
                            @endphp
                            <tr>
                                <td class="font-weight-bold align-middle">{{ $hariName }}</td>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-libur" id="libur_{{ $hariNum }}" name="hari[{{ $hariNum }}][is_libur]" value="1" {{ $isLibur ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="libur_{{ $hariNum }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_masuk]" class="form-control form-control-sm time-field" value="{{ $jamIn }}" {{ $isLibur ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_pulang]" class="form-control form-control-sm time-field" value="{{ $jamOut }}" {{ $isLibur ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_istirahat_mulai]" class="form-control form-control-sm time-field" value="{{ $istStart }}" {{ $isLibur ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_istirahat_selesai]" class="form-control form-control-sm time-field" value="{{ $istEnd }}" {{ $isLibur ? 'disabled' : '' }}>
                                </td>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="cross_{{ $hariNum }}" name="hari[{{ $hariNum }}][is_cross_day]" value="1" {{ $isCross ? 'checked' : '' }}>
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
        <button type="submit" class="btn btn-primary font-weight-bold px-4">Simpan Perubahan</button>
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
</script>
