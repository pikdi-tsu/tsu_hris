<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir - {{ $kegiatan->nama_kegiatan }}</title>
    <link rel="stylesheet" href="{{ asset('public/assets/dist/css/adminlte.min.css') }}">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            font-size: 11pt;
            margin: 20px;
        }
        .header-kop {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .table-print th, .table-print td {
            border: 1px solid #000 !important;
            padding: 5px 8px !important;
            vertical-align: middle;
        }
        .table-print th {
            background-color: #f2f2f2 !important;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print mb-3 text-right">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="fas fa-print mr-1"></i> Cetak Dokumen (Print / Save as PDF)
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ml-1">
            Tutup
        </button>
    </div>

    {{-- Kop Surat --}}
    <div class="header-kop text-center">
        <h4 class="font-weight-bold mb-1" style="letter-spacing: 1px;">UNIVERSITAS TIGA SERANGKAI (TSU)</h4>
        <div style="font-size: 10pt;">BAGIAN SUMBER DAYA MANUSIA & KEPEGAWAIAN</div>
        <div class="small text-muted" style="font-size: 9pt;">Jl. Dr. Supomo No. 23, Surakarta, Jawa Tengah &bull; Telp: (0271) 714344 &bull; Web: tsu.ac.id</div>
    </div>

    {{-- Judul Dokumen --}}
    <div class="text-center my-3">
        <h5 class="font-weight-bold mb-1" style="text-decoration: underline;">DAFTAR HADIR KEGIATAN UNIVERSITAS</h5>
        <small class="font-weight-bold">Nomor Dokumen: REG/ABS-KEG/{{ $kegiatan->tanggal_kegiatan->format('Ym') }}/{{ substr($kegiatan->id, 0, 6) }}</small>
    </div>

    {{-- Detail Acara --}}
    <table class="table table-borderless table-sm mb-3" style="font-size: 10.5pt; width: 100%;">
        <tr>
            <td style="width: 180px;"><strong>Nama Kegiatan</strong></td>
            <td style="width: 10px;">:</td>
            <td class="font-weight-bold">{{ $kegiatan->nama_kegiatan }}</td>
        </tr>
        <tr>
            <td><strong>Kategori & Target</strong></td>
            <td>:</td>
            <td>{{ $kegiatan->kategori }} &bull; Target: {{ $kegiatan->target_peserta }}</td>
        </tr>
        <tr>
            <td><strong>Hari / Tanggal</strong></td>
            <td>:</td>
            <td>{{ $kegiatan->tanggal_kegiatan->isoFormat('dddd, D MMMM Y') ?? $kegiatan->tanggal_kegiatan->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Pelaksanaan</strong></td>
            <td>:</td>
            <td>{{ substr($kegiatan->jam_mulai, 0, 5) }} - {{ substr($kegiatan->jam_selesai, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td><strong>Tempat / Ruangan</strong></td>
            <td>:</td>
            <td>{{ $kegiatan->lokasi }}</td>
        </tr>
        <tr>
            <td><strong>Penyelenggara / PIC</strong></td>
            <td>:</td>
            <td>{{ optional($kegiatan->penyelenggaraUnit)->nama_unit ?? 'Universitas' }} {{ $kegiatan->penanggungJawab ? '('.$kegiatan->penanggungJawab->nomor_induk.' - '.$kegiatan->penanggungJawab->nama.')' : '' }}</td>
        </tr>
    </table>

    {{-- Ringkasan Kehadiran --}}
    <div class="mb-2" style="font-size: 10pt;">
        <strong>Rekapitulasi Kehadiran:</strong>
        Hadir (Ya): {{ $presensis->where('status_kehadiran', 'Ya')->count() }} orang &bull;
        Terlambat: {{ $presensis->where('status_kehadiran', 'Terlambat')->count() }} orang &bull;
        Tidak Hadir: {{ $presensis->where('status_kehadiran', 'Tidak')->count() }} orang &bull;
        <strong>Total Presensi Tercatat: {{ $presensis->count() }} orang</strong>
    </div>

    {{-- Tabel Daftar Hadir --}}
    <table class="table table-print table-sm" style="width: 100%; font-size: 10pt;">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Lengkap Pegawai</th>
                <th style="width: 130px;">NIP / ID</th>
                <th>Unit Kerja</th>
                <th style="width: 85px;">Status</th>
                <th style="width: 85px;">Waktu</th>
                <th>Keterangan</th>
                <th style="width: 80px;">Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensis as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">
                        {{ optional($p->pegawai)->nama ?? optional($p->user)->name }}
                    </td>
                    <td>{{ optional($p->pegawai)->nomor_induk ?? '-' }}</td>
                    <td>{{ optional(optional($p->pegawai)->unit)->nama_unit ?? '-' }}</td>
                    <td class="text-center">
                        {{ $p->status_kehadiran === 'Ya' ? 'Hadir' : ($p->status_kehadiran === 'Terlambat' ? 'Terlambat' : 'Tidak') }}
                    </td>
                    <td class="text-center">{{ $p->waktu_presensi->format('H:i') }}</td>
                    <td>{{ $p->keterangan ?: '-' }}</td>
                    <td class="text-center small">
                        {{ $p->foto_selfie ? '✓ Foto Ada' : '✓ Sistem' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-3 text-muted">Belum ada catatan kehadiran peserta.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Lembar Pengesahan --}}
    <div class="row mt-5" style="page-break-inside: avoid;">
        <div class="col-6 text-center">
            <p class="mb-5">
                Mengetahui,<br>
                <strong>Penanggung Jawab Acara</strong>
            </p>
            <p class="font-weight-bold mb-0" style="text-decoration: underline;">
                {{ optional($kegiatan->penanggungJawab)->nama ?? '...................................................' }}
            </p>
            <small class="text-muted">NIP. {{ optional($kegiatan->penanggungJawab)->nomor_induk ?? '....................................' }}</small>
        </div>

        <div class="col-6 text-center">
            <p class="mb-5">
                Surakarta, {{ date('d F Y') }}<br>
                <strong>Bagian Sumber Daya Manusia (SDM)</strong>
            </p>
            <p class="font-weight-bold mb-0" style="text-decoration: underline;">
                ...................................................
            </p>
            <small class="text-muted">NIP. ....................................</small>
        </div>
    </div>

</body>
</html>
