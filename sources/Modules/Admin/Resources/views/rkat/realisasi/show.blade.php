@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <div class="d-flex align-items-center">
                    <h1 class="m-0 text-dark font-weight-bold mr-3" style="font-size: 1.45rem;">
                        <i class="fas fa-file-invoice text-success mr-2"></i>Pencatatan Realisasi Anggaran
                    </h1>
                    <span class="badge badge-success px-3 py-1 rounded-pill font-weight-bold">
                        {{ $pengajuan->nomor_pengajuan }}
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    {{ $pengajuan->nama_kegiatan }} &bull; Unit: <strong>{{ optional($pengajuan->unit)->nama_unit }}</strong>
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.rkat.realisasi.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-weight-bold mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="button" class="btn btn-success rounded-pill px-3 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTambahRealisasi">
                    <i class="fas fa-plus mr-1"></i> Tambah Transaksi SPJ
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

        {{-- Budget Balance Summary --}}
        @php
            $pagu = $pengajuan->total_anggaran_disetujui;
            $realisasi = $pengajuan->total_realisasi;
            $sisa = max(0, $pagu - $realisasi);
            $persen = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;
        @endphp
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center text-md-left mb-3 mb-md-0 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Pagu Disetujui</small>
                        <h4 class="font-weight-bold text-dark mb-0 mt-1">Rp {{ number_format($pagu, 0, ',', '.') }}</h4>
                        <small class="text-muted">TA {{ optional($pengajuan->periode)->tahun_anggaran }}</small>
                    </div>
                    <div class="col-md-3 text-center text-md-left mb-3 mb-md-0 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Total Terpakai (SPJ)</small>
                        <h4 class="font-weight-bold text-success mb-0 mt-1">Rp {{ number_format($realisasi, 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $pengajuan->realisasis->count() }} Transaksi</small>
                    </div>
                    <div class="col-md-3 text-center text-md-left mb-3 mb-md-0 border-right">
                        <small class="text-muted text-uppercase font-weight-bold">Sisa Saldo Pagu</small>
                        <h4 class="font-weight-bold text-primary mb-0 mt-1">Rp {{ number_format($sisa, 0, ',', '.') }}</h4>
                        <small class="text-muted">Dapat Dicairkan</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <small class="text-muted text-uppercase font-weight-bold">Persentase Serapan</small>
                        <h3 class="font-weight-bold mb-0 mt-1 {{ $persen > 90 ? 'text-success' : 'text-info' }}">{{ $persen }}%</h3>
                        <div class="progress mt-2" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar {{ $persen > 90 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ min(100, $persen) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-receipt text-success mr-2"></i>Daftar Transaksi Pengeluaran & SPJ
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ $pengajuan->realisasis->count() }} Transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 120px;">Tgl Transaksi</th>
                                <th>Uraian Pengeluaran</th>
                                <th style="width: 180px;">Pos Anggaran Item</th>
                                <th style="width: 140px;">Bukti / Kwitansi</th>
                                <th style="width: 150px;" class="text-right">Jumlah Realisasi</th>
                                <th style="width: 90px;" class="text-center">Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuan->realisasis as $index => $rel)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        {{ date('d/m/Y', strtotime($rel->tanggal_transaksi)) }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">{{ $rel->uraian_pengeluaran }}</div>
                                        @if($rel->keterangan)
                                            <small class="text-muted d-block">{{ $rel->keterangan }}</small>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($rel->anggaranItem)
                                            <span class="badge badge-light border text-dark font-weight-normal text-truncate" style="max-width: 180px;">
                                                {{ $rel->anggaranItem->komponen_biaya }}
                                            </span>
                                        @else
                                            <span class="text-muted small">Biaya Umum Kegiatan</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary px-2 py-1">{{ $rel->jenis_bukti }}</span>
                                        @if($rel->nomor_bukti)
                                            <small class="text-muted d-block font-weight-bold mt-1">{{ $rel->nomor_bukti }}</small>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-success">
                                        Rp {{ number_format($rel->jumlah_realisasi, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($rel->file_bukti)
                                            <a href="{{ asset('storage/' . $rel->file_bukti) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-circle" title="Unduh Berkas Bukti">
                                                <i class="fas fa-file-download"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-3x text-secondary mb-3" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold">Belum Ada Transaksi SPJ</div>
                                        <small>Klik tombol "Tambah Transaksi SPJ" di atas untuk mencatatkan belanja dan mengunggah nota/kwitansi.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="5" class="text-right py-3 text-dark">TOTAL SERAPAN REALISASI:</td>
                                <td class="text-right py-3 text-success" style="font-size: 1.1rem;">
                                    Rp {{ number_format($realisasi, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- MODAL TAMBAH REALISASI & UPLOAD SPJ --}}
<div class="modal fade" id="modalTambahRealisasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.rkat.realisasi.store', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title font-weight-bold">
                        <i class="fas fa-receipt mr-2"></i>Catat Transaksi Realisasi Belanja & SPJ
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark">Tanggal Transaksi Riil <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_transaksi" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark">Pos Komponen RKAT Terkait</label>
                            <select name="anggaran_item_id" class="form-control">
                                <option value="">-- Biaya Umum Kegiatan --</option>
                                @foreach($pengajuan->anggaranItems as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->komponen_biaya }} (Pagu: Rp {{ number_format($item->total_biaya, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small text-dark">Uraian Pengeluaran Belanja <span class="text-danger">*</span></label>
                            <input type="text" name="uraian_pengeluaran" class="form-control" placeholder="Contoh: Pembayaran Honorarium Narasumber Workshop Penulisan Jurnal" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark">Nominal Pengeluaran Riil (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_realisasi" class="form-control font-weight-bold text-success" min="1000" step="500" placeholder="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark">Jenis Bukti Pembayaran <span class="text-danger">*</span></label>
                            <select name="jenis_bukti" class="form-control" required>
                                <option value="Kwitansi">Kwitansi Bermaterai</option>
                                <option value="Nota Kontan">Nota Kontan / Struk Pembelian</option>
                                <option value="Invoice">Invoice Resmi Rekanan</option>
                                <option value="Bukti Transfer">Bukti Transfer Bank / Slip</option>
                                <option value="SPJ Rampung">Laporan SPJ Rampung Lengkap</option>
                                <option value="Lainnya">Dokumen Bukti Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark">Nomor Bukti / Seri Dokumen</label>
                            <input type="text" name="nomor_bukti" class="form-control" placeholder="Contoh: KW-2026/09/012">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark">Unggah Berkas Bukti / Scan SPJ</label>
                            <input type="file" name="file_bukti" class="form-control-file border p-1 rounded w-100" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG. Maksimal 5MB.</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold small text-dark">Keterangan Tambahan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan transaksi atau pihak penerima..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
