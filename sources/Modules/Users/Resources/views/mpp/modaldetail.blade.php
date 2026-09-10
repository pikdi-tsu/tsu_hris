<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border: none; border-radius: var(--tsu-radius-lg, 12px); overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--tsu-primary-dark, #094b54) 0%, var(--tsu-primary, #0c6170) 100%); color: white; border: none; padding: 1.15rem 1.5rem;">
                <h5 class="modal-title font-weight-bold" style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-file-invoice"></i>
                    Detail Pengajuan Manpower Planning
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                {{-- Hero Banner: Jabatan & Status --}}
                <div class="d-flex align-items-center justify-content-between p-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px);">
                    <div>
                        <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">Posisi / Jabatan</span>
                        <h4 class="font-weight-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 1.2rem;">
                            {{ $data->jabatan ? $data->jabatan->nama_jabatan : '-' }}
                        </h4>
                    </div>
                    <div>
                        @if($data->status == 'waiting')
                            <span class="badge badge-warning" style="font-size: 0.82rem; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: 6px;">
                                <i class="fas fa-clock mr-1"></i> Menunggu SDM
                            </span>
                        @elseif($data->status == 'approved')
                            <span class="badge badge-success" style="font-size: 0.82rem; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: 6px;">
                                <i class="fas fa-check-circle mr-1"></i> Disetujui
                            </span>
                        @elseif($data->status == 'rejected')
                            <span class="badge badge-danger" style="font-size: 0.82rem; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: 6px;">
                                <i class="fas fa-times-circle mr-1"></i> Ditolak
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Key Information Grid --}}
                <div class="row mb-4">
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px); height: 100%;">
                            <span class="text-muted d-block" style="font-size: 0.74rem; font-weight: 600; text-transform: uppercase;"><i class="fas fa-calendar-alt mr-1 text-primary"></i>Tahun</span>
                            <span class="font-weight-bold" style="font-size: 1rem; color: #1e293b;">{{ $data->tahun }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px); height: 100%;">
                            <span class="text-muted d-block" style="font-size: 0.74rem; font-weight: 600; text-transform: uppercase;"><i class="fas fa-users mr-1 text-primary"></i>Kebutuhan</span>
                            <span class="font-weight-bold" style="font-size: 1rem; color: #1e293b;">{{ $data->jumlah_kebutuhan }} Orang</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px); height: 100%;">
                            <span class="text-muted d-block" style="font-size: 0.74rem; font-weight: 600; text-transform: uppercase;"><i class="fas fa-tag mr-1 text-primary"></i>Tipe</span>
                            <span class="font-weight-bold" style="font-size: 0.9rem; color: #1e293b;">{{ $data->tipe_pengajuan }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px); height: 100%;">
                            <span class="text-muted d-block" style="font-size: 0.74rem; font-weight: 600; text-transform: uppercase;"><i class="fas fa-clock mr-1 text-primary"></i>Diajukan</span>
                            <span class="font-weight-bold" style="font-size: 0.85rem; color: #1e293b;">{{ $data->created_at ? \Carbon\Carbon::parse($data->created_at)->translatedFormat('d M Y H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Alasan / Justifikasi --}}
                <div class="mb-4">
                    <label class="font-weight-700 text-uppercase mb-2" style="font-size: 0.76rem; color: var(--tsu-primary-dark, #094b54); letter-spacing: 0.05em;">
                        <i class="fas fa-align-left mr-1"></i> Alasan &amp; Justifikasi Pengajuan
                    </label>
                    <div class="p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--tsu-radius, 8px); font-size: 0.86rem; color: #334155; line-height: 1.6; white-space: pre-line;">
                        {{ $data->alasan ?: '-' }}
                    </div>
                </div>

                {{-- SDM Review Section --}}
                @if($data->status != 'waiting')
                    <div class="p-3 mb-2" style="background: {{ $data->status == 'approved' ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ $data->status == 'approved' ? '#bbf7d0' : '#fecaca' }}; border-radius: var(--tsu-radius, 8px);">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2" style="border-bottom: 1px dashed {{ $data->status == 'approved' ? '#86efac' : '#fca5a5' }};">
                            <div style="font-size: 0.82rem; font-weight: 700; color: {{ $data->status == 'approved' ? '#166534' : '#991b1b' }};">
                                <i class="fas {{ $data->status == 'approved' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                Hasil Evaluasi SDM: {{ $data->status == 'approved' ? 'DISETUJUI' : 'DITOLAK' }}
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-calendar mr-1"></i>{{ $data->approval_date ? \Carbon\Carbon::parse($data->approval_date)->translatedFormat('d F Y H:i') : '-' }}
                            </div>
                        </div>
                        <div class="row" style="font-size: 0.84rem;">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Ditinjau Oleh:</span>
                                <strong>{{ $hrdName }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Catatan SDM:</span>
                                <span>{{ $data->keterangan_hrd ?: 'Tidak ada catatan tambahan.' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer d-flex justify-content-end" style="border-top: 1px solid #f1f5f9; background: #fafafa; padding: 0.85rem 1.5rem;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" style="border-radius: var(--tsu-radius, 8px); font-weight: 600; padding: 0.4rem 1.25rem;">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
