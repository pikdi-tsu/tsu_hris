<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2 text-warning"></i> Tambah Master Shift & Jam Kerja
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-shift.store') }}" method="POST" id="formShift">
    @csrf
    <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
        <div class="p-3 rounded mb-3" style="background: rgba(9, 75, 84, 0.05); border-left: 4px solid var(--tsu-primary, #094b54);">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="color: var(--tsu-primary, #094b54); font-size: 1rem;"></i>
                <div class="text-sm text-dark">
                    <b>Pola Jam Kerja:</b> Pilih tipe <i>Jadwal Jam Harian</i> untuk pegawai dengan jam kerja tetap (Tendik, Satpam, Sarpras), atau <i>Target Durasi</i> untuk pegawai dengan beban jam kerja harian fleksibel (Dosen, Struktural).
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-sm text-dark"><i class="fas fa-clock text-primary mr-1"></i> Nama Shift <span class="text-danger">*</span></label>
                <input type="text" name="nama_shift" class="form-control" placeholder="Contoh: Tendik Reguler, Dosen Asisten Ahli" required style="border-radius: 8px;">
            </div>
            <div class="col-md-3 form-group mb-3">
                <label class="font-weight-bold text-sm text-dark"><i class="fas fa-tag text-primary mr-1"></i> Kode Shift</label>
                <input type="text" name="kode_shift" class="form-control" placeholder="Contoh: TNDK-PAGI" style="border-radius: 8px;">
            </div>
            <div class="col-md-3 form-group mb-3">
                <label class="font-weight-bold text-sm text-dark"><i class="fas fa-sliders-h text-primary mr-1"></i> Tipe Shift <span class="text-danger">*</span></label>
                <select name="tipe_shift" id="tipe_shift" class="form-control custom-select" required style="border-radius: 8px;">
                    <option value="jadwal">Jadwal Jam Harian</option>
                    <option value="durasi">Target Durasi Jam</option>
                </select>
            </div>
        </div>

        <div class="row" id="section-durasi" style="display: none;">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-sm text-dark"><i class="fas fa-hourglass-half text-primary mr-1"></i> Target Jam Kerja Efektif per Hari (Jam) <span class="text-danger">*</span></label>
                <input type="number" step="0.5" name="target_durasi_jam" id="target_durasi_jam" class="form-control" value="7" min="1" max="24" style="border-radius: 8px;">
                <small class="text-muted d-block mt-1">Contoh: 7 Jam (Asisten Ahli/Struktural), 6 Jam (Lektor 200), 5 Jam (Lektor 300), 4 Jam (Lektor Kepala)</small>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Tambahkan catatan atau aturan khusus terkait pola shift ini (opsional)..." style="border-radius: 8px;"></textarea>
        </div>

        <div id="section-jadwal">
            <div class="d-flex align-items-center mb-2 mt-3">
                <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-calendar-week text-primary mr-2"></i> Rincian Jadwal Harian (Senin - Minggu)</h6>
            </div>
            <div class="table-responsive rounded border">
                <table class="table table-bordered table-sm text-center mb-0">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th width="14%" class="text-dark font-weight-bold">Hari</th>
                            <th width="10%" class="text-dark font-weight-bold">Libur?</th>
                            <th width="18%" class="text-dark font-weight-bold">Jam Masuk</th>
                            <th width="18%" class="text-dark font-weight-bold">Jam Pulang</th>
                            <th width="18%" class="text-dark font-weight-bold">Istirahat Mulai</th>
                            <th width="18%" class="text-dark font-weight-bold">Istirahat Selesai</th>
                            <th width="6%" class="text-dark font-weight-bold">Lintas Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hariNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                        @endphp
                        @foreach ($hariNames as $hariNum => $hariName)
                            <tr>
                                <td class="font-weight-bold align-middle text-dark">{{ $hariName }}</td>
                                <td class="align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-libur" id="libur_{{ $hariNum }}" name="hari[{{ $hariNum }}][is_libur]" value="1" {{ $hariNum == 7 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="libur_{{ $hariNum }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_masuk]" class="form-control form-control-sm time-field" value="{{ $hariNum == 6 ? '08:00' : ($hariNum == 7 ? '' : '08:00') }}" style="border-radius: 6px;">
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_pulang]" class="form-control form-control-sm time-field" value="{{ $hariNum == 6 ? '12:00' : ($hariNum == 7 ? '' : '16:30') }}" style="border-radius: 6px;">
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_istirahat_mulai]" class="form-control form-control-sm time-field" value="{{ $hariNum == 5 ? '11:30' : ($hariNum < 5 ? '12:00' : '') }}" style="border-radius: 6px;">
                                </td>
                                <td>
                                    <input type="time" name="hari[{{ $hariNum }}][jam_istirahat_selesai]" class="form-control form-control-sm time-field" value="{{ $hariNum <= 5 ? '13:00' : '' }}" style="border-radius: 6px;">
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
    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Data
        </button>
    </div>
</form>

<script>
    $('#tipe_shift').on('change', function() {
        if ($(this).val() === 'durasi') {
            $('#section-durasi').slideDown(200);
            $('#section-jadwal').slideUp(200);
        } else {
            $('#section-durasi').slideUp(200);
            $('#section-jadwal').slideDown(200);
        }
    });

    $('.check-libur').on('change', function() {
        var row = $(this).closest('tr');
        var isChecked = $(this).is(':checked');
        row.find('.time-field').prop('disabled', isChecked);
        if (isChecked) {
            row.find('.time-field').val('');
        }
    });

    // Inisialisasi awal pada baris yang libur (cth: Minggu)
    $('.check-libur:checked').each(function() {
        $(this).closest('tr').find('.time-field').prop('disabled', true);
    });
</script>
