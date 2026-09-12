@if($strukturals->isEmpty())
    <div class="alert alert-warning text-center small mb-0">
        <i class="fas fa-info-circle mr-1"></i> Belum ada jabatan struktural yang aktif.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th style="width: 5%" class="text-center">No</th>
                    <th style="width: 30%">Jabatan Struktural</th>
                    <th style="width: 25%">Berkas / Nomor SK</th>
                    <th style="width: 15%">Tgl Mulai</th>
                    <th style="width: 15%">Tgl Akhir</th>
                    <th style="width: 10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($strukturals as $index => $str)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $str->masterStruktural ? $str->masterStruktural->nama_jabatan : 'N/A' }}</div>
                        @if($str->unit)
                            <small class="text-muted"><i class="fas fa-building"></i> {{ $str->unit->nama_unit }}</small>
                        @endif
                    </td>
                    <td>
                        @if($str->sk_jabatan)
                            <div class="small font-weight-bold text-dark"><i class="fas fa-file-contract mr-1 text-muted"></i> {{ $str->sk_jabatan }}</div>
                        @endif
                        @if($str->file_sk_url)
                            <a href="{{ $str->file_sk_url }}" target="_blank" class="btn btn-xs btn-info mt-1 shadow-sm px-2">
                                <i class="fas fa-file-pdf mr-1"></i> Lihat SK
                            </a>
                        @elseif(!$str->sk_jabatan)
                            <span class="text-muted small font-italic">- Belum ada SK -</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($str->tgl_mulai)->format('d M Y') }}</td>
                    <td>{{ $str->tgl_akhir ? \Carbon\Carbon::parse($str->tgl_akhir)->format('d M Y') : '-' }}</td>
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
