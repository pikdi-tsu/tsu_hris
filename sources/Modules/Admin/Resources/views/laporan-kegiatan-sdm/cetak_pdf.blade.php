<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>LAPORAN KEGIATAN SDM - {{ strtoupper($laporan->nama_kegiatan) }}</title>
    <style>
        @page {
            margin: 30px 45px 40px 45px;
            size: A4 portrait;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #111827;
            line-height: 1.45;
        }

        .header-logo {
            text-align: left;
            margin-bottom: 12px;
        }
        .header-logo img {
            height: 52px;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 22px;
        }
        .doc-header .inst-name {
            font-size: 11pt;
            font-weight: bold;
            color: #094b54;
            letter-spacing: 0.8px;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-header .doc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111827;
            margin: 4px 0 2px 0;
            text-transform: uppercase;
        }
        .doc-header .activity-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0369a1;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-header .doc-no {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 3px;
        }

        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            color: #094b54;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .sub-heading {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1f2937;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .text-narrative {
            text-align: justify;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        table.tbl-doc {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        table.tbl-doc tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.tbl-doc th {
            background-color: #D9EAF7;
            color: #0c4a6e;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: middle;
            text-align: left;
        }
        table.tbl-doc th.center, table.tbl-doc td.center {
            text-align: center;
        }
        table.tbl-doc th.right, table.tbl-doc td.right {
            text-align: right;
        }
        table.tbl-doc td {
            border: 1px solid #cbd5e1;
            padding: 4.5px 6px;
            font-size: 8.5pt;
            vertical-align: top;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            border: none;
            padding: 0 15px;
        }
        .sign-space {
            height: 65px;
        }

        .footer-note {
            font-size: 8pt;
            color: #64748b;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        .photo-box {
            display: inline-block;
            width: 48%;
            margin-bottom: 10px;
            vertical-align: top;
            text-align: center;
            border: 1px solid #cbd5e1;
            padding: 5px;
            box-sizing: border-box;
        }
        .photo-box img {
            max-width: 100%;
            max-height: 160px;
            display: block;
            margin: 0 auto 5px auto;
        }
        .photo-caption {
            font-size: 8pt;
            color: #334155;
            font-weight: bold;
        }
    </style>
</head>
<body>

    @php
        // Logo Kampus
        $logoPath = public_path('assetsku/img/logotsu.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('assetsku/img/Logo_TSU_Transparan.png');
        }
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        // Tanggal Pengesahan
        $tglPengesahan = $laporan->tanggal_pengesahan ?: now();
        $tglPengesahanFormat = \Carbon\Carbon::parse($tglPengesahan)->locale('id')->isoFormat('D MMMM Y');
    @endphp

    <!-- Logo & Kop Surat -->
    <div class="header-logo">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo TSU">
        @else
            <div style="font-weight: bold; font-size: 14pt; color: #094B54;">TIGA SERANGKAI UNIVERSITY</div>
        @endif
    </div>

    <!-- Judul Dokumen (Sesuai Template Word TSU) -->
    <div class="doc-header">
        <div class="inst-name">TIGA SERANGKAI UNIVERSITY</div>
        <div class="doc-title">TEMPLATE LAPORAN KEGIATAN SUMBER DAYA MANUSIA</div>
        <div class="activity-name">[{{ strtoupper($laporan->nama_kegiatan) }}]</div>
        <div class="doc-no">Nomor Dokumen: {{ $laporan->nomor_laporan }}</div>
    </div>

    <!-- A. IDENTITAS KEGIATAN -->
    <div class="section-heading">A. IDENTITAS KEGIATAN</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="35%">Komponen</th>
                <th width="65%">Isian</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold;">Nama Kegiatan</td>
                <td>{{ $laporan->nama_kegiatan }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Kategori Kegiatan</td>
                <td>{{ $laporan->kategori_kegiatan ?: '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Penyelenggara / Unit</td>
                <td>Bagian Sumber Daya Manusia (SDM) TSU</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Sasaran Peserta</td>
                <td>Dosen dan Tenaga Kependidikan TSU</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Waktu Pelaksanaan</td>
                <td>{{ $laporan->rentang_tanggal_formatted }} ({{ $laporan->waktu_pelaksanaan ?: '-' }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tempat / Metode</td>
                <td>{{ ucfirst($laporan->tipe_tempat) }} - {{ $laporan->tempat_pelaksanaan ?: '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Penanggung Jawab (PIC)</td>
                <td>{{ $laporan->disusun_nama ?: 'PIC / Kepala Subbagian SDM' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Realisasi Anggaran</td>
                <td>Rp {{ number_format($laporan->total_realisasi, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- B. LATAR BELAKANG -->
    <div class="section-heading">B. LATAR BELAKANG</div>
    <div class="text-narrative">
        {!! nl2br(e($laporan->latar_belakang ?: '[Uraikan latar belakang dan alasan pelaksanaan kegiatan.]')) !!}
    </div>

    <!-- C. TUJUAN -->
    <div class="section-heading">C. TUJUAN</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="8%" class="center">No.</th>
                <th width="92%">Tujuan</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->tujuan && count($laporan->tujuan) > 0)
                @foreach($laporan->tujuan as $idx => $tuj)
                    @if(!empty($tuj['tujuan']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $tuj['tujuan'] }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- D. DASAR PELAKSANAAN -->
    <div class="section-heading">D. DASAR PELAKSANAAN</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="44%">Dasar Pelaksanaan</th>
                <th width="25%">Nomor/Tanggal</th>
                <th width="25%">Dokumen</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->dasar_pelaksanaan && count($laporan->dasar_pelaksanaan) > 0)
                @foreach($laporan->dasar_pelaksanaan as $idx => $dsr)
                    @if(!empty($dsr['dasar']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $dsr['dasar'] }}</td>
                            <td>{{ $dsr['nomor_tanggal'] ?: '-' }}</td>
                            <td>{{ $dsr['dokumen'] ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- E. WAKTU DAN TEMPAT -->
    <div class="section-heading">E. WAKTU DAN TEMPAT</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="35%">Komponen</th>
                <th width="65%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold;">Hari / Tanggal</td>
                <td>{{ $laporan->rentang_tanggal_formatted }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Waktu Pelaksanaan</td>
                <td>{{ $laporan->waktu_pelaksanaan ?: '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Metode Pelaksanaan</td>
                <td>{{ ucfirst($laporan->tipe_tempat) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tempat / Ruang</td>
                <td>{{ $laporan->tempat_pelaksanaan ?: '-' }}</td>
            </tr>
            @if($laporan->link_daring)
                <tr>
                    <td style="font-weight: bold;">Tautan Daring</td>
                    <td>{{ $laporan->link_daring }}</td>
                </tr>
            @endif
            @if($laporan->keterangan_waktu_tempat)
                <tr>
                    <td style="font-weight: bold;">Keterangan Tambahan</td>
                    <td>{{ $laporan->keterangan_waktu_tempat }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- F. PESERTA -->
    <div class="section-heading">F. PESERTA</div>
    <div class="text-narrative" style="margin-bottom: 6px;">
        Jumlah peserta yang direncanakan: <strong>{{ $laporan->jumlah_peserta_rencana }}</strong> orang<br>
        Jumlah peserta hadir: <strong>{{ $laporan->jumlah_peserta_hadir }}</strong> orang<br>
        Jumlah peserta tidak hadir: <strong>{{ $laporan->jumlah_peserta_tidak_hadir }}</strong> orang
    </div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="5%" class="center">No.</th>
                <th width="30%">Nama</th>
                <th width="17%">NIP/NIDN</th>
                <th width="20%">Unit</th>
                <th width="16%">Jabatan</th>
                <th width="12%" class="center">Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->pesertas->count() > 0)
                @foreach($laporan->pesertas as $p)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $p->nama }}</td>
                        <td>{{ $p->nip_nidn ?: '-' }}</td>
                        <td>{{ $p->unit ?: '-' }}</td>
                        <td>{{ $p->jabatan ?: '-' }}</td>
                        <td class="center">{{ $p->kehadiran }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td><td>-</td><td class="center">-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- G. PELAKSANA / PANITIA / PIHAK TERLIBAT -->
    <div class="section-heading">G. PELAKSANA / PANITIA / PIHAK TERLIBAT</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="30%">Nama</th>
                <th width="24%">Unit/Instansi</th>
                <th width="20%">Jabatan</th>
                <th width="20%">Peran</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->panitia && count($laporan->panitia) > 0)
                @foreach($laporan->panitia as $pan)
                    @if(!empty($pan['nama']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $pan['nama'] }}</td>
                            <td>{{ $pan['unit_instansi'] ?: '-' }}</td>
                            <td>{{ $pan['jabatan'] ?: '-' }}</td>
                            <td>{{ $pan['peran'] ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- H. NARASUMBER / FASILITATOR -->
    <div class="section-heading">H. NARASUMBER / FASILITATOR</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="28%">Nama</th>
                <th width="22%">Instansi</th>
                <th width="18%">Jabatan</th>
                <th width="26%">Materi/Peran</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->narasumber && count($laporan->narasumber) > 0)
                @foreach($laporan->narasumber as $ns)
                    @if(!empty($ns['nama']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $ns['nama'] }}</td>
                            <td>{{ $ns['instansi'] ?: '-' }}</td>
                            <td>{{ $ns['jabatan'] ?: '-' }}</td>
                            <td>{{ $ns['materi_peran'] ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- I. RANGKAIAN / PELAKSANAAN KEGIATAN -->
    <div class="section-heading">I. RANGKAIAN / PELAKSANAAN KEGIATAN</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="20%">Waktu</th>
                <th width="30%">Agenda</th>
                <th width="50%">Uraian Pelaksanaan</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->rundown && count($laporan->rundown) > 0)
                @foreach($laporan->rundown as $rd)
                    @if(!empty($rd['agenda']))
                        <tr>
                            <td>{{ $rd['waktu'] ?: '-' }}</td>
                            <td><strong>{{ $rd['agenda'] }}</strong></td>
                            <td>{{ $rd['uraian'] ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td>-</td><td>-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- J. HASIL DAN CAPAIAN -->
    <div class="section-heading">J. HASIL DAN CAPAIAN</div>

    <div class="sub-heading">1. Target Kegiatan</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="50%">Indikator</th>
                <th width="44%">Target</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->target_kegiatan && count($laporan->target_kegiatan) > 0)
                @foreach($laporan->target_kegiatan as $tgt)
                    @if(!empty($tgt['indikator']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $tgt['indikator'] }}</td>
                            <td>{{ $tgt['target'] }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>

    <div class="sub-heading">2. Realisasi dan Capaian</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="5%" class="center">No.</th>
                <th width="32%">Indikator</th>
                <th width="16%">Target</th>
                <th width="16%">Realisasi</th>
                <th width="15%" class="center">Capaian (%)</th>
                <th width="16%" class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->capaian_kegiatan && count($laporan->capaian_kegiatan) > 0)
                @foreach($laporan->capaian_kegiatan as $cap)
                    @if(!empty($cap['indikator']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $cap['indikator'] }}</td>
                            <td>{{ $cap['target'] }}</td>
                            <td>{{ $cap['realisasi'] }}</td>
                            <td class="center">{{ $cap['capaian_persen'] }}%</td>
                            <td class="center">{{ $cap['status'] }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td><td class="center">-</td><td class="center">-</td></tr>
            @endif
        </tbody>
    </table>

    <div class="sub-heading">3. Uraian Hasil</div>
    <div class="text-narrative">
        {!! nl2br(e($laporan->uraian_hasil ?: '[Jelaskan hasil utama, output, dan capaian kegiatan.]')) !!}
    </div>

    <!-- K. EVALUASI -->
    <div class="section-heading">K. EVALUASI</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="35%">Aspek/Indikator Evaluasi</th>
                <th width="25%">Metode</th>
                <th width="34%">Hasil</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->evaluasi && count($laporan->evaluasi) > 0)
                @foreach($laporan->evaluasi as $ev)
                    @if(!empty($ev['aspek_indikator']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $ev['aspek_indikator'] }}</td>
                            <td>{{ $ev['metode'] ?: '-' }}</td>
                            <td>{{ $ev['hasil'] ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td></tr>
            @endif
        </tbody>
    </table>
    @if($laporan->catatan_evaluasi)
        <div class="text-narrative" style="margin-top: 5px;">
            <strong>Catatan evaluasi:</strong><br>
            {!! nl2br(e($laporan->catatan_evaluasi)) !!}
        </div>
    @endif

    <!-- L. TINDAK LANJUT / IMPROVEMENT -->
    <div class="section-heading">L. TINDAK LANJUT / IMPROVEMENT</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="42%">Tindak Lanjut</th>
                <th width="20%">PIC</th>
                <th width="18%">Target Waktu</th>
                <th width="14%" class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->tindak_lanjut && count($laporan->tindak_lanjut) > 0)
                @foreach($laporan->tindak_lanjut as $tl)
                    @if(!empty($tl['tindak_lanjut']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $tl['tindak_lanjut'] }}</td>
                            <td>{{ $tl['pic'] ?: '-' }}</td>
                            <td>{{ $tl['target_waktu'] ?: '-' }}</td>
                            <td class="center">{{ $tl['status'] ?: 'Open' }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td>-</td><td>-</td><td class="center">-</td></tr>
            @endif
        </tbody>
    </table>

    <!-- M. REALISASI ANGGARAN -->
    <div class="section-heading">M. REALISASI ANGGARAN</div>
    <table class="tbl-doc">
        <thead>
            <tr>
                <th width="6%" class="center">No.</th>
                <th width="38%">Komponen</th>
                <th width="20%" class="right">Anggaran</th>
                <th width="20%" class="right">Realisasi</th>
                <th width="16%" class="right">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @if($laporan->anggaran && count($laporan->anggaran) > 0)
                @foreach($laporan->anggaran as $ang)
                    @if(!empty($ang['komponen']))
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $ang['komponen'] }}</td>
                            <td class="right">Rp {{ number_format($ang['anggaran'] ?? 0, 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($ang['realisasi'] ?? 0, 0, ',', '.') }}</td>
                            @php $sel = ($ang['anggaran'] ?? 0) - ($ang['realisasi'] ?? 0); @endphp
                            <td class="right">Rp {{ number_format($sel, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr><td class="center">1</td><td>-</td><td class="right">Rp 0</td><td class="right">Rp 0</td><td class="right">Rp 0</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2" class="right">TOTAL:</td>
                <td class="right">Rp {{ number_format($laporan->total_anggaran, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($laporan->total_realisasi, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($laporan->total_selisih, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    <div class="text-narrative" style="font-weight: bold; margin-bottom: 12px;">
        Total Anggaran: Rp {{ number_format($laporan->total_anggaran, 0, ',', '.') }} &nbsp;&nbsp;&nbsp;&nbsp;
        Total Realisasi: Rp {{ number_format($laporan->total_realisasi, 0, ',', '.') }} &nbsp;&nbsp;&nbsp;&nbsp;
        Sisa: Rp {{ number_format($laporan->total_selisih, 0, ',', '.') }}
    </div>

    <!-- N. DOKUMENTASI -->
    <div class="section-heading">N. DOKUMENTASI</div>
    <div class="text-narrative">[Dokumentasi foto pelaksanaan kegiatan:]</div>
    @if($laporan->fotoDokumentasis->count() > 0)
        <div>
            @foreach($laporan->fotoDokumentasis as $foto)
                @php
                    $fullPath = storage_path('app/' . $foto->file_path);
                    $fotoB64 = file_exists($fullPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($fullPath)) : null;
                @endphp
                @if($fotoB64)
                    <div class="photo-box">
                        <img src="{{ $fotoB64 }}" alt="Dokumentasi">
                        <div class="photo-caption">{{ $foto->keterangan ?: 'Dokumentasi Kegiatan' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-narrative italic text-muted">- Dokumentasi foto terlampir pada arsip fisik / digital terpisah -</div>
    @endif

    <!-- O. KESIMPULAN -->
    <div class="section-heading" style="clear: both; margin-top: 15px;">O. KESIMPULAN</div>
    <div class="text-narrative">
        {!! nl2br(e($laporan->kesimpulan ?: '[Tuliskan kesimpulan pelaksanaan kegiatan, tingkat pencapaian tujuan, dan rekomendasi perbaikan.]')) !!}
    </div>

    <!-- P. PENGESAHAN -->
    <div class="section-heading">P. PENGESAHAN</div>
    <div class="text-narrative">Laporan ini dibuat sebagai dokumentasi dan pertanggungjawaban pelaksanaan kegiatan SDM.</div>

    <table class="sign-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>{{ $laporan->mengetahui_jabatan ?: 'Direktur Sumber Daya Manusia' }}</strong>
                <div class="sign-space"></div>
                <strong style="text-decoration: underline;">
                    {{ $laporan->mengetahui_nama ?: '(................................................)' }}
                </strong><br>
                <span style="font-size: 8.5pt;">NIP: {{ $laporan->mengetahui_nip ?: '-' }}</span>
            </td>
            <td>
                Surakarta, {{ $tglPengesahanFormat }}<br>
                Disusun oleh,<br>
                <strong>{{ $laporan->disusun_jabatan ?: 'PIC/Kepala Subbagian SDM' }}</strong>
                <div class="sign-space"></div>
                <strong style="text-decoration: underline;">
                    {{ $laporan->disusun_nama ?: '(................................................)' }}
                </strong><br>
                <span style="font-size: 8.5pt;">NIP: {{ $laporan->disusun_nip ?: '-' }}</span>
            </td>
        </tr>
    </table>

    <!-- LAMPIRAN -->
    <div class="section-heading" style="margin-top: 25px;">LAMPIRAN</div>
    <div class="text-narrative" style="line-height: 1.6;">
        Lampiran 1. Daftar Hadir<br>
        Lampiran 2. Materi / Bahan Kegiatan<br>
        Lampiran 3. Hasil Evaluasi<br>
        Lampiran 4. Bukti Pengeluaran / Dokumen Keuangan<br>
        Lampiran 5. Dokumentasi Foto<br>
        Lampiran 6. Dokumen Pendukung Lainnya
    </div>

    <div class="footer-note">
        Dokumen ini diterbitkan secara resmi melalui Sistem HRIS Tiga Serangkai University pada {{ date('d/m/Y H:i:s') }}.
    </div>

</body>
</html>
