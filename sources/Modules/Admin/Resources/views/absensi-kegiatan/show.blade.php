@extends('system::template.admin.header')
@section('title', $title ?? 'Detail Absensi Kegiatan')

@section('content')
    <x-tsu-page-header
        title="Detail Absensi Kegiatan"
        subtitle="Pemantauan kehadiran peserta secara real-time, verifikasi foto selfie, dan rekapitulasi data kegiatan"
        :icon="$menuIcon ?? 'fas fa-calendar-check'"
        :breadcrumb="true"
    />

    <section class="content">
        <div class="container-fluid">

            {{-- Back button & Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 10px;">
                <a href="{{ route('admin.absensi-kegiatan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Kegiatan
                </a>
                <div class="d-flex" style="gap: 8px;">
                    <a href="{{ route('admin.absensi-kegiatan.print', $kegiatan->id) }}" target="_blank" class="btn btn-info btn-sm rounded-pill px-3 font-weight-bold shadow-sm">
                        <i class="fas fa-print mr-1"></i> Cetak / Print Daftar Hadir
                    </a>
                </div>
            </div>

            {{-- Event Information Banner Card --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; overflow: hidden; background: linear-gradient(135deg, #f8fcfd 0%, #eef7f8 100%); border-left: 5px solid #094b54 !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8 mb-3 mb-lg-0">
                            <div class="d-flex align-items-center mb-2" style="gap: 8px;">
                                <span class="badge badge-info px-2 py-1 rounded">{{ $kegiatan->kategori }}</span>
                                @if($kegiatan->status === 'Dibuka')
                                    <span class="badge badge-success px-2 py-1 rounded-pill">Status: Dibuka (Presensi Aktif)</span>
                                @elseif($kegiatan->status === 'Selesai')
                                    <span class="badge badge-secondary px-2 py-1 rounded-pill">Status: Selesai</span>
                                @elseif($kegiatan->status === 'Draft')
                                    <span class="badge badge-warning px-2 py-1 rounded-pill text-dark">Status: Draft</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1 rounded-pill">Status: {{ $kegiatan->status }}</span>
                                @endif
                                <span class="badge badge-light border text-muted px-2 py-1 rounded">
                                    <i class="fas fa-users mr-1"></i>{{ $kegiatan->target_peserta }}
                                </span>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-2" style="font-size: 1.35rem;">
                                {{ $kegiatan->nama_kegiatan }}
                            </h4>
                            <div class="row text-muted small mt-2" style="gap: 4px 0;">
                                <div class="col-sm-6 mb-1">
                                    <i class="far fa-calendar-alt text-primary mr-1"></i> <strong>Tanggal:</strong> {{ $kegiatan->tanggal_kegiatan->format('d F Y') }}
                                </div>
                                <div class="col-sm-6 mb-1">
                                    <i class="far fa-clock text-secondary mr-1"></i> <strong>Waktu:</strong> {{ substr($kegiatan->jam_mulai, 0, 5) }} - {{ substr($kegiatan->jam_selesai, 0, 5) }} WIB
                                </div>
                                <div class="col-sm-6 mb-1">
                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> <strong>Lokasi:</strong> {{ $kegiatan->lokasi }}
                                </div>
                                <div class="col-sm-6 mb-1">
                                    <i class="fas fa-building text-info mr-1"></i> <strong>Penyelenggara:</strong> {{ optional($kegiatan->penyelenggaraUnit)->nama_unit ?? 'Universitas' }}
                                </div>
                                @if($kegiatan->penanggungJawab)
                                    <div class="col-sm-12">
                                        <i class="fas fa-user-tie text-success mr-1"></i> <strong>PIC / Penanggung Jawab:</strong> {{ $kegiatan->penanggungJawab->nomor_induk }} - {{ $kegiatan->penanggungJawab->nama }}
                                    </div>
                                @endif
                            </div>
                            @if($kegiatan->keterangan)
                                <div class="mt-3 p-2 bg-white rounded border text-muted small" style="border-radius: 8px;">
                                    <strong><i class="fas fa-info-circle text-info mr-1"></i>Catatan:</strong> {{ $kegiatan->keterangan }}
                                </div>
                            @endif
                        </div>

                        {{-- Quick Stats Box on the Right --}}
                        <div class="col-lg-4 border-left pl-lg-4">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-white rounded shadow-sm border">
                                        <span class="text-success font-weight-bold d-block" style="font-size: 1.6rem;">{{ $countYa }}</span>
                                        <small class="text-muted font-weight-bold">Hadir (Ya)</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-white rounded shadow-sm border">
                                        <span class="text-warning font-weight-bold d-block" style="font-size: 1.6rem;">{{ $countTerlambat }}</span>
                                        <small class="text-muted font-weight-bold">Terlambat</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-white rounded shadow-sm border">
                                        <span class="text-danger font-weight-bold d-block" style="font-size: 1.6rem;">{{ $countTidak }}</span>
                                        <small class="text-muted font-weight-bold">Tidak Hadir</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-white rounded shadow-sm border" style="background-color: #f0fafc !important;">
                                        <span class="text-dark font-weight-bold d-block" style="font-size: 1.6rem;">{{ $totalRespon }}</span>
                                        <small class="text-muted font-weight-bold">Total Respon</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Attendees List Card --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h5 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-users-cog text-primary mr-2"></i>Daftar Kehadiran Peserta
                        </h5>
                        <small class="text-muted">Total {{ $totalRespon }} pegawai telah mengonfirmasi absensi kegiatan ini.</small>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 80px;" class="text-center">Selfie</th>
                                    <th>Nama Pegawai & NIP</th>
                                    <th>Unit Kerja</th>
                                    <th style="width: 130px;" class="text-center">Status Kehadiran</th>
                                    <th style="width: 150px;">Waktu Presensi</th>
                                    <th>Keterangan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($presensis as $index => $p)
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold align-middle">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($p->foto_selfie)
                                                <img src="{{ $p->foto_url }}" 
                                                    alt="Foto Selfie" 
                                                    class="img-thumbnail rounded-circle btn-zoom-foto" 
                                                    style="width: 46px; height: 46px; object-fit: cover; cursor: pointer; border: 2px solid #094b54;"
                                                    data-src="{{ $p->foto_url }}"
                                                    data-nama="{{ optional($p->pegawai)->nama ?? optional($p->user)->name }}"
                                                    title="Klik untuk melihat foto resolusi penuh">
                                            @else
                                                <div class="bg-light rounded-circle text-muted d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border: 1px dashed #ccc;">
                                                    <i class="fas fa-user text-secondary"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                                {{ optional($p->pegawai)->nama ?? optional($p->user)->name ?? 'Pegawai' }}
                                            </div>
                                            <small class="text-muted">
                                                NIP: {{ optional($p->pegawai)->nomor_induk ?? '-' }} &bull; {{ optional($p->pegawai)->tipe_karyawan ?? 'Pegawai' }}
                                            </small>
                                        </td>
                                        <td class="align-middle small">
                                            <span class="font-weight-bold text-dark">
                                                {{ optional(optional($p->pegawai)->unit)->nama_unit ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($p->status_kehadiran === 'Ya')
                                                <span class="badge badge-success px-3 py-2 rounded-pill font-weight-bold" style="font-size: 0.85rem;">
                                                    <i class="fas fa-check mr-1"></i> Hadir
                                                </span>
                                            @elseif($p->status_kehadiran === 'Terlambat')
                                                <span class="badge badge-warning text-dark px-3 py-2 rounded-pill font-weight-bold" style="font-size: 0.85rem;">
                                                    <i class="fas fa-user-clock mr-1"></i> Terlambat
                                                </span>
                                            @else
                                                <span class="badge badge-danger px-3 py-2 rounded-pill font-weight-bold" style="font-size: 0.85rem;">
                                                    <i class="fas fa-times mr-1"></i> Tidak Hadir
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle small">
                                            <div class="font-weight-bold text-dark">
                                                {{ $p->waktu_presensi->format('d/m/Y') }}
                                            </div>
                                            <div class="text-muted">
                                                <i class="far fa-clock mr-1"></i>{{ $p->waktu_presensi->format('H:i:s') }} WIB
                                            </div>
                                        </td>
                                        <td class="align-middle small text-muted">
                                            @if($p->keterangan)
                                                <div class="p-2 bg-light rounded" style="font-size: 0.88rem;">
                                                    {{ $p->keterangan }}
                                                </div>
                                            @else
                                                <span class="text-muted font-italic">- Tidak ada keterangan -</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-user-clock fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                            <div class="font-weight-bold">Belum Ada Presensi Masuk</div>
                                            <small>Peserta yang membuka sistem saat kegiatan berlangsung akan otomatis mengisi absensi.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Modal Zoom Foto Selfie --}}
    <div class="modal fade" id="modalZoomFoto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-2 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="modal-title font-weight-bold mb-0 text-white" id="zoomFotoNama">Foto Selfie Kehadiran</h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 text-center bg-dark">
                    <img id="zoomFotoImg" src="#" alt="Foto Selfie" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function() {
    $('.btn-zoom-foto').on('click', function() {
        var src = $(this).data('src');
        var nama = $(this).data('nama');
        $('#zoomFotoNama').text('Foto Kehadiran: ' + nama);
        $('#zoomFotoImg').attr('src', src);
        $('#modalZoomFoto').modal('show');
    });
});
</script>
@endpush
