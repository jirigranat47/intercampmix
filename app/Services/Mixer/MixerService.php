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
        $totalKids = 0;
        foreach ($interleavedBundles as $bundle) {
            $totalKids += $bundle->getSize();
        }

        $bucketCount = (int) ceil($totalKids / 8.0);
        if ($bucketCount === 0) return [];

        // Inicializujeme kbelíky ("A1".. "Z9" atd.)
        // Zjednodušené indexování SCX_G1, SCX_G2...
        $buckets = [];
        for ($i = 1; $i <= $bucketCount; $i++) {
            $bucketName = "SC" . $this->subcampId . "_G" . str_pad($i, 2, '0', STR_PAD_LEFT);
            $buckets[$bucketName] = [
                'name' => $bucketName,
                'size' => 0,
                'original_groups_inside' => [], // tracking for Priority group constraint
                'countries_inside' => [],       // tracking for Nationality diversity
                'bundles' => []
            ];
        }

        $stats = [
            'total_children' => $totalKids,
            'groups_created' => $bucketCount,
            'tier1' => 0, // Ideal: No OG, No Country
            'tier2' => 0, // Good: No OG
            'tier3' => 0, // Fallback: Space only
            'tier4' => 0  // Overfill: No space anywhere
        ];

        foreach ($interleavedBundles as $bundle) {
            $placedBucketName = null;
            $bundleSize = $bundle->getSize();

            // TIER 1: Najlepší kbelík (má místo A ZÁROVEŇ v něm není původní skupina A ZÁROVEŇ v něm není stejná národnost)
            $tier1Buckets = [];
            foreach ($buckets as $name => $bucket) {
                if ($bucket['size'] + $bundleSize <= 8) {
                    if (!in_array($bundle->originalGroupId, $bucket['original_groups_inside'])) {
                        if (!in_array($bundle->country, $bucket['countries_inside'])) {
                            $tier1Buckets[$name] = $bucket;
                        }
                    }
                }
            }

            if (!empty($tier1Buckets)) {
                // Z Tier 1 najít nejprázdnější
                uasort($tier1Buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                $placedBucketName = array_key_first($tier1Buckets);
                $stats['tier1']++;
            } else {
                // TIER 2: Dobrý kbelík (má místo A ZÁROVEŇ v něm není původní skupina, ale národnost už tam může být)
                $tier2Buckets = [];
                foreach ($buckets as $name => $bucket) {
                    if ($bucket['size'] + $bundleSize <= 8) {
                        if (!in_array($bundle->originalGroupId, $bucket['original_groups_inside'])) {
                            $tier2Buckets[$name] = $bucket;
                        }
                    }
                }

                if (!empty($tier2Buckets)) {
                    uasort($tier2Buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                    $placedBucketName = array_key_first($tier2Buckets);
                    $stats['tier2']++;
                } else {
                    // TIER 3: Má místo (ale porušuje OG nebo národnost)
                    $tier3Buckets = [];
                    foreach ($buckets as $name => $bucket) {
                        if ($bucket['size'] + $bundleSize <= 8) {
                            $tier3Buckets[$name] = $bucket;
                        }
                    }

                    if (!empty($tier3Buckets)) {
                        uasort($tier3Buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                        $placedBucketName = array_key_first($tier3Buckets);
                        $stats['tier3']++;
                    } else {
                        // TIER 4: ÚPLNÝ FALLBACK (přetečení přes 10)
                        $stats['tier4']++;
                        uasort($buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                        $placedBucketName = array_key_first($buckets);
                    }
                }
            }

            // Samotné vložení do kbelíku
            $buckets[$placedBucketName]['size'] += $bundleSize;
            $buckets[$placedBucketName]['original_groups_inside'][] = $bundle->originalGroupId;
            $buckets[$placedBucketName]['countries_inside'][] = $bundle->country;
            $buckets[$placedBucketName]['bundles'][] = $bundle;
        }

        // FINÁLNÍ ZÁPIS DO DB
        foreach ($buckets as $bucket) {
            $ordinal = 1;
            foreach ($bucket['bundles'] as $bundle) {
                foreach ($bundle->participants as $participant) {
                    $participant->target_group = $bucket['name'];
                    $participant->registration_code = $bucket['name'] . "_" . $ordinal;
                    $participant->save();
                    $ordinal++;
                }
            }
        }

        return $stats;
    }
}
