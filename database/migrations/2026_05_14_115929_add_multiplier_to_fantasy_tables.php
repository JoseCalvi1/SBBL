<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiplierToFantasyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fantasy_rosters', function (Blueprint $table) {
            $table->decimal('level_multiplier', 3, 1)->default(1.0)->after('is_captain');
        });

        Schema::table('fantasy_event_lineups', function (Blueprint $table) {
            $table->decimal('level_multiplier', 3, 1)->default(1.0)->after('is_captain');
        });
    }

    public function down()
    {
        Schema::table('fantasy_rosters', function (Blueprint $table) {
            $table->dropColumn('level_multiplier');
        });
        Schema::table('fantasy_event_lineups', function (Blueprint $table) {
            $table->dropColumn('level_multiplier');
        });
    }
}
