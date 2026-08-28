<div class="modal-body">
    <table class="table table-bordered table-striped" style="font-size: 10pt;">
        <tr>
            <th width="30%">Jenis Lembur</th>
            <td>{{ $data->masterLembur ? $data->masterLembur->jenislembur : '-' }}</td>
        </tr>
        <tr>
            <th>Karyawan</th>
            <td>{{ $data->user ? $data->user->nama . ' (' . $data->user->nik . ')' : '-' }}</td>
        </tr>
        <tr>
            <th>Waktu Lembur</th>
            <td>{{ $waktu }} ({{ $durasi }} Jam)</td>
        </tr>
        <tr>
            <th>Keterangan / Pekerjaan</th>
            <td>{{ $data->keterangan }}</td>
        </tr>
        <tr>
            <th>Bukti Kegiatan</th>
            <td>
                @if($data->bukti_kegiatan)
                    <a href="{{ asset('storage/lembur/bukti/' . $data->bukti_kegiatan) }}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file-download"></i> Lihat Bukti</a>
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>Atasan</th>
            <td>
                {{ $data->atasan ? $data->atasan->nama : '-' }} <br>
                Status: 
                @if($data->statusatasan == 'waiting')
                    <span class="badge badge-warning">Waiting</span>
                @elseif($data->statusatasan == 'approved')
                    <span class="badge badge-success">Approved</span>
                @else
                    <span class="badge badge-danger">Rejected</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>HRD</th>
            <td>
                {{ $data->hrd ? $data->hrd->nama : '-' }} <br>
                Status: 
                @if($data->statushrd == 'waiting')
                    <span class="badge badge-warning">Waiting</span>
                @elseif($data->statushrd == 'approved')
                    <span class="badge badge-success">Approved</span>
                @else
                    <span class="badge badge-danger">Rejected</span>
                @endif
            </td>
        </tr>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
</div>
