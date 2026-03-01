<?php

namespace App\Services\Mixer;

use App\Models\Participant;
use App\Models\OriginalGroup;

class MixerService
{
    private $subcampId;

    public function __construct(int $subcampId)
    {
        $this->subcampId = $subcampId;
    }

    /**
     * Spustí řazení pro aktuální subcamp
     */
    public function mix(): array
    {
        // 1. Získání původních skupin v tomto subcampu (společně s účastníky)
        $groups = OriginalGroup::where('subcamp', $this->subcampId)->get();

        if ($groups->isEmpty()) {
            return ['status' => 'error', 'message' => "Žádná data pro Subcamp {$this->subcampId}."];
        }

        // KROK A: Dekompozice do balíčků ----------------------------------------
        $bundles = $this->decomposeIntoBundles($groups);

        // KROK B a C: Třídění podle národnosti a Round Robin --------------------
        $interleavedBundles = $this->interleaveRoundRobin($bundles);

        // KROK D: Plnění Cílových Skupin (Placement) ----------------------------
        $stats = $this->assignToTargetGroups($interleavedBundles);

        return [
            'status' => 'success',
            'message' => "Subcamp {$this->subcampId} rozřazen.",
            'stats' => $stats
        ];
    }

    /**
     * KROK A
     * @return ParticipantBundle[]
     */
    private function decomposeIntoBundles($groups): array
    {
        $allBundles = [];

        foreach ($groups as $group) {
            $participants = Participant::where('original_group_id', $group->order_number)->get()->all();
            $total = count($participants);

            if ($total === 0) continue;

            // Logika rozkládání podle specifikace
            // Pokud N je liché -> jeden balíček o 3 lidech, zbytek po 2
            // Pokud N je sudé -> všechny balíčky po 2
            // Pokud N <= 3 -> celý zbytek je 1 balíček
            
            $offset = 0;
            
            if ($total % 2 !== 0 && $total >= 3) {
                // Liché
                $bundleKids = array_slice($participants, $offset, 3);
                $allBundles[] = new ParticipantBundle($bundleKids);
                $offset += 3;
            }

            // Zbytek padne do dvojic (popř. samostatná jednička pokud N=1)
            while ($offset < $total) {
                $take = min(2, $total - $offset);
                $bundleKids = array_slice($participants, $offset, $take);
                $allBundles[] = new ParticipantBundle($bundleKids);
                $offset += $take;
            }
        }

        return $allBundles;
    }

    /**
     * KROK B & C
     * @param ParticipantBundle[] $bundles
     * @return ParticipantBundle[]
     */
    private function interleaveRoundRobin(array $bundles): array
    {
        // Seskupení do "front" podle Země
        $queuesByCountry = [];
        foreach ($bundles as $bundle) {
            $queuesByCountry[$bundle->country][] = $bundle;
        }

        // Můžeme seřadit země podle velikosti fronty sestupně pro optimální prokládání
        uasort($queuesByCountry, function($a, $b) {
            return count($b) <=> count($a); // descending
        });

        $interleaved = [];
        
        // Round robin tahání
        $hasItemsRemaining = true;
        while ($hasItemsRemaining) {
            $hasItemsRemaining = false;
            
            foreach ($queuesByCountry as $country => &$queue) {
                if (count($queue) > 0) {
                    $hasItemsRemaining = true;
                    // Vytáhneme vrchní prvek z fronty
                    $interleaved[] = array_shift($queue);
                }
            }
        }

        return $interleaved;
    }

    /**
     * KROK D
     * Rozdělí lidi např. do 90 kbelíků podle toho kolik lidí je celkem
     * @param ParticipantBundle[] $interleavedBundles
     */
    private function assignToTargetGroups(array $interleavedBundles): array
    {
        // Spočítáme si celkový počet lidí abychom zjistili, kolik kbelíků (groups) skutečně založit pro tento subcamp.
        // Cíl je aby kapacity byly co nejblíže 10.
        $totalKids = 0;
        foreach ($interleavedBundles as $bundle) {
            $totalKids += $bundle->getSize();
        }

        $bucketCount = (int) ceil($totalKids / 10.0);
        if ($bucketCount === 0) return [];

        // Inicializujeme kbelíky ("A1".. "Z9" atd.)
        // Zjednodušené indexování SCX_G1, SCX_G2...
        $buckets = [];
        for ($i = 1; $i <= $bucketCount; $i++) {
            $bucketName = "SC" . $this->subcampId . "_G" . str_pad($i, 2, '0', STR_PAD_LEFT);
            $buckets[$bucketName] = [
                'name' => $bucketName,
                'size' => 0,
                'original_groups_inside' => [], // tracking for Priority 3 constraint
                'bundles' => []
            ];
        }

        $fallbacksTriggered = 0;

        foreach ($interleavedBundles as $bundle) {
            $placedBucketName = null;
            $bundleSize = $bundle->getSize();

            // 1. Najít ideální kbelíky (má místo A ZÁROVEŇ neni v něm původní skupina)
            $validBuckets = [];
            foreach ($buckets as $name => $bucket) {
                if ($bucket['size'] + $bundleSize <= 10) {
                    if (!in_array($bundle->originalGroupId, $bucket['original_groups_inside'])) {
                        $validBuckets[$name] = $bucket;
                    }
                }
            }

            if (!empty($validBuckets)) {
                // Z Validních najít ten nejprázdnější (Priorita 2)
                uasort($validBuckets, function($a, $b) {
                    return $a['size'] <=> $b['size'];
                });
                $placedBucketName = array_key_first($validBuckets);
            } else {
                // FALLBACK
                // 2. Pokud se nenašel vyhovující, vypneme "ZÁROVEŇ neni původní skupina"
                // prostě bereme cokoliv kde je místo, i nad povolených 10 pokud nelze jinak?
                $fallbacksTriggered++;
                
                $anyPlaceBuckets = [];
                foreach ($buckets as $name => $bucket) {
                    if ($bucket['size'] + $bundleSize <= 10) {
                        $anyPlaceBuckets[$name] = $bucket;
                    }
                }

                if (!empty($anyPlaceBuckets)) {
                    uasort($anyPlaceBuckets, function($a, $b) {
                        return $a['size'] <=> $b['size'];
                    });
                    $placedBucketName = array_key_first($anyPlaceBuckets);
                } else {
                    // ÚPLNÝ FALLBACK: Žádný kbelík nemá místo do 10, nacpeme to k někomu komu se to líbí nejméně aby přetekli jen o málo
                    uasort($buckets, function($a, $b) {
                        return $a['size'] <=> $b['size'];
                    });
                    $placedBucketName = array_key_first($buckets);
                }
            }

            // Samotné vložení do kbelíku
            $buckets[$placedBucketName]['size'] += $bundleSize;
            $buckets[$placedBucketName]['original_groups_inside'][] = $bundle->originalGroupId;
            $buckets[$placedBucketName]['bundles'][] = $bundle;
        }

        // FINÁLNÍ ZÁPIS DO DB
        foreach ($buckets as $bucket) {
            foreach ($bucket['bundles'] as $bundle) {
                foreach ($bundle->participants as $participant) {
                    $participant->target_group = $bucket['name'];
                    $participant->save();
                }
            }
        }

        return [
            'total_children' => $totalKids,
            'groups_created' => $bucketCount,
            'fallbacks_used' => $fallbacksTriggered
        ];
    }
}
