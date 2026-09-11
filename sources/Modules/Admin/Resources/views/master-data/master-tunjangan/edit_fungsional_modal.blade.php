<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2"></i> Atur Tunjangan Fungsional
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-tunjangan.fungsional.update', $tunjangan->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4" style="background-color: #fafbfc;">
        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                <div style="font-size: 0.84rem; line-height: 1.45;">
                    Perbarui nominal tunjangan fungsional tetap per bulan untuk jenjang jabatan fungsional ini.
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Jenjang Fungsional Terhubung (Dari Master Jabatan) <span class="text-danger">*</span>
            </label>
            <select name="jabatan_fungsional_id" id="edit_select_jabatan_fungsional" class="form-control select2" style="width: 100%;">
                @foreach($jabatans as $j)
                    <option value="{{ $j->id }}" {{ $tunjangan->jabatan_fungsional_id == $j->id ? 'selected' : '' }}>
                        {{ $j->nama_jabatan }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted" style="font-size: 0.75rem;">
                <i class="fas fa-link text-info mr-1"></i> Terhubung langsung dengan Master Jabatan Fungsional.
            </small>
        </div>

        <div class="row">
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Kode Singkatan <span class="text-danger">*</span>
                </label>
                <input type="text" name="kode" class="form-control font-weight-bold text-center" style="border-radius: 8px; border-color: #cbd5e1;" value="{{ $tunjangan->kode }}" placeholder="AA, L, LK, GB">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Contoh: AA, L, LK, GB</small>
            </div>
            <div class="col-md-8 form-group mb-3">
                <label class="font-weight-bold" style="font-size: 0.85rem; color: #047857;">
                    Nominal Tunjangan / Bulan (Rp) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text font-weight-bold text-white" style="background: #047857; border-color: #047857; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">Rp</span>
                    </div>
                    <input type="number" step="1000" min="0" name="nominal_tunjangan" class="form-control form-control-lg font-weight-bold" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #047857; color: #047857;" value="{{ $tunjangan->nominal_tunjangan ?: 0 }}" required placeholder="Contoh: 700000">
                </div>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Nominal tunjangan fungsional bulanan yang dibayarkan ke dosen.</small>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Keterangan / Catatan Tambahan
            </label>
            <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Opsional">{{ $tunjangan->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan Tunjangan
        </button>
    </div>
</form>

<script>
    if ($.fn.select2) {
        $('#edit_select_jabatan_fungsional').select2({
            dropdownParent: $('#modal-tunjangan'),
            width: '100%'
        });
    }
</script>
