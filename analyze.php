<?php
use App\Models\Participant;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel if possible, or just query DB if we have access.
// Since this is likely running in a Laravel environment:
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- ANALÝZA NÁRODNOSTÍ V CÍLOVÝCH SKUPINÁCH ---\n";

$groups = Participant::whereNotNull('target_group')
    ->select('target_group', 'country', DB::raw('count(*) as count'))
    ->groupBy('target_group', 'country')
    ->orderBy('target_group')
    ->get();

$currentGroup = null;
foreach ($groups as $row) {
    if ($currentGroup !== $row->target_group) {
        if ($currentGroup !== null) echo "\n";
        $currentGroup = $row->target_group;
        echo "Skupina {$currentGroup}: ";
    }
    echo "{$row->country}: {$row->count}, ";
}
echo "\n\n";

echo "--- ANALÝZA DUPLICIT PŮVODNÍCH SKUPIN (MAX 3 POVOLENO) ---\n";
$og_conflicts = Participant::whereNotNull('target_group')
    ->select('target_group', 'original_group_id', DB::raw('count(*) as count'))
    ->groupBy('target_group', 'original_group_id')
    ->having('count', '>', 3)
    ->get();

if ($og_conflicts->isEmpty()) {
    echo "Všechny skupiny splňují limit max 3 dětí ze stejné původní skupiny.\n";
} else {
    foreach ($og_conflicts as $conflict) {
        echo "POZOR: Skupina {$conflict->target_group} má {$conflict->count} dětí z původní skupiny {$conflict->original_group_id}!\n";
    }
}
