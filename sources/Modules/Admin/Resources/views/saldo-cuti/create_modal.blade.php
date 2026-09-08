<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-user-plus mr-2"></i> Tambah / Alokasi Saldo Cuti Manual
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.saldo-cuti.store') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        <div class="alert alert-light border shadow-sm mb-3">
            <i class="fas fa-lightbulb text-warning mr-1"></i>
            Gunakan form ini jika terdapat kebijakan khusus pimpinan untuk mengalokasikan saldo cuti perorangan (misal cuti prorata, pegawai baru dengan diskresi, dsb).
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Pilih Pegawai <span class="text-danger">*</span></label>
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

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark">Tahun Saldo <span class="text-danger">*</span></label>
                <input type="number" name="tahun" id="input-tahun-manual" class="form-control" value="{{ $currentYear }}" required>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark">Masa Berlaku Hingga (Expired) <span class="text-danger">*</span></label>
                <input type="date" name="expired" id="input-expired-manual" class="form-control" value="{{ $currentYear }}-12-31" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark">Jatah Hari Cuti <span class="text-danger">*</span></label>
                <input type="number" name="jatah" id="input-jatah-manual" class="form-control" value="12" min="1" max="60" required>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark">Sisa Saldo Awal <span class="text-danger">*</span></label>
                <input type="number" name="sisa" id="input-sisa-manual" class="form-control" value="12" min="0" max="60" required>
                <small class="text-muted">Biasanya sama dengan jatah jika belum ada cuti terpakai.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-secondary font-weight-bold shadow-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
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
