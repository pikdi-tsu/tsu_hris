<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/2026_kpi_tsu.ods';
$reader = IOFactory::createReaderForFile($file);
$spreadsheet = $reader->load($file);

function dumpSheetDetail($sheet, $maxRows = 35) {
    $highestRow = min($sheet->getHighestRow(), $maxRows);
    $highestCol = $sheet->getHighestColumn();
    $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
    $highestColIdx = min($highestColIdx, 15);
    
    for ($r = 1; $r <= $highestRow; $r++) {
        $row = [];
        for ($c = 1; $c <= $highestColIdx; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $val = $sheet->getCell($colLetter . $r)->getFormattedValue();
            if ($val !== null && trim($val) !== '') {
                $row[$colLetter] = trim($val);
            }
        }
        if (!empty($row)) {
            echo "  [$r] " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}

echo "=== DETAIL SHEET REFERENSI ===\n";
dumpSheetDetail($spreadsheet->getSheetByName('REFERENSI'), 25);

echo "\n=== DETAIL SHEET INDIKATOR WR 1 ===\n";
dumpSheetDetail($spreadsheet->getSheetByName('INDIKATOR WR 1'), 15);

echo "\n=== DETAIL SHEET CASCADING WR 1 ===\n";
dumpSheetDetail($spreadsheet->getSheetByName('CASCADING WR 1'), 15);

echo "\n=== DETAIL SHEET INDIKATOR SEKRETARIAT ===\n";
dumpSheetDetail($spreadsheet->getSheetByName('INDIKATOR SEKRETARIAT'), 20);

echo "\n=== DETAIL SHEET CASCADING SEKRETARIAT ===\n";
dumpSheetDetail($spreadsheet->getSheetByName('CASCADING SEKRETARIAT'), 20);
