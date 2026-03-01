<?php
require __DIR__ . '/vendor/autoload.php';

$file = __DIR__ . '/20250524_Groups per SC_Intercamp_V5.xlsx';

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // Tisk prvních 5 řádků pro pochopení struktury
    print_r(array_slice($rows, 0, 5));
} catch (Exception $e) {
    echo "Error reading file: " . $e->getMessage() . "\n";
}
