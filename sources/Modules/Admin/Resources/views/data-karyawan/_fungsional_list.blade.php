@if($fungsionals->isEmpty())
    <div class="alert alert-warning text-center small mb-0">
        <i class="fas fa-info-circle mr-1"></i> Belum ada jabatan fungsional yang aktif.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th style="width: 5%" class="text-center">No</th>
                    <th style="width: 25%">Jabatan</th>
                    <th style="width: 20%">Pangkat/Golongan</th>
                    <th style="width: 25%">Berkas / Nomor SK</th>
                    <th style="width: 10%">TMT Mulai</th>
                    <th style="width: 10%">TMT Akhir</th>
                    <th style="width: 5%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fungsionals as $index => $fung)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-weight-bold">{{ $fung->masterFungsional ? $fung->masterFungsional->nama_jabatan : 'N/A' }}</td>
                    <td>{{ $fung->pangkatGolongan ? $fung->pangkatGolongan->nama_pangkat_golongan : '-' }}</td>
                    <td>
                        @if($fung->sk_jabatan && !str_starts_with($fung->sk_jabatan, 'http'))
                            <div class="small font-weight-bold text-dark"><i class="fas fa-file-contract mr-1 text-muted"></i> {{ $fung->sk_jabatan }}</div>
                        @endif
                        @if($fung->file_sk_url)
                            <a href="{{ $fung->file_sk_url }}" target="_blank" class="btn btn-xs btn-info mt-1 shadow-sm px-2">
                                <i class="fas fa-file-pdf mr-1"></i> Lihat SK
                            </a>
                        @elseif(!$fung->sk_jabatan)
                            <span class="text-muted small font-italic">- Belum ada SK -</span>
                        @endif
                    </td>
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
