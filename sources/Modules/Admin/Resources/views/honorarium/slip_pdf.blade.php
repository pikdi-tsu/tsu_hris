<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SLIP HONORARIUM - {{ strtoupper($item->nama_dosen) }}</title>
    <style>
        @page {
            margin: 25px 30px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #2b2b2b;
            line-height: 1.3;
        }
        .header {
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 10.5pt;
            color: #374151;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #6b7280;
        }
        .badge-confidential {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #f87171;
            margin-top: 4px;
        }
        .table-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .table-info td {
            padding: 4px 8px;
            font-size: 8pt;
        }
        .table-info td.label {
            color: #64748b;
            width: 18%;
        }
        .table-info td.val {
            font-weight: bold;
            color: #1e293b;
            width: 32%;
        }
        .table-komponen {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-komponen th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #1e3a8a;
        }
        .table-komponen td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 8pt;
        }
        .table-total {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #ecfdf5;
            border: 2px solid #059669;
        }
        .table-total td {
            padding: 8px 12px;
        }
        .table-ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-ttd td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>TIGA SERANGKAI UNIVERSITY</h2>
        <h3>SLIP PEMBAYARAN HONORARIUM DOSEN</h3>
        <p>Periode: <strong>{{ $item->period->nama_periode }}</strong></p>
        <span class="badge-confidential">RAHASIA / CONFIDENTIAL</span>
    </div>

    <table class="table-info">
        <tr>
            <td class="label">Nama Dosen</td>
            <td class="val">: {{ $item->nama_dosen }}</td>
            <td class="label">Kode Periode</td>
            <td class="val">: {{ $item->period->kode_periode }}</td>
        </tr>
        <tr>
            <td class="label">NIP / NIDN</td>
            <td class="val">: {{ $item->nik_nip ?? '-' }}</td>
            <td class="label">Tipe</td>
            <td class="val">: Honorarium Dosen Terpadu</td>
        </tr>
        <tr>
            <td class="label">Fakultas / Unit</td>
            <td class="val">: {{ $item->nama_unit ?? '-' }}</td>
            <td class="label">Jabatan Fungsional</td>
            <td class="val">: {{ $item->kode_jafung ?? 'TP' }} ({{ $item->nama_jafung ?? 'Tenaga Pengajar' }})</td>
        </tr>
        <tr>
            <td class="label">Rekening Bank</td>
            <td class="val">: {{ $item->rekening_bank ?? 'BSI' }} - {{ $item->nomor_rekening ?? '-' }}</td>
            <td class="label">Tahun Akademik</td>
            <td class="val">: {{ $item->period->tahun_akademik ?? '-' }} ({{ $item->period->semester ?? '-' }}) {{ $item->period->bulan_honor ? '• Bulan: ' . $item->period->bulan_honor : '' }}</td>
        </tr>
    </table>

    {{-- Tabel Komponen Honor --}}
    <table class="table-komponen">
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">NO</th>
                <th width="50%">DESKRIPSI KOMPONEN HONORARIUM</th>
                <th width="20%" style="text-align: center;">VOLUME / SKS / MHS</th>
                <th width="25%" style="text-align: right;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp

            {{-- 8A: Kelebihan SKS --}}
            @if($item->total_honor_sks > 0 || $item->sks_lebih > 0)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>
                        <strong>8A. Honor Kelebihan Jam Mengajar (KJM) / SKS Lebih</strong><br>
                        <small style="color: #64748b;">(Struktural: {{ $item->sks_struktural }} SKS, Mengajar: {{ $item->sks_mengajar }} SKS, Tarif: Rp {{ number_format($item->tarif_sks, 0, ',', '.') }} × {{ $item->jumlah_pertemuan }} Pertemuan)</small>
                    </td>
                    <td style="text-align: center;">{{ $item->sks_lebih }} SKS Lebih</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->total_honor_sks, 0, ',', '.') }}</td>
                </tr>
            @endif

            {{-- 8B: Bimbingan & Penguji TA / KP --}}
            @if($item->jml_bimbingan_ta > 0)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>8B. Bimbingan Tugas Akhir / Skripsi (Rp {{ number_format($item->tarif_bimbingan_ta, 0, ',', '.') }}/mhs)</td>
                    <td style="text-align: center;">{{ $item->jml_bimbingan_ta }} Mahasiswa</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->total_bimbingan_ta, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($item->jml_penguji_ta > 0)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>8B. Penguji Sidang Tugas Akhir / Skripsi (Rp {{ number_format($item->tarif_penguji_ta, 0, ',', '.') }}/mhs)</td>
                    <td style="text-align: center;">{{ $item->jml_penguji_ta }} Mahasiswa</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->total_penguji_ta, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($item->jml_kerja_praktek > 0)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>8B. Seminar / Bimbingan Kerja Praktek (KP) (Rp {{ number_format($item->tarif_kerja_praktek, 0, ',', '.') }}/mhs)</td>
                    <td style="text-align: center;">{{ $item->jml_kerja_praktek }} Mahasiswa</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->total_kerja_praktek, 0, ',', '.') }}</td>
                </tr>
            @endif

            {{-- 8C: Ujian (UTS & UAS) --}}
            @if($item->total_kelas_soal > 0)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>8C. Pembuatan Naskah Soal Ujian (UTS/UAS) - MK: {{ $item->mata_kuliah ?: '-' }} ({{ $item->tipe_kelas ?: 'T' }})</td>
                    <td style="text-align: center;">{{ $item->total_kelas_soal }} Kelas</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->total_honor_soal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($item->total_peserta_koreksi > 0)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>8C. Koreksi Lembar Jawaban Ujian (UTS/UAS)</td>
                    <td style="text-align: center;">{{ $item->total_peserta_koreksi }} Mahasiswa</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->total_honor_koreksi, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($no == 1)
                <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">Tidak ada komponen honorarium terhitung.</td>
                </tr>
            @endif

            <tr style="background-color: #f8fafc;">
                <td colspan="3" style="font-weight: bold; text-align: right;">TOTAL HONORARIUM KOTOR:</td>
                <td style="text-align: right; font-weight: bold; color: #1e3a8a;">Rp {{ number_format($item->total_honor_kotor, 0, ',', '.') }}</td>
            </tr>

            {{-- Potongan --}}
            @if($item->total_potongan > 0)
                <tr>
                    <td style="text-align: center;">-</td>
                    <td colspan="2" style="color: #b91c1c;">Potongan Pajak / Potongan Lainnya {{ $item->keterangan_potongan ? '(' . $item->keterangan_potongan . ')' : '' }}</td>
                    <td style="text-align: right; font-weight: bold; color: #b91c1c;">- Rp {{ number_format($item->total_potongan, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="table-total">
        <tr>
            <td width="60%">
                <span style="font-size: 8pt; color: #065f46; font-weight: bold; text-transform: uppercase;">TOTAL HONORARIUM DITRANSFER (TAKE HOME PAY)</span><br>
                <small style="color: #047857;">Ditransfer ke Rekening: {{ $item->rekening_bank ?? 'BSI' }} {{ $item->nomor_rekening ?? '-' }} a.n {{ $item->nama_rekening ?? $item->nama_dosen }}</small>
            </td>
            <td width="40%" style="text-align: right;">
                <span style="font-size: 13pt; font-weight: bold; color: #047857;">Rp {{ number_format($item->total_transfer, 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    {{-- Tanda Tangan --}}
    <table class="table-ttd">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Validator 1</strong>
                <br><br><br><br>
                <u>{{ $item->period->validator1->nama ?? '..........................' }}</u>
            </td>
            <td>
                Menyetujui,<br>
                <strong>Validator 2</strong>
                <br><br><br><br>
                <u>{{ $item->period->validator2->nama ?? '..........................' }}</u>
            </td>
            <td>
                Mengetahui & Mengesahkan,<br>
                <strong>Approval Paling Atas</strong>
                <br><br><br><br>
                <u>{{ $item->period->approvalKaryawan->nama ?? '..........................' }}</u>
            </td>
        </tr>
    </table>
</body>
</html>
