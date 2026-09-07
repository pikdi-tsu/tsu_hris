<?php

namespace App\Exports;

use App\Models\PayrollPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapHonorariumExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected PayrollPeriod $period;

    public function __construct(PayrollPeriod $period)
    {
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Rekap Honorarium Dosen';
    }

    public function collection()
    {
        $honorariums = $this->period->honorariums()->get();

        return $honorariums->map(function ($row, $idx) {
            $tot8b = floatval($row->total_bimbingan_ta) + floatval($row->total_penguji_ta) + floatval($row->total_kerja_praktek);
            $tot8c = floatval($row->total_honor_soal) + floatval($row->total_honor_koreksi);

            return [
                'no'                    => $idx + 1,
                'nik_nip'               => $row->nik_nip ? "'".$row->nik_nip : '-',
                'nama_dosen'            => $row->nama_dosen,
                'nama_unit'             => $row->nama_unit ?: '-',
                'jafung'                => ($row->kode_jafung ?: 'TP') . ($row->nama_jafung ? ' - ' . $row->nama_jafung : ''),
                'sks_struktural'        => floatval($row->sks_struktural),
                'sks_mengajar'          => floatval($row->sks_mengajar),
                'total_sks'             => floatval($row->total_sks),
                'sks_lebih'             => floatval($row->sks_lebih),
                'tarif_sks'             => floatval($row->tarif_sks),
                'jumlah_pertemuan'      => intval($row->jumlah_pertemuan),
                'total_honor_sks'       => floatval($row->total_honor_sks),
                'jml_bimbingan_ta'      => intval($row->jml_bimbingan_ta),
                'total_bimbingan_ta'    => floatval($row->total_bimbingan_ta),
                'jml_penguji_ta'        => intval($row->jml_penguji_ta),
                'total_penguji_ta'      => floatval($row->total_penguji_ta),
                'jml_kerja_praktek'     => intval($row->jml_kerja_praktek),
                'total_kerja_praktek'   => floatval($row->total_kerja_praktek),
                'total_8b'              => $tot8b,
                'total_kelas_soal'      => intval($row->total_kelas_soal),
                'total_honor_soal'      => floatval($row->total_honor_soal),
                'total_peserta_koreksi' => intval($row->total_peserta_koreksi),
                'total_honor_koreksi'   => floatval($row->total_honor_koreksi),
                'total_8c'              => $tot8c,
                'total_honor_kotor'     => floatval($row->total_honor_kotor),
                'potongan_pajak'        => floatval($row->potongan_pajak),
                'potongan_lainnya'      => floatval($row->potongan_lainnya),
                'keterangan_potongan'   => $row->keterangan_potongan ?: '-',
                'total_potongan'        => floatval($row->total_potongan),
                'total_transfer'        => floatval($row->total_transfer),
                'rekening_bank'         => $row->rekening_bank ?: 'BSI',
                'nomor_rekening'        => $row->nomor_rekening ? "'".$row->nomor_rekening : '-',
                'nama_rekening'         => $row->nama_rekening ?: $row->nama_dosen,
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['TIGA SERANGKAI UNIVERSITY'],
            ['REKAPITULASI HONORARIUM DOSEN & TENAGA PENGAJAR - ' . strtoupper($this->period->nama_periode)],
            ['Tahun Akademik: ' . ($this->period->tahun_akademik ?? '-') . ' (' . ($this->period->semester ?? '-') . ') | Periode Cut-Off: ' . $this->period->cutoff_label],
            [],
            [
                'NO',
                'NIP / NIDN',
                'NAMA DOSEN',
                'UNIT / PRODI',
                'JABATAN FUNGSIONAL',
                'SKS STRUKTURAL',
                'SKS MENGAJAR',
                'TOTAL SKS',
                'SKS LEBIH',
                'TARIF SKS (RP)',
                'PERTEMUAN',
                'TOTAL HONOR 8A (RP)',
                'BIMBINGAN TA (MHS)',
                'HONOR BIMBINGAN (RP)',
                'PENGUJI TA (MHS)',
                'HONOR PENGUJI (RP)',
                'KERJA PRAKTEK (MHS)',
                'HONOR KP (RP)',
                'TOTAL HONOR 8B (RP)',
                'KELAS SOAL (KLS)',
                'HONOR BUAT SOAL (RP)',
                'PESERTA KOREKSI (MHS)',
                'HONOR KOREKSI (RP)',
                'TOTAL HONOR 8C (RP)',
                'TOTAL HONOR KOTOR (RP)',
                'POTONGAN PAJAK (RP)',
                'POTONGAN LAINNYA (RP)',
                'KETERANGAN POTONGAN',
                'TOTAL POTONGAN (RP)',
                'TOTAL TRANSFER BERSIH (RP)',
                'BANK',
                'NO REKENING',
                'ATAS NAMA REKENING',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => '#,##0',
            'L' => '#,##0',
            'N' => '#,##0',
            'P' => '#,##0',
            'R' => '#,##0',
            'S' => '#,##0',
            'U' => '#,##0',
            'W' => '#,##0',
            'X' => '#,##0',
            'Y' => '#,##0',
            'Z' => '#,##0',
            'AA' => '#,##0',
            'AC' => '#,##0',
            'AD' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:AG1');
        $sheet->mergeCells('A2:AG2');
        $sheet->mergeCells('A3:AG3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        $headerRow = 5;
        $sheet->getStyle("A{$headerRow}:AG{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'], // Navy Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= $headerRow) {
            $sheet->getStyle("A{$headerRow}:AG{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD1D5DB'],
                    ],
                ],
            ]);

            // Alignment data rows
            $dataStart = $headerRow + 1;
            if ($highestRow >= $dataStart) {
                $sheet->getStyle("A{$dataStart}:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$dataStart}:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$dataStart}:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$dataStart}:K{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M{$dataStart}:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("O{$dataStart}:O{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("Q{$dataStart}:Q{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("T{$dataStart}:T{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("V{$dataStart}:V{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("AE{$dataStart}:AE{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("AF{$dataStart}:AF{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Highlight Total Transfer column in bold green
                $sheet->getStyle("AD{$dataStart}:AD{$highestRow}")->getFont()->setBold(true);
            }
        }

        return [];
    }
}
