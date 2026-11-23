<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSwissGroupToTournamentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tournament_matches') && !Schema::hasColumn('tournament_matches', 'swiss_group')) {
            Schema::table('tournament_matches', function (Blueprint $table) {
                $table->unsignedTinyInteger('swiss_group')->nullable()->after('bracket_type');
            });
        }

        if (Schema::hasTable('assist_user_event') && !Schema::hasColumn('assist_user_event', 'swiss_group')) {
            Schema::table('assist_user_event', function (Blueprint $table) {
                $table->unsignedTinyInteger('swiss_group')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tournament_matches') && Schema::hasColumn('tournament_matches', 'swiss_group')) {
            Schema::table('tournament_matches', function (Blueprint $table) {
                $table->dropColumn('swiss_group');
            });
        }

        if (Schema::hasTable('assist_user_event') && Schema::hasColumn('assist_user_event', 'swiss_group')) {
            Schema::table('assist_user_event', function (Blueprint $table) {
                $table->dropColumn('swiss_group');
            });
        }
    }
}
