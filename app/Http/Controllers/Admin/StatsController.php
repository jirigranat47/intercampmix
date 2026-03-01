<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Participant;
use App\Models\OriginalGroup;

class StatsController extends Controller
{
    public function index()
    {
        // Počty dětí podle národnosti v jednotlivých subcampech
        // Joinujeme přes original_group_id (order_number)
        $stats = DB::table('participants')
            ->join('original_groups', 'participants.original_group_id', '=', 'original_groups.order_number')
            ->select('original_groups.subcamp', 'participants.country', DB::raw('count(*) as count'))
            ->groupBy('original_groups.subcamp', 'participants.country')
            ->orderBy('original_groups.subcamp')
            ->orderBy('participants.country')
            ->get();

        // Celkové počty za subcampy
        $subcampTotals = DB::table('participants')
            ->join('original_groups', 'participants.original_group_id', '=', 'original_groups.order_number')
            ->select('original_groups.subcamp', DB::raw('count(*) as total'))
            ->groupBy('original_groups.subcamp')
            ->pluck('total', 'subcamp');

        return view('admin.stats', compact('stats', 'subcampTotals'));
    }
}
