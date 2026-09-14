<div class="modal-header" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%); color: white; border: none; padding: 1.15rem 1.5rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-edit"></i>
        Edit Penyesuaian Saldo Cuti
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.saldo-cuti.update', $saldo->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4">
        {{-- INFO PEGAWAI --}}
        <div class="p-3 mb-4 d-flex align-items-center" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px);">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center mr-3" style="width: 44px; height: 44px; font-size: 1.15rem; background: linear-gradient(135deg, var(--tsu-primary-dark, #094b54) 0%, var(--tsu-primary, #0c6170) 100%); flex-shrink: 0;">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h6 class="font-weight-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                    {{ $saldo->pegawai->nama_lengkap ?? ($saldo->pegawai->nama ?? 'Pegawai') }}
                </h6>
                <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                    NIK: <strong>{{ $saldo->pegawai->nik ?? '-' }}</strong> &bull; Unit: <strong>{{ $saldo->pegawai->unit->nama_unit ?? '-' }}</strong>
                </small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Tahun Saldo <span class="text-danger">*</span>
                </label>
                <input type="number" name="tahun" class="form-control font-weight-bold" value="{{ $saldo->tahun }}" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
            </div>

            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Masa Berlaku (Expired) <span class="text-danger">*</span>
                </label>
                <input type="date" name="expired" class="form-control" value="{{ $saldo->expired ? \Carbon\Carbon::parse($saldo->expired)->format('Y-m-d') : '' }}" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Jatah Hari <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="jatah" id="edit-jatah" class="form-control font-weight-bold" value="{{ $saldo->jatah }}" min="0" max="60" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Hari</span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-4 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Terpakai <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="terpakai" id="edit-terpakai" class="form-control font-weight-bold text-primary" value="{{ $saldo->terpakai }}" min="0" max="60" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Hari</span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-4 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Sisa Cuti <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="sisa" id="edit-sisa" class="form-control font-weight-bold text-success" value="{{ $saldo->sisa }}" min="0" max="60" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Hari</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                Status Keaktifan Saldo <span class="text-danger">*</span>
            </label>
            <select name="is_active" class="form-control" style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                <option value="1" {{ $saldo->is_active == '1' ? 'selected' : '' }}>Aktif (Dapat digunakan untuk pengajuan cuti)</option>
                <option value="0" {{ $saldo->is_active == '0' ? 'selected' : '' }}>Non-Aktif / Expired</option>
            </select>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-between" style="border-top: 1px solid #f1f5f9; background: #fafafa; padding: 0.85rem 1.5rem;">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.1rem;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-create" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Otomatis hitung sisa = jatah - terpakai saat input berubah
        $('#edit-jatah, #edit-terpakai').on('input', function() {
            var jatah = parseInt($('#edit-jatah').val()) || 0;
            var terpakai = parseInt($('#edit-terpakai').val()) || 0;
            var sisa = Math.max(0, jatah - terpakai);
            $('#edit-sisa').val(sisa);
        });
    });
</script>
