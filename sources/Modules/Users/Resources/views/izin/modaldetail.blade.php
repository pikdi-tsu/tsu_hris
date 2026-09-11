<style>
    .izin-detail-info-row {
        display: flex;
        gap: .5rem;
        align-items: flex-start;
        padding: .5rem 0;
        border-bottom: 1px solid #f0f4f8;
    }
    .izin-detail-info-row:last-child { border-bottom: none; }
    .izin-detail-icon {
        width: 28px; height: 28px;
        border-radius: 7px;
        background: rgba(9,75,84,.08);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: 2px;
    }
    .izin-detail-icon i { color: var(--tsu-primary, #094b54); font-size: .72rem; }
    .izin-detail-label { font-size: .7rem; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .1rem; }
    .izin-detail-value { font-size: .85rem; font-weight: 600; color: #2d3748; }

    .izin-status-card {
        border-radius: 10px;
        padding: .85rem 1rem;
        display: flex; flex-direction: column; gap: .35rem;
    }
    .izin-status-card.atasan { background: #f0fdf9; border: 1.5px solid #a7f3d0; }
    .izin-status-card.hrd    { background: #eff6ff; border: 1.5px solid #bfdbfe; }
    .izin-status-card .sc-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #718096; }
    .izin-status-card .sc-name  { font-size: .82rem; font-weight: 700; color: #1a202c; }
    .izin-status-card .sc-nik   { font-size: .72rem; color: #718096; }

    .izin-badge-status {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .3rem .8rem; border-radius: 20px;
        font-size: .75rem; font-weight: 700; letter-spacing: .02em;
        cursor: default;
    }
    .izin-badge-status.approved { background: #dcfce7; color: #166534; }
    .izin-badge-status.rejected { background: #fee2e2; color: #991b1b; cursor: pointer; }
    .izin-badge-status.waiting  { background: #fef9c3; color: #854d0e; }

    .izin-rejected-note {
        margin-top: .4rem;
        background: #fff1f2;
        border: 1px solid #fecdd3;
        border-radius: 8px;
        padding: .5rem .75rem;
        font-size: .78rem;
        color: #9f1239;
        display: none;
    }
</style>

<div class="modal-body p-4">
    <div class="row">
        {{-- Kolom Kiri: Info Izin --}}
        <div class="col-md-6">
            <p style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--tsu-primary,#094b54); border-bottom:1.5px solid #b2dfdb; padding-bottom:.35rem; margin-bottom:.65rem;">
                <i class="fas fa-file-alt mr-1"></i> Informasi Izin
            </p>

            <div class="izin-detail-info-row">
                <div class="izin-detail-icon"><i class="fas fa-user"></i></div>
                <div>
                    <div class="izin-detail-label">Nama Karyawan</div>
                    <div class="izin-detail-value">{{ $profile->nama ?? '-' }}</div>
                    <div style="font-size:.72rem; color:#718096;">{{ $profile->nik ?? '-' }}</div>
                </div>
            </div>

            <div class="izin-detail-info-row">
                <div class="izin-detail-icon"><i class="fas fa-tag"></i></div>
                <div>
                    <div class="izin-detail-label">Jenis Izin</div>
                    <div class="izin-detail-value">{{ $data->masterIzin->jenisizin ?? '-' }}</div>
                </div>
            </div>

            <div class="izin-detail-info-row">
                <div class="izin-detail-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="izin-detail-label">Tanggal Izin</div>
                    <div class="izin-detail-value">{{ $tanggal }}</div>
                    <div style="font-size:.72rem; color:#718096;">
                        <i class="fas fa-clock mr-1"></i> {{ $jmlhari }} hari kerja efektif
                    </div>
                </div>
            </div>

            <div class="izin-detail-info-row">
                <div class="izin-detail-icon"><i class="fas fa-comment-alt"></i></div>
                <div>
                    <div class="izin-detail-label">Alasan / Keterangan</div>
                    <div class="izin-detail-value">{{ $data->keterangan ?? '-' }}</div>
                </div>
            </div>

            @if($data->file_bukti)
            <div class="izin-detail-info-row">
                <div class="izin-detail-icon"><i class="fas fa-paperclip"></i></div>
                <div>
                    <div class="izin-detail-label">Berkas Bukti Dukungan (Wajib)</div>
                    <a href="{{ $data->file_bukti_url }}" target="_blank" download class="btn btn-xs btn-outline-info rounded-pill px-3 py-1 font-weight-bold mt-1">
                        <i class="fas fa-download mr-1"></i> Unduh / Lihat Bukti Izin
                    </a>
                </div>
            </div>
            @endif
        </div>

        {{-- Kolom Kanan: Status Persetujuan --}}
        <div class="col-md-6">
            <p style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--tsu-primary,#094b54); border-bottom:1.5px solid #b2dfdb; padding-bottom:.35rem; margin-bottom:.65rem;">
                <i class="fas fa-tasks mr-1"></i> Status Persetujuan
            </p>

            {{-- Atasan --}}
            <div class="izin-status-card atasan mb-3">
                <div class="sc-label"><i class="fas fa-user-tie mr-1"></i> Atasan (Kepala Unit)</div>
                <div class="sc-name">{{ $data->atasan->nama ?? '-' }}</div>
                <div class="sc-nik">NIK: {{ $data->atasan->nik ?? '-' }}</div>
                <div class="mt-1">
                    @if ($data->statusatasan == 'approved')
                        <span class="izin-badge-status approved"><i class="fas fa-check-circle"></i> Disetujui</span>
                    @elseif ($data->statusatasan == 'rejected')
                        <span class="izin-badge-status rejected" id="toggleAtasanNote">
                            <i class="fas fa-times-circle"></i> Ditolak
                            <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
                        </span>
                        <div class="izin-rejected-note" id="atasanNote">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <strong>Alasan Penolakan:</strong> {{ $data->alasanatasan ?? 'Tidak ada keterangan.' }}
                        </div>
                    @else
                        <span class="izin-badge-status waiting"><i class="fas fa-hourglass-half"></i> Menunggu</span>
                    @endif
                </div>
            </div>

            {{-- HRD --}}
            <div class="izin-status-card hrd">
                <div class="sc-label"><i class="fas fa-users-cog mr-1"></i> SDM / HRD</div>
                <div class="sc-name">{{ $data->hrd->nama ?? '-' }}</div>
                <div class="sc-nik">NIK: {{ $data->hrd->nik ?? '-' }}</div>
                <div class="mt-1">
                    @if ($data->statushrd == 'approved')
                        <span class="izin-badge-status approved"><i class="fas fa-check-circle"></i> Disetujui</span>
                    @elseif ($data->statushrd == 'rejected')
                        <span class="izin-badge-status rejected" id="toggleHrdNote">
                            <i class="fas fa-times-circle"></i> Ditolak
                            <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
                        </span>
                        <div class="izin-rejected-note" id="hrdNote">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <strong>Alasan Penolakan:</strong> {{ $data->alasanhrd ?? 'Tidak ada keterangan.' }}
                        </div>
                    @else
                        <span class="izin-badge-status waiting"><i class="fas fa-hourglass-half"></i> Menunggu</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer" style="border-top:1px solid #f0f4f8; padding:.75rem 1.25rem;">
    <button type="button" class="btn btn-sm" data-dismiss="modal"
            style="border-radius:8px; border:1.5px solid #e2e8f0; color:#4a5568; font-size:.8rem; font-weight:600; padding:.4rem 1rem;">
        <i class="fas fa-times mr-1"></i> Tutup
    </button>
</div>

<script>
    $(function () {
        $('#toggleAtasanNote').on('click', function () {
            $('#atasanNote').slideToggle(200);
        });
        $('#toggleHrdNote').on('click', function () {
            $('#hrdNote').slideToggle(200);
        });
    });
</script>
