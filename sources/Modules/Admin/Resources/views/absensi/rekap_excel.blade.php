<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 14pt;">PRESENSI KEHADIRAN KARYAWAN</th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 12pt;">TIGA SERANGKAI UNIVERSITY</th>
        </tr>
        <tr>
            <th colspan="5"></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold;">Periode Cut-off Presensi</th>
            <th colspan="3" style="font-weight: bold;">: {{ $periodeText }}</th>
        </tr>
        <tr style="background-color: #d9ead3;">
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 8px;">NO</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 35px;">NAMA</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 18px;">ABSEN MASUK</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 18px;">NOMINAL</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 22px;">JUMLAH</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalKehadiran = 0;
            $totalUang = 0;
        @endphp
        @foreach ($data as $index => $row)
            @php
                $kehadiran = $row->total_hadir ?? 0;
                $isDapatTransport = !($row->users && $row->users->dapat_uang_transport === false);
                $rowNominal = $isDapatTransport ? $nominal : 0;
                $jumlah = $kehadiran * $rowNominal;
                $totalKehadiran += $kehadiran;
                $totalUang += $jumlah;
            @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000000;">{{ strtoupper($row->nama) }}</td>
                <td style="text-align: center; border: 1px solid #000000;">{{ $kehadiran }}</td>
                <td style="text-align: right; border: 1px solid #000000;">{{ number_format($rowNominal, 0, ',', '.') }}</td>
                <td style="text-align: right; border: 1px solid #000000;">{{ number_format($jumlah, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr style="background-color: #f3f3f3;">
            <th colspan="2" style="text-align: center; font-weight: bold; border: 1px solid #000000;">TOTAL</th>
            <th style="text-align: center; font-weight: bold; border: 1px solid #000000;">{{ $totalKehadiran }}</th>
            <th style="border: 1px solid #000000;"></th>
            <th style="text-align: right; font-weight: bold; border: 1px solid #000000;">Rp {{ number_format($totalUang, 0, ',', '.') }}</th>
        </tr>
    </tbody>
</table>
