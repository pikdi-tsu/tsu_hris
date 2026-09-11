<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2"></i> Tambah Master Gaji Pokok
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-gaji-pokok.store') }}" method="POST" id="formGajiPokok">
    @csrf
    <div class="modal-body p-4" style="background-color: #fafbfc;">
        {{-- Helper Guide Alert --}}
        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                <div style="font-size: 0.84rem; line-height: 1.45;">
                    Gaji Pokok 100% adalah gaji pokok penuh pegawai tetap. Gaji Pokok 80% berlaku untuk masa percobaan. Jenjang berkala masa kerja akan dihitung otomatis jika dikosongkan.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Golongan & Pangkat <span class="text-danger">*</span>
                </label>
                <input type="text" name="golongan" class="form-control font-weight-bold" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Contoh: III/a, IV/b" required>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format baku: I/a s/d IV/e</small>
            </div>
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold" style="font-size: 0.85rem; color: #047857;">
                    Gaji Pokok 100% (Penuh) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #cbd5e1; border-top-left-radius: 8px; border-bottom-left-radius: 8px; color: #094b54;">Rp</span>
                    </div>
                    <input type="number" name="gaji_pokok_100" id="inputGapok100" class="form-control font-weight-bold text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" placeholder="3037000" min="0" required>
                </div>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Nominal gaji penuh pegawai tetap</small>
            </div>
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Gaji Pokok 80% (Percobaan)
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #cbd5e1; border-top-left-radius: 8px; border-bottom-left-radius: 8px; color: #094b54;">Rp</span>
                    </div>
                    <input type="number" name="gaji_pokok_80" id="inputGapok80" class="form-control text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" placeholder="Otomatis 80%" min="0">
                </div>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Kosongkan untuk otomatis 80%</small>
            </div>
        </div>

        {{-- Jenjang Berkala Card --}}
        <div class="card border mb-3 shadow-none" style="background-color: #ffffff; border-radius: 10px; border-color: #e2e8f0 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-layer-group text-info mr-2"></i>
                    <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.85rem;">
                        Jenjang Berkala Kenaikan Masa Kerja (Opsional)
                    </h6>
                </div>
                <p class="text-muted mb-3" style="font-size: 0.75rem;">
                    Kosongkan kolom di bawah untuk menggunakan kalkulasi persentase otomatis standar perguruan tinggi.
                </p>
                <div class="row">
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-2 (Rp)</label>
                        <input type="number" name="tahun_2" id="inputTahun2" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="Otomatis jika kosong" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-4 (Rp)</label>
                        <input type="number" name="tahun_4" id="inputTahun4" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="Otomatis jika kosong" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-6 (Rp)</label>
                        <input type="number" name="tahun_6" id="inputTahun6" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="Otomatis jika kosong" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-8 (Rp)</label>
                        <input type="number" name="tahun_8" id="inputTahun8" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="Otomatis jika kosong" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-10 (Rp)</label>
                        <input type="number" name="tahun_10" id="inputTahun10" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" placeholder="Otomatis jika kosong" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Keterangan Tambahan
            </label>
            <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Catatan regulasi, penetapan SK Rektor, atau dasar penetapan gaji pokok..."></textarea>
        </div>
    </div>
    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-check mr-1"></i> Simpan Matriks Gaji
        </button>
    </div>
</form>

<script>
    $('#inputGapok100').on('input', function() {
        var val = parseFloat($(this).val()) || 0;
        if (val > 0) {
            if (!$('#inputGapok80').val()) {
                $('#inputGapok80').attr('placeholder', Math.round(val * 0.8));
            }
            if (!$('#inputTahun2').val()) $('#inputTahun2').attr('placeholder', Math.round(val * 1.1));
            if (!$('#inputTahun4').val()) $('#inputTahun4').attr('placeholder', Math.round(val * 1.21));
            if (!$('#inputTahun6').val()) $('#inputTahun6').attr('placeholder', Math.round(val * 1.331));
            if (!$('#inputTahun8').val()) $('#inputTahun8').attr('placeholder', Math.round(val * 1.4641));
            if (!$('#inputTahun10').val()) $('#inputTahun10').attr('placeholder', Math.round(val * 1.6105));
        }
    });
</script>
