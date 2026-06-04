@if($strukturals->isEmpty())
    <div class="alert alert-warning text-center small mb-0">
        <i class="fas fa-info-circle mr-1"></i> Belum ada jabatan struktural yang aktif.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Jabatan Struktural</th>
                    <th>Tgl Mulai</th>
                    <th style="width: 10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($strukturals as $index => $str)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-weight-bold">{{ $str->masterStruktural ? $str->masterStruktural->nama_jabatan : 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($str->tgl_mulai)->format('d M Y') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-delete-str" data-url="{{ route('admin.data-karyawan.destroy-struktural', $str->id) }}" title="Lepas Struktural">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
