<form action="{{ route('admin.master-cuti.store') }}" method="POST">
    @csrf
    <div class="modal-header bg-primary">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Master Cuti</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="jeniscuti">Jenis Cuti <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jeniscuti" name="jeniscuti"
                placeholder="Contoh: Cuti Reguler" required>
        </div>

        <div class="form-group mb-3">
            <label for="durasicuti">Durasi Hari Cuti</label>
            <input type="number" min="1" class="form-control" id="durasicuti" name="durasicuti"
                placeholder="12">
        </div>

        <div class="form-group mb-3">
            <label for="minimalhari">Minimal Hari Pengajuan</label>
            <input type="number" min="1" class="form-control" id="minimalhari" name="minimalhari"
                placeholder="15">
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
    </div>
</form>
