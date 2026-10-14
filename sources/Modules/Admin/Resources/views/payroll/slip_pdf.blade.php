<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SLIP GAJI - {{ strtoupper($row->nama) }} - {{ strtoupper($period->nama_periode) }}</title>
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
            position: relative;
        }
        .header-title {
            text-align: center;
        }
        .header-title h2 {
            margin: 0;
            font-size: 13pt;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .header-title h3 {
            margin: 3px 0 0 0;
            font-size: 10.5pt;
            color: #374151;
            text-transform: uppercase;
        }
        .header-title p {
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
            letter-spacing: 0.5px;
            border: 1px solid #f87171;
            margin-top: 4px;
        }
        .table-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .table-info td {
            padding: 4px 8px;
            vertical-align: top;
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
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #1e3a8a;
        }
        .table-komponen td {
            padding: 4.5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 8pt;
        }
        .table-komponen tr.subhead {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
        }
        .table-komponen tr.total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .box-take-home {
            background-color: #ecfdf5;
            border: 1.5px solid #10b981;
            border-radius: 5px;
            padding: 10px 14px;
            margin-bottom: 25px;
        }
        .box-take-home table {
            width: 100%;
        }
        .box-take-home .thp-label {
            font-size: 10pt;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
        }
        .box-take-home .thp-nominal {
            font-size: 14pt;
            font-weight: bold;
            color: #047857;
            text-align: right;
        }
        .box-take-home .thp-terbilang {
            font-size: 7.5pt;
            color: #047857;
            font-style: italic;
            margin-top: 3px;
        }

        .signatures {
            width: 100%;
            margin-top: 15px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .sign-title {
            color: #64748b;
            font-size: 7.5pt;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-title">
            <h2>TIGA SERANGKAI UNIVERSITY</h2>
            <h3>SLIP PENGGAJIAN PEGAWAI</h3>
            <p>Periode: <strong>{{ strtoupper($period->nama_periode) }}</strong> | Cut-off Presensi: {{ $period->cutoff_label }}</p>
            <div class="badge-confidential">Dokumen Rahasia / Confidential</div>
        </div>
    </div>

    {{-- Info Pegawai --}}
    <table class="table-info">
        <tr>
            <td class="label">NIK / PIN:</td>
            <td class="val">{{ $row->nik ?: '-' }} / {{ $row->pegawai->pin_absensi ?? '-' }}</td>
            <td class="label">Unit Kerja:</td>
            <td class="val">{{ $row->nama_unit ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Lengkap:</td>
            <td class="val">{{ $row->nama }}</td>
            <td class="label">Jabatan Struktural:</td>
            <td class="val">{{ $row->jabatan_struktural ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Golongan / Pangkat:</td>
            <td class="val">{{ $row->golongan_pangkat ?: '-' }} ({{ $row->persen_gapok }}%)</td>
            <td class="label">Jabatan Fungsional:</td>
            <td class="val">{{ $row->jabatan_fungsional ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Rekening Transfer:</td>
            <td class="val">{{ $row->nama_bank ?: 'Bank Mandiri' }} - {{ $row->no_rekening ?: '-' }}</td>
            <td class="label">Atas Nama Rekening:</td>
            <td class="val">{{ $row->atas_nama_rekening ?: $row->nama }}</td>
        </tr>
    </table>

    {{-- Rincian Komponen Penerimaan & Potongan --}}
    <table class="table-komponen">
        <thead>
            <tr>
                <th width="50%">PENERIMAAN (EARNINGS)</th>
                <th width="50%">POTONGAN (DEDUCTIONS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- Kolom Kiri: Penerimaan --}}
                <td style="vertical-align: top; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; width: 65%;">Gaji Pokok ({{ $row->persen_gapok }}%)</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;" class="font-bold">Rp {{ number_format($row->gaji_pokok, 0, ',', '.') }}</td>
                        </tr>
                        @if($row->tunjangan_fungsional > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Tunjangan Fungsional</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->tunjangan_fungsional, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->tunjangan_struktural > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Tunjangan Struktural</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->tunjangan_struktural, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->tunjangan_keluarga > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Tunjangan Keluarga</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->tunjangan_keluarga, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->tunjangan_anak > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Tunjangan Anak</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->tunjangan_anak, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->tunjangan_kesehatan > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Tunjangan Kesehatan (BPJS 4%)</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->tunjangan_kesehatan, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->tunjangan_khusus > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Tunjangan Khusus</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->tunjangan_khusus, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr style="background-color: #f1f5f9;">
                            <td style="border: none; border-bottom: 0.5px solid #cbd5e1;" class="font-bold">Subtotal Gaji Tetap</td>
                            <td style="border: none; border-bottom: 0.5px solid #cbd5e1; text-align: right;" class="font-bold text-primary">Rp {{ number_format($row->gaji_tetap, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0;">Uang Transport ({{ number_format($row->hari_hadir_valid, 0) }} Hari &times; Rp {{ number_format($row->tarif_transport, 0, ',', '.') }})</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right;">Rp {{ number_format($row->total_transport, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>

                {{-- Kolom Kanan: Potongan --}}
                <td style="vertical-align: top; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        @if($row->potongan_unpaid_leave > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; width: 65%;">Unpaid Leave / Izin ({{ number_format($row->hari_unpaid_leave, 0) }} Hari &times; Rate)</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right; color: #dc2626;">Rp {{ number_format($row->potongan_unpaid_leave, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->potongan_bpjs_kes > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; width: 65%;">Iuran BPJS Kesehatan</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right; color: #dc2626;">Rp {{ number_format($row->potongan_bpjs_kes, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->potongan_lainnya > 0)
                        <tr>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; width: 65%;">Potongan Lainnya {{ $row->keterangan_potongan ? '('.$row->keterangan_potongan.')' : '' }}</td>
                            <td style="border: none; border-bottom: 0.5px solid #e2e8f0; text-align: right; color: #dc2626;">Rp {{ number_format($row->potongan_lainnya, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($row->total_potongan == 0)
                        <tr>
                            <td colspan="2" style="border: none; color: #94a3b8; text-align: center; padding: 25px 0; font-style: italic;">
                                Tidak ada potongan pada periode ini.
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td style="font-size: 8.5pt;">
                    <div style="float: left;">TOTAL PENGHASILAN KOTOR:</div>
                    <div style="float: right; color: #1e3a8a;">Rp {{ number_format($row->gaji_kotor, 0, ',', '.') }}</div>
                    <div style="clear: both;"></div>
                </td>
                <td style="font-size: 8.5pt;">
                    <div style="float: left;">TOTAL POTONGAN:</div>
                    <div style="float: right; color: #dc2626;">Rp {{ number_format($row->total_potongan, 0, ',', '.') }}</div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Box Take Home Pay --}}
    <div class="box-take-home">
        <table>
            <tr>
                <td style="border: none; vertical-align: middle;">
                    <div class="thp-label">Gaji Bersih Diterima (Take Home Pay)</div>
                    @php
                        if (!function_exists('penyebut')) {
                            function penyebut($nilai) {
                                $nilai = abs($nilai);
                                $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
                                $temp = "";
                                if ($nilai < 12) {
                                    $temp = " ". $huruf[$nilai];
                                } else if ($nilai < 20) {
                                    $temp = penyebut($nilai - 10). " Belas";
                                } else if ($nilai < 100) {
                                    $temp = penyebut($nilai/10)." Puluh". penyebut($nilai % 10);
                                } else if ($nilai < 200) {
                                    $temp = " Seratus" . penyebut($nilai - 100);
                                } else if ($nilai < 1000) {
                                    $temp = penyebut($nilai/100) . " Ratus" . penyebut($nilai % 100);
                                } else if ($nilai < 2000) {
                                    $temp = " Seribu" . penyebut($nilai - 1000);
                                } else if ($nilai < 1000000) {
                                    $temp = penyebut($nilai/1000) . " Ribu" . penyebut($nilai % 1000);
                                } else if ($nilai < 1000000000) {
                                    $temp = penyebut($nilai/1000000) . " Juta" . penyebut($nilai % 1000000);
                                } else if ($nilai < 1000000000000) {
                                    $temp = penyebut($nilai/1000000000) . " Milyar" . penyebut(fmod($nilai,1000000000));
                                }
                                return $temp;
                            }
                        }
                        $terbilang = trim(penyebut($row->gaji_bersih)) . ' Rupiah';
                    @endphp
                    <div class="thp-terbilang">Terbilang: # {{ $terbilang }} #</div>
                </td>
                <td style="border: none; vertical-align: middle; text-align: right;">
                    <div class="thp-nominal">Rp {{ number_format($row->gaji_bersih, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Tanda Tangan Resmi Pejabat Penandatangan --}}
    @php
        // Lokasi & Tanggal Surat
        $kotaSurat = $period->kota_surat ?: 'Surakarta';
        $tglSuratFormat = $period->tanggal_cetak_slip 
            ? \Carbon\Carbon::parse($period->tanggal_cetak_slip)->translatedFormat('d F Y') 
            : \Carbon\Carbon::now()->translatedFormat('d F Y');

        // Pejabat Penandatangan
        $penandatanganJabatan = $period->penandatangan_jabatan ?: 'Sumber Daya Manusia';
        $penandatanganNama = $period->penandatangan_nama ?: ($ttdMaster->nama_pejabat ?? ($period->approvalKaryawan->nama ?? '-'));

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

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="width: 60%; border: none;"></td>
            <td style="width: 40%; text-align: center; vertical-align: top; border: none; font-size: 8.5pt;">
                <div>{{ $kotaSurat }}, {{ $tglSuratFormat }}</div>
                <div style="font-weight: bold; margin-top: 3px;">{{ $penandatanganJabatan }},</div>
                <div style="height: 65px; margin: 5px 0; position: relative; text-align: center;">
                    @if($stempelImgSrc)
                        <img src="{{ $stempelImgSrc }}" style="position: absolute; left: 10px; top: -5px; height: 65px; opacity: 0.85;" alt="Stempel">
                    @endif
                    @if($ttdImgSrc)
                        <img src="{{ $ttdImgSrc }}" style="position: relative; height: 60px; z-index: 2;" alt="TTD">
                    @endif
                </div>
                <div style="font-weight: bold; text-decoration: underline; color: #0f172a; font-size: 9pt;">{{ $penandatanganNama }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
