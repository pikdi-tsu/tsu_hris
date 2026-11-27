<div class="modal-header" style="background-color: var(--tsu-primary); color: #fff;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-user-plus mr-2"></i>Daftarkan Pegawai Masa Percobaan (Probation)
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formAddProbation">
    @csrf
    <div class="modal-body p-4">
        <div class="form-group">
            <label class="font-weight-bold text-dark">Pilih Pegawai <span class="text-danger">*</span></label>
            <select name="pegawai_id" id="probationPegawaiSelect" class="form-control select2-probation" required style="width: 100%;">
                <option value="">-- Pilih Pegawai --</option>
                @foreach($pegawais as $p)
                    @php
                        $nama = trim(($p->gelar_depan ? $p->gelar_depan.' ' : '') . $p->nama . ($p->gelar_belakang ? ', '.$p->gelar_belakang : ''));
                        $nik = $p->nik ?? $p->nip ?? '-';
                        $unit = $p->unit->nama_unit ?? '-';
                    @endphp
                    <option value="{{ $p->id }}" data-tgl="{{ $p->tanggal_masuk ?? date('Y-m-d') }}">
                        {{ $nama }} ({{ $p->tipe_karyawan }}) - NIK: {{ $nik }} - Unit: {{ $unit }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Durasi (Bulan) <span class="text-danger">*</span></label>
                    <input type="number" name="durasi_bulan" id="probationDurasi" class="form-control" value="3" min="1" max="12" required>
                    <small class="text-muted">Standar masa probation: 3 bulan</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_mulai" id="probationTglMulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_selesai" id="probationTglSelesai" class="form-control" value="{{ date('Y-m-d', strtotime('+3 months')) }}" required>
                </div>
            </div>
        </div>

        <div class="alert alert-light border small text-muted">
            <i class="fas fa-info-circle text-info mr-1"></i>
            Setelah didaftarkan, pegawai akan masuk ke pemantauan probation. Admin dan atasan unit dapat mengisi form evaluasi skor (Disiplin, Kompetensi, Kerjasama Tim) sebelum masa berlaku berakhir.
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSubmitProbation" style="background-color: var(--tsu-primary); border-color: var(--tsu-primary);">
            <i class="fas fa-save mr-1"></i> Daftarkan Probation
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('.select2-probation').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalAction')
    });

    // Otomatis hitung tanggal selesai saat tanggal mulai atau durasi berubah
    function updateTglSelesai() {
        const mulai = $('#probationTglMulai').val();
        const durasi = parseInt($('#probationDurasi').val()) || 3;
        if (mulai) {
            let d = new Date(mulai);
            d.setMonth(d.getMonth() + durasi);
            let yyyy = d.getFullYear();
            let mm = String(d.getMonth() + 1).padStart(2, '0');
            let dd = String(d.getDate()).padStart(2, '0');
            $('#probationTglSelesai').val(`${yyyy}-${mm}-${dd}`);
        }
    }

    $('#probationTglMulai, #probationDurasi').on('change input', updateTglSelesai);

    $('#formAddProbation').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSubmitProbation');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.post("{{ route('admin.employee-lifecycle.probation.store') }}", $(this).serialize(), function(res) {
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
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Daftarkan Probation');
            }
        }).fail(function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Daftarkan Probation');
            var msg = 'Terjadi kesalahan';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire('Error', msg, 'error');
        });
    });
});
</script>
