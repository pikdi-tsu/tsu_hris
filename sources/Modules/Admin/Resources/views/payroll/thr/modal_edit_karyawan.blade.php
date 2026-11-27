<div class="modal-header" style="background-color: #094b54; color: #fff;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-edit mr-2"></i>Penyesuaian THR Pegawai
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formEditThrKaryawan">
    @csrf
    <div class="modal-body p-4">
        <!-- Callout Info Pegawai -->
        <div class="alert alert-light border shadow-sm mb-4" style="border-left: 4px solid #094b54 !important;">
            <div class="font-weight-bold text-dark" style="font-size: 1.1rem;">
                {{ $karyawan->nama }}
            </div>
            <div class="text-muted small">
                NIK: <strong>{{ $karyawan->nik }}</strong> | Unit: <strong>{{ $karyawan->nama_unit }}</strong> | Posisi: {{ $karyawan->posisi }}
            </div>
            <div class="mt-2 text-dark small">
                Status THR: <strong>{{ $karyawan->status_thr }}</strong> | Masa Kerja: <strong>{{ $karyawan->masa_kerja_formatted }}</strong> | Upah Tetap: <strong>Rp {{ number_format($karyawan->upah_tetap, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Nominal Hasil Rumus (Rp)</label>
                    <input type="text" class="form-control bg-light font-weight-bold" value="Rp {{ number_format($karyawan->nominal_thr, 0, ',', '.') }}" readonly id="displayNominalRumus" data-nominal="{{ $karyawan->nominal_thr }}">
                    <small class="text-muted">Nominal default sesuai formula regulasi.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Penyesuaian / Koreksi (Rp)</label>
                    <input type="number" name="penyesuaian" id="inputPenyesuaian" class="form-control font-weight-bold" value="{{ intval($karyawan->penyesuaian) }}" step="1000" placeholder="Contoh: 100000 atau -50000">
                    <small class="text-muted">Gunakan tanda minus (-) jika pengurangan.</small>
                </div>
            </div>
        </div>

        <!-- Total Akhir Preview -->
        <div class="p-3 mb-3" style="background: #e8f5e9; border: 1.5px dashed #2e7d32; border-radius: 8px;">
            <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-dark">Total THR Diterima:</span>
                <span class="font-weight-bold" id="displayTotalPreview" style="font-size: 1.25rem; color: #1b5e20;">
                    Rp {{ number_format($karyawan->total_thr, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Alasan / Catatan Penyesuaian</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan mengapa ada penyesuaian nominal...">{!! e($karyawan->keterangan) !!}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSavePenyesuaian" style="background-color: #094b54; border-color: #094b54;">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    const nominalRumus = parseFloat($('#displayNominalRumus').data('nominal')) || 0;

    $('#inputPenyesuaian').on('input', function() {
        const penyesuaian = parseFloat($(this).val()) || 0;
        const total = Math.max(0, nominalRumus + penyesuaian);
        $('#displayTotalPreview').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
    });

    $('#formEditThrKaryawan').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSavePenyesuaian');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        const url = "{{ route('admin.payroll.thr.update-karyawan', $karyawan->id) }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    $('#modalEditKaryawan').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1200,
                        showConfirmButton: false
                    });
                    $('#tableThrKaryawan').DataTable().ajax.reload();
                    // Reload window jika diperlukan untuk refresh total card
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem', 'error');
            }
        });
    });
});
</script>
