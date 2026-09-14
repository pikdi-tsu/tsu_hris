<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); border-top-left-radius: 12px; border-top-right-radius: 12px;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2"></i> Atur Tunjangan Jabatan Struktural
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.master-tunjangan.struktural.update', $tunjangan->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4" style="background-color: #fafbfc;">
        <div class="alert alert-info border-0 mb-3" style="background-color: #f0fdfa; border-left: 4px solid #0c6170 !important; border-radius: 8px; color: #0f766e;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="font-size: 1.05rem;"></i>
                <div style="font-size: 0.84rem; line-height: 1.45;">
                    Perubahan nominal tunjangan struktural ini akan langsung disinkronkan ke master penggajian payroll universitas.
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Jabatan Struktural Terhubung (Dari Master Jabatan) <span class="text-danger">*</span>
            </label>
            <select name="jabatan_struktural_id" id="edit_select_jabatan_struktural" class="form-control select2" style="width: 100%;">
                @foreach($jabatans as $j)
                    <option value="{{ $j->id }}" {{ $tunjangan->jabatan_struktural_id == $j->id ? 'selected' : '' }}>
                        {{ $j->nama_jabatan }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted" style="font-size: 0.75rem;">Data tersimpan di tabel finansial <code>master_pengaturan_tunjangans</code>.</small>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Nominal Dasar 100% (Rp)
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #cbd5e1; border-top-left-radius: 8px; border-bottom-left-radius: 8px; color: #094b54;">Rp</span>
                    </div>
                    <input type="number" step="1000" min="0" id="input_nominal_dasar" name="nominal_dasar" class="form-control font-weight-bold text-dark" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #cbd5e1;" value="{{ $tunjangan->nominal_dasar ?: 0 }}" required>
                </div>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Tarif standar 100%</small>
            </div>
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                    Persen Pembayaran (%)
                </label>
                <div class="input-group">
                    <input type="number" step="0.1" min="0" max="100" id="input_persen_bayar" name="persen_bayar" class="form-control font-weight-bold text-dark" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-color: #cbd5e1;" value="{{ $tunjangan->persen_bayar ?: 100 }}" required>
                    <div class="input-group-append">
                        <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #cbd5e1; border-top-right-radius: 8px; border-bottom-right-radius: 8px; color: #094b54;">%</span>
                    </div>
                </div>
                <small class="form-text text-muted" style="font-size: 0.75rem;">Contoh: 100%, 70%, 55%, 45%, 35%</small>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold" style="font-size: 0.85rem; color: #047857;">
                Nominal Tunjangan Dibayarkan / Bulan (Rp) <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text font-weight-bold text-white" style="background: #047857; border-color: #047857; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">Rp</span>
                </div>
                <input type="number" step="1" min="0" id="input_nominal_tunjangan" name="nominal_tunjangan" class="form-control form-control-lg font-weight-bold" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: #047857; color: #047857;" value="{{ $tunjangan->nominal_tunjangan ?: 0 }}" required placeholder="Contoh: 1417500">
            </div>
            <small class="form-text text-muted" style="font-size: 0.75rem;">Nominal final per bulan yang otomatis masuk ke perhitungan payroll karyawan.</small>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                Keterangan / Catatan Matriks
            </label>
            <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 8px; border-color: #cbd5e1;" placeholder="Catatan beban kerja, pro-rate, atau penugasan ganda">{{ $tunjangan->keterangan }}</textarea>
        </div>
    </div>
    <div class="modal-footer bg-white border-top d-flex justify-content-between px-4 py-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 500;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-sm tsu-btn-primary-action px-4" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan Tarif
        </button>
    </div>
</form>

<script>
    if ($.fn.select2) {
        $('#edit_select_jabatan_struktural').select2({
            dropdownParent: $('#modal-tunjangan'),
            width: '100%'
        });
    }

    $('#input_nominal_dasar, #input_persen_bayar').on('input', function() {
        var dasar = parseFloat($('#input_nominal_dasar').val()) || 0;
        var persen = parseFloat($('#input_persen_bayar').val()) || 0;
        if (persen > 0) {
            var hitung = Math.round(dasar * (persen / 100));
            $('#input_nominal_tunjangan').val(hitung);
        }
    });
</script>
