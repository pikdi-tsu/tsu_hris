<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2 text-warning"></i> Tambah Master Izin
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-izin.store') }}" method="POST">
    @csrf

    <div class="modal-body p-4">
        <div class="p-3 rounded mb-3" style="background: rgba(9, 75, 84, 0.05); border-left: 4px solid var(--tsu-primary, #094b54);">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="color: var(--tsu-primary, #094b54); font-size: 1rem;"></i>
                <div class="text-sm text-dark">
                    <b>Master Izin:</b> Digunakan sebagai opsi kategori permohonan izin tidak masuk kerja atau dinas luar bagi dosen dan tendik.
                </div>
            </div>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-envelope-open-text text-primary mr-1"></i> Jenis Izin <span class="text-danger">*</span></label>
            <input type="text" name="jenisizin" class="form-control" required placeholder="Contoh: Izin Sakit, Izin Terlambat, Izin Urusan Keluarga" style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Nama kategori izin yang akan tampil pada formulir pengajuan pegawai.</small>
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Data
        </button>
    </div>
</form>
