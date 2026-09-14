<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2"></i> Edit Master Gaji Pokok: Golongan {{ $gapok->golongan }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-gaji-pokok.update', $gapok->id) }}" method="POST" id="formGajiPokok">
    @csrf
    @method('PUT')
    <div class="modal-body p-4" style="background-color: #fafbfc;">
        {{-- Helper Guide Alert --}}
        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                <div style="font-size: 0.84rem; line-height: 1.45;">
                    Perubahan nominal gaji pokok pada golongan ini akan berlaku pada perhitungan gaji dan tunjangan periode penggajian berikutnya.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Golongan & Pangkat <span class="text-danger">*</span>
                </label>
                <input type="text" name="golongan" class="form-control font-weight-bold" style="border-radius: 8px; border-color: #cbd5e1;" value="{{ $gapok->golongan }}" placeholder="Contoh: III/a, IV/b" required>
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
                    <input type="number" name="gaji_pokok_100" id="inputGapok100" class="form-control font-weight-bold text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" value="{{ intval($gapok->gaji_pokok_100) }}" placeholder="3037000" min="0" required>
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
                    <input type="number" name="gaji_pokok_80" id="inputGapok80" class="form-control text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" value="{{ intval($gapok->gaji_pokok_80) }}" placeholder="80%" min="0">
                </div>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Nominal gaji masa percobaan</small>
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
                    Nominal kenaikan berkala setiap jenjang masa kerja.
                </p>
                <div class="row">
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-2 (Rp)</label>
                        <input type="number" name="tahun_2" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" value="{{ intval($gapok->tahun_2) }}" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-4 (Rp)</label>
                        <input type="number" name="tahun_4" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" value="{{ intval($gapok->tahun_4) }}" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-6 (Rp)</label>
                        <input type="number" name="tahun_6" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" value="{{ intval($gapok->tahun_6) }}" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-8 (Rp)</label>
                        <input type="number" name="tahun_8" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" value="{{ intval($gapok->tahun_8) }}" min="0">
                    </div>
                    <div class="col-md-4 col-6 form-group mb-2">
                        <label class="font-weight-bold text-dark" style="font-size: 0.78rem;">Tahun Ke-10 (Rp)</label>
                        <input type="number" name="tahun_10" class="form-control form-control-sm" style="border-radius: 6px; border-color: #cbd5e1;" value="{{ intval($gapok->tahun_10) }}" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Keterangan Tambahan
            </label>
            <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Catatan regulasi atau penetapan surat keputusan...">{{ $gapok->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
