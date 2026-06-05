<form id="form-update-riwayat" action="{{ route('admin.data-karyawan.update-riwayat', $riwayat->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="modal-header bg-warning">
        <h5 class="modal-title font-weight-bold text-dark">
            <i class="fas fa-pencil-alt mr-2"></i> Edit Riwayat Jabatan
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4">
        <div class="alert alert-light border shadow-sm mb-4">
            <div class="font-weight-bold">{{ $riwayat->tipe_jabatan === 'struktural' ? ($riwayat->jabatanStruktural->nama_jabatan ?? 'Unknown') : ($riwayat->jabatanFungsional->nama_jabatan ?? 'Unknown') }}</div>
            <div class="small text-muted mt-1">Pegawai: {{ $riwayat->dataDosenTendik->nama }}</div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tgl_mulai" class="form-control" value="{{ \Carbon\Carbon::parse($riwayat->tgl_mulai)->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" class="form-control" value="{{ $riwayat->tgl_selesai ? \Carbon\Carbon::parse($riwayat->tgl_selesai)->format('Y-m-d') : '' }}">
                <small class="form-text text-muted">Kosongkan jika masih menjabat sampai sekarang.</small>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan Mutasi</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Serah terima jabatan ke pegawai lain...">{{ $riwayat->keterangan }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary mr-auto shadow-sm btn-back-to-riwayat" data-url="{{ route('admin.data-karyawan.riwayat', $riwayat->data_dosen_tendik_id) }}">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Timeline
        </button>
        <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary shadow-sm" id="btn-submit-update-riwayat">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Tombol Kembali ke Timeline
    $('.btn-back-to-riwayat').on('click', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Timeline...</p></div>`);
        $.get(url, function(res) {
            $('#modal-edit-content').html(res);
        });
    });

    // Submit Edit
    $('#form-update-riwayat').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#btn-submit-update-riwayat');
        let originalText = btn.html();
        let urlBack = $('.btn-back-to-riwayat').data('url');

        btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);
        
        pikdiAjax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            onSuccess: function(res) {
                // Balik ke timeline
                $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Ulang Timeline...</p></div>`);
                $.get(urlBack, function(html) {
                    $('#modal-edit-content').html(html);
                });
            },
            onError: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
