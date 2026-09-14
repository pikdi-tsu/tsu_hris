<div class="modal-header" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%); color: #ffffff; border-bottom: none; padding: 1.1rem 1.5rem;">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-clipboard-check"></i> {{ $title }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="form-onoff" action="{{ $action }}" method="POST">
    @csrf
    @if(isset($method) && in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-body p-4 bg-white">
        <div class="form-group">
            <label class="font-weight-bold text-dark">Nama Tugas / Aktivitas <span class="text-danger">*</span></label>
            <input type="text" name="nama_tugas" class="form-control" value="{{ old('nama_tugas', $item->nama_tugas) }}" required placeholder="Contoh: Pembuatan Email Resmi TSU (@tau.ac.id)">
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Kategori <span class="text-danger">*</span></label>
                <select name="kategori" class="form-control" required>
                    <option value="onboarding" {{ old('kategori', $item->kategori) == 'onboarding' ? 'selected' : '' }}>Onboarding (Pegawai Baru)</option>
                    <option value="offboarding" {{ old('kategori', $item->kategori) == 'offboarding' ? 'selected' : '' }}>Offboarding (Pegawai Resign)</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Sasaran Pegawai <span class="text-danger">*</span></label>
                <select name="sasaran" class="form-control" required>
                    <option value="semua" {{ old('sasaran', $item->sasaran) == 'semua' ? 'selected' : '' }}>Semua Pegawai (Dosen & Tendik)</option>
                    <option value="dosen" {{ old('sasaran', $item->sasaran) == 'dosen' ? 'selected' : '' }}>Khusus Dosen</option>
                    <option value="tendik" {{ old('sasaran', $item->sasaran) == 'tendik' ? 'selected' : '' }}>Khusus Tendik</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Nomor Urut Tampil</label>
                <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $item->urutan ?? 1) }}" min="0" placeholder="1, 2, 3...">
            </div>
            <div class="col-md-6 form-group d-flex align-items-center mt-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-dark" for="is_active">Status Tugas Aktif</label>
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Keterangan / Panduan Pelaksanaan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan petunjuk langkah kerja...">{{ old('keterangan', $item->keterangan) }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light" style="border-top: 1px solid #edf2f7; padding: 0.9rem 1.5rem;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn text-white font-weight-bold px-4" style="background-color: #094b54; border-color: #094b54; border-radius: 8px;">
            <i class="fas fa-save mr-1"></i> Simpan
        </button>
    </div>
</form>
