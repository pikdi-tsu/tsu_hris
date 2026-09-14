<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2"></i> Tambah Tarif Honorarium Jabatan Fungsional
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-tarif-honorarium.store') }}" method="POST" id="form-tarif-honor">
    @csrf
    <div class="modal-body p-4" style="background-color: #fafbfc;">
        {{-- Helper Guide Alert --}}
        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                <div style="font-size: 0.84rem; line-height: 1.45;">
                    Pilih jabatan fungsional dosen dari master data untuk menetapkan tarif kelebihan SKS, bimbingan & penguji TA/KP, serta honor kepanitiaan ujian UTS/UAS.
                </div>
            </div>
        </div>

        {{-- Identitas Jafung --}}
        <div class="card border mb-3 shadow-none" style="background-color: #ffffff; border-radius: 10px; border-color: #e2e8f0 !important;">
            <div class="card-body p-3">
                <div class="form-group mb-2">
                    <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                        Pilih dari Master Jabatan Fungsional <span class="text-danger">*</span>
                    </label>
                    <select class="form-control select2" id="select_master_jafung" name="jabatan_fungsional_id" style="border-radius: 8px;" required>
                        <option value="">-- Pilih Jabatan Fungsional --</option>
                        @foreach($jabatans as $jab)
                            @php
                                $already = in_array($jab->id, $existingIds);
                                $upper = strtoupper($jab->nama_jabatan);
                                $kodeGuess = 'TP';
                                if (str_contains($upper, 'GURU BESAR') || str_contains($upper, 'PROFESOR')) $kodeGuess = 'GB';
                                elseif (str_contains($upper, 'LEKTOR KEPALA')) $kodeGuess = 'LK';
                                elseif (str_contains($upper, 'LEKTOR')) $kodeGuess = 'L';
                                elseif (str_contains($upper, 'ASISTEN AHLI')) $kodeGuess = 'AA';
                            @endphp
                            <option value="{{ $jab->id }}" 
                                    data-nama="{{ $jab->nama_jabatan }}" 
                                    data-kode="{{ $kodeGuess }}"
                                    {{ $already ? 'disabled' : '' }}>
                                [{{ $kodeGuess }}] {{ $jab->nama_jabatan }} {{ $already ? '(Sudah Memiliki Tarif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted" style="font-size: 0.75rem;">Data bersumber dari Master Jabatan Fungsional TSU.</small>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4 form-group mb-0">
                        <label class="font-weight-bold text-dark" style="font-size: 0.82rem;">Kode Jafung <span class="text-danger">*</span></label>
                        <input type="text" name="kode_jafung" id="input_kode_jafung" class="form-control form-control-sm text-uppercase bg-light font-weight-bold" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="TP, AA, L, LK" readonly required>
                    </div>
                    <div class="col-md-8 form-group mb-0">
                        <label class="font-weight-bold text-dark" style="font-size: 0.82rem;">Nama Jabatan Fungsional <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jafung" id="input_nama_jafung" class="form-control form-control-sm bg-light font-weight-bold" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="Nama Jabatan Fungsional" readonly required>
                    </div>
                </div>
            </div>
        </div>

        {{-- 8A. Kelebihan SKS & Dosen Tidak Tetap --}}
        <div class="card border mb-3 shadow-none" style="background-color: #ffffff; border-radius: 10px; border-color: #e2e8f0 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-chalkboard-teacher mr-2" style="color: #047857;"></i>
                    <h6 class="font-weight-bold mb-0" style="color: #047857; font-size: 0.88rem;">
                        8A. Kelebihan SKS & Dosen Tidak Tetap
                    </h6>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-dark" style="font-size: 0.82rem;">Tarif / SKS / Pertemuan Hadir (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #cbd5e1; border-top-left-radius: 8px; border-bottom-left-radius: 8px; color: #094b54;">Rp</span>
                        </div>
                        <input type="number" name="tarif_sks_hadir" class="form-control font-weight-bold text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" min="0" step="1000" placeholder="25000" required>
                    </div>
                    <small class="form-text text-muted" style="font-size: 0.75rem;">Tarif kelebihan SKS per tatap muka perkuliahan.</small>
                </div>
            </div>
        </div>

        {{-- 8B. Bimbingan & Penguji TA / KP --}}
        <div class="card border mb-3 shadow-none" style="background-color: #ffffff; border-radius: 10px; border-color: #e2e8f0 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    <h6 class="font-weight-bold text-primary mb-0" style="font-size: 0.88rem;">
                        8B. Pembimbing & Penguji TA / Skripsi / KP
                    </h6>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Pembimbing TA (Rp/Mhs) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_bimbingan_ta" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="1000" placeholder="200000" required>
                        </div>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Penguji Sidang TA (Rp/Mhs) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_penguji_ta" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="1000" placeholder="75000" required>
                        </div>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Kerja Praktek / KP (Rp/Mhs) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_kerja_praktek" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="1000" placeholder="150000" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 8C. Ujian UTS & UAS --}}
        <div class="card border mb-3 shadow-none" style="background-color: #ffffff; border-radius: 10px; border-color: #e2e8f0 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-file-alt text-info mr-2"></i>
                    <h6 class="font-weight-bold text-info mb-0" style="font-size: 0.88rem;">
                        8C. Ujian (UTS & UAS)
                    </h6>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Soal Teori (T) (Rp/Berkas) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_soal_teori" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="1000" placeholder="25000" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Soal Teori/Praktik (T/P) (Rp/Berkas) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_soal_teori_praktik" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="1000" placeholder="30000" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Koreksi Teori (T) (Rp/Mhs) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_koreksi_teori" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="100" placeholder="2000" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.8rem;">Koreksi T/P (Rp/Mhs) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                            <input type="number" name="tarif_koreksi_teori_praktik" class="form-control" style="border-radius: 0 6px 6px 0;" min="0" step="100" placeholder="2500" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Keterangan / Catatan Regulasi
            </label>
            <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Catatan acuan SK Rektor atau ketentuan penetapan honorarium..."></textarea>
        </div>
    </div>
    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-check mr-1"></i> Simpan Tarif Jafung
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('#select_master_jafung').on('change', function() {
            var opt = $(this).find(':selected');
            var nama = opt.data('nama') || '';
            var kode = opt.data('kode') || '';
            $('#input_nama_jafung').val(nama);
            $('#input_kode_jafung').val(kode);
        });
    });
</script>
