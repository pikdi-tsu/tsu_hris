<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2 text-warning"></i> Edit Pangkat / Golongan
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-jabatan.pangkat.update', $pangkat->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-layer-group text-primary mr-1"></i> Nama Pangkat / Golongan <span class="text-danger">*</span></label>
            <input type="text" name="nama_pangkat_golongan" class="form-control" value="{{ $pangkat->nama_pangkat_golongan }}" required style="border-radius: 8px;">
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan atau deskripsi jika diperlukan (opsional)" style="border-radius: 8px;">{{ $pangkat->keterangan }}</textarea>
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
