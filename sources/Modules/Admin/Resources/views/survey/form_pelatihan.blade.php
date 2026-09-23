@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="mb-1">
            <a href="{{ route('admin.survey.pelatihan.index') }}" class="text-primary font-weight-bold small">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Survei Kepuasan Training
            </a>
        </div>
        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
            <i class="fas fa-poll text-primary mr-2"></i>Survey Kepuasan Training & Unggah Sertifikat
        </h1>
        <p class="text-muted small mb-0">Berikan umpan balik terhadap pelatihan yang telah Anda ikuti dan unggah dokumen sertifikat untuk sinkronisasi ke Roadmap Pengembangan SDM.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Card Info Pelatihan -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-left: 4px solid #0284c7 !important;">
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge badge-primary px-2 py-1 font-weight-normal mb-1">{{ $training->penyelenggara }}</span>
                        <h5 class="font-weight-bold text-dark mb-1">{{ $training->nama_training }}</h5>
                        <p class="text-muted small mb-0">
                            <i class="far fa-calendar-alt text-primary mr-1"></i>
                            {{ $training->tanggal_mulai->translatedFormat('d F Y') }} - {{ $training->tanggal_selesai->translatedFormat('d F Y') }}
                            @if($training->lokasi)
                                &nbsp;|&nbsp; <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $training->lokasi }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-md-right mt-2 mt-md-0">
                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 0.82rem;">
                            <i class="fas fa-user-check mr-1"></i> Status Kehadiran: {{ $peserta->status_kehadiran }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Survei & Sertifikat -->
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-star-half-alt text-warning mr-2"></i>Form Evaluasi & Berkas Sertifikat
                </h6>
            </div>
            <form action="{{ route('admin.survey.pelatihan.submit', $peserta->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">

                    <!-- Alert Auto-Sync Roadmap -->
                    <div class="alert alert-info border-0 d-flex align-items-center mb-4" style="border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: #0369a1;">
                        <i class="fas fa-sync-alt fa-2x mr-3 text-primary"></i>
                        <div>
                            <strong class="font-weight-bold">Integrasi Otomatis ke Roadmap Pengembangan SDM:</strong>
                            <p class="mb-0 small">
                                Setelah survei disimpan dan sertifikat diunggah, data kompetensi ini akan <strong>otomatis disinkronkan ke lembar kerja Roadmap Pengembangan Dosen / Tendik</strong> Anda tanpa perlu input manual ke admin SDM.
                            </p>
                        </div>
                    </div>

                    @php
                        $resp = $peserta->surveyResponse;
                    @endphp

                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                        Bagian 1: Penilaian Kepuasan Pelatihan (Skala 1 - 5)
                    </h6>

                    <!-- Pertanyaan 1: Kualitas Materi -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark mb-1">
                            1. Kualitas & Kedalaman Materi Pelatihan <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Materi yang disampaikan relevan, terstruktur dengan baik, dan mudah dipahami.</p>
                        <div class="d-flex flex-wrap" style="gap: 12px;">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="materi_{{ $i }}" name="skor_materi" value="{{ $i }}" class="custom-control-input"
                                           {{ old('skor_materi', $resp->skor_materi ?? 5) == $i ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="materi_{{ $i }}">
                                        <strong>{{ $i }}</strong>
                                        <small class="text-muted">({{ $i == 1 ? 'Sangat Buruk' : ($i == 3 ? 'Cukup' : ($i == 5 ? 'Sangat Baik' : $i)) }})</small>
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Pertanyaan 2: Narasumber -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark mb-1">
                            2. Kompetensi & Kemampuan Komunikasi Narasumber / Instruktur <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Narasumber menguasai materi, interaktif, dan mampu menjawab pertanyaan dengan jelas.</p>
                        <div class="d-flex flex-wrap" style="gap: 12px;">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="narasumber_{{ $i }}" name="skor_narasumber" value="{{ $i }}" class="custom-control-input"
                                           {{ old('skor_narasumber', $resp->skor_narasumber ?? 5) == $i ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="narasumber_{{ $i }}">
                                        <strong>{{ $i }}</strong>
                                        <small class="text-muted">({{ $i == 1 ? 'Sangat Buruk' : ($i == 3 ? 'Cukup' : ($i == 5 ? 'Sangat Baik' : $i)) }})</small>
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Pertanyaan 3: Fasilitas & Penyelenggaraan -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark mb-1">
                            3. Fasilitas & Kesiapan Panitia Penyelenggara <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Platform/ruangan, konektivitas, modul pelatihan, dan koordinasi panitia memadai.</p>
                        <div class="d-flex flex-wrap" style="gap: 12px;">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="fasilitas_{{ $i }}" name="skor_fasilitas" value="{{ $i }}" class="custom-control-input"
                                           {{ old('skor_fasilitas', $resp->skor_fasilitas ?? 5) == $i ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="fasilitas_{{ $i }}">
                                        <strong>{{ $i }}</strong>
                                        <small class="text-muted">({{ $i == 1 ? 'Sangat Buruk' : ($i == 3 ? 'Cukup' : ($i == 5 ? 'Sangat Baik' : $i)) }})</small>
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Pertanyaan 4: Relevansi dengan Pekerjaan -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark mb-1">
                            4. Relevansi Manfaat Terhadap Bidang Kerja / Pengajaran <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Pelatihan ini berdampak langsung terhadap peningkatan keterampilan kerja Anda di TSU.</p>
                        <div class="d-flex flex-wrap" style="gap: 12px;">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="relevansi_{{ $i }}" name="skor_relevansi" value="{{ $i }}" class="custom-control-input"
                                           {{ old('skor_relevansi', $resp->skor_relevansi ?? 5) == $i ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="relevansi_{{ $i }}">
                                        <strong>{{ $i }}</strong>
                                        <small class="text-muted">({{ $i == 1 ? 'Sangat Rendah' : ($i == 3 ? 'Cukup' : ($i == 5 ? 'Sangat Tinggi' : $i)) }})</small>
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Feedback & Saran -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">
                            Umpan Balik / Saran Peningkatan untuk Pelatihan Selanjutnya
                        </label>
                        <textarea name="feedback_manfaat" class="form-control" rows="3" placeholder="Tuliskan saran, kritik konstruktif, atau topik pelatihan lanjutan yang diharapkan...">{{ old('feedback_manfaat', $resp->feedback_manfaat ?? '') }}</textarea>
                    </div>

                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3 mt-4">
                        <i class="fas fa-certificate text-warning mr-1"></i>Bagian 2: Unggah Sertifikat Hasil Pelatihan
                    </h6>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Nomor Sertifikat (Opsional)</label>
                            <input type="text" name="sertifikat_nomor" class="form-control"
                                   placeholder="Contoh: 045/TSU-TRN/IX/2026 atau No. Registrasi BNSP"
                                   value="{{ old('sertifikat_nomor', $peserta->sertifikat_nomor) }}">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">
                                Berkas Sertifikat (PDF / JPG / PNG)
                                @if(!$peserta->sertifikat_file) <span class="text-danger">*</span> @endif
                            </label>
                            <input type="file" name="sertifikat_file" class="form-control-file border rounded p-2 w-100" accept=".pdf,.jpg,.jpeg,.png"
                                   {{ !$peserta->sertifikat_file ? 'required' : '' }}>
                            <small class="text-muted">Format file: PDF, JPG, atau PNG (Maksimal ukuran: 5MB).</small>

                            @if($peserta->sertifikat_file)
                                <div class="mt-2 p-2 bg-light rounded border d-flex align-items-center justify-content-between">
                                    <div>
                                        <i class="fas fa-file-pdf text-danger mr-1"></i>
                                        <small class="text-success font-weight-bold">Sertifikat sudah terunggah sebelumnya.</small>
                                    </div>
                                    <a href="{{ asset($peserta->sertifikat_file) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-external-link-alt mr-1"></i> Lihat Berkas
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.survey.pelatihan.index') }}" class="btn btn-secondary rounded-pill px-4">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 font-weight-bold shadow-sm">
                        <i class="fas fa-check-circle mr-1"></i> Simpan Survei & Sertifikat
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection
