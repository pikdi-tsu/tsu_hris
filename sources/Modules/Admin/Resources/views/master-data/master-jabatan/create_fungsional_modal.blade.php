<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-plus-circle mr-2 text-warning"></i> Tambah Jabatan Fungsional
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-jabatan.fungsional.store') }}" method="POST">
    @csrf

    <div class="modal-body p-4">
        <div class="p-3 rounded mb-3" style="background: rgba(9, 75, 84, 0.05); border-left: 4px solid var(--tsu-primary, #094b54);">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="color: var(--tsu-primary, #094b54); font-size: 1rem;"></i>
                <div class="text-sm text-dark">
                    <b>Jabatan Fungsional Akademik (JFA):</b> Mengatur jenjang fungsional dosen/akademisi (misal: Tenaga Pendidik, Asisten Ahli, Lektor, Lektor Kepala, Guru Besar).
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-graduation-cap text-primary mr-1"></i> Nama Jabatan Fungsional <span class="text-danger">*</span></label>
            <input type="text" name="nama_jabatan" class="form-control" required placeholder="Contoh: Tenaga Pengajar, Asisten Ahli, Lektor" style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="far fa-clock text-primary mr-1"></i> Periode Kenaikan / Reviu (Bulan)</label>
            <input type="number" name="periode_jabatan" class="form-control" placeholder="Contoh: 24 (untuk 2 tahun)" style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Kosongkan jika tidak ada batas periode tetap.</small>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-align-left text-primary mr-1"></i> Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan atau deskripsi jika diperlukan (opsional)" style="border-radius: 8px;"></textarea>
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
