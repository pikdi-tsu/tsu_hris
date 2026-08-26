<div class="modal fade" id="modal-approval">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tinjau Pengajuan Manpower Planning</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 40%">Pengaju</th>
                                <td>: {{ $data->pengaju ? $data->pengaju->nama : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Unit Kerja</th>
                                <td>: {{ $data->unit ? $data->unit->nama_unit : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Data SO Unit</th>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-users"></i> Saat Ini: {{ $existing_count }} Orang</span>
                                    <span class="badge {{ ($kuota_mpp > 0 && ($existing_count + $data->jumlah_kebutuhan) > $kuota_mpp) ? 'badge-danger' : 'badge-success' }}"><i class="fas fa-chart-pie"></i> Kuota: {{ $kuota_mpp > 0 ? $kuota_mpp : '∞' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Jabatan Diminta</th>
                                <td>: <strong class="text-primary">{{ $data->jabatan ? $data->jabatan->nama_jabatan : '-' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Kebutuhan (Orang)</th>
                                <td>: <strong>{{ $data->jumlah_kebutuhan }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 40%">Tahun</th>
                                <td>: {{ $data->tahun }}</td>
                            </tr>
                            <tr>
                                <th>Tipe Pengajuan</th>
                                <td>: {{ $data->tipe_pengajuan }}</td>
                            </tr>
                            <tr>
                                <th>Status Saat Ini</th>
                                <td>: 
                                    @if($data->status == 'waiting')
                                        <span class="badge badge-warning">Menunggu</span>
                                    @elseif($data->status == 'approved')
                                        <span class="badge badge-success">Disetujui</span>
                                    @elseif($data->status == 'rejected')
                                        <span class="badge badge-danger">Ditolak</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td>: {{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                <div class="form-group">
                    <label>Alasan / Keterangan Pengaju:</label>
                    <div class="p-2 border rounded bg-light" style="min-height: 60px;">
                        {{ $data->alasan }}
                    </div>
                </div>

                @if($data->status == 'waiting')
                <hr>
                <form id="form-approval">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="form-group">
                        <label>Tindakan Persetujuan <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="">-- Pilih Tindakan --</option>
                            <option value="approved">Setujui (Approved)</option>
                            <option value="rejected">Tolak (Rejected)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan SDM (Opsional)</label>
                        <textarea name="keterangan_hrd" class="form-control" rows="2" placeholder="Tuliskan catatan Anda di sini..."></textarea>
                    </div>
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-default mr-2" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-approval"><i class="fas fa-save"></i> Proses</button>
                    </div>
                </form>
                @else
                <hr>
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Info Riwayat Persetujuan</h5>
                    Pengajuan ini telah <strong>{{ $data->status == 'approved' ? 'DISETUJUI' : 'DITOLAK' }}</strong> 
                    oleh {{ $hrdName }} pada tanggal {{ \Carbon\Carbon::parse($data->approval_date)->translatedFormat('d F Y H:i') }}.
                    <br><br>
                    <strong>Catatan SDM:</strong><br>
                    {{ $data->keterangan_hrd ?? 'Tidak ada catatan.' }}
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    $('#form-approval').submit(function(e) {
        e.preventDefault();
        let btn = $('#btn-submit-approval');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({
            url: "{{ route('admin.mpp.approve') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                $('#modal-approval').modal('hide');
                table.ajax.reload();
                // If using cards on top, you might want to reload the page or update cards via JSON
                window.location.reload(); 
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Proses');
                let errMsg = err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan';
                Swal.fire('Gagal', errMsg, 'error');
            }
        });
    });
</script>
