<div class="modal-header bg-info text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-tasks mr-2"></i> Verifikasi Laporan BKD / LKD
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-verif-bkd" action="{{ route('admin.monitoring-bkd.store-verif', $laporan->id) }}" method="POST">
    @csrf

    <div class="modal-body p-4 bg-white">
        <div class="alert alert-light border py-2 px-3 mb-3">
            <div class="row">
                <div class="col-md-6">
                    <span class="small text-muted font-weight-bold">DOSEN:</span>
                    <div class="font-weight-bold text-dark">{{ $laporan->dosen->nama ?? '-' }}</div>
                    <small class="text-muted">NIK: {{ $laporan->dosen->nik ?? '-' }} | {{ $laporan->dosen->unit->nama_unit ?? '-' }}</small>
                </div>
                <div class="col-md-6 text-md-right mt-2 mt-md-0">
                    <span class="small text-muted font-weight-bold">PERIODE:</span>
                    <div class="font-weight-bold text-primary">{{ $laporan->periode->nama_periode ?? '-' }}</div>
                    <small class="text-muted">Diunggah: {{ $laporan->tanggal_upload->format('d M Y H:i') }}</small>
                </div>
            </div>
        </div>

        <div class="mb-3 text-center p-2 border rounded bg-light">
            <a href="{{ route('admin.monitoring-bkd.stream', $laporan->id) }}" target="_blank" class="btn btn-outline-danger btn-sm font-weight-bold">
                <i class="fas fa-file-pdf mr-1"></i> Buka / Pratinjau Dokumen PDF LKD
            </a>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Status Verifikasi <span class="text-danger">*</span></label>
            <select name="status_verifikasi" class="form-control" required>
                <option value="diverifikasi" {{ $laporan->status_verifikasi === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi (Lengkap &amp; Valid)</option>
                <option value="perlu_revisi" {{ $laporan->status_verifikasi === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi (Dokumen Kurang/Keliru)</option>
                <option value="draft" {{ $laporan->status_verifikasi === 'draft' ? 'selected' : '' }}>Draft (Menunggu Pemeriksaan)</option>
            </select>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan Verifikator</label>
            <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan hasil verifikasi berkas BKD dosen...">{{ $laporan->catatan }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-info font-weight-bold px-4">
            <i class="fas fa-save mr-1"></i> Simpan Hasil Verifikasi
        </button>
    </div>
</form>
