<div class="modal-header bg-info text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-folder-plus mr-2 text-warning"></i> {{ $title }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-jenis-dokumen" action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="modal-body p-4 bg-white">
        <div class="form-group mb-3">
            <label class="font-weight-bold">Nama Jenis Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="nama_dokumen" class="form-control" value="{{ old('nama_dokumen', $item->nama_dokumen) }}" placeholder="Contoh: Surat Perjanjian Kerja (SPK) Yayasan" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold">Kode Dokumen</label>
                <input type="text" name="kode_dokumen" class="form-control" value="{{ old('kode_dokumen', $item->kode_dokumen) }}" placeholder="Contoh: SPK_YAYASAN" style="text-transform: uppercase;">
                <small class="text-muted">Opsional, otomatis dari nama dokumen jika dikosongkan.</small>
            </div>
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold">Urutan Tampilan</label>
                <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $item->urutan ?? 0) }}" min="0">
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold">Deskripsi / Petunjuk Pengunggahan</label>
            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Petunjuk ringkas untuk karyawan yang mengunggah">{{ old('deskripsi', $item->deskripsi) }}</textarea>
        </div>

        <div class="row border-top pt-3 mt-2">
            <div class="col-md-6 form-group mb-0">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_wajib" name="is_wajib" value="1" {{ old('is_wajib', $item->is_wajib) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-dark" for="is_wajib">Wajib Dilengkapi Karyawan</label>
                </div>
                <small class="text-muted d-block">Centang jika dokumen ini mandatory bagi profil dosen/tendik.</small>
            </div>
            <div class="col-md-6 form-group mb-0">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-dark" for="is_active">Status Aktif</label>
                </div>
                <small class="text-muted d-block">Hanya jenis dokumen aktif yang muncul di pilihan upload.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-info px-4 font-weight-bold" style="background: var(--tsu-primary, #1d7a87); border-color: var(--tsu-primary, #1d7a87);">
            <i class="fas fa-save mr-1"></i> Simpan
        </button>
    </div>
</form>
