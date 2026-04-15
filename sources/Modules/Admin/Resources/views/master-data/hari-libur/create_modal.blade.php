<form action="{{ route('admin.hari-libur.store') }}" method="POST">
    @csrf

    <div class="modal-body p-4">
        <div class="alert alert-success bg-success border-0 text-white mb-4 shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>
            <b>Tambah Libur Internal:</b> Gunakan form ini untuk menambahkan libur internal TSU atau cuti bersama yang belum ada di kalender TSU.
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark"><i class="far fa-calendar-alt text-primary mr-1"></i> Tanggal Libur <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan <span class="text-danger">*</span></label>
            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Libur Dies Natalis TSU" required>
        </div>

        <div class="form-group mb-4">
            <label class="font-weight-bold text-dark"><i class="fas fa-tags text-primary mr-1"></i> Status Libur <span class="text-danger">*</span></label>
            <select name="status_libur" class="form-control custom-select" required>
                <option value="Institusi" selected>Libur Institusi</option>
                <option value="Cuti Bersama">Cuti Bersama</option>
            </select>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-success font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Data
        </button>
    </div>
</form>
