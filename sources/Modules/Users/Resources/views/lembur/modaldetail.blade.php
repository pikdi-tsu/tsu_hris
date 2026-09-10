<div class="modal-body p-0">
    <div class="p-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <small class="d-block text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;font-weight:700;">Jenis Lembur</small>
                    <span style="font-size:.95rem;font-weight:700;color:var(--tsu-primary-dark);">
                        <i class="fas fa-tag mr-1" style="color:var(--tsu-primary);"></i>
                        {{ $data->masterLembur ? $data->masterLembur->jenislembur : '-' }}
                    </span>
                </div>
                <div class="mb-3">
                    <small class="d-block text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;font-weight:700;">Karyawan</small>
                    <span style="font-size:.9rem;font-weight:600;color:var(--tsu-primary-dark);">
                        <i class="fas fa-user-circle mr-1" style="color:var(--tsu-primary);"></i>
                        {{ $data->user ? $data->user->nama . ' (' . $data->user->nik . ')' : '-' }}
                    </span>
                </div>
                <div class="mb-3">
                    <small class="d-block text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;font-weight:700;">Waktu Lembur</small>
                    <span style="font-size:.88rem;font-weight:600;color:var(--tsu-primary-dark);">
                        <i class="fas fa-clock mr-1" style="color:var(--tsu-primary);"></i> {{ $waktu }}
                    </span><br>
                    <span style="background:var(--tsu-primary-faint);color:var(--tsu-primary-dark);border:1px solid var(--tsu-primary-light);border-radius:20px;font-size:.78rem;font-weight:700;padding:.2em .6em;">
                        <i class="fas fa-stopwatch mr-1"></i> {{ $durasi }} Jam
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <small class="d-block text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;font-weight:700;">Keterangan / Pekerjaan</small>
                    <p style="font-size:.87rem;color:#374151;background:var(--tsu-primary-faint);border:1px solid var(--tsu-primary-light);border-radius:var(--tsu-radius);padding:.6rem .85rem;margin:0;line-height:1.55;">
                        {{ $data->keterangan ?: '-' }}
                    </p>
                </div>
                <div class="mb-3">
                    <small class="d-block text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;font-weight:700;">Bukti Kegiatan</small>
                    @if($data->bukti_kegiatan)
                        <a href="{{ asset('storage/lembur/bukti/' . $data->bukti_kegiatan) }}" target="_blank" class="btn btn-sm tsu-btn-export" style="font-size:.8rem;">
                            <i class="fas fa-file-download mr-1"></i> Unduh Bukti
                        </a>
                    @else
                        <span class="text-muted" style="font-size:.85rem;">Tidak ada bukti</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="background:var(--tsu-primary-faint);border:1px solid var(--tsu-primary-light);border-radius:var(--tsu-radius-lg);padding:1rem 1.25rem;">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--tsu-primary);margin-bottom:.75rem;">
                <i class="fas fa-project-diagram mr-1"></i> Alur Persetujuan
            </p>
            <div class="row">
                <div class="col-md-6">
                    <div style="background:white;border-radius:var(--tsu-radius);padding:.75rem;border:1px solid #e5e7eb;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <small style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">Atasan (Kepala Unit)</small>
                            @if($data->statusatasan=='waiting')
                                <span style="background:var(--tsu-warning-light);color:#92400e;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                            @elseif($data->statusatasan=='approved')
                                <span style="background:var(--tsu-success-light);color:#166534;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-check mr-1"></i>Disetujui</span>
                            @elseif($data->statusatasan=='draft')
                                <span style="background:#e5e7eb;color:#374151;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                            @else
                                <span style="background:var(--tsu-danger-light);color:#991b1b;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-times mr-1"></i>Ditolak</span>
                            @endif
                        </div>
                        <span style="font-size:.87rem;font-weight:600;color:var(--tsu-primary-dark);">
                            <i class="fas fa-user mr-1" style="color:var(--tsu-primary);"></i>
                            {{ $data->atasan ? $data->atasan->nama : '-' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="background:white;border-radius:var(--tsu-radius);padding:.75rem;border:1px solid #e5e7eb;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <small style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">SDM / HRD</small>
                            @if($data->statushrd=='waiting')
                                @if($data->statusatasan=='approved')
                                    <span style="background:var(--tsu-info-light);color:#164e63;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                                @else
                                    <span style="background:#e5e7eb;color:#374151;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-hourglass-half mr-1"></i>Belum Giliran</span>
                                @endif
                            @elseif($data->statushrd=='approved')
                                <span style="background:var(--tsu-success-light);color:#166534;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-check mr-1"></i>Disetujui</span>
                            @elseif($data->statushrd=='draft')
                                <span style="background:#e5e7eb;color:#374151;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                            @else
                                <span style="background:var(--tsu-danger-light);color:#991b1b;padding:.2em .6em;border-radius:20px;font-size:.72rem;font-weight:700;"><i class="fas fa-times mr-1"></i>Ditolak</span>
                            @endif
                        </div>
                        <span style="font-size:.87rem;font-weight:600;color:var(--tsu-primary-dark);">
                            <i class="fas fa-user-tie mr-1" style="color:var(--tsu-primary);"></i>
                            {{ $data->hrd ? $data->hrd->nama : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer" style="background:#f8fafc;border-top:1px solid var(--tsu-primary-light);">
    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Tutup</button>
</div>
