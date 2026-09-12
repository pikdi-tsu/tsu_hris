@extends('system::template.admin.header')
@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i>{{ $title }}
                </h1>
                <p class="text-muted mb-0">Unggah dan pantau status berkas Laporan Kinerja Dosen (LKD / BKD) per Semester</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('users.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan BKD</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if($isDosenBaru)
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-user-clock fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold mb-1">Masa Orientasi Dosen Baru</h6>
                        <p class="mb-0 small">
                            Masa kerja Anda di Universitas Tanri Abeng tercatat kurang dari 1 tahun (2 semester). Anda berada dalam masa orientasi dan tidak ditandai sebagai penunggak laporan. Namun, jika Anda telah memiliki laporan LKD/BKD dari SISTER, Anda tetap dapat mengunggahnya pada form di bawah ini.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            {{-- Form Upload Periode Aktif --}}
            <div class="col-lg-5 col-12 mb-4">
                <div class="card card-primary card-outline shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-cloud-upload-alt mr-1 text-primary"></i> Unggah Laporan BKD Semester Ini
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($activePeriode)
                            <div class="p-3 bg-light rounded mb-3 border">
                                <span class="small text-muted font-weight-bold text-uppercase d-block">Periode Pelaporan Aktif:</span>
                                <h5 class="font-weight-bold text-primary mb-1">{{ $activePeriode->nama_periode }}</h5>
                                <small class="text-muted">
                                    Batas Pelaporan: {{ $activePeriode->tgl_mulai ? $activePeriode->tgl_mulai->format('d M Y') : '-' }} s.d. {{ $activePeriode->tgl_selesai ? $activePeriode->tgl_selesai->format('d M Y') : '-' }}
                                </small>
                            </div>

                            @if($myLaporan)
                                <div class="alert alert-success border-0 py-3 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-check-circle fa-lg mr-2"></i>
                                        <strong>Anda sudah mengunggah laporan untuk periode ini.</strong>
                                    </div>
                                    <div class="small mb-2">
                                        Diunggah pada: <strong>{{ $myLaporan->tanggal_upload->format('d F Y H:i') }}</strong>
                                        <br>Status: <strong>{{ ucfirst($myLaporan->status_verifikasi) }}</strong>
                                        @if($myLaporan->catatan)
                                            <br>Catatan SDM: <em>"{{ $myLaporan->catatan }}"</em>
                                        @endif
                                    </div>
                                    <a href="{{ route('users.bkd.stream', $myLaporan->id) }}" target="_blank" class="btn btn-sm btn-outline-success bg-white font-weight-bold">
                                        <i class="fas fa-file-pdf mr-1"></i> Buka File PDF Saya
                                    </a>
                                </div>
                                <hr>
                                <p class="small text-muted font-italic mb-2">Ingin memperbarui berkas yang sudah diunggah? Unggah kembali file PDF terbaru di bawah:</p>
                            @endif

                            <form id="form-user-upload-bkd" action="{{ route('users.bkd.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">File PDF Laporan BKD / LKD <span class="text-danger">*</span></label>
                                    <input type="file" name="file_pdf" class="form-control-file" accept=".pdf" required>
                                    <small class="text-muted" style="font-size: 0.75rem;">Unduh laporan resmi dari SISTER terlebih dahulu, lalu unggah berkas PDF di sini (Maksimal 15 MB).</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark">Catatan Tambahan</label>
                                    <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional untuk verifikator SDM...">{{ $myLaporan->catatan ?? '' }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm" id="btn-submit-bkd">
                                    <i class="fas fa-save mr-1"></i> {{ $myLaporan ? 'Perbarui Laporan BKD' : 'Simpan & Unggah Laporan BKD' }}
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada periode pelaporan BKD yang aktif saat ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Riwayat Pelaporan BKD Saya --}}
            <div class="col-lg-7 col-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-history mr-1 text-info"></i> Riwayat Arsip BKD Saya
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
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
                                            <td class="align-middle font-weight-bold text-dark">
                                                {{ $rl->periode->nama_periode ?? '-' }}
                                            </td>
                                            <td class="align-middle text-center small">
                                                {{ $rl->tanggal_upload ? $rl->tanggal_upload->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($rl->status_verifikasi === 'diverifikasi')
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Valid</span>
                                                @elseif($rl->status_verifikasi === 'perlu_revisi')
                                                    <span class="badge badge-warning px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i> Perlu Revisi</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1">Tersimpan</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('users.bkd.stream', $rl->id) }}" target="_blank" class="btn btn-xs btn-outline-danger font-weight-bold">
                                                    <i class="fas fa-file-pdf mr-1"></i> Unduh PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                Belum ada riwayat arsip laporan BKD yang tersimpan.
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
<script>
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
