<form action="{{ route('admin.master-izin.store') }}" method="POST">
    @csrf
    <div class="modal-header bg-primary">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Master Izin</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="jenisizin">Jenis Izin <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jenisizin" name="jenisizin"
                placeholder="Contoh: Izin Reguler" required>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
    </div>
</form>
