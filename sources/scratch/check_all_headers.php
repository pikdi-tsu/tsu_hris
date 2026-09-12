<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/2026_kpi_tsu.ods';
$reader = IOFactory::createReaderForFile($file);
$spreadsheet = $reader->load($file);

foreach ($spreadsheet->getSheetNames() as $sheetName) {
    echo "=======================================================\n";
    echo "SHEET: $sheetName\n";
    echo "=======================================================\n";
    $sheet = $spreadsheet->getSheetByName($sheetName);
    
    // Check rows 1 to 5 to find the header row
    for ($r = 1; $r <= 5; $r++) {
        $headers = [];
        $highestCol = $sheet->getHighestColumn();
        $highestColIdx = min(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol), 20);
        for ($c = 1; $c <= $highestColIdx; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $val = $sheet->getCell($colLetter . $r)->getFormattedValue();
            if ($val !== null && trim($val) !== '') {
                $headers[$colLetter] = trim($val);
            }
        }
        if (count($headers) > 3) {
            echo "Header Row $r: " . json_encode($headers, JSON_UNESCAPED_UNICODE) . "\n";
            break;
        }
    }
}
