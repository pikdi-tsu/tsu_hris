<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/2026_kpi_tsu.ods';
$reader = IOFactory::createReaderForFile($file);
$spreadsheet = $reader->load($file);

$sheetCascWr2 = $spreadsheet->getSheetByName('CASCADING WR 2');
echo "=== CASCADING WR 2 (ROWS 1-35) ===\n";
for ($r = 1; $r <= 35; $r++) {
    $row = [];
    for ($c = 1; $c <= 14; $c++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $val = $sheetCascWr2->getCell($colLetter . $r)->getFormattedValue();
        if ($val !== null && trim($val) !== '') {
            $row[$colLetter] = trim($val);
        }
    }
    if (!empty($row)) {
        echo "[$r] " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
