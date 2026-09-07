<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-edit mr-2"></i> Edit Master Gaji Pokok: Golongan {{ $gapok->golongan }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-gaji-pokok.update', $gapok->id) }}" method="POST" id="formGajiPokok">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="font-weight-bold text-dark">Golongan & Pangkat <span class="text-danger">*</span></label>
                <input type="text" name="golongan" class="form-control font-weight-bold" value="{{ $gapok->golongan }}" placeholder="Contoh: III/a, IV/b" required>
                <small class="text-muted">Format baku: I/a s/d IV/e</small>
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold text-success">Gaji Pokok 100% (Penuh) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                    <input type="number" name="gaji_pokok_100" id="inputGapok100" class="form-control font-weight-bold" value="{{ intval($gapok->gaji_pokok_100) }}" placeholder="3037000" min="0" required>
                </div>
                <small class="text-muted">Nominal gaji penuh (100%)</small>
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold text-dark">Gaji Pokok 80% (Masa Percobaan)</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Rp</span></div>
                    <input type="number" name="gaji_pokok_80" id="inputGapok80" class="form-control" value="{{ intval($gapok->gaji_pokok_80) }}" placeholder="80%" min="0">
                </div>
                <small class="text-muted">Nominal gaji 80%</small>
            </div>
        </div>

        <div class="card bg-light border p-3 mt-2 mb-3">
            <h6 class="font-weight-bold text-secondary mb-2" style="font-size: 9pt;">
                <i class="fas fa-layer-group text-info mr-1"></i> Jenjang Berkala Kenaikan Masa Kerja (Opsional)
            </h6>
            <div class="row">
                <div class="col-md-4 col-6 form-group mb-2">
                    <label class="small font-weight-bold">Tahun Ke-2 (Rp)</label>
                    <input type="number" name="tahun_2" class="form-control form-control-sm" value="{{ intval($gapok->tahun_2) }}" min="0">
                </div>
                <div class="col-md-4 col-6 form-group mb-2">
                    <label class="small font-weight-bold">Tahun Ke-4 (Rp)</label>
                    <input type="number" name="tahun_4" class="form-control form-control-sm" value="{{ intval($gapok->tahun_4) }}" min="0">
                </div>
                <div class="col-md-4 col-6 form-group mb-2">
                    <label class="small font-weight-bold">Tahun Ke-6 (Rp)</label>
                    <input type="number" name="tahun_6" class="form-control form-control-sm" value="{{ intval($gapok->tahun_6) }}" min="0">
                </div>
                <div class="col-md-4 col-6 form-group mb-2">
                    <label class="small font-weight-bold">Tahun Ke-8 (Rp)</label>
                    <input type="number" name="tahun_8" class="form-control form-control-sm" value="{{ intval($gapok->tahun_8) }}" min="0">
                </div>
                <div class="col-md-4 col-6 form-group mb-2">
                    <label class="small font-weight-bold">Tahun Ke-10 (Rp)</label>
                    <input type="number" name="tahun_10" class="form-control form-control-sm" value="{{ intval($gapok->tahun_10) }}" min="0">
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Catatan regulasi atau penetapan surat keputusan...">{{ $gapok->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-4">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
