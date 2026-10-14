<div class="modal-header text-white" style="background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);">
    <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem;">
        <i class="fas fa-eye mr-2"></i> Preview Penandatangan Dokumen
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4 bg-white">
    {{-- Header Info Dokumen Simulasi --}}
    <div class="text-center pb-3 border-bottom mb-4">
        <span class="badge badge-primary px-3 py-1 font-weight-bold mb-2">
            Peruntukan: {{ $item->peruntukan_label }}
        </span>
        <div class="text-muted small">
            Pejabat Penandatangan Dokumen
        </div>
    </div>

    {{-- Kotak Simulasi Format Tanda Tangan Resmi --}}
    <div class="p-4 mx-auto rounded border bg-light shadow-sm" style="max-width: 380px; background-color: #fdfdfd; border-color: #cbd5e1 !important;">
        <div class="text-center" style="font-family: Arial, sans-serif;">
            <div class="small text-muted mb-1" style="font-size: 0.8rem;">
                Surakarta, {{ now()->translatedFormat('d F Y') }}
            </div>
            <div class="font-weight-bold text-dark mb-2" style="font-size: 0.88rem;">
                {{ $item->jabatan }}
            </div>

            {{-- Box TTD & Stempel Overlay --}}
            <div class="position-relative mx-auto my-2 d-flex align-items-center justify-content-center" style="height: 100px; max-width: 260px;">
                {{-- Stempel (Latar Belakang Sedikit Transparan / Geser Kiri) --}}
                @if($item->file_stempel_url)
                    <img src="{{ $item->file_stempel_url }}" 
                         alt="Stempel" 
                         class="position-absolute" 
                         style="max-height: 90px; max-width: 90px; left: 15px; opacity: 0.82; z-index: 1; pointer-events: none;">
                @endif

                {{-- Tanda Tangan (Lapisan Depan) --}}
                @if($item->file_ttd_url)
                    <img src="{{ $item->file_ttd_url }}" 
                         alt="Tanda Tangan" 
                         class="position-relative" 
                         style="max-height: 85px; max-width: 170px; z-index: 2; object-fit: contain;">
                @else
                    <div class="text-muted font-italic small py-4" style="border: 1px dashed #cbd5e1; width: 100%; border-radius: 6px;">
                        [Tanda Tangan Belum Diunggah]
                    </div>
                @endif
            </div>

            {{-- Nama Pejabat & NIP/NIK --}}
            <div class="font-weight-bold text-dark mt-2" style="font-size: 0.95rem; text-decoration: underline;">
                {{ $item->nama_pejabat }}
            </div>
            @if($item->nip_nik)
                <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                    NIP / NIK: {{ $item->nip_nik }}
                </div>
            @endif
        </div>
    </div>

    {{-- Detail Metadata --}}
    <div class="mt-4 pt-3 border-top small text-muted">
        <div class="row">
            <div class="col-6">
                <div>Status Penggunaan:</div>
                <strong class="{{ $item->is_active ? 'text-success' : 'text-secondary' }}">
                    <i class="fas {{ $item->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                    {{ $item->is_active ? 'Aktif (Digunakan)' : 'Non-Aktif' }}
                </strong>
            </div>
            <div class="col-6 text-right">
                <div>Terakhir Diperbarui:</div>
                <strong>{{ $item->updated_at ? $item->updated_at->translatedFormat('d M Y H:i') : '-' }}</strong>
            </div>
        </div>
        @if($item->keterangan)
            <div class="mt-2 pt-2 border-top">
                <span class="font-weight-bold">Catatan:</span> {{ $item->keterangan }}
            </div>
        @endif
    </div>
</div>

<div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end">
    <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
