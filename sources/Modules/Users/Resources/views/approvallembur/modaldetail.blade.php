<div class="modal-body" style="font-size: 0.9rem">
    <input type="hidden" id="idlemburkaryawan" value="{{ $data->id }}">
    <input type="hidden" id="iduser" value="{{ $data->id_user }}">

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Nama Pegawai</label>
                <input type="text" class="form-control form-control-sm bg-light"
                    value="{{ ($data->user->nama ?? '-') . ' (' . ($data->user->nik ?? '-') . ')' }}" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Tanggal Lembur</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $tanggal }}" readonly>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Jenis Lembur</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->masterLembur->jenislembur ?? '-' }}" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Jam Mulai - Selesai (Durasi)</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $waktu }}" readonly>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Uraian / Keterangan Lembur</label>
                <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->keterangan ?? '-' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Bukti Kegiatan</label>
                <div class="pt-1">
                    @if ($data->bukti_kegiatan)
                        <a href="{{ asset('storage/lembur/bukti/' . $data->bukti_kegiatan) }}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-paperclip mr-1"></i> Unduh / Buka Bukti Kegiatan
                        </a>
                    @else
                        <span class="text-muted font-italic small"><i class="fas fa-info-circle mr-1"></i> Tidak ada lampiran berkas</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Atasan Langsung</label>
                <input type="text" class="form-control form-control-sm bg-light"
                    value="{{ ($data->atasan->nama ?? '-') . ' (' . ($data->atasan->nik ?? '-') . ')' }}" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Status Approval Atasan</label>
                <div>
                    @if ($data->statusatasan == 'approved')
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                        @if ($data->atasanapprovaldate)
                            <small class="text-muted ml-2 font-italic">{{ \Carbon\Carbon::parse($data->atasanapprovaldate)->format('d/m/Y H:i') }}</small>
                        @endif
                    @elseif ($data->statusatasan == 'rejected')
                        <span class="badge badge-danger px-2 py-1" data-toggle="popover" data-trigger="hover focus click" data-html="true"
                            title="Alasan Penolakan Atasan" data-placement="top" data-content="{!! e($data->alasanatasan ?? 'Tidak ada catatan') !!}"
                            style="cursor:pointer"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                        @if ($data->atasanapprovaldate)
                            <small class="text-muted ml-2 font-italic">{{ \Carbon\Carbon::parse($data->atasanapprovaldate)->format('d/m/Y H:i') }}</small>
                        @endif
                    @else
                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> Menunggu Persetujuan</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Pemeriksa SDM</label>
                <input type="text" class="form-control form-control-sm bg-light"
                    value="{{ ($data->hrd->nama ?? '-') . ' (' . ($data->hrd->nik ?? '-') . ')' }}" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="text-muted small font-weight-bold mb-1">Status Approval SDM</label>
                <div>
                    @if ($data->statushrd == 'approved')
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                        @if ($data->hrdapprovaldate)
                            <small class="text-muted ml-2 font-italic">{{ \Carbon\Carbon::parse($data->hrdapprovaldate)->format('d/m/Y H:i') }}</small>
                        @endif
                    @elseif ($data->statushrd == 'rejected')
                        <span class="badge badge-danger px-2 py-1" data-toggle="popover" data-trigger="hover focus click" data-html="true"
                            title="Alasan Penolakan SDM" data-placement="top" data-content="{!! e($data->alasanhrd ?? 'Tidak ada catatan') !!}"
                            style="cursor:pointer"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                        @if ($data->hrdapprovaldate)
                            <small class="text-muted ml-2 font-italic">{{ \Carbon\Carbon::parse($data->hrdapprovaldate)->format('d/m/Y H:i') }}</small>
                        @endif
                    @else
                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> Menunggu Persetujuan</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr class="my-3">

    <div class="row bg-light p-2 rounded border mx-0">
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="font-weight-bold text-dark small mb-1">Keputusan Approval <span class="text-danger">*</span></label>
                <select id="approval" class="form-control form-control-sm">
                    <option value="">-- Pilih Keputusan --</option>
                    <option value="approved">Setujui (Approved)</option>
                    <option value="rejected">Tolak (Rejected)</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="font-weight-bold text-dark small mb-1">Catatan / Alasan <small class="text-muted">(Wajib jika ditolak)</small></label>
                <textarea id="keterangan" class="form-control form-control-sm" rows="2" placeholder="Tuliskan catatan atau alasan..."></textarea>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer justify-content-between py-2">
    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Batal
    </button>
    <button type="button" class="btn btn-sm btn-primary" id="btnsimpan">
        <i class="fas fa-check mr-1"></i> Simpan Approval
    </button>
</div>

<script>
    $(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>
