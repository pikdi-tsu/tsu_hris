<div class="modal-header text-white" style="background: var(--tsu-primary-dark, #094b54) !important;">
    <h5 class="modal-title font-weight-bold">
        <i class="fas fa-folder-open mr-2 text-warning"></i> Detail Surat Masuk: {{ $surat->no_agenda }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body p-4 bg-white">
    {{-- Banner Status & Sifat --}}
    <div class="d-flex align-items-center justify-content-between p-3 rounded mb-4"
         style="background: {{ $surat->status === 'selesai' ? '#dcfce7' : ($surat->status === 'didisposisi' ? '#e0f2fe' : '#fef3c7') }};">
        <div>
            <span class="text-muted small font-weight-bold text-uppercase d-block">Status Penanganan:</span>
            <div class="font-weight-bold" style="font-size: 1.15rem;">
                {!! $surat->status_badge !!}
                <span class="ml-2">{!! $surat->sifat_badge !!}</span>
            </div>
        </div>
        @if($surat->file_url)
            <a href="{{ $surat->file_url }}" target="_blank" class="btn btn-outline-danger font-weight-bold shadow-sm">
                <i class="fas fa-file-pdf mr-1"></i> Buka Scan Surat Asli (PDF)
            </a>
        @endif
    </div>

    {{-- Detail Data Surat --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Nomor Surat Asal</span>
            <div class="font-weight-bold text-dark">{{ $surat->no_surat_asal }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Instansi Pengirim</span>
            <div class="font-weight-bold text-primary">{{ $surat->pengirim_instansi }}</div>
        </div>

        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Tanggal Surat Asli</span>
            <div class="text-dark">{{ $surat->tgl_surat ? $surat->tgl_surat->format('d F Y') : '-' }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Tanggal Diterima SIKD</span>
            <div class="text-dark">{{ $surat->tgl_diterima ? $surat->tgl_diterima->format('d F Y') : '-' }}</div>
        </div>

        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Perihal Surat</span>
            <div class="p-2 border rounded bg-light text-dark font-weight-bold">{{ $surat->perihal }}</div>
        </div>

        @if($surat->ringkasan_isi)
        <div class="col-12 mb-3">
            <span class="text-muted small font-weight-bold text-uppercase d-block">Ringkasan / Catatan Surat</span>
            <div class="p-2 border rounded bg-light text-dark small">{{ $surat->ringkasan_isi }}</div>
        </div>
        @endif
    </div>

    {{-- Timeline / Riwayat Disposisi ke Unit --}}
    <h6 class="font-weight-bold text-dark border-bottom pb-2 mt-3 mb-3">
        <i class="fas fa-paper-plane mr-1 text-info"></i> Riwayat Lembar Disposisi & Tindak Lanjut Unit Kerja
    </h6>

    @if($surat->disposisis->isEmpty())
        <div class="alert alert-light border text-center py-3 text-muted">
            <i class="fas fa-info-circle mr-1"></i> Surat ini belum pernah didisposisikan ke unit kerja mana pun.
        </div>
    @else
        <div class="timeline timeline-inverse" style="font-size: 0.85rem;">
            @foreach($surat->disposisis as $disp)
            <div>
                <i class="fas fa-arrow-right bg-info"></i>
                <div class="timeline-item">
                    <span class="time"><i class="far fa-clock"></i> {{ $disp->tgl_disposisi->format('d M Y H:i') }}</span>
                    <h3 class="timeline-header font-weight-bold text-primary">
                        Disposisi ke: {{ $disp->unitTujuan->nama_unit ?? 'Unit Umum' }}
                        @if($disp->pegawaiTujuan)
                            <small class="text-muted font-weight-normal">(PIC: {{ $disp->pegawaiTujuan->nama_lengkap }})</small>
                        @endif
                    </h3>
                    <div class="timeline-body">
                        <div class="mb-2">
                            <strong>Instruksi:</strong> <span class="badge badge-primary">{{ $disp->instruksi }}</span>
                            @if($disp->batas_waktu)
                                <span class="text-danger ml-2 font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i> Batas: {{ $disp->batas_waktu->format('d/m/Y') }}</span>
                            @endif
                        </div>
                        @if($disp->catatan_disposisi)
                            <div class="text-muted font-italic mb-2">"{{ $disp->catatan_disposisi }}"</div>
                        @endif

                        {{-- Status Penanganan oleh Unit --}}
                        <div class="p-2 rounded bg-light border mt-2">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="font-weight-bold text-dark">Status Tindak Lanjut: {!! $disp->status_badge !!}</span>
                                @if($disp->tgl_selesai)
                                    <small class="text-success"><i class="fas fa-check mr-1"></i> Selesai: {{ $disp->tgl_selesai->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                            @if($disp->catatan_tindak_lanjut)
                                <div class="text-dark small"><strong>Tanggapan Unit:</strong> {{ $disp->catatan_tindak_lanjut }}</div>
                            @endif
                            @if($disp->file_tindak_lanjut_url)
                                <div class="mt-2">
                                    <a href="{{ $disp->file_tindak_lanjut_url }}" target="_blank" class="btn btn-xs btn-outline-info">
                                        <i class="fas fa-paperclip mr-1"></i> Unduh Dokumen Bukti Tindak Lanjut Unit
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<div class="modal-footer bg-light py-2 px-3">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>
