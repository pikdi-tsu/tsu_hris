<div class="modal-header">
    <h5 class="modal-title">Tambah Jabatan Fungsional</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-jabatan.fungsional.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="form-group">
            <label>Nama Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jabatan" class="form-control" required placeholder="Contoh: Asisten Ahli, Lektor">
        </div>
        <div class="form-group">
            <label>Periode Jabatan (Bulan)</label>
            <input type="number" name="periode_jabatan" class="form-control" placeholder="Contoh: 24">
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
    </div>
</form>
