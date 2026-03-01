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
            
            // Check headers on row 0
            if (count($rows) < 2) {
                return back()->withErrors('Soubor je prázdný nebo nemá dostatek dat.');
            }
            
            // Expected cols from index 0:
            // 6 => Country, 8 => Troop name, 11 => Number of Children, 18 => Order Number, 25 => Subcamp
            $header = $rows[0];
            if (trim($header[6]) !== 'Country' || trim($header[11]) !== 'Number of Children' || trim($header[25]) !== 'Subcamp') {
                return back()->withErrors('Soubor nemá očekávanou strukturu sloupců (chybí Country, Number of Children nebo Subcamp).');
            }

            // Vyčistíme stará data před novým importem
            Participant::truncate();
            OriginalGroup::truncate();

            $totalChildrenImported = 0;
            $groupsProcessed = 0;

            // Procesování řádků (od indexu 1, ignorujeme hlavičku)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Přeskočíme prázdné řádky na konci Excelu
                if (empty(trim($row[18])) && empty(trim($row[6]))) {
                    continue; 
                }

                $orderNumber = trim($row[18] ?? '');
                $country = trim($row[6] ?? 'Unknown');
                $troopName = trim($row[8] ?? '');
                $numChildren = (int)($row[11] ?? 0);
                
                // Převod sloupce 25 (Subcamp) na integer
                $rawSubcamp = trim($row[25] ?? '');
                $subcamp = 1;
                // Pokud z tabulky přijde rovnou číslo, nebo text jako "Subcamp 2"
                if (preg_match('/(\d+)/', $rawSubcamp, $matches)) {
                    $subcamp = (int)$matches[1];
                }

                if ($numChildren <= 0) {
                    continue; // Skip if no children to mix
                }

                $group = OriginalGroup::firstOrCreate(
                    ['order_number' => $orderNumber],
                    [
                        'country' => $country,
                        'subcamp' => $subcamp,
                        'troop_name' => $troopName,
                        'number_of_children' => 0 // we will increment below
                    ]
                );
                
                // Pokud se jednalo o existující, musíme zaručit že má správný subcamp (např. oprava po předchozím loopu s default=1)
                if ($group->subcamp === 1 && $subcamp !== 1) {
                    $group->subcamp = $subcamp;
                }
                
                // Add the children count from this row to the group
                $group->number_of_children += $numChildren;
                $group->save();

                // Create individual participants for algorithmic sorting
                $existingKidsCount = Participant::where('original_group_id', $group->order_number)->count();
                for ($p = 1; $p <= $numChildren; $p++) {
                    $kidIndex = $existingKidsCount + $p;
                    Participant::create([
                        'registration_code' => Str::random(8) . '-' . $kidIndex,
                        'first_name' => 'Kid #' . $kidIndex,
                        'last_name' => "($troopName)",
                        'country' => $country,
                        'original_group_id' => $group->order_number,
                    ]);
                    $totalChildrenImported++;
                }

                $groupsProcessed++;
            }

            return back()->with('success', "Import byl úspěšný! Načteno {$groupsProcessed} skupin s {$totalChildrenImported} dětmi.");

        } catch (\Exception $e) {
            return back()->withErrors('Nastala chyba při čtení souboru: ' . $e->getMessage());
        }
    }
}
