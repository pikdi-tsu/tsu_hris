<div class="modal-header" style="background-color: #094b54; color: #fff;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-clipboard-check mr-2"></i>Form Evaluasi Kinerja Masa Percobaan
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formEvaluasiProbation">
    @csrf
    <div class="modal-body p-4">
        <!-- Info Pegawai -->
        <div class="alert alert-info border-0 shadow-sm mb-4" style="background-color: #e0f2f1; color: #004d40; border-radius: 8px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-circle fa-2x mr-3"></i>
                <div>
                    <div class="font-weight-bold" style="font-size: 1.05rem;">
                        {{ $probation->pegawai->nama }}
                    </div>
                    <div class="small">
                        Unit: <strong>{{ $probation->pegawai->unit->nama_unit ?? '-' }}</strong> | Posisi: <strong>{{ $probation->pegawai->posisi ?? '-' }}</strong>
                    </div>
                    <div class="small">
                        Periode: <strong>{{ \Carbon\Carbon::parse($probation->tgl_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($probation->tgl_selesai)->format('d M Y') }}</strong> (Durasi {{ $probation->durasi_bulan }} Bulan)
                    </div>
                </div>
            </div>
        </div>

        <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
            <i class="fas fa-star text-warning mr-1"></i> Penilaian Aspek Kinerja (Skala 0 - 100)
        </h6>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Kedisiplinan & Presensi <span class="text-danger">*</span></label>
                    <input type="number" name="skor_kedisiplinan" id="skorKedisiplinan" class="form-control skor-input" min="0" max="100" value="{{ $probation->skor_kedisiplinan ?? 80 }}" required>
                    <small class="text-muted">Ketepatan jam kerja & kepatuhan tata tertib</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Kompetensi Kerja <span class="text-danger">*</span></label>
                    <input type="number" name="skor_kompetensi" id="skorKompetensi" class="form-control skor-input" min="0" max="100" value="{{ $probation->skor_kompetensi ?? 80 }}" required>
                    <small class="text-muted">Kualitas dan ketuntasan target pekerjaan</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Kerjasama & Perilaku <span class="text-danger">*</span></label>
                    <input type="number" name="skor_kerjasama" id="skorKerjasama" class="form-control skor-input" min="0" max="100" value="{{ $probation->skor_kerjasama ?? 80 }}" required>
                    <small class="text-muted">Inisiatif dan komunikasi dengan tim</small>
                </div>
            </div>
        </div>

        <!-- Live Score Summary -->
        <div class="card bg-light border-0 shadow-sm p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="font-weight-bold text-dark">Rata-Rata Skor Total:</span>
                    <span class="small text-muted d-block">(Kalkulasi otomatis dari ketiga aspek di atas)</span>
                </div>
                <div class="text-right">
                    <span class="font-weight-bold display-4" id="liveScoreTotal" style="font-size: 2rem; color: #094b54;">
                        {{ number_format($probation->skor_total ?? 80, 1) }}
                    </span>
                    <span class="text-muted font-weight-bold">/ 100</span>
                </div>
            </div>
        </div>

        <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
            <i class="fas fa-gavel text-info mr-1"></i> Keputusan Rekomendasi & Evaluator
        </h6>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Hasil Rekomendasi <span class="text-danger">*</span></label>
                    <select name="rekomendasi" class="form-control" required>
                        <option value="angkat_tetap" {{ ($probation->rekomendasi == 'angkat_tetap') ? 'selected' : '' }}>
                            Lulus - Angkat Karyawan Tetap
                        </option>
                        <option value="perpanjang_probation" {{ ($probation->rekomendasi == 'perpanjang_probation') ? 'selected' : '' }}>
                            Pertimbangan - Perpanjang Masa Percobaan
                        </option>
                        <option value="tidak_lolos" {{ ($probation->rekomendasi == 'tidak_lolos') ? 'selected' : '' }}>
                            Tidak Lolos - Pengakhiran Hubungan Kerja
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Evaluator / Penilai</label>
                    <select name="evaluator_id" class="form-control select2-evaluator" style="width: 100%;">
                        <option value="">-- Pilih Atasan / HR Penilai --</option>
                        @foreach($evaluators as $ev)
                            <option value="{{ $ev->id }}" {{ ($probation->evaluator_id == $ev->id) ? 'selected' : '' }}>
                                {{ $ev->nama }} ({{ $ev->posisi ?? 'Staff' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan & Saran Pengembangan</label>
            <textarea name="catatan_evaluasi" class="form-control" rows="3" placeholder="Tuliskan masukan penting untuk pegawai ini...">{{ $probation->catatan_evaluasi }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSubmitEvaluasi" style="background-color: #094b54; border-color: #094b54;">
            <i class="fas fa-check-circle mr-1"></i> Simpan Hasil Evaluasi
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('.select2-evaluator').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalAction')
    });

    function calcTotal() {
        var s1 = parseFloat($('#skorKedisiplinan').val()) || 0;
        var s2 = parseFloat($('#skorKompetensi').val()) || 0;
        var s3 = parseFloat($('#skorKerjasama').val()) || 0;
        var avg = (s1 + s2 + s3) / 3;
        $('#liveScoreTotal').text(avg.toFixed(1));
    }

    $('.skor-input').on('input change', calcTotal);

    $('#formEvaluasiProbation').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSubmitEvaluasi');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        const url = "{{ route('admin.employee-lifecycle.probation.store-evaluasi', $probation->id) }}";

        $.post(url, $(this).serialize(), function(res) {
            if (res.status === 'success') {
                $('#modalAction').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                Swal.fire('Gagal', res.message, 'error');
                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Simpan Hasil Evaluasi');
            }
        }).fail(function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Simpan Hasil Evaluasi');
            var msg = 'Terjadi kesalahan sistem';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire('Error', msg, 'error');
        });
    });
});
</script>
