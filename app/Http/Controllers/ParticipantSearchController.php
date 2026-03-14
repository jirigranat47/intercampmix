<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;

class ParticipantSearchController extends Controller
{
    /**
     * Zobrazí vyhledávací formulář (hlavní stránka)
     */
    public function index(Request $request)
    {
        $code = $request->input('code');
        $participant = null;

        if ($code) {
            $code = strtoupper(trim($code));
            // Hledáme účastníka podle kódu
            $participant = Participant::with('originalGroup')->where('registration_code', $code)->first();

            if (!$participant) {
                return back()->with('error', __('Kód nebyl nalezen. Zkontrolujte prosím zadání.'))->withInput();
            }
        }

        return view('search', [
            'participant' => $participant,
            'code' => $code
        ]);
    }
}
