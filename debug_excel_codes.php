<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = base_path('20250524_Groups per SC_Intercamp_V5.xlsx');
$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
$h = array_map('trim', $rows[0]);

echo "Header: " . implode(', ', $h) . "\n";

$colOrder = array_search('Order Number', $h);
$colTroop = array_search('Troop name', $h);
$colSubcamp = array_search('Subcamp', $h);

echo "Row details for SC1:\n";
foreach($rows as $i => $row) {
    if ($i === 0) continue;
    $sc = trim($row[$colSubcamp] ?? '');
    if (strpos($sc, '1') !== false) {
        echo "Row $i: Order=[" . ($row[$colOrder]??'') . "], Troop=[" . ($row[$colTroop]??'') . "]\n";
    }
}
