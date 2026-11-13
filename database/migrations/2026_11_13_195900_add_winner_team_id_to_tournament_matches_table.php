<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWinnerTeamIdToTournamentMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('tournament_matches', 'winner_team_id')) {
            Schema::table('tournament_matches', function (Blueprint $table) {
                $table->foreignId('winner_team_id')
                    ->nullable()
                    ->after('team2_id')
                    ->constrained('teams')
                    ->onDelete('set null');
            });
        }

        // Migrar registros existentes de duelos por equipos que guardaron el ID del equipo en winner_id
        DB::table('tournament_matches')
            ->whereNotNull('team1_id')
            ->whereNotNull('winner_id')
            ->whereNull('winner_team_id')
            ->whereIn('winner_id', DB::table('teams')->select('id'))
            ->update([
                'winner_team_id' => DB::raw('winner_id'),
                'winner_id'      => null,
            ]);

        DB::table('tournament_matches')
            ->whereNotNull('team1_id')
            ->whereNotNull('winner_id')
            ->update(['winner_id' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('tournament_matches', 'winner_team_id')) {
            DB::table('tournament_matches')
                ->whereNotNull('team1_id')
                ->whereNotNull('winner_team_id')
                ->whereNull('winner_id')
                ->whereIn('winner_team_id', DB::table('users')->select('id'))
                ->update([
                    'winner_id' => DB::raw('winner_team_id'),
                ]);

            Schema::table('tournament_matches', function (Blueprint $table) {
                $table->dropForeign(['winner_team_id']);
                $table->dropColumn('winner_team_id');
            });
        }
    }
}
