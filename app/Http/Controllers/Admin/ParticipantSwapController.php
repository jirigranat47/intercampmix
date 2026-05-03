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
                
                // Přidělíme nový registrační kód (např. cílová skupina + M (Moved) + ID)
                // Leader má navíc L, aby bylo jasné, že je vedoucí
                $suffix = $p1->is_leader ? '-L' : '-M';
                $p1->registration_code = $newGroup . $suffix . $p1->id;
                
                $p1->save();
            });

            return redirect()->route('admin.swap.index')->with('success', __('Účastník byl úspěšně přesunut do nové skupiny.'));

        } catch (\Exception $e) {
            return back()->withErrors(__('Nastala chyba při přesunu: ') . $e->getMessage());
        }
    }
}
