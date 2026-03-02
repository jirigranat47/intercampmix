<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\OriginalGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupsOverviewController extends Controller
{
    public function index(Request $request)
    {
        $selectedSubcamp = $request->get('subcamp');

        // Get all unique subcamps for filter
        $allSubcamps = OriginalGroup::distinct()->pluck('subcamp')->filter()->sort();

        // Query participants with their group info
        $query = Participant::query()
            ->join('original_groups', 'participants.original_group_id', '=', 'original_groups.order_number')
            ->select('participants.*', 'original_groups.subcamp');

        if ($selectedSubcamp) {
            $query->where('original_groups.subcamp', $selectedSubcamp);
        }

        $participants = $query->whereNotNull('target_group')
            ->orderBy('target_group')
            ->get();

        // Group participants by target_group
        $groups = $participants->groupBy('target_group')->map(function ($groupParticipants, $targetGroupId) {
            $total = $groupParticipants->count();
            
            // Stats per nationality
            $stats = $groupParticipants->groupBy('country')->map(function ($members) use ($total) {
                return [
                    'count' => $members->count(),
                    'percentage' => round(($members->count() / $total) * 100, 1)
                ];
            });

            // Find leaders
            $leaders = $groupParticipants->where('is_leader', true);

            // All codes
            $codes = $groupParticipants->pluck('registration_code');

            return [
                'target_group' => $targetGroupId,
                'subcamp' => $groupParticipants->first()->subcamp,
                'leaders' => $leaders,
                'stats' => $stats,
                'codes' => $codes,
                'member_count' => $total
            ];
        });

        return view('admin.groups', compact('groups', 'allSubcamps', 'selectedSubcamp'));
    }
}
