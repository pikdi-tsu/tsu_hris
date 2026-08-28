<div class="modal-header bg-warning">
    <h5 class="modal-title text-dark"><i class="fas fa-cloud-download-alt"></i> Sinkronisasi Kalender Nasional</h5>
    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.hari-libur.sync') }}" method="POST" id="form-sync-api">
    @csrf
    <div class="modal-body p-4">
        <div class="alert alert-warning bg-warning border-0 text-dark mb-4 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i>
            Pilih rentang tahun data libur nasional yang ingin ditarik dari server pemerintah.
        </div>

        <div class="form-group">
            <div class="custom-control custom-radio mb-2">
                <input class="custom-control-input" type="radio" id="sync_tahun_ini" name="opsi_tahun" value="tahun_ini" checked>
                <label for="sync_tahun_ini" class="custom-control-label font-weight-normal">
                    Tahun Ini ({{ date('Y') }})
                </label>
            </div>
            <div class="custom-control custom-radio mb-2">
                <input class="custom-control-input" type="radio" id="sync_tahun_depan" name="opsi_tahun" value="tahun_depan">
                <label for="sync_tahun_depan" class="custom-control-label font-weight-normal">
                    Tahun Depan ({{ date('Y', strtotime('+1 year')) }})
                </label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="sync_custom" name="opsi_tahun" value="custom">
                <label for="sync_custom" class="custom-control-label font-weight-normal">
                    Tahun Spesifik (Kustom)
                </label>
            </div>
        </div>

        <div class="form-group mt-3 d-none" id="div_tahun_custom">
            <input type="number" name="tahun_custom" id="input_tahun_custom" class="form-control border-warning shadow-sm" placeholder="Contoh: 2024" min="2020" max="2100">
        </div>
    </div>

    <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-warning font-weight-bold shadow-sm" id="btn-submit-sync" onclick="tampilkanLoading()">
            <span id="text-sync-btn"><i class="fas fa-sync-alt"></i> Tarik Data Sekarang</span>
        </button>
    </div>
</form>

<script>
    // Logic radio custom pindah ke sini agar jalan saat dipanggil AJAX
    $('input[type=radio][name=opsi_tahun]').change(function() {
        if (this.value === 'custom') {
            $('#div_tahun_custom').removeClass('d-none');
            $('#input_tahun_custom').prop('required', true).focus();
        } else {
            $('#div_tahun_custom').addClass('d-none');
            $('#input_tahun_custom').prop('required', false);
        }
    });

    function tampilkanLoading() {
        let btn = $('#btn-submit-sync');
        let form = $('#form-sync-api')[0];
        if (form.checkValidity()) {
            btn.prop('disabled', true);
            $('#text-sync-btn').html('<i class="fas fa-spinner fa-spin"></i> Menarik Data...');
            form.submit();
        }
    }
</script>
