<div class="modal-header" style="background-color: #047857; color: #fff;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-calendar-plus mr-2"></i>Perpanjang Kontrak Kerja (PKWT)
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formPerpanjangKontrak" enctype="multipart/form-data">
    @csrf
    <div class="modal-body p-4">
        <!-- Callout Info Kontrak Lama -->
        <div class="alert alert-info border-0 shadow-sm mb-4" style="background-color: #e6f4ea; color: #137333; border-radius: 8px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-history fa-2x mr-3"></i>
                <div>
                    <div class="font-weight-bold" style="font-size: 1.05rem;">
                        {{ $kontrakLama->pegawai->nama }}
                    </div>
                    <div class="small">
                        Kontrak Berjalan: <strong>{{ $kontrakLama->no_kontrak }}</strong> (Kontrak Ke-{{ $kontrakLama->kontrak_ke }})
                    </div>
                    <div class="small">
                        Berakhir pada: <strong>{{ \Carbon\Carbon::parse($kontrakLama->tgl_selesai)->format('d F Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Nomor Kontrak Baru <span class="text-danger">*</span></label>
                    <input type="text" name="no_kontrak" class="form-control" placeholder="Contoh: 089/PKWT/HRD-TSU/X/2026" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Urutan Kontrak</label>
                    <input type="number" class="form-control bg-light" value="{{ $saranKe }}" readonly>
                    <small class="text-muted">Otomatis Kontrak Ke-{{ $saranKe }}</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Tanggal Mulai Baru <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_mulai" class="form-control" value="{{ $saranMulai }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Tanggal Selesai Baru <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_selesai" class="form-control" value="{{ $saranSelesai }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Jabatan / Posisi</label>
                    <input type="text" name="posisi" class="form-control" value="{{ $kontrakLama->posisi }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold text-dark mb-1">Gaji Pokok Disepakati (Rp)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light font-weight-bold text-muted">Rp</span>
                        </div>
                        <input type="number" name="gaji_pokok_disepakati" id="inputGajiPokokPerpanjang" class="form-control" value="{{ $kontrakLama->gaji_pokok_disepakati }}" placeholder="Contoh: 4500000" min="0">
                        @if(isset($estimasiGapok) && $estimasiGapok > 0)
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-success" id="btnPakaiMasterGapokPerpanjang" data-gapok="{{ $estimasiGapok }}" title="Gunakan tarif standar Master">
                                <i class="fas fa-magic mr-1"></i> Gunakan Standar
                            </button>
                        </div>
                        @endif
                    </div>
                    @if(isset($estimasiGapok) && $estimasiGapok > 0)
                    <div class="small mt-1">
                        <span class="badge badge-info py-1 px-2">
                            <i class="fas fa-layer-group mr-1"></i> Standar Master ({{ $estimasiGolongan ?? '-' }}): <strong>Rp {{ number_format($estimasiGapok, 0, ',', '.') }}</strong>
                        </span>
                    </div>
                    @endif
                    <small class="text-muted d-block"><i class="fas fa-info-circle mr-1"></i>Terintegrasi langsung ke slip Payroll bulanan.</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Upload Scan Dokumen Kontrak Baru (PDF)</label>
            <div class="custom-file">
                <input type="file" name="dokumen_kontrak" class="custom-file-input" id="customFilePerpanjang" accept=".pdf">
                <label class="custom-file-label" for="customFilePerpanjang" id="labelFilePerpanjang">Pilih file PDF baru (Maks. 5MB)...</label>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan Perpanjangan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan evaluasi kinerja atau alasan perpanjangan..."></textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success" id="btnSubmitPerpanjang" style="background-color: #047857; border-color: #047857;">
            <i class="fas fa-check-circle mr-1"></i> Simpan & Perpanjang Kontrak
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#btnPakaiMasterGapokPerpanjang').on('click', function() {
        const gapok = $(this).data('gapok');
        if (gapok) {
            $('#inputGajiPokokPerpanjang').val(gapok);
        }
    });

    $('#customFilePerpanjang').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#labelFilePerpanjang').addClass("selected").html(fileName || 'Pilih file PDF...');
    });

    $('#formPerpanjangKontrak').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnSubmitPerpanjang');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        var formData = new FormData(this);
        const url = "{{ route('admin.employee-lifecycle.kontrak.store-perpanjang', $kontrakLama->id) }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(res) {
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
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Simpan & Perpanjang Kontrak');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Simpan & Perpanjang Kontrak');
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
