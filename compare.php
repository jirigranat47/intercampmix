<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\OriginalGroup;

$file = base_path('20250524_Groups per SC_Intercamp_V5.xlsx');
$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
$h = array_map('trim', $rows[0]);

$colCountry = array_search('Country', $h);
$colChildren = array_search('Number of Children', $h);
$colSubcamp = array_search('Subcamp', $h);
$colTroop = array_search('Troop name', $h);
$colOrder = array_search('Order Number', $h);

$excelTroops = [];
foreach($rows as $i => $row) {
    if ($i === 0) continue;
    $rawSC = trim($row[$colSubcamp] ?? '');
    if (strpos($rawSC, '1') !== false) {
        $excelTroops[] = trim($row[$colTroop] ?? '');
    }
}

$dbTroops = OriginalGroup::where('subcamp', 1)->pluck('troop_name')->toArray();

echo "Missing in DB:\n";
foreach($excelTroops as $et) {
    if(!in_array($et, $dbTroops)) {
        echo "- $et\n";
    }
}
