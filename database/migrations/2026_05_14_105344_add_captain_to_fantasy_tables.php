<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaptainToFantasyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fantasy_rosters', function (Blueprint $table) {
            $table->boolean('is_captain')->default(false)->after('status');
        });

        Schema::table('fantasy_event_lineups', function (Blueprint $table) {
            $table->boolean('is_captain')->default(false)->after('fantasy_real_player_id');
        });
    }

    public function down()
    {
        Schema::table('fantasy_rosters', function (Blueprint $table) {
            $table->dropColumn('is_captain');
        });
        Schema::table('fantasy_event_lineups', function (Blueprint $table) {
            $table->dropColumn('is_captain');
        });
    }
}
