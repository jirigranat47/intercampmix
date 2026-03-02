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

        // Maximálně 8 dětí do skupiny (aby s jedním vedoucím bylo max 9 osob celkem)
        $maxKidsPerBucket = 8;
        $bucketCount = (int) ceil($totalKids / (float)$maxKidsPerBucket);
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
                if ($bucket['size'] + $bundleSize <= $maxKidsPerBucket) {
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
                    if ($bucket['size'] + $bundleSize <= $maxKidsPerBucket) {
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
                        if ($bucket['size'] + $bundleSize <= $maxKidsPerBucket) {
                            $tier3Buckets[$name] = $bucket;
                        }
                    }

                    if (!empty($tier3Buckets)) {
                        uasort($tier3Buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                        $placedBucketName = array_key_first($tier3Buckets);
                        $stats['tier3']++;
                    } else {
                        // TIER 4 (Fallback)
                        // Musíme najít absolutně jakýkoliv bucket, do kterého se vejdou.
                        $stats['tier4']++;
                        $tier4Buckets = [];
                        foreach ($buckets as $name => $bucket) {
                            if ($bucket['size'] + $bundleSize <= $maxKidsPerBucket) {
                                $tier4Buckets[$name] = $bucket;
                            }
                        }
                        
                        // Pokud už fakt není nikde místo (např. kvůli kombinaci balíčků [3] a [2]),
                        // musíme vyrobit nový, nouzový bucket, abychom nepřekročili limit!
                        if (empty($tier4Buckets)) {
                            $stats['groups_created']++;
                            $stats['tier4']++; // extra penalty note
                            $newBucketId = count($buckets) + 1;
                            $bucketName = "SC" . $this->subcampId . "_G" . str_pad($newBucketId, 2, '0', STR_PAD_LEFT);
                            $buckets[$bucketName] = [
                                'name' => $bucketName,
                                'size' => 0,
                                'original_groups_inside' => [],
                                'countries_inside' => [],
                                'bundles' => [],
                                'leader' => null
                            ];
                            $placedBucketName = $bucketName;
                        } else {
                            uasort($tier4Buckets, function($a, $b) { return $a['size'] <=> $b['size']; });
                            $placedBucketName = array_key_first($tier4Buckets);
                        }
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
     * KROK E: Přiřazení vedoucích (v kolech)
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

        $initialLeaderCounts = [];
        foreach ($allLeaders as $origId => $leaders) {
            $initialLeaderCounts[$origId] = $leaders->count();
        }

        $emptyBucketIndices = array_keys($buckets);
        $assignedCount = 0;
        $round = 1;

        while (!empty($emptyBucketIndices)) {
            $candidatesToDraft = [];
            
            // Určení vhodných původních skupin pro toto kolo
            foreach ($initialLeaderCounts as $origId => $initialCount) {
                // Runda 1: všichni co mají alespoň 1
                // Runda 2: všichni co začínali s >= 3
                // Runda 3: všichni co začínali s >= 4, atd.
                $minRequired = ($round === 1) ? 1 : ($round + 1);
                
                if ($initialCount >= $minRequired && isset($allLeaders[$origId]) && $allLeaders[$origId]->isNotEmpty()) {
                    $candidatesToDraft[] = $origId;
                }
            }
            
            // Seřadit kandidáty podle počtu výchozích vedoucích (sestupně),
            // aby větší skupiny měly jistotu, že dostanou místo dřív, než dojdou volné skupiny.
            // Pokud dojdou emptyBuckets v tomto kole, vynecháni budou ti s nejméně vedoucími.
            usort($candidatesToDraft, function($a, $b) use ($initialLeaderCounts) {
                return $initialLeaderCounts[$b] <=> $initialLeaderCounts[$a];
            });

            // Pokud v tomto kole už nejsou žádní kandidáti
            if (empty($candidatesToDraft)) {
                $totalRemaining = 0;
                foreach ($allLeaders as $leaders) { 
                    $totalRemaining += $leaders->count(); 
                }
                if ($totalRemaining === 0) {
                    break; // Už nemáme VŮBEC žádné vedoucí pro naplnění skupin
                }
                
                // Pojistka pro případ, že nějaké nepřiřazené skupiny zbyly, 
                // ale nikdo už nesplňuje podmínky (např. všichni měli jen po 1 vedoucím).
                $maxInitial = !empty($initialLeaderCounts) ? max($initialLeaderCounts) : 0;
                if ($round > $maxInitial) {
                    // Nouzově vezmeme kohokoliv, kdo ještě zbyl v off-duty (ačkoliv to narušuje pravidlo)
                    // k dokončení povinného naplnění bucketů
                    foreach ($allLeaders as $origId => $leaders) {
                        if ($leaders->isNotEmpty()) {
                            $candidatesToDraft[] = $origId;
                        }
                    }
                } else {
                    $round++;
                    continue; // Skip this round if no one qualifies but we have remaining rounds to check
                }
            }

            // Fáze MATCH: Pokusíme se přiřadit kandidáty k bucketům, kde mají děti z vlastní výpravy
            $remainingCandidates = [];
            foreach ($candidatesToDraft as $origId) {
                if (empty($emptyBucketIndices)) break;
                
                $matched = false;
                foreach ($emptyBucketIndices as $idx => $bucketName) {
                    if (in_array($origId, $buckets[$bucketName]['original_groups_inside'])) {
                        // Našli jsme bucket s dětmi z této původní skupiny
                        $buckets[$bucketName]['leader'] = $allLeaders[$origId]->shift();
                        $assignedCount++;
                        $matched = true;
                        unset($emptyBucketIndices[$idx]); // Kbelík je plný
                        break;
                    }
                }
                // Pokud nikoho svého nenašel, musíme ho přiřadit naslepo v další fázi tohoto kola
                if (!$matched) {
                    $remainingCandidates[] = $origId;
                }
            }
            
            // Fáze FALLBACK: Nepřiřazené kandidáty z této rundy nacpeme do zbývajících volných bucketů
            foreach ($remainingCandidates as $origId) {
                if (empty($emptyBucketIndices)) break;
                
                $idx = array_key_first($emptyBucketIndices);
                $bucketName = $emptyBucketIndices[$idx];
                
                $buckets[$bucketName]['leader'] = $allLeaders[$origId]->shift();
                $assignedCount++;
                unset($emptyBucketIndices[$idx]);
            }
            
            $round++;
        }

        $offDutyCount = 0;
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
