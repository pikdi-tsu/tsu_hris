@extends('system::template.admin.header')

@section('title', $title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <div>
                <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                    <i class="fas fa-file-alt text-primary mr-2"></i>Laporan Komprehensif RKAT & Anggaran
                </h1>
                <p class="text-muted small mb-0">Laporan multi-dimensi berdasarkan Unit Kerja, Program Strategis, Usulan Kegiatan, dan Akun Belanja (COA).</p>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-outline-primary rounded-pill px-3 font-weight-bold shadow-sm" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak / Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- Filter & Kategori Laporan --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.rkat.laporan.index') }}" method="GET" class="row align-items-center">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Tahun Anggaran:</label>
                        <select name="periode_id" class="form-control form-control-sm rounded-pill font-weight-bold" onchange="this.form.submit()">
                            @foreach($periodes as $p)
                                <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                                    Tahun {{ $p->tahun_anggaran }} ({{ $p->nama_periode }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-9 mb-2 mb-md-0">
                        <label class="small text-muted mb-1 font-weight-bold">Dimensi / Kategori Laporan:</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-sm btn-outline-primary rounded-left {{ $selectedType == 'unit' ? 'active' : '' }}">
                                <input type="radio" name="type" value="unit" onchange="this.form.submit()" {{ $selectedType == 'unit' ? 'checked' : '' }}>
                                <i class="fas fa-building mr-1"></i> Berdasarkan Unit Kerja
                            </label>
                            <label class="btn btn-sm btn-outline-primary {{ $selectedType == 'program' ? 'active' : '' }}">
                                <input type="radio" name="type" value="program" onchange="this.form.submit()" {{ $selectedType == 'program' ? 'checked' : '' }}>
                                <i class="fas fa-tag mr-1"></i> Berdasarkan Program
                            </label>
                            <label class="btn btn-sm btn-outline-primary {{ $selectedType == 'kegiatan' ? 'active' : '' }}">
                                <input type="radio" name="type" value="kegiatan" onchange="this.form.submit()" {{ $selectedType == 'kegiatan' ? 'checked' : '' }}>
                                <i class="fas fa-tasks mr-1"></i> Berdasarkan Usulan Kegiatan
                            </label>
                            <label class="btn btn-sm btn-outline-primary rounded-right {{ $selectedType == 'akun' ? 'active' : '' }}">
                                <input type="radio" name="type" value="akun" onchange="this.form.submit()" {{ $selectedType == 'akun' ? 'checked' : '' }}>
                                <i class="fas fa-book mr-1"></i> Berdasarkan Akun COA
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Laporan Table --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="font-weight-bold mb-0 text-dark">
                    <i class="fas fa-table text-primary mr-2"></i>
                    Laporan Rekapitulasi Anggaran & Serapan (Kategori: {{ strtoupper($selectedType) }})
                </h6>
                <span class="badge badge-light border text-muted px-2 py-1">Total: {{ count($dataLaporan) }} Baris Data</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="thead-light">
                            @if($selectedType == 'unit')
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Unit / Fakultas / Lembaga</th>
                                    <th style="width: 130px;" class="text-center">Jml Kegiatan</th>
                                    <th style="width: 170px;" class="text-right">Usulan Diajukan</th>
                                    <th style="width: 170px;" class="text-right">Pagu Disetujui</th>
                                    <th style="width: 170px;" class="text-right">Realisasi (SPJ)</th>
                                    <th style="width: 170px;" class="text-right">Sisa Anggaran</th>
                                    <th style="width: 120px;" class="text-center">Serapan</th>
                                </tr>
                            @elseif($selectedType == 'program')
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="width: 120px;">Kode</th>
                                    <th>Nama Program Strategis</th>
                                    <th style="width: 130px;" class="text-center">Jml Kegiatan</th>
                                    <th style="width: 170px;" class="text-right">Pagu Disetujui</th>
                                    <th style="width: 170px;" class="text-right">Realisasi (SPJ)</th>
                                    <th style="width: 170px;" class="text-right">Sisa Anggaran</th>
                                    <th style="width: 120px;" class="text-center">Serapan</th>
                                </tr>
                            @elseif($selectedType == 'akun')
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="width: 140px;">Kode Akun COA</th>
                                    <th>Nama Akun Biaya</th>
                                    <th style="width: 150px;">Kategori</th>
                                    <th style="width: 130px;" class="text-center">Jml Penggunaan</th>
                                    <th style="width: 180px;" class="text-right">Total Alokasi Pagu</th>
                                </tr>
                            @else
                                {{-- Kegiatan --}}
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="width: 150px;">Nomor Pengajuan</th>
                                    <th>Nama Usulan Kegiatan</th>
                                    <th style="width: 180px;">Unit Pengusul</th>
                                    <th style="width: 110px;" class="text-center">Status</th>
                                    <th style="width: 160px;" class="text-right">Pagu Disetujui</th>
                                    <th style="width: 160px;" class="text-right">Realisasi (SPJ)</th>
                                    <th style="width: 110px;" class="text-center">Serapan</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @php
                                $totalPagu = 0;
                                $totalReal = 0;
                                $totalSisa = 0;
                            @endphp

                            @forelse($dataLaporan as $index => $row)
                                @if($selectedType == 'unit')
                                    @php
                                        $totalPagu += $row['anggaran_disetujui'];
                                        $totalReal += $row['realisasi'];
                                        $totalSisa += $row['sisa'];
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="font-weight-bold text-dark">{{ $row['nama'] }}</td>
                                        <td class="text-center">{{ $row['total_kegiatan'] }}</td>
                                        <td class="text-right text-muted">Rp {{ number_format($row['anggaran_diajukan'], 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold text-dark">Rp {{ number_format($row['anggaran_disetujui'], 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold text-success">Rp {{ number_format($row['realisasi'], 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold text-secondary">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</td>
                                        <td class="text-center font-weight-bold {{ $row['persen_serapan'] >= 90 ? 'text-success' : ($row['persen_serapan'] < 30 ? 'text-warning' : 'text-primary') }}">
                                            {{ $row['persen_serapan'] }}%
                                        </td>
                                    </tr>
                                @elseif($selectedType == 'program')
                                    @php
                                        $totalPagu += $row['anggaran_disetujui'];
                                        $totalReal += $row['realisasi'];
                                        $totalSisa += $row['sisa'];
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td><span class="badge badge-light border font-weight-bold">{{ $row['kode'] }}</span></td>
                                        <td class="font-weight-bold text-dark">{{ $row['nama'] }}</td>
                                        <td class="text-center">{{ $row['total_kegiatan'] }}</td>
                                        <td class="text-right font-weight-bold text-dark">Rp {{ number_format($row['anggaran_disetujui'], 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold text-success">Rp {{ number_format($row['realisasi'], 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold text-secondary">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</td>
                                        <td class="text-center font-weight-bold {{ $row['persen_serapan'] >= 90 ? 'text-success' : 'text-primary' }}">
                                            {{ $row['persen_serapan'] }}%
                                        </td>
                                    </tr>
                                @elseif($selectedType == 'akun')
                                    @php $totalPagu += $row['anggaran_disetujui']; @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="font-weight-bold text-dark">{{ $row['kode'] }}</td>
                                        <td>{{ $row['nama'] }}</td>
                                        <td><span class="badge badge-info px-2 py-1">{{ $row['kategori'] }}</span></td>
                                        <td class="text-center">{{ $row['total_item'] }} Item</td>
                                        <td class="text-right font-weight-bold text-primary">Rp {{ number_format($row['anggaran_disetujui'], 0, ',', '.') }}</td>
                                    </tr>
                                @else
                                    {{-- Kegiatan --}}
                                    @php
                                        $totalPagu += $row['anggaran_disetujui'];
                                        $totalReal += $row['realisasi'];
                                        $totalSisa += $row['sisa'];
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="font-weight-bold text-dark">{{ $row['nomor'] }}</td>
                                        <td>
                                            <div class="font-weight-bold text-primary">{{ $row['nama'] }}</div>
                                            <small class="text-muted">{{ $row['program'] }}</small>
                                        </td>
                                        <td>{{ $row['unit'] }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $row['status'] == 'Disetujui' ? 'badge-success' : ($row['status'] == 'Diajukan' ? 'badge-primary' : 'badge-secondary') }} px-2 py-1">
                                                {{ $row['status'] }}
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">Rp {{ number_format($row['anggaran_disetujui'], 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold text-success">Rp {{ number_format($row['realisasi'], 0, ',', '.') }}</td>
                                        <td class="text-center font-weight-bold">{{ $row['persen_serapan'] }}%</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-file-excel fa-3x text-secondary mb-3" style="opacity: 0.4;"></i>
                                        <div class="font-weight-bold">Tidak Ada Rekap Data</div>
                                        <small>Tidak ada data pengajuan RKAT yang memenuhi kriteria filter ini.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($dataLaporan) > 0)
                            <tfoot>
                                <tr class="bg-light font-weight-bold" style="font-size: 1.05rem;">
                                    @if($selectedType == 'unit')
                                        <td colspan="4" class="text-right py-3 text-dark">TOTAL KESELURUHAN:</td>
                                        <td class="text-right py-3 text-dark">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
                                        <td class="text-right py-3 text-success">Rp {{ number_format($totalReal, 0, ',', '.') }}</td>
                                        <td class="text-right py-3 text-secondary">Rp {{ number_format($totalSisa, 0, ',', '.') }}</td>
                                        <td class="text-center py-3 text-primary">
                                            {{ $totalPagu > 0 ? round(($totalReal / $totalPagu) * 100, 1) : 0 }}%
                                        </td>
                                    @elseif($selectedType == 'program')
                                        <td colspan="4" class="text-right py-3 text-dark">TOTAL KESELURUHAN:</td>
                                        <td class="text-right py-3 text-dark">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
                                        <td class="text-right py-3 text-success">Rp {{ number_format($totalReal, 0, ',', '.') }}</td>
                                        <td class="text-right py-3 text-secondary">Rp {{ number_format($totalSisa, 0, ',', '.') }}</td>
                                        <td class="text-center py-3 text-primary">
                                            {{ $totalPagu > 0 ? round(($totalReal / $totalPagu) * 100, 1) : 0 }}%
                                        </td>
                                    @elseif($selectedType == 'akun')
                                        <td colspan="5" class="text-right py-3 text-dark">TOTAL PAGU AKUN TERPAKAI:</td>
                                        <td class="text-right py-3 text-primary">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
                                    @else
                                        <td colspan="5" class="text-right py-3 text-dark">TOTAL KESELURUHAN:</td>
                                        <td class="text-right py-3 text-dark">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
                                        <td class="text-right py-3 text-success">Rp {{ number_format($totalReal, 0, ',', '.') }}</td>
                                        <td class="text-center py-3 text-primary">
                                            {{ $totalPagu > 0 ? round(($totalReal / $totalPagu) * 100, 1) : 0 }}%
                                        </td>
                                    @endif
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
