@extends('system::template.admin.header')
@section('title', $title ?? 'Detail Laporan Kegiatan SDM')

@section('link_href')
    <style>
        .doc-paper {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .doc-paper {
                padding: 1.25rem;
            }
        }
        .doc-section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 2px solid #094b54;
            padding-bottom: 0.4rem;
            margin-top: 1.75rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .doc-table {
            width: 100%;
            margin-bottom: 1.25rem;
            border-collapse: collapse;
        }
        .doc-table th {
            background-color: #d9eaf7;
            color: #0c4a6e;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid #cbd5e1;
            padding: 0.55rem 0.75rem;
            vertical-align: middle;
        }
        .doc-table td {
            border: 1px solid #e2e8f0;
            padding: 0.55rem 0.75rem;
            font-size: 0.88rem;
            vertical-align: middle;
        }
        .doc-info-table th {
            width: 25%;
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            padding: 0.5rem 0.75rem;
        }
        .doc-info-table td {
            border: 1px solid #e2e8f0;
            padding: 0.5rem 0.75rem;
        }
        .signature-box {
            text-align: center;
            padding: 1rem;
        }
        .signature-space {
            height: 70px;
        }
        .gallery-photo-card {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            background: #ffffff;
            height: 100%;
        }
        .gallery-photo-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .gallery-photo-card img:hover {
            transform: scale(1.02);
        }
    </style>
@endsection

@section('content')
    <x-tsu-page-header
        title="Detail Laporan Kegiatan SDM"
        :subtitle="$laporan->nama_kegiatan"
        :icon="$menuIcon ?? 'fas fa-file-invoice'"
        :breadcrumb="true"
    />

    <section class="content pb-5">
        <div class="container-fluid">

            <!-- Action Bar -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 bg-white p-3 rounded shadow-sm">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <span class="mr-3 font-weight-bold text-dark" style="font-size: 1.1rem;">
                        <i class="fas fa-barcode text-muted mr-1"></i>{{ $laporan->nomor_laporan }}
                    </span>
                    {!! $laporan->status_badge !!}
                    <span class="badge badge-light border text-primary ml-2 py-1 px-2">
                        <i class="fas fa-tag mr-1"></i>{{ $laporan->kategori_kegiatan }}
                    </span>
                </div>
                <div>
                    <a href="{{ route('admin.laporan-kegiatan-sdm.cetak-pdf', $laporan->id) }}" target="_blank" class="btn btn-danger shadow-sm mr-2" style="border-radius: 8px;">
                        <i class="fas fa-file-pdf mr-1"></i>Cetak Dokumen Resmi (PDF)
                    </a>
                    <a href="{{ route('admin.laporan-kegiatan-sdm.edit', $laporan->id) }}" class="btn btn-warning shadow-sm mr-2" style="border-radius: 8px;">
                        <i class="fas fa-pencil-alt mr-1"></i>Edit Laporan
                    </a>
                    <a href="{{ route('admin.laporan-kegiatan-sdm.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- Ringkasan Cepat KPI -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 10px; border-left: 4px solid #094b54 !important;">
                        <small class="text-muted font-weight-bold uppercase">Tanggal Pelaksanaan</small>
                        <h6 class="font-weight-bold text-dark mt-1 mb-0">{{ $laporan->rentang_tanggal_formatted }}</h6>
                        <small class="text-muted">{{ $laporan->waktu_pelaksanaan ?: '-' }}</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 10px; border-left: 4px solid #0284c7 !important;">
                        <small class="text-muted font-weight-bold uppercase">Tempat / Metode</small>
                        <h6 class="font-weight-bold text-dark mt-1 mb-0">{{ ucfirst($laporan->tipe_tempat) }}</h6>
                        <small class="text-muted text-truncate" title="{{ $laporan->tempat_pelaksanaan }}">{{ $laporan->tempat_pelaksanaan ?: '-' }}</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 10px; border-left: 4px solid #047857 !important;">
                        <small class="text-muted font-weight-bold uppercase">Kehadiran Peserta</small>
                        <h6 class="font-weight-bold text-success mt-1 mb-0">{{ $laporan->jumlah_peserta_hadir }} Orang Hadir</h6>
                        <small class="text-muted">Target: {{ $laporan->jumlah_peserta_rencana }} | Tidak Hadir: {{ $laporan->jumlah_peserta_tidak_hadir }}</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 10px; border-left: 4px solid #b45309 !important;">
                        <small class="text-muted font-weight-bold uppercase">Realisasi Anggaran</small>
                        <h6 class="font-weight-bold text-dark mt-1 mb-0">Rp {{ number_format($laporan->total_realisasi, 0, ',', '.') }}</h6>
                        @php
                            $persenSerapan = $laporan->total_anggaran > 0 ? round(($laporan->total_realisasi / $laporan->total_anggaran) * 100, 1) : 0;
                        @endphp
                        <small class="text-muted">Pagu: Rp {{ number_format($laporan->total_anggaran, 0, ',', '.') }} ({{ $persenSerapan }}%)</small>
                    </div>
                </div>
            </div>

            <!-- Dokumen Naskah Formal (Sesuai Template Word TSU) -->
            <div class="doc-paper">

                <!-- Header Dokumen -->
                <div class="text-center pb-4 mb-4 border-bottom">
                    <h5 class="font-weight-bold text-uppercase mb-1" style="letter-spacing: 1px; color: #094b54;">TIGA SERANGKAI UNIVERSITY</h5>
                    <h4 class="font-weight-bold text-dark text-uppercase mb-2">LAPORAN KEGIATAN SUMBER DAYA MANUSIA</h4>
                    <h5 class="font-weight-bold text-primary mb-1">"{{ $laporan->nama_kegiatan }}"</h5>
                    <span class="text-muted small">Nomor: {{ $laporan->nomor_laporan }}</span>
                </div>

                <!-- A. IDENTITAS KEGIATAN -->
                <div class="doc-section-title">
                    <i class="fas fa-tag mr-2 text-primary"></i>A. IDENTITAS KEGIATAN
                </div>
                <table class="doc-info-table table table-bordered mb-4">
                    <tr>
                        <th>Nama Kegiatan</th>
                        <td class="font-weight-bold text-dark">{{ $laporan->nama_kegiatan }}</td>
                    </tr>
                    <tr>
                        <th>Kategori Kegiatan</th>
                        <td>{{ $laporan->kategori_kegiatan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Penyelenggara / Unit</th>
                        <td>Bagian Sumber Daya Manusia (SDM) Tiga Serangkai University</td>
                    </tr>
                    <tr>
                        <th>Sasaran Peserta</th>
                        <td>Dosen & Tenaga Kependidikan TSU</td>
                    </tr>
                </table>

                <!-- B. LATAR BELAKANG -->
                <div class="doc-section-title">
                    <i class="fas fa-align-left mr-2 text-primary"></i>B. LATAR BELAKANG
                </div>
                <div class="p-3 bg-light rounded text-justify mb-4" style="line-height: 1.7; font-size: 0.92rem;">
                    {!! nl2br(e($laporan->latar_belakang ?: 'Tidak ada uraian latar belakang.')) !!}
                </div>

                <!-- C. TUJUAN -->
                <div class="doc-section-title">
                    <i class="fas fa-bullseye mr-2 text-primary"></i>C. TUJUAN
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="8%" class="text-center">No.</th>
                            <th width="92%">Tujuan Kegiatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->tujuan && count($laporan->tujuan) > 0)
                            @foreach($laporan->tujuan as $idx => $tuj)
                                @if(!empty($tuj['tujuan']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $tuj['tujuan'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="2" class="text-center text-muted">Tidak ada data tujuan</td></tr>
                        @endif
                    </tbody>
                </table>

                <!-- D. DASAR PELAKSANAAN -->
                <div class="doc-section-title">
                    <i class="fas fa-book mr-2 text-primary"></i>D. DASAR PELAKSANAAN
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="44%">Dasar Pelaksanaan</th>
                            <th width="25%">Nomor / Tanggal</th>
                            <th width="25%">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->dasar_pelaksanaan && count($laporan->dasar_pelaksanaan) > 0)
                            @foreach($laporan->dasar_pelaksanaan as $idx => $dsr)
                                @if(!empty($dsr['dasar']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $dsr['dasar'] }}</td>
                                        <td>{{ $dsr['nomor_tanggal'] ?: '-' }}</td>
                                        <td>{{ $dsr['dokumen'] ?: '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data dasar pelaksanaan</td></tr>
                        @endif
                    </tbody>
                </table>

                <!-- E. WAKTU DAN TEMPAT -->
                <div class="doc-section-title">
                    <i class="fas fa-map-marked-alt mr-2 text-primary"></i>E. WAKTU DAN TEMPAT
                </div>
                <table class="doc-info-table table table-bordered mb-4">
                    <tr>
                        <th>Hari / Tanggal</th>
                        <td class="font-weight-bold text-dark">{{ $laporan->rentang_tanggal_formatted }}</td>
                    </tr>
                    <tr>
                        <th>Waktu</th>
                        <td>{{ $laporan->waktu_pelaksanaan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Metode & Lokasi</th>
                        <td>
                            <span class="badge badge-info px-2 mr-1">{{ ucfirst($laporan->tipe_tempat) }}</span>
                            {{ $laporan->tempat_pelaksanaan ?: '-' }}
                        </td>
                    </tr>
                    @if($laporan->link_daring)
                        <tr>
                            <th>Tautan / Link Daring</th>
                            <td><a href="{{ $laporan->link_daring }}" target="_blank" class="text-primary font-weight-bold"><i class="fas fa-external-link-alt mr-1"></i>{{ $laporan->link_daring }}</a></td>
                        </tr>
                    @endif
                    @if($laporan->keterangan_waktu_tempat)
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $laporan->keterangan_waktu_tempat }}</td>
                        </tr>
                    @endif
                </table>

                <!-- F. PESERTA -->
                <div class="doc-section-title">
                    <i class="fas fa-users mr-2 text-primary"></i>F. PESERTA
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-2 border rounded bg-light text-center">
                            <small class="text-muted font-weight-bold">Direncanakan</small>
                            <h5 class="font-weight-bold mb-0 text-dark">{{ $laporan->jumlah_peserta_rencana }} Orang</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 border rounded bg-light text-center">
                            <small class="text-muted font-weight-bold">Jumlah Hadir</small>
                            <h5 class="font-weight-bold mb-0 text-success">{{ $laporan->jumlah_peserta_hadir }} Orang</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 border rounded bg-light text-center">
                            <small class="text-muted font-weight-bold">Tidak Hadir</small>
                            <h5 class="font-weight-bold mb-0 text-danger">{{ $laporan->jumlah_peserta_tidak_hadir }} Orang</h5>
                        </div>
                    </div>
                </div>

                @if($laporan->pesertas->count() > 0)
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No.</th>
                                <th width="30%">Nama</th>
                                <th width="18%">NIP / NIDN</th>
                                <th width="22%">Unit</th>
                                <th width="15%">Jabatan</th>
                                <th width="10%" class="text-center">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($laporan->pesertas as $p)
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                    <td class="font-weight-bold text-dark">{{ $p->nama }}</td>
                                    <td>{{ $p->nip_nidn ?: '-' }}</td>
                                    <td>{{ $p->unit ?: '-' }}</td>
                                    <td>{{ $p->jabatan ?: '-' }}</td>
                                    <td class="text-center">{!! $p->kehadiran_badge !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <!-- G. PELAKSANA / PANITIA -->
                <div class="doc-section-title">
                    <i class="fas fa-id-badge mr-2 text-primary"></i>G. PELAKSANA / PANITIA / PIHAK TERLIBAT
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="30%">Nama</th>
                            <th width="25%">Unit / Instansi</th>
                            <th width="20%">Jabatan</th>
                            <th width="19%">Peran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->panitia && count($laporan->panitia) > 0)
                            @foreach($laporan->panitia as $pan)
                                @if(!empty($pan['nama']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td class="font-weight-bold">{{ $pan['nama'] }}</td>
                                        <td>{{ $pan['unit_instansi'] ?: '-' }}</td>
                                        <td>{{ $pan['jabatan'] ?: '-' }}</td>
                                        <td><span class="badge badge-light border text-primary">{{ $pan['peran'] ?: '-' }}</span></td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data panitia</td></tr>
                        @endif
                    </tbody>
                </table>

                <!-- H. NARASUMBER / FASILITATOR -->
                <div class="doc-section-title">
                    <i class="fas fa-chalkboard-teacher mr-2 text-primary"></i>H. NARASUMBER / FASILITATOR
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="28%">Nama</th>
                            <th width="22%">Instansi</th>
                            <th width="18%">Jabatan</th>
                            <th width="26%">Materi / Peran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->narasumber && count($laporan->narasumber) > 0)
                            @foreach($laporan->narasumber as $ns)
                                @if(!empty($ns['nama']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td class="font-weight-bold">{{ $ns['nama'] }}</td>
                                        <td>{{ $ns['instansi'] ?: '-' }}</td>
                                        <td>{{ $ns['jabatan'] ?: '-' }}</td>
                                        <td>{{ $ns['materi_peran'] ?: '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data narasumber</td></tr>
                        @endif
                    </tbody>
                </table>

                <!-- I. RANGKAIAN KEGIATAN -->
                <div class="doc-section-title">
                    <i class="fas fa-clock mr-2 text-primary"></i>I. RANGKAIAN / PELAKSANAAN KEGIATAN (RUNDOWN)
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="18%">Waktu</th>
                            <th width="30%">Agenda</th>
                            <th width="52%">Uraian Pelaksanaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->rundown && count($laporan->rundown) > 0)
                            @foreach($laporan->rundown as $rd)
                                @if(!empty($rd['agenda']))
                                    <tr>
                                        <td class="font-weight-bold text-nowrap"><i class="far fa-clock mr-1 text-muted"></i>{{ $rd['waktu'] ?: '-' }}</td>
                                        <td class="font-weight-bold text-dark">{{ $rd['agenda'] }}</td>
                                        <td>{{ $rd['uraian'] ?: '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada data rundown kegiatan</td></tr>
                        @endif
                    </tbody>
                </table>

                <!-- J. HASIL DAN CAPAIAN -->
                <div class="doc-section-title">
                    <i class="fas fa-chart-line mr-2 text-primary"></i>J. HASIL DAN CAPAIAN
                </div>

                <h6 class="font-weight-bold text-dark mt-2 mb-2">1. Target Kegiatan</h6>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="50%">Indikator</th>
                            <th width="44%">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->target_kegiatan && count($laporan->target_kegiatan) > 0)
                            @foreach($laporan->target_kegiatan as $tgt)
                                @if(!empty($tgt['indikator']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $tgt['indikator'] }}</td>
                                        <td class="font-weight-bold">{{ $tgt['target'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada data target</td></tr>
                        @endif
                    </tbody>
                </table>

                <h6 class="font-weight-bold text-dark mt-3 mb-2">2. Realisasi dan Capaian</h6>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No.</th>
                            <th width="32%">Indikator</th>
                            <th width="16%">Target</th>
                            <th width="16%">Realisasi</th>
                            <th width="15%" class="text-center">Capaian (%)</th>
                            <th width="16%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->capaian_kegiatan && count($laporan->capaian_kegiatan) > 0)
                            @foreach($laporan->capaian_kegiatan as $cap)
                                @if(!empty($cap['indikator']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $cap['indikator'] }}</td>
                                        <td>{{ $cap['target'] }}</td>
                                        <td>{{ $cap['realisasi'] }}</td>
                                        <td class="text-center font-weight-bold text-primary">{{ $cap['capaian_persen'] }}%</td>
                                        <td class="text-center">
                                            @if(($cap['status'] ?? '') == 'Tercapai')
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Tercapai</span>
                                            @elseif(($cap['status'] ?? '') == 'Melebihi Target')
                                                <span class="badge badge-info px-2 py-1"><i class="fas fa-star mr-1"></i>Melebihi Target</span>
                                            @else
                                                <span class="badge badge-danger px-2 py-1">{{ $cap['status'] ?? 'Belum Tercapai' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data capaian</td></tr>
                        @endif
                    </tbody>
                </table>

                <h6 class="font-weight-bold text-dark mt-3 mb-2">3. Uraian Hasil</h6>
                <div class="p-3 bg-light rounded text-justify mb-4" style="line-height: 1.7; font-size: 0.92rem;">
                    {!! nl2br(e($laporan->uraian_hasil ?: 'Tidak ada uraian hasil.')) !!}
                </div>

                <!-- K. EVALUASI -->
                <div class="doc-section-title">
                    <i class="fas fa-clipboard-check mr-2 text-primary"></i>K. EVALUASI
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="35%">Aspek / Indikator Evaluasi</th>
                            <th width="25%">Metode</th>
                            <th width="34%">Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->evaluasi && count($laporan->evaluasi) > 0)
                            @foreach($laporan->evaluasi as $ev)
                                @if(!empty($ev['aspek_indikator']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $ev['aspek_indikator'] }}</td>
                                        <td>{{ $ev['metode'] ?: '-' }}</td>
                                        <td class="font-weight-bold text-dark">{{ $ev['hasil'] ?: '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data evaluasi</td></tr>
                        @endif
                    </tbody>
                </table>
                @if($laporan->catatan_evaluasi)
                    <div class="mt-2 p-3 bg-light rounded text-justify mb-4">
                        <strong class="text-dark d-block mb-1">Catatan Evaluasi (Temuan, Kendala, & Pembelajaran):</strong>
                        {!! nl2br(e($laporan->catatan_evaluasi)) !!}
                    </div>
                @endif

                <!-- L. TINDAK LANJUT -->
                <div class="doc-section-title">
                    <i class="fas fa-level-up-alt mr-2 text-primary"></i>L. TINDAK LANJUT / IMPROVEMENT
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="42%">Tindak Lanjut</th>
                            <th width="20%">PIC</th>
                            <th width="18%">Target Waktu</th>
                            <th width="14%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->tindak_lanjut && count($laporan->tindak_lanjut) > 0)
                            @foreach($laporan->tindak_lanjut as $tl)
                                @if(!empty($tl['tindak_lanjut']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $tl['tindak_lanjut'] }}</td>
                                        <td>{{ $tl['pic'] ?: '-' }}</td>
                                        <td>{{ $tl['target_waktu'] ?: '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ ($tl['status'] ?? '') == 'Done' ? 'success' : (($tl['status'] ?? '') == 'In Progress' ? 'warning' : 'secondary') }} px-2 py-1">
                                                {{ $tl['status'] ?? 'Open' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data tindak lanjut</td></tr>
                        @endif
                    </tbody>
                </table>

                <!-- M. REALISASI ANGGARAN -->
                <div class="doc-section-title">
                    <i class="fas fa-money-bill-wave mr-2 text-primary"></i>M. REALISASI ANGGARAN
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th width="6%" class="text-center">No.</th>
                            <th width="38%">Komponen</th>
                            <th width="20%" class="text-right">Anggaran (Rp)</th>
                            <th width="20%" class="text-right">Realisasi (Rp)</th>
                            <th width="16%" class="text-right">Selisih (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($laporan->anggaran && count($laporan->anggaran) > 0)
                            @foreach($laporan->anggaran as $ang)
                                @if(!empty($ang['komponen']))
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                        <td>{{ $ang['komponen'] }}</td>
                                        <td class="text-right">Rp {{ number_format($ang['anggaran'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold">Rp {{ number_format($ang['realisasi'] ?? 0, 0, ',', '.') }}</td>
                                        @php $sel = ($ang['anggaran'] ?? 0) - ($ang['realisasi'] ?? 0); @endphp
                                        <td class="text-right font-weight-bold {{ $sel < 0 ? 'text-danger' : 'text-success' }}">
                                            Rp {{ number_format($sel, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada rincian anggaran</td></tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-light font-weight-bold" style="font-size: 0.95rem;">
                        <tr>
                            <td colspan="2" class="text-right">TOTAL KESELURUHAN:</td>
                            <td class="text-right text-primary">Rp {{ number_format($laporan->total_anggaran, 0, ',', '.') }}</td>
                            <td class="text-right text-success">Rp {{ number_format($laporan->total_realisasi, 0, ',', '.') }}</td>
                            <td class="text-right {{ $laporan->total_selisih < 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($laporan->total_selisih, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <!-- N. DOKUMENTASI FOTO -->
                <div class="doc-section-title">
                    <i class="fas fa-camera mr-2 text-primary"></i>N. DOKUMENTASI KEGIATAN
                </div>
                @if($laporan->fotoDokumentasis->count() > 0)
                    <div class="row mb-4">
                        @foreach($laporan->fotoDokumentasis as $foto)
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="gallery-photo-card">
                                    <a href="{{ $foto->file_url }}" target="_blank">
                                        <img src="{{ $foto->file_url }}" alt="{{ $foto->keterangan }}">
                                    </a>
                                    <div class="p-2 text-center small text-muted font-weight-bold">
                                        {{ $foto->keterangan ?: 'Dokumentasi Kegiatan' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted italic mb-4">Belum ada foto dokumentasi yang diunggah.</p>
                @endif

                <!-- O. KESIMPULAN -->
                <div class="doc-section-title">
                    <i class="fas fa-check-circle mr-2 text-primary"></i>O. KESIMPULAN
                </div>
                <div class="p-3 bg-light rounded text-justify mb-4" style="line-height: 1.7; font-size: 0.92rem;">
                    {!! nl2br(e($laporan->kesimpulan ?: 'Tidak ada uraian kesimpulan.')) !!}
                </div>

                <!-- P. PENGESAHAN -->
                <div class="doc-section-title">
                    <i class="fas fa-signature mr-2 text-primary"></i>P. PENGESAHAN
                </div>
                <p class="text-muted small mb-4">Laporan ini dibuat sebagai dokumentasi dan pertanggungjawaban pelaksanaan kegiatan SDM.</p>

                <div class="row mt-4">
                    <div class="col-6">
                        <div class="signature-box">
                            <p class="mb-1 text-muted">Mengetahui,</p>
                            <strong class="text-dark d-block mb-1">{{ $laporan->mengetahui_jabatan ?: 'Direktur Sumber Daya Manusia' }}</strong>
                            <div class="signature-space"></div>
                            <strong class="d-block text-dark text-decoration-underline font-weight-bold">
                                {{ $laporan->mengetahui_nama ?: '(................................................)' }}
                            </strong>
                            <span class="text-muted small d-block">
                                NIP: {{ $laporan->mengetahui_nip ?: '-' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="signature-box">
                            <p class="mb-1 text-muted">Surakarta, {{ $laporan->tanggal_pengesahan ? $laporan->tanggal_pengesahan->translatedFormat('d F Y') : date('d F Y') }}</p>
                            <p class="mb-1 text-muted">Disusun oleh,</p>
                            <strong class="text-dark d-block mb-1">{{ $laporan->disusun_jabatan ?: 'PIC / Kepala Subbagian SDM' }}</strong>
                            <div class="signature-space"></div>
                            <strong class="d-block text-dark text-decoration-underline font-weight-bold">
                                {{ $laporan->disusun_nama ?: '(................................................)' }}
                            </strong>
                            <span class="text-muted small d-block">
                                NIP: {{ $laporan->disusun_nip ?: '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- LAMPIRAN BERKAS -->
                <div class="doc-section-title mt-5">
                    <i class="fas fa-paperclip mr-2 text-primary"></i>LAMPIRAN DOKUMEN PENDUKUNG
                </div>
                @if($laporan->berkasLampirans->count() > 0)
                    <div class="list-group mb-3">
                        @foreach($laporan->berkasLampirans as $b)
                            <a href="{{ $b->file_url }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf text-danger mr-2"></i>
                                    <strong>{{ $b->kategori_label }}</strong>
                                    <span class="text-muted ml-2">({{ $b->nama_file }} - {{ $b->keterangan }})</span>
                                </div>
                                <span class="badge badge-primary badge-pill"><i class="fas fa-download mr-1"></i>Unduh</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted italic">Tidak ada berkas lampiran pendukung fisik yang diunggah.</p>
                @endif

            </div>

        </div>
    </section>
@endsection
