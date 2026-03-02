<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;

class ParticipantSearchController extends Controller
{
    /**
     * Zobrazí vyhledávací formulář (hlavní stránka)
     */
    public function index()
    {
        return view('search');
    }

    /**
     * Provede vyhledání účastníka podle registračního kódu
     */
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $code = strtoupper(trim($request->input('code')));
        
        // Hledáme účastníka podle kódu
        $participant = Participant::with('originalGroup')->where('registration_code', $code)->first();

        if (!$participant) {
            return back()->with('error', 'Kód nebyl nalezen. Zkontrolujte prosím zadání.');
        }

        return view('search', [
            'participant' => $participant,
            'code' => $code
        ]);
    }
}
