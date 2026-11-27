@extends('system::template.admin.header')
@section('title', $title ?? 'Pengajuan Akun Email Resmi')

@section('link_href')
    <style>
        .tsu-stat-card-custom {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .tsu-stat-card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(9, 75, 84, 0.1) !important;
        }
        .tsu-stat-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .badge-bpo-diajukan { background: #fef3c7; color: #b45309; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.76rem; white-space: nowrap !important; display: inline-flex; align-items: center; justify-content: center; }
        .badge-bpo-sdm { background: #e0f2fe; color: #0369a1; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.76rem; white-space: nowrap !important; display: inline-flex; align-items: center; justify-content: center; }
        .badge-bpo-selesai { background: #dcfce7; color: #15803d; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.76rem; white-space: nowrap !important; display: inline-flex; align-items: center; justify-content: center; }
        .badge-bpo-ditolak { background: #fee2e2; color: #b91c1c; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 0.76rem; white-space: nowrap !important; display: inline-flex; align-items: center; justify-content: center; }
        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header Standar --}}
    <x-tsu-page-header
        title="Layanan Pengajuan Akun Email Resmi (@tsu.ac.id)"
        subtitle="Layanan mandiri sivitas akademika Universitas Tiga Serangkai untuk permohonan penerbitan akun resmi Google Workspace"
        icon="fas fa-paper-plane"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            <button type="button" class="btn tsu-btn-create btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTambahRequestUser">
                <i class="fas fa-plus mr-1"></i> Ajukan Akun Email Baru
            </button>
        </x-slot>
    </x-tsu-page-header>

    <section class="content">
        <div class="container-fluid">

            {{-- Alerts Flash Message --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Stat Metric Cards --}}
            <div class="row mb-3">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card tsu-stat-card-custom shadow-sm h-100" style="border-left: 4px solid #094b54 !important;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="tsu-stat-icon-circle mr-3" style="background: rgba(9, 75, 84, 0.1); color: #094b54;">
                                <i class="fas fa-envelope-open-text fa-lg"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted d-block text-truncate small font-weight-bold">Pengajuan Saya</span>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $totalPengajuan }}</h4>
                                <small class="text-muted" style="font-size: 0.72rem;">Total Riwayat Tiket</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card tsu-stat-card-custom shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="tsu-stat-icon-circle mr-3" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                                <i class="fas fa-user-clock fa-lg"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted d-block text-truncate small font-weight-bold">Verifikasi SDM</span>
                                <h4 class="mb-0 font-weight-bold text-warning">{{ $menungguSdm }}</h4>
                                <small class="text-muted" style="font-size: 0.72rem;">Sedang Direview SDM</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card tsu-stat-card-custom shadow-sm h-100" style="border-left: 4px solid #0284c7 !important;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="tsu-stat-icon-circle mr-3" style="background: rgba(2, 132, 199, 0.12); color: #0284c7;">
                                <i class="fas fa-server fa-lg"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted d-block text-truncate small font-weight-bold">Eksekusi PIKDI</span>
                                <h4 class="mb-0 font-weight-bold" style="color: #0284c7;">{{ $prosesPikdi }}</h4>
                                <small class="text-muted" style="font-size: 0.72rem;">Proses Aktivasi Akun</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card tsu-stat-card-custom shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="tsu-stat-icon-circle mr-3" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                                <i class="fas fa-check-double fa-lg"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-muted d-block text-truncate small font-weight-bold">Akun Resmi Aktif</span>
                                <h4 class="mb-0 font-weight-bold text-success">{{ $akunSelesai }}</h4>
                                <small class="text-muted" style="font-size: 0.72rem;">Siap Digunakan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step Timeline BPO Banner --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, rgba(9,75,84,0.06), rgba(2,132,199,0.06));">
                <div class="card-body py-3 px-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
                        <div class="d-flex align-items-center">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle mr-2" style="width: 32px; height: 32px; background: #094b54; color: #fff;">
                                <i class="fas fa-route" style="font-size: 0.85rem;"></i>
                            </span>
                            <strong class="text-dark small">Alur Standar BPO Penerbitan Email:</strong>
                        </div>
                        <div class="d-flex flex-wrap align-items-center small" style="gap: 8px;">
                            <span class="badge badge-light border px-2 py-1"><i class="fas fa-paper-plane mr-1 text-primary"></i> 1. Pengajuan Mandiri</span>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                            <span class="badge badge-light border px-2 py-1"><i class="fas fa-user-check mr-1 text-warning"></i> 2. Validasi &amp; Standarisasi SDM</span>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                            <span class="badge badge-light border px-2 py-1"><i class="fas fa-cogs mr-1 text-info"></i> 3. Penerbitan Akun PIKDI</span>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> 4. Akun Aktif &amp; Kredensial</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                        <div class="d-flex align-items-center">
                            <span class="d-inline-flex align-items-center justify-content-center rounded mr-2" style="width: 34px; height: 34px; background: rgba(9,75,84,0.1); color: #094b54;">
                                <i class="fas fa-history"></i>
                            </span>
                            <div>
                                <h6 class="font-weight-bold mb-0 text-dark">Riwayat &amp; Status Permohonan Saya</h6>
                                <small class="text-muted">Total {{ $requests->total() }} tiket terdata</small>
                            </div>
                        </div>

                        {{-- Toolbar Pencarian & Filter Status --}}
                        <form action="{{ route('users.request-email.index') }}" method="GET" class="d-flex flex-wrap align-items-center ml-auto" style="gap: 8px;">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control" placeholder="Cari tiket, usulan, keperluan..." value="{{ $filters['search'] ?? '' }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" style="background: #094b54; border-color: #094b54;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>

                            <select name="status" class="form-control form-control-sm" style="width: 170px;" onchange="this.form.submit()">
                                <option value="all" {{ ($filters['status'] ?? '') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="Diajukan" {{ ($filters['status'] ?? '') == 'Diajukan' ? 'selected' : '' }}>Diajukan (SDM)</option>
                                <option value="Divalidasi SDM" {{ ($filters['status'] ?? '') == 'Divalidasi SDM' ? 'selected' : '' }}>Divalidasi (PIKDI)</option>
                                <option value="Selesai" {{ ($filters['status'] ?? '') == 'Selesai' ? 'selected' : '' }}>Selesai (Aktif)</option>
                                <option value="Ditolak SDM" {{ ($filters['status'] ?? '') == 'Ditolak SDM' ? 'selected' : '' }}>Ditolak SDM</option>
                                <option value="Ditolak PIKDI" {{ ($filters['status'] ?? '') == 'Ditolak PIKDI' ? 'selected' : '' }}>Kendala PIKDI</option>
                            </select>

                            @if(!empty($filters['search']) || !empty($filters['status']))
                                <a href="{{ route('users.request-email.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                                    <i class="fas fa-undo"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 150px;">No. Tiket</th>
                                    <th style="min-width: 180px;">Usulan Alamat</th>
                                    <th style="min-width: 180px;">Rekomendasi SDM</th>
                                    <th style="min-width: 190px;">Akun Final PIKDI</th>
                                    <th style="min-width: 140px; width: 150px;" class="text-center text-nowrap">Status BPO</th>
                                    <th style="width: 110px;" class="text-center text-nowrap">Tgl Ajukan</th>
                                    <th style="width: 90px;" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $idx => $r)
                                    <tr>
                                        <td class="text-center text-muted font-weight-500">
                                            {{ $requests->firstItem() + $idx }}
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-dark">{{ $r->nomor_tiket }}</span>
                                            <div class="small text-muted">{{ $r->tipe_pemohon }}</div>
                                        </td>
                                        <td>
                                            <div class="font-weight-semibold text-dark">{{ $r->email_usulan }}</div>
                                            <small class="text-muted">{{ Str::limit($r->keperluan, 30) }}</small>
                                        </td>
                                        <td>
                                            @if($r->email_rekomendasi_sdm)
                                                <div class="font-weight-bold text-primary">
                                                    <i class="fas fa-check-circle text-info mr-1"></i>{{ $r->email_rekomendasi_sdm }}
                                                </div>
                                                @if($r->email_rekomendasi_sdm !== $r->email_usulan)
                                                    <small class="badge badge-warning px-1 py-0" style="font-size: 0.65rem;">Disesuaikan SDM</small>
                                                @endif
                                            @else
                                                <span class="text-muted font-italic">Menunggu review SDM</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($r->email_final_dibuat)
                                                <div class="font-weight-bold text-success">
                                                    <i class="fas fa-certificate text-success mr-1"></i>{{ $r->email_final_dibuat }}
                                                </div>
                                                <small class="text-muted">Password: <code>{{ $r->password_sementara ?? 'Tersedia' }}</code></small>
                                            @else
                                                <span class="text-muted font-italic">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap align-middle">
                                            {!! $r->status_badge !!}
                                        </td>
                                        <td class="text-center text-muted">
                                            <small>{{ $r->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="showDetailModal({{ json_encode($r) }})" title="Lihat Status & Kredensial">
                                                <i class="fas fa-eye mr-1"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px; background: rgba(9,75,84,0.08); color: #094b54;">
                                                <i class="fas fa-envelope-open-text fa-2x"></i>
                                            </div>
                                            <h6 class="font-weight-bold text-dark mb-1">Belum Ada Pengajuan Akun Email</h6>
                                            <p class="text-muted small mb-3">Anda belum memiliki riwayat pengajuan permohonan akun email resmi (@tsu.ac.id).</p>
                                            <button type="button" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm" style="background: #094b54; border-color: #094b54;" data-toggle="modal" data-target="#modalTambahRequestUser">
                                                <i class="fas fa-plus mr-1"></i> Ajukan Akun Email Sekarang
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($requests->hasPages())
                    <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>

        </div>
    </section>

    {{-- MODAL BUAT PENGAJUAN AKUN EMAIL BARU --}}
    <div class="modal fade" id="modalTambahRequestUser" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-paper-plane text-primary mr-2" style="color: #094b54 !important;"></i>Form Pengajuan Akun Email Resmi (@tsu.ac.id)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('users.request-email.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 mb-3 small d-flex align-items-center" style="border-radius: 8px; background: rgba(9, 75, 84, 0.08); color: #094b54;">
                            <i class="fas fa-info-circle fa-2x mr-3"></i>
                            <div>
                                Data pemohon otomatis terisi dari akun profil Anda. Usulan alamat email akan diverifikasi dan disesuaikan format standarnya oleh Tim SDM sebelum diterbitkan oleh Tim PIKDI.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-dark">Nama Pemohon</label>
                                <input type="text" name="nama_pemohon" class="form-control form-control-sm bg-light" value="{{ $profile->nama ?? $user->name }}" readonly>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-dark">NIP / NIK / NIDN</label>
                                <input type="text" name="nik_nip" class="form-control form-control-sm bg-light" value="{{ $profile->nomor_induk ?? ($user->username ?? '-') }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-dark">Unit Kerja / Fakultas</label>
                                <input type="text" name="unit_nama" class="form-control form-control-sm bg-light" value="{{ $profile->unit->nama_unit ?? ($user->unit ?? 'Universitas Tiga Serangkai') }}" readonly>
                                <input type="hidden" name="unit_id" value="{{ $profile->unit_id ?? '' }}">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold small text-dark">Tipe Pemohon <span class="text-danger">*</span></label>
                                <select name="tipe_pemohon" class="form-control form-control-sm" required>
                                    <option value="{{ $profile->tipe_karyawan ?? 'Tendik' }}" selected>{{ $profile->tipe_karyawan ?? 'Tendik' }} (Pribadi Pegawai)</option>
                                    <option value="Unit Kerja">Unit Kerja / Divisi</option>
                                    <option value="Organisasi / Kepanitiaan">Organisasi / Kepanitiaan Kampus</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-7 form-group mb-3">
                                <label class="font-weight-bold small text-dark">Usulan Alamat Email (@tsu.ac.id) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="email_usulan" id="input_user_email_usulan" class="form-control font-weight-bold" placeholder="contoh: nama.lengkap" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold bg-light">@tsu.ac.id</span>
                                    </div>
                                </div>
                                <small class="text-muted">Masukkan username usulan. Format akhir akan ditinjau SDM sesuai ketentuan universitas.</small>
                            </div>
                            <div class="col-md-5 form-group mb-3">
                                <label class="font-weight-bold small text-dark">No. WhatsApp Aktif <span class="text-danger">*</span></label>
                                <input type="text" name="kontak_hp" class="form-control form-control-sm" value="{{ $profile->no_hp ?? '' }}" placeholder="08xxxxxxxxxx" required>
                                <small class="text-muted">Untuk konfirmasi aktivasi akun &amp; pengiriman password.</small>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark">Email Alternatif / Pribadi</label>
                            <input type="email" name="email_alternatif" class="form-control form-control-sm" value="{{ $user->email ?? '' }}" placeholder="email.pribadi@gmail.com">
                            <small class="text-muted">Digunakan sebagai email pemulihan (recovery email) jika diperlukan.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-dark">Keperluan Pembuatan Email <span class="text-danger">*</span></label>
                            <input type="text" name="keperluan" class="form-control form-control-sm" placeholder="Contoh: Korespondensi akademik, publikasi jurnal, operasional panitia..." required>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-dark">Catatan Tambahan (Opsional)</label>
                            <textarea name="catatan_pemohon" class="form-control form-control-sm" rows="2" placeholder="Tuliskan catatan khusus bila ada..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm rounded-pill px-4 font-weight-bold text-white shadow-sm" style="background: #094b54;">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL PERMOHONAN & STATUS KREDENSIAL --}}
    <div class="modal fade" id="modalDetailUserRequest" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-ticket-alt text-primary mr-2" style="color: #094b54 !important;"></i>Detail Permohonan <span id="user_dtl_tiket"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    {{-- Box Kredensial jika Selesai --}}
                    <div id="box_kredensial_selesai" class="p-3 mb-3 rounded border" style="background: #ecfdf5; border-color: #a7f3d0 !important; display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="font-weight-bold text-success">
                                <i class="fas fa-check-circle mr-1"></i> Akun Email Resmi Anda Telah Aktif!
                            </div>
                            <span class="badge badge-success px-2 py-1">Siap Digunakan</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <table class="table table-sm table-borderless small mb-0">
                                    <tr>
                                        <td style="width: 130px;" class="text-muted">Alamat Resmi:</td>
                                        <td class="font-weight-bold text-dark" id="box_email_aktif">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Password Sementara:</td>
                                        <td><code class="font-weight-bold" id="box_password_aktif">-</code></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-5 text-md-right mt-2 mt-md-0">
                                <a href="https://mail.google.com" target="_blank" class="btn btn-sm btn-success rounded-pill font-weight-bold shadow-sm px-3">
                                    <i class="fab fa-google mr-1"></i> Buka Google Mail
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless small mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 140px;">Nama Pemohon</td>
                                    <td class="font-weight-bold text-dark" id="user_dtl_nama">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Unit Kerja</td>
                                    <td class="font-weight-bold text-dark" id="user_dtl_unit">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. WhatsApp</td>
                                    <td class="text-dark" id="user_dtl_hp">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 border-left">
                            <table class="table table-sm table-borderless small mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 140px;">Usulan Pemohon</td>
                                    <td class="font-weight-bold text-dark" id="user_dtl_usulan">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Rekomendasi SDM</td>
                                    <td class="font-weight-bold text-primary" id="user_dtl_rekomendasi">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Terkini</td>
                                    <td id="user_dtl_status">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.72rem;">Keperluan</label>
                        <p class="small text-dark p-2 rounded bg-light border mb-0" id="user_dtl_keperluan">-</p>
                    </div>

                    {{-- Catatan SDM / PIKDI --}}
                    <div id="box_catatan_petugas" class="mb-3" style="display: none;">
                        <label class="small text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.72rem;">Catatan Petugas (SDM / PIKDI)</label>
                        <p class="small text-dark p-2 rounded bg-light border mb-0" id="user_dtl_catatan_petugas">-</p>
                    </div>

                    {{-- Timeline BPO --}}
                    <div class="mt-4">
                        <label class="small text-muted font-weight-bold text-uppercase mb-2" style="font-size: 0.72rem;">
                            <i class="fas fa-history mr-1"></i> Perjalanan Status Permohonan
                        </label>
                        <div class="p-3 rounded bg-light border" id="user_dtl_timeline_container">
                            {{-- Injected dynamically --}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function showDetailModal(data) {
        $('#user_dtl_tiket').text(data.nomor_tiket);
        $('#user_dtl_nama').text(data.nama_pemohon);
        $('#user_dtl_unit').text(data.unit_nama + ' (' + data.tipe_pemohon + ')');
        $('#user_dtl_hp').text(data.kontak_hp || '-');
        $('#user_dtl_usulan').text(data.email_usulan);
        $('#user_dtl_rekomendasi').text(data.email_rekomendasi_sdm || 'Menunggu verifikasi SDM');
        $('#user_dtl_status').html(data.status_badge || ('<span class="badge badge-info px-2 py-1 text-nowrap" style="white-space: nowrap;">' + data.status + '</span>'));
        $('#user_dtl_keperluan').text(data.keperluan);

        if (data.status === 'Selesai' && data.email_final_dibuat) {
            $('#box_kredensial_selesai').show();
            $('#box_email_aktif').text(data.email_final_dibuat);
            $('#box_password_aktif').text(data.password_sementara || 'TigaSerangkai#' + new Date().getFullYear());
        } else {
            $('#box_kredensial_selesai').hide();
        }

        var catatan = [];
        if (data.catatan_sdm) catatan.push('SDM: ' + data.catatan_sdm);
        if (data.catatan_pikdi) catatan.push('PIKDI: ' + data.catatan_pikdi);

        if (catatan.length > 0) {
            $('#box_catatan_petugas').show();
            $('#user_dtl_catatan_petugas').html(catatan.join('<br>'));
        } else {
            $('#box_catatan_petugas').hide();
        }

        var timelineHtml = '<ul class="list-unstyled mb-0 small">';
        if (data.riwayat_status && data.riwayat_status.length > 0) {
            data.riwayat_status.forEach(function(rs) {
                timelineHtml += '<li class="mb-2 d-flex align-items-start">' +
                    '<span class="timeline-dot bg-success mt-1"></span>' +
                    '<div><strong class="text-dark mr-2">' + rs.tanggal + '</strong>' +
                    '<span class="text-muted">' + rs.status + '</span></div>' +
                '</li>';
            });
        } else {
            timelineHtml += '<li class="text-muted font-italic">Belum ada riwayat aktivitas.</li>';
        }
        timelineHtml += '</ul>';

        $('#user_dtl_timeline_container').html(timelineHtml);
        $('#modalDetailUserRequest').modal('show');
    }
</script>
@endsection
