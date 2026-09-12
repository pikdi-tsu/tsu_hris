<div class="modal-header bg-danger text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-times-circle mr-2"></i> Tolak Permohonan Surat: {{ $surat->nomor_tiket }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-admin-tolak-surat" action="{{ route('admin.request-surat.admin-store-tolak', $surat->id) }}" method="POST">
    @csrf

    <div class="modal-body p-4 bg-white">
        <div class="alert alert-light border py-2 px-3 mb-3">
            <div class="small text-muted font-weight-bold">PEMOHON:</div>
            <strong>{{ $surat->pegawai->nama ?? '-' }}</strong> ({{ $surat->pegawai->nik ?? '-' }}) — 
            <span class="text-danger font-weight-bold">{{ $surat->jenis_surat }}</span>
        </div>

        <div class="alert alert-warning border-0 small">
            <i class="fas fa-exclamation-triangle mr-1"></i> Permohonan yang ditolak akan diarsipkan dan pemohon akan melihat alasan penolakan ini.
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea name="catatan_petugas" class="form-control" rows="4" placeholder="Tuliskan secara jelas alasan penolakan atau dokumen persyaratan yang belum lengkap..." required minlength="5"></textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-danger px-4 font-weight-bold">
            <i class="fas fa-ban mr-1"></i> Tolak Permohonan
        </button>
    </div>
</form>
