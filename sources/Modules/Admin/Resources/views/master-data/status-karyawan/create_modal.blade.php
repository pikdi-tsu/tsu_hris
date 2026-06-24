<div class="modal-header bg-light">
    <h5 class="modal-title font-weight-bold">Tambah Status Karyawan</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-status-karyawan.store') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold">Nama Status <span class="text-danger">*</span></label>
            <input type="text" name="nama_status" class="form-control" placeholder="Contoh: TETAP, KONTRAK, HONORER" required>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional..."></textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan
        </button>
    </div>
</form>
