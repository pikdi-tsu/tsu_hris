<form action="{{ route('admin.master-lembur.store') }}" method="POST">
    @csrf
    <div class="modal-header bg-primary">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Master Lembur</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="jenislembur">Jenis Lembur <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jenislembur" name="jenislembur" placeholder="Contoh: Lembur Reguler" required>
        </div>

        <div class="form-group mb-3">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Lembur pada hari kerja biasa"></textarea>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
    </div>
</form>
