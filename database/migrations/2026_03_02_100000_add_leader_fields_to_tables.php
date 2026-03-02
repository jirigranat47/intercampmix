<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaderFieldsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('is_leader')->default(false)->after('last_name');
        });

        Schema::table('original_groups', function (Blueprint $table) {
            $table->integer('number_of_leaders')->default(0)->after('number_of_children');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('is_leader');
        });

        Schema::table('original_groups', function (Blueprint $table) {
            $table->dropColumn('number_of_leaders');
        });
    }
}
