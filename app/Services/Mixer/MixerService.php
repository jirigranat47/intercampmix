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
        $assignment = $this->assignToTargetGroups($interleavedBundles);
        $buckets = $assignment['buckets'];
        $stats = $assignment['stats'];

        // KROK E: Přiřazení vedoucích --------------------------------------------
        $leaderStats = $this->assignLeaders($buckets);
        $stats = array_merge($stats, $leaderStats);

        // KROK F: Finální zápis do DB --------------------------------------------
        $this->saveToDatabase($buckets);

        return [
            'status' => 'success',
            'message' => "Subcamp {$this->subcampId} rozřazen včetně vedoucích.",
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
            // Jen děti, vedoucí se řeší sepárátně v Kroku E
            $participants = Participant::where('original_group_id', $group->order_number)
                ->where('is_leader', false)
                ->get()
                ->all();

            $total = count($participants);

            if ($total === 0) continue;

            $offset = 0;
            if ($total % 2 !== 0 && $total >= 3) {
                $bundleKids = array_slice($participants, $offset, 3);
                $allBundles[] = new ParticipantBundle($bundleKids);
                $offset += 3;
            }

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
        $queuesByCountry = [];
        foreach ($bundles as $bundle) {
            $queuesByCountry[$bundle->country][] = $bundle;
        }

        uasort($queuesByCountry, function($a, $b) {
            return count($b) <=> count($a);
        });

        $interleaved = [];
        $hasItemsRemaining = true;
        while ($hasItemsRemaining) {
            $hasItemsRemaining = false;
            foreach ($queuesByCountry as $country => &$queue) {
                if (count($queue) > 0) {
                    $hasItemsRemaining = true;
                    $interleaved[] = array_shift($queue);
                }
            }
        }

        return $interleaved;
    }

    /**
     * KROK D
     */
    private function assignToTargetGroups(array $interleavedBundles): array
    {
        $totalKids = 0;
        foreach ($interleavedBundles as $bundle) {
            $totalKids += $bundle->getSize();
        }

        $bucketCount = (int) ceil($totalKids / 8.0);
        if ($bucketCount === 0) return ['buckets' => [], 'stats' => []];

        $buckets = [];
        for ($i = 1; $i <= $bucketCount; $i++) {
            $bucketName = "SC" . $this->subcampId . "_G" . str_pad($i, 2, '0', STR_PAD_LEFT);
            $buckets[$bucketName] = [
                'name' => $bucketName,
                'size' => 0,
                'original_groups_inside' => [],
                'countries_inside' => [],
                'bundles' => [],
                'leader' => null
            ];
        }

        $stats = [
            'total_children' => $totalKids,
            'groups_created' => $bucketCount,
            'tier1' => 0,
            'tier2' => 0,
            'tier3' => 0,
            'tier4' => 0
        ];

        foreach ($interleavedBundles as $bundle) {
            $placedBucketName = null;
            $bundleSize = $bundle->getSize();

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
                uasort($tier1Buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                $placedBucketName = array_key_first($tier1Buckets);
                $stats['tier1']++;
            } else {
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
                        $stats['tier4']++;
                        uasort($buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                        $placedBucketName = array_key_first($buckets);
                    }
                }
            }

            $buckets[$placedBucketName]['size'] += $bundleSize;
            $buckets[$placedBucketName]['original_groups_inside'][] = $bundle->originalGroupId;
            $buckets[$placedBucketName]['countries_inside'][] = $bundle->country;
            $buckets[$placedBucketName]['bundles'][] = $bundle;
        }

        return ['buckets' => $buckets, 'stats' => $stats];
    }

    /**
     * KROK E: Přiřazení vedoucích
     */
    private function assignLeaders(array &$buckets): array
    {
        // Najdeme všechny vedoucí v tomto subcampu
        $allLeaders = Participant::where('is_leader', true)
            ->whereIn('original_group_id', function($query) {
                $query->select('order_number')->from('original_groups')->where('subcamp', $this->subcampId);
            })
            ->get()
            ->groupBy('original_group_id');

        $assignedCount = 0;
        $offDutyCount = 0;

        foreach ($buckets as &$bucket) {
            $assigned = false;
            
            // Priorita: Vedoucí z výpravy, která je v kbelíku
            foreach ($bucket['original_groups_inside'] as $origId) {
                if (isset($allLeaders[$origId]) && $allLeaders[$origId]->isNotEmpty()) {
                    $bucket['leader'] = $allLeaders[$origId]->shift();
                    $assigned = true;
                    $assignedCount++;
                    break;
                }
            }

            // Fallback: Pokud nikdo z "vlastních" už není, vezmeme kohokoliv volného
            if (!$assigned) {
                foreach ($allLeaders as $origId => $leaders) {
                    if ($leaders->isNotEmpty()) {
                        $bucket['leader'] = $leaders->shift();
                        $assigned = true;
                        $assignedCount++;
                        break;
                    }
                }
            }
        }

        // Spočíteme kolik jich zbylo (Off-duty)
        foreach ($allLeaders as $leaders) {
            $offDutyCount += $leaders->count();
        }

        return [
            'leaders_assigned' => $assignedCount,
            'leaders_off_duty' => $offDutyCount
        ];
    }

    /**
     * KROK F: Zápis do DB
     */
    private function saveToDatabase(array $buckets): void
    {
        foreach ($buckets as $bucket) {
            // Uložení dětí
            $ordinal = 1;
            foreach ($bucket['bundles'] as $bundle) {
                foreach ($bundle->participants as $participant) {
                    $participant->target_group = $bucket['name'];
                    $participant->registration_code = $bucket['name'] . "_" . $ordinal;
                    $participant->save();
                    $ordinal++;
                }
            }

            // Uložení vedoucího
            if ($bucket['leader']) {
                $leader = $bucket['leader'];
                $leader->target_group = $bucket['name'];
                $leader->registration_code = $bucket['name'] . "_X";
                $leader->save();
            }
        }
        
        // Důležité: Vedoucí, kteří nebyli přiřazeni, by měli mít smazané staré rozřazení
        // (V reálu se tabulka truncateuje při importu, ale pro jistotu)
    }
}
