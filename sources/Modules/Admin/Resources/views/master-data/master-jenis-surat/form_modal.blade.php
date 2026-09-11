<form id="form-master-jenis-surat" action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="modal-header bg-info text-white">
        <h5 class="modal-title font-weight-bold">
            <i class="fas fa-envelope-open-text mr-1"></i> {{ $title }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4">
        <div class="form-group mb-3">
            <label class="font-weight-bold">Nama Surat yang Dimohonkan <span class="text-danger">*</span></label>
            <input type="text" name="nama_surat" class="form-control" value="{{ old('nama_surat', $item->nama_surat) }}" 
                placeholder="Contoh: Surat Keterangan Kerja Aktif" required>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold">Kode Singkatan</label>
                <input type="text" name="kode_surat" class="form-control" value="{{ old('kode_surat', $item->kode_surat) }}" 
                    placeholder="Contoh: SKK, KPR, VISA">
                <small class="text-muted">Kode identifikasi atau klasifikasi arsip surat.</small>
            </div>
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold">Nomor Urutan Tampilan</label>
                <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $item->urutan ?? 0) }}" min="0">
                <small class="text-muted">Urutan pada dropdown pilihan pegawai.</small>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="font-weight-bold">Deskripsi / Petunjuk untuk Pegawai</label>
            <textarea name="deskripsi" class="form-control" rows="3" 
                placeholder="Tuliskan petunjuk pengajuan atau berkas yang diperlukan jika ada...">{{ old('deskripsi', $item->deskripsi) }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold">Kewajiban Berkas Lampiran <span class="text-danger">*</span></label>
                <select name="perlu_lampiran" class="form-control" required>
                    <option value="0" {{ (string)old('perlu_lampiran', $item->perlu_lampiran) === '0' ? 'selected' : '' }}>
                        Opsional (Tidak wajib unggah berkas)
                    </option>
                    <option value="1" {{ (string)old('perlu_lampiran', $item->perlu_lampiran) === '1' ? 'selected' : '' }}>
                        Wajib (Pegawai wajib lampirkan dokumen)
                    </option>
                </select>
                <small class="text-muted">Contoh: Visa/Beasiswa wajib melampirkan berkas undangan/formulir.</small>
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold">Status Aktif <span class="text-danger">*</span></label>
                <select name="is_active" class="form-control" required>
                    <option value="1" {{ (string)old('is_active', $item->is_active ?? 1) === '1' ? 'selected' : '' }}>
                        Aktif (Ditampilkan di form permohonan)
                    </option>
                    <option value="0" {{ (string)old('is_active', $item->is_active ?? 1) === '0' ? 'selected' : '' }}>
                        Non-Aktif (Disembunyikan)
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-info font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Jenis Surat
        </button>
    </div>
</form>
