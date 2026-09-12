<form action="{{ route('admin.honorarium.store') }}" method="POST" id="form-create-honorarium">
    @csrf
    <div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; padding: 1.1rem 1.4rem;">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem; color: #ffffff;">
            <i class="fas fa-calendar-plus mr-1"></i> Buat Periode Honorarium Dosen Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body p-4" style="font-size: 9.5pt;">
        {{-- Section 1: Informasi Periode --}}
        <h6 class="font-weight-bold border-bottom pb-2 mb-3" style="color: var(--tsu-primary, #094b54);">
            <i class="fas fa-calendar-alt mr-1"></i> 1. Informasi Periode &amp; Cut-Off
        </h6>

        <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Nama Periode Honorarium <span class="text-danger">*</span></label>
            <input type="text" name="nama_periode" id="namaPeriodeInput" class="form-control" placeholder="Contoh: Honorarium Dosen Semester Genap TA 2025/2026" style="border-radius: 6px;" required>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-dark">Tahun Akademik <span class="text-danger">*</span></label>
                    <input type="text" name="tahun_akademik" id="tahunAkademikInput" class="form-control" value="{{ date('Y') }}/{{ date('Y')+1 }}" placeholder="2025/2026" style="border-radius: 6px;" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-dark">Semester <span class="text-danger">*</span></label>
                    <select name="semester" id="semesterSelect" class="form-control" style="border-radius: 6px;" required>
                        <option value="Genap" selected>Genap</option>
                        <option value="Ganjil">Ganjil</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-dark">Bulan Transaksi</label>
                    <select name="bulan_honor" id="bulanHonorSelect" class="form-control" style="border-radius: 6px;">
                        @php
                            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            $curMonth = $months[date('n') - 1];
                        @endphp
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $m == $curMonth ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-dark">Standar Jml Pertemuan (SKS)</label>
                    <input type="number" name="jumlah_pertemuan" id="jumlahPertemuanInput" class="form-control" min="1" max="16" value="3" style="border-radius: 6px;">
                    <small class="text-muted">Standar: 3 pertemuan/bulan</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-dark">Tanggal Awal Cut-Off <span class="text-danger">*</span></label>
                    <input type="date" name="start_date_cutoff" class="form-control" value="{{ date('Y-m-01') }}" style="border-radius: 6px;" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small text-dark">Tanggal Akhir Cut-Off <span class="text-danger">*</span></label>
                    <input type="date" name="end_date_cutoff" class="form-control" value="{{ date('Y-m-t') }}" style="border-radius: 6px;" required>
                </div>
            </div>
        </div>

        <div class="p-3 my-2 rounded" style="background: #e6f3f4; border: 1px solid #c4e4e7; color: #07383f; font-size: 0.82rem;">
            <i class="fas fa-info-circle mr-1 text-primary"></i> Setelah periode dibuat, lembar kroscek akan disiapkan. Anda dapat menambahkan dosen-dosen penerima honorarium secara langsung di lembar kroscek.
        </div>

        {{-- Section 2: Susunan Approval Bertingkat --}}
        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4" style="color: var(--tsu-primary, #094b54);">
            <i class="fas fa-users-cog mr-1"></i> 2. Susunan Validator &amp; Approval
        </h6>

        <div class="form-group row mb-3">
            <label class="col-sm-4 col-form-label small font-weight-bold text-dark">1. Validator 1 <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select name="validator_1_id" class="form-control select2-modal" required style="width: 100%;">
                    <option value="">-- Pilih Pegawai Validator 1 --</option>
                    @foreach($dosenTendiks as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }})</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">Pemeriksa awal berkas &amp; keabsahan data.</small>
            </div>
        </div>
        <div class="form-group row mb-3">
            <label class="col-sm-4 col-form-label small font-weight-bold text-dark">2. Validator 2 <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select name="validator_2_id" class="form-control select2-modal" required style="width: 100%;">
                    <option value="">-- Pilih Pegawai Validator 2 --</option>
                    @foreach($dosenTendiks as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }})</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">Pemeriksa lanjutan berkas.</small>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-4 col-form-label small font-weight-bold text-dark">3. Approval Final <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select name="approval_id" class="form-control select2-modal" required style="width: 100%;">
                    <option value="">-- Pilih Pimpinan Approval --</option>
                    @foreach($dosenTendiks as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }})</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">Pimpinan tertinggi yang berwenang menyetujui &amp; mengunci berkas.</small>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light px-4 py-3 border-top">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
        <button type="submit" class="btn btn-sm text-white px-4 font-weight-bold" id="btnSubmitCreateHonor" style="background: #094b54; border-radius: 6px;">
            <i class="fas fa-save mr-1"></i> Buat Periode &amp; Lanjut Kroscek
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('.select2-modal').select2({
            dropdownParent: $('#form-create-honorarium').closest('.modal'),
            width: '100%'
        });

        function autoGeneratePeriodName() {
            var ta = $('#tahunAkademikInput').val();
            var smt = $('#semesterSelect').val();
            var bln = $('#bulanHonorSelect').val();

            $('#namaPeriodeInput').val('Honorarium Dosen ' + bln + ' Semester ' + smt + ' TA ' + ta);
        }

        if (!$('#namaPeriodeInput').val()) {
            autoGeneratePeriodName();
        }

        $('#tahunAkademikInput, #semesterSelect, #bulanHonorSelect').on('change input', function() {
            autoGeneratePeriodName();
        });

        // Submit form with SweetAlert2
        $('#form-create-honorarium').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#btnSubmitCreateHonor');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if (res.success && res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Buat Periode &amp; Lanjut Kroscek');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message || 'Periode honorarium berhasil dibuat.',
                            icon: 'success',
                            confirmButtonColor: '#094b54'
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Buat Periode &amp; Lanjut Kroscek');
                    var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                    var errorMsg = 'Gagal membuat periode honorarium.';
                    if (errors) {
                        errorMsg = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Gagal!',
                        html: errorMsg,
                        icon: 'error',
                        confirmButtonColor: '#094b54'
                    });
                }
            });
        });
    });
</script>
