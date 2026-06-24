<div class="modal-header">
    <h5 class="modal-title">Edit Jabatan Struktural</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-jabatan.struktural.update', $struktural->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label>Nama Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jabatan" class="form-control" value="{{ $struktural->nama_jabatan }}" required>
        </div>
        <div class="form-group">
            <label>Periode Jabatan (Bulan)</label>
            <input type="number" name="periode_jabatan" class="form-control" value="{{ $struktural->periode_jabatan }}">
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3">{{ $struktural->keterangan }}</textarea>
        </div>
        <div class="form-group">
            <label>Wajib Pilih Unit Penugasan? <span class="text-danger">*</span></label>
            <select name="is_unit_specific" class="form-control" required>
                <option value="Y" {{ $struktural->is_unit_specific == 'Y' ? 'selected' : '' }}>Ya (Wajib pilih unit, misal: Kepala Biro)</option>
                <option value="N" {{ $struktural->is_unit_specific == 'N' ? 'selected' : '' }}>Tidak (Tingkat Universitas, misal: Rektor)</option>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
</form>
