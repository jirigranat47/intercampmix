<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = base_path('20250524_Groups per SC_Intercamp_V5.xlsx');
if(!file_exists($file)) {
	echo "File not found locally.\n";
	exit;
}
$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
$h = array_map('trim', $rows[0]);

$colCountry = array_search('Country', $h);
$colChildren = array_search('Number of Children', $h);
$colSubcamp = array_search('Subcamp', $h);
$colTroop = array_search('Troop name', $h);
$colOrder = array_search('Order Number', $h);

$sc1count = 0;
foreach($rows as $i => $row) {
    if ($i === 0) continue;
    $rawSC = trim($row[$colSubcamp] ?? '');
    if (strpos($rawSC, '1') !== false) {
        $sc1count++;
        echo "- Found SC1 row: " . ($row[$colOrder]??'') . " | " . ($row[$colTroop]??'') . "\n";
    }
}
echo "Total raw SC1 rows: $sc1count\n";
