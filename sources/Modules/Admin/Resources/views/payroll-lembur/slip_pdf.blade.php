<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SLIP LEMBUR - {{ strtoupper($karyawan->nama) }} - {{ strtoupper($period->nama_periode) }}</title>
    <style>
        @page {
            margin: 35px 45px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #111827;
            line-height: 1.4;
        }

        .header-logo {
            margin-bottom: 20px;
        }
        .header-logo img {
            height: 60px;
        }

        .title-block {
            text-align: center;
            margin-bottom: 25px;
        }
        .title-block h1 {
            font-size: 12.5pt;
            font-weight: bold;
            letter-spacing: 0.8px;
            margin: 0;
            text-transform: uppercase;
        }
        .title-block h2 {
            font-size: 11.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 3px 0 0 0;
            text-transform: uppercase;
        }
        .title-block .subtitle-cutoff {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 3px 0 0 0;
        }

        .info-pegawai {
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .info-pegawai table {
            border-collapse: collapse;
        }
        .info-pegawai td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* Table Rincian Lembur */
        .table-lembur {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-lembur th, .table-lembur td {
            border: 1px solid #1e293b;
            padding: 5.5px 8px;
            font-size: 9.5pt;
        }
        .table-lembur th {
            background-color: #b8cce4;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .table-lembur td.text-center {
            text-align: center;
        }
        .table-lembur td.text-right {
            text-align: right;
        }
        .table-lembur tr.total-row td {
            font-weight: bold;
            background-color: #ffffff;
        }

        /* Catatan */
        .catatan-box {
            margin-top: 15px;
            margin-bottom: 30px;
            font-size: 9.5pt;
            line-height: 1.4;
        }

        /* Signature Section */
        .sign-container {
            width: 100%;
            margin-top: 20px;
        }
        .sign-table {
            width: 270px;
            float: right;
            text-align: left;
        }
        .sign-table td {
            font-size: 10pt;
            line-height: 1.35;
        }
        .sign-img-wrapper {
            position: relative;
            height: 80px;
            margin: 6px 0 4px 0;
        }
        .sign-stamp-img {
            position: absolute;
            left: -15px;
            top: 0px;
            max-height: 75px;
            max-width: 90px;
            opacity: 0.88;
            z-index: 1;
        }
        .sign-ttd-img {
            position: absolute;
            left: 20px;
            top: 5px;
            max-height: 70px;
            max-width: 160px;
            z-index: 2;
        }
        .sign-name {
            font-weight: bold;
            margin-top: 6px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    @php
        // Logo Kampus (Fallback ke tsu.png atau Logo_TSU_Transparan.png)
        $logoPath = base_path('../public/assetsku/img/tsu.png');
        if (!file_exists($logoPath)) {
            $logoPath = base_path('../public/assetsku/img/Logo_TSU_Transparan.png');
        }
        if (!file_exists($logoPath)) {
            $logoPath = base_path('../public/assetsku/img/logotsu.png');
        }
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        // Cek apakah Approval Final sudah ACC (periode resmi berstatus terkunci/final)
        $isFinalApproved = in_array($period->status, ['locked', 'approved', 'final']) || !empty($period->is_locked) || !empty($period->locked_at);

        // Tanda tangan & Stempel (HANYA muncul jika Approval Final sudah ACC)
        $ttdImgSrc = null;
        $stempelImgSrc = null;

        if ($isFinalApproved && isset($ttdMaster) && $ttdMaster) {
            if ($ttdMaster->file_ttd && \Illuminate\Support\Facades\Storage::disk('public')->exists($ttdMaster->file_ttd)) {
                $ttdPath = storage_path('app/public/' . $ttdMaster->file_ttd);
                $ttdImgSrc = file_exists($ttdPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ttdPath)) : null;
            }
            if ($ttdMaster->file_stempel && \Illuminate\Support\Facades\Storage::disk('public')->exists($ttdMaster->file_stempel)) {
                $stempelPath = storage_path('app/public/' . $ttdMaster->file_stempel);
                $stempelImgSrc = file_exists($stempelPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath)) : null;
            }
        }

        // Tanggal Surat
        $tglSurat = $period->tanggal_surat ?: now();
        $tglSuratFormat = \Carbon\Carbon::parse($tglSurat)->translatedFormat('d F Y');
        $kotaSurat = $period->kota_surat ?: 'Surakarta';

        // Penandatangan
        $penandatanganNama = $period->penandatangan_nama ?: ($ttdMaster->nama_pejabat ?? ($period->karyawanTtd->nama ?? '-'));
        $penandatanganJabatan = $period->penandatangan_jabatan ?: ($ttdMaster->jabatan ?? 'Bagian SDM');

        // Rentang Cut-Off
        $startCutoff = \Carbon\Carbon::parse($period->start_date_cutoff)->translatedFormat('j F');
        $endCutoff = \Carbon\Carbon::parse($period->end_date_cutoff)->translatedFormat('j F Y');
    @endphp

    <!-- 1. Header Logo -->
    <div class="header-logo">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo TSU">
        @else
            <div style="font-weight: bold; font-size: 15pt; color: #094B54;">TSU <span style="font-size: 10pt; color: #64748b; font-weight: normal;">TIGA SERANGKAI UNIVERSITY</span></div>
        @endif
    </div>

    <!-- 2. Judul Dokumen -->
    <div class="title-block">
        <h1>SLIP LEMBUR {{ strtoupper($karyawan->tipe_karyawan) }} TSU</h1>
        <h2>{{ strtoupper($period->periode_bulan_tahun) }}</h2>
        <div class="subtitle-cutoff">({{ $startCutoff }} – {{ $endCutoff }})</div>
    </div>

    <!-- 3. Info Pegawai -->
    <div class="info-pegawai">
        <table>
            <tr>
                <td style="width: 110px;">Nama Lengkap</td>
                <td style="width: 15px;">:</td>
                <td style="font-weight: 500;">{{ $karyawan->nama }}</td>
            </tr>
            <tr>
                <td>Gaji Tetap</td>
                <td>:</td>
                <td style="font-weight: 500;">Rp{{ number_format($karyawan->gaji_tetap, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- 4. Tabel Rincian Lembur -->
    <table class="table-lembur">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="35%">TANGGAL</th>
                <th width="18%">JENIS LEMBUR</th>
                <th width="14%">JAM LEMBUR</th>
                <th width="13%">UPAH/JAM</th>
                <th width="15%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($karyawan->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->hari_tanggal_label }}</td>
                    <td>{{ $detail->jenis_lembur }}</td>
                    <td class="text-center">{{ number_format($detail->jam_lembur, 0) }} jam</td>
                    <td class="text-right">{{ number_format($detail->upah_per_jam, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->total_nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada rincian jam lembur pada periode ini.
                    </td>
                </tr>
            @endforelse

            <!-- Baris Total -->
            <tr class="total-row">
                <td colspan="5" class="text-right" style="letter-spacing: 0.5px;">TOTAL</td>
                <td class="text-right">{{ number_format($karyawan->total_lembur_final, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 5. Catatan Keterangan -->
    <div class="catatan-box">
        <strong>**Catatan:</strong><br>
        <em>Periode cut-off lembur adalah {{ $startCutoff }} – {{ $endCutoff }}</em>
        @if($karyawan->penyesuaian != 0)
            <br><em>*Terdapat penyesuaian/koreksi nominal: {{ $karyawan->penyesuaian > 0 ? '+' : '' }}Rp{{ number_format($karyawan->penyesuaian, 0, ',', '.') }} ({{ $karyawan->catatan_koreksi ?: 'Penyesuaian resmi SDM' }})</em>
        @endif
    </div>

    <!-- 6. Area Tanda Tangan & Stempel -->
    <div class="sign-container">
        <table class="sign-table">
            <tr>
                <td>{{ $kotaSurat }}, {{ $tglSuratFormat }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">{{ $penandatanganJabatan }},</td>
            </tr>
            <tr>
                <td>
                    <div class="sign-img-wrapper">
                        @if($isFinalApproved)
                            @if($stempelImgSrc)
                                <img src="{{ $stempelImgSrc }}" class="sign-stamp-img" alt="Stempel">
                            @elseif($logoBase64)
                                <img src="{{ $logoBase64 }}" class="sign-stamp-img" style="opacity: 0.75;" alt="Stempel TSU">
                            @endif
                        @endif

                        @if($ttdImgSrc)
                            <img src="{{ $ttdImgSrc }}" class="sign-ttd-img" alt="TTD">
                        @else
                            <div style="height: 60px;"></div>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td class="sign-name">{{ $penandatanganNama }}</td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

</body>
</html>
