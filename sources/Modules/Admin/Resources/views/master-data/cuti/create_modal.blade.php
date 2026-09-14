<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2 text-warning"></i> Tambah Master Cuti
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-cuti.store') }}" method="POST">
    @csrf

    <div class="modal-body p-4">
        {{-- Trigger Kategori Cuti --}}
        <div class="form-group mb-3 p-3 rounded" style="background: rgba(9, 75, 84, 0.05); border-left: 4px solid var(--tsu-primary, #094b54);">
            <label for="kategori_cuti" class="font-weight-bold mb-1 text-sm text-dark">
                <i class="fas fa-layer-group mr-1" style="color: var(--tsu-primary, #094b54);"></i> Kategori Cuti (Pembeda Sistem) <span class="text-danger">*</span>
            </label>
            <select class="form-control custom-select" id="kategori_cuti" name="kategori_cuti" required style="border-radius: 8px;">
                <option value="tahunan">Cuti Tahunan &amp; Reguler (Mengurangi Kuota 12 Hari)</option>
                <option value="khusus">Cuti Khusus (Surat Edaran SDM - Tanpa Potong Kuota)</option>
            </select>
            <small class="text-muted mt-1 d-block" id="kategori-help-text" style="font-size: 0.78rem;">
                Kategori ini secara otomatis menentukan tab tampilan di menu pengajuan cuti pegawai.
            </small>
        </div>

        <div class="form-group mb-3">
            <label for="jeniscuti" class="font-weight-bold text-sm text-dark">Nama / Jenis Cuti <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jeniscuti" name="jeniscuti"
                placeholder="Contoh: Cuti Melahirkan (Maternity) atau Cuti Tahunan" required style="border-radius: 8px;">
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="durasicuti" class="font-weight-bold text-sm text-dark">Maksimal Durasi (Hari) <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control" id="durasicuti" name="durasicuti"
                    placeholder="Contoh: 12 atau 90" required style="border-radius: 8px;">
            </div>

            <div class="col-md-6 form-group mb-3">
                <label for="minimalhari" class="font-weight-bold text-sm text-dark">Minimal Hari Pengajuan <span class="text-danger">*</span></label>
                <input type="number" min="0" class="form-control" id="minimalhari" name="minimalhari"
                    value="0" required style="border-radius: 8px;">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="memotong_kuota" class="font-weight-bold text-sm text-dark">Pengurangan Kuota 12 Hari</label>
                <select class="form-control custom-select" id="memotong_kuota" name="memotong_kuota" style="border-radius: 8px;">
                    <option value="1">Ya, Memotong Kuota Tahunan</option>
                    <option value="0">Tidak, Tanpa Potong Kuota</option>
                </select>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label for="khusus_pegawai_tetap" class="font-weight-bold text-sm text-dark">Akses Pegawai Tetap</label>
                <select class="form-control custom-select" id="khusus_pegawai_tetap" name="khusus_pegawai_tetap" style="border-radius: 8px;">
                    <option value="0">Semua Pegawai (Termasuk Kontrak/Honorer)</option>
                    <option value="1">Khusus Pegawai Tetap (PKWTT / PNS)</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-2">
            <label for="keterangan_edaran" class="font-weight-bold text-sm text-dark">Dasar Surat Edaran / Keterangan</label>
            <input type="text" class="form-control" id="keterangan_edaran" name="keterangan_edaran"
                placeholder="Contoh: Sesuai Surat Edaran Rektor No. 012/SE/TSU/2026" style="border-radius: 8px;">
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Master Cuti
        </button>
    </div>
</form>

<script>
    $('#kategori_cuti').on('change', function() {
        var kat = $(this).val();
        if (kat === 'khusus') {
            $('#memotong_kuota').val('0');
            $('#khusus_pegawai_tetap').val('1');
            $('#kategori-help-text').html('<span class="text-success font-weight-bold">Otomatis diset: Tanpa potong kuota tahunan &amp; diperuntukkan bagi Pegawai Tetap.</span>');
        } else {
            $('#memotong_kuota').val('1');
            $('#khusus_pegawai_tetap').val('0');
            $('#kategori-help-text').html('<span class="text-primary font-weight-bold">Otomatis diset: Memotong kuota tahunan 12 hari &amp; berlaku untuk pegawai berhak.</span>');
        }
    });
</script>