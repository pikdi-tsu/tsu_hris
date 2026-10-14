<?php

namespace App\Exports;

use App\Models\PayrollLemburPeriod;
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

class PayrollLemburExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    protected PayrollLemburPeriod $period;

    public function __construct(PayrollLemburPeriod $period)
    {
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Rekap Lembur ' . $this->period->periode_bulan_tahun;
    }

    public function collection()
    {
        $karyawans = $this->period->karyawans()->get();

        return $karyawans->map(function ($row, $idx) {
            return [
                'no'               => $idx + 1,
                'nama'             => $row->nama,
                'nik'              => $row->nik ?: '-',
                'tipe_karyawan'    => $row->tipe_karyawan,
                'posisi'           => $row->posisi ?: '-',
                'nama_unit'        => $row->nama_unit ?: '-',
                'gaji_tetap'       => floatval($row->gaji_tetap),
                'upah_per_jam'     => floatval($row->upah_per_jam),
                'total_hari'       => intval($row->total_hari_lembur),
                'total_jam'        => floatval($row->total_jam_lembur),
                'jam_kerja_biasa'  => floatval($row->jam_kerja_biasa),
                'jam_hari_libur'   => floatval($row->jam_hari_libur),
                'nominal_sistem'   => floatval($row->nominal_lembur_sistem),
                'penyesuaian'      => floatval($row->penyesuaian),
                'total_lembur'     => floatval($row->total_lembur_final),
                'nama_bank'        => $row->nama_bank ?: 'Bank Mandiri',
                'no_rekening'      => $row->no_rekening ? '`' . $row->no_rekening : '-',
                'catatan'          => $row->catatan_koreksi ?: '-',
            ];
        });
    }

    public function headings(): array
    {
        $cutoffStr = $this->period->start_date_cutoff->translatedFormat('d F Y') . ' s/d ' . $this->period->end_date_cutoff->translatedFormat('d F Y');

        return [
            ['TIGA SERANGKAI UNIVERSITY'],
            ['REKAPITULASI PEMBAYARAN UPAH LEMBUR KARYAWAN & TENDIK'],
            ['Periode: ' . strtoupper($this->period->nama_periode) . ' (Cut-Off: ' . $cutoffStr . ')'],
            [],
            [
                'NO',
                'NAMA LENGKAP',
                'NIK',
                'TIPE',
                'JABATAN / POSISI',
                'UNIT KERJA',
                'GAJI TETAP (RP)',
                'UPAH / JAM (RP)',
                'TOTAL HARI',
                'TOTAL JAM',
                'JAM KERJA',
                'JAM LIBUR',
                'LEMBUR SISTEM (RP)',
                'PENYESUAIAN (RP)',
                'TOTAL LEMBUR (RP)',
                'BANK',
                'NO REKENING',
                'CATATAN'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'J' => '#,##0.00',
            'K' => '#,##0.00',
            'L' => '#,##0.00',
            'M' => '#,##0',
            'N' => '#,##0',
            'O' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $this->period->karyawans()->count();
        $lastRow = 5 + $rowCount;

        // Title styling
        $sheet->mergeCells('A1:R1');
        $sheet->mergeCells('A2:R2');
        $sheet->mergeCells('A3:R3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header table styling
        $sheet->getStyle('A5:R5')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '094B54'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        if ($rowCount > 0) {
            // Borders
            $sheet->getStyle("A5:R{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);

            // Alignment
            $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C6:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I6:L{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Total summary row
            $totalRow = $lastRow + 1;
            $sheet->setCellValue("A{$totalRow}", 'TOTAL');
            $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
            $sheet->setCellValue("I{$totalRow}", "=SUM(I6:I{$lastRow})");
            $sheet->setCellValue("J{$totalRow}", "=SUM(J6:J{$lastRow})");
            $sheet->setCellValue("M{$totalRow}", "=SUM(M6:M{$lastRow})");
            $sheet->setCellValue("N{$totalRow}", "=SUM(N6:N{$lastRow})");
            $sheet->setCellValue("O{$totalRow}", "=SUM(O6:O{$lastRow})");

            $sheet->getStyle("A{$totalRow}:R{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '94A3B8'],
                    ],
                ],
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }
}
