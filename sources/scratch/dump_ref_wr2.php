<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/2026_kpi_tsu.ods';
$reader = IOFactory::createReaderForFile($file);
$spreadsheet = $reader->load($file);

$sheetRef = $spreadsheet->getSheetByName('REFERENSI');
echo "=== REFERENSI (ALL NON-EMPTY ROWS) ===\n";
for ($r = 1; $r <= 40; $r++) {
    $row = [];
    for ($c = 1; $c <= 12; $c++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $val = $sheetRef->getCell($colLetter . $r)->getFormattedValue();
        if ($val !== null && trim($val) !== '') {
            $row[$colLetter] = trim($val);
        }
    }
    if (!empty($row)) {
        echo "[$r] " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

$sheetWr2 = $spreadsheet->getSheetByName('INDIKATOR WR 2');
echo "\n=== INDIKATOR WR 2 (SAMPLE) ===\n";
for ($r = 1; $r <= 25; $r++) {
    $row = [];
    for ($c = 1; $c <= 14; $c++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $val = $sheetWr2->getCell($colLetter . $r)->getFormattedValue();
        if ($val !== null && trim($val) !== '') {
            $row[$colLetter] = trim($val);
        }
    }
    if (!empty($row)) {
        echo "[$r] " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
