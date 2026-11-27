<div class="modal-header bg-primary">
    <h5 class="modal-title font-weight-bold text-white">
        <i class="fas fa-history mr-2"></i> Riwayat & Jejak Pegawai: {{ $karyawan->nama }}
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

{{-- 2 TABS NAVIGATION --}}
<div class="bg-light border-bottom px-3 pt-2">
    <ul class="nav nav-pills" id="tab-riwayat-nav" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="nav-jejak-tab" data-toggle="pill" href="#tab-jejak-karir" role="tab">
                <i class="fas fa-briefcase mr-1 text-primary"></i> Tab 1: Jejak Karir & Jabatan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold position-relative" id="nav-perubahan-tab" data-toggle="pill" href="#tab-perubahan-data" role="tab">
                <i class="fas fa-user-edit mr-1 text-info"></i> Tab 2: Riwayat Perubahan Data Individu
                @php $pendingCount = $perubahanDatas->where('status', 'menunggu')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="badge badge-warning text-dark ml-1 shadow-sm">{{ $pendingCount }} Menunggu ACC</span>
                @else
                    <span class="badge badge-secondary ml-1">{{ $perubahanDatas->count() }}</span>
                @endif
            </a>
        </li>
    </ul>
</div>

<div class="modal-body p-0" style="background-color: #f8f9fa;">
    <div class="tab-content" id="riwayatTabContent">
        
        {{-- ======================================================== --}}
        {{-- TAB 1: JEJAK KARIR & JABATAN --}}
        {{-- ======================================================== --}}
        <div class="tab-pane fade show active" id="tab-jejak-karir" role="tabpanel">
            {{-- Header Section --}}
            <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-weight-bold text-dark mb-1"><i class="fas fa-layer-group text-primary mr-1"></i> Jejak Karir & Riwayat Jabatan</h6>
                    <p class="text-muted small mb-0">Menampilkan rekaman perpindahan jabatan struktural dan fungsional pegawai ini.</p>
                </div>
                <div>
                    @if($riwayats->isEmpty())
                        <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="Swal.fire('Data Kosong', 'Pegawai ini belum memiliki catatan riwayat jabatan untuk diekspor!', 'warning');">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </button>
                    @else
                        <a href="{{ route('admin.data-karyawan.export-riwayat', $karyawan->id) }}" class="btn btn-sm btn-success shadow-sm" target="_blank">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    @endif
                </div>
            </div>

            {{-- Timeline Section --}}
            <div class="p-4">
                @if($riwayats->isEmpty())
                    <div class="alert alert-light text-center border shadow-sm py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3 opacity-50"></i>
                        <h6 class="font-weight-bold text-muted">Belum Ada Riwayat Jabatan</h6>
                        <p class="text-muted small mb-0">Pegawai ini belum pernah mengalami mutasi atau pergantian jabatan.</p>
                    </div>
                @else
                    <div class="timeline-wrapper position-relative px-2">
                        <div class="timeline-line position-absolute bg-secondary" style="width: 2px; top: 0; bottom: 0; left: 24px; opacity: 0.2;"></div>
                        
                        @foreach($riwayats as $riwayat)
                            @php
                                $isStruktural = $riwayat->tipe_jabatan === 'struktural';
                                $iconClass = $isStruktural ? 'fa-briefcase' : 'fa-medal';
                                $bgClass = $isStruktural ? 'bg-dark' : 'bg-info';
                                
                                $jabatanName = '';
                                if ($isStruktural) {
                                    $jabatanName = $riwayat->jabatanStruktural->nama_jabatan ?? 'Unknown Struktural';
                                } else {
                                    $jabatanName = $riwayat->jabatanFungsional->nama_jabatan ?? 'Unknown Fungsional';
                                    if ($riwayat->pangkatGolongan) {
                                        $jabatanName .= ' (' . $riwayat->pangkatGolongan->nama_pangkat_golongan . ')';
                                    }
                                }

                                $tglMulai = \Carbon\Carbon::parse($riwayat->tgl_mulai)->format('d M Y');
                                $tglSelesai = $riwayat->tgl_selesai ? \Carbon\Carbon::parse($riwayat->tgl_selesai)->format('d M Y') : 'Sekarang';
                                $durasi = $riwayat->lama_menjabat_bulan ? $riwayat->lama_menjabat_bulan . ' Bulan' : '< 1 Bulan';
                            @endphp

                            <div class="timeline-item d-flex position-relative mb-4">
                                <div class="timeline-icon {{ $bgClass }} text-white rounded-circle shadow-sm d-flex justify-content-center align-items-center" style="width: 48px; height: 48px; min-width: 48px; position: relative; z-index: 10;">
                                    <i class="fas {{ $iconClass }}"></i>
                                </div>
                                
                                <div class="timeline-content card border-0 shadow-sm ml-4 w-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="badge {{ $isStruktural ? 'badge-dark' : 'badge-info' }} mb-2 text-uppercase">{{ $riwayat->tipe_jabatan }}</span>
                                                <h6 class="font-weight-bold text-dark mb-1" style="font-size: 1.1rem; line-height: 1.3;">{{ $jabatanName }}</h6>
                                                @if($isStruktural && $riwayat->unit)
                                                    <div class="text-secondary small mt-1 font-weight-bold">
                                                        <i class="fas fa-building mr-1"></i> {{ $riwayat->unit->nama_unit }}
                                                    </div>
                                                @endif
                                                <div class="text-muted small mt-2">
                                                    <i class="far fa-calendar-alt mr-1"></i> {{ $tglMulai }} &nbsp;&mdash;&nbsp; {{ $tglSelesai }}
                                                    <span class="mx-2 text-light">|</span>
                                                    <i class="far fa-clock mr-1"></i> {{ $durasi }}
                                                </div>
                                            </div>
                                        </div>

                                        @if($riwayat->keterangan)
                                        <div class="mt-3 p-2 bg-light rounded border text-secondary small">
                                            <strong><i class="fas fa-quote-left mr-1 opacity-50"></i> Catatan:</strong><br>
                                            {{ $riwayat->keterangan }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 2: RIWAYAT PERUBAHAN DATA INDIVIDU --}}
        {{-- ======================================================== --}}
        <div class="tab-pane fade" id="tab-perubahan-data" role="tabpanel">
            <div class="p-3 bg-white border-bottom">
                <h6 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-user-check text-info mr-1"></i> Riwayat Pengajuan & Verifikasi Perubahan Data
                </h6>
                <p class="text-muted small mb-0">
                    Menampilkan permohonan edit data mandiri yang diajukan oleh pegawai. Admin SDM dapat meninjau perbedaan data serta memberikan persetujuan (ACC) atau penolakan.
                </p>
            </div>

            <div class="p-4">
                @if($perubahanDatas->isEmpty())
                    <div class="alert alert-light text-center border shadow-sm py-5">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3 opacity-50"></i>
                        <h6 class="font-weight-bold text-muted">Belum Ada Pengajuan Perubahan Data</h6>
                        <p class="text-muted small mb-0">Pegawai ini belum pernah mengajukan perubahan data mandiri melalui portal profil.</p>
                    </div>
                @else
                    @foreach($perubahanDatas as $item)
                        @php
                            $statusBadge = match($item->status) {
                                'menunggu' => 'badge-warning text-dark',
                                'disetujui' => 'badge-success',
                                'ditolak' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                            $statusText = match($item->status) {
                                'menunggu' => 'Menunggu Approval SDM',
                                'disetujui' => 'Disetujui (ACC)',
                                'ditolak' => 'Ditolak',
                                default => $item->status
                            };
                            $diffs = $item->perubahan_data ?: [];
                        @endphp
                        <div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid {{ $item->status == 'menunggu' ? '#ffc107' : ($item->status == 'disetujui' ? '#28a745' : '#dc3545') }} !important;">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
                                <div>
                                    <span class="badge {{ $statusBadge }} font-weight-bold px-2 py-1 mr-2 text-uppercase">
                                        {{ $statusText }}
                                    </span>
                                    <strong class="text-dark">{{ $item->kategori_perubahan }}</strong>
                                    <span class="text-muted small ml-2">
                                        <i class="far fa-clock mr-1"></i> Diajukan: {{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }}
                                    </span>
                                </div>
                                <div>
                                    @if($item->status === 'menunggu')
                                        <button type="button" 
                                            class="btn btn-sm btn-success shadow-sm btn-acc-perubahan mr-1"
                                            data-url="{{ route('admin.data-karyawan.perubahan.approve', [$karyawan->id, $item->id]) }}">
                                            <i class="fas fa-check-circle mr-1"></i> Setujui (ACC)
                                        </button>
                                        <button type="button" 
                                            class="btn btn-sm btn-danger shadow-sm btn-tolak-perubahan"
                                            data-url="{{ route('admin.data-karyawan.perubahan.reject', [$karyawan->id, $item->id]) }}">
                                            <i class="fas fa-times-circle mr-1"></i> Tolak
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body p-3">
                                @if($item->catatan_pemohon)
                                    <div class="alert alert-light border py-2 px-3 mb-3 small">
                                        <strong><i class="fas fa-comment-dots text-info mr-1"></i> Catatan Pemohon:</strong> {{ $item->catatan_pemohon }}
                                    </div>
                                @endif

                                {{-- TABEL KOMPARASI PERUBAHAN --}}
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 bg-white" style="font-size: 0.88rem;">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="30%">Bagian / Field Data</th>
                                                <th width="35%" class="text-danger"><i class="fas fa-history mr-1"></i> Data Lama</th>
                                                <th width="35%" class="text-success"><i class="fas fa-edit mr-1"></i> Data Baru (Diajukan)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($diffs as $fieldKey => $diff)
                                                <tr>
                                                    <td class="font-weight-bold text-dark">{{ $diff['label'] ?? $fieldKey }}</td>
                                                    <td class="text-muted" style="background-color: #fff5f5;">
                                                        {{ !empty($diff['old']) ? $diff['old'] : '-' }}
                                                    </td>
                                                    <td class="font-weight-bold text-dark" style="background-color: #f0fdf4;">
                                                        {{ !empty($diff['new']) ? $diff['new'] : '-' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada rincian perbedaan data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- REVIEW LOG --}}
                                @if($item->status !== 'menunggu')
                                    <div class="mt-3 p-2 rounded bg-light border text-muted small d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-shield mr-1"></i> Diproses oleh: <strong>{{ $item->reviewer->name ?? 'Admin SDM' }}</strong>
                                            @if($item->approved_at)
                                                pada {{ $item->approved_at->format('d M Y, H:i') }}
                                            @endif
                                        </div>
                                        @if($item->catatan_sdm)
                                            <div>
                                                <strong>Catatan SDM:</strong> {{ $item->catatan_sdm }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    </div>
</div>

<div class="modal-footer bg-white border-top">
    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Tutup</button>
</div>

<script>
    $(document).ready(function() {
        // ACC Perubahan Data
        $('.btn-acc-perubahan').on('click', function(e) {
            e.preventDefault();
            let url = $(this).data('url');
            let btn = $(this);

            Swal.fire({
                title: 'Konfirmasi Persetujuan (ACC)',
                text: 'Apakah Anda yakin ingin menyetujui perubahan data ini? Data profil pegawai akan otomatis diperbarui sesuai nilai baru.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Setujui Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if(result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if(res.success || res.status === 'success') {
                                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                    // Reload modal riwayat
                                    $.get('{{ route("admin.data-karyawan.riwayat", $karyawan->id) }}', function(html) {
                                        $('#modal-edit-content').html(html);
                                        $('#nav-perubahan-tab').tab('show');
                                    });
                                    if(typeof window.LaravelDataTables !== 'undefined' && window.LaravelDataTables["table-karyawan"]) {
                                        window.LaravelDataTables["table-karyawan"].ajax.reload(null, false);
                                    }
                                });
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Setujui (ACC)');
                            }
                        },
                        error: function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kegagalan komunikasi dengan server.';
                            Swal.fire('Kesalahan!', msg, 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Setujui (ACC)');
                        }
                    });
                }
            });
        });

        // Tolak Perubahan Data
        $('.btn-tolak-perubahan').on('click', function(e) {
            e.preventDefault();
            let url = $(this).data('url');

            Swal.fire({
                title: 'Tolak Pengajuan Perubahan',
                text: 'Masukkan alasan / catatan penolakan:',
                input: 'textarea',
                inputPlaceholder: 'Contoh: Data tidak sesuai dengan lampiran KTP / SK fisik.',
                inputAttributes: {
                    'aria-label': 'Catatan penolakan'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times mr-1"></i> Ya, Tolak Pengajuan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Anda wajib mengisi alasan penolakan!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            catatan_sdm: result.value
                        },
                        success: function(res) {
                            if(res.success || res.status === 'success') {
                                Swal.fire('Ditolak!', res.message, 'success').then(() => {
                                    $.get('{{ route("admin.data-karyawan.riwayat", $karyawan->id) }}', function(html) {
                                        $('#modal-edit-content').html(html);
                                        $('#nav-perubahan-tab').tab('show');
                                    });
                                });
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                            Swal.fire('Kesalahan!', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
