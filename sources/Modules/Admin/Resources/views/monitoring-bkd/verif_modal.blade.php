<div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-tasks"></i> Verifikasi Laporan BKD / LKD
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-verif-bkd" action="{{ route('admin.monitoring-bkd.store-verif', $laporan->id) }}" method="POST">
    @csrf

    <div class="modal-body p-4 bg-white">
        <div class="alert alert-light border py-3 px-3 mb-3" style="border-radius: 8px;">
            <div class="row">
                <div class="col-md-6">
                    <span class="small text-muted font-weight-bold">DOSEN:</span>
                    <div class="font-weight-bold text-dark">{{ $laporan->dosen->nama ?? '-' }}</div>
                    <small class="text-muted">NIK: {{ $laporan->dosen->nik ?? '-' }} | {{ $laporan->dosen->unit->nama_unit ?? '-' }}</small>
                </div>
                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="small text-muted font-weight-bold">PERIODE:</span>
                    <div class="font-weight-bold" style="color: #094b54;">{{ $laporan->periode->nama_periode ?? '-' }}</div>
                    <small class="text-muted">Diunggah: {{ $laporan->tanggal_upload->format('d M Y H:i') }}</small>
                </div>
            </div>
        </div>

        <div class="mb-3 text-center p-3 border rounded" style="background: #f8fafc; border-radius: 8px;">
            <a href="{{ route('admin.monitoring-bkd.stream', $laporan->id) }}" target="_blank" class="btn btn-outline-danger btn-sm font-weight-bold" style="border-radius: 6px;">
                <i class="fas fa-file-pdf mr-1"></i> Buka / Pratinjau Dokumen PDF LKD
            </a>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Status Verifikasi <span class="text-danger">*</span></label>
            <select name="status_verifikasi" class="form-control" required style="border-radius: 8px;">
                <option value="diverifikasi" {{ $laporan->status_verifikasi === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi (Lengkap &amp; Valid)</option>
                <option value="perlu_revisi" {{ $laporan->status_verifikasi === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi (Dokumen Kurang/Keliru)</option>
                <option value="draft" {{ $laporan->status_verifikasi === 'draft' ? 'selected' : '' }}>Draft (Menunggu Pemeriksaan)</option>
            </select>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan Verifikator</label>
            <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan hasil verifikasi berkas BKD dosen..." style="border-radius: 8px;">{{ $laporan->catatan }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn text-white font-weight-bold px-4" style="background-color: #094b54; border-color: #094b54; border-radius: 8px;">
            <i class="fas fa-save mr-1"></i> Simpan Hasil Verifikasi
        </button>
    </div>
</form>
