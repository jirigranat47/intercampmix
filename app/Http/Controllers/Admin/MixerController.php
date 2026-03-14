<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OriginalGroup;
use App\Models\Participant;
use App\Services\Mixer\MixerService;

class MixerController extends Controller
{
    /**
     * Spustí algoritmus pro všechny známé Subcampy
     */
    public function runAlgorithm(Request $request)
    {
        // 1. Zjistit jaké subcampy vůbec máme z nahraného Excelu
        $subcamps = OriginalGroup::select('subcamp')->distinct()->pluck('subcamp')->toArray();

        // 2. Clear old target groups (bezpečnostní smazání předřazenosti) a resetovat registrační klíče aby nedošlo ke kolizi
        Participant::query()->update([
            'target_group' => null,
            'registration_code' => \Illuminate\Support\Facades\DB::raw("CONCAT('TEMP_', id, '_', md5(random()::text))")
        ]);

        $results = [];
        $totalFallbacks = 0;

        foreach ($subcamps as $scLabel) {
            $service = new MixerService($scLabel);
            $outcome = $service->mix();
            $stats = $outcome['stats'];
            
            $results[] = __('Subcamp') . " {$scLabel}: {$stats['groups_created']} " . __('skupin') . " / {$stats['total_children']} " . __('dětí') . " (" . __('Ideální') . ": {$stats['tier1']}, " . __('Jen skupina') . ": {$stats['tier2']}, " . __('Fallback') . ": {$stats['tier3']}, " . __('Přeteklo') . ": {$stats['tier4']}).";
            $totalFallbacks += ($stats['tier3'] + $stats['tier4']);
        }

        $msg = __('Úspěšně rozřazeno!') . " " . implode(" ", $results);
        if ($totalFallbacks > 0) {
            $msg .= " " . __('(Upozornění: Pravidlo o unikátnosti původní skupiny nebo národnosti muselo být :total krát na konci prolomeno [Fallback]).', ['total' => $totalFallbacks]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Vygeneruje a stáhne CSV výsledků
     */
    public function export(Request $request)
    {
        $participants = Participant::orderBy('target_group')->get();

        if ($participants->isEmpty()) {
            return back()->withErrors(__('Nejsou k dispozici žádná data k exportu.'));
        }

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=rozrazeni_intercamp.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($participants) {
            $file = fopen('php://output', 'w');
            
            // BOM pro český a německý Excel Unicode fix
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Hlavička CSV
            fputcsv($file, ['Target Group', 'Subcamp', 'Original Order Number', 'Country', 'Troop Name', 'Kid ID Code'], ';');

            foreach ($participants as $p) {
                // Přibalíme data z OriginalGroup k dosažení úplného reportu
                $orig = OriginalGroup::where('order_number', $p->original_group_id)->first();
                $subcamp = $orig ? $orig->subcamp : '?';
                $troop = $orig ? $orig->troop_name : 'Unknown';

                $targetGroup = $p->target_group;
                $regCode = $p->registration_code;

                // Označení nepřiřazených vedoucí (off-duty) pro přehlednost v exportu
                if (empty($targetGroup) && $p->is_leader) {
                    $targetGroup = 'EXTRA_LEADER';
                    $regCode = 'N/A';
                }

                fputcsv($file, [
                    $targetGroup,
                    $subcamp,
                    $p->original_group_id,
                    $p->country,
                    $troop,
                    $regCode
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
