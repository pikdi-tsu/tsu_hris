<div class="modal-header bg-success text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-plus-circle mr-2"></i> Tambah Tunjangan Fungsional Dosen
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-tunjangan.fungsional.store') }}" method="POST">
    @csrf
    <div class="modal-body p-4">
        <div class="form-group">
            <label class="font-weight-bold">Pilih Jenjang Fungsional (Dari Master Jabatan) <span class="text-danger">*</span></label>
            <select name="jabatan_fungsional_id" id="select_jabatan_fungsional" class="form-control select2" required style="width: 100%;">
                <option value="">-- Pilih Jenjang Jabatan Fungsional --</option>
                @foreach($jabatans as $j)
                    @php $alreadySet = in_array($j->id, $existingIds); @endphp
                    <option value="{{ $j->id }}" {{ $alreadySet ? 'disabled' : '' }}>
                        {{ $j->nama_jabatan }} {{ $alreadySet ? '(Sudah Diatur)' : '' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Jenjang jabatan diambil dari Master Jabatan Fungsional.</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Kode Singkatan <span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control font-weight-bold text-center" required placeholder="AA, L, LK, GB">
                    <small class="text-muted">Contoh: AA, L, LK</small>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Nominal Tunjangan / Bulan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold bg-success text-white">Rp</span>
                        </div>
                        <input type="number" step="1000" min="0" name="nominal_tunjangan" class="form-control form-control-lg font-weight-bold text-success" required placeholder="Contoh: 700000">
                    </div>
                    <small class="text-muted">Nominal tunjangan bulanan</small>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold">Keterangan / Catatan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Jenjang dosen bersertifikasi"></textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Jenjang Tunjangan
        </button>
    </div>
</form>

<script>
    if ($.fn.select2) {
        $('#select_jabatan_fungsional').select2({
            dropdownParent: $('#modal-tunjangan'),
            width: '100%'
        });
    }
</script>
