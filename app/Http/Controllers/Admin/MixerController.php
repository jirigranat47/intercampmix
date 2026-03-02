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
            
            $results[] = "Subcamp {$scLabel}: {$stats['groups_created']} skupin / {$stats['total_children']} dětí (Ideální: {$stats['tier1']}, Jen skupina: {$stats['tier2']}, Fallback: {$stats['tier3']}, Přeteklo: {$stats['tier4']}).";
            $totalFallbacks += ($stats['tier3'] + $stats['tier4']);
        }

        $msg = "Úspěšně rozřazeno! " . implode(" ", $results);
        if ($totalFallbacks > 0) {
            $msg .= " (Upozornění: Pravidlo o unikátnosti původní skupiny nebo národnosti muselo být $totalFallbacks krát na konci prolomeno [Fallback]).";
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
            return back()->withErrors('Nejsou k dispozici žádná data k exportu.');
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

                fputcsv($file, [
                    $p->target_group,
                    $subcamp,
                    $p->original_group_id,
                    $p->country,
                    $troop,
                    $p->registration_code
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
