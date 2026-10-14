@extends('system::template.admin.header')

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        title="Approval Perubahan Data Pegawai"
        :icon="$menuIcon ?? 'fas fa-user-check'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.data-karyawan.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-users mr-1"></i> Kembali ke Data Karyawan
            </a>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- 1. STAT CARDS --}}
            <div class="row mb-3">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100" style="background: linear-gradient(135deg, #094b54, #0c6170); color: #fff;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(255,255,255,0.15);">
                                <i class="fas fa-clipboard-list fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small font-weight-bold" style="opacity: 0.85;">Total Pengajuan</div>
                                <h3 class="mb-0 font-weight-bold">{{ number_format($statTotal) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(255,255,255,0.2);">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small font-weight-bold" style="opacity: 0.9;">Menunggu Approval</div>
                                <h3 class="mb-0 font-weight-bold">{{ number_format($statMenunggu) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(255,255,255,0.2);">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small font-weight-bold" style="opacity: 0.9;">Disetujui (ACC)</div>
                                <h3 class="mb-0 font-weight-bold">{{ number_format($statDisetujui) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(255,255,255,0.2);">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small font-weight-bold" style="opacity: 0.9;">Ditolak</div>
                                <h3 class="mb-0 font-weight-bold">{{ number_format($statDitolak) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. FILTER & SEARCH PANEL --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.approval-perubahan-data.index', ['status' => 'menunggu', 'q' => $search]) }}"
                                   class="btn btn-sm mr-2 mb-1 {{ $statusFilter === 'menunggu' ? 'btn-warning font-weight-bold text-dark shadow-sm' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-clock mr-1"></i> Menunggu Persetujuan
                                    @if($statMenunggu > 0)
                                        <span class="badge badge-danger ml-1">{{ $statMenunggu }}</span>
                                    @endif
                                </a>

                                <a href="{{ route('admin.approval-perubahan-data.index', ['status' => 'disetujui', 'q' => $search]) }}"
                                   class="btn btn-sm mr-2 mb-1 {{ $statusFilter === 'disetujui' ? 'btn-success font-weight-bold shadow-sm' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-check-circle mr-1"></i> Disetujui (ACC)
                                    <span class="badge badge-light text-dark ml-1">{{ $statDisetujui }}</span>
                                </a>

                                <a href="{{ route('admin.approval-perubahan-data.index', ['status' => 'ditolak', 'q' => $search]) }}"
                                   class="btn btn-sm mr-2 mb-1 {{ $statusFilter === 'ditolak' ? 'btn-danger font-weight-bold shadow-sm' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-times-circle mr-1"></i> Ditolak
                                    <span class="badge badge-light text-dark ml-1">{{ $statDitolak }}</span>
                                </a>

                                <a href="{{ route('admin.approval-perubahan-data.index', ['status' => 'all', 'q' => $search]) }}"
                                   class="btn btn-sm mb-1 {{ $statusFilter === 'all' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-list mr-1"></i> Semua Riwayat
                                    <span class="badge badge-light text-dark ml-1">{{ $statTotal }}</span>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <form action="{{ route('admin.approval-perubahan-data.index') }}" method="GET">
                                <input type="hidden" name="status" value="{{ $statusFilter }}">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama / NIK pegawai..." value="{{ $search }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        @if(!empty($search))
                                            <a href="{{ route('admin.approval-perubahan-data.index', ['status' => $statusFilter]) }}" class="btn btn-sm btn-secondary" title="Reset">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. DAFTAR PERMOHONAN PERUBAHAN DATA --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="fas fa-tasks text-primary mr-2"></i>
                        Daftar Pengajuan Perubahan Data Mandiri
                        @if($statusFilter === 'menunggu')
                            <span class="badge badge-warning text-dark ml-2">Menunggu Approval SDM</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($perubahans->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3 opacity-50"></i>
                            <h6 class="font-weight-bold text-muted">Tidak Ada Data Pengajuan</h6>
                            <p class="text-muted small mb-0">
                                @if(!empty($search))
                                    Tidak ditemukan pengajuan dengan kata kunci "{{ $search }}".
                                @elseif($statusFilter === 'menunggu')
                                    Semua pengajuan perubahan data telah selesai diverifikasi.
                                @else
                                    Belum ada data pengajuan pada filter ini.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead style="background-color: #f1f5f9; color: #334155;">
                                    <tr>
                                        <th width="4%" class="text-center">No</th>
                                        <th width="22%">Pegawai Pemohon</th>
                                        <th width="12%">Tgl Diajukan</th>
                                        <th width="32%">Rincian Perubahan (Lama vs Baru)</th>
                                        <th width="15%">Catatan Pemohon</th>
                                        <th width="15%" class="text-center">Aksi / Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($perubahans as $index => $item)
                                        @php
                                            $karyawan = $item->dataDosenTendik;
                                            $diffs = $item->perubahan_data ?: [];
                                            $statusBadge = match($item->status) {
                                                'menunggu' => 'badge-warning text-dark',
                                                'disetujui' => 'badge-success',
                                                'ditolak' => 'badge-danger',
                                                default => 'badge-secondary'
                                            };
                                            $statusLabel = match($item->status) {
                                                'menunggu' => 'Menunggu ACC',
                                                'disetujui' => 'Disetujui (ACC)',
                                                'ditolak' => 'Ditolak',
                                                default => $item->status
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">
                                                {{ $perubahans->firstItem() + $index }}
                                            </td>

                                            {{-- Pegawai Info --}}
                                            <td>
                                                @if($karyawan)
                                                    <div class="font-weight-bold text-dark text-uppercase" style="line-height: 1.2;">
                                                        {{ $karyawan->nama_lengkap ?? $karyawan->nama }}
                                                    </div>
                                                    <div class="small text-muted mt-1">
                                                        <span class="badge badge-secondary mr-1">NIK: {{ $karyawan->nik }}</span>
                                                        <span class="badge {{ $karyawan->tipe_karyawan == 'Dosen' ? 'badge-primary' : 'badge-warning text-dark' }}">
                                                            {{ $karyawan->tipe_karyawan }}
                                                        </span>
                                                    </div>
                                                    <div class="small text-secondary mt-1">
                                                        <i class="fas fa-building mr-1"></i> {{ $karyawan->unit->nama_unit ?? 'Unit Kerja Belum Diatur' }}
                                                    </div>
                                                @else
                                                    <span class="text-muted font-italic">Pegawai Telah Dihapus</span>
                                                @endif
                                            </td>

                                            {{-- Tanggal --}}
                                            <td>
                                                <div class="font-weight-bold text-dark">
                                                    {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}
                                                </div>
                                                <small class="text-muted">
                                                    <i class="far fa-clock mr-1"></i> {{ $item->created_at ? $item->created_at->format('H:i') : '' }} WIB
                                                </small>
                                            </td>

                                            {{-- Rincian Perubahan --}}
                                            <td>
                                                <div class="table-responsive border rounded bg-white p-1" style="max-height: 180px; overflow-y: auto;">
                                                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th width="35%">Field</th>
                                                                <th width="32%" class="text-danger">Sebelum</th>
                                                                <th width="33%" class="text-success">Diajukan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($diffs as $fKey => $dVal)
                                                                <tr>
                                                                    <td class="font-weight-bold">{{ $dVal['label'] ?? $fKey }}</td>
                                                                    <td class="text-muted bg-light">{{ !empty($dVal['old']) ? $dVal['old'] : '-' }}</td>
                                                                    <td class="font-weight-bold text-success" style="background-color: #f0fdf4;">{{ !empty($dVal['new']) ? $dVal['new'] : '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>

                                            {{-- Catatan Pemohon --}}
                                            <td>
                                                @if($item->catatan_pemohon)
                                                    <div class="p-2 rounded bg-light border text-dark small" style="font-style: italic;">
                                                        "{{ $item->catatan_pemohon }}"
                                                    </div>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>

                                            {{-- Aksi / Status --}}
                                            <td class="text-center">
                                                @if($item->status === 'menunggu')
                                                    <div class="d-flex flex-column gap-1">
                                                        <button type="button"
                                                                class="btn btn-sm btn-success font-weight-bold shadow-sm mb-1 btn-approve-action"
                                                                data-url="{{ route('admin.approval-perubahan-data.approve', $item->id) }}"
                                                                data-name="{{ $karyawan->nama ?? 'Pegawai ini' }}">
                                                            <i class="fas fa-check-circle mr-1"></i> Setujui (ACC)
                                                        </button>

                                                        <button type="button"
                                                                class="btn btn-sm btn-danger font-weight-bold shadow-sm btn-reject-action"
                                                                data-url="{{ route('admin.approval-perubahan-data.reject', $item->id) }}"
                                                                data-name="{{ $karyawan->nama ?? 'Pegawai ini' }}">
                                                            <i class="fas fa-times-circle mr-1"></i> Tolak
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="badge {{ $statusBadge }} px-2 py-1 text-uppercase font-weight-bold mb-1">
                                                        {{ $statusLabel }}
                                                    </span>
                                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                        Oleh: <strong>{{ $item->reviewer->name ?? 'Admin SDM' }}</strong><br>
                                                        {{ $item->approved_at ? $item->approved_at->format('d M Y, H:i') : '' }}
                                                    </div>
                                                    @if($item->catatan_sdm)
                                                        <div class="small text-danger mt-1 p-1 bg-light rounded border text-left">
                                                            <strong>Alasan:</strong> {{ $item->catatan_sdm }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="p-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                Menampilkan {{ $perubahans->firstItem() }} - {{ $perubahans->lastItem() }} dari total {{ $perubahans->total() }} pengajuan
                            </span>
                            <div>
                                {{ $perubahans->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // ACC Action
            $('.btn-approve-action').on('click', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                let name = $(this).data('name');
                let btn = $(this);

                Swal.fire({
                    title: 'Setujui Perubahan Data?',
                    html: `Apakah Anda yakin ingin menyetujui (ACC) perubahan data milik <b>${name}</b>?<br><small class="text-muted">Data di profil karyawan akan otomatis langsung diperbarui ke database.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Setujui Sekarang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success || res.status === 'success') {
                                    Swal.fire('Berhasil Disetujui!', res.message, 'success').then(() => {
                                        location.reload();
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

            // Tolak Action
            $('.btn-reject-action').on('click', function(e) {
                e.preventDefault();
                let url = $(this).data('url');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'Tolak Perubahan Data',
                    html: `Tolak pengajuan perubahan data milik <b>${name}</b>.<br>Silakan masukkan catatan alasan penolakan:`,
                    input: 'textarea',
                    inputPlaceholder: 'Contoh: Data tidak sesuai dengan lampiran berkas resmi.',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-times mr-1"></i> Tolak Pengajuan',
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
                                if (res.success || res.status === 'success') {
                                    Swal.fire('Ditolak!', res.message, 'success').then(() => {
                                        location.reload();
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
@endsection
