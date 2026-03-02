<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OriginalGroup;
$groups = OriginalGroup::where('subcamp', 1)->get();
echo "Total: " . $groups->count() . "\n";
foreach($groups as $g) {
    echo "- {$g->order_number}: {$g->troop_name} (Kids: {$g->number_of_children})\n";
}
