<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Participant;
use App\Models\OriginalGroup;

$sc = 1;

$leadersInBuckets = Participant::whereNotNull('target_group')
    ->whereHas('originalGroup', function($q) use ($sc) { $q->where('subcamp', $sc);})
    ->where('is_leader', true)
    ->count();

$incomingLeaders = OriginalGroup::where('subcamp', $sc)->sum('number_of_leaders');
$totalGroups = Participant::whereNotNull('target_group')
    ->whereHas('originalGroup', function($q) use ($sc) { $q->where('subcamp', $sc);})
    ->distinct()->count('target_group');

echo "Incoming SC$sc Leaders: $incomingLeaders\n";
echo "SC$sc Leaders in Buckets: $leadersInBuckets\n";
echo "Total SC$sc Target Groups: $totalGroups\n";
