<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2 text-warning"></i> Edit Master Cuti
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-cuti.update', $cuti->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-warning">
        <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit mr-1"></i> Edit Master Cuti</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4">
        {{-- Trigger Kategori Cuti --}}
        <div class="form-group mb-3 p-3 bg-light rounded border border-warning">
            <label for="kategori_cuti_edit" class="font-weight-bold text-dark mb-1">
                <i class="fas fa-layer-group mr-1 text-warning"></i> Kategori Cuti (Pembeda Sistem) <span class="text-danger">*</span>
            </label>
            <select class="form-control font-weight-bold" id="kategori_cuti_edit" name="kategori_cuti" required>
                <option value="tahunan" {{ ($cuti->kategori_cuti ?? 'tahunan') === 'tahunan' ? 'selected' : '' }}>
                    Cuti Tahunan &amp; Reguler (Mengurangi Kuota 12 Hari)
                </option>
                <option value="khusus" {{ ($cuti->kategori_cuti ?? '') === 'khusus' ? 'selected' : '' }}>
                    Cuti Khusus (Surat Edaran SDM - Tanpa Potong Kuota)
                </option>
            </select>
            <small class="text-muted mt-1 d-block" id="kategori-help-text-edit">
                Kategori ini secara otomatis menentukan tab tampilan di menu pengajuan cuti pegawai.
            </small>
        </div>

        <div class="form-group mb-3">
            <label for="jeniscuti" class="font-weight-bold">Nama / Jenis Cuti <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jeniscuti" name="jeniscuti" value="{{ $cuti->jeniscuti }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="durasicuti" class="font-weight-bold">Maksimal Durasi (Hari) <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control" id="durasicuti" name="durasicuti"
                    value="{{ $cuti->durasicuti }}" required>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label for="minimalhari" class="font-weight-bold">Minimal Hari Pengajuan <span class="text-danger">*</span></label>
                <input type="number" min="0" class="form-control" id="minimalhari" name="minimalhari"
                    value="{{ $cuti->minimalhari }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="memotong_kuota_edit" class="font-weight-bold">Pengurangan Kuota 12 Hari</label>
                <select class="form-control" id="memotong_kuota_edit" name="memotong_kuota">
                    <option value="1" {{ (string)$cuti->memotong_kuota === '1' ? 'selected' : '' }}>Ya, Memotong Kuota Tahunan</option>
                    <option value="0" {{ (string)$cuti->memotong_kuota === '0' ? 'selected' : '' }}>Tidak, Tanpa Potong Kuota</option>
                </select>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label for="khusus_pegawai_tetap_edit" class="font-weight-bold">Akses Pegawai Tetap</label>
                <select class="form-control" id="khusus_pegawai_tetap_edit" name="khusus_pegawai_tetap">
                    <option value="0" {{ (string)$cuti->khusus_pegawai_tetap === '0' ? 'selected' : '' }}>Semua Pegawai (Termasuk Kontrak/Honorer)</option>
                    <option value="1" {{ (string)$cuti->khusus_pegawai_tetap === '1' ? 'selected' : '' }}>Khusus Pegawai Tetap (PKWTT / PNS)</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="keterangan_edaran" class="font-weight-bold">Dasar Surat Edaran / Keterangan</label>
            <input type="text" class="form-control" id="keterangan_edaran" name="keterangan_edaran"
                value="{{ $cuti->keterangan_edaran }}" placeholder="Contoh: Sesuai Surat Edaran Rektor No. 012/SE/TSU/2026">
        </div>

        <div class="form-group mb-2">
            <label for="is_active" class="font-weight-bold">Status Aktif Master Cuti <span class="text-danger">*</span></label>
            <select class="form-control" id="is_active" name="is_active" required>
                <option value="1" {{ $cuti->is_active === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ $cuti->is_active === '0' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
    </div>
</form>

<script>
    $('#kategori_cuti_edit').on('change', function() {
        var kat = $(this).val();
        if (kat === 'khusus') {
            $('#memotong_kuota_edit').val('0');
            $('#khusus_pegawai_tetap_edit').val('1');
            $('#kategori-help-text-edit').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Otomatis diset: Tanpa potong kuota tahunan &amp; diperuntukkan bagi Pegawai Tetap.</span>');
        } else {
            $('#memotong_kuota_edit').val('1');
            $('#khusus_pegawai_tetap_edit').val('0');
            $('#kategori-help-text-edit').html('<span class="text-primary font-weight-bold"><i class="fas fa-info-circle"></i> Otomatis diset: Memotong kuota tahunan 12 hari &amp; berlaku untuk pegawai berhak.</span>');
        }
    });
</script>
