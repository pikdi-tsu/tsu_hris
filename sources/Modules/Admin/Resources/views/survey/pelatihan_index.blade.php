@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i>Survey Kepuasan Training
                </h1>
                <p class="text-muted small mb-0">Daftar pelatihan yang Anda ikuti. Berikan umpan balik (feedback) dan unggah sertifikat pelatihan untuk otomatis tercatat di Roadmap Pengembangan SDM Anda.</p>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-tasks text-primary mr-2"></i>Pelatihan yang Diikuti ({{ $pesertaList->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                @if($pesertaList->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-check fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                        <h6 class="font-weight-bold text-dark">Belum Ada Riwayat Pelatihan</h6>
                        <p class="small mb-0">Anda belum terdaftar dalam agenda pelatihan dari Biro SDM.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Nama Pelatihan / Kompetensi</th>
                                    <th style="width: 140px;">Penyelenggara</th>
                                    <th style="width: 180px;">Tanggal Pelaksanaan</th>
                                    <th style="width: 140px;" class="text-center">Kehadiran</th>
                                    <th style="width: 170px;" class="text-center">Status Survei & Sertifikat</th>
                                    <th style="width: 180px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pesertaList as $idx => $item)
                                    @php $t = $item->training; @endphp
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark">{{ $t->nama_training ?? '-' }}</div>
                                            @if($t && $t->lokasi)
                                                <small class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $t->lokasi }}</small>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge {{ ($t->penyelenggara ?? '') == 'Internal TSU' ? 'badge-primary' : 'badge-info' }} px-2 py-1 font-weight-normal">
                                                {{ $t->penyelenggara ?? 'Internal TSU' }}
                                            </span>
                                        </td>
                                        <td class="align-middle small font-weight-bold text-dark">
                                            @if($t)
                                                {{ $t->tanggal_mulai->translatedFormat('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge {{ $item->status_kehadiran == 'Hadir' ? 'badge-success' : 'badge-warning' }} px-2 py-1 font-weight-normal">
                                                {{ $item->status_kehadiran }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($item->is_survey_filled)
                                                <span class="badge badge-success px-2 py-1 font-weight-normal shadow-sm">
                                                    <i class="fas fa-check-circle mr-1"></i> Sudah Mengisi
                                                </span>
                                                @if($item->sertifikat_file)
                                                    <div class="mt-1">
                                                        <a href="{{ asset($item->sertifikat_file) }}" target="_blank" class="badge badge-light border text-primary" style="text-decoration: none;">
                                                            <i class="fas fa-certificate mr-1"></i> Sertifikat Ada
                                                        </a>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-normal shadow-sm">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> Wajib Diisi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if(!$item->is_survey_filled)
                                                <a href="{{ route('admin.survey.pelatihan.form', $item->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold shadow-sm">
                                                    <i class="fas fa-edit mr-1"></i> Isi Survei & Sertifikat
                                                </a>
                                            @else
                                                <a href="{{ route('admin.survey.pelatihan.form', $item->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="fas fa-eye mr-1"></i> Lihat / Edit
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>
@endsection
