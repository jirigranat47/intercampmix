<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Participant;
use App\Models\OriginalGroup;

$kidsInBuckets = Participant::whereNotNull('target_group')
    ->whereHas('originalGroup', function($q){ $q->where('subcamp', 1);})
    ->where('is_leader', false)
    ->count();

$incomingKids = OriginalGroup::where('subcamp', 1)->sum('number_of_children');

echo "Incoming SC1 Kids: $incomingKids\n";
echo "SC1 Kids in Buckets: $kidsInBuckets\n";
