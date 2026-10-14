<form action="{{ $action }}" method="POST" id="formPolicy" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="kategori" value="{{ $kategori }}">

    <div class="modal-header text-white" style="background: linear-gradient(135deg, #094B54 0%, #0c6170 100%);">
        <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
            <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
            {{ $isEdit ? 'Edit Dokumen' : 'Tambah Dokumen Baru' }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-4" style="font-size: 0.9rem;">
        {{-- 1. Judul Dokumen --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Judul Dokumen <span class="text-danger">*</span>
            </label>
            <input type="text" name="judul" class="form-control form-control-sm" 
                   value="{{ $item->judul }}" 
                   placeholder="Masukkan judul dokumen kebijakan..." required>
        </div>

        {{-- 2. Unggah Berkas Dokumen --}}
        <div class="form-group mb-3">
            <label class="font-weight-bold text-dark small mb-1">
                Berkas Dokumen @if(!$isEdit) <span class="text-danger">*</span> @endif
            </label>
            <div class="custom-file">
                <input type="file" name="file" class="custom-file-input form-control-sm" id="inputPolicyFile" 
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.png" 
                       {{ !$isEdit ? 'required' : '' }} onchange="updateFileName(this)">
                <label class="custom-file-label small" for="inputPolicyFile" id="labelPolicyFile">
                    {{ $isEdit && $item->file_name ? $item->file_name : 'Pilih berkas dokumen (PDF, Word, Excel, dll)...' }}
                </label>
            </div>
            <small class="text-muted d-block mt-1">
                Format yang didukung: PDF, DOCX, XLSX, PPTX, ZIP, Gambar (Maks. 25 MB).
            </small>

            @if($isEdit && $item->file_path)
                <div class="mt-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between" style="border-radius: 8px;">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <i class="{{ $item->file_icon }} fa-lg"></i>
                        <div>
                            <span class="small font-weight-bold text-dark d-block text-truncate" style="max-width: 250px;">
                                {{ $item->file_name ?: basename($item->file_path) }}
                            </span>
                            <small class="text-muted">{{ $item->formatted_file_size }}</small>
                        </div>
                    </div>
                    <a href="{{ $item->file_url }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2">
                        <i class="fas fa-external-link-alt mr-1"></i> Lihat Berkas
                    </a>
                </div>
                <small class="text-info font-italic mt-1 d-block">
                    * Kosongkan pilihan berkas di atas jika tidak ingin mengubah berkas yang sudah ada.
                </small>
            @endif
        </div>

        {{-- 3. Keterangan / Deskripsi --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">
                Keterangan / Catatan Tambahan
            </label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="3" 
                      placeholder="Tuliskan keterangan, ringkasan, atau instruksi terkait dokumen ini...">{{ $item->keterangan }}</textarea>
        </div>
    </div>

    <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-dismiss="modal">
            Batal
        </button>
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm" id="btnSubmitPolicy" style="background-color: #094B54; border-color: #094B54;">
            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Dokumen' }}
        </button>
    </div>
</form>

<script>
function updateFileName(input) {
    var fileName = input.files[0] ? input.files[0].name : 'Pilih berkas dokumen...';
    document.getElementById('labelPolicyFile').innerText = fileName;
}
</script>
