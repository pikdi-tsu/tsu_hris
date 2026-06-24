<form action="{{ route('admin.master-cuti.update', $cuti->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Master Cuti</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="jeniscuti">Jenis Lembur <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="jeniscuti" name="jeniscuti" value="{{ $cuti->jeniscuti }}"
                required>
        </div>

        <div class="form-group mb-3">
            <label for="durasicuti">Durasi Hari Cuti<span class="text-danger">*</span></label>
            <input type="number" min="0" class="form-control" id="durasicuti" name="durasicuti" placeholder="12"
                value="{{ $cuti->durasicuti }}">
        </div>

        <div class="form-group mb-3">
            <label for="minimalhari">Minimal Hari Pengajuan<span class="text-danger">*</span></label>
            <input type="number" min="0" class="form-control" id="minimalhari" name="minimalhari"
                placeholder="15" value="{{ $cuti->minimalhari }}">
        </div>

        <div class="form-group mb-3">
            <label for="is_active">Status Aktif <span class="text-danger">*</span></label>
            <select class="form-control" id="is_active" name="is_active" required>
                <option value="1" {{ $cuti->is_active === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ $cuti->is_active === '0' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
</form>
