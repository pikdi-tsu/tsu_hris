<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-edit mr-2"></i> Edit Penyesuaian Saldo Cuti
    </h5>
    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.saldo-cuti.update', $saldo->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4">
        {{-- INFO PEGAWAI --}}
        <div class="card border-0 bg-light shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mr-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-dark mb-0">{{ $saldo->pegawai->nama_lengkap ?? ($saldo->pegawai->nama ?? 'Pegawai') }}</h6>
                        <small class="text-muted d-block">
                            NIK: {{ $saldo->pegawai->nik ?? '-' }} &bull; Unit: {{ $saldo->pegawai->unit->nama_unit ?? '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark">Tahun Saldo <span class="text-danger">*</span></label>
                <input type="number" name="tahun" class="form-control" value="{{ $saldo->tahun }}" required>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark">Masa Berlaku (Expired) <span class="text-danger">*</span></label>
                <input type="date" name="expired" class="form-control" value="{{ $saldo->expired ? \Carbon\Carbon::parse($saldo->expired)->format('Y-m-d') : '' }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark">Jatah Hari <span class="text-danger">*</span></label>
                <input type="number" name="jatah" id="edit-jatah" class="form-control font-weight-bold" value="{{ $saldo->jatah }}" min="0" max="60" required>
            </div>

            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark">Terpakai (Hari) <span class="text-danger">*</span></label>
                <input type="number" name="terpakai" id="edit-terpakai" class="form-control font-weight-bold" value="{{ $saldo->terpakai }}" min="0" max="60" required>
            </div>

            <div class="col-md-4 form-group mb-3">
                <label class="font-weight-bold text-dark">Sisa Cuti (Hari) <span class="text-danger">*</span></label>
                <input type="number" name="sisa" id="edit-sisa" class="form-control font-weight-bold text-success" value="{{ $saldo->sisa }}" min="0" max="60" required>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Status Keaktifan Saldo <span class="text-danger">*</span></label>
            <select name="is_active" class="form-control">
                <option value="1" {{ $saldo->is_active == '1' ? 'selected' : '' }}>Aktif (Dapat digunakan untuk pengajuan cuti)</option>
                <option value="0" {{ $saldo->is_active == '0' ? 'selected' : '' }}>Non-Aktif / Expired</option>
            </select>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-secondary font-weight-bold shadow-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
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
