<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OriginalGroup;
use App\Models\Participant;

class DBViewerController extends Controller
{
    public function index()
    {
        $groups = OriginalGroup::orderBy('subcamp')->orderBy('order_number')->get();
        // Omezíme počet dětí pro prohlížeč ať nepadne prohlížeč jestli jich je 2000
        $participants = Participant::orderBy('target_group')->limit(300)->get();
        $totalParticipants = Participant::count();

        return view('admin_db', compact('groups', 'participants', 'totalParticipants'));
    }
}
