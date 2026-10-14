<div class="modal-header bg-danger text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-user-slash mr-2"></i>Proses Pemutusan Hubungan Kerja (PHK) / Purna Tugas
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formAddPhk" method="POST">
    @csrf
    <div class="modal-body p-4 bg-white">
        <div class="alert alert-warning py-2 px-3 small mb-3">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <strong>Perhatian:</strong> Tindakan ini akan mengubah status kepegawaian menjadi <strong>Non-Aktif</strong> dan memutuskan kontrak aktif terkait.
        </div>

        <!-- Pilih Pegawai Aktif -->
        <div class="form-group">
            <label class="font-weight-bold text-dark">Pilih Pegawai Aktif <span class="text-danger">*</span></label>
            <select name="pegawai_id" id="phkPegawaiSelect" class="form-control select2-modal" required style="width: 100%;">
                <option value="">-- Cari Nama / NIK Pegawai Aktif --</option>
                @foreach($pegawais as $p)
                    @php
                        $nama = trim(($p->gelar_depan ? $p->gelar_depan.' ' : '') . $p->nama . ($p->gelar_belakang ? ', '.$p->gelar_belakang : ''));
                        $nik = $p->nik ?? $p->nip ?? '-';
                        $unit = $p->unit->nama_unit ?? '-';
                    @endphp
                    <option value="{{ $p->id }}" data-tipe="{{ $p->tipe_karyawan }}" data-posisi="{{ $p->posisi }}" data-unit="{{ $unit }}">
                        {{ $nama }} ({{ $p->tipe_karyawan }}) - NIK: {{ $nik }} - Unit: {{ $unit }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Tanggal Efektif PHK <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_efektif" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Kategori / Alasan PHK <span class="text-danger">*</span></label>
                    <select name="alasan_phk" class="form-control" required>
                        <option value="Pengunduran Diri (Resign)">Pengunduran Diri (Resign)</option>
                        <option value="Habis Masa Kontrak Kerja (PKWT)">Habis Masa Kontrak Kerja (PKWT)</option>
                        <option value="Memasuki Usia Pensiun">Memasuki Usia Pensiun</option>
                        <option value="Pensiun Dini atas Permohonan Sendiri">Pensiun Dini atas Permohonan Sendiri</option>
                        <option value="Pemutusan Hubungan Kerja (Efisiensi / Restrukturisasi)">Pemutusan Hubungan Kerja (Efisiensi / Restrukturisasi)</option>
                        <option value="Pelanggaran Disiplin / Kode Etik">Pelanggaran Disiplin / Kode Etik</option>
                        <option value="Meninggal Dunia">Meninggal Dunia</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Nomor SK / Surat Keterangan PHK</label>
            <input type="text" name="no_sk_phk" class="form-control" placeholder="Contoh: SK-PHK/YAYASAN-TSU/X/2026/08">
            <small class="text-muted">Nomor ketetapan surat keputusan penghentian kerja.</small>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Keterangan / Hak Pesangon / Uang Pisah</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan kompensasi pesangon, alasan detail, atau catatan serah terima..."></textarea>
        </div>

        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="chkTriggerOffboarding" name="trigger_offboarding" value="1" checked>
            <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="chkTriggerOffboarding">
                Otomatis inisiasi alur checklist Offboarding di Employee Transition
            </label>
            <small class="form-text text-muted">
                Sistem akan menyiapkan daftar checklist serah terima aset, penonaktifan akun, dan administrasi pelepasan tugas.
            </small>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger" id="btnSubmitPhk">
            <i class="fas fa-check mr-1"></i> Proses PHK Pegawai
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('.select2-modal').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalPhkAction')
    });

    $('#formAddPhk').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSubmitPhk');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        $.ajax({
            url: "{{ route('admin.employee-lifecycle.phk.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    $('#modalPhkAction').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Proses PHK Pegawai');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Proses PHK Pegawai');
                var msg = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
