<div class="modal-header" style="background-color: #094b54; color: #fff;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-gifts mr-2"></i>Buat Periode Tunjangan Hari Raya (THR) Baru
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formCreateThrPeriod">
    @csrf
    <div class="modal-body p-4">

        {{-- Section 1: Data Periode & Tanggal --}}
        <h6 class="font-weight-bold border-bottom pb-2 mb-3" style="color: var(--tsu-primary, #094b54);">
            <i class="fas fa-calendar-alt mr-1"></i> 1. Informasi Periode &amp; Tanggal Dokumen
        </h6>

        <div class="form-group">
            <label class="font-weight-bold text-dark small">Nama Periode THR <span class="text-danger">*</span></label>
            <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: THR Idul Fitri 1447 H (Maret 2026)" required value="THR Idul Fitri {{ date('Y') }}">
            <small class="text-muted" style="font-size: 8pt;">Nama ini akan tercetak sebagai sub-judul resmi pada slip THR pegawai.</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark small">Tahun Anggaran <span class="text-danger">*</span></label>
                    <input type="number" name="tahun" class="form-control form-control-sm" value="{{ date('Y') }}" min="2020" max="2099" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark small">Tanggal Cut-Off Masa Kerja <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_cutoff" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    <small class="text-muted" style="font-size: 8pt;">Patokan perhitungan masa kerja.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark small">Tanggal Cetak Surat <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_surat" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    <small class="text-muted" style="font-size: 8pt;">Tanggal tertera di slip.</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark small">Kota Surat <span class="text-danger">*</span></label>
                    <input type="text" name="kota_surat" class="form-control form-control-sm" value="Surakarta" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark small">Jabatan Penandatangan <span class="text-danger">*</span></label>
                    <input type="text" name="penandatangan_jabatan" class="form-control form-control-sm" value="Bagian SDM" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark small">Nama Penandatangan <span class="text-danger">*</span></label>
                    <input type="text" name="penandatangan_nama" class="form-control form-control-sm" value="Afifah Raisya Putri Sanjaya, S.I.P." required>
                </div>
            </div>
        </div>

        {{-- Section 2: Penugasan Validator & Approval Bertingkat --}}
        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-3" style="color: var(--tsu-primary, #094b54);">
            <i class="fas fa-users-cog mr-1"></i> 2. Penugasan Validator &amp; Approval Bertingkat
        </h6>
        <p class="text-muted small mb-3" style="font-size: 8.5pt;">
            Tentukan susunan pejabat/karyawan yang bertugas memverifikasi dan menyetujui rekapitulasi THR sebelum dikunci final.
        </p>

        <div class="form-group">
            <label class="font-weight-bold text-dark small">
                <i class="fas fa-user-check text-info mr-1"></i> Validator 1 (Pemeriksa Tingkat 1) <span class="text-danger">*</span>
            </label>
            <select name="validator_1_id" class="form-control select2-modal" required style="width: 100%;">
                <option value="">-- Pilih Karyawan Validator 1 --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">
                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) &bull; {{ $k->nama_unit ?: $k->posisi }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark small">
                <i class="fas fa-user-check text-primary mr-1"></i> Validator 2 (Pemeriksa Tingkat 2 - Opsional)
            </label>
            <select name="validator_2_id" class="form-control select2-modal" style="width: 100%;">
                <option value="">-- Langsung ke Approval Final (Tanpa Validator 2) --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">
                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) &bull; {{ $k->nama_unit ?: $k->posisi }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark small">
                <i class="fas fa-crown text-warning mr-1"></i> Approval Paling Atas (Persetujuan Final &amp; Kunci) <span class="text-danger">*</span>
            </label>
            <select name="approval_id" class="form-control select2-modal" required style="width: 100%;">
                <option value="">-- Pilih Pejabat Approval Paling Atas --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">
                        {{ $k->nama }} ({{ $k->nik ?: '-' }}) &bull; {{ $k->nama_unit ?: $k->posisi }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Section 3: Catatan --}}
        <div class="form-group mb-0 mt-3">
            <label class="font-weight-bold text-dark small">Catatan / Keterangan (Opsional)</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan internal atau memo pimpinan terkait pencairan THR..."></textarea>
        </div>

        <div class="alert alert-info border-0 shadow-sm mt-3 mb-0" style="background-color: #e0f2f1; color: #004d40; border-radius: 8px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-magic fa-2x mr-3"></i>
                <div class="small">
                    <strong>Kalkulasi Otomatis Seketika:</strong> Setelah disimpan, sistem akan langsung menghitung masa kerja, formula penuh/prorata, serta upah tetap untuk <strong>seluruh Dosen &amp; Tendik aktif</strong> secara instan dengan status <strong>Draft</strong>.
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3 border-top">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-sm text-white font-weight-bold px-4" id="btnSubmitPeriod" style="background-color: #094b54; border-radius: 6px;">
            <i class="fas fa-calculator mr-1"></i> Buat &amp; Hitung THR
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 di dalam modal
    $('.select2-modal').select2({
        dropdownParent: $('#modalCreatePeriod'),
        width: '100%',
        placeholder: '-- Pilih --',
        allowClear: true
    });

    $('#formCreateThrPeriod').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSubmitPeriod');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menghitung THR Seluruh Pegawai...');

        $.ajax({
            url: "{{ route('admin.payroll.thr.store-period') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status === 'success') {
                    $('#modalCreatePeriod').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = res.redirect;
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-calculator mr-1"></i> Buat &amp; Hitung THR');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-calculator mr-1"></i> Buat &amp; Hitung THR');
                let msg = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
