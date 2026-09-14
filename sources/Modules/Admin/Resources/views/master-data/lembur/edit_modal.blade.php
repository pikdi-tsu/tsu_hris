<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2 text-warning"></i> Edit Master Lembur: {{ $lembur->jenislembur }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-lembur.update', $lembur->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark" for="jenislembur">
                <i class="fas fa-business-time text-primary mr-1"></i> Jenis Lembur <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="jenislembur" name="jenislembur" value="{{ $lembur->jenislembur }}" required style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark" for="keterangan">
                <i class="fas fa-align-left text-primary mr-1"></i> Keterangan Ketentuan
            </label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" style="border-radius: 8px;">{{ $lembur->keterangan }}</textarea>
        </div>

        <hr class="my-3" style="border-top: 1px dashed #e2e8f0;">

        <div class="form-group mb-0 p-3 rounded" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-left: 4px solid #10b981;">
            <label class="font-weight-bold text-sm text-dark mb-1" for="is_active">
                <i class="fas fa-toggle-on text-success mr-1"></i> Status Ketersediaan <span class="text-danger">*</span>
            </label>
            <select class="form-control custom-select" id="is_active" name="is_active" required style="border-radius: 8px;">
                <option value="1" {{ $lembur->is_active === '1' ? 'selected' : '' }}>✅ Aktif (Dapat Dipilih Karyawan)</option>
                <option value="0" {{ $lembur->is_active === '0' ? 'selected' : '' }}>❌ Non-Aktif (Ditutup dari Opsi Pengajuan)</option>
            </select>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-info-circle text-info mr-1"></i> Jenis lembur non-aktif tidak akan muncul di form pengajuan lembur mandiri oleh staf/dosen.
            </small>
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
