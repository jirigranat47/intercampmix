<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\ImportController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

$request = new Request();
$file = new UploadedFile(base_path('20250524_Groups per SC_Intercamp_V5.xlsx'), 'file.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
$request->files->set('excel_file', $file);

$controller = new ImportController();
$controller->process($request);

$count = \App\Models\OriginalGroup::where('subcamp', 1)->count();
echo "Total SC1 Original Groups after fresh import: " . $count . "\n";

$groups = \App\Models\OriginalGroup::where('order_number', 'like', 'IST-11133%')->get();
foreach($groups as $g) {
    echo "- {$g->order_number}: {$g->troop_name}\n";
}
