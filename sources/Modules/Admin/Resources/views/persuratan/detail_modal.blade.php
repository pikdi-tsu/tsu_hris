<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-ticket-alt mr-2 text-warning"></i> Detail Tiket: {{ $surat->nomor_tiket }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4 bg-white">
    {{-- Status Banner --}}
    <div class="d-flex align-items-center justify-content-between p-3 rounded mb-4" 
         style="background: {{ $surat->status === 'selesai' ? '#dcfce7' : ($surat->status === 'ditolak' ? '#fee2e2' : ($surat->status === 'diproses' ? '#e0f2fe' : '#fef3c7')) }};">
        <div>
            <span class="text-muted small font-weight-bold text-uppercase d-block">Status Permohonan:</span>
            <div class="font-weight-bold" style="font-size: 1.15rem;">
                {!! $surat->status_badge !!}
            </div>
        </div>
        @if($surat->status === 'selesai' && $surat->file_hasil_url)
            <a href="{{ $surat->file_hasil_url }}" target="_blank" download class="btn btn-success font-weight-bold shadow-sm">
                <i class="fas fa-file-download mr-1"></i> Unduh Surat Resmi (PDF)
            </a>
        @endif
    </div>

    {{-- Detail Data Surat --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Pemohon</span>
            <div class="font-weight-bold text-dark">{{ $surat->pegawai->nama ?? '-' }}</div>
            <small class="text-muted">NIK: {{ $surat->pegawai->nik ?? '-' }}</small>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Jenis Surat</span>
            <div class="font-weight-bold text-dark">{{ $surat->jenis_surat }}</div>
        </div>

        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Keperluan Pembuatan Surat</span>
            <div class="p-2 border rounded bg-light text-dark font-weight-normal">{{ $surat->keperluan }}</div>
        </div>

        @if($surat->keterangan_tambahan)
        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Keterangan / Catatan Pemohon</span>
            <div class="p-2 border rounded bg-light text-dark font-italic small">{{ $surat->keterangan_tambahan }}</div>
        </div>
        @endif

        @if($surat->file_lampiran_url)
        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Berkas Lampiran Pendukung</span>
            <a href="{{ $surat->file_lampiran_url }}" target="_blank" class="btn btn-sm btn-outline-info mt-1">
                <i class="fas fa-paperclip mr-1"></i> Lihat / Buka Lampiran
            </a>
        </div>
        @endif

        @if($surat->nomor_surat_keluar)
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Nomor Surat / SK Resmi</span>
            <div class="font-weight-bold text-success">{{ $surat->nomor_surat_keluar }}</div>
        </div>
        @endif

        @if($surat->status_hardfile && $surat->status_hardfile !== 'belum_tersedia')
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Status Hardfile / Berkas Fisik</span>
            <div class="mt-1">
                {!! $surat->status_hardfile_badge !!}
                @if($surat->status_hardfile === 'siap_diambil')
                    <button type="button" class="btn btn-xs btn-outline-success ml-2 btn-confirm-hardfile" data-url="{{ route('admin.request-surat.update-hardfile', $surat->id) }}">
                        <i class="fas fa-check-double mr-1"></i> Konfirmasi Terima Fisik
                    </button>
                @endif
            </div>
        </div>
        @endif

        @if($surat->catatan_sekretariat)
        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block text-primary">Catatan dari Sekretariat Rektorat</span>
            <div class="p-2 border rounded border-info bg-light text-dark">{{ $surat->catatan_sekretariat }}</div>
        </div>
        @endif

        @if($surat->catatan_petugas)
        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block text-danger">Catatan dari Petugas SDM</span>
            <div class="p-2 border rounded border-warning bg-warning-light text-dark">{{ $surat->catatan_petugas }}</div>
        </div>
        @endif
    </div>

    {{-- Timeline Status --}}
    <h6 class="font-weight-bold text-dark border-bottom pb-2 mt-3 mb-3">
        <i class="fas fa-stream mr-1 text-info"></i> Riwayat Progres Permohonan
    </h6>
    <div class="timeline timeline-inverse" style="font-size: 0.85rem;">
        <div>
            <i class="fas fa-paper-plane bg-warning"></i>
            <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $surat->created_at->format('d M Y H:i') }}</span>
                <h3 class="timeline-header font-weight-bold">Permohonan Diajukan</h3>
                <div class="timeline-body text-muted">
                    Tiket permohonan surat masuk ke sistem antrean SDM.
                </div>
            </div>
        </div>

        @if($surat->processed_at)
        <div>
            <i class="fas fa-cogs bg-primary"></i>
            <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $surat->processed_at->format('d M Y H:i') }}</span>
                <h3 class="timeline-header font-weight-bold text-primary">Sedang Diproses oleh SDM</h3>
                <div class="timeline-body text-muted">
                    Surat sedang diverifikasi dan disiapkan oleh petugas SDM ({{ $surat->petugas->name ?? 'Admin SDM' }}).
                </div>
            </div>
        </div>
        @endif

        @if($surat->diteruskan_at)
        <div>
            <i class="fas fa-share bg-info"></i>
            <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $surat->diteruskan_at->format('d M Y H:i') }}</span>
                <h3 class="timeline-header font-weight-bold text-info"><i class="fas fa-share mr-1"></i> Diteruskan ke {{ $surat->penerimaTerusan->name ?? 'Sekretariat / Pimpinan' }}</h3>
                <div class="timeline-body text-dark">
                    <strong>Catatan / Disposisi:</strong> {{ $surat->catatan_terusan ?? '-' }}
                </div>
            </div>
        </div>
        @endif

        @if($surat->status === 'selesai' && $surat->completed_at)
        <div>
            <i class="fas fa-check-circle bg-success"></i>
            <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $surat->completed_at->format('d M Y H:i') }}</span>
                <h3 class="timeline-header font-weight-bold text-success">Surat Resmi Telah Diterbitkan</h3>
                <div class="timeline-body">
                    Surat resmi dengan nomor <strong>{{ $surat->nomor_surat_keluar ?? '-' }}</strong> telah ditandatangani dan siap diunduh.
                </div>
            </div>
        </div>
        @elseif($surat->status === 'ditolak' && $surat->completed_at)
        <div>
            <i class="fas fa-times-circle bg-danger"></i>
            <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $surat->completed_at->format('d M Y H:i') }}</span>
                <h3 class="timeline-header font-weight-bold text-danger">Permohonan Surat Ditolak</h3>
                <div class="timeline-body text-danger">
                    {{ $surat->catatan_petugas ?? 'Permohonan tidak dapat diproses.' }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="modal-footer bg-light py-2 px-3">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
