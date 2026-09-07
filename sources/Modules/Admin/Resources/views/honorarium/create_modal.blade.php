<form action="{{ route('admin.honorarium.store') }}" method="POST" id="form-create-honorarium">
    @csrf
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-plus-circle mr-2"></i> Buat Periode Honorarium Dosen Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold small">Nama Periode Honorarium <span class="text-danger">*</span></label>
            <input type="text" name="nama_periode" id="namaPeriodeInput" class="form-control form-control-sm" placeholder="Contoh: Honorarium Dosen Semester Genap TA 2025/2026" required>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Tahun Akademik <span class="text-danger">*</span></label>
                    <input type="text" name="tahun_akademik" id="tahunAkademikInput" class="form-control form-control-sm" value="{{ date('Y') }}/{{ date('Y')+1 }}" placeholder="2025/2026" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Semester <span class="text-danger">*</span></label>
                    <select name="semester" id="semesterSelect" class="form-control form-control-sm" required>
                        <option value="Genap" selected>Genap</option>
                        <option value="Ganjil">Ganjil</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Bulan Transaksi</label>
                    <select name="bulan_honor" id="bulanHonorSelect" class="form-control form-control-sm">
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
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Default Jml Pertemuan (SKS)</label>
                    <input type="number" name="jumlah_pertemuan" id="jumlahPertemuanInput" class="form-control form-control-sm" min="1" max="16" value="3">
                    <small class="text-muted">Standar: 3 pertemuan/bulan</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Tanggal Awal Cut-Off <span class="text-danger">*</span></label>
                    <input type="date" name="start_date_cutoff" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">Tanggal Akhir Cut-Off <span class="text-danger">*</span></label>
                    <input type="date" name="end_date_cutoff" class="form-control form-control-sm" value="{{ date('Y-m-t') }}" required>
                </div>
            </div>
        </div>

        <div class="alert alert-info py-2 px-3 mt-3 mb-0 small">
            <i class="fas fa-info-circle mr-1"></i> Setelah periode dibuat, lembar kroscek akan dibuat dalam keadaan <strong>kosong</strong>. Anda dapat menambahkan dosen-dosen penerima honorarium secara langsung di lembar kroscek.
        </div>

        <hr class="my-3">

        {{-- Susunan Approval Bertingkat --}}
        <h6 class="font-weight-bold text-dark mb-2">
            <i class="fas fa-user-check text-primary mr-1"></i> Susunan Validator & Approval
        </h6>
        <div class="form-group row mb-2">
            <label class="col-sm-4 col-form-label small font-weight-bold">1. Validator 1 <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select name="validator_1_id" class="form-control form-control-sm select2-modal" required>
                    <option value="">-- Pilih Pegawai Validator 1 --</option>
                    @foreach($dosenTendiks as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }})</option>
                    @endforeach
                </select>
                <small class="text-muted">Pemeriksa awal berkas & keabsahan data.</small>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-4 col-form-label small font-weight-bold">2. Validator 2 <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select name="validator_2_id" class="form-control form-control-sm select2-modal" required>
                    <option value="">-- Pilih Pegawai Validator 2 --</option>
                    @foreach($dosenTendiks as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }})</option>
                    @endforeach
                </select>
                <small class="text-muted">Pemeriksa lanjutan berkas.</small>
            </div>
        </div>
        <div class="form-group row mb-2">
            <label class="col-sm-4 col-form-label small font-weight-bold">3. Approval Final <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select name="approval_id" class="form-control form-control-sm select2-modal" required>
                    <option value="">-- Pilih Pimpinan Approval --</option>
                    @foreach($dosenTendiks as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->nip ?? $d->nik ?? '-' }})</option>
                    @endforeach
                </select>
                <small class="text-muted">Pimpinan tertinggi yang berwenang menyetujui & mengunci berkas.</small>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success btn-sm font-weight-bold" id="btnSubmitCreateHonor">
            <i class="fas fa-save mr-1"></i> Buat Periode & Lanjut Kroscek
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
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

        // Submit form
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
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Buat Periode & Lanjut Kroscek');
                        alert(res.message || 'Berhasil dibuat.');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Buat Periode & Lanjut Kroscek');
                    var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                    var errorMsg = 'Gagal membuat periode honorarium.';
                    if (errors) {
                        errorMsg = Object.values(errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        });
    });
</script>
