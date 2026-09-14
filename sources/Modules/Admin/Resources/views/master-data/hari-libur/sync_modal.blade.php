<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); padding: 1.1rem 1.4rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-cloud-download-alt mr-2 text-warning"></i> Sinkronisasi Kalender Libur Nasional
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form action="{{ route('admin.hari-libur.sync') }}" method="POST" id="form-sync-api">
    @csrf
    <div class="modal-body p-4">
        <div class="p-3 rounded mb-3" style="background: rgba(180, 83, 9, 0.06); border-left: 4px solid #d97706;">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle mr-2 mt-1" style="color: #b45309; font-size: 1rem;"></i>
                <div class="text-sm text-dark">
                    Pilih tahun kalender yang ingin disinkronkan langsung dari server data hari libur nasional resmi. Data yang ada akan otomatis diperbarui.
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-sm text-dark mb-2">Pilih Tahun Sinkronisasi:</label>
            
            <div class="p-2 px-3 rounded mb-2 border" style="background: #f8fafc;">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="sync_tahun_ini" name="opsi_tahun" value="tahun_ini" checked>
                    <label for="sync_tahun_ini" class="custom-control-label font-weight-bold text-dark text-sm" style="cursor: pointer;">
                        Tahun Berjalan ({{ date('Y') }})
                    </label>
                </div>
            </div>

            <div class="p-2 px-3 rounded mb-2 border" style="background: #f8fafc;">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="sync_tahun_depan" name="opsi_tahun" value="tahun_depan">
                    <label for="sync_tahun_depan" class="custom-control-label font-weight-bold text-dark text-sm" style="cursor: pointer;">
                        Tahun Depan ({{ date('Y', strtotime('+1 year')) }})
                    </label>
                </div>
            </div>

            <div class="p-2 px-3 rounded border" style="background: #f8fafc;">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="sync_custom" name="opsi_tahun" value="custom">
                    <label for="sync_custom" class="custom-control-label font-weight-bold text-dark text-sm" style="cursor: pointer;">
                        Tahun Spesifik (Kustom)
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group mt-3 d-none" id="div_tahun_custom">
            <label class="font-weight-bold text-sm text-dark">Masukkan Angka Tahun (YYYY) <span class="text-danger">*</span></label>
            <input type="number" name="tahun_custom" id="input_tahun_custom" class="form-control" placeholder="Contoh: 2027" min="2020" max="2100" style="border-radius: 8px;">
        </div>
    </div>

    <div class="modal-footer justify-content-between p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn tsu-btn-primary-action px-4" id="btn-submit-sync" onclick="tampilkanLoading()">
            <span id="text-sync-btn"><i class="fas fa-cloud-download-alt mr-1"></i> Tarik Data Sekarang</span>
        </button>
    </div>
</form>

<script>
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
            $('#text-sync-btn').html('<i class="fas fa-spinner fa-spin mr-1"></i> Sedang Menarik Data...');
            form.submit();
        }
    }
</script>
