<div class="modal-header bg-info text-white">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-share mr-2"></i> Teruskan / Disposisi Surat: {{ $surat->nomor_tiket }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-admin-teruskan-surat" action="{{ route('admin.request-surat.admin-store-teruskan', $surat->id) }}" method="POST">
    @csrf

    <div class="modal-body p-4 bg-white">
        <div class="alert alert-light border py-2 px-3 mb-3">
            <div class="small text-muted font-weight-bold">PEMOHON:</div>
            <strong>{{ $surat->pegawai->nama ?? '-' }}</strong> ({{ $surat->pegawai->nik ?? '-' }}) — 
            <span class="text-primary font-weight-bold">{{ $surat->jenis_surat }}</span>
        </div>

        <div class="alert alert-info border-0 small">
            <i class="fas fa-info-circle mr-1"></i> Surat dapat diteruskan ke Sekretariat/Pimpinan untuk bantuan cetak fisik, tanda tangan basah, maupun stempel. Berkas hasil jadi tetap dapat diunggah nanti oleh pihak penerima maupun Admin SDM.
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark">Teruskan Kepada (Staf / Sekretariat / Pimpinan) <span class="text-danger">*</span></label>
            <select name="diteruskan_ke" class="form-control select2" required style="width: 100%;">
                <option value="">-- Pilih Staf / Sekretariat Penerima --</option>
                @foreach($staffList as $staff)
                    <option value="{{ $staff->id }}" {{ $surat->diteruskan_ke == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }} ({{ $staff->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan Instruksi / Disposisi <span class="text-danger">*</span></label>
            <textarea name="catatan_terusan" class="form-control" rows="3" placeholder="Contoh: Mohon bantuan cetak draft terlampir dan mintakan tanda tangan basah/stempel rektorat..." required minlength="3">{{ $surat->catatan_terusan }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-info px-4 font-weight-bold text-white">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Terusan Surat
        </button>
    </div>
</form>
