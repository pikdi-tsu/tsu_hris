<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-file-signature mr-2 text-warning"></i> Form Permohonan Surat ke SDM
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-user-request-surat" action="{{ route('admin.request-surat.user-store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="modal-body p-4 bg-white">
        {{-- Identitas Pemohon --}}
        <div class="alert alert-light border mb-4 py-2 px-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase font-weight-bold d-block">Pemohon:</small>
                <strong>{{ $karyawan->nama ?? auth()->user()->name }}</strong> 
                <span class="text-muted font-weight-normal">({{ $karyawan->nik ?? '-' }})</span>
            </div>
            <div>
                <span class="badge badge-info px-2 py-1">{{ $karyawan->tipe_karyawan ?? 'Pegawai' }}</span>
                <span class="badge badge-light border">{{ $karyawan->unit->nama_unit ?? '-' }}</span>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Jenis Surat yang Dimohonkan <span class="text-danger">*</span></label>
            <select name="jenis_surat" id="select_jenis_surat" class="form-control" required>
                <option value="" data-perlu-lampiran="0" data-deskripsi="">-- Pilih Jenis Surat --</option>
                @if(isset($masterJenisSurat) && count($masterJenisSurat) > 0)
                    @foreach($masterJenisSurat as $mjs)
                        <option value="{{ $mjs->nama_surat }}" 
                            data-perlu-lampiran="{{ $mjs->perlu_lampiran ? '1' : '0' }}" 
                            data-deskripsi="{{ $mjs->deskripsi }}">
                            {{ $mjs->nama_surat }} {{ $mjs->perlu_lampiran ? '(Wajib Berkas Lampiran)' : '' }}
                        </option>
                    @endforeach
                @else
                    <option value="Surat Keterangan Kerja Aktif">Surat Keterangan Kerja Aktif</option>
                    <option value="Surat Pengantar Pengajuan KPR / Bank">Surat Pengantar Pengajuan KPR / Bank</option>
                    <option value="Surat Pengantar Studi Lanjut / Beasiswa">Surat Pengantar Studi Lanjut / Beasiswa</option>
                    <option value="Surat Keterangan Visa / Pembuatan Paspor">Surat Keterangan Visa / Pembuatan Paspor</option>
                    <option value="Surat Rekomendasi Kepegawaian">Surat Rekomendasi Kepegawaian</option>
                    <option value="Surat Keterangan Bebas Tanggungan">Surat Keterangan Bebas Tanggungan</option>
                    <option value="Surat Keterangan Lainnya">Surat Keterangan Lainnya</option>
                @endif
            </select>
            <div id="jenis-surat-hint" class="small text-info mt-1" style="display: none;"></div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Keperluan Pembuatan Surat <span class="text-danger">*</span></label>
            <textarea name="keperluan" class="form-control" rows="3" placeholder="Jelaskan secara spesifik tujuan pembuatan surat (Contoh: Pengajuan KPR Rumah di Bank Mandiri Cabang Merr Surabaya)" required></textarea>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Keterangan Tambahan / Format Khusus</label>
            <textarea name="keterangan_tambahan" class="form-control" rows="2" placeholder="Catatan tambahan jika ada data khusus yang wajib dicantumkan dalam surat (opsional)"></textarea>
        </div>

        <div class="form-group mb-2">
            <label class="font-weight-bold text-dark" id="label-lampiran">Berkas Lampiran Pendukung <small class="text-muted">(Opsional)</small></label>
            <input type="file" name="file_lampiran" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted d-block mt-1">Unggah berkas pendukung jika disyaratkan (Contoh: Brosur beasiswa, draft formulir bank, dsb. Format PDF/JPG/PNG maksimal 10 MB).</small>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-info px-4 font-weight-bold" style="background: var(--tsu-primary, #1d7a87); border-color: var(--tsu-primary, #1d7a87);">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan
        </button>
    </div>
</form>

<script>
    $('#select_jenis_surat').on('change', function() {
        var opt = $(this).find('option:selected');
        var desc = opt.data('deskripsi');
        var perluLampiran = opt.data('perlu-lampiran') == '1';

        if (desc) {
            $('#jenis-surat-hint').html('<i class="fas fa-info-circle mr-1"></i> ' + desc).show();
        } else {
            $('#jenis-surat-hint').hide();
        }

        if (perluLampiran) {
            $('#label-lampiran').html('Berkas Lampiran Pendukung <span class="text-danger font-weight-bold">* (Wajib Diunggah)</span>');
            $('input[name="file_lampiran"]').prop('required', true);
        } else {
            $('#label-lampiran').html('Berkas Lampiran Pendukung <small class="text-muted">(Opsional)</small>');
            $('input[name="file_lampiran"]').prop('required', false);
        }
    });
</script>
