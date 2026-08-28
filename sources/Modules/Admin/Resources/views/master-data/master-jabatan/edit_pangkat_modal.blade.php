<div class="modal-header">
    <h5 class="modal-title">Edit Pangkat / Golongan</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-jabatan.pangkat.update', $pangkat->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label>Nama Pangkat / Golongan <span class="text-danger">*</span></label>
            <input type="text" name="nama_pangkat_golongan" class="form-control" value="{{ $pangkat->nama_pangkat_golongan }}" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3">{{ $pangkat->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
</form>
