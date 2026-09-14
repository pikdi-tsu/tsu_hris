@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    <link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        :root {
            --tsu-primary: #094b54;
            --tsu-primary-dark: #063339;
            --tsu-primary-light: #cce6e9;
            --tsu-teal-accent: #0ea5e9;
            --tsu-surface: #ffffff;
            --tsu-bg-subtle: #f8fafc;
            --tsu-border: #e2e8f0;
            --tsu-text-main: #0f172a;
            --tsu-text-muted: #64748b;
        }

        .tsu-bkd-card {
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            background: #ffffff;
            transition: box-shadow 0.2s ease;
        }

        .tsu-bkd-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        }

        .tsu-bkd-card-header {
            background: #ffffff;
            border-bottom: 1px solid var(--tsu-border);
            padding: 1.1rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tsu-periode-box {
            background: linear-gradient(135deg, rgba(9, 75, 84, 0.04) 0%, rgba(14, 165, 233, 0.08) 100%);
            border: 1px solid rgba(9, 75, 84, 0.15);
            border-radius: 10px;
            padding: 1.15rem;
            position: relative;
        }

        .tsu-file-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .tsu-file-dropzone:hover, .tsu-file-dropzone.dragover {
            border-color: var(--tsu-primary);
            background: rgba(9, 75, 84, 0.03);
        }

        .tsu-file-dropzone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .tsu-btn-primary {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            border: none;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.65rem 1.25rem;
            box-shadow: 0 2px 6px rgba(9, 75, 84, 0.25);
            transition: all 0.2s ease;
        }

        .tsu-btn-primary:hover {
            background: linear-gradient(135deg, #063339 0%, #094b54 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(9, 75, 84, 0.35);
            transform: translateY(-1px);
        }

        /* TABLE STYLING */
        .tsu-table-modern thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .tsu-table-modern tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .tsu-table-modern tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Laporan BKD / LKD Dosen"
        subtitle="Unggah dan pantau status berkas Laporan Kinerja Dosen (LKD / BKD) per Semester"
        :icon="$menuIcon ?? 'fas fa-graduation-cap'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">

            @if($isDosenBaru)
                <div class="alert border-0 shadow-sm mb-4" style="background: rgba(2, 132, 199, 0.08); border-left: 4px solid #0284c7 !important; border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 text-info">
                            <i class="fas fa-user-clock fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-1">Masa Orientasi Dosen Baru</h6>
                            <p class="mb-0 text-muted small">
                                Masa kerja Anda di Universitas Tanri Abeng tercatat kurang dari 1 tahun (2 semester). Anda berada dalam masa orientasi dan tidak ditandai sebagai penunggak laporan. Namun, jika Anda telah memiliki laporan LKD/BKD dari SISTER, Anda tetap dapat mengunggahnya pada form di bawah ini.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                {{-- Form Upload Periode Aktif --}}
                <div class="col-lg-5 col-12 mb-4">
                    <div class="tsu-bkd-card h-100">
                        <div class="tsu-bkd-card-header">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-cloud-upload-alt" style="color: var(--tsu-primary);"></i> Unggah Laporan BKD Semester Ini
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if($activePeriode)
                                <div class="tsu-periode-box mb-4">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge" style="background: rgba(9, 75, 84, 0.12); color: #094b54; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em; border-radius: 6px; padding: 4px 8px;">
                                            PERIODE AKTIF
                                        </span>
                                        <span class="badge badge-success" style="border-radius: 9999px; padding: 4px 8px; font-weight: 600; font-size: 0.72rem;">
                                            <i class="fas fa-check-circle mr-1"></i> Buka
                                        </span>
                                    </div>
                                    <h5 class="font-weight-bold mb-1" style="color: var(--tsu-primary); font-size: 1.15rem;">
                                        {{ $activePeriode->nama_periode }}
                                    </h5>
                                    <div class="small text-muted" style="display: flex; align-items: center; gap: 6px;">
                                        <i class="far fa-calendar-alt text-secondary"></i>
                                        <span>Batas Pelaporan: <strong>{{ $activePeriode->tgl_mulai ? $activePeriode->tgl_mulai->format('d M Y') : '-' }}</strong> s.d. <strong>{{ $activePeriode->tgl_selesai ? $activePeriode->tgl_selesai->format('d M Y') : '-' }}</strong></span>
                                    </div>
                                </div>

                                @if($myLaporan)
                                    <div class="alert border-0 py-3 mb-4" style="background: rgba(16, 185, 129, 0.08); border-left: 4px solid #10b981 !important; border-radius: 10px;">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle text-success fa-lg mr-2"></i>
                                            <strong class="text-dark">Anda sudah mengunggah laporan untuk periode ini.</strong>
                                        </div>
                                        <div class="small text-muted mb-3">
                                            <div>Diunggah pada: <strong class="text-dark">{{ $myLaporan->tanggal_upload->format('d F Y H:i') }}</strong></div>
                                            <div>Status Verifikasi: 
                                                @if($myLaporan->status_verifikasi === 'diverifikasi')
                                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; border-radius: 9999px; font-weight: 600; padding: 2px 8px;">Diverifikasi (Valid)</span>
                                                @elseif($myLaporan->status_verifikasi === 'perlu_revisi')
                                                    <span class="badge" style="background: rgba(217, 119, 6, 0.15); color: #b45309; border-radius: 9999px; font-weight: 600; padding: 2px 8px;">Perlu Revisi</span>
                                                @else
                                                    <span class="badge" style="background: rgba(100, 116, 139, 0.15); color: #475569; border-radius: 9999px; font-weight: 600; padding: 2px 8px;">Tersimpan (Menunggu Review)</span>
                                                @endif
                                            </div>
                                            @if($myLaporan->catatan)
                                                <div class="mt-1 font-italic">Catatan SDM: "<span class="text-dark">{{ $myLaporan->catatan }}</span>"</div>
                                            @endif
                                        </div>
                                        <a href="{{ route('users.bkd.stream', $myLaporan->id) }}" target="_blank" class="btn btn-sm btn-outline-success font-weight-bold" style="border-radius: 6px; background: #ffffff;">
                                            <i class="fas fa-file-pdf mr-1"></i> Buka File PDF Saya
                                        </a>
                                    </div>
                                    <p class="small text-muted font-italic mb-3">
                                        <i class="fas fa-info-circle mr-1"></i> Ingin memperbarui berkas yang sudah diunggah? Unggah kembali file PDF terbaru di bawah:
                                    </p>
                                @endif

                                <form id="form-user-upload-bkd" action="{{ route('users.bkd.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-dark mb-1">
                                            File PDF Laporan BKD / LKD <span class="text-danger">*</span>
                                        </label>
                                        <div class="tsu-file-dropzone" id="dropzone-bkd">
                                            <input type="file" name="file_pdf" id="file_pdf" accept=".pdf" required onchange="handleFileSelected(this)">
                                            <div id="dropzone-idle">
                                                <i class="fas fa-file-pdf fa-2x mb-2" style="color: #ef4444;"></i>
                                                <div class="font-weight-bold text-dark" style="font-size: 0.92rem;">
                                                    Pilih berkas PDF atau seret ke sini
                                                </div>
                                                <small class="text-muted d-block mt-1">Unduh laporan resmi dari SISTER terlebih dahulu (Maksimal 15 MB)</small>
                                            </div>
                                            <div id="dropzone-selected" style="display: none;">
                                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                                <div class="font-weight-bold text-dark" id="selected-filename">-</div>
                                                <small class="text-muted">Klik untuk memilih file lain</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold text-dark mb-1">Catatan Tambahan (Opsional)</label>
                                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional untuk verifikator SDM..." style="border-radius: 8px; font-size: 0.88rem;">{{ $myLaporan->catatan ?? '' }}</textarea>
                                    </div>

                                    <button type="submit" class="btn tsu-btn-primary btn-block" id="btn-submit-bkd">
                                        <i class="fas fa-save mr-1"></i> {{ $myLaporan ? 'Perbarui Laporan BKD' : 'Simpan & Unggah Laporan BKD' }}
                                    </button>
                                </form>
                            @else
                                <div class="alert border-0 py-4 text-center" style="background: rgba(245, 158, 11, 0.1); border-radius: 10px;">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                    <h6 class="font-weight-bold text-dark mb-1">Belum Ada Periode Aktif</h6>
                                    <p class="small text-muted mb-0">Saat ini belum ada periode pelaporan BKD yang dibuka oleh Bagian SDM.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Riwayat Pelaporan BKD Saya --}}
                <div class="col-lg-7 col-12 mb-4">
                    <div class="tsu-bkd-card h-100">
                        <div class="tsu-bkd-card-header">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-history" style="color: #0284c7;"></i> Riwayat Arsip BKD Saya
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table tsu-table-modern table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Periode Semester</th>
                                            <th class="text-center">Tanggal Unggah</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($riwayatLaporan as $rl)
                                            <tr>
                                                <td class="align-middle">
                                                    <span class="font-weight-bold text-dark d-block">
                                                        {{ $rl->periode->nama_periode ?? '-' }}
                                                    </span>
                                                    @if($rl->file_size)
                                                        <small class="text-muted">{{ round($rl->file_size / 1024) }} KB</small>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center small text-muted">
                                                    {{ $rl->tanggal_upload ? $rl->tanggal_upload->format('d/m/Y H:i') : '-' }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    @if($rl->status_verifikasi === 'diverifikasi')
                                                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">
                                                            Valid
                                                        </span>
                                                    @elseif($rl->status_verifikasi === 'perlu_revisi')
                                                        <span class="badge" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">
                                                            Perlu Revisi
                                                        </span>
                                                    @else
                                                        <span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">
                                                            Tersimpan
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="{{ route('users.bkd.stream', $rl->id) }}" target="_blank" class="btn btn-sm btn-outline-danger font-weight-bold" style="border-radius: 6px; padding: 3px 10px; font-size: 0.8rem;">
                                                        <i class="fas fa-file-pdf mr-1"></i> Unduh PDF
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                                    <div class="font-weight-bold text-dark">Belum Ada Riwayat Laporan</div>
                                                    <small class="text-muted">Arsip laporan BKD yang Anda unggah setiap semester akan tersimpan rapi di sini.</small>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('assets/adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        function handleFileSelected(input) {
            if (input.files && input.files[0]) {
                $('#selected-filename').text(input.files[0].name);
                $('#dropzone-idle').hide();
                $('#dropzone-selected').show();
            } else {
                $('#dropzone-idle').show();
                $('#dropzone-selected').hide();
            }
        }

        $(document).ready(function() {
            $('#form-user-upload-bkd').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);
                let btn = $('#btn-submit-bkd');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah Berkas...');

                pikdiAjax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    loadingText: 'Sedang mengunggah laporan BKD...',
                    onSuccess: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Laporan BKD berhasil diunggah.'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    onError: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan & Unggah Laporan BKD');
                    }
                });
            });
        });
    </script>
@endsection
