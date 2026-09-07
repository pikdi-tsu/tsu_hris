<form action="{{ route('admin.master-tarif-honorarium.update', $data->id) }}" method="POST" id="form-tarif-honor">
    @csrf
    @method('PUT')
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-edit mr-2"></i> Edit Tarif Honorarium: {{ $data->nama_jafung }} ({{ $data->kode_jafung }})
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body p-4">
        <div class="row">
            {{-- Dropdown Pilih dari Master Jabatan Fungsional --}}
            <div class="col-md-12 mb-2">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Pilih dari Master Jabatan Fungsional <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm select2" id="edit_select_master_jafung" name="jabatan_fungsional_id">
                        <option value="">-- Pilih Jabatan Fungsional --</option>
                        @foreach($jabatans as $jab)
                            @php
                                $upper = strtoupper($jab->nama_jabatan);
                                $kodeGuess = 'TP';
                                if (str_contains($upper, 'GURU BESAR') || str_contains($upper, 'PROFESOR')) $kodeGuess = 'GB';
                                elseif (str_contains($upper, 'LEKTOR KEPALA')) $kodeGuess = 'LK';
                                elseif (str_contains($upper, 'LEKTOR')) $kodeGuess = 'L';
                                elseif (str_contains($upper, 'ASISTEN AHLI')) $kodeGuess = 'AA';
                                $selected = ($data->jabatan_fungsional_id == $jab->id) || ($data->kode_jafung == $kodeGuess);
                            @endphp
                            <option value="{{ $jab->id }}" 
                                    data-nama="{{ $jab->nama_jabatan }}" 
                                    data-kode="{{ $kodeGuess }}"
                                    {{ $selected ? 'selected' : '' }}>
                                [{{ $kodeGuess }}] {{ $jab->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Data bersumber dari Master Jabatan Fungsional.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold small">Kode Jafung <span class="text-danger">*</span></label>
                    <input type="text" name="kode_jafung" id="edit_input_kode_jafung" class="form-control form-control-sm text-uppercase bg-light" value="{{ $data->kode_jafung }}" readonly required>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label class="font-weight-bold small">Nama Jabatan Fungsional <span class="text-danger">*</span></label>
                    <input type="text" name="nama_jafung" id="edit_input_nama_jafung" class="form-control form-control-sm bg-light" value="{{ $data->nama_jafung }}" readonly required>
                </div>
            </div>
        </div>

        <hr class="my-2">

        {{-- 8A. Kelebihan SKS --}}
        <h6 class="font-weight-bold text-success mt-2 mb-2">
            <i class="fas fa-chalkboard-teacher mr-1"></i> 8A. Kelebihan SKS & Dosen Tidak Tetap
        </h6>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Tarif / SKS / Pertemuan (Rp) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_sks_hadir" class="form-control form-control-sm" min="0" step="1000" value="{{ $data->tarif_sks_hadir }}" required>
            </div>
        </div>

        <hr class="my-2">

        {{-- 8B. Bimbingan & Penguji TA / KP --}}
        <h6 class="font-weight-bold text-primary mt-2 mb-2">
            <i class="fas fa-user-graduate mr-1"></i> 8B. Pembimbing & Penguji TA / Skripsi / KP
        </h6>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Pembimbing Tugas Akhir / Skripsi (Rp/Mhs) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_bimbingan_ta" class="form-control form-control-sm" min="0" step="1000" value="{{ $data->tarif_bimbingan_ta }}" required>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Penguji Sidang Tugas Akhir / Skripsi (Rp/Mhs) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_penguji_ta" class="form-control form-control-sm" min="0" step="1000" value="{{ $data->tarif_penguji_ta }}" required>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Kerja Praktek / KP (Rp/Mhs) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_kerja_praktek" class="form-control form-control-sm" min="0" step="1000" value="{{ $data->tarif_kerja_praktek }}" required>
            </div>
        </div>

        <hr class="my-2">

        {{-- 8C. Ujian UTS & UAS --}}
        <h6 class="font-weight-bold text-info mt-2 mb-2">
            <i class="fas fa-file-alt mr-1"></i> 8C. Ujian (UTS & UAS)
        </h6>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Pembuatan Soal Teori (T) (Rp/Berkas) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_soal_teori" class="form-control form-control-sm" min="0" step="1000" value="{{ $data->tarif_soal_teori }}" required>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Pembuatan Soal Teori/Praktik (T/P) (Rp/Berkas) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_soal_teori_praktik" class="form-control form-control-sm" min="0" step="1000" value="{{ $data->tarif_soal_teori_praktik }}" required>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Koreksi Lembar Jawaban Teori (T) (Rp/Mhs) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_koreksi_teori" class="form-control form-control-sm" min="0" step="100" value="{{ $data->tarif_koreksi_teori }}" required>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-6 col-form-label small">Koreksi Lembar Jawaban T/P (Rp/Mhs) <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <input type="number" name="tarif_koreksi_teori_praktik" class="form-control form-control-sm" min="0" step="100" value="{{ $data->tarif_koreksi_teori_praktik }}" required>
            </div>
        </div>

        <hr class="my-2">

        <div class="form-group mb-0">
            <label class="font-weight-bold small">Keterangan / Catatan Regulasi</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Catatan acuan SK Rektor / Ketentuan tarif...">{{ $data->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('#edit_select_master_jafung').on('change', function() {
            var opt = $(this).find(':selected');
            var nama = opt.data('nama') || '';
            var kode = opt.data('kode') || '';
            if (nama) $('#edit_input_nama_jafung').val(nama);
            if (kode) $('#edit_input_kode_jafung').val(kode);
        });
    });
</script>
