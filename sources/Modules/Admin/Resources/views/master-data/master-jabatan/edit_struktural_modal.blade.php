<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2 text-warning"></i> Edit Jabatan Struktural
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-jabatan.struktural.update', $struktural->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-sitemap text-primary mr-1"></i> Nama Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jabatan" class="form-control" value="{{ $struktural->nama_jabatan }}" required style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="far fa-clock text-primary mr-1"></i> Periode Masa Jabatan (Bulan)</label>
            <input type="number" name="periode_jabatan" class="form-control" value="{{ $struktural->periode_jabatan }}" placeholder="Contoh: 48" style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Kosongkan jika tidak ada batas periode masa bakti tetap.</small>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-building text-primary mr-1"></i> Wajib Pilih Unit Penugasan? <span class="text-danger">*</span></label>
            <select name="is_unit_specific" class="form-control custom-select" required style="border-radius: 8px;">
                <option value="Y" {{ $struktural->is_unit_specific == 'Y' ? 'selected' : '' }}>Ya (Wajib pilih unit kerja, misal: Kepala Biro, Kaprodi)</option>
                <option value="N" {{ $struktural->is_unit_specific == 'N' ? 'selected' : '' }}>Tidak (Tingkat Universitas, misal: Rektor, Warek)</option>
            </select>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan atau deskripsi fungsi jika diperlukan (opsional)" style="border-radius: 8px;">{{ $struktural->keterangan }}</textarea>
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
