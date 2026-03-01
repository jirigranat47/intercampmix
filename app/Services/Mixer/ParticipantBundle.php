<?php

namespace App\Services\Mixer;

class ParticipantBundle
{
    /**
     * @var \App\Models\Participant[]
     */
    public $participants = [];

    public $country;
    public $originalGroupId;

    public function __construct(array $participants)
    {
        $this->participants = $participants;
        
        if (count($participants) > 0) {
            $this->country = $participants[0]->country;
            $this->originalGroupId = $participants[0]->original_group_id;
        }
    }

    public function getSize(): int
    {
        return count($this->participants);
    }
}
