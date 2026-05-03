<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantSwapController extends Controller
{
    public function index()
    {
        $targetGroups = Participant::whereNotNull('target_group')
            ->distinct()
            ->pluck('target_group')
            ->sort()
            ->values();

        return view('admin.swap', compact('targetGroups'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        if (strlen($query) < 2) return response()->json([]);

        $participants = Participant::with('originalGroup')
            ->where('registration_code', 'ILIKE', "%{$query}%") // ILIKE for Postgres case-insensitive
            ->orWhere('first_name', 'ILIKE', "%{$query}%")
            ->orWhere('last_name', 'ILIKE', "%{$query}%")
            ->limit(15)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'code' => $p->registration_code,
                    'name' => $p->first_name . ' ' . $p->last_name,
                    'group' => $p->target_group ?? 'EXTRA_LEADER',
                    'subcamp' => $p->originalGroup->subcamp ?? '?',
                    'troop' => $p->originalGroup->troop_name ?? '?',
                    'country' => $p->country
                ];
            });

        return response()->json($participants);
    }

    public function swap(Request $request)
    {
        $request->validate([
            'p1_id' => 'required|exists:participants,id',
            'target_group' => 'required|string',
        ]);

        $p1 = Participant::with('originalGroup')->findOrFail($request->p1_id);
        $newGroup = trim($request->target_group);

        // Volitelná validace: zjistit zda skupina existuje
        $groupExists = Participant::where('target_group', $newGroup)->exists();
        if (!$groupExists && $newGroup !== 'EXTRA_LEADER') {
            return back()->withErrors(__('Cílová skupina neexistuje. Zkontrolujte prosím překlepy.'));
        }

        try {
            DB::transaction(function() use ($p1, $newGroup) {
                $p1->target_group = $newGroup;
                
                // Zjistíme jaké kódy už ve skupině jsou
                $existingCodes = Participant::where('target_group', $newGroup)->pluck('registration_code');
                $maxIndex = 0;
                $hasLeaderX = false;

                foreach ($existingCodes as $code) {
                    $parts = explode('-', $code);
                    $lastPart = end($parts);
                    
                    if (strtoupper($lastPart) === 'X') {
                        $hasLeaderX = true;
                    } elseif (is_numeric($lastPart)) {
                        $maxIndex = max($maxIndex, (int) $lastPart);
                    }
                }

                // Pokud je přesouvaná osoba vedoucí a skupina ještě nemá svého "X" vedoucího
                if ($p1->is_leader && !$hasLeaderX) {
                    $p1->registration_code = $newGroup . '-X';
                } else {
                    // Jinak přidělíme další volné číslo (např. 8 pokud je max 7)
                    $newIndex = $maxIndex + 1;
                    
                    // Podpora pro případné nuly na začátku (jak zaznělo v požadavku S2-01-08)
                    // Pokud už ve skupině nějaké nuly jsou, můžeme to detekovat, ale pro jistotu 
                    // se budeme držet standardu a přidáme případně padování, pokud to uživatel tak zamýšlel.
                    // Ale dle readme je to 'S1-17-7', takže necháme bez padování.
                    $p1->registration_code = $newGroup . '-' . $newIndex;
                }
                
                $p1->save();
            });

            return redirect()->route('admin.swap.index')->with('success', __('Účastník byl úspěšně přesunut do nové skupiny.'));

        } catch (\Exception $e) {
            return back()->withErrors(__('Nastala chyba při přesunu: ') . $e->getMessage());
        }
    }
}
