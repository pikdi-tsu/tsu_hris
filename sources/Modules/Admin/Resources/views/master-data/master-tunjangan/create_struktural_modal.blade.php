<div class="modal-header bg-primary text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-plus-circle mr-2"></i> Tambah Tunjangan Jabatan Struktural
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-tunjangan.struktural.store') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        <div class="form-group">
            <label class="font-weight-bold">Pilih Jabatan Struktural (Dari Master Jabatan) <span class="text-danger">*</span></label>
            <select name="jabatan_struktural_id" id="select_jabatan_struktural" class="form-control select2" required style="width: 100%;">
                <option value="">-- Pilih Jabatan Struktural --</option>
                @foreach($jabatans as $j)
                    @php $alreadySet = in_array($j->id, $existingIds); @endphp
                    <option value="{{ $j->id }}" {{ $alreadySet ? 'disabled' : '' }}>
                        {{ $j->nama_jabatan }} {{ $alreadySet ? '(Sudah Diatur)' : '' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Daftar jabatan diambil otomatis dari Master Jabatan Struktural.</small>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Nominal Dasar 100% (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light">Rp</span>
                    </div>
                    <input type="number" step="1000" min="0" id="create_nominal_dasar" name="nominal_dasar" class="form-control font-weight-bold" value="0" required>
                </div>
                <small class="text-muted">Nominal standar penuh (100%)</small>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Persen Bayar (%) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" step="0.1" min="0" max="100" id="create_persen_bayar" name="persen_bayar" class="form-control font-weight-bold" value="70" required>
                    <div class="input-group-append">
                        <span class="input-group-text bg-light">%</span>
                    </div>
                </div>
                <small class="text-muted">Contoh: 100, 70, 55, 45, 35</small>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Nominal Tunjangan Dibayarkan / Bulan (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text font-weight-bold bg-success text-white">Rp</span>
                </div>
                <input type="number" step="1" min="0" id="create_nominal_tunjangan" name="nominal_tunjangan" class="form-control form-control-lg font-weight-bold text-success" value="0" required placeholder="Contoh: 882000">
            </div>
            <small class="text-muted">Nominal final per bulan yang otomatis masuk ke perhitungan payroll karyawan.</small>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold">Keterangan / Catatan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pro-rate 70% atau beban kerja khusus"></textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Tarif Tunjangan
        </button>
    </div>
</form>

<script>
    if ($.fn.select2) {
        $('#select_jabatan_struktural').select2({
            dropdownParent: $('#modal-tunjangan'),
            width: '100%'
        });
    }

    $('#create_nominal_dasar, #create_persen_bayar').on('input', function() {
        var dasar = parseFloat($('#create_nominal_dasar').val()) || 0;
        var persen = parseFloat($('#create_persen_bayar').val()) || 0;
        if (persen > 0) {
            var hitung = Math.round(dasar * (persen / 100));
            $('#create_nominal_tunjangan').val(hitung);
        }
    });
</script>
