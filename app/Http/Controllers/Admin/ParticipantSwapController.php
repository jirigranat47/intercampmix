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
        return view('admin.swap');
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
            'p2_id' => 'required|exists:participants,id',
        ]);

        $p1 = Participant::with('originalGroup')->findOrFail($request->p1_id);
        $p2 = Participant::with('originalGroup')->findOrFail($request->p2_id);

        if ($p1->id === $p2->id) {
            return back()->withErrors(__('Nelze prohodit účastníka se sebou samým.'));
        }

        // Validation: Must be in the same subcamp
        $sc1 = $p1->originalGroup->subcamp ?? null;
        $sc2 = $p2->originalGroup->subcamp ?? null;

        if ($sc1 !== $sc2) {
            return back()->withErrors(__('Účastníci musí být ve stejném subcampu.'));
        }

        try {
            DB::transaction(function() use ($p1, $p2) {
                // Store values
                $code1 = $p1->registration_code;
                $group1 = $p1->target_group;
                
                $code2 = $p2->registration_code;
                $group2 = $p2->target_group;

                // 1. Give P1 a temporary code to free up its code for P2
                $p1->registration_code = 'TEMP_SWAP_' . $p1->id . '_' . time();
                $p1->save();

                // 2. Give P2 the original values of P1
                $p2->registration_code = $code1;
                $p2->target_group = $group1;
                $p2->save();

                // 3. Give P1 the original values of P2
                $p1->registration_code = $code2;
                $p1->target_group = $group2;
                $p1->save();
            });

            return redirect()->route('admin.swap.index')->with('success', __('Účastníci byli úspěšně prohozeni.'));

        } catch (\Exception $e) {
            return back()->withErrors(__('Nastala chyba při prohazování: ') . $e->getMessage());
        }
    }
}
