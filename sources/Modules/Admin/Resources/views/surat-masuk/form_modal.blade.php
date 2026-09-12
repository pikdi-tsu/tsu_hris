<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-file-import mr-2 text-warning"></i> Registrasi Surat Masuk Eksternal (SIKD)
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formSuratMasuk" action="{{ route('admin.surat-masuk.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body p-4 bg-white">
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Nomor Agenda SIKD <span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-light" name="no_agenda" value="{{ $noAgendaOtomatis }}" readonly>
                <small class="form-text text-muted">Nomor agenda otomatis dari sistem tata persuratan SIKD.</small>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Nomor Surat dari Instansi Pengirim <span class="text-danger">*</span></label>
                <input type="text" name="no_surat_asal" class="form-control" placeholder="Contoh: 015/DIR-LOG/VIII/2026" required>
                <small class="form-text text-muted">Nomor surat yang tertera pada dokumen asli pengirim.</small>
            </div>

            <div class="col-md-8 form-group">
                <label class="font-weight-bold text-dark">Instansi / Perusahaan Pengirim <span class="text-danger">*</span></label>
                <input type="text" name="pengirim_instansi" class="form-control" placeholder="Contoh: PT. Sumber Sarana Pengadaan / Kemendikbudristek" required>
            </div>
            <div class="col-md-4 form-group">
                <label class="font-weight-bold text-dark">Sifat Surat <span class="text-danger">*</span></label>
                <select name="sifat_surat" class="form-control" required>
                    <option value="biasa" selected>Biasa</option>
                    <option value="penting">Penting</option>
                    <option value="segera">Segera / Mendesak</option>
                    <option value="rahasia">Rahasia</option>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Tanggal Surat Pengirim <span class="text-danger">*</span></label>
                <input type="date" name="tgl_surat" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold text-dark">Tanggal Surat Diterima di Kampus <span class="text-danger">*</span></label>
                <input type="date" name="tgl_diterima" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-12 form-group">
                <label class="font-weight-bold text-dark">Perihal Surat Masuk <span class="text-danger">*</span></label>
                <input type="text" name="perihal" class="form-control" placeholder="Contoh: Penawaran Pengadaan Perangkat Laboratorium Komputer" required>
            </div>

            <div class="col-12 form-group">
                <label class="font-weight-bold text-dark">Ringkasan Isi / Catatan Surat <span class="text-muted small">(Opsional)</span></label>
                <textarea name="ringkasan_isi" class="form-control" rows="3" placeholder="Tuliskan ringkasan singkat atau poin-poin utama dari surat ini..."></textarea>
            </div>

            <div class="col-12 form-group mb-0">
                <label class="font-weight-bold text-dark">Unggah Berkas Scan Surat Masuk (PDF) <span class="text-danger">*</span></label>
                <div class="custom-file">
                    <input type="file" name="file_surat" class="custom-file-input" id="file_surat_masuk" accept=".pdf" required>
                    <label class="custom-file-label" for="file_surat_masuk">Pilih file scan surat masuk (PDF max 15MB)...</label>
                </div>
                <small class="form-text text-muted">File PDF hasil scan surat fisik beserta lampirannya.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary" id="btnSubmitSuratMasuk">
            <i class="fas fa-save mr-1"></i> Simpan Registrasi Surat
        </button>
    </div>
</form>
