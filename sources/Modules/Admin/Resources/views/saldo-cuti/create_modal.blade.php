<div class="modal-header" style="background: linear-gradient(135deg, var(--tsu-primary-dark, #094b54) 0%, var(--tsu-primary, #0c6170) 100%); color: white; border: none; padding: 1.15rem 1.5rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-user-plus"></i>
        Tambah / Alokasi Saldo Cuti Manual
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.saldo-cuti.store') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        <div class="mb-3" style="background: #eef9fa; border: 1px solid var(--tsu-primary-light, #cce6e9); border-left: 4px solid var(--tsu-primary, #094b54); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.83rem; color: var(--tsu-primary-dark, #094b54);">
            <i class="fas fa-lightbulb text-warning mr-1"></i>
            Gunakan formulir ini jika terdapat kebijakan khusus pimpinan untuk mengalokasikan saldo cuti perorangan (misal cuti prorata, pegawai baru dengan diskresi, penyesuaian khusus, dsb).
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                Pilih Pegawai <span class="text-danger">*</span>
            </label>
            <select name="id_user" class="form-control select2-manual" required style="width: 100%;">
                <option value="">-- Pilih Pegawai --</option>
                @foreach ($pegawais as $p)
                    @php
                        $tenure = \App\Services\SaldoCutiService::getMasaKerjaTahun($p, $currentYear);
                        $isEligible = \App\Services\SaldoCutiService::isBerhakCutiTahunan($p, $currentYear);
                        $statusMasaKerja = $isEligible ? "Masa Kerja: {$tenure} Thn (Berhak)" : "Masa Kerja: {$tenure} Thn (< 2 Thn)";
                    @endphp
                    <option value="{{ $p->id }}">
                        {{ $p->nama_lengkap ?? $p->nama }} (NIK: {{ $p->nik ?? '-' }}) - [{{ $statusMasaKerja }}]
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Tahun Saldo <span class="text-danger">*</span>
                </label>
                <input type="number" name="tahun" id="input-tahun-manual" class="form-control" value="{{ $currentYear }}" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
            </div>

            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Masa Berlaku (Expired) <span class="text-danger">*</span>
                </label>
                <input type="date" name="expired" id="input-expired-manual" class="form-control" value="{{ $currentYear }}-12-31" required style="border-radius: var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Jatah Hari Cuti <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="jatah" id="input-jatah-manual" class="form-control" value="12" min="1" max="60" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Hari</span>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-6 mb-3">
                <label class="font-weight-600 mb-1" style="font-size: 0.83rem; color: #334155;">
                    Sisa Saldo Awal <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="sisa" id="input-sisa-manual" class="form-control" value="12" min="0" max="60" required style="border-radius: var(--tsu-radius, 8px) 0 0 var(--tsu-radius, 8px); height: 38px; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <span class="input-group-text" style="background: #f1f5f9; border-color: #ced4da; font-size: 0.8rem; font-weight: 600; border-radius: 0 var(--tsu-radius, 8px) var(--tsu-radius, 8px) 0;">Hari</span>
                    </div>
                </div>
                <small class="text-muted mt-1 d-block" style="font-size: 0.74rem;">Biasanya sama dengan jatah jika belum ada cuti terpakai.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-between" style="border-top: 1px solid #f1f5f9; background: #fafafa; padding: 0.85rem 1.5rem;">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.1rem;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-create" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="fas fa-save mr-1"></i> Simpan Saldo
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('.select2-manual').select2({
            dropdownParent: $('#modal-saldo-global'),
            placeholder: '-- Cari Pegawai Berdasarkan Nama/NIK --'
        });

        $('#input-tahun-manual').on('input', function() {
            var val = $(this).val();
            if (val && val.length === 4) {
                $('#input-expired-manual').val(val + '-12-31');
            }
        });

        $('#input-jatah-manual').on('input', function() {
            $('#input-sisa-manual').val($(this).val());
        });
    });
</script>
