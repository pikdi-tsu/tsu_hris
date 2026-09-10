<div class="modal-body p-4">
    <div class="row">
        {{-- Section 1: Informasi Permohonan --}}
        <div class="col-md-6 mb-3">
            <div class="p-3 rounded border h-100" style="background:#f8fafc; border-color:#e2e8f0!important;">
                <h6 class="font-weight-bold text-uppercase mb-3" style="font-size:0.75rem; color:var(--tsu-primary); letter-spacing:0.05em; border-bottom:1.5px solid var(--tsu-primary-light); padding-bottom:0.4rem;">
                    <i class="fas fa-file-alt mr-1"></i> Detail Permohonan Cuti
                </h6>
                <div class="mb-2">
                    <small class="text-muted d-block font-weight-600">Nama Pegawai:</small>
                    <span class="font-weight-bold" style="color:var(--tsu-primary-dark); font-size:0.92rem;">
                        {{ $profile->nama ?? '-' }}
                    </span>
                    <span class="text-muted" style="font-size:0.82rem;">({{ $profile->nik ?? '-' }})</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block font-weight-600">Jenis Cuti:</small>
                    <span class="badge text-white px-2 py-1 mt-1" style="background:linear-gradient(135deg,var(--tsu-primary-dark),var(--tsu-primary)); font-size:0.82rem;">
                        {{ $data->masterCuti->jeniscuti ?? '-' }}
                    </span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block font-weight-600">Periode Cuti:</small>
                    <div class="font-weight-600 text-dark" style="font-size:0.88rem;">
                        <i class="far fa-calendar-alt text-muted mr-1"></i>{{ $tanggal }}
                    </div>
                    <span class="badge px-2 py-1 mt-1 font-weight-bold" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:0.78rem;">
                        <i class="fas fa-business-time mr-1"></i>{{ $jmlhari }} Hari Kerja Efektif
                    </span>
                </div>
                <div>
                    <small class="text-muted d-block font-weight-600">Keterangan / Alasan:</small>
                    <div class="p-2 rounded mt-1 bg-white border" style="font-size:0.85rem; color:#334155; min-height:50px;">
                        {{ $data->keterangan ?: '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Status Persetujuan --}}
        <div class="col-md-6 mb-3">
            <div class="p-3 rounded border h-100" style="background:#f8fafc; border-color:#e2e8f0!important;">
                <h6 class="font-weight-bold text-uppercase mb-3" style="font-size:0.75rem; color:var(--tsu-primary); letter-spacing:0.05em; border-bottom:1.5px solid var(--tsu-primary-light); padding-bottom:0.4rem;">
                    <i class="fas fa-tasks mr-1"></i> Status Persetujuan Berjenjang
                </h6>

                {{-- Approval Atasan --}}
                <div class="p-3 bg-white rounded border mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <small class="text-muted d-block font-weight-600"><i class="fas fa-user-tie mr-1 text-primary"></i> Atasan Langsung:</small>
                            <span class="font-weight-bold text-dark" style="font-size:0.88rem;">
                                {{ $data->atasan->nama ?? '-' }}
                            </span>
                            @if(isset($data->atasan->nik))
                                <small class="text-muted d-block">({{ $data->atasan->nik }})</small>
                            @endif
                        </div>
                        <div>
                            @if ($data->statusatasan == 'approved')
                                <span class="badge px-2 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:0.8rem; font-weight:600;">
                                    <i class="fas fa-check-circle mr-1"></i> Disetujui
                                </span>
                            @elseif($data->statusatasan == 'rejected')
                                <span class="badge px-2 py-1" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; font-size:0.8rem; font-weight:600;">
                                    <i class="fas fa-times-circle mr-1"></i> Ditolak
                                </span>
                            @else
                                <span class="badge px-2 py-1" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:0.8rem; font-weight:600;">
                                    <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($data->statusatasan == 'rejected' && $data->alasanatasan)
                        <div class="mt-2 p-2 rounded" style="background:#fef2f2; border:1px solid #fecaca; font-size:0.8rem; color:#991b1b;">
                            <strong>Catatan Penolakan:</strong> {{ $data->alasanatasan }}
                        </div>
                    @endif
                </div>

                {{-- Approval HRD / SDM --}}
                <div class="p-3 bg-white rounded border">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <small class="text-muted d-block font-weight-600"><i class="fas fa-user-shield mr-1 text-info"></i> Verifikator SDM:</small>
                            <span class="font-weight-bold text-dark" style="font-size:0.88rem;">
                                {{ $data->hrd->nama ?? '-' }}
                            </span>
                            @if(isset($data->hrd->nik))
                                <small class="text-muted d-block">({{ $data->hrd->nik }})</small>
                            @endif
                        </div>
                        <div>
                            @if ($data->statushrd == 'approved')
                                <span class="badge px-2 py-1" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:0.8rem; font-weight:600;">
                                    <i class="fas fa-check-circle mr-1"></i> Disetujui
                                </span>
                            @elseif($data->statushrd == 'rejected')
                                <span class="badge px-2 py-1" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; font-size:0.8rem; font-weight:600;">
                                    <i class="fas fa-times-circle mr-1"></i> Ditolak
                                </span>
                            @else
                                <span class="badge px-2 py-1" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:0.8rem; font-weight:600;">
                                    <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($data->statushrd == 'rejected' && $data->alasanhrd)
                        <div class="mt-2 p-2 rounded" style="background:#fef2f2; border:1px solid #fecaca; font-size:0.8rem; color:#991b1b;">
                            <strong>Catatan Penolakan:</strong> {{ $data->alasanhrd }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer" style="background:#f8fafc; border-top:1px solid var(--tsu-primary-light);">
    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:var(--tsu-radius); font-weight:600; padding:0.35rem 1rem;">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>

<script>
    $(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>
