@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <div class="d-flex align-items-center">
                    <h1 class="m-0 text-dark font-weight-bold mr-3" style="font-size: 1.45rem;">
                        <i class="fas fa-file-contract text-primary mr-2"></i>{{ $pengajuan->nomor_pengajuan }}
                    </h1>
                    @if($pengajuan->status == 'Disetujui')
                        <span class="badge badge-success px-3 py-1 rounded-pill font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                    @elseif($pengajuan->status == 'Diajukan')
                        <span class="badge badge-primary px-3 py-1 rounded-pill font-weight-bold"><i class="fas fa-paper-plane mr-1"></i>Diajukan</span>
                    @elseif($pengajuan->status == 'Review')
                        <span class="badge badge-info px-3 py-1 rounded-pill font-weight-bold"><i class="fas fa-search mr-1"></i>Review ({{ $pengajuan->current_approval_level }})</span>
                    @elseif($pengajuan->status == 'Revisi')
                        <span class="badge badge-warning px-3 py-1 rounded-pill font-weight-bold"><i class="fas fa-undo mr-1"></i>Revisi</span>
                    @elseif($pengajuan->status == 'Ditolak')
                        <span class="badge badge-danger px-3 py-1 rounded-pill font-weight-bold"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>
                    @else
                        <span class="badge badge-secondary px-3 py-1 rounded-pill font-weight-bold"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                    @endif
                </div>
                <p class="text-muted small mb-0 mt-1">
                    {{ $pengajuan->nama_kegiatan }} &bull; Unit: <strong>{{ optional($pengajuan->unit)->nama_unit ?? '-' }}</strong> &bull; Tahun Anggaran: <strong>{{ optional($pengajuan->periode)->tahun_anggaran ?? '-' }}</strong>
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.rkat.pengajuan.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-weight-bold mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                @if(in_array($pengajuan->status, ['Draft', 'Revisi']))
                    <a href="{{ route('admin.rkat.pengajuan.edit', $pengajuan->id) }}" class="btn btn-warning rounded-pill px-3 font-weight-bold shadow-sm mr-2 text-dark">
                        <i class="fas fa-edit mr-1"></i> {{ $pengajuan->status == 'Revisi' ? 'Revisi / Perbaiki Usulan' : 'Edit Pengajuan' }}
                    </a>
                    <form action="{{ route('admin.rkat.pengajuan.submit', $pengajuan->id) }}" method="POST" class="d-inline mr-2" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan usulan RKAT ini sekarang untuk diproses persetujuan?')">
                        @csrf
                        <button type="submit" class="btn btn-success rounded-pill px-3 font-weight-bold shadow-sm">
                            <i class="fas fa-paper-plane mr-1"></i> {{ $pengajuan->status == 'Revisi' ? 'Ajukan Kembali' : 'Ajukan Sekarang' }}
                        </button>
                    </form>
                @endif
                @if($pengajuan->status == 'Disetujui')
                    <a href="{{ route('admin.rkat.realisasi.show', $pengajuan->id) }}" class="btn btn-success rounded-pill px-3 font-weight-bold shadow-sm">
                        <i class="fas fa-receipt mr-1"></i> Catat Realisasi
                    </a>
                @endif
                <button type="button" class="btn btn-light border rounded-pill px-3 font-weight-bold shadow-sm" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak Usulan
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($pengajuan->status == 'Revisi')
            @php
                $revisionLog = $pengajuan->approvalLogs->where('action', 'Revisi')->sortByDesc('created_at')->first();
            @endphp
            <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #fff3cd; border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-start">
                    <div class="mr-3 mt-1">
                        <i class="fas fa-undo-alt fa-2x text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="font-weight-bold mb-1 text-dark">Status Usulan: Perlu Revisi / Perbaikan</h6>
                        <div class="small text-dark mb-1">
                            Pengajuan ini telah direview dan dikembalikan oleh <strong>{{ optional($revisionLog->user)->name ?? 'Reviewer' }} ({{ $revisionLog->level_jabatan ?? 'Reviewer' }})</strong>:
                        </div>
                        <div class="bg-white p-3 rounded border text-dark font-italic mb-2" style="font-size: 0.95rem;">
                            "{{ $revisionLog->catatan ?? 'Silakan sesuaikan rincian anggaran atau target pelaksanaan kegiatan.' }}"
                        </div>
                        <div>
                            <a href="{{ route('admin.rkat.pengajuan.edit', $pengajuan->id) }}" class="btn btn-warning btn-sm rounded-pill px-3 font-weight-bold text-dark shadow-sm">
                                <i class="fas fa-edit mr-1"></i> Buka Formulir Revisi & Sesuaikan Anggaran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Highlight Overview Card --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #007bff !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Total Anggaran Diajukan</small>
                        <h4 class="font-weight-bold text-dark mt-1 mb-0">Rp {{ number_format($pengajuan->total_anggaran_diajukan, 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $pengajuan->anggaranItems->count() }} Komponen Biaya Rincian</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #28a745 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Total Anggaran Disetujui</small>
                        <h4 class="font-weight-bold text-success mt-1 mb-0">
                            @if($pengajuan->status == 'Disetujui')
                                Rp {{ number_format($pengajuan->total_anggaran_disetujui, 0, ',', '.') }}
                            @else
                                <span class="text-muted font-weight-normal" style="font-size: 1.1rem;">Menunggu Keputusan Final</span>
                            @endif
                        </h4>
                        <small class="text-muted">Persetujuan Tingkat: {{ $pengajuan->current_approval_level ?: 'Selesai' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: white; border-left: 5px solid #6f42c1 !important;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold">Realisasi & Sisa Pagu</small>
                        <h4 class="font-weight-bold text-purple mt-1 mb-0" style="color: #6f42c1;">
                            Rp {{ number_format($pengajuan->total_realisasi, 0, ',', '.') }}
                        </h4>
                        <small class="text-muted">
                            Sisa Pagu: <strong>Rp {{ number_format(max(0, $pengajuan->total_anggaran_disetujui - $pengajuan->total_realisasi), 0, ',', '.') }}</strong> ({{ $pengajuan->persen_serapan }}%)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs border-0" id="detailTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold py-3 px-4" id="info-tab" data-toggle="tab" href="#tab-info" role="tab">
                            <i class="fas fa-info-circle mr-1"></i> Informasi & Kinerja
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold py-3 px-4" id="budget-tab" data-toggle="tab" href="#tab-budget" role="tab">
                            <i class="fas fa-list-ol mr-1"></i> Rincian Biaya ({{ $pengajuan->anggaranItems->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold py-3 px-4" id="audit-tab" data-toggle="tab" href="#tab-audit" role="tab">
                            <i class="fas fa-history mr-1"></i> Riwayat Persetujuan ({{ $pengajuan->approvalLogs->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold py-3 px-4" id="realisasi-tab" data-toggle="tab" href="#tab-realisasi" role="tab">
                            <i class="fas fa-receipt mr-1"></i> Realisasi Biaya ({{ $pengajuan->realisasis->count() }})
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="detailTabsContent">

                    {{-- TAB 1: INFORMASI & TARGET KINERJA --}}
                    <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-id-card mr-2"></i>Identitas Usulan</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted font-weight-bold" style="width: 180px;">Nomor Pengajuan:</td>
                                        <td class="font-weight-bold text-dark">{{ $pengajuan->nomor_pengajuan }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Tahun Anggaran:</td>
                                        <td>{{ optional($pengajuan->periode)->tahun_anggaran }} ({{ optional($pengajuan->periode)->nama_periode }})</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Unit Kerja:</td>
                                        <td>{{ optional($pengajuan->unit)->nama_unit }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Program Strategis:</td>
                                        <td>
                                            @if($pengajuan->program)
                                                <span class="badge badge-light border font-weight-normal">
                                                    [{{ $pengajuan->program->kode_program }}] {{ $pengajuan->program->nama_program }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">PIC Kegiatan:</td>
                                        <td>
                                            <i class="fas fa-user-circle text-secondary mr-1"></i>
                                            <strong>{{ optional($pengajuan->pic)->nama ?? '-' }}</strong>
                                            @if(optional($pengajuan->pic)->nik)
                                                ({{ $pengajuan->pic->nik }})
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Rentang Pelaksanaan:</td>
                                        <td>
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ $pengajuan->periode_pelaksanaan_mulai ? date('d M Y', strtotime($pengajuan->periode_pelaksanaan_mulai)) : '-' }}
                                            s/d
                                            {{ $pengajuan->periode_pelaksanaan_selesai ? date('d M Y', strtotime($pengajuan->periode_pelaksanaan_selesai)) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Diajukan Pada:</td>
                                        <td>{{ $pengajuan->submitted_at ? date('d M Y H:i', strtotime($pengajuan->submitted_at)) : 'Belum disubmit' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6 mb-4">
                                <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-bullseye mr-2"></i>Sasaran & Target Capaian</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted font-weight-bold" style="width: 180px;">Target Kuantitas:</td>
                                        <td class="font-weight-bold text-dark">
                                            <span class="badge badge-info px-2 py-1 font-weight-bold">
                                                {{ $pengajuan->target_kuantitas }} {{ $pengajuan->satuan_target }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Sasaran Peserta:</td>
                                        <td>{{ $pengajuan->sasaran ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Sasaran Renstra:</td>
                                        <td>{{ $pengajuan->sasaran_strategis ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Indikator Kinerja:</td>
                                        <td>{{ $pengajuan->indikator_keberhasilan ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Output Kegiatan:</td>
                                        <td>{{ $pengajuan->output ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted font-weight-bold">Outcome Kegiatan:</td>
                                        <td>{{ $pengajuan->outcome ?: '-' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-12 border-top pt-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6 class="font-weight-bold text-dark mb-2">Latar Belakang & Urgensi</h6>
                                        <div class="p-3 bg-light rounded text-dark" style="font-size: 0.9rem; line-height: 1.6;">
                                            {{ $pengajuan->latar_belakang ?: 'Tidak ada uraian latar belakang tercatat.' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="font-weight-bold text-dark mb-2">Tujuan Pelaksanaan Kegiatan</h6>
                                        <div class="p-3 bg-light rounded text-dark" style="font-size: 0.9rem; line-height: 1.6;">
                                            {{ $pengajuan->tujuan ?: 'Tidak ada uraian tujuan tercatat.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: RINCIAN ANGGARAN (ITEMS) --}}
                    <div class="tab-pane fade" id="tab-budget" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Komponen Biaya</th>
                                        <th style="width: 220px;">Akun COA</th>
                                        <th style="width: 140px;">Sumber Dana</th>
                                        <th style="width: 90px;" class="text-center">Qty</th>
                                        <th style="width: 90px;">Satuan</th>
                                        <th style="width: 140px;" class="text-right">Harga Satuan</th>
                                        <th style="width: 150px;" class="text-right">Total Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pengajuan->anggaranItems as $index => $item)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $item->komponen_biaya }}</td>
                                            <td>
                                                @if($item->akun)
                                                    <span class="badge badge-light border text-dark">
                                                        {{ $item->akun->kode_akun }} - {{ $item->akun->nama_akun }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->sumberDana)
                                                    <span class="badge badge-primary px-2 py-1 font-weight-normal">{{ $item->sumberDana->nama_sumber_dana ?? $item->sumberDana->nama_sumber }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold">{{ $item->kuantitas }}</td>
                                            <td>{{ $item->satuan }}</td>
                                            <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold text-primary">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada rincian komponen biaya.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold" style="font-size: 1.05rem;">
                                        <td colspan="7" class="text-right py-3 text-dark">TOTAL ANGGARAN:</td>
                                        <td class="text-right py-3 text-primary">Rp {{ number_format($pengajuan->total_anggaran_diajukan, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 3: RIWAYAT PERSETUJUAN (AUDIT TRAIL) --}}
                    <div class="tab-pane fade" id="tab-audit" role="tabpanel">
                        <div class="timeline">
                            @forelse($pengajuan->approvalLogs as $log)
                                <div>
                                    @if($log->action == 'Disetujui')
                                        <i class="fas fa-check bg-success"></i>
                                    @elseif($log->action == 'Diajukan')
                                        <i class="fas fa-paper-plane bg-primary"></i>
                                    @elseif($log->action == 'Revisi')
                                        <i class="fas fa-undo bg-warning"></i>
                                    @elseif($log->action == 'Ditolak')
                                        <i class="fas fa-times bg-danger"></i>
                                    @else
                                        <i class="fas fa-clock bg-info"></i>
                                    @endif

                                    <div class="timeline-item shadow-sm border-0" style="border-radius: 10px;">
                                        <span class="time"><i class="fas fa-clock mr-1"></i>{{ $log->created_at->format('d M Y H:i') }}</span>
                                        <h3 class="timeline-header font-weight-bold">
                                            <span class="text-primary">{{ optional($log->user)->name ?? 'Sistem' }}</span>
                                            <span class="badge badge-light border ml-1">{{ $log->level_jabatan }}</span>
                                            melakukan tindakan:
                                            <span class="badge {{ $log->action == 'Disetujui' ? 'badge-success' : ($log->action == 'Diajukan' ? 'badge-primary' : ($log->action == 'Revisi' ? 'badge-warning' : 'badge-danger')) }} ml-1">
                                                {{ $log->action }}
                                            </span>
                                        </h3>
                                        <div class="timeline-body">
                                            <div class="mb-2">
                                                Nominal Pengesahan: <strong>Rp {{ number_format($log->nominal_disetujui, 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="p-2 bg-light rounded text-muted">
                                                <em>Catatan: "{{ $log->catatan ?: 'Tidak ada catatan khusus.' }}"</em>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <div>Belum ada aktivitas persetujuan tercatat.</div>
                                </div>
                            @endforelse
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 4: REALISASI ANGGARAN & SPJ --}}
                    <div class="tab-pane fade" id="tab-realisasi" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-dark mb-0">
                                Transaksi Pencairan & Bukti SPJ
                            </h6>
                            @if($pengajuan->status == 'Disetujui')
                                <a href="{{ route('admin.rkat.realisasi.show', $pengajuan->id) }}" class="btn btn-sm btn-success rounded-pill px-3 font-weight-bold shadow-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambah Transaksi SPJ
                                </a>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th style="width: 120px;">Tgl Transaksi</th>
                                        <th>Uraian Pengeluaran</th>
                                        <th style="width: 140px;">Bukti / Kwitansi</th>
                                        <th style="width: 150px;" class="text-right">Nominal Realisasi</th>
                                        <th style="width: 90px;" class="text-center">Berkas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pengajuan->realisasis as $index => $rel)
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td>{{ date('d/m/Y', strtotime($rel->tanggal_transaksi)) }}</td>
                                            <td class="font-weight-bold text-dark">{{ $rel->uraian_pengeluaran }}</td>
                                            <td>
                                                <span class="badge badge-light border">{{ $rel->jenis_bukti }}</span>
                                                <small class="text-muted d-block">{{ $rel->nomor_bukti }}</small>
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                Rp {{ number_format($rel->jumlah_realisasi, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                @if($rel->file_bukti)
                                                    <a href="{{ asset('storage/' . $rel->file_bukti) }}" target="_blank" class="btn btn-outline-primary btn-xs" title="Lihat Berkas">
                                                        <i class="fas fa-file-download"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                Belum ada transaksi realisasi yang dicatat untuk kegiatan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="4" class="text-right py-2 text-dark">TOTAL SERAPAN REALISASI:</td>
                                        <td class="text-right py-2 text-success">
                                            Rp {{ number_format($pengajuan->total_realisasi, 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>
@endsection
