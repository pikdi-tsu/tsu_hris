@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <div class="d-flex align-items-center">
                    <h1 class="m-0 text-dark font-weight-bold mr-3" style="font-size: 1.45rem;">
                        <i class="fas fa-clipboard-check text-primary mr-2"></i>Review Pengajuan RKAT
                    </h1>
                    <span class="badge badge-warning px-3 py-1 rounded-pill font-weight-bold">
                        Tingkat: {{ $pengajuan->current_approval_level ?: 'Pemeriksaan' }}
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    Nomor Usulan: <strong>{{ $pengajuan->nomor_pengajuan }}</strong> &bull; Unit: <strong>{{ optional($pengajuan->unit)->nama_unit }}</strong>
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.rkat.approval.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Antrean
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            {{-- Kiri: Detail Usulan & Rincian Biaya --}}
            <div class="col-lg-8 mb-4">
                {{-- Info Usulan --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-info-circle text-primary mr-2"></i>Informasi Kegiatan Usulan
                        </h6>
                    </div>
                    <div class="card-body">
                        <h4 class="font-weight-bold text-dark mb-2">{{ $pengajuan->nama_kegiatan }}</h4>
                        <div class="mb-3">
                            <span class="badge badge-light border mr-1"><i class="fas fa-calendar mr-1"></i>TA {{ optional($pengajuan->periode)->tahun_anggaran }}</span>
                            <span class="badge badge-light border mr-1"><i class="fas fa-building mr-1"></i>{{ optional($pengajuan->unit)->nama_unit }}</span>
                            <span class="badge badge-light border"><i class="fas fa-user mr-1"></i>PIC: {{ optional($pengajuan->pic)->nama }}</span>
                        </div>

                        <div class="row text-sm mb-3">
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted d-block">Target Capaian:</strong>
                                <span class="font-weight-bold text-dark">{{ $pengajuan->target_kuantitas }} {{ $pengajuan->satuan_target }}</span> (Sasaran: {{ $pengajuan->sasaran ?: '-' }})
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted d-block">Program Strategis:</strong>
                                <span class="font-weight-bold text-dark">[{{ optional($pengajuan->program)->kode_program }}] {{ optional($pengajuan->program)->nama_program }}</span>
                            </div>
                            <div class="col-12 mb-2">
                                <strong class="text-muted d-block">Latar Belakang:</strong>
                                <p class="mb-0 text-secondary">{{ $pengajuan->latar_belakang ?: '-' }}</p>
                            </div>
                            <div class="col-12">
                                <strong class="text-muted d-block">Tujuan & Output:</strong>
                                <p class="mb-0 text-secondary">{{ $pengajuan->tujuan ?: '-' }} &bull; Output: {{ $pengajuan->output ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Rincian Biaya --}}
                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-list text-primary mr-2"></i>Komponen Rincian Anggaran ({{ $pengajuan->anggaranItems->count() }} Item)
                        </h6>
                        <span class="badge badge-primary px-2 py-1">Diajukan: Rp {{ number_format($pengajuan->total_anggaran_diajukan, 0, ',', '.') }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 30px;" class="text-center">#</th>
                                        <th>Uraian Komponen</th>
                                        <th style="width: 140px;">Akun COA</th>
                                        <th style="width: 70px;" class="text-center">Qty</th>
                                        <th style="width: 110px;" class="text-right">Satuan (Rp)</th>
                                        <th style="width: 130px;" class="text-right">Subtotal (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pengajuan->anggaranItems as $idx => $item)
                                        <tr>
                                            <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->komponen_biaya }}</strong>
                                                <div class="small text-muted">{{ optional($item->sumberDana)->nama_sumber_dana ?? optional($item->sumberDana)->nama_sumber }}</div>
                                            </td>
                                            <td class="small">{{ optional($item->akun)->kode_akun }}</td>
                                            <td class="text-center font-weight-bold">{{ $item->kuantitas }} {{ $item->satuan }}</td>
                                            <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold text-dark">
                                                {{ number_format($item->total_biaya, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="5" class="text-right">TOTAL USULAN:</td>
                                        <td class="text-right text-primary">Rp {{ number_format($pengajuan->total_anggaran_diajukan, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanan: Form Tindakan Keputusan Approval --}}
            <div class="col-lg-4">
                {{-- Panel Keputusan Approve --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-success text-white py-3">
                        <h6 class="font-weight-bold mb-0">
                            <i class="fas fa-check-circle mr-2"></i>Persetujuan (Setujui Usulan)
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.rkat.approval.approve', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengajuan RKAT ini?')">
                            @csrf
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Plafon Anggaran yang Disetujui (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="nominal_disetujui" class="form-control font-weight-bold text-success" style="font-size: 1.15rem;" value="{{ old('nominal_disetujui', $pengajuan->total_anggaran_diajukan) }}" min="0" step="1000" required>
                                <small class="text-muted">Dapat disesuaikan jika nominal persetujuan berbeda dari usulan.</small>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Catatan Rekomendasi / Arahan Pelaksanaan</label>
                                <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Disetujui sesuai pagu unit. Pelaksanaan wajib berkoordinasi dengan LPPM...">{{ old('catatan') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block rounded-pill font-weight-bold shadow-sm py-2">
                                <i class="fas fa-check mr-1"></i> SETUJUI PENGAJUAN INI
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Panel Revisi & Penolakan --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-undo-alt text-warning mr-2"></i>Kembalikan untuk Revisi
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.rkat.approval.revise', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Kembalikan pengajuan ini ke pengusul untuk direvisi?')">
                            @csrf
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Poin-poin yang Harus Direvisi <span class="text-danger">*</span></label>
                                <textarea name="catatan" class="form-control" rows="3" placeholder="Jelaskan hal-hal yang perlu disesuaikan oleh PIC pengusul..." required>{{ old('catatan') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-warning btn-block rounded-pill font-weight-bold py-2">
                                <i class="fas fa-undo mr-1"></i> Minta Revisi Pengusul
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Panel Tolak --}}
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="font-weight-bold mb-0 text-danger">
                            <i class="fas fa-times-circle mr-2"></i>Tolak Usulan
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.rkat.approval.reject', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK usulan kegiatan ini secara permanen?')">
                            @csrf
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Tuliskan alasan penolakan usulan..." required>{{ old('catatan') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger btn-block rounded-pill font-weight-bold py-2">
                                <i class="fas fa-ban mr-1"></i> Tolak Usulan RKAT
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
