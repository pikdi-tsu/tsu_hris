<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-file-signature mr-2 text-warning"></i> Penyelesaian SK Rektorat & Softfile (Tiket: {{ $surat->nomor_tiket }})
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<form id="formSekretariatSelesai" action="{{ route('admin.request-surat.sekretariat-store-selesai', $surat->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body p-4 bg-white">
        {{-- Info Permohonan --}}
        <div class="card card-outline card-info mb-3">
            <div class="card-body py-2 px-3 bg-light">
                <div class="row small">
                    <div class="col-md-6">
                        <span class="text-muted font-weight-bold">Pemohon:</span>
                        <div class="font-weight-bold text-dark">{{ $surat->pegawai->nama_lengkap ?? '-' }}</div>
                        <div class="text-muted">{{ $surat->pegawai->unit->nama_unit ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted font-weight-bold">Jenis Permohonan:</span>
                        <div class="font-weight-bold text-primary">{{ $surat->jenis_surat }}</div>
                        <div class="text-muted">{{ $surat->keperluan }}</div>
                    </div>
                </div>
                @if($surat->catatan_terusan)
                <div class="mt-2 pt-2 border-top">
                    <span class="text-muted font-weight-bold"><i class="fas fa-comment-dots mr-1 text-info"></i> Catatan Permintaan dari SDM:</span>
                    <div class="font-italic text-dark mt-1">"{{ $surat->catatan_terusan }}"</div>
                </div>
                @endif
                @if($surat->file_lampiran_url)
                <div class="mt-2">
                    <a href="{{ $surat->file_lampiran_url }}" target="_blank" class="btn btn-xs btn-outline-info">
                        <i class="fas fa-paperclip mr-1"></i> Buka Lampiran Berkas dari Pemohon/SDM
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Form Fields --}}
        <div class="form-group">
            <label class="font-weight-bold text-dark">Nomor Surat Keputusan (SK) / Surat Rektorat <span class="text-danger">*</span></label>
            <input type="text" name="nomor_surat_keluar" class="form-control" placeholder="Contoh: 089/SK-REK/TSU/IX/2026" required>
            <small class="form-text text-muted">Masukkan nomor registrasi SK resmi yang telah diterbitkan oleh Sekretariat Rektorat.</small>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Unggah Softfile SK Resmi (PDF) <span class="text-danger">*</span></label>
            <div class="custom-file">
                <input type="file" name="file_surat_hasil" class="custom-file-input" id="file_sk_hasil" accept=".pdf" required>
                <label class="custom-file-label" for="file_sk_hasil">Pilih file scan SK (PDF max 15MB)...</label>
            </div>
            <small class="form-text text-muted">Softfile scan SK bertandatangan Rektorat dalam format PDF. Softfile ini akan otomatis dapat diunduh oleh SDM dan pemohon.</small>
        </div>

        <div class="form-group">
            <label class="font-weight-bold text-dark">Status Hardfile / Berkas Fisik Bertandatangan Basah <span class="text-danger">*</span></label>
            <select name="status_hardfile" class="form-control" required>
                <option value="siap_diambil" selected>Siap Diambil di Sekretariat Rektorat (SDM mengambil berkas fisik)</option>
                <option value="telah_diterima_sdm">Telah Diserahkan Langsung ke Pihak SDM</option>
            </select>
            <small class="form-text text-muted">Pilih apakah berkas fisik bertandatangan asli/cap basah siap diambil oleh SDM di Sekretariat atau sudah diserahkan.</small>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Catatan dari Sekretariat Rektorat <span class="text-muted small">(Opsional)</span></label>
            <textarea name="catatan_sekretariat" class="form-control" rows="3" placeholder="Contoh: SK asli rangkap 2 sudah ditandatangani Rektor dan diberi cap basah. Silakan diambil di meja Sekretariat Rektorat."></textarea>
        </div>
    </div>

    <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Batal
        </button>
        <button type="submit" class="btn btn-success" id="btnSubmitSekretariat">
            <i class="fas fa-check-circle mr-1"></i> Terbitkan SK & Selesaikan
        </button>
    </div>
</form>
