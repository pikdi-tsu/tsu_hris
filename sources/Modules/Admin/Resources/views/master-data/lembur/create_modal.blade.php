<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2 text-warning"></i> Tambah Master Lembur
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-lembur.store') }}" method="POST">
    @csrf
    
    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark" for="jenislembur">
                <i class="fas fa-business-time text-primary mr-1"></i> Jenis Lembur <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="jenislembur" name="jenislembur" placeholder="Contoh: Lembur Khusus Event / Weekend" required style="border-radius: 8px;">
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark" for="keterangan">
                <i class="fas fa-align-left text-primary mr-1"></i> Keterangan Ketentuan
            </label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Jelaskan deskripsi atau ketentuan pelaksanaan lembur ini..." style="border-radius: 8px;"></textarea>
        </div>
    </div>
    
    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Data
        </button>
    </div>
</form>
