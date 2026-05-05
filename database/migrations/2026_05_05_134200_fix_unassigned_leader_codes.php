<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Participant;

class FixUnassignedLeaderCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Vyhledáme všechny vedoucí, kteří jsou označeni jako EXTRA_LEADER
        // a opravíme jejich kód tak, aby obsahoval identifikaci subcampu (např. S1-369-L)
        Participant::where('is_leader', true)
            ->where('target_group', 'EXTRA_LEADER')
            ->get()
            ->each(function($leader) {
                $subcamp = $leader->originalGroup->subcamp ?? '?';
                $leader->registration_code = "S{$subcamp}-{$leader->id}-L";
                $leader->save();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Návrat k původnímu formátu EXTRA_L_{id}
        Participant::where('is_leader', true)
            ->where('target_group', 'EXTRA_LEADER')
            ->get()
            ->each(function($leader) {
                $leader->registration_code = "EXTRA_L_{$leader->id}";
                $leader->save();
            });
    }
}
