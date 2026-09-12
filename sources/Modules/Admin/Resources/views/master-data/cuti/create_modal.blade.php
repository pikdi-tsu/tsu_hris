<form action="{{ route('admin.master-cuti.store') }}" method="POST">
    @csrf
    <div class="modal-header bg-primary">
        <h5 class="modal-title text-white"><i class="fas fa-plus mr-1"></i> Tambah Master Cuti</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4">
        {{-- Trigger Kategori Cuti --}}
        <div class="form-group mb-3 p-3 bg-light rounded border border-primary">
            <label for="kategori_cuti" class="font-weight-bold text-primary mb-1">
                <i class="fas fa-layer-group mr-1"></i> Kategori Cuti (Pembeda Sistem) <span class="text-danger">*</span>
            </label>
            <select class="form-control font-weight-bold" id="kategori_cuti" name="kategori_cuti" required>
                <option value="tahunan">Cuti Tahunan &amp; Reguler (Mengurangi Kuota 12 Hari)</option>
                <option value="khusus">Cuti Khusus (Surat Edaran SDM - Tanpa Potong Kuota)</option>
            </select>
            <small class="text-muted mt-1 d-block" id="kategori-help-text">
                Kategori ini secara otomatis menentukan tab tampilan di menu pengajuan cuti pegawai.
            </small>
        </div>

        <div class="form-group mb-3">
            <label for="jeniscuti" class="font-weight-bold">Nama / Jenis Cuti <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jeniscuti" name="jeniscuti"
                placeholder="Contoh: Cuti Melahirkan (Maternity) atau Cuti Tahunan" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="durasicuti" class="font-weight-bold">Maksimal Durasi (Hari) <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control" id="durasicuti" name="durasicuti"
                    placeholder="Contoh: 12 atau 90" required>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label for="minimalhari" class="font-weight-bold">Minimal Hari Pengajuan <span class="text-danger">*</span></label>
                <input type="number" min="0" class="form-control" id="minimalhari" name="minimalhari"
                    value="0" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="memotong_kuota" class="font-weight-bold">Pengurangan Kuota 12 Hari</label>
                <select class="form-control" id="memotong_kuota" name="memotong_kuota">
                    <option value="1">Ya, Memotong Kuota Tahunan</option>
                    <option value="0">Tidak, Tanpa Potong Kuota</option>
                </select>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label for="khusus_pegawai_tetap" class="font-weight-bold">Akses Pegawai Tetap</label>
                <select class="form-control" id="khusus_pegawai_tetap" name="khusus_pegawai_tetap">
                    <option value="0">Semua Pegawai (Termasuk Kontrak/Honorer)</option>
                    <option value="1">Khusus Pegawai Tetap (PKWTT / PNS)</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-2">
            <label for="keterangan_edaran" class="font-weight-bold">Dasar Surat Edaran / Keterangan</label>
            <input type="text" class="form-control" id="keterangan_edaran" name="keterangan_edaran"
                placeholder="Contoh: Sesuai Surat Edaran Rektor No. 012/SE/TSU/2026">
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Master Cuti</button>
    </div>
</form>

<script>
    $('#kategori_cuti').on('change', function() {
        var kat = $(this).val();
        if (kat === 'khusus') {
            $('#memotong_kuota').val('0');
            $('#khusus_pegawai_tetap').val('1');
            $('#kategori-help-text').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Otomatis diset: Tanpa potong kuota tahunan &amp; diperuntukkan bagi Pegawai Tetap.</span>');
        } else {
            $('#memotong_kuota').val('1');
            $('#khusus_pegawai_tetap').val('0');
            $('#kategori-help-text').html('<span class="text-primary font-weight-bold"><i class="fas fa-info-circle"></i> Otomatis diset: Memotong kuota tahunan 12 hari &amp; berlaku untuk pegawai berhak.</span>');
        }
    });
</script>