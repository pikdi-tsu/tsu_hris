<form action="{{ route('admin.master-lembur.update', $lembur->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Master Lembur</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="jenislembur">Jenis Lembur <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jenislembur" name="jenislembur" value="{{ $lembur->jenislembur }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3">{{ $lembur->keterangan }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="is_active">Status Aktif <span class="text-danger">*</span></label>
            <select class="form-control" id="is_active" name="is_active" required>
                <option value="1" {{ $lembur->is_active === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ $lembur->is_active === '0' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
</form>
