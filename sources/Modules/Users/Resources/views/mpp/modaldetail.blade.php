<div class="modal fade" id="modal-detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Pengajuan MPP</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%">Jabatan</th>
                        <td>{{ $data->jabatan ? $data->jabatan->nama_jabatan : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tahun</th>
                        <td>{{ $data->tahun }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Kebutuhan</th>
                        <td>{{ $data->jumlah_kebutuhan }} Orang</td>
                    </tr>
                    <tr>
                        <th>Tipe Pengajuan</th>
                        <td>{{ $data->tipe_pengajuan }}</td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td>{{ $data->alasan }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($data->status == 'waiting')
                                <span class="badge badge-warning">Menunggu SDM</span>
                            @elseif($data->status == 'approved')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($data->status == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @if($data->status != 'waiting')
                    <tr>
                        <th>Ditinjau Oleh (SDM)</th>
                        <td>{{ $hrdName }}</td>
                    </tr>
                    <tr>
                        <th>Catatan SDM</th>
                        <td>{{ $data->keterangan_hrd ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Tinjauan</th>
                        <td>{{ $data->approval_date ? \Carbon\Carbon::parse($data->approval_date)->translatedFormat('d F Y H:i') : '-' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
