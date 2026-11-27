<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SLIP THR - {{ strtoupper($karyawan->nama) }} - {{ strtoupper($period->nama_periode) }}</title>
    <style>
        @page {
            margin: 40px 50px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #111827;
            line-height: 1.5;
        }
        
        .header-logo {
            margin-bottom: 25px;
        }
        .header-logo img {
            height: 65px;
        }

        .title-block {
            text-align: center;
            margin-bottom: 35px;
        }
        .title-block h1 {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.8px;
            margin: 0;
            text-transform: uppercase;
        }
        .title-block h2 {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 4px 0 0 0;
            text-transform: uppercase;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table-data td {
            padding: 3.5px 0;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .table-data td.field-label {
            width: 250px;
        }
        .table-data td.field-sep {
            width: 15px;
            text-align: center;
        }
        .table-data td.field-value {
            font-weight: normal;
        }
        .table-data tr.spacer td {
            padding-top: 18px;
        }
        .table-data tr.total-row td {
            font-weight: bold;
            font-size: 11pt;
        }

        .legal-notes {
            font-size: 9.8pt;
            line-height: 1.55;
            color: #1f2937;
            margin-top: 20px;
            margin-bottom: 40px;
            text-align: justify;
        }
        .legal-notes p {
            margin: 0 0 8px 0;
        }
        .legal-notes ol {
            margin: 0;
            padding-left: 22px;
        }
        .legal-notes ol li {
            margin-bottom: 8px;
        }
        .formula-box {
            text-align: center;
            font-weight: bold;
            margin: 6px 0 8px 0;
            font-size: 10pt;
        }

        .sign-wrapper {
            width: 100%;
            margin-top: 15px;
        }
        .sign-table {
            width: 320px;
            float: right;
            text-align: left;
        }
        .sign-table td {
            font-size: 10.5pt;
            line-height: 1.4;
        }
        .sign-img-box {
            height: 75px;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .sign-img-box img {
            max-height: 70px;
            opacity: 0.9;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: none;
            margin-top: 5px;
        }

        .clear {
            clear: both;
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

        // Tanggal Awal Kerja
        $tglAwalFormat = '-';
        if ($karyawan->tgl_awal_kerja) {
            $tglAwalFormat = \Carbon\Carbon::parse($karyawan->tgl_awal_kerja)->locale('id')->isoFormat('D MMMM Y');
        }

        // Tanggal Surat
        $tglSurat = $period->tanggal_surat ?: now();
        $tglSuratFormat = \Carbon\Carbon::parse($tglSurat)->locale('id')->isoFormat('D MMMM Y');
        $kotaSurat = $period->kota_surat ?: 'Surakarta';

        // Penandatangan
        $penandatanganNama = $period->penandatangan_nama ?: ($ttdMaster->nama_pejabat ?? ($period->karyawanTtd->nama ?? '-'));
        $penandatanganJabatan = $period->penandatangan_jabatan ?: ($ttdMaster->jabatan ?? 'Sumber Daya Manusia');

        // Cek apakah Approval Final sudah ACC (periode resmi berstatus terkunci/final)
        $isFinalApproved = in_array($period->status, ['locked', 'approved', 'final']) || !empty($period->is_locked) || !empty($period->locked_at);

        // Tanda tangan & Stempel dari Master Data Tanda Tangan (HANYA muncul jika Approval Final sudah ACC)
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
    @endphp

    <!-- Logo TSU -->
    <div class="header-logo">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo TSU">
        @else
            <div style="font-weight: bold; font-size: 16pt; color: #094B54;">TSU <span style="font-size: 10pt; color: #6b7280; font-weight: normal;">TIGA SERANGKAI UNIVERSITY</span></div>
        @endif
    </div>

    <!-- Judul Dokumen -->
    <div class="title-block">
        <h1>SLIP TUNJANGAN HARI RAYA</h1>
        <h2>{{ strtoupper($period->nama_periode) }}</h2>
    </div>

    <!-- Tabel Data Rincian THR -->
    <table class="table-data">
        <tr>
            <td class="field-label">Nama Lengkap</td>
            <td class="field-sep">:</td>
            <td class="field-value">{{ strtoupper($karyawan->nama) }}</td>
        </tr>
        <tr>
            <td class="field-label">Tanggal Awal Kerja TSU</td>
            <td class="field-sep">:</td>
            <td class="field-value">{{ $tglAwalFormat }}</td>
        </tr>
        <tr>
            <td class="field-label">Status THR</td>
            <td class="field-sep">:</td>
            <td class="field-value">{{ $karyawan->status_thr }}</td>
        </tr>
        
        <!-- Spacer Baris Kosong -->
        <tr class="spacer">
            <td class="field-label">Upah Tetap</td>
            <td class="field-sep">:</td>
            <td class="field-value">Rp{{ number_format($karyawan->upah_tetap, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="field-label">Masa Kerja</td>
            <td class="field-sep">:</td>
            <td class="field-value">{{ $karyawan->masa_kerja_text }}</td>
        </tr>
        <tr class="total-row">
            <td class="field-label">Total Tunjangan Hari Raya</td>
            <td class="field-sep">:</td>
            <td class="field-value">Rp{{ number_format($karyawan->total_thr, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Keterangan Regulasi Perhitungan -->
    <div class="legal-notes">
        <p>Perhitungan Tunjangan Hari Raya (THR) Keagamaan mengacu pada ketentuan peraturan perundang-undangan yang berlaku, dengan rincian sebagai berikut:</p>
        <ol>
            <li>Karyawan dengan masa kerja 12 (dua belas) bulan atau lebih secara terus-menerus berhak menerima THR sebesar 1 (satu) bulan upah tetap.</li>
            <li>Karyawan dengan masa kerja kurang dari 12 (dua belas) bulan berhak menerima THR secara proporsional (pro rata) dengan perhitungan:
                <div class="formula-box">
                    (Masa Kerja / 12) x 1 (satu) bulan upah tetap
                </div>
            </li>
            <li>Upah tetap yang menjadi dasar perhitungan THR terdiri atas gaji pokok dan tunjangan tetap sesuai ketentuan yang berlaku di lingkungan institusi.</li>
        </ol>
    </div>

    <!-- Tanda Tangan SDM -->
    <div class="sign-wrapper">
        <table class="sign-table">
            <tr>
                <td>{{ $kotaSurat }}, {{ $tglSuratFormat }}</td>
            </tr>
            <tr>
                <td><strong>{{ $penandatanganJabatan }},</strong></td>
            </tr>
            <tr>
                <td class="sign-img-box" style="position: relative; height: 65px;">
                    @if($stempelImgSrc)
                        <img src="{{ $stempelImgSrc }}" style="position: absolute; left: 0px; top: 0px; height: 60px; opacity: 0.85;" alt="Stempel">
                    @endif
                    @if($ttdImgSrc)
                        <img src="{{ $ttdImgSrc }}" style="position: relative; height: 55px; z-index: 2;" alt="TTD">
                    @else
                        <div style="height: 55px;"></div>
                    @endif
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
