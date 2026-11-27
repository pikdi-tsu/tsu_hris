<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-edit mr-2"></i> Penyesuaian Lembur - {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-edit-karyawan-lembur" action="{{ route('admin.payroll.lembur.update-karyawan', $karyawan->id) }}" method="POST">
    @csrf
    <div class="modal-body p-4 bg-white">

        {{-- Info Box --}}
        <div class="p-3 mb-3 rounded border bg-light">
            <div class="row text-center">
                <div class="col-4 border-right">
                    <small class="text-muted d-block">Gaji Tetap</small>
                    <strong class="text-dark">Rp {{ number_format($karyawan->gaji_tetap, 0, ',', '.') }}</strong>
                </div>
                <div class="col-4 border-right">
                    <small class="text-muted d-block">Total Jam Lembur</small>
                    <strong class="text-primary">{{ number_format($karyawan->total_jam_lembur, 1) }} Jam</strong>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Lembur Sistem</small>
                    <strong class="text-dark">Rp {{ number_format($karyawan->nominal_lembur_sistem, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        {{-- Input Penyesuaian --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Penyesuaian / Koreksi Nominal (Rp) <span class="text-danger">*</span>
            </label>
            <input type="number" name="penyesuaian" id="input_penyesuaian" class="form-control font-weight-bold" 
                   value="{{ $karyawan->penyesuaian }}" step="1000" required>
            <small class="text-muted" style="font-size: 0.74rem;">
                Gunakan nilai positif (+) untuk penambahan atau negatif (-) untuk pengurangan.
            </small>
        </div>

        {{-- Preview Total Final --}}
        <div class="form-group mb-3 p-3 rounded" style="background-color: #f1f5f9;">
            <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-dark">Total Uang Lembur Akhir:</span>
                <span class="font-weight-bold text-success h5 mb-0" id="preview_total_final">
                    Rp {{ number_format($karyawan->total_lembur_final, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Catatan Koreksi --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">Catatan / Alasan Penyesuaian</label>
            <textarea name="catatan_koreksi" class="form-control" rows="2" 
                      placeholder="Contoh: Tambahan jam tugas khusus / koreksi presensi...">{{ $karyawan->catatan_koreksi }}</textarea>
        </div>

    </div>

    <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-create btn-sm px-4" id="btn-submit-edit-karyawan">
            <i class="fas fa-save mr-1"></i> Simpan Penyesuaian
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        const nominalSistem = {{ floatval($karyawan->nominal_lembur_sistem) }};

        $('#input_penyesuaian').on('input', function() {
            let penyesuaian = parseFloat($(this).val()) || 0;
            let total = Math.max(0, nominalSistem + penyesuaian);
            $('#preview_total_final').text('Rp ' + Math.round(total).toLocaleString('id-ID'));
        });

        $('#form-edit-karyawan-lembur').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn-submit-edit-karyawan');
            let formData = form.serialize();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(res) {
                    if (res.status === 'success') {
                        $('#modal-edit-karyawan-lembur').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                        $('#table-payroll-lembur').DataTable().ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penyesuaian');
                    let msg = xhr.responseJSON?.message || 'Gagal menyimpan penyesuaian.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                }
            });
        });
    });
</script>
