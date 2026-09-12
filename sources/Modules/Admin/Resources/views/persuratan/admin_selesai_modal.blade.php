<div class="modal-header bg-success text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-check-circle mr-2"></i> Terbitkan & Selesaikan Surat: {{ $surat->nomor_tiket }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-admin-selesai-surat" action="{{ route('admin.request-surat.admin-store-selesai', $surat->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="modal-body p-4 bg-white">
        <div class="alert alert-light border py-2 px-3 mb-3">
            <div class="small text-muted font-weight-bold">PEMOHON:</div>
            <strong>{{ $surat->pegawai->nama ?? '-' }}</strong> ({{ $surat->pegawai->nik ?? '-' }}) — 
            <span class="text-primary font-weight-bold">{{ $surat->jenis_surat }}</span>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Nomor Surat Resmi SDM / Keluar <span class="text-danger">*</span></label>
            <input type="text" name="nomor_surat_keluar" class="form-control" placeholder="Contoh: 154/TSU/SDM-KET/IX/2026" required>
            <small class="text-muted">Nomor surat resmi yang tercetak pada dokumen fisik/digital.</small>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Unggah Berkas Surat Resmi Bertanda Tangan / Cap (PDF) <span class="text-danger">*</span></label>
            <input type="file" name="file_surat_hasil" class="form-control-file" accept=".pdf" required>
            <small class="text-muted d-block mt-1">Berkas PDF ini akan dapat langsung diunduh oleh dosen/tendik bersangkutan. Maksimal 10 MB.</small>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan Petugas SDM (Opsional)</label>
            <textarea name="catatan_petugas" class="form-control" rows="2" placeholder="Contoh: Surat asli bertanda tangan basah dapat diambil di Ruang SDM Kampus A pada jam kerja."></textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-success px-4 font-weight-bold">
            <i class="fas fa-check-circle mr-1"></i> Terbitkan & Selesaikan
        </button>
    </div>
</form>
