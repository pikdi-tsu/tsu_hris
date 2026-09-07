<div class="modal-header bg-success text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-edit mr-2"></i> Atur Tunjangan Fungsional
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('admin.master-tunjangan.fungsional.update', $tunjangan->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4">
        <div class="form-group">
            <label class="font-weight-bold">Jenjang Fungsional Terhubung (Dari Master Jabatan) <span class="text-danger">*</span></label>
            <select name="jabatan_fungsional_id" id="edit_select_jabatan_fungsional" class="form-control select2" style="width: 100%;">
                @foreach($jabatans as $j)
                    <option value="{{ $j->id }}" {{ $tunjangan->jabatan_fungsional_id == $j->id ? 'selected' : '' }}>
                        {{ $j->nama_jabatan }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted"><i class="fas fa-link text-success mr-1"></i> Terhubung langsung dengan Master Jabatan Fungsional.</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Kode Singkatan <span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control font-weight-bold text-center" value="{{ $tunjangan->kode }}" placeholder="AA, L, LK, GB">
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
                        <input type="number" step="1000" min="0" name="nominal_tunjangan" class="form-control form-control-lg font-weight-bold text-success" value="{{ $tunjangan->nominal_tunjangan ?: 0 }}" required placeholder="Contoh: 700000">
                    </div>
                    <small class="text-muted">Nominal tunjangan fungsional bulanan.</small>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold">Keterangan / Catatan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional">{{ $tunjangan->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success font-weight-bold">
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
