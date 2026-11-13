<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToSbblTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tournament_results', function (Blueprint $table) {
            $table->index('event_id', 'idx_tr_event_id');
            $table->index(['blade', 'ratchet', 'bit'], 'idx_tr_combo');
        });

        Schema::table('match_battles', function (Blueprint $table) {
            $table->index(['tournament_match_id', 'winner_id'], 'idx_mb_match_winner');
        });

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->index(['event_id', 'round_number', 'status'], 'idx_tm_event_round_status');
        });

        Schema::table('assist_user_event', function (Blueprint $table) {
            $table->index(['event_id', 'user_id'], 'idx_aue_event_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tournament_results', function (Blueprint $table) {
            $table->dropIndex('idx_tr_event_id');
            $table->dropIndex('idx_tr_combo');
        });

        Schema::table('match_battles', function (Blueprint $table) {
            $table->dropIndex('idx_mb_match_winner');
        });

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropIndex('idx_tm_event_round_status');
        });

        Schema::table('assist_user_event', function (Blueprint $table) {
            $table->dropIndex('idx_aue_event_user');
        });
    }
}
