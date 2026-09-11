<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/2026_kpi_tsu.ods';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$reader = IOFactory::createReaderForFile($file);
$sheetNames = $reader->listWorksheetNames($file);

echo "Sheet Names:\n";
foreach ($sheetNames as $idx => $name) {
    echo ($idx + 1) . ". $name\n";
}

$spreadsheet = $reader->load($file);

foreach ($sheetNames as $name) {
    echo "\n=======================================================\n";
    echo "SHEET: $name\n";
    echo "=======================================================\n";
    $sheet = $spreadsheet->getSheetByName($name);
    $highestRow = min($sheet->getHighestRow(), 30);
    $highestColumn = $sheet->getHighestColumn();
    
    for ($r = 1; $r <= $highestRow; $r++) {
        $rowVals = [];
        for ($col = 'A'; $col <= min($highestColumn, 'M'); $col++) {
            $val = $sheet->getCell($col . $r)->getFormattedValue();
            if ($val !== null && trim($val) !== '') {
                $rowVals[$col] = trim($val);
            }
        }
        if (!empty($rowVals)) {
            echo "Row $r: " . json_encode($rowVals, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}
