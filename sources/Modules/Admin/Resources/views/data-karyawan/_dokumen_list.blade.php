@php
    $berkasList = $karyawan->dokumenBerkas ?? collect([]);
    $canEdit = $canEdit ?? false;

    // Deteksi dokumen legacy (link teks / drive lama jika ada)
    $legacyDocs = [
        ['label' => 'KTP (Tautan Lama)', 'url' => $karyawan->scan_ktp, 'icon' => 'fa-id-card', 'key' => 'KTP'],
        ['label' => 'Kartu Keluarga (Tautan Lama)', 'url' => $karyawan->scan_kk, 'icon' => 'fa-users', 'key' => 'KK'],
        ['label' => 'NPWP (Tautan Lama)', 'url' => $karyawan->scan_npwp, 'icon' => 'fa-file-invoice-dollar', 'key' => 'NPWP'],
        ['label' => 'Ijazah (Tautan Lama)', 'url' => $karyawan->scan_ijazah, 'icon' => 'fa-user-graduate', 'key' => 'IJAZAH'],
    ];
@endphp

@if($berkasList->isEmpty() && collect($legacyDocs)->where('url', '!=', null)->isEmpty())
    <div class="text-center py-4 text-muted">
        <i class="fas fa-folder-open fa-3x mb-2 text-black-50"></i>
        <p class="mb-0">Belum ada berkas dokumen digital yang diunggah.</p>
        <small>Silakan unggah dokumen berkas menggunakan form di atas.</small>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped bg-white mb-0" style="font-size: 0.9rem;">
            <thead style="background: var(--tsu-primary-light, #d0eef2); color: var(--tsu-primary-dark, #094b54);">
                <tr>
                    <th width="4%" class="text-center">No</th>
                    <th width="24%">Jenis Dokumen</th>
                    <th width="22%">Nomor & Keterangan</th>
                    <th width="14%">Tgl Dokumen</th>
                    <th width="12%" class="text-center">Ukuran</th>
                    <th width="24%" class="text-center">Aksi Dokumen</th>
                </tr>
            </thead>
            <tbody>
                {{-- 1. Tampilkan Berkas Dinamis Baru --}}
                @foreach($berkasList as $index => $doc)
                    @php
                        $isPdf = $doc->is_pdf;
                        $isImg = $doc->is_image;
                        $iconFile = $isPdf ? 'fa-file-pdf text-danger' : ($isImg ? 'fa-file-image text-success' : 'fa-file-alt text-primary');
                    @endphp
                    <tr>
                        <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-weight-bold text-dark d-flex align-items-center">
                                <i class="fas {{ $iconFile }} mr-2 fa-lg"></i>
                                <span>{{ $doc->masterJenis ? $doc->masterJenis->nama_dokumen : $doc->nama_berkas }}</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <span class="badge badge-light border text-uppercase">{{ $doc->file_extension ?? 'FILE' }}</span>
                                {{ Str::limit(basename($doc->file_path), 25) }}
                            </small>
                        </td>
                        <td>
                            @if($doc->nomor_dokumen)
                                <div class="font-weight-bold text-dark small"><i class="fas fa-hashtag text-muted mr-1"></i> {{ $doc->nomor_dokumen }}</div>
                            @endif
                            @if($doc->keterangan)
                                <small class="text-muted font-italic">{{ $doc->keterangan }}</small>
                            @elseif(!$doc->nomor_dokumen)
                                <span class="text-black-50 small">-</span>
                            @endif
                        </td>
                        <td>
                            @if($doc->tanggal_dokumen)
                                <div class="small"><i class="far fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($doc->tanggal_dokumen)->format('d/m/Y') }}</div>
                            @else
                                <span class="text-black-50 small">-</span>
                            @endif
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Diunggah {{ $doc->created_at ? $doc->created_at->diffForHumans() : '-' }}</small>
                        </td>
                        <td class="text-center small font-weight-bold text-muted">
                            {{ $doc->formatted_size }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" 
                                    class="btn btn-info btn-preview-dokumen" 
                                    data-url="{{ route('admin.data-karyawan.preview-dokumen', $doc->id) }}"
                                    title="Lihat / Preview Dokumen">
                                    <i class="fas fa-eye mr-1"></i> Lihat
                                </button>
                                <a href="{{ $doc->file_url }}" 
                                    target="_blank" 
                                    download 
                                    class="btn btn-outline-secondary"
                                    title="Unduh Berkas">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if($canEdit)
                                <button type="button" 
                                    class="btn btn-danger btn-delete-dokumen" 
                                    data-url="{{ route('admin.data-karyawan.destroy-dokumen', $doc->id) }}"
                                    data-nama="{{ $doc->masterJenis ? $doc->masterJenis->nama_dokumen : $doc->nama_berkas }}"
                                    title="Hapus Dokumen">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

                {{-- 2. Tampilkan Berkas Legacy Tautan Google Drive (Jika Belum Diunggah Ulang) --}}
                @foreach($legacyDocs as $leg)
                    @if(!empty($leg['url']) && $leg['url'] !== '-' && !str_contains($leg['url'], 'null'))
                        <tr class="bg-light">
                            <td class="text-center text-muted"><i class="fas fa-link"></i></td>
                            <td>
                                <div class="font-weight-bold text-dark d-flex align-items-center">
                                    <i class="fas {{ $leg['icon'] }} mr-2 fa-lg text-info"></i>
                                    <span>{{ $leg['label'] }}</span>
                                </div>
                                <span class="badge badge-warning text-dark mt-1" style="font-size: 0.72rem;">
                                    <i class="fab fa-google-drive mr-1"></i> Tautan Drive (Legacy)
                                </span>
                            </td>
                            <td colspan="2">
                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $leg['url'] }}</small>
                            </td>
                            <td class="text-center small text-muted">-</td>
                            <td class="text-center">
                                <a href="{{ $leg['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 0.8rem;">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Tautan
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif
