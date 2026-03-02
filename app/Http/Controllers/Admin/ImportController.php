<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OriginalGroup;
use App\Models\Participant;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin_import');
    }

    public function process(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('excel_file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Expected cols names:
            // Country, Troop name, Number of Children, Order Number, Subcamp, Number of Leaders
            $header = array_map('trim', $rows[0]);
            
            $colCountry = array_search('Country', $header);
            $colChildren = array_search('Number of Children', $header);
            $colSubcamp = array_search('Subcamp', $header);
            $colOrderNum = array_search('Order Number', $header);
            $colTroop = array_search('Troop name', $header);
            $colLeaders = array_search('Number of Leaders', $header);

            if ($colCountry === false || $colChildren === false || $colSubcamp === false) {
                return back()->withErrors('Soubor nemá očekávanou strukturu sloupců (chybí Country, Number of Children nebo Subcamp).');
            }

            // Vyčistíme stará data před novým importem
            Participant::truncate();
            OriginalGroup::truncate();

            $totalChildrenImported = 0;
            $totalLeadersImported = 0;
            $groupsProcessed = 0;

            // Procesování řádků (od indexu 1, ignorujeme hlavičku)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Přeskočíme prázdné řádky na konci Excelu
                if (empty(trim($row[$colOrderNum] ?? '')) && empty(trim($row[$colCountry] ?? ''))) {
                    continue; 
                }

                $orderNumber = trim($row[$colOrderNum] ?? '');
                $country = trim($row[$colCountry] ?? 'Unknown');
                $troopName = trim($row[$colTroop] ?? '');
                $numChildren = (int)($row[$colChildren] ?? 0);
                $numLeaders = ($colLeaders !== false) ? (int)($row[$colLeaders] ?? 0) : 0;
                
                $rawSubcamp = trim($row[$colSubcamp] ?? '');
                $subcamp = 1;
                if (preg_match('/(\d+)/', $rawSubcamp, $matches)) {
                    $subcamp = (int)$matches[1];
                }

                if ($numChildren <= 0 && $numLeaders <= 0) {
                    continue; // Skip if nothing in the group
                }

                $group = OriginalGroup::firstOrCreate(
                    ['order_number' => $orderNumber],
                    [
                        'country' => $country,
                        'subcamp' => $subcamp,
                        'troop_name' => $troopName,
                        'number_of_children' => 0,
                        'number_of_leaders' => 0
                    ]
                );
                
                if ($group->subcamp === 1 && $subcamp !== 1) {
                    $group->subcamp = $subcamp;
                }
                
                $group->number_of_children += $numChildren;
                $group->number_of_leaders += $numLeaders;
                $group->save();

                // Create children
                $existingKidsCount = Participant::where('original_group_id', $group->order_number)->where('is_leader', false)->count();
                for ($p = 1; $p <= $numChildren; $p++) {
                    $kidIndex = $existingKidsCount + $p;
                    Participant::create([
                        'registration_code' => 'TEMP_CHILD_' . Str::random(5), // Will be replaced by Mixer
                        'first_name' => 'Kid #' . $kidIndex,
                        'last_name' => "($troopName)",
                        'is_leader' => false,
                        'country' => $country,
                        'original_group_id' => $group->order_number,
                    ]);
                    $totalChildrenImported++;
                }

                // Create leaders
                $existingLeadersCount = Participant::where('original_group_id', $group->order_number)->where('is_leader', true)->count();
                for ($l = 1; $l <= $numLeaders; $l++) {
                    $leaderIndex = $existingLeadersCount + $l;
                    Participant::create([
                        'registration_code' => 'TEMP_LEADER_' . Str::random(5), // Will be replaced by Mixer
                        'first_name' => 'Leader #' . $leaderIndex,
                        'last_name' => "($troopName)",
                        'is_leader' => true,
                        'country' => $country,
                        'original_group_id' => $group->order_number,
                    ]);
                    $totalLeadersImported++;
                }

                $groupsProcessed++;
            }

            return back()->with('success', "Import byl úspěšný! Načteno {$groupsProcessed} skupin s {$totalChildrenImported} dětmi a {$totalLeadersImported} vedoucími.");

        } catch (\Exception $e) {
            return back()->withErrors('Nastala chyba při čtení souboru: ' . $e->getMessage());
        }
    }
}
