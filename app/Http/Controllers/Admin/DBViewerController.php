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

        $participantsQuery = Participant::orderBy('target_group');
        if ($selectedSubcamp) {
            $participantsQuery->whereHas('originalGroup', function($q) use ($selectedSubcamp) {
                $q->where('subcamp', $selectedSubcamp);
            });
        }
        
        $participants = $participantsQuery->paginate(100)->withQueryString();
        $totalParticipants = Participant::count();
        $allSubcamps = OriginalGroup::select('subcamp')->distinct()->orderBy('subcamp')->pluck('subcamp');

        return view('admin_db', compact('groups', 'participants', 'totalParticipants', 'allSubcamps', 'selectedSubcamp'));
    }
}
