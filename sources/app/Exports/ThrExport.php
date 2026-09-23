<?php

namespace App\Exports;

use App\Models\ThrPeriod;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThrExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected ThrPeriod $period;

    public function __construct(ThrPeriod $period)
    {
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Rekap THR ' . $this->period->tahun;
    }

    public function collection()
    {
        $karyawans = $this->period->karyawans()->get();

        return $karyawans->map(function ($row, $idx) {
            $tglAwal = '-';
            if ($row->tgl_awal_kerja) {
                $carbon = Carbon::parse($row->tgl_awal_kerja)->locale('id');
                $tglAwal = $carbon->isoFormat('D MMMM Y');
            }

            return [
                'no'               => $idx + 1,
                'nama'             => $row->nama,
                'tgl_awal_kerja'   => $tglAwal,
                'upah_tetap'       => floatval($row->upah_tetap),
                'masa_kerja_bulan' => intval($row->masa_kerja_bulan),
                'status'           => $row->status_thr,
                'thr'              => floatval($row->total_thr),
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['TIGA SERANGKAI UNIVERSITY'],
            ['REKAPITULASI TUNJANGAN HARI RAYA (THR)'],
            ['PERIODE: ' . strtoupper($this->period->nama_periode)],
            [],
            [
                'NO',
                'NAMA LENGKAP',
                'TANGGAL AWAL KERJA',
                'UPAH TETAP',
                'MASA KERJA (bulan)',
                'STATUS',
                'THR',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'G' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Header Judul
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');

        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '094B54'],
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ]
        ]);
        $sheet->getStyle('A1')->getFont()->setSize(14);

        // 2. Header Tabel Kolom (Baris 5) - Warna Biru Muda Sesuai Screenshot (#8EAADB / #9FC5E8)
        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8EAADB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // 3. Styling Data Rows (Baris 6 s/d LastRow)
        if ($lastRow >= 6) {
            $sheet->getStyle("A6:G{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D7DE'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment kolom spesifik
            $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C6:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D6:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E6:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F6:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G6:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // 4. Baris Total di paling bawah
            $totalRow = $lastRow + 1;
            $sheet->setCellValue("A{$totalRow}", 'TOTAL');
            $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
            $sheet->setCellValue("D{$totalRow}", "=SUM(D6:D{$lastRow})");
            $sheet->setCellValue("G{$totalRow}", "=SUM(G6:G{$lastRow})");

            $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF'],
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}
