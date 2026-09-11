<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2 text-warning"></i> Edit Master Cuti
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-cuti.update', $cuti->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-calendar-check text-primary mr-1"></i> Jenis Cuti <span class="text-danger">*</span></label>
            <input type="text" name="jeniscuti" class="form-control" value="{{ $cuti->jeniscuti }}" required style="border-radius: 8px;">
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="far fa-clock text-primary mr-1"></i> Durasi Cuti (Hari) <span class="text-danger">*</span></label>
            <input type="number" min="1" name="durasicuti" class="form-control" value="{{ $cuti->durasicuti }}" required style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Maksimal batas alokasi durasi hari cuti untuk jenis ini.</small>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-hourglass-start text-primary mr-1"></i> Minimal Hari Pengajuan Sebelumnya <span class="text-danger">*</span></label>
            <input type="number" min="0" name="minimalhari" class="form-control" value="{{ $cuti->minimalhari }}" required style="border-radius: 8px;">
            <small class="text-muted d-block mt-1">Berapa hari sebelum tanggal pelaksanaan cuti permohonan harus diserahkan (H-).</small>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-sm text-dark"><i class="fas fa-toggle-on text-primary mr-1"></i> Status Aktif <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control custom-select" required style="border-radius: 8px;">
                <option value="1" {{ $cuti->is_active == '1' || $cuti->is_active == 1 ? 'selected' : '' }}>Aktif (Bisa Diajukan)</option>
                <option value="0" {{ $cuti->is_active == '0' || $cuti->is_active == 0 ? 'selected' : '' }}>Non-Aktif (Dinonaktifkan)</option>
            </select>
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
