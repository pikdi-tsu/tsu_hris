@if($fungsionals->isEmpty())
    <div class="alert alert-warning text-center small mb-0">
        <i class="fas fa-info-circle mr-1"></i> Belum ada jabatan fungsional yang aktif.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Jabatan</th>
                    <th>Pangkat/Golongan</th>
                    <th>SK Jabatan</th>
                    <th>TMT Mulai</th>
                    <th>TMT Akhir</th>
                    <th style="width: 10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fungsionals as $index => $fung)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-weight-bold">{{ $fung->masterFungsional ? $fung->masterFungsional->nama_jabatan : 'N/A' }}</td>
                    <td>{{ $fung->pangkatGolongan ? $fung->pangkatGolongan->nama_pangkat_golongan : '-' }}</td>
                    <td>{{ $fung->sk_jabatan ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($fung->tgl_mulai)->format('d M Y') }}</td>
                    <td>{{ $fung->tgl_akhir ? \Carbon\Carbon::parse($fung->tgl_akhir)->format('d M Y') : '-' }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-delete-fung" data-url="{{ route('admin.data-karyawan.destroy-fungsional', $fung->id) }}" title="Hapus Fungsional">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
