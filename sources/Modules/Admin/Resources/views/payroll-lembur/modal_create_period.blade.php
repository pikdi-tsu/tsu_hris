<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-calendar-plus mr-2"></i> Buat Periode Payroll Lembur Baru
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-create-period-lembur" action="{{ route('admin.payroll.lembur.store-period') }}" method="POST">
    @csrf
    <div class="modal-body p-4 bg-white" style="max-height: calc(85vh - 120px); overflow-y: auto;">

        <div class="alert alert-light border mb-4 d-flex align-items-center" style="border-radius: 8px; border-left: 4px solid #094b54 !important;">
            <i class="fas fa-info-circle text-primary fa-lg mr-3"></i>
            <div class="small text-muted" style="line-height: 1.4;">
                Sistem akan <strong>otomatis menarik seluruh lembur yang disetujui (Approved)</strong> oleh Atasan dan HRD dalam rentang tanggal cut-off yang Anda tentukan di bawah ini.
            </div>
        </div>

        {{-- Row 1: Nama Periode --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Nama Periode Lembur <span class="text-danger">*</span>
            </label>
            <input type="text" name="nama_periode" id="nama_periode" class="form-control" 
                   value="Lembur Tendik TSU - {{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('F Y') }}" 
                   required placeholder="Contoh: Lembur Tendik TSU - Februari 2026">
        </div>

        {{-- Row 2: Bulan & Tahun --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Bulan Penggajian <span class="text-danger">*</span>
                    </label>
                    <select name="bulan" id="bulan" class="form-control" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Tahun Penggajian <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="tahun" id="tahun" class="form-control" 
                           value="{{ $currentYear }}" min="2020" max="2035" required>
                </div>
            </div>
        </div>

        {{-- Row 3: Rentang Cut-Off Khusus Lembur --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        <i class="fas fa-calendar-alt text-primary mr-1"></i> Tanggal Mulai Cut-Off <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="start_date_cutoff" id="start_date_cutoff" class="form-control" 
                           value="{{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->subMonth()->format('Y-m-25') }}" required>
                    <small class="text-muted" style="font-size: 0.74rem;">Batas awal jam lembur ditarik.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        <i class="fas fa-calendar-check text-primary mr-1"></i> Tanggal Selesai Cut-Off <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="end_date_cutoff" id="end_date_cutoff" class="form-control" 
                           value="{{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 24)->format('Y-m-d') }}" required>
                    <small class="text-muted" style="font-size: 0.74rem;">Batas akhir jam lembur ditarik.</small>
                </div>
            </div>
        </div>

        {{-- Row 4: Tanggal Surat & Kota --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Tanggal Cetak Surat / Slip
                    </label>
                    <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control" 
                           value="{{ now()->format('Y-m-d') }}">
                    <small class="text-muted" style="font-size: 0.74rem;">Tercetak di pojok kanan bawah slip.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Kota Surat
                    </label>
                    <input type="text" name="kota_surat" id="kota_surat" class="form-control" 
                           value="Surakarta">
                    <small class="text-muted" style="font-size: 0.74rem;">Lokasi penerbitan dokumen slip.</small>
                </div>
            </div>
        </div>

        {{-- Row 5: Pilih Pegawai Penandatangan (Daftar Karyawan) --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                <i class="fas fa-user-tie text-primary mr-1"></i> Pilih Pegawai Penandatangan (Daftar Karyawan) <span class="text-danger">*</span>
            </label>
            <select name="karyawan_ttd_id" id="karyawan_ttd_id" class="form-control select2-ttd" style="width: 100%;">
                <option value="">-- Ketik Nama atau NIP/NIK untuk Mencari --</option>
                @foreach($karyawans as $k)
                    @php
                        $hasMaster = isset($masterTtdList[$k->id]);
                        $masterObj = $hasMaster ? $masterTtdList[$k->id] : null;

                        $namaFormatted = $masterObj ? $masterObj->nama_pejabat : trim(($k->gelar_depan ? $k->gelar_depan . ' ' : '') . $k->nama . ($k->gelar_belakang ? ', ' . $k->gelar_belakang : ''));
                        $jabatanFormatted = $masterObj ? $masterObj->jabatan : ($k->posisi ?: ($k->tipe_karyawan ?: 'Bagian SDM'));
                        $isSelected = ($ttdDefault && $ttdDefault->karyawan_id == $k->id) || ($k->nama == 'Afifah Raisya Putri Sanjaya');
                    @endphp
                    <option value="{{ $k->id }}"
                            data-nama="{{ $namaFormatted }}"
                            data-jabatan="{{ $jabatanFormatted }}"
                            data-has-master="{{ $hasMaster ? '1' : '0' }}"
                            {{ $isSelected ? 'selected' : '' }}>
                        {{ $namaFormatted }} ({{ $k->nip ?: ($k->nik ?: '-') }}) - {{ $jabatanFormatted }} {{ $hasMaster ? '★ [Master TTD & Stempel]' : '' }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                Opsi bertanda ★ memiliki Tanda Tangan &amp; Stempel Digital di <strong>Master Data Tanda Tangan</strong> yang otomatis tercetak pada slip. Anda tetap dapat mengeditnya jika diperlukan.
            </small>
        </div>

        {{-- Row 6: Nama Pejabat & Jabatan Penandatangan --}}
        <div class="row">
            <div class="col-md-7">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Nama Pejabat Penandatangan <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="penandatangan_nama" id="penandatangan_nama" class="form-control" 
                           value="{{ $ttdDefault->nama_pejabat ?? 'Afifah Raisya Putri Sanjaya, S.I.P.' }}" required>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark small mb-1">
                        Jabatan Penandatangan <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="penandatangan_jabatan" id="penandatangan_jabatan" class="form-control" 
                           value="{{ $ttdDefault->jabatan ?? 'Sumber Daya Manusia' }}" required>
                </div>
            </div>
        </div>

        {{-- Section 2: Penugasan Validator & Approval Bertingkat --}}
        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-3" style="color: var(--tsu-primary, #094b54);">
            <i class="fas fa-users-cog mr-1"></i> Penugasan Validator &amp; Approval Bertingkat
        </h6>
        <p class="text-muted small mb-3" style="font-size: 8.5pt;">
            Tentukan susunan personil yang bertugas memverifikasi dan memberikan persetujuan final sebelum periode lembur dikunci resmi.
        </p>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                <i class="fas fa-user-check text-info mr-1"></i> Validator 1 (Pemeriksa Tingkat 1) <span class="text-danger">*</span>
            </label>
            <select name="validator_1_id" id="validator_1_id" class="form-control select2-modal" required style="width: 100%;">
                <option value="">-- Pilih Karyawan Validator 1 --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">
                        {{ trim(($k->gelar_depan ? $k->gelar_depan . ' ' : '') . $k->nama . ($k->gelar_belakang ? ', ' . $k->gelar_belakang : '')) }} ({{ $k->nip ?: ($k->nik ?: '-') }}) &bull; {{ $k->posisi ?: ($k->tipe_karyawan ?: 'Tendik') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                <i class="fas fa-user-check text-primary mr-1"></i> Validator 2 (Pemeriksa Tingkat 2 - Opsional)
            </label>
            <select name="validator_2_id" id="validator_2_id" class="form-control select2-modal" style="width: 100%;">
                <option value="">-- Langsung ke Approval Final (Tanpa Validator 2) --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">
                        {{ trim(($k->gelar_depan ? $k->gelar_depan . ' ' : '') . $k->nama . ($k->gelar_belakang ? ', ' . $k->gelar_belakang : '')) }} ({{ $k->nip ?: ($k->nik ?: '-') }}) &bull; {{ $k->posisi ?: ($k->tipe_karyawan ?: 'Tendik') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                <i class="fas fa-crown text-warning mr-1"></i> Approval Paling Atas (Persetujuan Final &amp; Kunci Otomatis) <span class="text-danger">*</span>
            </label>
            <select name="approval_id" id="approval_id" class="form-control select2-modal" required style="width: 100%;">
                <option value="">-- Pilih Pejabat Approval Paling Atas --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">
                        {{ trim(($k->gelar_depan ? $k->gelar_depan . ' ' : '') . $k->nama . ($k->gelar_belakang ? ', ' . $k->gelar_belakang : '')) }} ({{ $k->nip ?: ($k->nik ?: '-') }}) &bull; {{ $k->posisi ?: ($k->tipe_karyawan ?: 'Tendik') }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Catatan --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">Catatan Tambahan (Opsional)</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan internal periode lembur..."></textarea>
        </div>

    </div>

    <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary btn-sm px-4" id="btn-submit-create-period">
            <i class="fas fa-magic mr-1"></i> Buat &amp; Tarik Data Lembur
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 di dalam modal
        if ($.fn.select2) {
            $('.select2-ttd').select2({
                dropdownParent: $('#modal-create-period-lembur'),
                width: '100%',
                placeholder: "-- Ketik Nama atau NIP/NIK untuk Mencari --"
            });
            $('.select2-modal').select2({
                dropdownParent: $('#modal-create-period-lembur'),
                width: '100%'
            });
        }

        // Autofill saat pegawai penandatangan dipilih
        $('#karyawan_ttd_id').on('change', function() {
            let selected = $(this).find(':selected');
            let nama = selected.data('nama');
            let jabatan = selected.data('jabatan');

            if (nama) {
                $('#penandatangan_nama').val(nama);
            }
            if (jabatan) {
                $('#penandatangan_jabatan').val(jabatan);
            }
        });

        // Submit handler
        $('#form-create-period-lembur').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn-submit-create-period');
            let formData = form.serialize();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses & Menghitung Data...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(res) {
                    if (res.status === 'success') {
                        $('#modal-create-period-lembur').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(function() {
                            window.location.href = res.redirect;
                        }, 1200);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-magic mr-1"></i> Buat &amp; Tarik Data Lembur');
                    let errors = xhr.responseJSON?.errors;
                    let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    if (errors) {
                        msg = Object.values(errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Membuat Periode!',
                        html: msg
                    });
                }
            });
        });
    });
</script>
