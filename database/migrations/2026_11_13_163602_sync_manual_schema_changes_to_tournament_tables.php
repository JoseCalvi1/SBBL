<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncManualSchemaChangesToTournamentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('tournament_results', 'over_blade')) {
            Schema::table('tournament_results', function (Blueprint $table) {
                $table->string('over_blade')->nullable()->after('blade');
            });
        }

        Schema::table('tournament_matches', function (Blueprint $table) {
            if (!Schema::hasColumn('tournament_matches', 'match_number')) {
                $table->integer('match_number')->nullable()->after('round_number');
            }
            if (!Schema::hasColumn('tournament_matches', 'team1_id')) {
                $table->unsignedBigInteger('team1_id')->nullable()->after('player2_score');
            }
            if (!Schema::hasColumn('tournament_matches', 'team2_id')) {
                $table->unsignedBigInteger('team2_id')->nullable()->after('team1_id');
            }
            if (!Schema::hasColumn('tournament_matches', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('status');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tournament_matches MODIFY COLUMN player1_id BIGINT UNSIGNED NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('tournament_results', 'over_blade')) {
            Schema::table('tournament_results', function (Blueprint $table) {
                $table->dropColumn('over_blade');
            });
        }

        Schema::table('tournament_matches', function (Blueprint $table) {
            $columnsToDrop = array_values(array_filter(
                ['match_number', 'team1_id', 'team2_id', 'locked_by'],
                fn (string $column) => Schema::hasColumn('tournament_matches', $column)
            ));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
}
