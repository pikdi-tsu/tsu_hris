<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SLIP PRESENSI - {{ strtoupper($karyawanName) }}</title>
    <style>
        @page {
            margin: 20px 25px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #333333;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #2e7d32;
        }
        .header h3 {
            margin: 0;
            font-size: 12pt;
            color: #1b5e20;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h4 {
            margin: 3px 0 0 0;
            font-size: 9.5pt;
            color: #555555;
            text-transform: uppercase;
        }
        .table-info {
            width: 100%;
            margin-bottom: 8px;
            font-size: 8pt;
        }
        .table-info td {
            padding: 2px 4px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .table-data th {
            background-color: #e8f5e9;
            color: #1b5e20;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
            border: 0.5px solid #a5d6a7;
            font-size: 7.5pt;
        }
        .table-data td {
            padding: 3px 3px;
            border: 0.5px solid #e0e0e0;
            font-size: 7pt;
        }
        .table-data tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer-total {
            background-color: #c8e6c9 !important;
            font-weight: bold;
            color: #1b5e20;
        }
        .badge-valid {
            color: #2e7d32;
            font-weight: bold;
        }
        .badge-invalid {
            color: #c62828;
        }
        .badge-cuti {
            color: #0d47a1;
            font-weight: bold;
        }
        .badge-izin {
            color: #4a148c;
            font-weight: bold;
        }
        .badge-libur {
            color: #616161;
        }
    </style>
</head>
<body>

    <div class="header">
        <h3>PRESENSI {{ strtoupper($periodeTitle) }}</h3>
        <h4>TIGA SERANGKAI UNIVERSITY</h4>
    </div>

    <table class="table-info">
        <tr>
            <td width="15%" class="font-bold">NAMA LENGKAP</td>
            <td width="35%">: {{ strtoupper($karyawanName) }}</td>
            <td width="15%" class="font-bold">PIN ABSENSI</td>
            <td width="35%">: {{ $pin }}</td>
        </tr>
        <tr>
            <td class="font-bold">UNIT KERJA</td>
            <td>: {{ $unitName ?? '-' }}</td>
            <td class="font-bold">PERIODE CUT-OFF</td>
            <td>: {{ $periodeText }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="18%">HARI & TANGGAL</th>
                <th width="8%">SCAN 1</th>
                <th width="8%">SCAN 2</th>
                <th width="8%">SCAN 3</th>
                <th width="8%">SCAN 4</th>
                <th width="10%">DURASI</th>
                <th width="24%">STATUS / KETERANGAN</th>
                <th width="12%">VALIDASI</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalValid = 0;
            @endphp
            @if (!empty($logs))
                @foreach ($logs as $idx => $row)
                    @php
                        $validVal = floatval($row['akumulasi'] ?? 0);
                        $totalValid += $validVal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $row['tanggal_formatted'] }}</strong></td>
                        <td class="text-center">{{ $row['scan_1'] }}</td>
                        <td class="text-center">{{ $row['scan_2'] }}</td>
                        <td class="text-center">{{ $row['scan_3'] }}</td>
                        <td class="text-center">{{ $row['scan_4'] }}</td>
                        <td class="text-center font-bold">{{ $row['durasi'] }}</td>
                        <td>{{ $row['status_label'] }} - <small>{{ $row['keterangan'] }}</small></td>
                        <td class="text-center font-bold {{ $validVal > 0 ? 'badge-valid' : ($row['status_type'] == 'ALPHA' ? 'badge-invalid' : '') }}">
                            {{ number_format($validVal, 1) }}
                        </td>
                    </tr>
                @endforeach
            @elseif (!empty($records))
                @foreach ($records as $idx => $row)
                    @php
                        $validVal = floatval($row->akumulasi_validasi ?? 0);
                        $totalValid += $validVal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal_absen)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $row->scan_1 ?? '-' }}</td>
                        <td class="text-center">{{ $row->scan_2 ?? '-' }}</td>
                        <td class="text-center">{{ $row->scan_3 ?? '-' }}</td>
                        <td class="text-center">{{ $row->scan_4 ?? '-' }}</td>
                        <td class="text-center font-bold">{{ $row->durasi_kerja ?? '-' }}</td>
                        <td>{{ $row->keterangan_validasi ?? ($validVal > 0 ? 'Hadir (Valid)' : 'Kurang Durasi') }}</td>
                        <td class="text-center font-bold {{ $validVal > 0 ? 'badge-valid' : 'badge-invalid' }}">
                            {{ number_format($validVal, 1) }}
                        </td>
                    </tr>
                @endforeach
            @endif
            <tr class="footer-total">
                <td colspan="8" class="text-right font-bold" style="padding-right: 15px;">TOTAL KEHADIRAN VALID</td>
                <td class="text-center font-bold" style="font-size: 8pt;">{{ number_format($totalValid, 1) }} Hari</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 25px; font-size: 7.5pt;">
        <tr>
            <td width="70%"></td>
            <td width="30%" class="text-center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Bagian SDM / HRD<br><br><br><br>
                <strong>( ________________________ )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
