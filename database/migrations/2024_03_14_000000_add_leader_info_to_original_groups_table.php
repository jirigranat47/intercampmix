<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaderInfoToOriginalGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('original_groups', function (Blueprint $blueprint) {
            $blueprint->string('leader_name')->nullable();
            $blueprint->string('leader_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('original_groups', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['leader_name', 'leader_phone']);
        });
    }
}
