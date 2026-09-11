<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold d-flex align-items-center">
        @if($dokumen->is_pdf)
            <i class="fas fa-file-pdf mr-2 text-danger"></i>
        @elseif($dokumen->is_image)
            <i class="fas fa-file-image mr-2 text-success"></i>
        @else
            <i class="fas fa-file-alt mr-2 text-warning"></i>
        @endif
        <span>{{ $dokumen->masterJenis ? $dokumen->masterJenis->nama_dokumen : $dokumen->nama_berkas }}</span>
        <small class="badge badge-light ml-2 text-dark font-weight-normal">{{ $dokumen->pegawai->nama ?? '' }}</small>
    </h5>
    <div class="ml-auto d-flex align-items-center">
        <a href="{{ $dokumen->file_url }}" target="_blank" class="btn btn-sm btn-outline-light mr-2" title="Buka di Tab Baru">
            <i class="fas fa-external-link-alt mr-1"></i> Buka Fullscreen
        </a>
        <a href="{{ $dokumen->file_url }}" download class="btn btn-sm btn-light text-dark font-weight-bold mr-2" title="Unduh File">
            <i class="fas fa-download mr-1"></i> Unduh
        </a>
        <button type="button" class="close text-white ml-1" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

<div class="modal-body p-0 bg-dark" style="min-height: 520px; max-height: 80vh; overflow: auto; display: flex; align-items: center; justify-content: center;">
    @if($dokumen->is_pdf)
        <iframe src="{{ $dokumen->file_url }}#toolbar=1&navpanes=0&scrollbar=1" 
                style="width: 100%; height: 75vh; border: none;" 
                title="Preview PDF Dokumen">
            <div class="p-4 text-white text-center">
                <p>Browser Anda tidak mendukung preview PDF langsung.</p>
                <a href="{{ $dokumen->file_url }}" target="_blank" class="btn btn-primary">Buka / Unduh File</a>
            </div>
        </iframe>
    @elseif($dokumen->is_image)
        <div class="p-3 text-center w-100">
            <img src="{{ $dokumen->file_url }}" 
                 alt="{{ $dokumen->nama_berkas }}" 
                 class="img-fluid rounded shadow" 
                 style="max-height: 75vh; object-fit: contain;">
        </div>
    @else
        <div class="p-5 text-center text-white">
            <i class="fas fa-file-download fa-4x mb-3 text-warning"></i>
            <h5>Preview tidak tersedia untuk format berkas ini (.{{ $dokumen->file_extension }})</h5>
            <p class="text-muted">Silakan unduh berkas untuk melihat isinya.</p>
            <a href="{{ $dokumen->file_url }}" download class="btn btn-info mt-2">
                <i class="fas fa-download mr-1"></i> Unduh Berkas
            </a>
        </div>
    @endif
</div>

<div class="modal-footer bg-light py-2 px-3 justify-content-between">
    <div class="small text-muted">
        @if($dokumen->nomor_dokumen)
            <span class="mr-3"><strong>No. Dokumen:</strong> {{ $dokumen->nomor_dokumen }}</span>
        @endif
        @if($dokumen->tanggal_dokumen)
            <span class="mr-3"><strong>Tgl:</strong> {{ \Carbon\Carbon::parse($dokumen->tanggal_dokumen)->format('d/m/Y') }}</span>
        @endif
        <span><strong>Ukuran:</strong> {{ $dokumen->formatted_size }}</span>
    </div>
    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
