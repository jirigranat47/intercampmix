<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Participant;
$list = Participant::whereNotNull('target_group')->whereHas('originalGroup', function($q){$q->where('subcamp',1);})->select('target_group')->distinct()->pluck('target_group')->toArray();
var_dump($list);
