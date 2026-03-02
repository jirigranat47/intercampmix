<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OriginalGroup;
use App\Models\Participant;

class DBViewerController extends Controller
{
    public function index(Request $request)
    {
        $selectedSubcamp = $request->input('subcamp');
        
        $groupsQuery = OriginalGroup::orderBy('subcamp')->orderBy('order_number');
        if ($selectedSubcamp) {
            $groupsQuery->where('subcamp', $selectedSubcamp);
        }
        $groups = $groupsQuery->get();

        $targetGroupsQuery = Participant::whereNotNull('target_group')->select('target_group')->distinct()->orderBy('target_group');
        if ($selectedSubcamp) {
            $targetGroupsQuery->whereHas('originalGroup', function($q) use ($selectedSubcamp) {
                $q->where('subcamp', $selectedSubcamp);
            });
        }
        
        $targetGroups = $targetGroupsQuery->get();
        
        $targetGroupNames = $targetGroups->pluck('target_group');
        $participantsByGroup = Participant::whereIn('target_group', $targetGroupNames)
            ->with('originalGroup')
            ->orderBy('target_group')
            ->orderBy('is_leader', 'desc') // Vedoucí napřed
            ->get()
            ->groupBy('target_group');

        $totalParticipants = Participant::count();
        $allSubcamps = OriginalGroup::select('subcamp')->distinct()->orderBy('subcamp')->pluck('subcamp');

        return view('admin_db', compact('groups', 'targetGroups', 'participantsByGroup', 'totalParticipants', 'allSubcamps', 'selectedSubcamp'));
    }
}
